<?php

require_once ROOT_PATH . '/app/config/database.php';

class Claim {

    private $conn;
    private $table = 'claim';

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(){
        $query = "
            SELECT
                c.*,
                p.policy_number,
                u.username,
                u.lastname,
                ct.name AS claim_type
            FROM claim c
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            LEFT JOIN user u ON c.id_user = u.id
            LEFT JOIN claim_type ct ON c.id_claim_type = ct.id_claim_type
            ORDER BY c.id_claim DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todos los reclamos de un cliente (panel Cliente) */
    public function getByUser($idUser){
        $query = "
            SELECT
                c.*,
                p.policy_number,
                ct.name AS claim_type
            FROM {$this->table} c
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            LEFT JOIN claim_type ct ON c.id_claim_type = ct.id_claim_type
            WHERE c.id_user = :id_user
            ORDER BY c.date_claim DESC, c.id_claim DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $idUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCompany($idCompany){
        $query = "
            SELECT
                c.*,
                p.policy_number,
                ct.name AS claim_type
            FROM {$this->table} c
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            LEFT JOIN claim_type ct ON c.id_claim_type = ct.id_claim_type
            WHERE p.id_insurance_company = :id_company
            ORDER BY c.date_claim DESC, c.id_claim DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_company', $idCompany, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id){
        $query = "SELECT * FROM {$this->table} WHERE id_claim = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function claimNumberExists($claimNumber, $excludeId = null){
        $query = "SELECT COUNT(*) AS total FROM {$this->table} WHERE claim_number = :claim_number";
        if ($excludeId) $query .= " AND id_claim != :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':claim_number', $claimNumber);
        if ($excludeId) $stmt->bindValue(':id', $excludeId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    /** Cantidad de pagos ya asociados a este reclamo (para advertir antes de eliminar) */
    public function countPayments($id){
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM claim_payment WHERE id_claim = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data){
        $query = "
            INSERT INTO {$this->table}
                (id_policy, id_user, id_claim_type, claim_number, date_claim, description, amount_claimed, status, resolution_date, notes, rejection_reason)
            VALUES
                (:id_policy, :id_user, :id_claim_type, :claim_number, :date_claim, :description, :amount_claimed, :status, :resolution_date, :notes, :rejection_reason)
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id_policy'        => (int) $data['id_policy'],
            ':id_user'          => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':id_claim_type'    => !empty($data['id_claim_type']) ? (int)$data['id_claim_type'] : null,
            ':claim_number'     => trim($data['claim_number']),
            ':date_claim'       => !empty($data['date_claim']) ? $data['date_claim'] : date('Y-m-d'),
            ':description'      => !empty($data['description']) ? trim($data['description']) : null,
            ':amount_claimed'   => $data['amount_claimed'] !== '' ? $data['amount_claimed'] : 0,
            ':status'           => !empty($data['status']) ? $data['status'] : 'Pendiente',
            ':resolution_date'  => !empty($data['resolution_date']) ? $data['resolution_date'] : null,
            ':notes'            => !empty($data['notes']) ? trim($data['notes']) : null,
            ':rejection_reason' => !empty($data['rejection_reason']) ? trim($data['rejection_reason']) : null,
        ]);
    }

    public function update($id, $data){
        $query = "
            UPDATE {$this->table}
            SET id_policy = :id_policy,
                id_user = :id_user,
                id_claim_type = :id_claim_type,
                claim_number = :claim_number,
                date_claim = :date_claim,
                description = :description,
                amount_claimed = :amount_claimed,
                status = :status,
                resolution_date = :resolution_date,
                notes = :notes,
                rejection_reason = :rejection_reason
            WHERE id_claim = :id
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'               => $id,
            ':id_policy'        => (int) $data['id_policy'],
            ':id_user'          => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':id_claim_type'    => !empty($data['id_claim_type']) ? (int)$data['id_claim_type'] : null,
            ':claim_number'     => trim($data['claim_number']),
            ':date_claim'       => !empty($data['date_claim']) ? $data['date_claim'] : date('Y-m-d'),
            ':description'      => !empty($data['description']) ? trim($data['description']) : null,
            ':amount_claimed'   => $data['amount_claimed'] !== '' ? $data['amount_claimed'] : 0,
            ':status'           => !empty($data['status']) ? $data['status'] : 'Pendiente',
            ':resolution_date'  => !empty($data['resolution_date']) ? $data['resolution_date'] : null,
            ':notes'            => !empty($data['notes']) ? trim($data['notes']) : null,
            ':rejection_reason' => !empty($data['rejection_reason']) ? trim($data['rejection_reason']) : null,
        ]);
    }

    /** Cambia únicamente el estado del reclamo (Pendiente / Activo / Aprovado / Inactivo) */
    public function changeStatus($id, $status){
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status WHERE id_claim = :id");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_claim = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Reclamos activos/pendientes para mostrar en el calendario */
    public function getActiveForCalendar(){
        $query = "
            SELECT
                c.id_claim, c.claim_number, c.date_claim, c.status, c.amount_claimed,
                u.username, u.lastname,
                p.policy_number,
                ct.name AS claim_type
            FROM {$this->table} c
            LEFT JOIN user u ON c.id_user = u.id
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            LEFT JOIN claim_type ct ON c.id_claim_type = ct.id_claim_type
            WHERE c.status IN ('Pendiente', 'Activo')
            ORDER BY c.date_claim DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}