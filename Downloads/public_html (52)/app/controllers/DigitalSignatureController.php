<?php

require_once ROOT_PATH . '/app/models/DigitalSignature.php';
require_once ROOT_PATH . '/app/models/Policy.php';

class DigitalSignatureController {

    private function jsonResponse($data, $code = 200){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function requireAuth(){
        if (!isset($_SESSION['id_user'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión.'], 401);
        }
        return (int) $_SESSION['id_user'];
    }

    public function sign(){
        header('Content-Type: application/json; charset=utf-8');
        try {
            $uid = $this->requireAuth();
            $body = json_decode(file_get_contents('php://input'), true);

            $idPolicy    = intval($body['id_policy'] ?? 0);
            $sigType     = $body['signature_type'] ?? 'canvas';
            $sigData     = $body['signature_data'] ?? '';
            $signerName  = trim($body['signer_name'] ?? '');

            if (!$idPolicy || !$sigData || !$signerName) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos.']);
            }
            if (!in_array($sigType, ['canvas', 'token'], true)) {
                $this->jsonResponse(['success' => false, 'message' => 'Tipo de firma inválido.']);
            }

            $policyModel = new Policy();
            $policy = $policyModel->belongsToUser($idPolicy, $uid);
            if (!$policy) {
                $this->jsonResponse(['success' => false, 'message' => 'Póliza no encontrada o no te pertenece.']);
            }

            if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
                $sigData = substr($sigData, strpos($sigData, ',') + 1);
            }
            $sigData = base64_decode($sigData);
            if ($sigData === false) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos de firma inválidos.']);
            }

            $dir = ROOT_PATH . '/assets/img/signatures/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = 'sig_' . $idPolicy . '_' . $uid . '_' . time() . '.png';
            $written = file_put_contents($dir . $filename, $sigData);
            if ($written === false) {
                error_log('DigitalSignature: No se pudo escribir archivo de firma en ' . $dir . $filename);
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el archivo de firma. Verifica permisos del servidor.']);
            }

            $hash = hash_file('sha256', $dir . $filename);

            require_once ROOT_PATH . '/app/models/User.php';
            $userModel = new User();
            $user = $userModel->findById($uid);

            $sigModel = new DigitalSignature();
            $result = $sigModel->create([
                'id_policy'      => $idPolicy,
                'id_user'        => $uid,
                'signature_type' => $sigType,
                'signature_url'  => 'assets/img/signatures/' . $filename,
                'signature_hash' => $hash,
                'signer_name'    => $signerName,
                'signer_email'   => $user['email'] ?? null,
                'signer_dpi'     => $user['dpi'] ?? null,
                'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            if ($result) {
                try {
                    $policyModel->updateSignatureStatus($idPolicy, 'Firmada');
                } catch (Exception $e) {
                    error_log('DigitalSignature: Error actualizando signature_status: ' . $e->getMessage());
                }
                $this->jsonResponse(['success' => true, 'message' => 'Póliza firmada correctamente.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar la firma en base de datos.']);
            }
        } catch (Exception $e) {
            error_log('DigitalSignature::sign error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->jsonResponse(['success' => false, 'message' => 'Error interno al procesar la firma.'], 500);
        }
    }

    public function getByPolicy(){
        $this->requireAuth();
        $idPolicy = intval($_GET['id_policy'] ?? 0);
        if (!$idPolicy) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        }
        $sigModel = new DigitalSignature();
        $sig = $sigModel->getByPolicy($idPolicy);
        $this->jsonResponse(['success' => true, 'data' => $sig]);
    }

    public function verify(){
        $this->requireAuth();
        $idSig = intval($_GET['id'] ?? 0);
        $hash  = $_GET['hash'] ?? '';
        if (!$idSig || !$hash) {
            $this->jsonResponse(['success' => false, 'message' => 'Parámetros inválidos.']);
        }
        $sigModel = new DigitalSignature();
        $result = $sigModel->verify($idSig, $hash);
        $this->jsonResponse($result);
    }

    public function downloadPdf(){
        header('Content-Type: application/json; charset=utf-8');
        try {
            $uid = $this->requireAuth();
            $idPolicy = intval($_GET['id_policy'] ?? 0);
            if (!$idPolicy) {
                $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
            }
            $policyModel = new Policy();
            if (!$policyModel->belongsToUser($idPolicy, $uid)) {
                $this->jsonResponse(['success' => false, 'message' => 'Póliza no encontrada o no te pertenece.']);
            }
            require_once ROOT_PATH . '/app/helpers/PdfHelper.php';
            $pdfHelper = new PdfHelper();
            $result = $pdfHelper->generatePolicyPdf($idPolicy);
            if ($result['success']) {
                $this->jsonResponse(['success' => true, 'url' => $result['url'], 'filename' => $result['filename']]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']]);
            }
        } catch (Exception $e) {
            error_log('DigitalSignature::downloadPdf error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->jsonResponse(['success' => false, 'message' => 'Error interno al generar el PDF.'], 500);
        }
    }

    public function previewPdf(){
        try {
            $uid = $this->requireAuth();
            $idPolicy = intval($_GET['id_policy'] ?? 0);
            if (!$idPolicy) {
                http_response_code(400);
                echo 'ID inválido.';
                exit;
            }
            require_once ROOT_PATH . '/app/helpers/PdfHelper.php';
            $pdfHelper = new PdfHelper();
            $result = $pdfHelper->generatePolicyPdf($idPolicy);
            if ($result['success']) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $result['filename'] . '"');
                readfile(ROOT_PATH . '/' . $result['url']);
            } else {
                http_response_code(500);
                echo $result['message'];
            }
        } catch (Exception $e) {
            error_log('DigitalSignature::previewPdf error: ' . $e->getMessage());
            http_response_code(500);
            echo 'Error al generar el PDF.';
        }
        exit;
    }
}