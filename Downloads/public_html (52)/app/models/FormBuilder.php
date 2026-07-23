<?php

require_once ROOT_PATH . '/app/config/database.php';

class FormBuilder {

    private $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getFieldTypes(){
        $stmt = $this->conn->prepare("SELECT * FROM form_field_type WHERE status = 1 ORDER BY id_form_field_type");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVersion($versionId){
        $stmt = $this->conn->prepare("
            SELECT v.*, t.name AS form_name, t.form_key
            FROM form_version v
            INNER JOIN form_type t ON t.id_form_type = v.id_form_type
            WHERE v.id_form_version = :id LIMIT 1
        ");
        $stmt->execute([':id' => $versionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Devuelve la estructura completa (secciones -> campos -> opciones/validaciones) de una versión */
    public function getStructure($versionId){
        $sections = $this->conn->prepare("
            SELECT * FROM form_section WHERE id_form_version = :id ORDER BY id_form_section
        ");
        $sections->execute([':id' => $versionId]);
        $sections = $sections->fetchAll(PDO::FETCH_ASSOC);

        $fieldsStmt = $this->conn->prepare("
            SELECT f.*, ft.name AS field_type_name
            FROM form_field f
            INNER JOIN form_field_type ft ON ft.id_form_field_type = f.id_form_field_type
            WHERE f.id_form_section = :id
            ORDER BY f.id_form_field
        ");
        $optStmt = $this->conn->prepare("
            SELECT * FROM form_field_option WHERE id_form_field = :id ORDER BY id_form_field_option
        ");
        $valStmt = $this->conn->prepare("
            SELECT * FROM form_field_validation WHERE id_form_field = :id
        ");

        foreach ($sections as &$section) {
            $fieldsStmt->execute([':id' => $section['id_form_section']]);
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fields as &$field) {
                $optStmt->execute([':id' => $field['id_form_field']]);
                $field['options'] = $optStmt->fetchAll(PDO::FETCH_ASSOC);

                $valStmt->execute([':id' => $field['id_form_field']]);
                $field['validations'] = $valStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($field);
            $section['fields'] = $fields;
        }
        unset($section);

        return $sections;
    }

    /** Devuelve todas las versiones de todos los formularios, con nombre del tipo */
    public function getAllVersions(){
        $query = "
            SELECT
                v.*,
                t.name AS form_name,
                t.id_form_type,
                (ft2.id_current_version = v.id_form_version) AS is_current
            FROM form_version v
            INNER JOIN form_type t ON t.id_form_type = v.id_form_type
            LEFT JOIN form_type ft2 ON ft2.id_form_type = t.id_form_type
            ORDER BY t.name ASC, v.version_number DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reemplaza toda la estructura (secciones, campos, opciones, validaciones) de una
     * versión de formulario a partir del árbol enviado por el constructor visual.
     * Estrategia simple y robusta: borra lo existente de la versión y vuelve a insertar.
     */
    public function saveStructure($versionId, $formTypeId, array $sections){
        try {
            $this->conn->beginTransaction();

            // Borrar estructura anterior de esta versión (en cascada manual)
            $oldFields = $this->conn->prepare("SELECT id_form_field FROM form_field WHERE id_form_version = :id");
            $oldFields->execute([':id' => $versionId]);
            $oldFieldIds = array_column($oldFields->fetchAll(PDO::FETCH_ASSOC), 'id_form_field');

            if (!empty($oldFieldIds)) {
                $in = implode(',', array_fill(0, count($oldFieldIds), '?'));
                $this->conn->prepare("DELETE FROM form_field_option WHERE id_form_field IN ($in)")->execute($oldFieldIds);
                $this->conn->prepare("DELETE FROM form_field_validation WHERE id_form_field IN ($in)")->execute($oldFieldIds);
            }
            $this->conn->prepare("DELETE FROM form_field WHERE id_form_version = :id")->execute([':id' => $versionId]);
            $this->conn->prepare("DELETE FROM form_section WHERE id_form_version = :id")->execute([':id' => $versionId]);

            $sectionStmt = $this->conn->prepare("
                INSERT INTO form_section (id_form_version, section_name, section_description, is_repeatable, sort_order)
                VALUES (:vid, :name, :desc, :repeatable, :sort_order)
            ");
            $fieldStmt = $this->conn->prepare("
                INSERT INTO form_field
                    (id_form_field_type, id_form_type, id_form_version, id_form_section, name, label, placeholder, help_text, default_value, is_required, is_visible, allowed_file_types, sort_order)
                VALUES
                    (:type_id, :form_type_id, :vid, :section_id, :name, :label, :placeholder, :help, :default, :required, :visible, :file_types, :sort)
            ");
            $optionStmt = $this->conn->prepare("
                INSERT INTO form_field_option (id_form_field, option_label, option_value, sort_order)
                VALUES (:field_id, :label, :value, :sort_order)
            ");
            $validationStmt = $this->conn->prepare("
                INSERT INTO form_field_validation (id_form_field, validation_type, validation_value, error_message)
                VALUES (:field_id, :vtype, :vvalue, :verror)
            ");

            $sectionSort = 0;
            foreach ($sections as $section) {
                $sectionStmt->execute([
                    ':vid'        => $versionId,
                    ':name'       => trim($section['section_name'] ?? 'Seccion'),
                    ':desc'       => $section['section_description'] ?? null,
                    ':repeatable' => !empty($section['is_repeatable']) ? 1 : 0,
                    ':sort_order' => $sectionSort++,
                ]);
                $sectionId = (int) $this->conn->lastInsertId();

                $fieldSort = 0;
                foreach (($section['fields'] ?? []) as $field) {
                    $fieldStmt->execute([
                        ':type_id'      => (int) $field['id_form_field_type'],
                        ':form_type_id' => $formTypeId,
                        ':vid'          => $versionId,
                        ':section_id'   => $sectionId,
                        ':name'         => trim($field['name'] ?? ('campo_' . ($fieldSort + 1))),
                        ':label'        => trim($field['label'] ?? ''),
                        ':placeholder'  => $field['placeholder'] ?? null,
                        ':help'         => $field['help_text'] ?? null,
                        ':default'      => $field['default_value'] ?? null,
                        ':required'     => !empty($field['is_required']) ? 1 : 0,
                        ':visible'      => array_key_exists('is_visible', $field) ? (!empty($field['is_visible']) ? 1 : 0) : 1,
                        ':file_types'   => $field['allowed_file_types'] ?? null,
                        ':sort'         => $fieldSort++,
                    ]);
                    $fieldId = (int) $this->conn->lastInsertId();

                    $optSort = 0;
                    foreach (($field['options'] ?? []) as $opt) {
                        if (trim($opt['option_label'] ?? '') === '') continue;
                        $optionStmt->execute([
                            ':field_id'  => $fieldId,
                            ':label'     => trim($opt['option_label']),
                            ':value'     => $opt['option_value'] ?? trim($opt['option_label']),
                            ':sort_order' => $optSort++,
                        ]);
                    }

                    if (!empty($field['is_required'])) {
                        $validationStmt->execute([
                            ':field_id' => $fieldId,
                            ':vtype'    => 'required',
                            ':vvalue'   => '1',
                            ':verror'   => 'Este campo es obligatorio.',
                        ]);
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /** Crea una nueva versión de un formulario, copiando o partiendo de cero */
    public function createNewVersion($formTypeId, $userId = null, $description = null){
        $vStmt = $this->conn->prepare("SELECT MAX(version_number) AS maxv FROM form_version WHERE id_form_type = :id");
        $vStmt->execute([':id' => $formTypeId]);
        $next = ((int) ($vStmt->fetch(PDO::FETCH_ASSOC)['maxv'] ?? 0)) + 1;

        $stmt = $this->conn->prepare("
            INSERT INTO form_version (id_form_type, version_number, description, status, created_by)
            VALUES (:id_form_type, :vnum, :desc, 1, :created_by)
        ");
        $stmt->execute([
            ':id_form_type' => $formTypeId,
            ':vnum'         => $next,
            ':desc'         => $description ?: "Versión {$next}",
            ':created_by'   => $userId,
        ]);
        $versionId = (int) $this->conn->lastInsertId();

        $upd = $this->conn->prepare("UPDATE form_type SET id_current_version = :vid, updated_at = CURRENT_TIMESTAMP WHERE id_form_type = :id");
        $upd->execute([':vid' => $versionId, ':id' => $formTypeId]);

        return $versionId;
    }
}