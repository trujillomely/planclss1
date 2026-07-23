<?php

class ClienteController {

    private function requireAuth() {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ?url=login');
            exit;
        }
    }

    public function dashboard(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/Claim.php';
        require_once ROOT_PATH . '/app/models/PaymentTransaction.php';

        $idUser = $_SESSION['id_user'];

        $policyModel  = new Policy();
        $claimModel   = new Claim();
        $paymentModel = new PaymentTransaction();

        $policies = [];
        $claims   = [];
        $totalPaid = 0;

        try { $policies = $policyModel->getByUser($idUser); }
        catch (Exception $e) { error_log('dashboard policies: ' . $e->getMessage()); }

        try { $claims = $claimModel->getByUser($idUser); }
        catch (Exception $e) { error_log('dashboard claims: ' . $e->getMessage()); }

        try { $totalPaid = $paymentModel->getTotalPaidByUser($idUser); }
        catch (Exception $e) { error_log('dashboard totalPaid: ' . $e->getMessage()); }

        // Pólizas activas
        $activePolicies = array_values(array_filter($policies, fn($p) => $p['status'] === 'Activo'));

        // Próximos vencimientos: pólizas activas cuya fecha de expiración es futura, ordenadas por cercanía
        $today = new DateTime('today');
        $upcomingRenewals = array_values(array_filter($policies, function($p) use ($today) {
            if (empty($p['date_expiration']) || $p['status'] !== 'Activo') return false;
            return new DateTime($p['date_expiration']) >= $today;
        }));
        usort($upcomingRenewals, fn($a, $b) => strtotime($a['date_expiration']) <=> strtotime($b['date_expiration']));
        $upcomingRenewals = array_slice($upcomingRenewals, 0, 4);

        // Reclamos abiertos (no cerrados/rechazados)
        $openClaims = array_values(array_filter($claims, fn($c) => in_array($c['status'], ['Pendiente', 'Activo'])));

        $recentClaims = array_slice($claims, 0, 3);

        require_once ROOT_PATH . '/app/views/cliente/dashboard.php';
    }

    public function renewals(){
        Auth::requirePermission('polizas', 'ver');

        require_once ROOT_PATH . '/app/models/PolicyRenewal.php';

        $idUser = $_SESSION['id_user'];

        $renewalModel = new PolicyRenewal();
        $renewals     = $renewalModel->getByUser($idUser);

        require_once ROOT_PATH . '/app/views/cliente/renewals.php';
    }

    public function policies(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/PolicyCoverage.php';
        require_once ROOT_PATH . '/app/models/PolicyBeneficiary.php';
        require_once ROOT_PATH . '/app/models/PolicyDocument.php';
        require_once ROOT_PATH . '/app/models/PaymentSchedule.php';

        $idUser = $_SESSION['id_user'];

        $policyModel    = new Policy();
        $coverageModel  = new PolicyCoverage();
        $beneficiaryModel = new PolicyBeneficiary();
        $documentModel  = new PolicyDocument();
        $scheduleModel  = new PaymentSchedule();

        $policies = $policyModel->getByUser($idUser);

        // Para cada póliza, adjuntamos su detalle (coberturas, beneficiarios, documentos y cuotas)
        foreach ($policies as &$p) {
            try { $p['coverages'] = $coverageModel->getByPolicy($p['id_policy']); }
            catch (Exception $e) { $p['coverages'] = []; error_log('policies coverages: ' . $e->getMessage()); }

            try { $p['beneficiaries'] = $beneficiaryModel->getByPolicy($p['id_policy']); }
            catch (Exception $e) { $p['beneficiaries'] = []; error_log('policies beneficiaries: ' . $e->getMessage()); }

            try { $p['documents'] = $documentModel->getByPolicy($p['id_policy']); }
            catch (Exception $e) { $p['documents'] = []; error_log('policies documents: ' . $e->getMessage()); }

            try { $p['schedule'] = $scheduleModel->getByPolicy($p['id_policy']); }
            catch (Exception $e) { $p['schedule'] = []; error_log('policies schedule: ' . $e->getMessage()); }
        }
        unset($p);

        $activeCount   = count(array_filter($policies, fn($p) => $p['status'] === 'Activo'));
        $expiredCount  = count(array_filter($policies, fn($p) => $p['status'] === 'Expirado'));
        $totalCoverage = array_sum(array_column($policies, 'total_coverage_amount'));

        require_once ROOT_PATH . '/app/views/cliente/policies.php';
    }

    public function claims(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Claim.php';
        require_once ROOT_PATH . '/app/models/ClaimType.php';
        require_once ROOT_PATH . '/app/models/ClaimPayment.php';
        require_once ROOT_PATH . '/app/models/Policy.php';

        $idUser = $_SESSION['id_user'];

        $claimModel     = new Claim();
        $claimTypeModel = new ClaimType();
        $paymentModel   = new ClaimPayment();
        $policyModel    = new Policy();

        $claims = [];
        try { $claims = $claimModel->getByUser($idUser); }
        catch (Exception $e) { error_log('claims getByUser: ' . $e->getMessage()); }

        foreach ($claims as &$c) {
            try { $c['payments'] = $paymentModel->getByClaim($c['id_claim']); }
            catch (Exception $e) { $c['payments'] = []; }
        }
        unset($c);

        $claimTypes = [];
        try { $claimTypes = $claimTypeModel->getActive(); }
        catch (Exception $e) { error_log('claims getActive: ' . $e->getMessage()); }

        // Solo pólizas activas pueden usarse para levantar un nuevo reclamo
        $policies = [];
        try { $policies = array_values(array_filter($policyModel->getByUser($idUser), fn($p) => $p['status'] === 'Activo')); }
        catch (Exception $e) { error_log('claims policies: ' . $e->getMessage()); }

        $openClaims   = count(array_filter($claims, fn($c) => in_array($c['status'], ['Pendiente', 'Activo', 'Reportado', 'En evaluación', 'En proceso'])));
        $closedClaims = count(array_filter($claims, fn($c) => in_array($c['status'], ['Aprovado', 'Aprobado', 'Inactivo', 'Cerrado', 'Rechazado'])));
        $totalClaimed = array_sum(array_column($claims, 'amount_claimed'));

        require_once ROOT_PATH . '/app/views/cliente/claims.php';
    }

    public function payments(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/PaymentSchedule.php';
        require_once ROOT_PATH . '/app/models/PaymentTransaction.php';
        require_once ROOT_PATH . '/app/models/PaymentMethod.php';

        $idUser = $_SESSION['id_user'];

        $scheduleModel    = new PaymentSchedule();
        $transactionModel = new PaymentTransaction();
        $methodModel      = new PaymentMethod();

        $pendingSchedules = [];
        $transactions     = [];
        $paymentMethods   = [];
        $dbError          = null;

        try {
            $pendingSchedules = $scheduleModel->getPendingByUser($idUser);
        } catch (Exception $e) {
            error_log('ClienteController::payments getPendingByUser: ' . $e->getMessage());
        }

        try {
            $transactions = $transactionModel->getAllByUser($idUser);
        } catch (Exception $e) {
            error_log('ClienteController::payments getAllByUser: ' . $e->getMessage());
        }

        try {
            $paymentMethods = $methodModel->getActive();
        } catch (Exception $e) {
            error_log('ClienteController::payments getActive: ' . $e->getMessage());
            $dbError = 'No se pudieron cargar los metodos de pago. Ejecute la migracion de base de datos.';
        }

        if (empty($paymentMethods) && !$dbError) {
            try {
                $this->seedDefaultPaymentMethods($methodModel);
                $paymentMethods = $methodModel->getActive();
            } catch (Exception $e) {
                error_log('ClienteController::payments seed: ' . $e->getMessage());
            }
        }

        $totalPaid = 0;
        try {
            $totalPaid = $transactionModel->getTotalPaidByUser($idUser);
        } catch (Exception $e) {
            error_log('ClienteController::payments getTotalPaid: ' . $e->getMessage());
        }

        $pendingBalance = array_sum(array_column($pendingSchedules, 'balance_due'));
        $overdueCount = count(array_filter($pendingSchedules, fn($s) => $s['payment_status'] === 'Atrasado'));
        $awaitingConfirmation = count(array_filter($transactions, fn($t) => !$t['confirmed']));

        require_once ROOT_PATH . '/app/views/cliente/payments.php';
    }

    private function seedDefaultPaymentMethods($methodModel){
        $defaults = [
            ['name' => 'Efectivo',               'description' => 'Pago en efectivo en oficina',              'requires_reference' => 0, 'is_online' => 0],
            ['name' => 'Tarjeta de Crédito',     'description' => 'Pago con tarjeta Visa, Mastercard, Amex',  'requires_reference' => 0, 'is_online' => 1],
            ['name' => 'Tarjeta de Débito',      'description' => 'Pago con tarjeta de débito',               'requires_reference' => 0, 'is_online' => 1],
            ['name' => 'Transferencia Bancaria', 'description' => 'Transferencia electrónica',                'requires_reference' => 1, 'is_online' => 0],
            ['name' => 'Depósito Bancario',      'description' => 'Depósito en cuenta bancaria',              'requires_reference' => 1, 'is_online' => 0],
            ['name' => 'PayPal',                 'description' => 'Pago en línea vía PayPal',                 'requires_reference' => 0, 'is_online' => 1],
        ];
        foreach ($defaults as $m) {
            try { $methodModel->create($m); } catch (Exception $e) {}
        }
    }

    public function paymentCalendar(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/PaymentSchedule.php';
        require_once ROOT_PATH . '/app/models/ClaimPayment.php';
        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/Claim.php';
        require_once ROOT_PATH . '/app/models/User.php';
        require_once ROOT_PATH . '/app/models/CalendarEvent.php';

        $idUser = $_SESSION['id_user'];

        $scheduleModel     = new PaymentSchedule();
        $claimPaymentModel = new ClaimPayment();
        $policyModel       = new Policy();
        $claimModel        = new Claim();
        $userModel         = new User();
        $eventModel        = new CalendarEvent();

        $schedules = $scheduleModel->getForCalendarByUser($idUser);

        $calendarEvents = [];

        // Cuotas (cobros)
        foreach ($schedules as $s) {
            if (empty($s['due_date'])) continue;
            $calendarEvents[] = [
                'date'   => $s['due_date'],
                'type'   => 'cobro',
                'label'  => 'Cuota ' . ($s['policy_number'] ?? 'Póliza'),
                'amount' => (float) $s['amount_due'],
                'status' => $s['payment_status'],
                'ref'    => $s['policy_number'] ?? '',
            ];
        }

        // Pagos de reclamos
        $claimPayments = $claimPaymentModel->getForCalendarByUser($idUser);
        foreach ($claimPayments as $p) {
            if (empty($p['payment_date'])) continue;
            $calendarEvents[] = [
                'date'   => $p['payment_date'],
                'type'   => 'pago',
                'label'  => 'Reclamo ' . ($p['claim_number'] ?? ''),
                'amount' => (float) $p['amount'],
                'status' => $p['status'],
                'ref'    => $p['claim_number'] ?? '',
            ];
        }

        // Mi cumpleaños
        $me = $userModel->findById($idUser);
        if ($me && !empty($me['birth_date'])) {
            $bday = $me['birth_date'];
            $thisYear = (int) date('Y');
            $bdayThisYear = $thisYear . '-' . substr($bday, 5);
            if ($bdayThisYear < date('Y-m-d')) {
                $bdayThisYear = ($thisYear + 1) . '-' . substr($bday, 5);
            }
            $calendarEvents[] = [
                'date'   => $bdayThisYear,
                'type'   => 'cumpleano',
                'label'  => 'Mi cumpleaños',
                'amount' => 0,
                'status' => 'info',
                'ref'    => '',
            ];
        }

        // Mis renovaciones próximas
        $myPolicies = $policyModel->getByUser($idUser);
        foreach ($myPolicies as $mp) {
            if (($mp['renewable'] ?? 0) == 1
                && ($mp['status'] ?? '') === 'Activo'
                && !empty($mp['date_expiration'])
                && $mp['date_expiration'] >= date('Y-m-d')
                && $mp['date_expiration'] <= date('Y-m-d', strtotime('+60 days'))) {
                $calendarEvents[] = [
                    'date'   => $mp['date_expiration'],
                    'type'   => 'renovacion',
                    'label'  => 'Renovar: ' . ($mp['policy_number'] ?? ''),
                    'amount' => (float) $mp['total_premium_amount'],
                    'status' => 'info',
                    'ref'    => $mp['policy_number'] ?? '',
                ];
            }
        }

        // Mis reclamos activos
        $myClaims = $claimModel->getByUser($idUser);
        foreach ($myClaims as $mc) {
            if (in_array($mc['status'] ?? '', ['Pendiente', 'Activo'])) {
                $calendarEvents[] = [
                    'date'   => $mc['date_claim'],
                    'type'   => 'reclamo',
                    'label'  => 'Reclamo: ' . ($mc['claim_number'] ?? ''),
                    'amount' => (float) $mc['amount_claimed'],
                    'status' => $mc['status'],
                    'ref'    => $mc['claim_number'] ?? '',
                ];
            }
        }

        // Mis eventos manuales
        $manualEvents = $eventModel->getForCalendarByUser($idUser);
        foreach ($manualEvents as $me) {
            $calendarEvents[] = [
                'date'   => $me['date'],
                'type'   => $me['type'],
                'label'  => $me['label'],
                'amount' => 0,
                'status' => $me['status'],
                'ref'    => '',
                'id'     => $me['id'] ?? null,
            ];
        }

        $totalDue  = array_sum(array_column($schedules, 'amount_due'));
        $totalPaid = array_sum(array_column($schedules, 'amount_paid'));

        require_once ROOT_PATH . '/app/views/cliente/payment_calendar.php';
    }

    public function agenda(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/AgendaTask.php';

        $idUser = $_SESSION['id_user'];

        $agendaModel = new AgendaTask();
        $tasks   = $agendaModel->getForClient($idUser);
        $stats   = $agendaModel->getTaskStats($idUser);
        $history = $agendaModel->getActivityHistory($idUser);

        require_once ROOT_PATH . '/app/views/cliente/agenda.php';
    }
    public function form(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/FormType.php';
        require_once ROOT_PATH . '/app/models/FormSubmission.php';

        $formTypeModel   = new FormType();
        $submissionModel = new FormSubmission();

        $formTypes   = $formTypeModel->getActiveForClient();
        $submissions = $submissionModel->getByUser($_SESSION['id_user']);

        require_once ROOT_PATH . '/app/views/cliente/form.php';
    }

    public function formDetail(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/FormType.php';
        require_once ROOT_PATH . '/app/models/FormBuilder.php';
        require_once ROOT_PATH . '/app/models/FormSubmission.php';

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ?url=cliente/form');
            exit;
        }

        $formTypeModel   = new FormType();
        $builderModel    = new FormBuilder();
        $submissionModel = new FormSubmission();

        $formType = $formTypeModel->findById($id);
        if (!$formType || !$formType['status'] || !$formType['allow_digital_fill']) {
            header('Location: ?url=cliente/form');
            exit;
        }

        $structure   = $builderModel->getStructure($formType['id_current_version']);
        $prevSubmissions = $submissionModel->getByVersionAndUser($formType['id_current_version'], $_SESSION['id_user']);

        $dependencies = [];
        if (!empty($formType['id_current_version'])) {
            try {
                $db = new Database();
                $conn = $db->connect();
                $depStmt = $conn->prepare("SELECT * FROM form_field_dependency WHERE id_source_field IN (SELECT id_form_field FROM form_field WHERE id_form_version = :vid)");
                $depStmt->execute([':vid' => $formType['id_current_version']]);
                $dependencies = $depStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }

        require_once ROOT_PATH . '/app/views/cliente/form_detail.php';
    }

    public function services(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Service.php';
        require_once ROOT_PATH . '/app/models/ServiceCategory.php';

        $serviceModel = new Service();
        $services     = $serviceModel->getAll();

        $activeServices   = array_filter($services, fn($s) => ($s['status'] ?? 1) == 1);
        $serviceCategories = [];
        foreach ($activeServices as $s) {
            $cat = $s['category_name'] ?? 'Sin categoría';
            $serviceCategories[$cat][] = $s;
        }

        require_once ROOT_PATH . '/app/views/cliente/services.php';
    }

    public function reports(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/Claim.php';
        require_once ROOT_PATH . '/app/models/PaymentTransaction.php';

        $idUser = $_SESSION['id_user'];

        $policyModel  = new Policy();
        $claimModel   = new Claim();
        $paymentModel = new PaymentTransaction();

        $policies = $policyModel->getByUser($idUser);
        $claims   = $claimModel->getByUser($idUser);
        $payments = $paymentModel->getAllByUser($idUser);

        /* ── Filtro de rango de fechas (aplica a Pagos y Siniestros) ──── */
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        $paymentsFiltered = array_values(array_filter($payments, function ($p) use ($desde, $hasta) {
            if (empty($p['payment_date'])) return true;
            if ($desde && $p['payment_date'] < $desde) return false;
            if ($hasta && $p['payment_date'] > $hasta) return false;
            return true;
        }));

        $claimsFiltered = array_values(array_filter($claims, function ($c) use ($desde, $hasta) {
            if (empty($c['date_claim'])) return true;
            if ($desde && $c['date_claim'] < $desde) return false;
            if ($hasta && $c['date_claim'] > $hasta) return false;
            return true;
        }));

        /* ── KPIs generales ────────────────────────────────────────────── */
        $activePolicies   = array_values(array_filter($policies, fn($p) => $p['status'] === 'Activo'));
        $totalPrimaAnual  = array_sum(array_map(fn($p) => (float)$p['total_premium_amount'], $activePolicies));
        $totalPagadoConf  = array_sum(array_map(fn($p) => (float)$p['amount'], array_filter($paymentsFiltered, fn($p) => (int)$p['confirmed'] === 1)));
        $openClaims       = array_values(array_filter($claims, fn($c) => in_array($c['status'], ['Pendiente', 'Activo'])));

        /* ── Pólizas por tipo de seguro (para gráfica de dona) ──────────── */
        $policiesByType = [];
        foreach ($policies as $p) {
            $label = $p['insurance_type_name'] ?? 'Otro';
            $policiesByType[$label] = ($policiesByType[$label] ?? 0) + 1;
        }

        /* ── Pagos confirmados de los últimos 6 meses (para gráfica de barras) ── */
        $paymentsByMonth = [];
        $mesesCortos = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
        for ($i = 5; $i >= 0; $i--) {
            $ref = new DateTime("first day of -{$i} months");
            $key = $ref->format('Y-m');
            $paymentsByMonth[$key] = ['label' => $mesesCortos[(int)$ref->format('n') - 1] . ' ' . $ref->format('y'), 'total' => 0.0];
        }
        foreach ($payments as $p) {
            if (empty($p['payment_date']) || (int)$p['confirmed'] !== 1) continue;
            $key = substr($p['payment_date'], 0, 7);
            if (isset($paymentsByMonth[$key])) {
                $paymentsByMonth[$key]['total'] += (float)$p['amount'];
            }
        }

        /* ── Siniestros por estado (para gráfica de dona) ────────────────── */
        $claimsByStatus = ['Pendiente' => 0, 'Reportado' => 0, 'Activo' => 0, 'En proceso' => 0, 'Aprobado' => 0, 'Inactivo' => 0, 'Cerrado' => 0, 'Rechazado' => 0];
        foreach ($claims as $c) {
            $s = $c['status'] ?? 'Pendiente';
            $claimsByStatus[$s] = ($claimsByStatus[$s] ?? 0) + 1;
        }

        require_once ROOT_PATH . '/app/views/cliente/reports.php';
    }

    /** Descarga en CSV del reporte seleccionado (pólizas, pagos o siniestros) */
    public function reportsExport(){
        $this->requireAuth();

        $idUser = $_SESSION['id_user'];
        $tipo   = $_GET['tipo'] ?? 'policies';

        $filename = 'reporte-' . $tipo . '-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel muestre bien los acentos

        if ($tipo === 'payments') {
            require_once ROOT_PATH . '/app/models/PaymentTransaction.php';
            $rows = (new PaymentTransaction())->getAllByUser($idUser);
            fputcsv($out, ['Fecha', 'Póliza', 'Cuota', 'Método de pago', 'Monto', 'Moneda', 'Estado']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['payment_date'],
                    $r['policy_number'],
                    $r['installment_number'],
                    $r['payment_method'],
                    $r['amount'],
                    $r['currency'],
                    (int)$r['confirmed'] === 1 ? 'Confirmado' : 'Pendiente de confirmación',
                ]);
            }
        } elseif ($tipo === 'claims') {
            require_once ROOT_PATH . '/app/models/Claim.php';
            $rows = (new Claim())->getByUser($idUser);
            fputcsv($out, ['N.º de siniestro', 'Póliza', 'Tipo', 'Fecha reportado', 'Monto reclamado', 'Estado', 'Fecha de resolución']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['claim_number'],
                    $r['policy_number'],
                    $r['claim_type'],
                    $r['date_claim'],
                    $r['amount_claimed'],
                    $r['status'],
                    $r['resolution_date'],
                ]);
            }
        } else {
            require_once ROOT_PATH . '/app/models/Policy.php';
            $rows = (new Policy())->getByUser($idUser);
            fputcsv($out, ['N.º de póliza', 'Tipo de seguro', 'Aseguradora', 'Vigencia inicio', 'Vigencia fin', 'Prima anual', 'Estado']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['policy_number'],
                    $r['insurance_type_name'],
                    $r['insurance_company_name'],
                    $r['date_start'],
                    $r['date_expiration'],
                    $r['total_premium_amount'],
                    $r['status'],
                ]);
            }
        }

        fclose($out);
        exit;
    }

    public function quotes(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Quote.php';
        require_once ROOT_PATH . '/app/models/InsuranceCompany.php';
        require_once ROOT_PATH . '/app/models/InsuranceType.php';

        $idUser = $_SESSION['id_user'];

        $quoteModel   = new Quote();
        $companyModel = new InsuranceCompany();
        $typeModel    = new InsuranceType();

        $quotes         = $quoteModel->getByUser($idUser);
        $companies      = $companyModel->getActive();
        $insuranceTypes = $typeModel->getActive();

        $pendientes = count(array_filter($quotes, fn($q) => in_array($q['status'], ['Pendiente', 'Enviada', 'En análisis'], true)));
        $aceptadas  = count(array_filter($quotes, fn($q) => $q['status'] === 'Aceptada'));

        require_once ROOT_PATH . '/app/views/cliente/quotes.php';
    }

    public function profile(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/User.php';
        require_once ROOT_PATH . '/app/models/Address.php';
        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/Claim.php';

        $idUser = $_SESSION['id_user'];

        $userModel    = new User();
        $addressModel = new Address();
        $policyModel  = new Policy();
        $claimModel   = new Claim();

        $user = $userModel->findById($idUser);

        $departments    = $addressModel->getDepartments();
        $municipalities = $addressModel->getMunicipalities();
        $localities     = $addressModel->getLocalitiesCatalog();

        $policies = $policyModel->getByUser($idUser);
        $claims   = $claimModel->getByUser($idUser);

        $activePoliciesCount = count(array_filter($policies, fn($p) => $p['status'] === 'Activo'));
        $openClaimsCount     = count(array_filter($claims, fn($c) => in_array($c['status'], ['Pendiente', 'Activo'])));

        require_once ROOT_PATH . '/app/views/cliente/profile.php';
    }

    public function settings(){
        $this->requireAuth();
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        $user = $userModel->findById($_SESSION['id_user']);
        require_once ROOT_PATH . '/app/views/cliente/settings.php';
    }

    public function notifications(){
        $this->requireAuth();

        require_once ROOT_PATH . '/app/models/Notification.php';

        $idUser = $_SESSION['id_user'];

        $notificationModel = new Notification();
        $notifications = $notificationModel->getByUser($idUser);
        $unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));

        require_once ROOT_PATH . '/app/views/cliente/notifications.php';
    }
}