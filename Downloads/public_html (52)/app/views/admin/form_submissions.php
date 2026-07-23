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

    <style>
        .filter-bar {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .filter-bar-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar input[type="text"],
        .filter-bar select,
        .filter-bar input[type="date"] {
            padding: 8px 12px;
            border: 1px solid var(--arco-perla);
            border-radius: 8px;
            font-size: .85rem;
            background: var(--arco-blanco);
            color: var(--color-text, #1f2937);
            outline: none;
            transition: border-color .2s;
        }

        .filter-bar input[type="text"]:focus,
        .filter-bar select:focus,
        .filter-bar input[type="date"]:focus {
            border-color: var(--arco-siena);
        }

        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 200px;
        }

        .filter-bar select {
            min-width: 160px;
        }

        .filter-bar input[type="date"] {
            min-width: 140px;
        }

        .status-tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .status-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--arco-perla);
            background: var(--arco-blanco);
            color: var(--color-text, #4b5563);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            user-select: none;
        }

        .status-tab:hover {
            border-color: var(--arco-siena);
            color: var(--arco-siena);
        }

        .status-tab.active {
            background: var(--mod-accent, var(--arco-siena));
            color: #fff;
            border-color: var(--mod-accent, var(--arco-siena));
        }

        .status-tab .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            font-size: .7rem;
            font-weight: 700;
            padding: 0 5px;
            background: rgba(0,0,0,.08);
        }

        .status-tab.active .tab-count {
            background: rgba(255,255,255,.25);
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--arco-perla);
            background: var(--arco-blanco);
            color: var(--color-text, #4b5563);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
        }

        .btn-export:hover {
            border-color: var(--arco-siena);
            color: var(--arco-siena);
        }

        .pagination-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0 4px;
            font-size: .8rem;
            color: #6b7280;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pagination-info .showing-text {
            font-weight: 500;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pagination-controls button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            border: 1px solid var(--arco-perla);
            background: var(--arco-blanco);
            color: var(--color-text, #4b5563);
            font-size: .8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .pagination-controls button:hover:not(:disabled):not(.active) {
            border-color: var(--arco-siena);
            color: var(--arco-siena);
        }

        .pagination-controls button.active {
            background: var(--mod-accent, var(--arco-siena));
            color: #fff;
            border-color: var(--mod-accent, var(--arco-siena));
        }

        .pagination-controls button:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .filter-divider {
            width: 1px;
            height: 28px;
            background: var(--arco-perla);
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .filter-bar-row {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-divider {
                display: none;
            }
            .status-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
            }
            .filter-bar input[type="text"],
            .filter-bar select,
            .filter-bar input[type="date"] {
                min-width: 100%;
            }
        }
    </style>
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
                        <div class="status-tabs" id="statusTabs">
                            <button class="status-tab active" data-status="Todos" onclick="filterByStatus('Todos', this)">
                                Todos <span class="tab-count" id="countTodos">0</span>
                            </button>
                            <button class="status-tab" data-status="Pendiente" onclick="filterByStatus('Pendiente', this)">
                                Pendientes <span class="tab-count" id="countPendiente">0</span>
                            </button>
                            <button class="status-tab" data-status="Enviado" onclick="filterByStatus('Enviado', this)">
                                Enviados <span class="tab-count" id="countEnviado">0</span>
                            </button>
                            <button class="status-tab" data-status="Revisado" onclick="filterByStatus('Revisado', this)">
                                Revisados <span class="tab-count" id="countRevisado">0</span>
                            </button>
                            <button class="status-tab" data-status="Rechazado" onclick="filterByStatus('Rechazado', this)">
                                Rechazados <span class="tab-count" id="countRechazado">0</span>
                            </button>
                        </div>
                        <div class="filter-bar-row">
                            <input type="text" id="searchSubmissions" placeholder="Buscar por nombre, numero, formulario..." oninput="searchTable(this.value)">
                            <div class="filter-divider"></div>
                            <select id="formFilter" onchange="filterByForm(this.value)">
                                <option value="">Todos los formularios</option>
                            </select>
                            <div class="filter-divider"></div>
                            <input type="date" id="dateFrom" onchange="applyAllFilters()" placeholder="Desde">
                            <input type="date" id="dateTo" onchange="applyAllFilters()" placeholder="Hasta">
                            <div class="filter-divider"></div>
                            <button class="btn-export" onclick="exportCSV()">
                                <i class="bi bi-filetype-csv"></i> Exportar CSV
                            </button>
                        </div>
                    </div>
                    <div class="tbl-wrap">
                        <table class="admin-table" id="submissionsTable">
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
                            <tbody id="submissionsBody"></tbody>
                        </table>
                    </div>
                    <div class="pagination-info" id="paginationInfo" style="display:none;">
                        <span class="showing-text" id="showingText"></span>
                        <div class="pagination-controls" id="paginationControls"></div>
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
            <div class="detail-footer-controls" id="statusControls" style="display:none;">
                <div>
                    <label class="detail-notes-label" for="reviewNotesInput">Notas de revisión</label>
                    <textarea class="detail-notes-area" id="reviewNotesInput" placeholder="Escribe notas sobre esta revisión..."></textarea>
                </div>
                <div class="admin-status-controls">
                    <select id="statusSelect">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Revisado">Revisado</option>
                        <option value="Rechazado">Rechazado</option>
                    </select>
                    <button onclick="confirmUpdateStatus()" style="display:flex;align-items:center;gap:6px;"><i class="bi bi-check-lg"></i> Actualizar</button>
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-left:auto;">
                <a id="detailPdfLink" href="#" target="_blank" class="btn-sm primary" style="display:none;padding:5px 12px;border-radius:6px;font-size:.8rem;font-weight:500;background:var(--arco-siena);color:#fff;text-decoration:none"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <button class="btn-secondary" onclick="closeDetail()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
var allSubmissions = <?= json_encode(array_values($submissions), JSON_UNESCAPED_UNICODE) ?>;
var filteredSubmissions = allSubmissions.slice();
var currentPage = 1;
var rowsPerPage = 15;
var currentStatus = 'Todos';
var currentFormKey = '';
var currentDateFrom = '';
var currentDateTo = '';
var currentSearch = '';

function initFormFilter() {
    var formMap = {};
    allSubmissions.forEach(function(s) {
        if (s.form_key && !formMap[s.form_key]) {
            formMap[s.form_key] = s.form_name;
        }
    });
    var sel = document.getElementById('formFilter');
    var keys = Object.keys(formMap).sort();
    keys.forEach(function(k) {
        var opt = document.createElement('option');
        opt.value = k;
        opt.textContent = formMap[k];
        sel.appendChild(opt);
    });
}

function updateStatusCounts() {
    var counts = { Todos: 0, Pendiente: 0, Enviado: 0, Revisado: 0, Rechazado: 0 };
    allSubmissions.forEach(function(s) {
        counts.Todos++;
        if (counts[s.status] !== undefined) counts[s.status]++;
    });
    document.getElementById('countTodos').textContent = counts.Todos;
    document.getElementById('countPendiente').textContent = counts.Pendiente;
    document.getElementById('countEnviado').textContent = counts.Enviado;
    document.getElementById('countRevisado').textContent = counts.Revisado;
    document.getElementById('countRechazado').textContent = counts.Rechazado;
}

function applyAllFilters() {
    currentSearch = (document.getElementById('searchSubmissions').value || '').toLowerCase().trim();
    currentFormKey = document.getElementById('formFilter').value;
    currentDateFrom = document.getElementById('dateFrom').value;
    currentDateTo = document.getElementById('dateTo').value;

    filteredSubmissions = allSubmissions.filter(function(s) {
        if (currentStatus !== 'Todos' && s.status !== currentStatus) return false;

        if (currentFormKey && s.form_key !== currentFormKey) return false;

        if (currentSearch) {
            var haystack = ((s.submission_number || '') + ' ' + (s.form_name || '') + ' ' + (s.username || '') + ' ' + (s.lastname || '') + ' ' + (s.user_email || '')).toLowerCase();
            if (haystack.indexOf(currentSearch) === -1) return false;
        }

        if (currentDateFrom || currentDateTo) {
            var rawDate = s.submitted_at || s.created_at || '';
            var dateVal = rawDate.substring(0, 10);
            if (currentDateFrom && dateVal < currentDateFrom) return false;
            if (currentDateTo && dateVal > currentDateTo) return false;
        }

        return true;
    });

    currentPage = 1;
    renderPage(currentPage);
}

function filterByStatus(status, el) {
    currentStatus = status;
    document.querySelectorAll('.status-tab').forEach(function(t) { t.classList.remove('active'); });
    if (el) el.classList.add('active');
    applyAllFilters();
}

function filterByForm(formKey) {
    applyAllFilters();
}

function searchTable(q) {
    applyAllFilters();
}

function renderPage(page) {
    var tbody = document.getElementById('submissionsBody');
    var total = filteredSubmissions.length;
    var totalPages = Math.max(1, Math.ceil(total / rowsPerPage));

    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;
    currentPage = page;

    var start = (page - 1) * rowsPerPage;
    var end = Math.min(start + rowsPerPage, total);
    var pageData = filteredSubmissions.slice(start, end);

    if (total === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inboxes"></i><p>No se encontraron envios con los filtros aplicados.</p></div></td></tr>';
        document.getElementById('paginationInfo').style.display = 'none';
        return;
    }

    var html = '';
    for (var i = 0; i < pageData.length; i++) {
        var s = pageData[i];
        var dateRaw = s.submitted_at || s.created_at || '';
        var dateFormatted = '';
        if (dateRaw) {
            var dt = new Date(dateRaw);
            if (!isNaN(dt.getTime())) {
                var dd = String(dt.getDate()).padStart(2, '0');
                var mm = String(dt.getMonth() + 1).padStart(2, '0');
                var yyyy = dt.getFullYear();
                var hh = String(dt.getHours()).padStart(2, '0');
                var mi = String(dt.getMinutes()).padStart(2, '0');
                dateFormatted = dd + '/' + mm + '/' + yyyy + ' ' + hh + ':' + mi;
            } else {
                dateFormatted = escHtml(dateRaw);
            }
        }
        var fullName = ((s.username || '') + ' ' + (s.lastname || '')).trim();
        var statusClass = (s.status || '').toLowerCase();
        html += '<tr>';
        html += '<td>#' + parseInt(s.id_form_submission) + '</td>';
        html += '<td><code>' + escHtml(s.submission_number) + '</code></td>';
        html += '<td class="fw">' + escHtml(s.form_name) + '</td>';
        html += '<td>' + escHtml(fullName) + '</td>';
        html += '<td>' + dateFormatted + '</td>';
        html += '<td><span class="submission-status-badge status-' + statusClass + '">' + escHtml(s.status) + '</span></td>';
        html += '<td><button class="btn-icon" title="Ver detalle" onclick="viewSubmission(' + parseInt(s.id_form_submission) + ')"><i class="bi bi-eye-fill"></i></button></td>';
        html += '</tr>';
    }
    tbody.innerHTML = html;

    document.getElementById('paginationInfo').style.display = 'flex';
    document.getElementById('showingText').textContent = 'Mostrando ' + (start + 1) + '-' + end + ' de ' + total + ' envios';

    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    var container = document.getElementById('paginationControls');
    var html = '';

    html += '<button ' + (currentPage <= 1 ? 'disabled' : '') + ' onclick="renderPage(' + (currentPage - 1) + ')"><i class="bi bi-chevron-left"></i></button>';

    var pages = [];
    if (totalPages <= 7) {
        for (var i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        pages.push(1);
        if (currentPage > 3) pages.push('...');
        var pStart = Math.max(2, currentPage - 1);
        var pEnd = Math.min(totalPages - 1, currentPage + 1);
        for (var p = pStart; p <= pEnd; p++) pages.push(p);
        if (currentPage < totalPages - 2) pages.push('...');
        pages.push(totalPages);
    }

    for (var j = 0; j < pages.length; j++) {
        if (pages[j] === '...') {
            html += '<button disabled style="border:none;background:none;cursor:default;">...</button>';
        } else {
            html += '<button class="' + (pages[j] === currentPage ? 'active' : '') + '" onclick="renderPage(' + pages[j] + ')">' + pages[j] + '</button>';
        }
    }

    html += '<button ' + (currentPage >= totalPages ? 'disabled' : '') + ' onclick="renderPage(' + (currentPage + 1) + ')"><i class="bi bi-chevron-right"></i></button>';

    container.innerHTML = html;
}

function exportCSV() {
    var headers = ['#', 'Numero', 'Formulario', 'Usuario', 'Email', 'Fecha', 'Estado'];
    var rows = [headers.join(',')];

    filteredSubmissions.forEach(function(s, idx) {
        var fullName = ((s.username || '') + ' ' + (s.lastname || '')).trim();
        var dateRaw = s.submitted_at || s.created_at || '';
        var dateFormatted = '';
        if (dateRaw) {
            var dt = new Date(dateRaw);
            if (!isNaN(dt.getTime())) {
                var dd = String(dt.getDate()).padStart(2, '0');
                var mm = String(dt.getMonth() + 1).padStart(2, '0');
                var yyyy = dt.getFullYear();
                var hh = String(dt.getHours()).padStart(2, '0');
                var mi = String(dt.getMinutes()).padStart(2, '0');
                dateFormatted = dd + '/' + mm + '/' + yyyy + ' ' + hh + ':' + mi;
            } else {
                dateFormatted = dateRaw;
            }
        }
        var row = [
            idx + 1,
            csvEscape(s.submission_number),
            csvEscape(s.form_name),
            csvEscape(fullName),
            csvEscape(s.user_email),
            csvEscape(dateFormatted),
            csvEscape(s.status)
        ];
        rows.push(row.join(','));
    });

    var blob = new Blob(['\uFEFF' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'envios_formularios_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function csvEscape(val) {
    var str = String(val || '');
    if (str.indexOf(',') !== -1 || str.indexOf('"') !== -1 || str.indexOf('\n') !== -1) {
        return '"' + str.replace(/"/g, '""') + '"';
    }
    return str;
}

function escHtml(t) {
    var d = document.createElement('div');
    d.textContent = t || '';
    return d.innerHTML;
}

var currentSubmissionId = null;

function closeDetail() {
    document.getElementById('detailModal').classList.remove('open');
    currentSubmissionId = null;
    var pdfLink = document.getElementById('detailPdfLink');
    if (pdfLink) { pdfLink.style.display = 'none'; pdfLink.href = '#'; }
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});

function viewSubmission(id) {
    currentSubmissionId = id;
    document.getElementById('detailBody').innerHTML = '<div class="detail-loading"><i class="bi bi-arrow-repeat"></i>Cargando...</div>';
    document.getElementById('detailModal').classList.add('open');
    document.getElementById('statusControls').style.display = 'none';
    document.getElementById('reviewNotesInput').value = '';
    var prefix = window.location.search.includes('gerente') ? 'gerente' : 'admin';
    fetch('?url=' + prefix + '/form-submissions/detail&id=' + id)
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(function(data) {
            if (!data.success) { document.getElementById('detailBody').innerHTML = '<p>' + (data.message || 'Error') + '</p>'; return; }
            var sub = data.data;
            document.getElementById('detailTitle').textContent = 'Envio ' + sub.submission_number;
            var html = '<div class="submission-detail-grid">';
            html += '<div class="submission-detail-field"><div class="field-label">Formulario</div><div class="field-value">' + escHtml(sub.form_name) + ' v' + sub.version_number + '</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Usuario</div><div class="field-value">' + escHtml((sub.username||'')+' '+(sub.lastname||'')) + ' (' + escHtml(sub.user_email||'') + ')</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Fecha</div><div class="field-value">' + (sub.submitted_at || sub.created_at) + '</div></div>';
            html += '<div class="submission-detail-field"><div class="field-label">Estado</div><div class="field-value"><span class="submission-status-badge status-' + sub.status.toLowerCase() + '">' + escHtml(sub.status) + '</span></div></div>';
            if (sub.review_notes) html += '<div class="submission-detail-field"><div class="field-label">Notas de revisión</div><div class="field-value">' + escHtml(sub.review_notes) + '</div></div>';
            html += '<div style="margin-top:8px;font-size:.85rem;font-weight:700;">Valores del formulario</div>';
            (sub.values || []).forEach(function(v) {
                var dv = v.field_value || '---';
                if (v.field_key === 'signature' && dv.indexOf('data:image') === 0) {
                    dv = '<img src="' + dv + '" style="max-width:200px;border:1px solid #e5e7eb;border-radius:4px;">';
                } else if (v.field_key === 'file' && dv && dv !== '---') {
                    var fileUrl = dv;
                    var fileName = dv.split('/').pop();
                    dv = '<a href="' + escHtml(fileUrl) + '" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;color:var(--mod-accent,var(--arco-siena));text-decoration:none;font-weight:600;"><i class="bi bi-file-earmark-arrow-down"></i>' + escHtml(fileName) + '</a>';
                } else {
                    dv = escHtml(dv);
                }
                html += '<div class="submission-detail-field"><div class="field-label">' + escHtml(v.field_label) + '</div><div class="field-value">' + dv + '</div></div>';
            });

            if (sub.attachments && sub.attachments.length > 0) {
                html += '<div class="detail-attachments"><div class="att-title"><i class="bi bi-paperclip"></i> Archivos adjuntos</div>';
                sub.attachments.forEach(function(att) {
                    var size = att.file_size ? formatFileSize(att.file_size) : '';
                    html += '<a href="' + escHtml(att.file_url) + '" target="_blank" rel="noopener"><i class="bi bi-file-earmark"></i>' + escHtml(att.file_name || 'Archivo') + (size ? '<span class="att-size">' + size + '</span>' : '') + '</a>';
                });
                html += '</div>';
            }

            if (sub.history && sub.history.length > 0) {
                html += '<div class="detail-history"><div class="hist-title"><i class="bi bi-clock-history"></i> Historial de cambios</div><div class="detail-history-list">';
                sub.history.forEach(function(h) {
                    var userName = ((h.username || '') + ' ' + (h.lastname || '')).trim() || 'Sistema';
                    var histDate = h.changed_at || '';
                    var noteText = '<strong>' + escHtml(userName) + '</strong>';
                    if (h.previous_status && h.new_status) {
                        noteText += ' cambió de <strong>' + escHtml(h.previous_status) + '</strong> a <strong>' + escHtml(h.new_status) + '</strong>';
                    }
                    if (h.notes) noteText += ' — ' + escHtml(h.notes);
                    html += '<div class="detail-history-item"><div class="hist-dot"></div><div class="hist-text">' + noteText + '</div><div class="hist-date">' + escHtml(histDate) + '</div></div>';
                });
                html += '</div></div>';
            }

            html += '</div>';
            document.getElementById('detailBody').innerHTML = html;
            document.getElementById('statusSelect').value = sub.status;
            if (sub.review_notes) document.getElementById('reviewNotesInput').value = sub.review_notes;
            document.getElementById('statusControls').style.display = 'block';
            var pdfLink = document.getElementById('detailPdfLink');
            pdfLink.href = '?url=' + prefix + '/form-submissions/download-pdf&id=' + sub.id_form_submission;
            pdfLink.style.display = 'inline-flex';
        })
        .catch(function() { document.getElementById('detailBody').innerHTML = '<p>Error de conexion.</p>'; });
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    var b = parseInt(bytes, 10);
    if (isNaN(b)) return '';
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
}

function confirmUpdateStatus() {
    if (!currentSubmissionId) return;
    var status = document.getElementById('statusSelect').value;
    showConfirm('¿Actualizar el estado a "' + status + '"?').then(function(ok) {
        if (ok) updateStatus();
    });
}

function updateStatus() {
    if (!currentSubmissionId) return;
    var status = document.getElementById('statusSelect').value;
    var notes = document.getElementById('reviewNotesInput').value.trim();
    var prefix = window.location.search.includes('gerente') ? 'gerente' : 'admin';
    fetch('?url=' + prefix + '/form-submissions/update-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: currentSubmissionId, status: status, review_notes: notes })
    })
    .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
    .then(function(data) {
        if (data.success) {
            var id = currentSubmissionId;
            allSubmissions.forEach(function(s) {
                if (parseInt(s.id_form_submission) === parseInt(id)) {
                    s.status = status;
                    s.review_notes = notes;
                }
            });
            var row = document.querySelector('tr[data-submission-id="' + id + '"]');
            if (!row) {
                var rows = document.querySelectorAll('#submissionsBody tr');
                for (var i = 0; i < rows.length; i++) {
                    var cell = rows[i].querySelector('td:first-child');
                    if (cell && cell.textContent === '#' + id) { row = rows[i]; break; }
                }
            }
            if (row) {
                var statusCell = row.querySelector('.submission-status-badge');
                if (statusCell) {
                    statusCell.className = 'submission-status-badge status-' + status.toLowerCase();
                    statusCell.textContent = status;
                }
            }
            updateStatusCounts();
            closeDetail();
            showToast('Estado actualizado correctamente.', 'success');
        } else {
            showToast(data.message || 'Error al actualizar.', 'error');
        }
    })
    .catch(function() { showToast('Error de conexion.', 'error'); });
}

document.addEventListener('DOMContentLoaded', function() {
    initFormFilter();
    updateStatusCounts();
    applyAllFilters();
});
</script>
<script src="<?= URL ?>assets/js/toast.js"></script>
</body>
</html>
