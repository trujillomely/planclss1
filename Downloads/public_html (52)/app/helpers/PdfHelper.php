<?php

require_once ROOT_PATH . '/app/models/Policy.php';
require_once ROOT_PATH . '/app/models/DigitalSignature.php';

class PdfHelper {

    public function generatePolicyPdf($idPolicy){
        try {
            $policyModel = new Policy();
            $policy = $policyModel->getWithSignature($idPolicy);
            if (!$policy) {
                return ['success' => false, 'message' => 'Póliza no encontrada.'];
            }

            $html = $this->buildPolicyHtml($policy);

            $autoloadPath = ROOT_PATH . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('letter', 'portrait');
                $dompdf->render();

                $dir = ROOT_PATH . '/assets/pdfs/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'poliza_' . $policy['policy_number'] . '.pdf';
                $filepath = $dir . $filename;
                file_put_contents($filepath, $dompdf->output());

                return [
                    'success'  => true,
                    'url'      => 'assets/pdfs/' . $filename,
                    'filename' => $filename,
                ];
            }

            $dir = ROOT_PATH . '/assets/pdfs/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'poliza_' . $policy['policy_number'] . '.html';
            $filepath = $dir . $filename;
            file_put_contents($filepath, $html);

            return [
                'success'  => true,
                'url'      => 'assets/pdfs/' . $filename,
                'filename' => $filename,
            ];
        } catch (Exception $e) {
            error_log('PdfHelper::generatePolicyPdf error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return ['success' => false, 'message' => 'Error al generar el PDF: ' . $e->getMessage()];
        }
    }

    private function signatureToBase64($sigUrl){
        $filePath = ROOT_PATH . '/' . $sigUrl;
        if (!file_exists($filePath)) return '';
        $mime = mime_content_type($filePath);
        if (!$mime) $mime = 'image/png';
        $data = file_get_contents($filePath);
        if ($data === false) return '';
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    private function buildPolicyHtml($policy){
        $sigDataUri = '';
        $sigName = '';
        $sigDate = '';
        $sigHash = '';
        if (!empty($policy['signature'])) {
            $sig = $policy['signature'];
            $sigDataUri = $this->signatureToBase64($sig['signature_url']);
            $sigName = htmlspecialchars($sig['signer_name']);
            $sigDate = date('d/m/Y H:i', strtotime($sig['signed_at']));
            $sigHash = htmlspecialchars($sig['signature_hash']);
        }

        $coverages = '';
        if (!empty($policy['coverages'])) {
            foreach ($policy['coverages'] as $cov) {
                $coverages .= '<tr>
                    <td>' . htmlspecialchars($cov['coverage_name']) . '</td>
                    <td>' . htmlspecialchars($cov['coverage_description'] ?? '—') . '</td>
                    <td>Q ' . number_format($cov['coverage_amount'], 2) . '</td>
                    <td>Q ' . number_format($cov['deductible'], 2) . '</td>
                </tr>';
            }
        }

        $beneficiaries = '';
        if (!empty($policy['beneficiaries'])) {
            foreach ($policy['beneficiaries'] as $b) {
                $beneficiaries .= '<tr>
                    <td>' . htmlspecialchars($b['full_name']) . '</td>
                    <td>' . htmlspecialchars($b['relationship'] ?? '—') . '</td>
                    <td>' . htmlspecialchars($b['benefit_percentage']) . '%</td>
                </tr>';
            }
        }

        $signatureSection = '';
        if ($sigDataUri) {
            $signatureSection = '<div class="signature-section">
                <h2>Firma Digital</h2>
                <div class="signature-box">
                    <img src="' . $sigDataUri . '" class="signature-img" alt="Firma digital">
                    <div class="signature-details">
                        <p><strong>Firmado por:</strong> ' . $sigName . '</p>
                        <p><strong>Fecha:</strong> ' . $sigDate . '</p>
                        <p><strong>Tipo:</strong> ' . htmlspecialchars($policy['signature']['signature_type'] ?? '—') . '</p>
                        <p class="hash"><strong>Hash SHA-256:</strong><br>' . $sigHash . '</p>
                    </div>
                </div>
            </div>';
        }

        $coveragesSection = '';
        if ($coverages) {
            $coveragesSection = '<div class="section"><h2>Coberturas</h2><table><thead><tr><th>Cobertura</th><th>Descripción</th><th>Monto</th><th>Deducible</th></tr></thead><tbody>' . $coverages . '</tbody></table></div>';
        }

        $beneficiariesSection = '';
        if ($beneficiaries) {
            $beneficiariesSection = '<div class="section"><h2>Beneficiarios</h2><table><thead><tr><th>Nombre</th><th>Parentesco</th><th>% Beneficio</th></tr></thead><tbody>' . $beneficiaries . '</tbody></table></div>';
        }

        $coverageSummarySection = '';
        if (!empty($policy['coverage_summary'])) {
            $coverageSummarySection = '<div class="section"><h2>Resumen de Cobertura</h2><p>' . nl2br(htmlspecialchars($policy['coverage_summary'])) . '</p></div>';
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 11pt; color: #1a1a2e; margin: 40px; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3B6178; padding-bottom: 20px; }
    .header h1 { font-size: 22pt; color: #3B6178; margin: 0; }
    .header p { font-size: 10pt; color: #6b7280; margin: 4px 0 0; }
    .section { margin-bottom: 20px; }
    .section h2 { font-size: 13pt; color: #3B6178; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 10px; }
    .field { display: flex; margin-bottom: 6px; }
    .field-label { font-weight: 600; width: 180px; color: #4a4a52; }
    .field-value { flex: 1; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10pt; }
    th { background: #f3f4f6; text-align: left; padding: 8px; border: 1px solid #e5e7eb; font-weight: 600; }
    td { padding: 8px; border: 1px solid #e5e7eb; }
    .signature-section { margin-top: 40px; page-break-inside: avoid; }
    .signature-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; display: flex; gap: 30px; align-items: flex-start; }
    .signature-img { max-width: 220px; height: auto; border: 1px solid #e5e7eb; padding: 8px; background: #fff; }
    .signature-details { flex: 1; font-size: 10pt; }
    .signature-details p { margin: 4px 0; }
    .signature-details .hash { font-family: monospace; font-size: 8pt; color: #6b7280; word-break: break-all; }
    .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
</style>
</head>
<body>
    <div class="header">
        <h1>ARCO SEGUROS</h1>
        <p>Sistema de Gesti&oacute;n de Seguros</p>
        <p>P&oacute;liza de Seguro &mdash; ' . htmlspecialchars($policy['policy_number']) . '</p>
    </div>

    <div class="section">
        <h2>Datos Generales</h2>
        <div class="field"><span class="field-label">N&uacute;mero de p&oacute;liza:</span><span class="field-value">' . htmlspecialchars($policy['policy_number']) . '</span></div>
        <div class="field"><span class="field-label">Cliente:</span><span class="field-value">' . htmlspecialchars($policy['user_name'] ?? '—') . '</span></div>
        <div class="field"><span class="field-label">Aseguradora:</span><span class="field-value">' . htmlspecialchars($policy['insurance_company_name'] ?? '—') . '</span></div>
        <div class="field"><span class="field-label">Tipo de seguro:</span><span class="field-value">' . htmlspecialchars($policy['insurance_type_name'] ?? '—') . '</span></div>
        <div class="field"><span class="field-label">Productor:</span><span class="field-value">' . htmlspecialchars($policy['producer_name'] ?? '—') . '</span></div>
        <div class="field"><span class="field-label">Fecha de inicio:</span><span class="field-value">' . (!empty($policy['date_start']) ? date('d/m/Y', strtotime($policy['date_start'])) : '—') . '</span></div>
        <div class="field"><span class="field-label">Fecha de vencimiento:</span><span class="field-value">' . (!empty($policy['date_expiration']) ? date('d/m/Y', strtotime($policy['date_expiration'])) : '—') . '</span></div>
        <div class="field"><span class="field-label">Prima total:</span><span class="field-value">Q ' . number_format($policy['total_premium_amount'] ?? 0, 2) . '</span></div>
        <div class="field"><span class="field-label">Cobertura total:</span><span class="field-value">Q ' . number_format($policy['total_coverage_amount'] ?? 0, 2) . '</span></div>
        <div class="field"><span class="field-label">Deducible total:</span><span class="field-value">Q ' . number_format($policy['total_deductible_amount'] ?? 0, 2) . '</span></div>
        <div class="field"><span class="field-label">Estado:</span><span class="field-value">' . htmlspecialchars($policy['status']) . '</span></div>
    </div>

    ' . $coverageSummarySection . '
    ' . $coveragesSection . '
    ' . $beneficiariesSection . '
    ' . $signatureSection . '

    <div class="footer">
        <p>Arco Seguros &mdash; Documento generado el ' . date('d/m/Y \a\s H:i') . '</p>
    </div>
</body></html>';
    }
}