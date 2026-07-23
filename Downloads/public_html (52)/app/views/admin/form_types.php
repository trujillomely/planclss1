<?php
$formTypes = $formTypes ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    
    <?= Csrf::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Formulario | Panel <?= $panelLabel ?? 'Administrador' ?></title>
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
        $pageTitle    = 'Tipos de Formulario';
        $pageModule   = 'formularios';
        $pageSubtitle = 'Crea y administra los formularios que verán tus clientes y colaboradores';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';

        $totalFormularios = count($formTypes);
        $activosCount     = count(array_filter($formTypes, fn($f) => ($f['status'] ?? 1) == 1));
        $inactivosCount   = $totalFormularios - $activosCount;
        $totalEnvios      = array_sum(array_map(fn($f) => (int) ($f['total_submissions'] ?? 0), $formTypes));
        ?>

        <div class="dash-content">

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total formularios</div>
                        <div class="kpi-value"><?= $totalFormularios ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Activos</div>
                        <div class="kpi-value"><?= $activosCount ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon danger"><i class="bi bi-x-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Inactivos</div>
                        <div class="kpi-value"><?= $inactivosCount ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon info"><i class="bi bi-inboxes"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Envíos recibidos</div>
                        <div class="kpi-value"><?= $totalEnvios ?></div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title">Listado de formularios</div>
                        <div class="content-card-sub">Cada formulario nace con su versión 1; usa "Constructor" para diseñar sus campos</div>
                    </div>
                </div>
                <div class="content-card-body">

                    <div class="filter-bar">
                        <input type="text" id="searchFormTypes" placeholder="Buscar por nombre, clave o categoría..." oninput="searchTable(this.value)">
                        <?php if (Auth::can('formularios', 'crear')): ?>
                        <button class="btn-agregar" onclick="openModal()">
                            <i class="bi bi-plus-lg"></i> Nuevo Formulario
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="tbl-wrap">
                        <table class="admin-table" id="formTypesTable" data-pagination>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Clave</th>
                                    <th>Categoría</th>
                                    <th>Versión</th>
                                    <th>Campos</th>
                                    <th>Envíos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($formTypes)): ?>
                                    <?php foreach ($formTypes as $form): $activo = ($form['status'] ?? 1) == 1; ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($form['id_form_type']) ?></td>
                                            <td class="fw"><?= htmlspecialchars($form['name']) ?></td>
                                            <td><code><?= htmlspecialchars($form['form_key']) ?></code></td>
                                            <td><?= htmlspecialchars($form['category'] ?: '—') ?></td>
                                            <td>v<?= (int) ($form['current_version_number'] ?? 1) ?></td>
                                            <td><?= (int) ($form['total_fields'] ?? 0) ?></td>
                                            <td><?= (int) ($form['total_submissions'] ?? 0) ?></td>
                                            <td>
                                                <span class="badge <?= $activo ? 'badge-active' : 'badge-inactive' ?>">
                                                    <?= $activo ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:6px;">
                                                    <?php if (Auth::can('formularios', 'editar')): ?>
                                                    <a class="btn-icon" title="Ir al Constructor" href="?url=admin/form-builder&id=<?= (int) $form['id_form_type'] ?>">
                                                        <i class="bi bi-tools"></i>
                                                    </a>
                                                    <button class="btn-icon" title="Editar datos" onclick='openModalEdit(<?= json_encode($form) ?>)'>
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (Auth::can('formularios', 'eliminar')): ?>
                                                        <?php if ($activo): ?>
                                                        <button class="btn-icon" title="Desactivar" onclick="toggleStatus(<?= (int) $form['id_form_type'] ?>, 1)">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                        <?php else: ?>
                                                        <button class="btn-icon" title="Reactivar" onclick="toggleStatus(<?= (int) $form['id_form_type'] ?>, 0)">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class="bi bi-file-earmark-text"></i>
                                                <p>No se han creado formularios todavía. Usa "Nuevo Formulario" para crear el primero.</p>
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

<!-- Modal Agregar / Editar Tipo de Formulario -->
<div class="modal-overlay" id="formTypeModal">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header" style="border-top:3px solid var(--mod-accent, var(--arco-siena));">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena));display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <h3 id="modalTitle" style="margin:0;font-size:1.05rem;">Nuevo Formulario</h3>
            </div>
            <button class="modal-close" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="formTypeForm" onsubmit="submitForm(event)">
            <input type="hidden" id="form_id" name="id">
            <div class="modal-body">

                <div class="form-group">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Nombre del formulario</label>
                    <input type="text" id="form_name" name="name" placeholder="Ej. Solicitud de Reclamo" required oninput="suggestKey()" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                </div>

                <div class="form-group">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Clave interna (form_key)</label>
                    <input type="text" id="form_key" name="form_key" placeholder="Ej. solicitud_reclamo" required style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                    <div style="font-size:.74rem;color:#9ca3af;margin-top:4px;">Identificador único. Se sugiere automáticamente.</div>
                </div>

                <div class="form-group">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Categoría</label>
                    <input type="text" id="form_category" name="category" list="categoryOptions" placeholder="Ej. Reclamos" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                    <datalist id="categoryOptions">
                        <option value="Pólizas">
                        <option value="Reclamos">
                        <option value="Beneficiarios">
                        <option value="Pagos">
                        <option value="Contacto">
                        <option value="General">
                    </datalist>
                </div>

                <div class="form-group">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Descripción</label>
                    <textarea id="form_description" name="description" rows="3" placeholder="¿Para qué se usa este formulario?" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;resize:vertical;min-height:70px;"></textarea>
                </div>

                <div style="display:flex;gap:20px;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:.84rem;font-weight:600;color:var(--arco-carbon);cursor:pointer;">
                        <input type="checkbox" id="form_allow_download" name="allow_download" value="1" checked style="accent-color:var(--mod-accent,var(--arco-siena));">
                        Permitir descarga PDF
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:.84rem;font-weight:600;color:var(--arco-carbon);cursor:pointer;">
                        <input type="checkbox" id="form_allow_digital_fill" name="allow_digital_fill" value="1" checked style="accent-color:var(--mod-accent,var(--arco-siena));">
                        Permitir llenado digital
                    </label>
                </div>

            </div>
            <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--arco-perla);">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary" style="padding:10px 24px;font-weight:700;">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>
let keyEditedManually = false;
document.getElementById('form_key').addEventListener('input', () => keyEditedManually = true);

function slugify(text) {
    return text.toString().trim().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s_-]/g, '')
        .replace(/\s+/g, '_');
}

function suggestKey() {
    if (keyEditedManually) return;
    document.getElementById('form_key').value = slugify(document.getElementById('form_name').value);
}

function searchTable(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('#formTypesTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
}

function openModal() {
    keyEditedManually = false;
    document.getElementById('modalTitle').textContent = 'Nuevo Formulario';
    document.getElementById('formTypeForm').reset();
    document.getElementById('form_id').value = '';
    document.getElementById('form_allow_download').checked = true;
    document.getElementById('form_allow_digital_fill').checked = true;
    document.getElementById('formTypeModal').classList.add('open');
}

function openModalEdit(form) {
    keyEditedManually = true;
    document.getElementById('modalTitle').textContent = 'Editar Formulario';
    document.getElementById('form_id').value                   = form.id_form_type ?? '';
    document.getElementById('form_name').value                 = form.name ?? '';
    document.getElementById('form_key').value                  = form.form_key ?? '';
    document.getElementById('form_category').value             = form.category ?? '';
    document.getElementById('form_description').value          = form.description ?? '';
    document.getElementById('form_allow_download').checked     = Number(form.allow_download ?? 1) === 1;
    document.getElementById('form_allow_digital_fill').checked = Number(form.allow_digital_fill ?? 1) === 1;
    document.getElementById('formTypeModal').classList.add('open');
}

function closeModal() {
    document.getElementById('formTypeModal').classList.remove('open');
}

document.getElementById('formTypeModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

function submitForm(e) {
    e.preventDefault();
    const id  = document.getElementById('form_id').value;
    const url = id ? '?url=admin/form-types/update' : '?url=admin/form-types/store';
    const btn = e.target.querySelector('.btn-primary');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(url, { method: 'POST', body: new FormData(e.target) })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(data => {
            if (data.success) {
                if (!id && data.data && data.data.id_form_type) {
                    window.location.href = '?url=admin/form-builder&id=' + data.data.id_form_type;
                    return;
                }
                closeModal();
                location.reload();
            } else {
                showToast(data.message || 'Ocurrió un error.', 'error');
                btn.disabled = false;
                btn.textContent = 'Guardar Formulario';
            }
        })
        .catch(() => {
            showToast('Error de conexión. Intente nuevamente.', 'error');
            btn.disabled = false;
            btn.textContent = 'Guardar Formulario';
        });
}

function toggleStatus(id, currentStatus, force) {
    const willActivate = Number(currentStatus) === 0;
    const mensaje = willActivate ? '¿Activar este formulario?' : '¿Desactivar este formulario?';

    function doAction() {
        const url = willActivate ? '?url=admin/form-types/reactivate' : '?url=admin/form-types/delete';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, force: !!force })
        })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(data => {
            if (data.success) {
                location.reload();
            } else if (data.requires_confirmation) {
                showConfirm(data.message).then(function(ok) {
                    if (ok) toggleStatus(id, currentStatus, true);
                });
            } else {
                showToast(data.message || 'Error al actualizar el estado.', 'error');
            }
        })
        .catch(() => showToast('Error de conexión. Intente nuevamente.', 'error'));
    }

    if (force) { doAction(); } else {
        showConfirm(mensaje).then(function(ok) { if (ok) doAction(); });
    }
}
</script>

<script src="<?= URL ?>assets/js/toast.js"></script>
</body>
</html>
