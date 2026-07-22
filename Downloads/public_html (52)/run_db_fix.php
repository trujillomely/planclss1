<?php
/**
 * ONE-CLICK DATABASE FIX — Subir, abrir en navegador, luego ELIMINAR.
 *
 * Este script:
 *   1. Verifica que el usuario esté logueado como admin
 *   2. Crea tablas/columnas faltantes de forma idempotente
 *   3. Inserta datos seed si faltan
 *   4. Reporta qué se hizo
 *
 * DESPUÉS DE USARLO, ELIMINALO DEL SERVIDOR.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'APP_ENV=production') !== false) {
        ini_set('display_errors', 0);
    }
}

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/app/helpers/Environment.php';
Environment::load();
require_once ROOT_PATH . '/app/config/database.php';

session_start();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isCli   = php_sapi_name() === 'cli';

if (!$isAdmin && !$isCli) {
    http_response_code(403);
    echo '<h1>403 — Acceso denegado</h1><p>Solo administradores pueden ejecutar este script.</p>';
    echo '<p><a href="?url=admin/dashboard">Volver al panel</a></p>';
    exit;
}

$db = new Database();
$conn = $db->connect();

$checks  = [];
$created = [];
$errors  = [];

function tableExists($conn, $table) {
    $stmt = $conn->prepare("SHOW TABLES LIKE :t");
    $stmt->execute([':t' => $table]);
    return $stmt->fetch() !== false;
}

function columnExists($conn, $table, $column) {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
    $stmt->execute([':c' => $column]);
    return $stmt->fetch() !== false;
}

function indexExists($conn, $table, $index) {
    $stmt = $conn->prepare("SHOW INDEX FROM `$table` WHERE Key_name = :k");
    $stmt->execute([':k' => $index]);
    return $stmt->fetch() !== false;
}

function runSql($conn, $sql, $label) {
    global $created, $errors;
    try {
        $conn->exec($sql);
        $created[] = $label;
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate') !== false || strpos($msg, 'already exists') !== false || strpos($msg, '1061') !== false || strpos($msg, '1060') !== false) {
            $checks[] = $label . ' (ya existía)';
        } else {
            $errors[] = $label . ': ' . $msg;
        }
    }
}

// ─── 1. Tabla digital_signature ────────────────────────────────
if (!tableExists($conn, 'digital_signature')) {
    runSql($conn, "CREATE TABLE `digital_signature` (
        `id_signature` int PRIMARY KEY AUTO_INCREMENT,
        `id_policy` int NOT NULL,
        `id_user` int NOT NULL,
        `signature_type` enum('canvas','token','upload') NOT NULL DEFAULT 'canvas',
        `signature_url` varchar(500) NOT NULL,
        `signature_hash` varchar(64) NOT NULL,
        `signer_name` varchar(255) NOT NULL,
        `signer_email` varchar(255) NULL,
        `signer_dpi` varchar(50) NULL,
        `ip_address` varchar(45) NULL,
        `user_agent` text NULL,
        `metadata` json NULL,
        `signed_at` timestamp DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'Tabla digital_signature');
} else {
    $checks[] = 'Tabla digital_signature (ya existe)';
}

// Foreign keys
if (tableExists($conn, 'digital_signature')) {
    $fkExists = false;
    $stmt = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_signature' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
    $fkCount = $stmt->fetchColumn();
    if ($fkCount == 0) {
        runSql($conn, "ALTER TABLE `digital_signature` ADD FOREIGN KEY (`id_policy`) REFERENCES `policy`(`id_policy`)", 'FK digital_signature → policy');
        runSql($conn, "ALTER TABLE `digital_signature` ADD FOREIGN KEY (`id_user`) REFERENCES `user`(`id`)", 'FK digital_signature → user');
    } else {
        $checks[] = 'Foreign keys digital_signature (ya existen)';
    }
}

// ─── 2. Columna signature_status ──────────────────────────────
if (!columnExists($conn, 'policy', 'signature_status')) {
    runSql($conn, "ALTER TABLE `policy` ADD COLUMN `signature_status` enum('Sin firma','Pendiente','Firmada') DEFAULT 'Sin firma' AFTER `status`", 'Columna policy.signature_status');
} else {
    $checks[] = 'Columna policy.signature_status (ya existe)';
}

// ─── 3. Tabla payment_method ──────────────────────────────────
if (!tableExists($conn, 'payment_method')) {
    runSql($conn, "CREATE TABLE `payment_method` (
        `id_payment_method` int PRIMARY KEY AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` varchar(255) NULL,
        `requires_reference` tinyint(1) DEFAULT 0,
        `is_online` tinyint(1) DEFAULT 0,
        `status` tinyint(1) DEFAULT 1,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'Tabla payment_method');
} else {
    $checks[] = 'Tabla payment_method (ya existe)';
}

// Seed payment methods
if (tableExists($conn, 'payment_method')) {
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM payment_method");
    $count = $stmt->fetch()['total'];
    if ($count == 0) {
        runSql($conn, "INSERT INTO payment_method (name, description, requires_reference, is_online, status) VALUES
            ('Efectivo',               'Pago en efectivo en oficina o punto de cobro', 0, 0, 1),
            ('Tarjeta de Credito',     'Pago con tarjeta Visa, Mastercard, Amex', 1, 1, 1),
            ('Tarjeta de Debito',      'Pago con tarjeta de debito vinculada a cuenta bancaria', 1, 1, 1),
            ('Transferencia Bancaria', 'Transferencia electronica a cuenta de la empresa', 1, 0, 1),
            ('Deposito Bancario',      'Deposito en cuenta bancaria de la empresa', 1, 0, 1),
            ('PayPal',                 'Pago en linea a traves de PayPal', 0, 1, 1)", 'Seed payment_method');
    } else {
        $checks[] = "Seed payment_method (ya tiene {$count} registros)";
    }
}

// ─── 4. Columna notes en payment_schedule ─────────────────────
if (tableExists($conn, 'payment_schedule')) {
    if (!columnExists($conn, 'payment_schedule', 'notes')) {
        runSql($conn, "ALTER TABLE `payment_schedule` ADD COLUMN `notes` text NULL AFTER `payment_status`", 'Columna payment_schedule.notes');
    } else {
        $checks[] = 'Columna payment_schedule.notes (ya existe)';
    }
} else {
    $errors[] = 'Tabla payment_schedule NO EXISTE — revisar schema base';
}

// ─── 5. Tabla payment_transaction ─────────────────────────────
if (!tableExists($conn, 'payment_transaction')) {
    runSql($conn, "CREATE TABLE `payment_transaction` (
        `id_payment_transaction` int PRIMARY KEY AUTO_INCREMENT,
        `id_payment_schedule` int NULL,
        `id_payment_method` int NULL,
        `payment_date` date NULL,
        `transaction_reference` varchar(255) NULL,
        `amount` decimal(12,2) NOT NULL DEFAULT 0,
        `currency` varchar(3) DEFAULT 'GTQ',
        `confirmed` tinyint(1) DEFAULT 0,
        `confirmed_at` datetime NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'Tabla payment_transaction');
} else {
    $checks[] = 'Tabla payment_transaction (ya existe)';
}

// ─── 6. Verificar tablas base ─────────────────────────────────
$requiredTables = ['policy', 'user', 'claim', 'payment_schedule', 'payment_frequency',
                   'insurance_company', 'insurance_type', 'producer', 'address',
                   'locality', 'municipality', 'department',
                   'policy_coverage', 'policy_beneficiary'];
foreach ($requiredTables as $t) {
    if (!tableExists($conn, $t)) {
        $errors[] = "Tabla BASE faltante: {$t} — ejecutar Corredor_Seguros.sql primero";
    } else {
        $checks[] = "Tabla {$t} (OK)";
    }
}

// ─── 7. Verificar columnas criticas en policy ────────────────
$policyCols = ['id_user', 'id_producer', 'id_insurance_company', 'id_insurance_type',
               'id_payment_frequency', 'policy_number', 'date_start', 'date_expiration',
               'total_coverage_amount', 'total_deductible_amount', 'total_premium_amount',
               'renewable', 'status'];
foreach ($policyCols as $c) {
    if (!columnExists($conn, 'policy', $c)) {
        $errors[] = "Columna policy.{$c} FALTANTE";
    }
}

// ─── OUTPUT ───────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Database Fix — Arco Seguros</title>
    <style>
        body { font-family: -apple-system, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f8f6f3; color: #333; }
        h1 { color: #3B6178; }
        .section { background: #fff; border-radius: 8px; padding: 20px; margin: 16px 0; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .ok { color: #16a34a; }
        .created { color: #2563eb; font-weight: 600; }
        .error { color: #dc2626; font-weight: 600; }
        ul { list-style: none; padding: 0; }
        li { padding: 4px 0; }
        li::before { content: '•'; margin-right: 8px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3B6178; color: #fff; border-radius: 6px; text-decoration: none; margin-top: 16px; }
        .btn:hover { background: #2d4d5e; }
        .warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Database Fix — Arco Seguros</h1>

    <?php if (!empty($errors)): ?>
    <div class="section">
        <h2 class="error">Errores</h2>
        <ul>
            <?php foreach ($errors as $e): ?>
            <li class="error"><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($created)): ?>
    <div class="section">
        <h2 class="created">Tablas/columnas creadas</h2>
        <ul>
            <?php foreach ($created as $c): ?>
            <li class="created"><?php echo htmlspecialchars($c); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($checks)): ?>
    <div class="section">
        <h2>Verificaciones</h2>
        <ul>
            <?php foreach ($checks as $ck): ?>
            <li class="ok"><?php echo htmlspecialchars($ck); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="warning">
        <strong>IMPORTANTE:</strong> Elimina este archivo del servidor despues de usarlo.
        <br><br>
        <strong>Archivo:</strong> <code>run_db_fix.php</code>
    </div>

    <a href="?url=admin/dashboard" class="btn">Volver al panel</a>
</body>
</html>
