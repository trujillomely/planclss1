<?php

require_once ROOT_PATH . '/app/config/database.php';

class PaymentSchedule {

    private $conn;
    private $table = 'payment_schedule';
    private static $hasNotesColumn = null;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    private function hasNotesColumn(){
        if (self::$hasNotesColumn === null) {
            try {
                $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payment_schedule` LIKE 'notes'");
                $stmt->execute();
                self::$hasNotesColumn = $stmt->fetch() !== false;
            } catch (Exception $e) {
                self::$hasNotesColumn = false;
            }
        }
        return self::$hasNotesColumn;
    }

    public function getAll(){
        $query = "
            SELECT
                ps.*,
                p.policy_number
            FROM payment_schedule ps
            LEFT JOIN policy p ON ps.id_policy = p.id_policy
            ORDER BY ps.due_date DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cuotas con datos de póliza y cliente, listas para pintar en el calendario de cobros */
    public function getForCalendar(){
        $query = "
            SELECT
                ps.id_payment_schedule,
                ps.due_date,
                ps.amount_due,
                ps.amount_paid,
                ps.payment_status,
                p.policy_number,
                u.username,
                u.lastname
            FROM {$this->table} ps
            LEFT JOIN policy p ON ps.id_policy = p.id_policy
            LEFT JOIN user u ON p.id_user = u.id
            ORDER BY ps.due_date ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id){
        $query = "SELECT * FROM {$this->table} WHERE id_payment_schedule = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Cantidad de transacciones ya registradas contra esta cuota (para advertir antes de eliminar) */
    public function countTransactions($id){
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM payment_transaction WHERE id_payment_schedule = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /** Cuotas pendientes/atrasadas/parciales de las pólizas de un cliente (panel Cliente) */
    public function getPendingByUser($idUser){
        $query = "
            SELECT
                ps.*,
                p.policy_number,
                it.name AS insurance_type_name,
                (ps.amount_due - COALESCE(ps.amount_paid, 0)) AS balance_due
            FROM {$this->table} ps
            INNER JOIN policy p ON ps.id_policy = p.id_policy
            LEFT JOIN insurance_type it ON p.id_insurance_type = it.id_insurance_type
            WHERE p.id_user = :id_user
              AND ps.payment_status IN ('Pendiente', 'Atrasado', 'Parcial')
            ORDER BY ps.due_date ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $idUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cuotas (todas, cualquier estado) de las pólizas de un cliente — para su calendario de pagos */
    public function getForCalendarByUser($idUser){
        $query = "
            SELECT
                ps.id_payment_schedule,
                ps.due_date,
                ps.amount_due,
                ps.amount_paid,
                ps.payment_date,
                ps.payment_status,
                ps.installment_number,
                p.policy_number
            FROM {$this->table} ps
            INNER JOIN policy p ON ps.id_policy = p.id_policy
            WHERE p.id_user = :id_user
            ORDER BY ps.due_date ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $idUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** ¿La cuota indicada pertenece a una póliza del cliente indicado? (chequeo de propiedad antes de aceptar un pago) */
    public function belongsToUser($idSchedule, $idUser){
        $query = "
            SELECT ps.*
            FROM {$this->table} ps
            INNER JOIN policy p ON ps.id_policy = p.id_policy
            WHERE ps.id_payment_schedule = :id_schedule AND p.id_user = :id_user
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_schedule' => $idSchedule, ':id_user' => $idUser]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recalcula amount_paid / payment_date / payment_status de una cuota a partir de
     * sus transacciones CONFIRMADAS. Centralizado aquí para que admin y cliente
     * disparen siempre la misma lógica tras crear/editar/eliminar una transacción.
     */
    public function syncFromTransactions($idSchedule){
        if (!$idSchedule) return;

        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(amount), 0) AS total_paid, MAX(payment_date) AS last_date
            FROM payment_transaction
            WHERE id_payment_schedule = :id AND confirmed = 1
        ");
        $stmt->execute([':id' => $idSchedule]);
        $sums = $stmt->fetch(PDO::FETCH_ASSOC);

        $schedule = $this->findById($idSchedule);
        if (!$schedule) return;

        $totalPaid = (float) $sums['total_paid'];
        $amountDue = (float) $schedule['amount_due'];

        if ($totalPaid <= 0) {
            $status = 'Pendiente';
        } elseif ($totalPaid >= $amountDue) {
            $status = 'Pagado';
        } else {
            $status = 'Parcial';
        }

        // Si ya estaba marcada como atrasada y sigue sin cubrirse, conserva "Atrasado"
        if ($schedule['payment_status'] === 'Atrasado' && $totalPaid < $amountDue) {
            $status = $totalPaid > 0 ? 'Parcial' : 'Atrasado';
        }

        $updateData = [
            'id_policy'          => $schedule['id_policy'],
            'installment_number' => $schedule['installment_number'],
            'due_date'           => $schedule['due_date'],
            'amount_due'         => $schedule['amount_due'],
            'amount_paid'        => $totalPaid,
            'payment_date'       => $sums['last_date'],
            'payment_status'     => $status,
        ];
        if ($this->hasNotesColumn()) {
            $updateData['notes'] = $schedule['notes'] ?? null;
        }
        $this->update($idSchedule, $updateData);
    }

    public function create($data){
        $hasNotes = $this->hasNotesColumn();
        $cols = 'id_policy, installment_number, due_date, amount_due, amount_paid, payment_date, payment_status';
        $params = ':id_policy, :installment_number, :due_date, :amount_due, :amount_paid, :payment_date, :payment_status';
        if ($hasNotes) {
            $cols .= ', notes';
            $params .= ', :notes';
        }
        $query = "INSERT INTO {$this->table} ({$cols}) VALUES ({$params})";
        $stmt = $this->conn->prepare($query);
        $binds = [
            ':id_policy'           => (int) $data['id_policy'],
            ':installment_number'  => !empty($data['installment_number']) ? (int)$data['installment_number'] : 1,
            ':due_date'            => !empty($data['due_date']) ? $data['due_date'] : null,
            ':amount_due'          => $data['amount_due'] !== '' ? $data['amount_due'] : 0,
            ':amount_paid'         => $data['amount_paid'] !== '' ? $data['amount_paid'] : null,
            ':payment_date'        => !empty($data['payment_date']) ? $data['payment_date'] : null,
            ':payment_status'      => !empty($data['payment_status']) ? $data['payment_status'] : 'Pendiente',
        ];
        if ($hasNotes) {
            $binds[':notes'] = !empty($data['notes']) ? trim($data['notes']) : null;
        }
        return $stmt->execute($binds);
    }

    public function update($id, $data){
        $hasNotes = $this->hasNotesColumn();
        $setClauses = 'id_policy = :id_policy,
                installment_number = :installment_number,
                due_date = :due_date,
                amount_due = :amount_due,
                amount_paid = :amount_paid,
                payment_date = :payment_date,
                payment_status = :payment_status,
                updated_at = CURRENT_TIMESTAMP';
        if ($hasNotes) {
            $setClauses .= ",
                notes = :notes";
        }
        $query = "UPDATE {$this->table} SET {$setClauses} WHERE id_payment_schedule = :id";
        $stmt = $this->conn->prepare($query);
        $binds = [
            ':id'                  => $id,
            ':id_policy'           => (int) $data['id_policy'],
            ':installment_number'  => !empty($data['installment_number']) ? (int)$data['installment_number'] : 1,
            ':due_date'            => !empty($data['due_date']) ? $data['due_date'] : null,
            ':amount_due'          => $data['amount_due'] !== '' ? $data['amount_due'] : 0,
            ':amount_paid'         => $data['amount_paid'] !== '' ? $data['amount_paid'] : null,
            ':payment_date'        => !empty($data['payment_date']) ? $data['payment_date'] : null,
            ':payment_status'      => !empty($data['payment_status']) ? $data['payment_status'] : 'Pendiente',
        ];
        if ($hasNotes) {
            $binds[':notes'] = !empty($data['notes']) ? trim($data['notes']) : null;
        }
        return $stmt->execute($binds);
    }

    public function getByPolicy($idPolicy){
        $query = "SELECT * FROM {$this->table} WHERE id_policy = :id_policy ORDER BY installment_number ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_policy', $idPolicy, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_payment_schedule = :id");
        return $stmt->execute([':id' => $id]);
    }
}