<?php

require_once ROOT_PATH . '/app/models/FormSubmission.php';
require_once ROOT_PATH . '/app/models/FormType.php';
require_once ROOT_PATH . '/app/models/FormBuilder.php';

class FormSubmissionController {

    private function getClientUser(){
        if (!isset($_SESSION['id_user'])) {
            header('Location: ?url=login');
            exit;
        }
        return $_SESSION['id_user'];
    }

    public function submit(){
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $idUser = $this->getClientUser();
        $idVersion = intval($_POST['id_form_version'] ?? 0);

        if (!$idVersion) {
            echo json_encode(['success' => false, 'message' => 'Formulario inválido.']);
            exit;
        }

        require_once ROOT_PATH . '/app/models/FormType.php';
        require_once ROOT_PATH . '/app/models/FormBuilder.php';

        $formTypeModel = new FormType();
        $builderModel  = new FormBuilder();

        $version = $builderModel->getVersion($idVersion);
        if (!$version) {
            echo json_encode(['success' => false, 'message' => 'Versión de formulario no encontrada.']);
            exit;
        }

        $formType = $formTypeModel->findById($version['id_form_type']);
        if (!$formType || !$formType['status'] || !$formType['allow_digital_fill']) {
            echo json_encode(['success' => false, 'message' => 'Este formulario no está disponible para llenado digital.']);
            exit;
        }

        $structure = $builderModel->getStructure($idVersion);

        $requiredFields = [];
        foreach ($structure as $section) {
            foreach ($section['fields'] as $field) {
                if (!empty($field['is_required'])) {
                    $requiredFields[$field['id_form_field']] = $field['label'];
                }
            }
        }

        $fields = $_POST['fields'] ?? [];
        foreach ($requiredFields as $fieldId => $label) {
            $value = $fields[$fieldId] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if (trim($value) === '') {
                echo json_encode(['success' => false, 'message' => "El campo \"{$label}\" es obligatorio."]);
                exit;
            }
        }

        $submissionModel = new FormSubmission();
        $result = $submissionModel->create($idVersion, $idUser);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Error al crear el envío.']);
            exit;
        }

        $idSubmission = $result['id_form_submission'];

        $uploadDir = ROOT_PATH . '/uploads/form_attachments/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        foreach ($fields as $fieldId => $value) {
            $fieldId = intval($fieldId);
            if ($fieldId <= 0) continue;

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if (isset($_FILES['fields']) && isset($_FILES['fields']['name'][$fieldId]) && !empty($_FILES['fields']['name'][$fieldId])) {
                $fileName = $_FILES['fields']['name'][$fieldId];
                $fileTmp  = $_FILES['fields']['tmp_name'][$fieldId];
                $fileSize = $_FILES['fields']['size'][$fieldId];
                $fileType = $_FILES['fields']['type'][$fieldId] ?? 'application/octet-stream';

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $safeName = 'form_' . $idSubmission . '_' . $fieldId . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $safeName;

                if (move_uploaded_file($fileTmp, $dest)) {
                    $fileUrl = URL . 'uploads/form_attachments/' . $safeName;
                    $submissionModel->addAttachment($idSubmission, $fieldId, $fileName, $fileUrl, $fileType, $fileSize);
                    $value = $fileUrl;
                }
            }

            $submissionModel->addValue($idSubmission, $fieldId, $value);
        }

        echo json_encode([
            'success'           => true,
            'message'           => 'Formulario enviado exitosamente.',
            'submission_number' => $result['submission_number'],
        ]);
        exit;
    }

    public function getSubmission(){
        header('Content-Type: application/json');
        $idUser = $this->getClientUser();
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new FormSubmission();
        $submission = $model->getById($id);

        if (!$submission) {
            echo json_encode(['success' => false, 'message' => 'Envío no encontrado.']);
            exit;
        }

        if ($submission['id_user'] != $idUser && ($_SESSION['role_id'] ?? 0) < 3) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver este envío.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $submission]);
        exit;
    }

    public function mySubmissions(){
        header('Content-Type: application/json');
        $idUser = $this->getClientUser();

        $model = new FormSubmission();
        $submissions = $model->getByUser($idUser);

        echo json_encode(['success' => true, 'data' => $submissions]);
        exit;
    }

    public function downloadPdf(){
        $idUser = $this->getClientUser();
        $idFormType = intval($_GET['id_form_type'] ?? 0);

        if (!$idFormType) {
            header('Location: ?url=cliente/form');
            exit;
        }

        require_once ROOT_PATH . '/app/helpers/FormPdfHelper.php';

        $formTypeModel = new FormType();
        $formData = $formTypeModel->getForPdf($idFormType);

        if (!$formData) {
            header('Location: ?url=cliente/form');
            exit;
        }

        FormPdfHelper::generateBlankPdf($formData['form_type'], $formData['structure']);
        exit;
    }

    public function previewPdf(){
        $idUser = $this->getClientUser();
        $idFormType = intval($_GET['id_form_type'] ?? 0);

        if (!$idFormType) {
            header('Location: ?url=cliente/form');
            exit;
        }

        require_once ROOT_PATH . '/app/helpers/FormPdfHelper.php';

        $formTypeModel = new FormType();
        $formData = $formTypeModel->getForPdf($idFormType);

        if (!$formData) {
            header('Location: ?url=cliente/form');
            exit;
        }

        FormPdfHelper::previewHtml($formData['form_type'], $formData['structure']);
        exit;
    }

    public function adminList(){
        Auth::requirePermissionAjax('formularios', 'ver');
        header('Content-Type: application/json');

        $model = new FormSubmission();
        $submissions = $model->getAllWithDetails();

        echo json_encode(['success' => true, 'data' => $submissions]);
        exit;
    }

    public function adminDetail(){
        Auth::requirePermissionAjax('formularios', 'ver');
        header('Content-Type: application/json');

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new FormSubmission();
        $submission = $model->getById($id);

        if (!$submission) {
            echo json_encode(['success' => false, 'message' => 'Envío no encontrado.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $submission]);
        exit;
    }

    public function adminUpdateStatus(){
        Auth::requirePermissionAjax('formularios', 'editar');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? $_POST['id'] ?? 0);
        $status = $body['status'] ?? $_POST['status'] ?? '';
        $notes  = $body['review_notes'] ?? $_POST['review_notes'] ?? null;

        if (!$id || !in_array($status, ['Pendiente', 'Enviado', 'Revisado', 'Rechazado'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        $model = new FormSubmission();
        $result = $model->updateStatus($id, $status, $notes, $_SESSION['id_user'] ?? null);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado.']);
        }
        exit;
    }
}
