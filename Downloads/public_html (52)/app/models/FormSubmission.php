<?php

require_once ROOT_PATH . '/app/config/database.php';

class FormSubmission {

    private $conn;
    private $table = 'form_submission';

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($idVersion, $idUser, $status = 'Enviado'){
        $today = date('Ymd');
        $prefix = "ENV-{$today}-";

        $stmt = $this->conn->prepare("
            SELECT submission_number FROM {$this->table}
            WHERE submission_number LIKE :prefix
            ORDER BY id_form_submission DESC LIMIT 1
        ");
        $stmt->execute([':prefix' => $prefix . '%']);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($last) {
            $lastNum = (int) substr($last['submission_number'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        $submissionNumber = $prefix . $nextNum;

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (id_form_version, id_user, submission_number, status, submitted_at)
            VALUES (:version, :user, :number, :status, NOW())
        ");
        $stmt->execute([
            ':version' => $idVersion,
            ':user'    => $idUser,
            ':number'  => $submissionNumber,
            ':status'  => $status,
        ]);

        return [
            'id_form_submission'  => (int) $this->conn->lastInsertId(),
            'submission_number'   => $submissionNumber,
        ];
    }

    public function addValue($idSubmission, $idField, $value){
        $stmt = $this->conn->prepare("
            INSERT INTO form_submission_value (id_form_submission, id_form_field, field_value)
            VALUES (:submission, :field, :value)
        ");
        return $stmt->execute([
            ':submission' => $idSubmission,
            ':field'      => $idField,
            ':value'      => $value,
        ]);
    }

    public function addAttachment($idSubmission, $idField, $fileName, $fileUrl, $fileType, $fileSize){
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO form_attachment (id_form_submission, id_form_field, file_name, file_url, file_type, file_size)
                VALUES (:submission, :field, :name, :url, :type, :size)
            ");
            return $stmt->execute([
                ':submission' => $idSubmission,
                ':field'      => $idField,
                ':name'       => $fileName,
                ':url'        => $fileUrl,
                ':type'       => $fileType,
                ':size'       => $fileSize,
            ]);
        } catch (Exception $e) {
            error_log('[FORM-ATTACHMENT] Error: ' . $e->getMessage());
            return false;
        }
    }

    public function addHistory($idSubmission, $changedBy, $previousStatus, $newStatus, $action, $notes = null){
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO form_submission_history (id_form_submission, changed_by, previous_status, new_status, action, notes, changed_at)
                VALUES (:submission, :user, :prev, :new, :action, :notes, NOW())
            ");
            return $stmt->execute([
                ':submission' => $idSubmission,
                ':user'       => $changedBy,
                ':prev'       => $previousStatus,
                ':new'        => $newStatus,
                ':action'     => $action,
                ':notes'      => $notes,
            ]);
        } catch (Exception $e) {
            error_log('[FORM-HISTORY] Error: ' . $e->getMessage());
            return false;
        }
    }

    public function getById($idSubmission){
        $stmt = $this->conn->prepare("
            SELECT s.*, v.version_number, t.name AS form_name, t.form_key,
                   u.username, u.lastname, u.email AS user_email
            FROM {$this->table} s
            INNER JOIN form_version v ON v.id_form_version = s.id_form_version
            INNER JOIN form_type t ON t.id_form_type = v.id_form_type
            LEFT JOIN user u ON u.id = s.id_user
            WHERE s.id_form_submission = :id LIMIT 1
        ");
        $stmt->execute([':id' => $idSubmission]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) return null;

        $valStmt = $this->conn->prepare("
            SELECT sv.*, f.label AS field_label, f.name AS field_name, ft.name AS field_key
            FROM form_submission_value sv
            INNER JOIN form_field f ON f.id_form_field = sv.id_form_field
            INNER JOIN form_field_type ft ON ft.id_form_field_type = f.id_form_field_type
            WHERE sv.id_form_submission = :id
            ORDER BY f.sort_order
        ");
        $valStmt->execute([':id' => $idSubmission]);
        $submission['values'] = $valStmt->fetchAll(PDO::FETCH_ASSOC);

        return $submission;
    }

    public function getByUser($idUser){
        $stmt = $this->conn->prepare("
            SELECT s.*, v.version_number, t.name AS form_name, t.form_key
            FROM {$this->table} s
            INNER JOIN form_version v ON v.id_form_version = s.id_form_version
            INNER JOIN form_type t ON t.id_form_type = v.id_form_type
            WHERE s.id_user = :user
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([':user' => $idUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByVersion($idVersion){
        $stmt = $this->conn->prepare("
            SELECT s.*, u.username, u.lastname, u.email AS user_email
            FROM {$this->table} s
            LEFT JOIN user u ON u.id = s.id_user
            WHERE s.id_form_version = :version
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([':version' => $idVersion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByVersionAndUser($idVersion, $idUser){
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table}
            WHERE id_form_version = :version AND id_user = :user
            ORDER BY created_at DESC
        ");
        $stmt->execute([':version' => $idVersion, ':user' => $idUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($idSubmission, $status, $reviewNotes = null, $changedBy = null){
        $current = $this->getById($idSubmission);
        $previousStatus = $current ? $current['status'] : null;

        $reviewedAt = ($status !== 'Pendiente') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET status = :status, review_notes = :notes, reviewed_at = :reviewed_at
            WHERE id_form_submission = :id
        ");
        $result = $stmt->execute([
            ':status'     => $status,
            ':notes'      => $reviewNotes,
            ':reviewed_at' => $reviewedAt,
            ':id'         => $idSubmission,
        ]);

        if ($result && $previousStatus && $previousStatus !== $status) {
            $this->addHistory($idSubmission, $changedBy, $previousStatus, $status, 'cambio_estado', $reviewNotes);
        }

        return $result;
    }

    public function countByFormType($idFormType){
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total FROM {$this->table} s
            INNER JOIN form_version v ON v.id_form_version = s.id_form_version
            WHERE v.id_form_type = :id
        ");
        $stmt->execute([':id' => $idFormType]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countByStatus(){
        $stmt = $this->conn->prepare("
            SELECT status, COUNT(*) AS total FROM {$this->table} GROUP BY status
        ");
        $stmt->execute();
        $result = ['Pendiente' => 0, 'Enviado' => 0, 'Revisado' => 0, 'Rechazado' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['total'];
        }
        return $result;
    }

    public function getAllWithDetails(){
        $stmt = $this->conn->prepare("
            SELECT s.*, v.version_number, t.name AS form_name, t.form_key,
                   u.username, u.lastname, u.email AS user_email
            FROM {$this->table} s
            INNER JOIN form_version v ON v.id_form_version = s.id_form_version
            INNER JOIN form_type t ON t.id_form_type = v.id_form_type
            LEFT JOIN user u ON u.id = s.id_user
            ORDER BY s.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(){
        $total = $this->conn->query("SELECT COUNT(*) AS total FROM {$this->table}")->fetch(PDO::FETCH_ASSOC)['total'];
        $byStatus = $this->countByStatus();

        $today = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table} WHERE DATE(created_at) = :today");
        $stmt->execute([':today' => $today]);
        $todayCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'total'       => (int) $total,
            'pendientes'  => $byStatus['Pendiente'] + $byStatus['Enviado'],
            'revisados'   => $byStatus['Revisado'],
            'rechazados'  => $byStatus['Rechazado'],
            'hoy'         => $todayCount,
        ];
    }
}
