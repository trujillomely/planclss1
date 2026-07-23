<?php
$companies = $companies ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    
    <?= Csrf::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aseguradoras | Panel <?= $panelLabel ?? 'Administrador' ?></title>
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
        $pageTitle    = 'Aseguradoras';
        $pageModule   = 'aseguradoras';
        $pageSubtitle = 'Gestión de compañías aseguradoras';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';

        $totalCompanias = count($companies);
        $activas   = count(array_filter($companies, fn($c) => ($c['status'] ?? 1) == 1));
        $inactivas = $totalCompanias - $activas;
        ?>

        <div class="dash-content">

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="bi bi-building"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total registradas</div>
                        <div class="kpi-value"><?= $totalCompanias ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Activas</div>
                        <div class="kpi-value"><?= $activas ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon danger"><i class="bi bi-x-circle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Inactivas</div>
                        <div class="kpi-value"><?= $inactivas ?></div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title">Listado de aseguradoras</div>
                        <div class="content-card-sub">Compañías aseguradoras registradas en el sistema</div>
                    </div>
                </div>
                <div class="content-card-body">

                    <div class="filter-bar">
                        <input type="text" id="searchCompanies" placeholder="Buscar por nombre, NIT o correo..." oninput="searchTable(this.value)">
                        <?php if (Auth::can('aseguradoras', 'crear')): ?>
                        <button class="btn-agregar" onclick="openModal()">
                            <i class="bi bi-plus-lg"></i> Agregar Aseguradora
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="tbl-wrap">
                        <table class="admin-table" id="companiesTable" data-pagination>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Nombre</th>
                                    <th>NIT</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Sitio web</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($companies)): ?>
                                    <?php foreach ($companies as $company): $activo = ($company['status'] ?? 1) == 1; ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($company['id_insurance_company']) ?></td>
                                            <td>
                                                <div class="ic-logo">
                                                    <?php if (!empty($company['logo_url'])): ?>
                                                        <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="">
                                                    <?php else: ?>
                                                        <?= strtoupper(mb_substr($company['name'] ?? 'A', 0, 2)) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="fw"><a href="javascript:void(0)" onclick="openCompanyDetail(<?= (int)$company['id_insurance_company'] ?>)" style="color:var(--arco-siena,#8C7B6E);text-decoration:none;font-weight:700;cursor:pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?= htmlspecialchars($company['name']) ?></a></td>
                                            <td><?= htmlspecialchars($company['nit'] ?: '—') ?></td>
                                            <td><?= htmlspecialchars($company['email'] ?: '—') ?></td>
                                            <td><?= htmlspecialchars($company['phone'] ?: '—') ?></td>
                                            <td>
                                                <?php if (!empty($company['website'])): ?>
                                                    <a class="tbl-link" href="<?= htmlspecialchars($company['website']) ?>" target="_blank" rel="noopener">
                                                        <?= htmlspecialchars($company['website']) ?>
                                                    </a>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td><?php
                                                $addr = trim(($company['address_line1'] ?? '') . ' ' . ($company['address_line2'] ?? ''));
                                                $loc  = $company['locality_name'] ?? '';
                                                $mun  = $company['municipality_name'] ?? '';
                                                $dep  = $company['department_name'] ?? '';
                                                $full = trim($addr . ', ' . $loc . ', ' . $mun . ', ' . $dep, ', ');
                                                echo htmlspecialchars($full ?: '—');
                                            ?></td>
                                            <td>
                                                <span class="badge <?= $activo ? 'badge-active' : 'badge-inactive' ?>">
                                                    <?= $activo ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:6px;">
                                                    <?php if (Auth::can('aseguradoras', 'editar')): ?>
                                                    <button class="btn-icon" title="Editar" onclick='openModalEdit(<?= json_encode($company) ?>)'>
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (Auth::can('aseguradoras', 'eliminar')): ?>
                                                        <?php if ($activo): ?>
                                                        <button class="btn-icon" title="Desactivar" onclick="toggleStatus(<?= (int)$company['id_insurance_company'] ?>, 1)">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                        <?php else: ?>
                                                        <button class="btn-icon" title="Reactivar" onclick="toggleStatus(<?= (int)$company['id_insurance_company'] ?>, 0)">
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
                                        <td colspan="10">
                                            <div class="empty-state">
                                                <i class="bi bi-building"></i>
                                                <p>No se encontraron aseguradoras. Usa "Agregar Aseguradora" para crear la primera.</p>
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

<!-- Modal Agregar / Editar Aseguradora -->
<div class="modal-overlay" id="companyModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Agregar Aseguradora</h3>
            <button class="modal-close" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="companyForm" onsubmit="submitForm(event)">
            <input type="hidden" id="company_id" name="id">
            <div class="modal-body">

                <div class="form-group">
                    <label>Nombre de la aseguradora</label>
                    <input type="text" id="company_name" name="name" placeholder="Ej. Seguros Universales" required>
                </div>

                <div class="form-group">
                    <label>NIT</label>
                    <input type="text" id="company_nit" name="nit" placeholder="Ej. 1234567-8">
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" id="company_email" name="email" placeholder="contacto@aseguradora.com">
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="company_phone" name="phone" placeholder="Ej. 2222-3333">
                </div>

                <div class="form-group">
                    <label>Sitio web</label>
                    <input type="url" id="company_website" name="website" placeholder="https://www.aseguradora.com">
                </div>

                <div class="form-group">
                    <label>URL del logo</label>
                    <input type="url" id="company_logo" name="logo_url" placeholder="https://...">
                </div>

                <div class="form-group" style="border-top:1px solid var(--arco-perla); padding-top:14px; margin-top:4px;">
                    <label style="font-weight:700;">Dirección</label>
                </div>

                <div class="form-group">
                    <label>Departamento</label>
                    <select id="id_department" name="id_department" onchange="onDepartmentChange()">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departments as $dep): ?>
                            <option value="<?= (int)$dep['id_department'] ?>"><?= htmlspecialchars($dep['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Municipio</label>
                    <select id="id_municipality" name="id_municipality" onchange="onMunicipalityChange()" disabled>
                        <option value="">Seleccione primero un departamento</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Localidad / Ciudad</label>
                    <select id="id_locality" name="id_locality" disabled>
                        <option value="">Seleccione primero un municipio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Código Postal</label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="01001">
                </div>

                <div class="form-group">
                    <label>Dirección (línea 1)</label>
                    <input type="text" id="address_line1" name="address_line1" placeholder="Calle, avenida, número...">
                </div>

                <div class="form-group">
                    <label>Dirección (línea 2)</label>
                    <input type="text" id="address_line2" name="address_line2" placeholder="Referencia, zona, colonia... (opcional)">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar Aseguradora</button>
            </div>
        </form>
    </div>
</div>

<script>
const CATALOG_DEPARTMENTS    = <?= json_encode($departments) ?>;
const CATALOG_MUNICIPALITIES = <?= json_encode($municipalities) ?>;
const CATALOG_LOCALITIES     = <?= json_encode($localities) ?>;

function populateMunicipalities(idDepartment, selectedMunicipality) {
    const sel = document.getElementById('id_municipality');
    sel.innerHTML = '';
    if (!idDepartment) {
        sel.innerHTML = '<option value="">Seleccione primero un departamento</option>';
        sel.disabled = true;
        return;
    }
    const items = CATALOG_MUNICIPALITIES.filter(m => String(m.id_department) === String(idDepartment));
    sel.innerHTML = '<option value="">Seleccione un municipio</option>' +
        items.map(m => '<option value="' + m.id_municipality + '">' + m.municipality_name + '</option>').join('');
    sel.disabled = items.length === 0;
    if (selectedMunicipality) sel.value = selectedMunicipality;
}

function populateLocalities(idMunicipality, selectedLocality) {
    const sel = document.getElementById('id_locality');
    sel.innerHTML = '';
    if (!idMunicipality) {
        sel.innerHTML = '<option value="">Seleccione primero un municipio</option>';
        sel.disabled = true;
        return;
    }
    const items = CATALOG_LOCALITIES.filter(l => String(l.id_municipality) === String(idMunicipality));
    sel.innerHTML = '<option value="">Seleccione una localidad</option>' +
        items.map(l => '<option value="' + l.id_locality + '">' + l.locality_name + (l.locality_type ? ' (' + l.locality_type + ')' : '') + '</option>').join('');
    sel.disabled = items.length === 0;
    if (selectedLocality) sel.value = selectedLocality;
}

function onDepartmentChange() {
    populateMunicipalities(document.getElementById('id_department').value, null);
    populateLocalities(null, null);
}

function onMunicipalityChange() {
    populateLocalities(document.getElementById('id_municipality').value, null);
}

function prefillLocationCascade(idLocality) {
    if (!idLocality) {
        document.getElementById('id_department').value = '';
        populateMunicipalities(null, null);
        populateLocalities(null, null);
        return;
    }
    const locality = CATALOG_LOCALITIES.find(l => String(l.id_locality) === String(idLocality));
    if (!locality) return;
    const municipality = CATALOG_MUNICIPALITIES.find(m => String(m.id_municipality) === String(locality.id_municipality));
    if (!municipality) return;
    document.getElementById('id_department').value = municipality.id_department;
    populateMunicipalities(municipality.id_department, locality.id_municipality);
    populateLocalities(locality.id_municipality, idLocality);
}

function searchTable(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('#companiesTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
}

function openModal() {
    document.getElementById('modalTitle').textContent = 'Agregar Aseguradora';
    document.getElementById('company_id').value = '';
    document.getElementById('companyForm').reset();
    populateMunicipalities(null, null);
    populateLocalities(null, null);
    document.getElementById('companyModal').classList.add('open');
}

function openModalEdit(company) {
    document.getElementById('modalTitle').textContent = 'Editar Aseguradora';
    document.getElementById('company_id').value      = company.id_insurance_company ?? '';
    document.getElementById('company_name').value    = company.name ?? '';
    document.getElementById('company_nit').value     = company.nit ?? '';
    document.getElementById('company_email').value   = company.email ?? '';
    document.getElementById('company_phone').value   = company.phone ?? '';
    document.getElementById('company_website').value = company.website ?? '';
    document.getElementById('company_logo').value    = company.logo_url ?? '';
    document.getElementById('postal_code').value     = company.postal_code ?? '';
    document.getElementById('address_line1').value   = company.address_line1 ?? '';
    document.getElementById('address_line2').value   = company.address_line2 ?? '';
    prefillLocationCascade(company.id_locality ?? null);
    document.getElementById('companyModal').classList.add('open');
}

function closeModal() {
    document.getElementById('companyModal').classList.remove('open');
}

document.getElementById('companyModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

function submitForm(e) {
    e.preventDefault();
    const id  = document.getElementById('company_id').value;
    const url = id ? '?url=admin/insurance-companies/update' : '?url=admin/insurance-companies/store';
    const btn = e.target.querySelector('.btn-primary');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(url, { method: 'POST', body: new FormData(e.target) })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                showToast(data.message || 'Ocurrió un error.', 'error');
                btn.disabled = false;
                btn.textContent = 'Guardar Aseguradora';
            }
        })
        .catch(() => {
            showToast('Error de conexión. Intente nuevamente.', 'error');
            btn.disabled = false;
            btn.textContent = 'Guardar Aseguradora';
        });
}

function toggleStatus(id, currentStatus, force) {
    const willActivate = Number(currentStatus) === 0;
    const mensaje = willActivate ? '¿Activar esta aseguradora?' : '¿Desactivar esta aseguradora?';

    function doAction() {
        const url = willActivate ? '?url=admin/insurance-companies/reactivate' : '?url=admin/insurance-companies/delete';

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
                showConfirm(data.message + ' (Esta acción no elimina las pólizas existentes)').then(function(ok) {
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

<!-- ═══ DETALLE DE ASEGURADORA — Drawer lateral ═══ -->
<style>
.ic-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:1050;opacity:0;transition:opacity .25s;}
.ic-overlay.active{display:block;opacity:1;}
.ic-drawer{position:fixed;top:0;right:-540px;width:520px;max-width:100vw;height:100vh;background:var(--card-bg,#fff);box-shadow:-4px 0 24px rgba(0,0,0,.12);z-index:1060;display:flex;flex-direction:column;transition:right .3s cubic-bezier(.4,0,.2,1);overflow:hidden;}
.ic-drawer.active{right:0;}
.ic-hdr{padding:20px 24px;border-bottom:1px solid var(--border-color,#e5e7eb);display:flex;align-items:center;gap:16px;flex-shrink:0;}
.ic-hdr-avatar{width:56px;height:56px;border-radius:12px;background:var(--arco-lino,#f1ebe1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;color:var(--arco-siena,#8C7B6E);flex-shrink:0;overflow:hidden;}
.ic-hdr-avatar img{width:100%;height:100%;object-fit:cover;}
.ic-hdr-info{flex:1;min-width:0;}
.ic-hdr-name{font-weight:700;font-size:1.05rem;color:var(--text-primary,#1f2937);}
.ic-hdr-sub{font-size:.82rem;color:var(--text-secondary,#6b7280);margin-top:2px;}
.ic-hdr-badges{margin-top:4px;}
.ic-close{background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-secondary,#6b7280);padding:4px 8px;border-radius:6px;}
.ic-close:hover{background:var(--bg-secondary,#f3f4f6);}
.ic-body{flex:1;overflow-y:auto;padding:20px 24px;}
.ic-kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;}
.ic-kpi{background:var(--bg-secondary,#f9fafb);border:1px solid var(--border-color,#e5e7eb);border-radius:10px;padding:12px;text-align:center;}
.ic-kpi-val{font-size:1.3rem;font-weight:700;color:var(--arco-siena,#8C7B6E);}
.ic-kpi-lbl{font-size:.7rem;color:var(--text-secondary,#6b7280);margin-top:2px;}
.ic-section{margin-bottom:20px;}
.ic-section-title{font-weight:700;font-size:.85rem;color:var(--text-primary,#1f2937);margin-bottom:10px;display:flex;align-items:center;gap:8px;}
.ic-section-title i{color:var(--arco-siena,#8C7B6E);}
.ic-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;font-size:.82rem;}
.ic-info-label{color:var(--text-secondary,#6b7280);}
.ic-info-val{font-weight:600;color:var(--text-primary,#1f2937);}
.ic-list{list-style:none;padding:0;margin:0;}
.ic-list-item{padding:10px 12px;border:1px solid var(--border-color,#e5e7eb);border-radius:8px;margin-bottom:6px;font-size:.82rem;display:flex;justify-content:space-between;align-items:center;transition:background .12s;}
.ic-list-item:hover{background:var(--bg-secondary,#f9fafb);}
.ic-list-title{font-weight:600;color:var(--text-primary,#1f2937);}
.ic-list-sub{color:var(--text-secondary,#6b7280);font-size:.75rem;}
.ic-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600;}
.ic-badge-active{background:#D1FAE5;color:#065F46;}
.ic-badge-inactive{background:#FEE2E2;color:#991B1B;}
.ic-badge-pending{background:#FEF3C7;color:#92400E;}
.ic-badge-info{background:#DBEAFE;color:#1E40AF;}
.ic-empty{padding:20px;text-align:center;color:var(--text-secondary,#9ca3af);font-size:.82rem;}
.ic-money{text-align:right;font-weight:600;white-space:nowrap;}
@media(max-width:560px){.ic-drawer{width:100vw;right:-100vw;}.ic-kpi-row{grid-template-columns:repeat(2,1fr);}.ic-info-grid{grid-template-columns:1fr;}}
</style>

<div class="ic-overlay" id="icOverlay"></div>
<div class="ic-drawer" id="icDrawer">
    <div class="ic-hdr">
        <div class="ic-hdr-avatar" id="icAvatar"></div>
        <div class="ic-hdr-info">
            <div class="ic-hdr-name" id="icName">—</div>
            <div class="ic-hdr-sub" id="icSub">—</div>
            <div class="ic-hdr-badges" id="icBadges"></div>
        </div>
        <button class="ic-close" onclick="closeCompanyDetail()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="ic-body" id="icBody">
        <div class="ic-empty"><i class="bi bi-hourglass-split"></i><p>Cargando informacion...</p></div>
    </div>
</div>

<script>
function openCompanyDetail(id) {
    document.getElementById('icOverlay').classList.add('active');
    document.getElementById('icDrawer').classList.add('active');
    document.getElementById('icBody').innerHTML = '<div class="ic-empty"><i class="bi bi-hourglass-split"></i><p>Cargando informacion...</p></div>';

    fetch('?url=admin/insurance-companies/detail&id=' + id)
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(res => {
            if (!res.success) { document.getElementById('icBody').innerHTML = '<div class="ic-empty">' + (res.message || 'Error') + '</div>'; return; }
            renderCompanyDetail(res.data);
        })
        .catch(() => { document.getElementById('icBody').innerHTML = '<div class="ic-empty">Error al cargar los datos.</div>'; });
}

function closeCompanyDetail() {
    document.getElementById('icOverlay').classList.remove('active');
    document.getElementById('icDrawer').classList.remove('active');
}
document.getElementById('icOverlay').addEventListener('click', closeCompanyDetail);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCompanyDetail(); });

function renderCompanyDetail(d) {
    const c = d.company;
    const cnt = d.counts;
    const esc = s => { const el = document.createElement('div'); el.textContent = s || ''; return el.innerHTML; };
    const badgeClass = s => s === 'Activo' ? 'ic-badge-active' : (s === 'Inactivo' ? 'ic-badge-inactive' : 'ic-badge-info');
    const fmtDate = s => { if (!s) return '—'; const dt = new Date(s.replace(' ','T')); return isNaN(dt) ? s : dt.toLocaleDateString('es-GT',{day:'2-digit',month:'2-digit',year:'numeric'}); };
    const fmtMoney = s => 'Q ' + parseFloat(s||0).toLocaleString('es-GT',{minimumFractionDigits:2});

    // Avatar
    const av = document.getElementById('icAvatar');
    if (c.logo_url) {
        av.innerHTML = '<img src="' + esc(c.logo_url) + '" alt="">';
    } else {
        av.textContent = esc((c.name || 'A').substring(0,2).toUpperCase());
    }

    document.getElementById('icName').textContent = c.name || '—';
    document.getElementById('icSub').textContent = (c.nit ? 'NIT: ' + esc(c.nit) : '') + (c.email ? ' · ' + esc(c.email) : '') + (c.phone ? ' · ' + esc(c.phone) : '');

    const status = (c.status ?? 1) == 1 ? 'Activo' : 'Inactivo';
    let badges = '<span class="ic-badge ' + badgeClass(status) + '">' + esc(status) + '</span>';
    if (c.website) badges += ' <a href="' + esc(c.website) + '" target="_blank" class="ic-badge ic-badge-info" style="text-decoration:none;">Sitio web</a>';
    document.getElementById('icBadges').innerHTML = badges;

    let html = '';

    // KPIs
    html += '<div class="ic-kpi-row">';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + cnt.policies + '</div><div class="ic-kpi-lbl">Polizas</div></div>';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + cnt.active_policies + '</div><div class="ic-kpi-lbl">Activas</div></div>';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + cnt.quotes + '</div><div class="ic-kpi-lbl">Cotizaciones</div></div>';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + cnt.claims + '</div><div class="ic-kpi-lbl">Reclamos</div></div>';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + fmtMoney(d.totals.premium) + '</div><div class="ic-kpi-lbl">Prima Total</div></div>';
    html += '<div class="ic-kpi"><div class="ic-kpi-val">' + fmtMoney(d.totals.coverage) + '</div><div class="ic-kpi-lbl">Cobertura Total</div></div>';
    html += '</div>';

    // Datos generales
    html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-building"></i> Datos Generales</div>';
    html += '<div class="ic-info-grid">';
    html += '<div><div class="ic-info-label">Nombre</div><div class="ic-info-val">' + esc(c.name) + '</div></div>';
    html += '<div><div class="ic-info-label">NIT</div><div class="ic-info-val">' + esc(c.nit || '—') + '</div></div>';
    html += '<div><div class="ic-info-label">Email</div><div class="ic-info-val">' + esc(c.email || '—') + '</div></div>';
    html += '<div><div class="ic-info-label">Telefono</div><div class="ic-info-val">' + esc(c.phone || '—') + '</div></div>';
    html += '<div><div class="ic-info-label">Sitio web</div><div class="ic-info-val">' + (c.website ? '<a href="' + esc(c.website) + '" target="_blank" style="color:var(--arco-siena);">' + esc(c.website) + '</a>' : '—') + '</div></div>';
    html += '<div><div class="ic-info-label">Registro</div><div class="ic-info-val">' + fmtDate(c.created_at) + '</div></div>';
    html += '</div></div>';

    // Direccion
    if (c.department_name || c.municipality_name || c.address_line1) {
        html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-geo-alt"></i> Direccion</div>';
        html += '<div class="ic-info-grid">';
        html += '<div><div class="ic-info-label">Departamento</div><div class="ic-info-val">' + esc(c.department_name || '—') + '</div></div>';
        html += '<div><div class="ic-info-label">Municipio</div><div class="ic-info-val">' + esc(c.municipality_name || '—') + '</div></div>';
        html += '<div><div class="ic-info-label">Localidad</div><div class="ic-info-val">' + esc(c.locality_name || '—') + '</div></div>';
        html += '<div><div class="ic-info-label">Codigo Postal</div><div class="ic-info-val">' + esc(c.postal_code || '—') + '</div></div>';
        html += '<div style="grid-column:1/-1"><div class="ic-info-label">Direccion</div><div class="ic-info-val">' + esc((c.address_line1 || '') + (c.address_line2 ? ', ' + c.address_line2 : '')) + '</div></div>';
        html += '</div></div>';
    }

    // Polizas
    html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-file-earmark-check"></i> Polizas (' + cnt.policies + ')</div>';
    if (d.policies.length === 0) { html += '<div class="ic-empty">Sin polizas registradas con esta aseguradora</div>'; }
    else {
        html += '<ul class="ic-list">';
        d.policies.forEach(p => {
            const sClass = p.status === 'Activo' ? 'ic-badge-active' : (p.status === 'Expirado' ? 'ic-badge-inactive' : 'ic-badge-info');
            html += '<li><div><div class="ic-list-title">' + esc(p.policy_number) + '</div><div class="ic-list-sub">' + esc(p.client || '—') + ' · ' + esc(p.type) + '</div><div class="ic-list-sub">Vence: ' + fmtDate(p.expiration) + ' · Prima: ' + fmtMoney(p.premium) + '</div></div><span class="ic-badge ' + sClass + '">' + esc(p.status) + '</span></li>';
        });
        html += '</ul>';
    }
    html += '</div>';

    // Cotizaciones
    html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-file-earmark-text"></i> Cotizaciones (' + cnt.quotes + ')</div>';
    if (d.quotes.length === 0) { html += '<div class="ic-empty">Sin cotizaciones registradas</div>'; }
    else {
        html += '<ul class="ic-list">';
        d.quotes.forEach(q => {
            html += '<li><div><div class="ic-list-title">' + esc(q.folio) + '</div><div class="ic-list-sub">' + esc(q.client || '—') + ' · ' + esc(q.type) + '</div><div class="ic-list-sub">Prima: ' + fmtMoney(q.premium) + ' · ' + fmtDate(q.created_at) + '</div></div><span class="ic-badge ic-badge-info">' + esc(q.status) + '</span></li>';
        });
        html += '</ul>';
    }
    html += '</div>';

    // Reclamos
    html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-shield-exclamation"></i> Reclamos (' + cnt.claims + ')</div>';
    if (d.claims.length === 0) { html += '<div class="ic-empty">Sin reclamos registrados</div>'; }
    else {
        html += '<ul class="ic-list">';
        d.claims.forEach(cl => {
            html += '<li><div><div class="ic-list-title">' + esc(cl.claim_number) + '</div><div class="ic-list-sub">' + esc(cl.type) + ' · Poliza: ' + esc(cl.policy_number) + '</div><div class="ic-list-sub">Monto: ' + fmtMoney(cl.amount) + ' · ' + fmtDate(cl.date) + '</div></div><span class="ic-badge ic-badge-info">' + esc(cl.status) + '</span></li>';
        });
        html += '</ul>';
    }
    html += '</div>';

    // Comisiones
    if (cnt.commissions > 0) {
        html += '<div class="ic-section"><div class="ic-section-title"><i class="bi bi-cash-stack"></i> Comisiones (' + cnt.commissions + ')</div>';
        html += '<ul class="ic-list">';
        d.commissions.forEach(cm => {
            const cmClass = cm.status === 'Pagada' ? 'ic-badge-active' : (cm.status === 'Pendiente' ? 'ic-badge-pending' : 'ic-badge-info');
            html += '<li><div><div class="ic-list-title">Poliza: ' + esc(cm.policy_number) + '</div><div class="ic-list-sub">Monto: ' + fmtMoney(cm.amount) + ' · Tarifa: ' + parseFloat(cm.rate||0).toFixed(1) + '% · ' + fmtDate(cm.created_at) + '</div></div><span class="ic-badge ' + cmClass + '">' + esc(cm.status) + '</span></li>';
        });
        html += '</ul></div>';
    }

    document.getElementById('icBody').innerHTML = html;
}
</script>
</body>
</html>
