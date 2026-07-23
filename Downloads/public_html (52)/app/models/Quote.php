<?php

require_once ROOT_PATH . '/app/config/database.php';

class Quote {

    private $conn;
    private $table = 'quote';

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(){

        $query = "
            SELECT
                q.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                u.email AS user_email,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name,
                pr.full_name AS producer_name

            FROM {$this->table} q

            LEFT JOIN user u
                ON q.id_user = u.id

            LEFT JOIN insurance_company ic
                ON q.id_insurance_company = ic.id_insurance_company

            LEFT JOIN insurance_type it
                ON q.id_insurance_type = it.id_insurance_type

            LEFT JOIN producer pr
                ON q.id_producer = pr.id_producer

            ORDER BY q.id_quote DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todas las cotizaciones de un cliente (panel Cliente) */
    public function getByUser($idUser){

        $query = "
            SELECT
                q.*,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name
            FROM {$this->table} q
            LEFT JOIN insurance_company ic ON q.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON q.id_insurance_type = it.id_insurance_type
            WHERE q.id_user = :id_user
            ORDER BY q.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $idUser, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCompany($idCompany){
        $query = "
            SELECT
                q.*,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name
            FROM {$this->table} q
            LEFT JOIN insurance_company ic ON q.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON q.id_insurance_type = it.id_insurance_type
            WHERE q.id_insurance_company = :id_company
            ORDER BY q.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_company', $idCompany, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id){

        $query = "
            SELECT
                q.*,
                CONCAT(u.username, ' ', u.lastname) AS user_name,
                u.email AS user_email,
                ic.name AS insurance_company_name,
                it.name AS insurance_type_name,
                pr.full_name AS producer_name
            FROM {$this->table} q
            LEFT JOIN user u ON q.id_user = u.id
            LEFT JOIN insurance_company ic ON q.id_insurance_company = ic.id_insurance_company
            LEFT JOIN insurance_type it ON q.id_insurance_type = it.id_insurance_type
            LEFT JOIN producer pr ON q.id_producer = pr.id_producer
            WHERE q.id_quote = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Genera el siguiente folio consecutivo: COT-{año}-{0001} */
    public function generateFolio(){
        $year = date('Y');
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table} WHERE folio LIKE :prefix");
        $stmt->execute([':prefix' => "COT-{$year}-%"]);
        $total = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $next  = str_pad($total + 1, 4, '0', STR_PAD_LEFT);
        return "COT-{$year}-{$next}";
    }

    public function create($data){
        $folio = !empty($data['folio']) ? trim($data['folio']) : $this->generateFolio();

        $query = "
            INSERT INTO {$this->table}
                (folio, id_user, client_name, client_email, id_producer, id_insurance_company, id_insurance_type,
                 estimated_premium, notes, valid_until, status)
            VALUES
                (:folio, :id_user, :client_name, :client_email, :id_producer, :id_insurance_company, :id_insurance_type,
                 :estimated_premium, :notes, :valid_until, :status)
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':folio'                  => $folio,
            ':id_user'                => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':client_name'            => !empty($data['client_name']) ? trim($data['client_name']) : null,
            ':client_email'           => !empty($data['client_email']) ? trim($data['client_email']) : null,
            ':id_producer'            => !empty($data['id_producer']) ? (int)$data['id_producer'] : null,
            ':id_insurance_company'   => !empty($data['id_insurance_company']) ? (int)$data['id_insurance_company'] : null,
            ':id_insurance_type'      => !empty($data['id_insurance_type']) ? (int)$data['id_insurance_type'] : null,
            ':estimated_premium'      => $data['estimated_premium'] !== '' && isset($data['estimated_premium']) ? $data['estimated_premium'] : null,
            ':notes'                  => !empty($data['notes']) ? trim($data['notes']) : null,
            ':valid_until'            => !empty($data['valid_until']) ? $data['valid_until'] : null,
            ':status'                 => !empty($data['status']) ? $data['status'] : 'Pendiente',
        ]);
    }

    public function update($id, $data){
        $query = "
            UPDATE {$this->table}
            SET id_user = :id_user,
                client_name = :client_name,
                client_email = :client_email,
                id_producer = :id_producer,
                id_insurance_company = :id_insurance_company,
                id_insurance_type = :id_insurance_type,
                estimated_premium = :estimated_premium,
                notes = :notes,
                valid_until = :valid_until,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id_quote = :id
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'                     => $id,
            ':id_user'                => !empty($data['id_user']) ? (int)$data['id_user'] : null,
            ':client_name'            => !empty($data['client_name']) ? trim($data['client_name']) : null,
            ':client_email'           => !empty($data['client_email']) ? trim($data['client_email']) : null,
            ':id_producer'            => !empty($data['id_producer']) ? (int)$data['id_producer'] : null,
            ':id_insurance_company'   => !empty($data['id_insurance_company']) ? (int)$data['id_insurance_company'] : null,
            ':id_insurance_type'      => !empty($data['id_insurance_type']) ? (int)$data['id_insurance_type'] : null,
            ':estimated_premium'      => $data['estimated_premium'] !== '' && isset($data['estimated_premium']) ? $data['estimated_premium'] : null,
            ':notes'                  => !empty($data['notes']) ? trim($data['notes']) : null,
            ':valid_until'            => !empty($data['valid_until']) ? $data['valid_until'] : null,
            ':status'                 => !empty($data['status']) ? $data['status'] : 'Pendiente',
        ]);
    }

    /** Cambia únicamente el estatus (usado por los botones rápidos de la tabla) */
    public function changeStatus($id, $status){
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id_quote = :id");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_quote = :id");
        return $stmt->execute([':id' => $id]);
    }
}
