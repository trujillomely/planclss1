<?php

require_once ROOT_PATH . '/app/models/InsuranceCompany.php';
require_once ROOT_PATH . '/app/models/Address.php';

class InsuranceCompanyController {

    public function getById(){
        Auth::requirePermissionAjax('aseguradoras', 'ver');
        header('Content-Type: application/json');

        $model  = new InsuranceCompany();
        $result = $model->findById(intval($_GET['id'] ?? 0));

        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aseguradora no encontrada.']);
        }
        exit;
    }

    // Ruta: admin/insurance-companies/detail?id=X
    public function getDetail(){
        Auth::requirePermissionAjax('aseguradoras', 'ver');
        header('Content-Type: application/json');

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new InsuranceCompany();
        $company = $model->findById($id);
        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Aseguradora no encontrada.']);
            exit;
        }

        require_once ROOT_PATH . '/app/models/Policy.php';
        require_once ROOT_PATH . '/app/models/Quote.php';
        require_once ROOT_PATH . '/app/models/Claim.php';
        require_once ROOT_PATH . '/app/models/Commission.php';

        $policyModel = new Policy();
        $policies = $policyModel->getByCompany($id);

        $quotes = (new Quote())->getByCompany($id);
        $claims = (new Claim())->getByCompany($id);

        $commissions = [];
        try {
            $commModel = new Commission();
            $commissions = $commModel->getByCompany($id);
        } catch (\Throwable $e) {}

        $totalPremium = 0;
        $totalCoverage = 0;
        foreach ($policies as $p) {
            $totalPremium += (float)($p['total_premium_amount'] ?? 0);
            $totalCoverage += (float)($p['total_coverage_amount'] ?? 0);
        }

        $data = [
            'company' => $company,
            'policies' => array_map(function($p) {
                return [
                    'id_policy' => $p['id_policy'],
                    'policy_number' => $p['policy_number'] ?? '—',
                    'client' => trim(($p['client_name'] ?? '') . ' ' . ($p['client_lastname'] ?? '')),
                    'type' => $p['insurance_type_name'] ?? '—',
                    'start' => $p['date_start'],
                    'expiration' => $p['date_expiration'],
                    'premium' => $p['total_premium_amount'],
                    'coverage' => $p['total_coverage_amount'],
                    'status' => $p['status'],
                ];
            }, $policies),
            'quotes' => array_map(function($q) {
                return [
                    'id_quote' => $q['id_quote'],
                    'folio' => $q['folio'] ?? '—',
                    'client' => $q['client_name'] ?? '—',
                    'type' => $q['insurance_type_name'] ?? '—',
                    'premium' => $q['estimated_premium'],
                    'status' => $q['status'],
                    'created_at' => $q['created_at'],
                ];
            }, $quotes),
            'claims' => array_map(function($c) {
                return [
                    'id_claim' => $c['id_claim'],
                    'claim_number' => $c['claim_number'] ?? '—',
                    'policy_number' => $c['policy_number'] ?? '—',
                    'type' => $c['claim_type'] ?? '—',
                    'amount' => $c['amount_claimed'],
                    'date' => $c['date_claim'],
                    'status' => $c['status'],
                ];
            }, $claims),
            'commissions' => array_map(function($cm) {
                return [
                    'policy_number' => $cm['policy_number'] ?? '—',
                    'amount' => $cm['commission_amount'] ?? 0,
                    'rate' => $cm['commission_rate'] ?? 0,
                    'status' => $cm['status'] ?? '—',
                    'created_at' => $cm['created_at'] ?? '',
                ];
            }, $commissions),
            'counts' => [
                'policies' => count($policies),
                'active_policies' => count(array_filter($policies, fn($p) => ($p['status'] ?? '') === 'Activo')),
                'quotes' => count($quotes),
                'claims' => count($claims),
                'commissions' => count($commissions),
            ],
            'totals' => [
                'premium' => $totalPremium,
                'coverage' => $totalCoverage,
            ],
        ];

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Ruta: admin/insurance-companies/store
    public function store(){
        Auth::requirePermissionAjax('aseguradoras', 'crear');
        header('Content-Type: application/json');

        $data = $_POST;

        if (empty(trim($data['name'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'El nombre de la aseguradora es obligatorio.']);
            exit;
        }

        if (!empty($data['email']) && !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
            exit;
        }

        $model = new InsuranceCompany();

        if ($model->nameExists(trim($data['name']))) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una aseguradora con ese nombre.']);
            exit;
        }

        if (!empty($data['nit']) && $model->nitExists(trim($data['nit']))) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una aseguradora registrada con ese NIT.']);
            exit;
        }

        $addressModel = new Address();
        $idAddress = $addressModel->createOrUpdate(null, $data);
        $data['id_address'] = $idAddress;

        $result = $model->create($data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Aseguradora creada exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la aseguradora.']);
        }
        exit;
    }

    // Ruta: admin/insurance-companies/update
    public function update(){
        Auth::requirePermissionAjax('aseguradoras', 'editar');
        header('Content-Type: application/json');

        $data = $_POST;
        $id   = intval($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de aseguradora inválido.']);
            exit;
        }
        if (empty(trim($data['name'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'El nombre de la aseguradora es obligatorio.']);
            exit;
        }
        if (!empty($data['email']) && !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
            exit;
        }

        $model = new InsuranceCompany();
        $current = $model->findById($id);

        if (!$current) {
            echo json_encode(['success' => false, 'message' => 'Aseguradora no encontrada.']);
            exit;
        }

        if ($model->nameExists(trim($data['name']), $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra aseguradora con ese nombre.']);
            exit;
        }

        if (!empty($data['nit']) && $model->nitExists(trim($data['nit']), $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra aseguradora registrada con ese NIT.']);
            exit;
        }

        $addressModel = new Address();
        $idAddress = $addressModel->createOrUpdate($current['id_address'] ?? null, $data);
        $data['id_address'] = $idAddress;

        $result = $model->update($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Aseguradora actualizada exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la aseguradora.']);
        }
        exit;
    }

    // Ruta: admin/insurance-companies/delete (desactiva, no elimina físicamente)
    public function delete(){
        Auth::requirePermissionAjax('aseguradoras', 'eliminar');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $id   = intval($json['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model   = new InsuranceCompany();
        $company = $model->findById($id);

        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Aseguradora no encontrada.']);
            exit;
        }

        $policiesCount = $model->countPolicies($id);
        if ($policiesCount > 0 && empty($json['force']) && empty($_POST['force'])) {
            echo json_encode([
                'success' => false,
                'message' => "Esta aseguradora tiene {$policiesCount} póliza(s) asociada(s). ¿Deseas desactivarla de todas formas?",
                'requires_confirmation' => true,
                'policies_count' => $policiesCount,
            ]);
            exit;
        }

        $result = $model->deactivate($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Aseguradora desactivada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al desactivar la aseguradora.']);
        }
        exit;
    }

    // Ruta: admin/insurance-companies/reactivate
    public function reactivate(){
        Auth::requirePermissionAjax('aseguradoras', 'editar');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        $id   = intval($json['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $model = new InsuranceCompany();
        if (!$model->findById($id)) {
            echo json_encode(['success' => false, 'message' => 'Aseguradora no encontrada.']);
            exit;
        }

        $result = $model->activate($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Aseguradora reactivada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al reactivar la aseguradora.']);
        }
        exit;
    }
}
