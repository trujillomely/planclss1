<?php

class UserProfilePdfHelper {

    private static string $blanco   = '#F8F6F3';
    private static string $perla    = '#E2DCDA';
    private static string $lino     = '#F1EBE1';
    private static string $siena    = '#8C7B6E';
    private static string $carbon   = '#1C1C1E';
    private static string $dorado   = '#f2bf4a';
    private static string $muted    = '#7A7268';

    public static function generate(array $data): void {
        $html = self::buildHtml($data);

        require_once ROOT_PATH . '/vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $user = $data['user'];
        $slug = self::slug(($user['username'] ?? '') . '_' . ($user['lastname'] ?? ''));
        $filename = 'perfil_' . $slug . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private static function initials(string $name): string {
        $parts = array_filter(explode(' ', trim($name)));
        $ini = '';
        if (isset($parts[0])) $ini .= mb_strtoupper(mb_substr($parts[0], 0, 1));
        if (isset($parts[1])) $ini .= mb_strtoupper(mb_substr($parts[1], 0, 1));
        if (strlen($ini) < 2 && isset($parts[0])) $ini = mb_strtoupper(mb_substr($parts[0], 0, 2));
        return $ini ?: 'US';
    }

    private static function buildHtml(array $data): string {
        $u = $data['user'];
        $c = $data['counts'];
        $fullName = trim(($u['username'] ?? '') . ' ' . ($u['lastname'] ?? ''));
        $ini = self::initials($fullName);
        $genre = ($u['genre'] ?? '') === 'M' ? 'Masculino' : (($u['genre'] ?? '') === 'F' ? 'Femenino' : '—');
        $status = ($u['status'] ?? 0) == 1 ? 'Activo' : 'Inactivo';
        $statusColor = ($u['status'] ?? 0) == 1 ? '#2d7a4f' : '#b91c1c';
        $regDate = !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—';
        $updDate = !empty($u['updated_at']) ? date('d/m/Y', strtotime($u['updated_at'])) : '—';
        $birth   = !empty($u['birth_date']) ? date('d/m/Y', strtotime($u['birth_date'])) : '—';

        $address = '';
        if (!empty($u['department_name']) || !empty($u['municipality_name']) || !empty($u['address_line1'])) {
            $parts = [];
            if (!empty($u['address_line1'])) $parts[] = htmlspecialchars($u['address_line1']);
            if (!empty($u['address_line2'])) $parts[] = htmlspecialchars($u['address_line2']);
            $addrLine = implode(', ', $parts);
            $locParts = [];
            if (!empty($u['locality_name'])) $locParts[] = htmlspecialchars($u['locality_name']);
            if (!empty($u['municipality_name'])) $locParts[] = htmlspecialchars($u['municipality_name']);
            if (!empty($u['department_name'])) $locParts[] = htmlspecialchars($u['department_name']);
            $location = implode(', ', $locParts);
            $postal = !empty($u['postal_code']) ? ' — CP: ' . htmlspecialchars($u['postal_code']) : '';

            $address = '<div class="card" style="margin-top:12px;">
                <div class="card-header"><span class="card-icon">&#9906;</span> Direccion</div>
                <div class="card-body">
                    <div class="detail-row"><span class="detail-label">Ubicacion:</span><span class="detail-value">' . $location . $postal . '</span></div>
                    <div class="detail-row"><span class="detail-label">Direccion:</span><span class="detail-value">' . $addrLine . '</span></div>
                </div>
            </div>';
        }

        $policiesRows = '';
        if (!empty($data['policies'])) {
            foreach ($data['policies'] as $p) {
                $exp = !empty($p['expiration']) ? date('d/m/Y', strtotime($p['expiration'])) : '—';
                $policiesRows .= '<tr>
                    <td>' . htmlspecialchars($p['policy_number']) . '</td>
                    <td>' . htmlspecialchars($p['company']) . '</td>
                    <td>' . htmlspecialchars($p['type']) . '</td>
                    <td>' . $exp . '</td>
                    <td class="money">Q ' . number_format((float)($p['premium'] ?? 0), 2) . '</td>
                    <td class="money">Q ' . number_format((float)($p['coverage'] ?? 0), 2) . '</td>
                    <td>' . htmlspecialchars($p['status']) . '</td>
                </tr>';
            }
        }

        $quotesRows = '';
        if (!empty($data['quotes'])) {
            foreach ($data['quotes'] as $q) {
                $created = !empty($q['created_at']) ? date('d/m/Y', strtotime($q['created_at'])) : '—';
                $quotesRows .= '<tr>
                    <td>' . htmlspecialchars($q['folio']) . '</td>
                    <td>' . htmlspecialchars($q['company']) . '</td>
                    <td>' . htmlspecialchars($q['type']) . '</td>
                    <td class="money">Q ' . number_format((float)($q['premium'] ?? 0), 2) . '</td>
                    <td>' . $created . '</td>
                    <td>' . htmlspecialchars($q['status']) . '</td>
                </tr>';
            }
        }

        $claimsRows = '';
        if (!empty($data['claims'])) {
            foreach ($data['claims'] as $cl) {
                $clDate = !empty($cl['date']) ? date('d/m/Y', strtotime($cl['date'])) : '—';
                $claimsRows .= '<tr>
                    <td>' . htmlspecialchars($cl['claim_number']) . '</td>
                    <td>' . htmlspecialchars($cl['policy_number']) . '</td>
                    <td>' . htmlspecialchars($cl['type']) . '</td>
                    <td class="money">Q ' . number_format((float)($cl['amount'] ?? 0), 2) . '</td>
                    <td>' . $clDate . '</td>
                    <td>' . htmlspecialchars($cl['status']) . '</td>
                </tr>';
            }
        }

        $renewalsRows = '';
        if (!empty($data['renewals'])) {
            foreach ($data['renewals'] as $r) {
                $exp = !empty($r['expiration']) ? date('d/m/Y', strtotime($r['expiration'])) : '—';
                $renewalsRows .= '<tr>
                    <td>' . htmlspecialchars($r['policy_number']) . '</td>
                    <td>' . $exp . '</td>
                    <td>' . (int)$r['days'] . '</td>
                    <td class="money">Q ' . number_format((float)($r['premium'] ?? 0), 2) . '</td>
                    <td>' . htmlspecialchars($r['status']) . '</td>
                </tr>';
            }
        }

        $docsRows = '';
        if (!empty($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                $docsRows .= '<tr>
                    <td>' . htmlspecialchars($doc['name']) . '</td>
                    <td>' . htmlspecialchars($doc['type'] ?? '—') . '</td>
                    <td>' . htmlspecialchars($doc['policy_number']) . '</td>
                </tr>';
            }
        }

        $eventsRows = '';
        if (!empty($data['events'])) {
            foreach ($data['events'] as $ev) {
                $evDate = !empty($ev['date']) ? date('d/m/Y', strtotime($ev['date'])) : '—';
                $evTime = !empty($ev['time']) ? substr($ev['time'], 0, 5) : '—';
                $eventsRows .= '<tr>
                    <td>' . htmlspecialchars($ev['title']) . '</td>
                    <td>' . htmlspecialchars($ev['type']) . '</td>
                    <td>' . $evDate . '</td>
                    <td>' . $evTime . '</td>
                    <td>' . htmlspecialchars($ev['priority']) . '</td>
                    <td>' . htmlspecialchars($ev['status']) . '</td>
                </tr>';
            }
        }

        $policiesSection = '';
        if ($c['policies'] > 0) {
            $policiesSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#9733;</span> Polizas (' . $c['policies'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Numero</th><th>Aseguradora</th><th>Tipo</th><th>Vencimiento</th><th>Prima</th><th>Cobertura</th><th>Estado</th></tr></thead>
                    <tbody>' . $policiesRows . '</tbody></table>
                </div>
            </div>';
        }

        $quotesSection = '';
        if ($c['quotes'] > 0) {
            $quotesSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#9998;</span> Cotizaciones (' . $c['quotes'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Folio</th><th>Aseguradora</th><th>Tipo</th><th>Prima</th><th>Fecha</th><th>Estado</th></tr></thead>
                    <tbody>' . $quotesRows . '</tbody></table>
                </div>
            </div>';
        }

        $claimsSection = '';
        if ($c['claims'] > 0) {
            $claimsSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#9888;</span> Reclamos (' . $c['claims'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Numero</th><th>Poliza</th><th>Tipo</th><th>Monto</th><th>Fecha</th><th>Estado</th></tr></thead>
                    <tbody>' . $claimsRows . '</tbody></table>
                </div>
            </div>';
        }

        $renewalsSection = '';
        if ($c['renewals'] > 0) {
            $renewalsSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#8635;</span> Renovaciones (' . $c['renewals'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Poliza</th><th>Vencimiento</th><th>Dias</th><th>Prima</th><th>Estado</th></tr></thead>
                    <tbody>' . $renewalsRows . '</tbody></table>
                </div>
            </div>';
        }

        $docsSection = '';
        if ($c['documents'] > 0) {
            $docsSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#9993;</span> Documentos (' . $c['documents'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Nombre</th><th>Tipo</th><th>Poliza</th></tr></thead>
                    <tbody>' . $docsRows . '</tbody></table>
                </div>
            </div>';
        }

        $eventsSection = '';
        if ($c['events'] > 0) {
            $eventsSection = '<div class="card">
                <div class="card-header"><span class="card-icon">&#9782;</span> Eventos (' . $c['events'] . ')</div>
                <div class="card-body" style="padding:0;">
                    <table><thead><tr><th>Titulo</th><th>Tipo</th><th>Fecha</th><th>Hora</th><th>Prioridad</th><th>Estado</th></tr></thead>
                    <tbody>' . $eventsRows . '</tbody></table>
                </div>
            </div>';
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 12mm 14mm 16mm 14mm;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 9pt;
        color: ' . self::$carbon . ';
        line-height: 1.45;
        background: #fff;
    }

    /* ── HEADER ─────────────────────────────────────────── */
    .header {
        background: ' . self::$carbon . ';
        color: ' . self::$blanco . ';
        padding: 22px 28px 18px;
        margin: -12mm -14mm 0;
        position: relative;
    }
    .header::after {
        content: "";
        display: block;
        height: 4px;
        background: linear-gradient(90deg, ' . self::$siena . ', ' . self::$dorado . ');
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .header-brand {
        font-size: 17pt;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: ' . self::$dorado . ';
    }
    .header-subtitle {
        font-size: 8.5pt;
        color: ' . self::$siena . ';
        letter-spacing: 0.5px;
        margin-top: 1px;
    }
    .header-doc {
        font-size: 8pt;
        color: #999;
        text-align: right;
    }
    .header-doc strong {
        color: ' . self::$perla . ';
        font-size: 8.5pt;
    }

    /* ── PROFILE CARD ───────────────────────────────────── */
    .profile-card {
        display: flex;
        gap: 20px;
        margin: 20px 0 16px;
        page-break-inside: avoid;
    }
    .avatar-section {
        flex: 0 0 100px;
        text-align: center;
    }
    .avatar-circle {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: linear-gradient(135deg, ' . self::$siena . ', ' . self::$dorado . ');
        color: #fff;
        font-size: 26pt;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        letter-spacing: 1px;
    }
    .avatar-role {
        font-size: 7pt;
        color: ' . self::$siena . ';
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 600;
    }
    .avatar-status {
        display: inline-block;
        margin-top: 4px;
        font-size: 7pt;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 10px;
        color: #fff;
    }

    /* ── CARDS ──────────────────────────────────────────── */
    .card {
        border: 1px solid ' . self::$perla . ';
        border-radius: 8px;
        margin-bottom: 14px;
        page-break-inside: avoid;
        overflow: hidden;
    }
    .card-header {
        background: ' . self::$lino . ';
        padding: 8px 14px;
        font-size: 10pt;
        font-weight: 700;
        color: ' . self::$siena . ';
        border-bottom: 1px solid ' . self::$perla . ';
        letter-spacing: 0.3px;
    }
    .card-icon {
        margin-right: 5px;
    }
    .card-body {
        padding: 12px 14px;
    }

    /* ── DETAIL GRID ────────────────────────────────────── */
    .detail-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
    }
    .detail-row {
        display: flex;
        width: 50%;
        padding: 5px 0;
        border-bottom: 1px solid #f0ece8;
    }
    .detail-row:nth-child(odd) {
        padding-right: 10px;
    }
    .detail-row:nth-child(even) {
        padding-left: 10px;
    }
    .detail-label {
        font-weight: 600;
        color: ' . self::$muted . ';
        width: 120px;
        flex-shrink: 0;
        font-size: 8.5pt;
    }
    .detail-value {
        color: ' . self::$carbon . ';
        font-size: 8.5pt;
        font-weight: 500;
    }

    /* ── KPI STRIP ──────────────────────────────────────── */
    .kpi-strip {
        display: flex;
        gap: 8px;
        margin: 14px 0;
    }
    .kpi-item {
        flex: 1;
        background: ' . self::$lino . ';
        border: 1px solid ' . self::$perla . ';
        border-radius: 8px;
        padding: 10px 6px;
        text-align: center;
    }
    .kpi-number {
        font-size: 17pt;
        font-weight: 700;
        color: ' . self::$siena . ';
        line-height: 1;
    }
    .kpi-label {
        font-size: 6.5pt;
        color: ' . self::$muted . ';
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-top: 3px;
        font-weight: 600;
    }

    /* ── TABLES ─────────────────────────────────────────── */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    thead tr {
        background: ' . self::$carbon . ';
    }
    th {
        color: ' . self::$blanco . ';
        font-weight: 600;
        font-size: 7pt;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 7px 6px;
        text-align: left;
        border: none;
    }
    td {
        padding: 6px 6px;
        border-bottom: 1px solid ' . self::$perla . ';
        vertical-align: middle;
        color: ' . self::$carbon . ';
    }
    tr:nth-child(even) td {
        background: ' . self::$lino . ';
    }
    .money {
        text-align: right;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ── FOOTER ─────────────────────────────────────────── */
    .footer {
        margin-top: 24px;
        padding: 12px 0 0;
        border-top: 2px solid ' . self::$siena . ';
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .footer-brand {
        font-size: 8pt;
        color: ' . self::$siena . ';
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .footer-date {
        font-size: 7.5pt;
        color: ' . self::$muted . ';
    }
</style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-top">
            <div>
                <div class="header-brand">Arco Seguros</div>
                <div class="header-subtitle">Sistema de Gestion de Seguros</div>
            </div>
            <div class="header-doc">
                <strong>Ficha de Usuario</strong><br>
                Generado: ' . date('d/m/Y') . '
            </div>
        </div>
    </div>

    <!-- PROFILE CARD -->
    <div class="profile-card">
        <div class="avatar-section">
            <div class="avatar-circle">' . $ini . '</div>
            <div class="avatar-role">' . htmlspecialchars($u['role_name'] ?? '—') . '</div>
            <div class="avatar-status" style="background:' . $statusColor . ';">' . $status . '</div>
        </div>
        <div style="flex:1;">
            <div class="card">
                <div class="card-header"><span class="card-icon">&#9998;</span> Datos Personales</div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Nombre:</span><span class="detail-value">' . htmlspecialchars($fullName) . '</span></div>
                        <div class="detail-row"><span class="detail-label">Email:</span><span class="detail-value">' . htmlspecialchars($u['email'] ?? '—') . '</span></div>
                        <div class="detail-row"><span class="detail-label">DPI:</span><span class="detail-value">' . htmlspecialchars($u['dpi'] ?? '—') . '</span></div>
                        <div class="detail-row"><span class="detail-label">Telefono:</span><span class="detail-value">' . htmlspecialchars($u['phone'] ?? '—') . '</span></div>
                        <div class="detail-row"><span class="detail-label">Fecha nac.:</span><span class="detail-value">' . $birth . '</span></div>
                        <div class="detail-row"><span class="detail-label">Genero:</span><span class="detail-value">' . $genre . '</span></div>
                        <div class="detail-row"><span class="detail-label">Registro:</span><span class="detail-value">' . $regDate . '</span></div>
                        <div class="detail-row"><span class="detail-label">Actualizado:</span><span class="detail-value">' . $updDate . '</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    ' . $address . '

    <!-- KPI STRIP -->
    <div class="kpi-strip">
        <div class="kpi-item"><div class="kpi-number">' . $c['policies'] . '</div><div class="kpi-label">Polizas</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['active_policies'] . '</div><div class="kpi-label">Activas</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['quotes'] . '</div><div class="kpi-label">Cotizaciones</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['claims'] . '</div><div class="kpi-label">Reclamos</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['renewals'] . '</div><div class="kpi-label">Renovaciones</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['documents'] . '</div><div class="kpi-label">Documentos</div></div>
        <div class="kpi-item"><div class="kpi-number">' . $c['events'] . '</div><div class="kpi-label">Eventos</div></div>
    </div>

    ' . $policiesSection . '
    ' . $quotesSection . '
    ' . $claimsSection . '
    ' . $renewalsSection . '
    ' . $docsSection . '
    ' . $eventsSection . '

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-brand">Arco Seguros</div>
        <div class="footer-date">Documento generado el ' . date('d/m/Y \a\s H:i') . ' &mdash; Sistema de Gestion de Seguros</div>
    </div>

</body>
</html>';
    }

    private static function slug(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_');
    }
}
