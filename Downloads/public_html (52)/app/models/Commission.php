<?php

require_once ROOT_PATH . '/app/config/database.php';

class Commission {

    private $conn;
    private $table = 'commission';

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(){
        $query = "
            SELECT
                c.*,
                pr.full_name AS producer_name,
                p.policy_number
            FROM {$this->table} c
            LEFT JOIN producer pr ON c.id_producer = pr.id_producer
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            ORDER BY c.generated_date DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCompany($idCompany){
        $query = "
            SELECT
                c.*,
                pr.full_name AS producer_name,
                p.policy_number
            FROM {$this->table} c
            LEFT JOIN producer pr ON c.id_producer = pr.id_producer
            LEFT JOIN policy p ON c.id_policy = p.id_policy
            WHERE p.id_insurance_company = :id_company
            ORDER BY c.generated_date DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_company', $idCompany, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id){
        $query = "SELECT * FROM {$this->table} WHERE id_commission = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Total de comisiones generadas (por estado) dentro de un rango de fechas, para el dashboard Gerente */
    public function getTotalsByPeriod($dateFrom, $dateTo){
        $query = "
            SELECT
                COALESCE(SUM(amount), 0) AS total,
                COALESCE(SUM(CASE WHEN status = 'Pagada' THEN amount ELSE 0 END), 0) AS total_pagadas,
                COALESCE(SUM(CASE WHEN status = 'Pendiente' THEN amount ELSE 0 END), 0) AS total_pendientes
            FROM {$this->table}
            WHERE generated_date BETWEEN :date_from AND :date_to
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $query = "
            INSERT INTO {$this->table}
                (id_policy, id_producer, amount, rate_applied, status, generated_date, payment_date, notes)
            VALUES
                (:id_policy, :id_producer, :amount, :rate_applied, :status, :generated_date, :payment_date, :notes)
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id_policy'       => (int) $data['id_policy'],
            ':id_producer'     => (int) $data['id_producer'],
            ':amount'          => $data['amount'] !== '' ? $data['amount'] : 0,
            ':rate_applied'    => $data['rate_applied'] !== '' ? $data['rate_applied'] : 0,
            ':status'          => !empty($data['status']) ? $data['status'] : 'Pendiente',
            ':generated_date'  => !empty($data['generated_date']) ? $data['generated_date'] : date('Y-m-d'),
            ':payment_date'    => !empty($data['payment_date']) ? $data['payment_date'] : null,
            ':notes'           => !empty($data['notes']) ? trim($data['notes']) : null,
        ]);
    }

    public function update($id, $data){
        $query = "
            UPDATE {$this->table}
            SET id_policy = :id_policy,
                id_producer = :id_producer,
                amount = :amount,
                rate_applied = :rate_applied,
                status = :status,
                generated_date = :generated_date,
                payment_date = :payment_date,
                notes = :notes,
                updated_at = CURRENT_TIMESTAMP
            WHERE id_commission = :id
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'              => $id,
            ':id_policy'       => (int) $data['id_policy'],
            ':id_producer'     => (int) $data['id_producer'],
            ':amount'          => $data['amount'] !== '' ? $data['amount'] : 0,
            ':rate_applied'    => $data['rate_applied'] !== '' ? $data['rate_applied'] : 0,
            ':status'          => !empty($data['status']) ? $data['status'] : 'Pendiente',
            ':generated_date'  => !empty($data['generated_date']) ? $data['generated_date'] : date('Y-m-d'),
            ':payment_date'    => !empty($data['payment_date']) ? $data['payment_date'] : null,
            ':notes'           => !empty($data['notes']) ? trim($data['notes']) : null,
        ]);
    }

    public function markPaid($id){
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET status = 'Pagada', payment_date = :today, updated_at = CURRENT_TIMESTAMP
            WHERE id_commission = :id
        ");
        return $stmt->execute([':id' => $id, ':today' => date('Y-m-d')]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_commission = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getMonthlySummary($yearMonth){
        $query = "SELECT 
                    COALESCE(SUM(amount), 0) AS total_commissions,
                    COUNT(*) AS total_records,
                    SUM(CASE WHEN status = 'Pagada' THEN amount ELSE 0 END) AS paid,
                    SUM(CASE WHEN status = 'Pendiente' THEN amount ELSE 0 END) AS pending
                  FROM {$this->table}
                  WHERE DATE_FORMAT(created_at, '%Y-%m') = :ym";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':ym' => $yearMonth]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_commissions' => 0, 'total_records' => 0, 'paid' => 0, 'pending' => 0];
    }

    public function calculateForPolicy($idPolicy){
        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/InsuranceCompany.php';
        
        $policy = (new Policy())->findById($idPolicy);
        if (!$policy) return false;
        
        $company = (new InsuranceCompany())->findById($policy['id_insurance_company']);
        if (!$company) return false;
        
        $rate = (float)($company['commission_rate'] ?? 0);
        $basis = $company['commission_basis'] ?? 'Prima total';
        
        $premium = (float)($policy['total_premium_amount'] ?? 0);
        $amount = 0;
        
        if ($basis === 'Prima total') {
            $amount = $premium * ($rate / 100);
        } elseif ($basis === 'Prima neta') {
            $amount = $premium * 0.7 * ($rate / 100);
        }
        
        return [
            'id_policy' => $idPolicy,
            'amount' => round($amount, 2),
            'rate' => $rate,
            'basis' => $basis,
        ];
    }
}