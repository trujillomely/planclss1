<?php

require_once ROOT_PATH . '/app/config/database.php';

class Policy {

    private $conn;
    private $table = 'policy';

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

   public function getAll(){

        $query = "
            SELECT
                p.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name,
                pr.full_name AS producer_name
    
            FROM policy p
    
            LEFT JOIN user u
                ON p.id_user = u.id
    
            LEFT JOIN insurance_company ic
                ON p.id_insurance_company = ic.id_insurance_company
    
            LEFT JOIN insurance_type it
                ON p.id_insurance_type = it.id_insurance_type

            LEFT JOIN producer pr
                ON p.id_producer = pr.id_producer
    
            ORDER BY p.id_policy DESC
        ";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todas las pólizas de un cliente (panel Cliente) */
    public function getByUser($idUser){

        $query = "
            SELECT
                p.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name
            FROM {$this->table} p
            LEFT JOIN user u ON p.id_user = u.id
            LEFT JOIN insurance_company ic ON p.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON p.id_insurance_type = it.id_insurance_type
            WHERE p.id_user = :id_user
            ORDER BY p.date_expiration ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $idUser, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCompany($idCompany){
        $query = "
            SELECT
                p.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                u.username AS client_name,
                u.lastname AS client_lastname,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name
            FROM {$this->table} p
            LEFT JOIN user u ON p.id_user = u.id
            LEFT JOIN insurance_company ic ON p.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON p.id_insurance_type = it.id_insurance_type
            WHERE p.id_insurance_company = :id_company
            ORDER BY p.date_expiration ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_company', $idCompany, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** ¿La póliza indicada pertenece al usuario indicado? (chequeo de propiedad para el panel Cliente) */
    public function belongsToUser($idPolicy, $idUser){
        $query = "SELECT * FROM {$this->table} WHERE id_policy = :id_policy AND id_user = :id_user LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_policy' => $idPolicy, ':id_user' => $idUser]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id){

        $query = "
            SELECT
                p.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name,
                pf.name AS payment_frequency_name,
                pr.full_name AS producer_name
            FROM {$this->table} p
            LEFT JOIN user u ON p.id_user = u.id
            LEFT JOIN insurance_company ic ON p.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON p.id_insurance_type = it.id_insurance_type
            LEFT JOIN payment_frequency pf ON p.id_payment_frequency = pf.id_payment_frequency
            LEFT JOIN producer pr ON p.id_producer = pr.id_producer
            WHERE p.id_policy = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** ¿Ya existe una póliza con ese número? (excluyendo opcionalmente un id) */
    public function policyNumberExists($policyNumber, $excludeId = null){
        $query = "SELECT COUNT(*) AS total FROM {$this->table} WHERE policy_number = :policy_number";
        if ($excludeId) $query .= " AND id_policy != :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':policy_number', $policyNumber);
        if ($excludeId) $stmt->bindValue(':id', $excludeId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    /** Cantidad de reclamos asociados a esta póliza (para advertir antes de cancelar) */
    public function countClaims($id){
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM claim WHERE id_policy = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data){
        $query = "
            INSERT INTO {$this->table}
                (id_user, id_producer, id_insurance_company, id_insurance_type, id_payment_frequency,
                 policy_number, date_start, date_expiration, coverage_summary,
                 total_coverage_amount, total_deductible_amount, total_premium_amount,
                 renewable, status)
            VALUES
                (:id_user, :id_producer, :id_insurance_company, :id_insurance_type, :id_payment_frequency,
                 :policy_number, :date_start, :date_expiration, :coverage_summary,
                 :total_coverage_amount, :total_deductible_amount, :total_premium_amount,
                 :renewable, :status)
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id_user'                  => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':id_producer'              => !empty($data['id_producer']) ? (int)$data['id_producer'] : null,
            ':id_insurance_company'     => !empty($data['id_insurance_company']) ? (int)$data['id_insurance_company'] : null,
            ':id_insurance_type'        => !empty($data['id_insurance_type']) ? (int)$data['id_insurance_type'] : null,
            ':id_payment_frequency'     => !empty($data['id_payment_frequency']) ? (int)$data['id_payment_frequency'] : null,
            ':policy_number'            => trim($data['policy_number']),
            ':date_start'               => !empty($data['date_start']) ? $data['date_start'] : null,
            ':date_expiration'          => !empty($data['date_expiration']) ? $data['date_expiration'] : null,
            ':coverage_summary'         => !empty($data['coverage_summary']) ? trim($data['coverage_summary']) : null,
            ':total_coverage_amount'    => $data['total_coverage_amount'] !== '' ? $data['total_coverage_amount'] : null,
            ':total_deductible_amount'  => $data['total_deductible_amount'] !== '' ? $data['total_deductible_amount'] : null,
            ':total_premium_amount'     => $data['total_premium_amount'] !== '' ? $data['total_premium_amount'] : null,
            ':renewable'                => !empty($data['renewable']) ? 1 : 0,
            ':status'                   => !empty($data['status']) ? $data['status'] : 'Activo',
        ]);
    }

    public function update($id, $data){
        $query = "
            UPDATE {$this->table}
            SET id_user = :id_user,
                id_producer = :id_producer,
                id_insurance_company = :id_insurance_company,
                id_insurance_type = :id_insurance_type,
                id_payment_frequency = :id_payment_frequency,
                policy_number = :policy_number,
                date_start = :date_start,
                date_expiration = :date_expiration,
                coverage_summary = :coverage_summary,
                total_coverage_amount = :total_coverage_amount,
                total_deductible_amount = :total_deductible_amount,
                total_premium_amount = :total_premium_amount,
                renewable = :renewable,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id_policy = :id
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'                       => $id,
            ':id_user'                  => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':id_producer'              => !empty($data['id_producer']) ? (int)$data['id_producer'] : null,
            ':id_insurance_company'     => !empty($data['id_insurance_company']) ? (int)$data['id_insurance_company'] : null,
            ':id_insurance_type'        => !empty($data['id_insurance_type']) ? (int)$data['id_insurance_type'] : null,
            ':id_payment_frequency'     => !empty($data['id_payment_frequency']) ? (int)$data['id_payment_frequency'] : null,
            ':policy_number'            => trim($data['policy_number']),
            ':date_start'               => !empty($data['date_start']) ? $data['date_start'] : null,
            ':date_expiration'          => !empty($data['date_expiration']) ? $data['date_expiration'] : null,
            ':coverage_summary'         => !empty($data['coverage_summary']) ? trim($data['coverage_summary']) : null,
            ':total_coverage_amount'    => $data['total_coverage_amount'] !== '' ? $data['total_coverage_amount'] : null,
            ':total_deductible_amount'  => $data['total_deductible_amount'] !== '' ? $data['total_deductible_amount'] : null,
            ':total_premium_amount'     => $data['total_premium_amount'] !== '' ? $data['total_premium_amount'] : null,
            ':renewable'                => !empty($data['renewable']) ? 1 : 0,
            ':status'                   => !empty($data['status']) ? $data['status'] : 'Activo',
        ]);
    }

    /** Cambia el estado de la póliza (Activo / Expirado / Cancelado) */
    public function changeStatus($id, $status){
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id_policy = :id");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    /** Pólizas renovables que vencen en los próximos $days días (calendario) */
    public function getUpcomingRenewals($days = 60){
        $query = "
            SELECT
                p.id_policy, p.policy_number, p.date_expiration, p.total_premium_amount,
                p.renewable, p.status,
                u.username, u.lastname, u.email,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name
            FROM {$this->table} p
            LEFT JOIN user u ON p.id_user = u.id
            LEFT JOIN insurance_company ic ON p.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON p.id_insurance_type = it.id_insurance_type
            WHERE p.renewable = 1
              AND p.status = 'Activo'
              AND p.date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY p.date_expiration ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Póliza con datos de firma digital, coberturas y beneficiarios (para PDF) */
    public function getWithSignature($idPolicy){
        $policy = $this->findById($idPolicy);
        if (!$policy) return null;

        try {
            $stmt = $this->conn->prepare("
                SELECT ds.*, u.username, u.lastname, u.email AS user_email
                FROM digital_signature ds
                JOIN user u ON ds.id_user = u.id
                WHERE ds.id_policy = :id_policy
                ORDER BY ds.signed_at DESC
                LIMIT 1
            ");
            $stmt->execute([':id_policy' => $idPolicy]);
            $policy['signature'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('Policy::getWithSignature signature query: ' . $e->getMessage());
            $policy['signature'] = null;
        }

        require_once ROOT_PATH . '/app/models/PolicyCoverage.php';
        require_once ROOT_PATH . '/app/models/PolicyBeneficiary.php';

        try {
            $coverageModel  = new PolicyCoverage();
            $policy['coverages']    = $coverageModel->getByPolicy($idPolicy);
        } catch (Exception $e) {
            error_log('Policy::getWithSignature coverages: ' . $e->getMessage());
            $policy['coverages'] = [];
        }

        try {
            $beneficiaryModel = new PolicyBeneficiary();
            $policy['beneficiaries'] = $beneficiaryModel->getByPolicy($idPolicy);
        } catch (Exception $e) {
            error_log('Policy::getWithSignature beneficiaries: ' . $e->getMessage());
            $policy['beneficiaries'] = [];
        }

        return $policy;
    }

    /** Actualizar estado de firma de la póliza */
    public function updateSignatureStatus($idPolicy, $status){
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET signature_status = :status, updated_at = CURRENT_TIMESTAMP WHERE id_policy = :id");
        return $stmt->execute([':id' => $idPolicy, ':status' => $status]);
    }
}