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

                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
                $allowedMimeTypes = [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => "Extensión no permitida: {$ext}. Archivos permitidos: " . implode(', ', $allowedExtensions) . "."]);
                    exit;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);

                if (!in_array($realMimeType, $allowedMimeTypes)) {
                    echo json_encode(['success' => false, 'message' => "Tipo de archivo no permitido: {$realMimeType}."]);
                    exit;
                }

                $maxFileSize = 10 * 1024 * 1024;
                if ($fileSize > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo de 10MB.']);
                    exit;
                }

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

        $this->sendAdminNotification($formType['name'], $result['submission_number'], $idUser);
        $this->sendUserConfirmation($formType['name'], $result['submission_number'], $idUser);

        exit;
    }

    private function sendAdminNotification($formName, $submissionNumber, $userId){
        try {
            require_once ROOT_PATH . '/app/config/database.php';
            require_once ROOT_PATH . '/app/models/User.php';

            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT name, email FROM user WHERE role_id >= 3 AND status = 1");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($admins)) return;

            $userModel = new User();
            $user = $userModel->findById($userId);
            $userName = $user ? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) : 'Usuario #' . $userId;

            $baseUrl = URL;
            $viewUrl = $baseUrl . 'admin/form-submissions';
            $fecha = date('d/m/Y H:i');

            $htmlBody = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">';
            $htmlBody .= '<div style="max-width:600px;margin:30px auto;background-color:#ffffff;border-radius:8px;overflow:hidden;">';
            $htmlBody .= '<div style="background-color:#1a1a2e;padding:24px 30px;text-align:center;">';
            $htmlBody .= '<h1 style="margin:0;color:#ffffff;font-size:20px;letter-spacing:3px;">ARCO SEGUROS</h1>';
            $htmlBody .= '</div>';
            $htmlBody .= '<div style="padding:30px;text-align:center;">';
            $htmlBody .= '<div style="width:60px;height:60px;border-radius:50%;background-color:#C0392B;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;">';
            $htmlBody .= '<span style="color:#ffffff;font-size:28px;">&#9993;</span>';
            $htmlBody .= '</div>';
            $htmlBody .= '<h2 style="margin:0 0 10px;color:#1a1a2e;font-size:18px;">Nuevo env&iacute;o de formulario</h2>';
            $htmlBody .= '<p style="color:#666;font-size:14px;margin:0 0 24px;">Se ha recibido un nuevo env&iacute;o en el sistema.</p>';
            $htmlBody .= '<table style="width:100%;border-collapse:collapse;text-align:left;margin-bottom:24px;">';
            $htmlBody .= '<tr><td style="padding:10px 14px;background:#f9f9f9;border-radius:6px 0 0 6px;font-weight:bold;color:#555;font-size:13px;width:40%;">Formulario</td>';
            $htmlBody .= '<td style="padding:10px 14px;background:#f9f9f9;border-radius:0 6px 6px 0;color:#1a1a2e;font-size:13px;">' . htmlspecialchars($formName) . '</td></tr>';
            $htmlBody .= '<tr><td style="padding:10px 14px;font-weight:bold;color:#555;font-size:13px;">N&uacute;mero de env&iacute;o</td>';
            $htmlBody .= '<td style="padding:10px 14px;color:#1a1a2e;font-size:13px;">' . htmlspecialchars($submissionNumber) . '</td></tr>';
            $htmlBody .= '<tr><td style="padding:10px 14px;background:#f9f9f9;border-radius:6px 0 0 6px;font-weight:bold;color:#555;font-size:13px;">Usuario</td>';
            $htmlBody .= '<td style="padding:10px 14px;background:#f9f9f9;border-radius:0 6px 6px 0;color:#1a1a2e;font-size:13px;">' . htmlspecialchars($userName) . '</td></tr>';
            $htmlBody .= '<tr><td style="padding:10px 14px;font-weight:bold;color:#555;font-size:13px;">Fecha</td>';
            $htmlBody .= '<td style="padding:10px 14px;color:#1a1a2e;font-size:13px;">' . $fecha . '</td></tr>';
            $htmlBody .= '</table>';
            $htmlBody .= '<a href="' . $viewUrl . '" style="display:inline-block;background-color:#C0392B;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:bold;font-size:14px;">Ver env&iacute;os</a>';
            $htmlBody .= '</div>';
            $htmlBody .= '<div style="background-color:#f4f4f4;padding:16px 30px;text-align:center;">';
            $htmlBody .= '<p style="margin:0;color:#999;font-size:11px;">Este es un correo autom&aacute;tico de Arco Seguros. No respondas directamente a este mensaje.</p>';
            $htmlBody .= '</div>';
            $htmlBody .= '</div></body></html>';

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Arco Seguros <noreply@arco-seguros.com>\r\n";

            foreach ($admins as $admin) {
                if (!empty($admin['email'])) {
                    mail($admin['email'], "Nuevo envío de formulario: {$formName}", $htmlBody, $headers);
                }
            }
        } catch (Exception $e) {
            error_log('sendAdminNotification error: ' . $e->getMessage());
        }
    }

    private function sendUserConfirmation($formName, $submissionNumber, $userId){
        try {
            require_once ROOT_PATH . '/app/models/User.php';

            $userModel = new User();
            $user = $userModel->findById($userId);
            if (!$user || empty($user['email'])) return;

            $userEmail = $user['email'];
            $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

            $htmlBody = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">';
            $htmlBody .= '<div style="max-width:600px;margin:30px auto;background-color:#ffffff;border-radius:8px;overflow:hidden;">';
            $htmlBody .= '<div style="background-color:#1a1a2e;padding:24px 30px;text-align:center;">';
            $htmlBody .= '<h1 style="margin:0;color:#ffffff;font-size:20px;letter-spacing:3px;">ARCO SEGUROS</h1>';
            $htmlBody .= '</div>';
            $htmlBody .= '<div style="padding:30px;text-align:center;">';
            $htmlBody .= '<div style="width:60px;height:60px;border-radius:50%;background-color:#27ae60;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;">';
            $htmlBody .= '<span style="color:#ffffff;font-size:28px;">&#10003;</span>';
            $htmlBody .= '</div>';
            $htmlBody .= '<h2 style="margin:0 0 10px;color:#1a1a2e;font-size:18px;">Confirmaci&oacute;n de env&iacute;o</h2>';
            $htmlBody .= '<p style="color:#666;font-size:14px;margin:0 0 24px;">Hola ' . htmlspecialchars($userName) . ', tu formulario ha sido recibido exitosamente.</p>';
            $htmlBody .= '<table style="width:100%;border-collapse:collapse;text-align:left;margin-bottom:24px;">';
            $htmlBody .= '<tr><td style="padding:10px 14px;background:#f9f9f9;border-radius:6px 0 0 6px;font-weight:bold;color:#555;font-size:13px;width:40%;">Formulario</td>';
            $htmlBody .= '<td style="padding:10px 14px;background:#f9f9f9;border-radius:0 6px 6px 0;color:#1a1a2e;font-size:13px;">' . htmlspecialchars($formName) . '</td></tr>';
            $htmlBody .= '<tr><td style="padding:10px 14px;font-weight:bold;color:#555;font-size:13px;">N&uacute;mero de env&iacute;o</td>';
            $htmlBody .= '<td style="padding:10px 14px;color:#1a1a2e;font-size:13px;">' . htmlspecialchars($submissionNumber) . '</td></tr>';
            $htmlBody .= '<tr><td style="padding:10px 14px;background:#f9f9f9;border-radius:6px 0 0 6px;font-weight:bold;color:#555;font-size:13px;">Fecha</td>';
            $htmlBody .= '<td style="padding:10px 14px;background:#f9f9f9;border-radius:0 6px 6px 0;color:#1a1a2e;font-size:13px;">' . date('d/m/Y H:i') . '</td></tr>';
            $htmlBody .= '</table>';
            $htmlBody .= '<p style="color:#666;font-size:13px;margin:0;">Guarda tu n&uacute;mero de env&iacute;o para futuras consultas.</p>';
            $htmlBody .= '</div>';
            $htmlBody .= '<div style="background-color:#f4f4f4;padding:16px 30px;text-align:center;">';
            $htmlBody .= '<p style="margin:0;color:#999;font-size:11px;">Este es un correo autom&aacute;tico de Arco Seguros. No respondas directamente a este mensaje.</p>';
            $htmlBody .= '</div>';
            $htmlBody .= '</div></body></html>';

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Arco Seguros <noreply@arco-seguros.com>\r\n";

            mail($userEmail, "Confirmación de envío - {$formName}", $htmlBody, $headers);
        } catch (Exception $e) {
            error_log('sendUserConfirmation error: ' . $e->getMessage());
        }
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

    public function downloadFilledPdf(){
        $idUser = $this->getClientUser();
        $idSubmission = intval($_GET['id'] ?? 0);

        if (!$idSubmission) {
            header('Location: ?url=cliente/form/submissions');
            exit;
        }

        $model = new FormSubmission();
        $submission = $model->getById($idSubmission);

        if (!$submission || $submission['id_user'] != $idUser) {
            header('Location: ?url=cliente/form/submissions');
            exit;
        }

        require_once ROOT_PATH . '/app/models/FormBuilder.php';
        $builderModel = new FormBuilder();
        $structure = $builderModel->getStructure($submission['id_form_version']);

        require_once ROOT_PATH . '/app/helpers/FormSubmissionPdfHelper.php';
        FormSubmissionPdfHelper::generate($submission, $structure);
        exit;
    }

    public function adminDownloadFilledPdf(){
        Auth::requirePermissionAjax('formularios', 'ver');
        $idSubmission = intval($_GET['id'] ?? 0);

        if (!$idSubmission) {
            header('Location: ?url=admin/form-submissions');
            exit;
        }

        $model = new FormSubmission();
        $submission = $model->getById($idSubmission);

        if (!$submission) {
            header('Location: ?url=admin/form-submissions');
            exit;
        }

        require_once ROOT_PATH . '/app/models/FormBuilder.php';
        $builderModel = new FormBuilder();
        $structure = $builderModel->getStructure($submission['id_form_version']);

        require_once ROOT_PATH . '/app/helpers/FormSubmissionPdfHelper.php';
        FormSubmissionPdfHelper::generate($submission, $structure);
        exit;
    }
}
