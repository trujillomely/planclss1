<?php

require_once ROOT_PATH . '/app/models/FormType.php';

class FormTypeController {

    public function getById(){
        Auth::requirePermissionAjax('formularios', 'ver');
        header('Content-Type: application/json');

        $model  = new FormType();
        $result = $model->findById(intval($_GET['id'] ?? 0));

        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado.']);
        }
        exit;
    }

    // Ruta: admin/form-types/store  (crea el tipo + su primera versión)
    public function store(){
        Auth::requirePermissionAjax('formularios', 'crear');
        header('Content-Type: application/json');

        $data = $_POST;

        if (empty(trim($data['name'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'El nombre del formulario es obligatorio.']);
            exit;
        }
        if (empty(trim($data['form_key'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'La clave (form_key) del formulario es obligatoria.']);
            exit;
        }

        $key = preg_replace('/[^a-z0-9_\-]/', '', strtolower(str_replace(' ', '_', trim($data['form_key']))));
        $data['form_key'] = $key;

        $model = new FormType();
        if ($model->keyExists($key)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un formulario con esa clave.']);
            exit;
        }

        $userId = $_SESSION['id_user'] ?? null;
        $result = $model->createWithFirstVersion($data, $userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Formulario creado exitosamente.', 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el formulario.']);
        }
        exit;
    }

    // Ruta: admin/form-types/update
    public function update(){
        Auth::requirePermissionAjax('formularios', 'editar');
        header('Content-Type: application/json');

        $data = $_POST;
        $id   = intval($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de formulario inválido.']);
            exit;
        }
        if (empty(trim($data['name'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'El nombre del formulario es obligatorio.']);
            exit;
        }

        $model = new FormType();
        if (!$model->findById($id)) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado.']);
            exit;
        }

        $key = preg_replace('/[^a-z0-9_\-]/', '', strtolower(str_replace(' ', '_', trim($data['form_key'] ?? ''))));
        $data['form_key'] = $key;

        if ($key !== '' && $model->keyExists($key, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro formulario con esa clave.']);
            exit;
        }

        $result = $model->update($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Formulario actualizado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el formulario.']);
        }
        exit;
    }

    // Ruta: admin/form-types/delete (desactiva, no elimina físicamente)
    public function delete(){
        Auth::requirePermissionAjax('formularios', 'eliminar');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $id   = intval($json['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new FormType();
        if (!$model->findById($id)) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado.']);
            exit;
        }

        $submissionsCount = $model->countSubmissions($id);
        if ($submissionsCount > 0 && empty($json['force']) && empty($_POST['force'])) {
            echo json_encode([
                'success' => false,
                'message' => "Este formulario tiene {$submissionsCount} envío(s) asociado(s). ¿Deseas desactivarlo de todas formas?",
                'requires_confirmation' => true,
            ]);
            exit;
        }

        $result = $model->deactivate($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Formulario desactivado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al desactivar el formulario.']);
        }
        exit;
    }

    // Ruta: admin/form-types/reactivate
    public function reactivate(){
        Auth::requirePermissionAjax('formularios', 'editar');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $id   = intval($json['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new FormType();
        if (!$model->findById($id)) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado.']);
            exit;
        }

        $result = $model->activate($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Formulario reactivado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al reactivar el formulario.']);
        }
        exit;
    }

    // Ruta: admin/form-types/store-from-template
    public function storeFromTemplate(){
        Auth::requirePermissionAjax('formularios', 'crear');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $templateKey = $json['template_key'] ?? $_POST['template_key'] ?? '';

        $templates = [
            'poliza' => [
                'name' => 'Solicitud de Póliza',
                'form_key' => 'solicitud_poliza',
                'category' => 'Pólizas',
                'description' => 'Formulario de solicitud de póliza de seguros',
                'sections' => [
                    [
                        'section_name' => 'Datos del Asegurado',
                        'section_description' => 'Información personal del solicitante',
                        'fields' => [
                            ['name' => 'nombre', 'label' => 'Nombre completo', 'type_key' => 'text', 'required' => true, 'placeholder' => 'Ingrese su nombre completo'],
                            ['name' => 'email', 'label' => 'Correo electrónico', 'type_key' => 'email', 'required' => true, 'placeholder' => 'correo@ejemplo.com'],
                            ['name' => 'telefono', 'label' => 'Teléfono', 'type_key' => 'phone', 'required' => true, 'placeholder' => '0000-0000'],
                            ['name' => 'fecha_nacimiento', 'label' => 'Fecha de nacimiento', 'type_key' => 'date', 'required' => true],
                            ['name' => 'direccion', 'label' => 'Dirección', 'type_key' => 'textarea', 'required' => false, 'placeholder' => 'Dirección completa'],
                        ],
                    ],
                    [
                        'section_name' => 'Detalles de Cobertura',
                        'section_description' => 'Información sobre el seguro solicitado',
                        'fields' => [
                            ['name' => 'tipo_seguro', 'label' => 'Tipo de seguro', 'type_key' => 'select', 'required' => true, 'options' => [['option_label'=>'Auto','option_value'=>'auto'],['option_label'=>'Vida','option_value'=>'vida'],['option_label'=>'Hogar','option_value'=>'hogar'],['option_label'=>'Comercial','option_value'=>'comercial']]],
                            ['name' => 'monto_asegurado', 'label' => 'Monto asegurado', 'type_key' => 'number', 'required' => true, 'placeholder' => '0.00'],
                            ['name' => 'deducible', 'label' => 'Deducible', 'type_key' => 'number', 'required' => false, 'placeholder' => '0.00'],
                        ],
                    ],
                    [
                        'section_name' => 'Información Adicional',
                        'section_description' => 'Comentarios u observaciones',
                        'fields' => [
                            ['name' => 'observaciones', 'label' => 'Observaciones', 'type_key' => 'textarea', 'required' => false, 'placeholder' => 'Información adicional relevante'],
                        ],
                    ],
                ],
            ],
            'reclamo' => [
                'name' => 'Reclamo de Seguro',
                'form_key' => 'reclamo_seguro',
                'category' => 'Reclamos',
                'description' => 'Formulario para reportar y gestionar reclamos de seguros',
                'sections' => [
                    [
                        'section_name' => 'Datos del Reclamante',
                        'section_description' => 'Información del titular del siniestro',
                        'fields' => [
                            ['name' => 'nombre', 'label' => 'Nombre completo', 'type_key' => 'text', 'required' => true, 'placeholder' => 'Ingrese su nombre completo'],
                            ['name' => 'email', 'label' => 'Correo electrónico', 'type_key' => 'email', 'required' => true, 'placeholder' => 'correo@ejemplo.com'],
                            ['name' => 'telefono', 'label' => 'Teléfono', 'type_key' => 'phone', 'required' => true, 'placeholder' => '0000-0000'],
                            ['name' => 'numero_poliza', 'label' => 'Número de póliza', 'type_key' => 'text', 'required' => true, 'placeholder' => 'Ej. POL-000123'],
                        ],
                    ],
                    [
                        'section_name' => 'Detalles del Reclamo',
                        'section_description' => 'Descripción del incidente',
                        'fields' => [
                            ['name' => 'fecha_incidente', 'label' => 'Fecha del incidente', 'type_key' => 'date', 'required' => true],
                            ['name' => 'tipo_reclamo', 'label' => 'Tipo de reclamo', 'type_key' => 'select', 'required' => true, 'options' => [['option_label'=>'Daños','option_value'=>'danos'],['option_label'=>'Robo','option_value'=>'robo'],['option_label'=>'Accidente','option_value'=>'accidente'],['option_label'=>'Tercero','option_value'=>'tercero']]],
                            ['name' => 'descripcion_incidente', 'label' => 'Descripción del incidente', 'type_key' => 'textarea', 'required' => true, 'placeholder' => 'Describa lo ocurrido con detalle'],
                            ['name' => 'monto_reclamado', 'label' => 'Monto reclamado', 'type_key' => 'number', 'required' => true, 'placeholder' => '0.00'],
                        ],
                    ],
                    [
                        'section_name' => 'Documentos Soporte',
                        'section_description' => 'Evidencia del siniestro',
                        'fields' => [
                            ['name' => 'evidencia_foto', 'label' => 'Evidencia fotográfica', 'type_key' => 'file', 'required' => false],
                        ],
                    ],
                ],
            ],
            'actualizacion' => [
                'name' => 'Actualización de Datos',
                'form_key' => 'actualizacion_datos',
                'category' => 'General',
                'description' => 'Solicitud de actualización de datos personales',
                'sections' => [
                    [
                        'section_name' => 'Datos Personales',
                        'section_description' => 'Información actual del usuario',
                        'fields' => [
                            ['name' => 'nombre', 'label' => 'Nombre completo', 'type_key' => 'text', 'required' => true, 'placeholder' => 'Ingrese su nombre completo'],
                            ['name' => 'email', 'label' => 'Correo electrónico', 'type_key' => 'email', 'required' => true, 'placeholder' => 'correo@ejemplo.com'],
                            ['name' => 'telefono', 'label' => 'Teléfono', 'type_key' => 'phone', 'required' => true, 'placeholder' => '0000-0000'],
                            ['name' => 'direccion_nueva', 'label' => 'Dirección actualizada', 'type_key' => 'textarea', 'required' => false, 'placeholder' => 'Nueva dirección'],
                        ],
                    ],
                    [
                        'section_name' => 'Cambios Solicitados',
                        'section_description' => 'Detalles de la actualización',
                        'fields' => [
                            ['name' => 'campo_a_modificar', 'label' => 'Campo a modificar', 'type_key' => 'select', 'required' => true, 'options' => [['option_label'=>'Teléfono','option_value'=>'telefono'],['option_label'=>'Dirección','option_value'=>'direccion'],['option_label'=>'Email','option_value'=>'email'],['option_label'=>'Otro','option_value'=>'otro']]],
                            ['name' => 'nuevo_valor', 'label' => 'Nuevo valor', 'type_key' => 'text', 'required' => true, 'placeholder' => 'Ingrese el nuevo valor'],
                        ],
                    ],
                ],
            ],
        ];

        if (!isset($templates[$templateKey])) {
            echo json_encode(['success' => false, 'message' => 'Plantilla no válida.']);
            exit;
        }

        $tpl = $templates[$templateKey];
        $model = new FormType();
        if ($model->keyExists($tpl['form_key'])) {
            $tpl['form_key'] = $tpl['form_key'] . '_' . time();
        }

        $userId = $_SESSION['id_user'] ?? null;
        $result = $model->createWithFirstVersion([
            'name' => $tpl['name'],
            'description' => $tpl['description'],
            'form_key' => $tpl['form_key'],
            'category' => $tpl['category'],
            'allow_download' => 1,
            'allow_digital_fill' => 1,
        ], $userId);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Error al crear el formulario desde plantilla.']);
            exit;
        }

        $formTypeId = $result['id_form_type'];
        $versionId = $result['id_form_version'];

        require_once ROOT_PATH . '/app/models/FormBuilder.php';
        $builder = new FormBuilder();

        $typeMap = [];
        foreach ($builder->getFieldTypes() as $ft) {
            $typeMap[$ft['field_key']] = (int) $ft['id_form_field_type'];
        }

        $resolvedSections = [];
        foreach ($tpl['sections'] as $section) {
            $resolvedFields = [];
            foreach (($section['fields'] ?? []) as $field) {
                $resolvedFields[] = [
                    'id_form_field_type' => $typeMap[$field['type_key']] ?? 1,
                    'name'               => $field['name'] ?? '',
                    'label'              => $field['label'] ?? '',
                    'placeholder'        => $field['placeholder'] ?? null,
                    'help_text'          => $field['help_text'] ?? null,
                    'default_value'      => $field['default_value'] ?? null,
                    'is_required'        => !empty($field['required']),
                    'is_visible'         => true,
                    'allowed_file_types' => $field['allowed_file_types'] ?? null,
                    'options'            => $field['options'] ?? [],
                ];
            }
            $resolvedSections[] = [
                'section_name'        => $section['section_name'] ?? '',
                'section_description' => $section['section_description'] ?? null,
                'is_repeatable'       => $section['is_repeatable'] ?? false,
                'fields'              => $resolvedFields,
            ];
        }

        $builder->saveStructure($versionId, $formTypeId, $resolvedSections);

        echo json_encode(['success' => true, 'message' => 'Formulario creado desde plantilla.', 'data' => ['id_form_type' => $formTypeId]]);
        exit;
    }

    // Ruta: admin/form-types/duplicate
    public function duplicate(){
        Auth::requirePermissionAjax('formularios', 'crear');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $id = intval($json['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new FormType();
        $original = $model->findById($id);
        if (!$original) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado.']);
            exit;
        }

        $newName = $original['name'] . ' (Copia)';
        $newKey = preg_replace('/[^a-z0-9_\-]/', '', strtolower(str_replace(' ', '_', trim($newName))));
        if ($model->keyExists($newKey)) {
            $newKey = $newKey . '_' . time();
        }

        $userId = $_SESSION['id_user'] ?? null;
        $result = $model->createWithFirstVersion([
            'name' => $newName,
            'description' => $original['description'] ?? null,
            'form_key' => $newKey,
            'category' => $original['category'] ?? null,
            'allow_download' => $original['allow_download'] ?? 1,
            'allow_digital_fill' => $original['allow_digital_fill'] ?? 1,
        ], $userId);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Error al duplicar el formulario.']);
            exit;
        }

        $newFormTypeId = $result['id_form_type'];
        $newVersionId = $result['id_form_version'];

        if (!empty($original['id_current_version'])) {
            try {
                require_once ROOT_PATH . '/app/models/FormBuilder.php';
                $builder = new FormBuilder();
                $structure = $builder->getStructure($original['id_current_version']);

                if (!empty($structure)) {
                    $builder->saveStructure($newVersionId, $newFormTypeId, $structure);
                }
            } catch (Exception $e) {
                error_log('duplicate form structure: ' . $e->getMessage());
            }
        }

        echo json_encode(['success' => true, 'message' => 'Formulario duplicado correctamente.', 'data' => ['id_form_type' => $newFormTypeId]]);
        exit;
    }
}