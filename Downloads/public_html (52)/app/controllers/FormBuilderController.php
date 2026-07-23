<?php

require_once ROOT_PATH . '/app/models/FormBuilder.php';
require_once ROOT_PATH . '/app/models/FormType.php';

class FormBuilderController {

    // Ruta: admin/form-builder/save-structure
    // Recibe (JSON): { id_form_type, id_form_version, sections: [ { section_name, section_description, is_repeatable, fields: [ {...} ] } ] }
    public function saveStructure(){
        Auth::requirePermissionAjax('formularios', 'editar');
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        $formTypeId = intval($body['id_form_type'] ?? 0);
        $versionId  = intval($body['id_form_version'] ?? 0);
        $sections   = $body['sections'] ?? [];

        if (!$formTypeId || !$versionId) {
            echo json_encode(['success' => false, 'message' => 'Selecciona un formulario válido antes de guardar.']);
            exit;
        }

        $formTypeModel = new FormType();
        if (!$formTypeModel->findById($formTypeId)) {
            echo json_encode(['success' => false, 'message' => 'El formulario no existe.']);
            exit;
        }

        if (empty($sections)) {
            echo json_encode(['success' => false, 'message' => 'Agrega al menos una sección con un campo antes de guardar.']);
            exit;
        }

        // Validación básica: cada sección debe tener al menos un campo con tipo y etiqueta
        foreach ($sections as $section) {
            if (empty(trim($section['section_name'] ?? ''))) {
                echo json_encode(['success' => false, 'message' => 'Todas las secciones deben tener un nombre.']);
                exit;
            }
            foreach (($section['fields'] ?? []) as $field) {
                if (empty($field['id_form_field_type']) || trim($field['label'] ?? '') === '') {
                    echo json_encode(['success' => false, 'message' => 'Todos los campos deben tener tipo y etiqueta.']);
                    exit;
                }
            }
        }

        $model  = new FormBuilder();
        $result = $model->saveStructure($versionId, $formTypeId, $sections);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Formulario guardado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al guardar la estructura del formulario.']);
        }
        exit;
    }

    // Ruta: admin/form-builder/get-structure?id=<versionId>
    public function getStructure(){
        Auth::requirePermissionAjax('formularios', 'ver');
        header('Content-Type: application/json');

        $versionId = intval($_GET['id'] ?? 0);
        if (!$versionId) {
            echo json_encode(['success' => false, 'message' => 'ID de versión inválido.']);
            exit;
        }

        $model = new FormBuilder();
        $structure = $model->getStructure($versionId);

        echo json_encode(['success' => true, 'data' => $structure]);
        exit;
    }
}
