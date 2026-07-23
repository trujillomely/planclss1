<?php
$submissions = $submissions ?? [];
$formTypes   = $formTypes ?? [];
$stats       = $stats ?? ['total' => 0, 'pendientes' => 0, 'revisados' => 0, 'rechazados' => 0, 'hoy' => 0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    
    <?= Csrf::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envios de Formularios | Panel <?= $panelLabel ?? 'Administrador' ?></title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/toast.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/client-form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <script>
    (function(){
        var meta = document.querySelector('meta[name="_csrf_token"]');
        if (!meta) return;
        var token = meta.getAttribute('content');
        var origFetch = window.fetch;
        window.fetch = function(url, opts) {
            if (opts && opts.method && opts.method.toUpperCase() !== 'GET') {
                if (opts.body && opts.body instanceof FormData) {
                    if (!opts.body.has('_csrf_token')) opts.body.append('_csrf_token', token);
                } else if (opts.headers && typeof opts.headers === 'object') {
                    var ct = '';
                    for (var k in opts.headers) {
                        if (k.toLowerCase() === 'content-type') ct = opts.headers[k];
                    }
                    if (ct.indexOf('application/json') !== -1 && typeof opts.body === 'string') {
                        try {
                            var obj = JSON.parse(opts.body);
                            if (!obj._csrf_token) { obj._csrf_token = token; opts.body = JSON.stringify(obj); }
                        } catch(e) {}
                    }
                }
            }
            return origFetch.apply(this, arguments);
        };
        window.safeJson = function(url, opts) {
            return fetch(url, opts).then(function(r) { return r.text(); }).then(function(text) {
                try { return JSON.parse(text); } catch(e) {
                    if (typeof showToast === 'function') showToast('Error de conexion. Intente nuevamente.', 'error');
                    return { success: false, message: 'Error de conexion.' };
                }
            });
        };
    })();
    </script>
</head>
<body>
<div class="dash-layout">
    <?php include ROOT_PATH . '/app/views/layouts/sidebar_' . ($panelPrefix ?? 'admin') . '.php'; ?>
    <div class="dash-main">

        <?php
        $pageTitle    = 'Envios de Formularios';
        $pageModule   = 'formularios';
        $pageSubtitle = 'Gestiona los envios recibidos de tus formularios';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">

            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="bi bi-inboxes"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total envios</div>
                        <div class="kpi-value"><?= $stats['total'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon warning"><i class="bi bi-hourglass-split"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Pendientes</div>
                        <div class="kpi-value"><?= $stats['pendientes'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Revisados</div>
                        <div class="kpi-value"><?= $stats['revisados'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon danger"><i class="bi bi-x-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Rechazados</div>
                        <div class="kpi-value"><?= $stats['rechazados'] ?></div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title">Listado de envios</div>
                        <div class="content-card-sub">Haz clic en un envio para ver su detalle</div>
                    </div>
                </div>
                <div class="content-card-body">
                    <div class="filter-bar">
                        <input type="text" id="searchSubmissions" placeholder="Buscar por nombre, formulario, numero..." oninput="searchTable(this.value)">
                    </div>
                    <div class="tbl-wrap">
                        <table class="admin-table" id="submissionsTable" data-pagination>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Numero</th>
                                    <th>Formulario</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($submissions)): ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td>#<?= (int)$sub['id_form_submission'] ?></td>
                                            <td><code><?= htmlspecialchars($sub['submission_number']) ?></code></td>
                                            <td class="fw"><?= htmlspecialchars($sub['form_name']) ?></td>
                                            <td><?= htmlspecialchars(($sub['username'] ?? '') . ' ' . ($sub['lastname'] ?? '')) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'] ?: $sub['created_at'])) ?></td>
                                            <td>
                                                <span class="submission-status-badge status-<?= strtolower($sub['status']) ?>">
                                                    <?= htmlspecialchars($sub['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn-icon" title="Ver detalle" onclick="viewSubmission(<?= (int)$sub['id_form_submission'] ?>)">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="bi bi-inboxes"></i>
                                                <p>No se han recibido envios todavía.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal-overlay" id="detailModal">
    <div class="modal-box" style="max-width:640px;">
        <div class="modal-header" style="border-top:3px solid var(--mod-accent, var(--arco-siena));">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena));display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 id="detailTitle" style="margin:0;font-size:1.05rem;">Detalle del envío</h3>
            </div>
            <button class="modal-close" onclick="closeDetail()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" id="detailBody" style="max-height:55vh;overflow-y:auto;padding:20px;"><p style="color:#9ca3af;text-align:center;padding:24px;">Cargando...</p></div>
        <div class="modal-footer" style="padding:16px 20px;border-top:1px solid var(--arco-perla);">
            <div class="admin-status-controls" id="statusControls" style="display:none;">
                <select id="statusSelect">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Enviado">Enviado</option>
                    <option value="Revisado">Revisado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
                <button onclick="updateStatus()" style="display:flex;align-items:center;gap:6px;"><i class="bi bi-check-lg"></i> Actualizar</button>
            </div>
            <button class="btn-secondary" onclick="closeDetail()">Cerrar</button>
        </div>
    </div>
</div>

<script>
let currentSubmissionId = null;

function searchTable(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('#submissionsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
}

function closeDetail() {
    document.getElementById('detailModal').classList.remove('open');
    currentSubmissionId = null;
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});

function viewSubmission(id) {
    currentSubmissionId = id;
    document.getElementById('detailBody').innerHTML = '<p>Cargando...</p>';
    document.getElementById('detailModal').classList.add('open');
    document.getElementById('statusControls').style.display = 'none';
    const prefix = window.location.search.includes('gerente') ? 'gerente' : 'admin';
    fetch('?url=' + prefix + '/form-submissions/detail&id=' + id)
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(data => {
            if (!data.success) { document.getElementById('detailBody').innerHTML = '<p>' + (data.message || 'Error') + '</p>'; return; }
            const sub = data.data;
            document.getElementById('detailTitle').textContent = 'Envio ' + sub.submission_number;
            let html = '<div class="submission-detail-grid">';
            html += '<div class="submission-detail-field"><div class="field-label">Formulario</div><div class="field-value">' + esc(sub.form_name) + ' v' + sub.version_number + '</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Usuario</div><div class="field-value">' + esc((sub.username||'')+' '+(sub.lastname||'')) + ' (' + esc(sub.user_email||'') + ')</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Fecha</div><div class="field-value">' + (sub.submitted_at || sub.created_at) + '</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Estado</div><div class="field-value"><span class="submission-status-badge status-' + sub.status.toLowerCase() + '">' + esc(sub.status) + '</span></div></div>';
            if (sub.review_notes) html += '<div class="submission-detail-field"><div class="field-label">Notas</div><div class="field-value">' + esc(sub.review_notes) + '</div></div>';
            html += '<div style="margin-top:8px;font-size:.85rem;font-weight:700;">Valores del formulario</div>';
            (sub.values || []).forEach(function(v) {
                let dv = v.field_value || '---';
                if (v.field_key === 'signature' && dv.indexOf('data:image') === 0) {
                    dv = '<img src="' + dv + '" style="max-width:200px;border:1px solid #e5e7eb;border-radius:4px;">';
                } else { dv = esc(dv); }
                html += '<div class="submission-detail-field"><div class="field-label">' + esc(v.field_label) + '</div><div class="field-value">' + dv + '</div></div>';
            });
            html += '</div>';
            document.getElementById('detailBody').innerHTML = html;
            document.getElementById('statusSelect').value = sub.status;
            document.getElementById('statusControls').style.display = 'flex';
        })
        .catch(() => { document.getElementById('detailBody').innerHTML = '<p>Error de conexion.</p>'; });
}

function updateStatus() {
    if (!currentSubmissionId) return;
    const status = document.getElementById('statusSelect').value;
    const prefix = window.location.search.includes('gerente') ? 'gerente' : 'admin';
    fetch('?url=' + prefix + '/form-submissions/update-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: currentSubmissionId, status: status })
    })
    .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
    .then(data => {
        if (data.success) { location.reload(); }
        else { showToast(data.message || 'Error al actualizar.', 'error'); }
    })
    .catch(() => showToast('Error de conexion.', 'error'));
}

function esc(t) { const d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; }
</script>
<script src="<?= URL ?>assets/js/toast.js"></script>
</body>
</html>
