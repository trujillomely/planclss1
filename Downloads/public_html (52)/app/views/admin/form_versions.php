<?php
$formTypes = $formTypes ?? [];
$versions  = $versions ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    
    <?= Csrf::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versiones de Formulario | Panel <?= $panelLabel ?? 'Administrador' ?></title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/toast.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
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
        $pageTitle    = 'Versiones de Formulario';
        $pageModule   = 'formularios';
        $pageSubtitle = 'Historial de versiones de todos los formularios';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';

        $totalVersions  = count($versions);
        $activeVersions = count(array_filter($versions, fn($v) => ($v['status'] ?? 1) == 1));
        $formTypesCount = count($formTypes);
        ?>

        <div class="dash-content">

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Tipos de formulario</div>
                        <div class="kpi-value"><?= $formTypesCount ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon info"><i class="bi bi-code-branch"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total versiones</div>
                        <div class="kpi-value"><?= $totalVersions ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Activas</div>
                        <div class="kpi-value"><?= $activeVersions ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon warning"><i class="bi bi-archive"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Inactivas</div>
                        <div class="kpi-value"><?= $totalVersions - $activeVersions ?></div>
                    </div>
                </div>
            </div>

            <!-- Filtro y tabla -->
            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title">Historial de versiones</div>
                        <div class="content-card-sub">Todas las versiones registradas de cada formulario</div>
                    </div>
                </div>
                <div class="content-card-body">

                    <div class="filter-bar">
                        <input type="text" id="searchInput" placeholder="Buscar por formulario, versión o descripción…" oninput="filterTable()">
                        <select id="formFilter" onchange="filterTable()">
                            <option value="">Todos los formularios</option>
                            <?php foreach ($formTypes as $ft): ?>
                                <option value="<?= htmlspecialchars($ft['name']) ?>"><?= htmlspecialchars($ft['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tbl-wrap">
                        <table id="verTable" class="admin-table" data-pagination>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Formulario</th>
                                    <th>Versión</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Activa</th>
                                    <th>Creada</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($versions)): ?>
                                    <?php foreach ($versions as $v):
                                        $isActive = ($v['status'] ?? 1) == 1;
                                        $isCurrent = !empty($v['is_current']);
                                    ?>
                                        <tr data-form="<?= htmlspecialchars($v['form_name'] ?? '') ?>">
                                            <td>#<?= (int)$v['id_form_version'] ?></td>
                                            <td class="fw"><?= htmlspecialchars($v['form_name'] ?? '—') ?></td>
                                            <td><span class="badge badge-active">v<?= (int)$v['version_number'] ?></span></td>
                                            <td><?= htmlspecialchars($v['description'] ?? '—') ?></td>
                                            <td>
                                                <span class="badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?>">
                                                    <?= $isActive ? 'Activa' : 'Inactiva' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($isCurrent): ?>
                                                    <span class="badge badge-active"><i class="bi bi-check-lg"></i> Actual</span>
                                                <?php else: ?>
                                                    <span style="color:#9ca3af;font-size:.8rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $v['created_at'] ? date('d/m/Y H:i', strtotime($v['created_at'])) : '—' ?></td>
                                            <td>
                                                <div style="display:flex; gap:6px;">
                                                    <?php if ($isCurrent && !empty($v['id_form_type'])): ?>
                                                    <a href="?url=<?= htmlspecialchars($panelPrefix ?? 'admin') ?>/form-builder?id=<?= (int)$v['id_form_type'] ?>" class="btn-icon" title="Abrir en constructor">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <button class="btn-icon" title="Ver estructura" onclick="viewStructure(<?= (int)$v['id_form_version'] ?>)">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="bi bi-code-branch"></i>
                                                <p>No se encontraron versiones de formularios. Crea un tipo de formulario para comenzar.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div><!-- /dash-content -->
    </div><!-- /dash-main -->
</div><!-- /dash-layout -->

<!-- Modal: Estructura de versión -->
<div class="modal-overlay" id="structureModal">
    <div class="modal-box" style="max-width:680px;">
        <div class="modal-header" style="border-top:3px solid var(--mod-accent, var(--arco-siena));">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena));display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <h3 style="margin:0;font-size:1.05rem;">Estructura del formulario</h3>
            </div>
            <button class="modal-close" onclick="closeStructureModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" id="structureBody" style="max-height:60vh;overflow-y:auto;padding:20px;">
            <p style="color:#9ca3af;text-align:center;padding:24px;">Cargando estructura…</p>
        </div>
    </div>
</div>

<script>
function filterTable() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var form   = document.getElementById('formFilter').value;
    document.querySelectorAll('#verTable tbody tr').forEach(function(row) {
        var matchSearch = !search || row.textContent.toLowerCase().includes(search);
        var matchForm   = !form   || row.dataset.form === form;
        row.style.display = (matchSearch && matchForm) ? '' : 'none';
    });
}

function viewStructure(versionId) {
    var body = document.getElementById('structureBody');
    body.innerHTML = '<p style="color:#6b7280;text-align:center;padding:20px;">Cargando…</p>';
    document.getElementById('structureModal').classList.add('open');

    fetch('?url=' + prefix + '/form-builder/get-structure&id=' + versionId)
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(function(res){
            if (!res.success || !res.data || !res.data.length) {
                body.innerHTML = '<div style="text-align:center;padding:40px 20px;color:#9ca3af;"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4;"></i><p style="font-size:.86rem;">Esta versión no tiene secciones ni campos definidos.</p></div>';
                return;
            }
            var html = '';
            var fieldCount = 0;
            res.data.forEach(function(sec, si){
                fieldCount += (sec.fields || []).length;
                html += '<div style="margin-bottom:20px;background:var(--arco-card-bg,#fff);border:1px solid var(--arco-perla,#e5e0d8);border-radius:10px;overflow:hidden;">';
                html += '<div style="padding:14px 18px;background:var(--arco-lino,#faf9f7);border-bottom:1px solid var(--arco-perla,#e5e0d8);display:flex;align-items:center;gap:10px;">';
                html += '<div style="width:28px;height:28px;border-radius:7px;background:var(--mod-accent,var(--arco-siena));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;">' + (si+1) + '</div>';
                html += '<div><div style="font-weight:700;font-size:.9rem;color:var(--arco-carbon);">' + escHtml(sec.section_name) + '</div>';
                if (sec.section_description) html += '<div style="font-size:.75rem;color:var(--arco-siena);margin-top:2px;">' + escHtml(sec.section_description) + '</div>';
                html += '</div></div>';
                html += '<div style="padding:12px 18px;">';
                if (sec.fields && sec.fields.length) {
                    sec.fields.forEach(function(f){
                        html += '<div style="padding:8px 0 8px 16px;font-size:.82rem;color:var(--arco-carbon);border-left:2px solid var(--mod-accent,var(--arco-siena));margin-left:4px;margin-bottom:4px;display:flex;align-items:center;gap:8px;">';
                        html += '<strong>' + escHtml(f.label || f.name) + '</strong>';
                        html += '<span style="font-size:.7rem;background:var(--arco-lino);padding:2px 8px;border-radius:12px;color:var(--arco-siena);">' + escHtml(f.field_type_name || f.id_form_field_type) + '</span>';
                        if (f.is_required) html += ' <span style="color:#B1503F;font-size:.72rem;font-weight:700;">*</span>';
                        html += '</div>';
                    });
                } else {
                    html += '<div style="padding:12px 0;color:#9ca3af;font-size:.8rem;text-align:center;">Sin campos definidos</div>';
                }
                html += '</div></div>';
            });
            html = '<div style="font-size:.78rem;color:var(--arco-siena);margin-bottom:12px;font-weight:600;">' + res.data.length + ' secciones · ' + fieldCount + ' campos</div>' + html;
            body.innerHTML = html;
        })
        .catch(function(){
            body.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-circle"></i><p>Error al cargar la estructura.</p></div>';
        });
}

function closeStructureModal() {
    document.getElementById('structureModal').classList.remove('open');
}

document.getElementById('structureModal').addEventListener('click', function(e) {
    if (e.target === this) closeStructureModal();
});

function escHtml(t) {
    var d = document.createElement('div');
    d.textContent = t || '';
    return d.innerHTML;
}

var prefix = window.location.search.includes('gerente') ? 'gerente' : 'admin';
</script>
<script src="<?= URL ?>assets/js/toast.js"></script>
</body>
</html>
