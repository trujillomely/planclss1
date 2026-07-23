<?php
$quotes         = $quotes ?? [];
$clientes       = $clientes ?? [];
$companies      = $companies ?? [];
$insuranceTypes = $insuranceTypes ?? [];
$producers      = $producers ?? [];

$calcInsuranceTypes    = $calcInsuranceTypes ?? [];
$calcInsuranceCompanies = $calcInsuranceCompanies ?? [];

$activeTab = $activeTab ?? ($_GET['tab'] ?? 'listado');

$statusOptions = ['Pendiente', 'Enviada', 'En análisis', 'Aceptada', 'Rechazada'];
$statusClass = [
    'Pendiente'   => 'st-cot-pendiente',
    'Enviada'     => 'st-cot-enviada',
    'En análisis' => 'st-cot-analisis',
    'Aceptada'    => 'st-cot-aceptada',
    'Rechazada'   => 'st-cot-rechazada',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Csrf::metaTag() ?>
    <title>Cotizaciones | Panel <?= $panelLabel ?? 'Administrador' ?></title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/toast.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/table-export.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
    /* ===== Cotizador styles ===== */
    .calc-hero{background:linear-gradient(135deg,var(--mod-accent,var(--arco-carbon)),color-mix(in srgb,var(--mod-accent,var(--arco-carbon)) 80%,#000));color:#fff;border-radius:var(--radius-card);padding:28px 32px;margin-bottom:20px;display:flex;align-items:center;gap:20px}
    .calc-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0}
    .calc-hero h2{margin:0 0 4px;font-size:1.3rem;font-weight:700}
    .calc-hero p{margin:0;opacity:.8;font-size:.88rem}
    .calc-grid{display:grid;grid-template-columns:5fr 7fr;gap:24px;align-items:start}
    .calc-form-card{position:relative;overflow:hidden}
    .calc-form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--mod-accent,var(--arco-siena)),color-mix(in srgb,var(--mod-accent,var(--arco-siena)) 60%,#fff))}
    .calc-results-card{position:relative;overflow:hidden}
    .calc-results-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#16a34a,#22c55e)}
    .calc-result-row{display:flex;align-items:center;gap:16px;padding:16px;border:1px solid var(--arco-perla);border-radius:12px;margin-bottom:10px;transition:all .2s;background:var(--arco-card-bg)}
    .calc-result-row:hover{border-color:var(--mod-accent,var(--arco-siena));box-shadow:0 4px 16px rgba(0,0,0,.06);transform:translateY(-1px)}
    .calc-result-row.best{border-color:#16a34a;background:linear-gradient(135deg,rgba(22,163,74,.04),rgba(34,197,94,.02))}
    .calc-result-rank{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0}
    .calc-result-info{flex:1;min-width:0}
    .calc-result-company{font-weight:700;font-size:.92rem;color:var(--arco-carbon)}
    .calc-result-rate{font-size:.78rem;color:var(--arco-siena);margin-top:2px}
    .calc-result-prices{text-align:right;flex-shrink:0}
    .calc-result-monthly{font-weight:700;font-size:1rem;color:var(--mod-accent,var(--arco-siena))}
    .calc-result-annual{font-size:.78rem;color:var(--arco-siena);margin-top:2px}
    .calc-result-action{flex-shrink:0}
    .calc-best-tag{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:#dcfce7;color:#16a34a;margin-right:8px}
    .calc-loading{text-align:center;padding:40px 20px}
    .calc-loading .spinner{width:40px;height:40px;border:3px solid var(--arco-perla);border-top-color:var(--mod-accent,var(--arco-siena));border-radius:50%;animation:calcSpin .8s linear infinite;margin:0 auto 12px}
    @keyframes calcSpin{to{transform:rotate(360deg)}}
    .calc-summary-box{background:var(--arco-lino);border:1px solid var(--arco-perla);border-radius:10px;padding:14px 16px;font-size:.84rem;line-height:1.5}
    .calc-summary-box strong{color:var(--mod-accent,var(--arco-siena))}
    .calc-form-card .form-group label{font-weight:600;font-size:.82rem;color:var(--arco-carbon);margin-bottom:4px}
    .calc-form-card .form-group input,
    .calc-form-card .form-group select,
    .calc-form-card .form-group textarea{width:100%;padding:10px 14px;border:1.5px solid var(--arco-perla);border-radius:8px;font-size:.88rem;background:var(--arco-card-bg);transition:border-color .2s,box-shadow .2s}
    .calc-form-card .form-group input:focus,
    .calc-form-card .form-group select:focus,
    .calc-form-card .form-group textarea:focus{outline:none;border-color:var(--mod-accent,var(--arco-siena));box-shadow:0 0 0 3px rgba(0,0,0,.04)}
    .calc-form-card .form-group{margin-bottom:16px}
    .calc-form-card .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .calc-form-card .btn-primary{margin-top:4px;padding:12px;font-size:.92rem;font-weight:700;border-radius:10px;transition:all .2s}
    .calc-form-card .btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
    @media(max-width:768px){.calc-grid{grid-template-columns:1fr}.calc-hero{flex-direction:column;text-align:center;padding:20px}.calc-result-row{flex-wrap:wrap}.calc-result-prices{width:100%;text-align:left;margin-top:8px;padding-top:8px;border-top:1px solid var(--arco-perla)}}

    /* ===== Comparador styles ===== */
    .cmp-hero{background:linear-gradient(135deg,var(--mod-accent,var(--arco-carbon)),color-mix(in srgb,var(--mod-accent,var(--arco-carbon)) 80%,#000));color:#fff;border-radius:var(--radius-card);padding:24px 28px;margin-bottom:20px;display:flex;align-items:center;gap:16px}
    .cmp-hero-icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
    .cmp-hero h2{margin:0 0 2px;font-size:1.2rem;font-weight:700}
    .cmp-hero p{margin:0;opacity:.8;font-size:.85rem}
    .cmp-loading{text-align:center;padding:40px 20px}
    .cmp-loading .spinner{width:36px;height:36px;border:3px solid var(--arco-perla);border-top-color:var(--mod-accent,var(--arco-siena));border-radius:50%;animation:cmpSpin .8s linear infinite;margin:0 auto 10px}
    @keyframes cmpSpin{to{transform:rotate(360deg)}}
    .cmp-form-card{position:relative;overflow:hidden}
    .cmp-form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--mod-accent,var(--arco-siena)),color-mix(in srgb,var(--mod-accent,var(--arco-siena)) 60%,#fff))}
    .cmp-form-grid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr auto;gap:16px;align-items:end}
    @media(max-width:900px){.cmp-form-grid{grid-template-columns:1fr 1fr;}.cmp-form-grid .cmp-submit-col{grid-column:1/-1;}}
    @media(max-width:540px){.cmp-form-grid{grid-template-columns:1fr;}}
    .cmp-actions{display:flex;gap:8px;flex-wrap:wrap}
    .cmp-card-body{padding:20px}
    .compare-card{border:2px solid var(--arco-perla);border-radius:var(--radius-card);transition:all .25s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden;background:var(--arco-card-bg,#fff);box-shadow:var(--shadow-card)}
    .compare-card:hover{border-color:var(--mod-accent,var(--arco-siena));box-shadow:0 8px 30px rgba(140,123,110,.15);transform:translateY(-6px) scale(1.01)}
    .compare-card.best{border-color:#16a34a}
    .compare-card.cheapest{border-color:#f59e0b}
    .compare-badge{position:absolute;top:12px;right:12px;font-size:.7rem;padding:4px 10px;border-radius:20px;font-weight:700;letter-spacing:.3px}
    .compare-price{text-align:center;padding:20px 15px;background:linear-gradient(135deg,var(--arco-lino,#f8fafc),var(--mod-accent-soft,#eef2ff));border-radius:12px;margin:15px 0}
    .compare-price .amount{font-size:2rem;font-weight:800;color:var(--mod-accent,var(--arco-siena));line-height:1}
    .compare-price .period{font-size:.8rem;color:var(--arco-siena)}
    .compare-feature{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--arco-perla);font-size:.85rem;color:var(--arco-carbon)}
    .compare-feature:last-child{border-bottom:none}
    .compare-feature .icon{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;flex-shrink:0}
    .compare-feature .icon.yes{background:#dcfce7;color:#16a34a}
    .compare-feature .icon.no{background:#fee2e2;color:#dc2626}
    .compare-feature .icon.partial{background:#fef3c7;color:#d97706}
    .compare-stars{color:#f59e0b;font-size:.9rem}
    .compare-section-title{font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--arco-siena);margin:12px 0 6px;font-weight:700}
    .company-logo-placeholder{width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,var(--mod-accent,#2563eb),#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.3rem;margin:0 auto 10px}
    .compare-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px}
    .side-by-side{overflow-x:auto}
    .side-by-side table{min-width:100%;width:100%;border-collapse:collapse;font-size:.86rem}
    .side-by-side th,.side-by-side td{padding:10px 14px;text-align:center;min-width:140px;border-bottom:1px solid var(--arco-perla);color:var(--arco-carbon)}
    .side-by-side th{position:sticky;top:0;background:var(--arco-card-bg,#fff);z-index:1;font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--arco-siena)}
    .side-by-side tbody tr:hover{background:var(--arco-lino)}
    .side-by-side td.fw-bold{font-weight:700;color:var(--mod-accent,var(--arco-siena))}
    .side-by-side tr.section-row td{background:var(--mod-accent-soft,var(--arco-lino));font-weight:700;text-align:left;color:var(--arco-carbon)}
    .verdict-bar{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;border-radius:var(--radius-card);padding:16px 24px;display:flex;align-items:center;gap:16px;margin-bottom:24px}
    .verdict-bar strong{color:#fff}

    /* ===== Unified tab nav ===== */
    .cotizaciones-tab-nav{display:flex;gap:0;margin-bottom:24px;background:var(--arco-card-bg,#fff);border-radius:var(--radius-card);box-shadow:var(--shadow-card);overflow:hidden;border:1px solid var(--arco-perla)}
    .cotizaciones-tab-nav a{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:14px 20px;font-size:.9rem;font-weight:600;color:var(--arco-siena);text-decoration:none;transition:all .2s;border-bottom:3px solid transparent}
    .cotizaciones-tab-nav a:hover{background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena))}
    .cotizaciones-tab-nav a.active{color:var(--mod-accent,var(--arco-siena));border-bottom-color:var(--mod-accent,var(--arco-siena));background:var(--arco-lino)}
    .cotizaciones-tab-nav a i{font-size:1.1rem}
    </style>

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
        $pageTitle    = 'Cotizaciones';
        $pageModule   = 'cotizaciones';
        $pageSubtitle = 'Gestiona, calcula y compara cotizaciones';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">

            <!-- Tab Navigation -->
            <div class="cotizaciones-tab-nav">
                <a href="?url=<?= $panelPrefix ?? 'admin' ?>/cotizaciones&tab=listado" class="<?= $activeTab === 'listado' ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> Listado
                </a>
                <a href="?url=<?= $panelPrefix ?? 'admin' ?>/cotizaciones&tab=cotizador" class="<?= $activeTab === 'cotizador' ? 'active' : '' ?>">
                    <i class="bi bi-calculator"></i> Cotizador
                </a>
                <a href="?url=<?= $panelPrefix ?? 'admin' ?>/cotizaciones&tab=comparador" class="<?= $activeTab === 'comparador' ? 'active' : '' ?>">
                    <i class="bi bi-arrow-left-right"></i> Comparador
                </a>
            </div>

            <!-- ================================================================ -->
            <!-- TAB: LISTADO -->
            <!-- ================================================================ -->
            <div class="tab-content" id="tab-listado" style="display:<?= $activeTab === 'listado' ? 'block' : 'none' ?>">

                <?php
                $totalCotizaciones = count($quotes);
                $enviadas   = count(array_filter($quotes, fn($q) => $q['status'] === 'Enviada'));
                $pendientes = count(array_filter($quotes, fn($q) => $q['status'] === 'Pendiente'));
                $aceptadas  = count(array_filter($quotes, fn($q) => $q['status'] === 'Aceptada'));
                ?>

                <!-- KPIs -->
                <div class="kpi-row">
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Cotizaciones totales</div>
                            <div class="kpi-value"><?= $totalCotizaciones ?></div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon success"><i class="bi bi-send"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Enviadas</div>
                            <div class="kpi-value"><?= $enviadas ?></div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon warning"><i class="bi bi-clock-history"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Pendientes</div>
                            <div class="kpi-value"><?= $pendientes ?></div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Aceptadas</div>
                            <div class="kpi-value"><?= $aceptadas ?></div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="content-card">
                    <div class="content-card-header">
                        <div>
                            <div class="content-card-title">Listado de cotizaciones</div>
                            <div class="content-card-sub">Cotizaciones registradas en el sistema</div>
                        </div>
                    </div>
                    <div class="content-card-body">

                        <div class="filter-bar">
                            <input type="text" id="searchQuotes" placeholder="Buscar por folio, cliente o aseguradora..." oninput="quoteList_applyFilters()">

                            <select id="filterStatus" onchange="quoteList_applyFilters()">
                                <option value="">Todos los estatus</option>
                                <?php foreach ($statusOptions as $st): ?>
                                    <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select id="filterType" onchange="quoteList_applyFilters()">
                                <option value="">Todos los ramos</option>
                                <?php foreach ($insuranceTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type['name']) ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select id="filterCompany" onchange="quoteList_applyFilters()">
                                <option value="">Todas las compañías</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= htmlspecialchars($company['name']) ?>"><?= htmlspecialchars($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div data-arco-export="quotes" data-arco-export-label="Exportar"></div>
                            <?php if (Auth::can('cotizaciones', 'crear')): ?>
                            <button class="btn-agregar" onclick="quoteList_openModal()">
                                <i class="bi bi-plus-lg"></i> Nueva cotización
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="tbl-wrap">
                            <table class="admin-table" id="quotesTable" data-pagination>
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Ramo</th>
                                        <th>Compañía</th>
                                        <th>Productor</th>
                                        <th>Prima estimada</th>
                                        <th>Estatus</th>
                                        <th>Fecha creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($quotes)): ?>
                                        <?php foreach ($quotes as $quote):
                                            $clientLabel = $quote['user_name'] && trim($quote['user_name']) !== ''
                                                ? $quote['user_name']
                                                : ($quote['client_name'] ?? '—');
                                            $clientEmail = $quote['user_email'] ?? $quote['client_email'] ?? '';
                                            $badge = $statusClass[$quote['status']] ?? 'badge-inactive';
                                        ?>
                                            <tr data-status="<?= htmlspecialchars($quote['status']) ?>"
                                                data-type="<?= htmlspecialchars($quote['insurance_type_name'] ?? '') ?>"
                                                data-company="<?= htmlspecialchars($quote['insurance_company_name'] ?? '') ?>">
                                                <td class="fw"><?= htmlspecialchars($quote['folio']) ?></td>
                                                <td>
                                                    <div><?= htmlspecialchars($clientLabel) ?></div>
                                                    <?php if ($clientEmail): ?><div class="text-muted" style="font-size:.78rem;"><?= htmlspecialchars($clientEmail) ?></div><?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($quote['insurance_type_name'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($quote['insurance_company_name'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($quote['producer_name'] ?? '—') ?></td>
                                                <td><?= $quote['estimated_premium'] !== null ? 'Q ' . number_format($quote['estimated_premium'], 2) : '—' ?></td>
                                                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($quote['status']) ?></span></td>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($quote['created_at']))) ?></td>
                                                <td>
                                                    <div style="display:flex; gap:6px;">
                                                        <?php if (Auth::can('cotizaciones', 'editar')): ?>
                                                            <?php if ($quote['status'] === 'Pendiente'): ?>
                                                            <button class="btn-icon" title="Marcar como enviada" onclick="quoteList_changeStatus(<?= (int)$quote['id_quote'] ?>, 'Enviada')">
                                                                <i class="bi bi-send"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                            <?php if (in_array($quote['status'], ['Enviada', 'En análisis'], true)): ?>
                                                            <button class="btn-icon" title="Marcar como aceptada" onclick="quoteList_changeStatus(<?= (int)$quote['id_quote'] ?>, 'Aceptada')">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                            <button class="btn-icon" title="Marcar como rechazada" onclick="quoteList_changeStatus(<?= (int)$quote['id_quote'] ?>, 'Rechazada')">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                            <button class="btn-icon" title="Editar" onclick='quoteList_openModalEdit(<?= json_encode($quote) ?>)'>
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (Auth::can('cotizaciones', 'eliminar')): ?>
                                                            <button class="btn-icon" title="Eliminar" onclick="quoteList_deleteQuote(<?= (int)$quote['id_quote'] ?>)">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
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
                                                    <p>No se encontraron cotizaciones. Usa "Nueva cotización" para crear la primera.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div><!-- /tab-listado -->

            <!-- ================================================================ -->
            <!-- TAB: COTIZADOR -->
            <!-- ================================================================ -->
            <div class="tab-content" id="tab-cotizador" style="display:<?= $activeTab === 'cotizador' ? 'block' : 'none' ?>">

                <div class="calc-hero">
                  <div class="calc-hero-icon">
                    <i class="bi bi-calculator"></i>
                  </div>
                  <div>
                    <h2>Cotizador Automático</h2>
                    <p>Calcula primas comparativas en tiempo real y guarda las mejores opciones para tus clientes</p>
                  </div>
                </div>

                <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
                  <a href="?url=<?= $panelPrefix ?? 'admin' ?>/quoteRates" class="btn-secondary"><i class="bi bi-gear"></i> Gestionar Tarifas</a>
                </div>

                <div class="calc-grid">
                  <div class="content-card calc-form-card">
                    <div class="content-card-header">
                      <div class="content-card-title"><i class="bi bi-sliders"></i> Parámetros de Cotización</div>
                    </div>
                    <div class="content-card-body">
                      <form id="calcForm" autocomplete="off">
                        <div class="form-group">
                          <label>Ramo de Seguro <span style="color:#991b1b">*</span></label>
                          <select id="calcType" required>
                            <option value="">Seleccionar ramo...</option>
                            <?php foreach ($calcInsuranceTypes as $t): ?>
                              <option value="<?= $t['id_insurance_type'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-group">
                          <label>Aseguradora (Opcional)</label>
                          <select id="calcCompany">
                            <option value="">Todas las aseguradoras</option>
                            <?php foreach ($calcInsuranceCompanies as $c): ?>
                              <option value="<?= $c['id_insurance_company'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-row">
                          <div class="form-group">
                            <label>Monto Asegurado <span style="color:#991b1b">*</span></label>
                            <input type="text" id="calcAmount" placeholder="500,000" data-validate="decimal" required>
                          </div>
                          <div class="form-group">
                            <label>Edad <span style="color:#991b1b">*</span></label>
                            <input type="text" id="calcAge" placeholder="35" data-validate="numeros" required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label>Deducible (%)</label>
                          <select id="calcDeductible">
                            <option value="0">Sin deducible</option>
                            <option value="1">1%</option>
                            <option value="2">2%</option>
                            <option value="3">3%</option>
                            <option value="5">5%</option>
                          </select>
                        </div>
                        <button type="submit" class="btn-primary" id="btnCalc" style="width:100%; justify-content:center;">
                          <i class="bi bi-calculator"></i> Calcular Primas
                        </button>
                      </form>
                    </div>
                  </div>

                  <div class="content-card calc-results-card">
                    <div class="content-card-header">
                      <div class="content-card-title"><i class="bi bi-bar-chart-line"></i> Comparativa de Primas</div>
                      <span class="badge" id="resultCount">0 cotizaciones</span>
                    </div>
                    <div class="content-card-body">
                      <div id="calcResults" class="empty-state">
                        <div class="calc-loading">
                          <div class="spinner"></div>
                          <p style="color:var(--arco-siena)">Ingresa los datos y presiona <strong>Calcular</strong></p>
                        </div>
                      </div>
                      <div id="calcResultsTable" style="display:none;">
                        <div id="resultsBody"></div>
                      </div>
                    </div>
                  </div>
                </div>

            </div><!-- /tab-cotizador -->

            <!-- ================================================================ -->
            <!-- TAB: COMPARADOR -->
            <!-- ================================================================ -->
            <div class="tab-content" id="tab-comparador" style="display:<?= $activeTab === 'comparador' ? 'block' : 'none' ?>">

                <div class="cmp-hero">
                  <div class="cmp-hero-icon"><i class="bi bi-arrow-left-right"></i></div>
                  <div>
                    <h2>Comparador de Aseguradoras</h2>
                    <p>Compara coberturas, precios y beneficios lado a lado</p>
                  </div>
                </div>

                <div class="content-card cmp-form-card">
                    <div class="content-card-header">
                        <div>
                            <div class="content-card-title">Parámetros de Comparación</div>
                            <div class="content-card-sub">Ingresa los datos para comparar opciones de seguros</div>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <form id="compareForm">
                            <div class="cmp-form-grid">
                                <div class="form-group">
                                    <label>Ramo de Seguro <span style="color:#c0483f">*</span></label>
                                    <select id="cmpType" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($calcInsuranceTypes as $t): ?>
                                            <option value="<?= $t['id_insurance_type'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monto Asegurado <span style="color:#c0483f">*</span></label>
                                    <input type="number" id="cmpAmount" placeholder="500,000" min="1" required data-validate="numeros">
                                </div>
                                <div class="form-group">
                                    <label>Edad <span style="color:#c0483f">*</span></label>
                                    <input type="number" id="cmpAge" placeholder="35" min="1" max="100" required data-validate="numeros">
                                </div>
                                <div class="form-group">
                                    <label>Deducible</label>
                                    <select id="cmpDeduct">
                                        <option value="0">Sin deducible</option>
                                        <option value="1">1%</option>
                                        <option value="2">2%</option>
                                        <option value="3">3%</option>
                                        <option value="5">5%</option>
                                    </select>
                                </div>
                                <div class="form-group cmp-submit-col">
                                    <button type="submit" class="btn-primary" id="btnCompare">
                                        <i class="bi bi-arrow-left-right"></i> Comparar Ahora
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="verdictBar" class="verdict-bar" style="display:none"></div>

                <div id="cardsView">
                    <div class="compare-grid" id="compareGrid"></div>
                </div>

                <div id="tableView" style="display:none">
                    <div class="content-card">
                        <div class="content-card-header">
                            <div>
                                <div class="content-card-title">Comparativa Lado a Lado</div>
                                <div class="content-card-sub">Vista detallada de todas las aseguradoras</div>
                            </div>
                        </div>
                        <div class="content-card-body">
                            <div class="side-by-side" id="sideBySide"></div>
                        </div>
                    </div>
                </div>

                <div id="emptyState" class="empty-state" style="display:none">
                    <i class="bi bi-arrow-left-right"></i>
                    <p>Selecciona un ramo y monto para comparar</p>
                    <p style="font-size:.8rem;color:var(--arco-siena)">El sistema mostrará todas las opciones de aseguradoras con sus coberturas, precios y beneficios</p>
                </div>

            </div><!-- /tab-comparador -->

        </div><!-- /dash-content -->
    </div><!-- /dash-main -->
</div><!-- /dash-layout -->

<!-- ================================================================ -->
<!-- MODALS -->
<!-- ================================================================ -->

<!-- Modal Agregar / Editar Cotización (Listado) -->
<div class="modal-overlay" id="quoteModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Nueva cotización</h3>
            <button class="modal-close" onclick="quoteList_closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="quoteForm" onsubmit="quoteList_submitForm(event)">
            <input type="hidden" id="quote_id" name="id">
            <div class="modal-body">

                <div class="form-group">
                    <label>Cliente registrado</label>
                    <select id="id_user" name="id_user" onchange="quoteList_toggleClientFields()">
                        <option value="">— Prospecto sin cuenta —</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int)$cliente['id'] ?>">
                                <?= htmlspecialchars($cliente['username'] . ' ' . $cliente['lastname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="clientNameGroup">
                    <label>Nombre del prospecto</label>
                    <input type="text" id="client_name" name="client_name" placeholder="Nombre completo" data-validate="letras">
                </div>

                <div class="form-group" id="clientEmailGroup">
                    <label>Correo del prospecto</label>
                    <input type="email" id="client_email" name="client_email" placeholder="correo@ejemplo.com" data-validate="email">
                </div>

                <div class="form-group">
                    <label>Aseguradora</label>
                    <select id="id_insurance_company" name="id_insurance_company">
                        <option value="">Seleccione una aseguradora</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= (int)$company['id_insurance_company'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ramo (tipo de seguro)</label>
                    <select id="id_insurance_type" name="id_insurance_type">
                        <option value="">Seleccione un ramo</option>
                        <?php foreach ($insuranceTypes as $type): ?>
                            <option value="<?= (int)$type['id_insurance_type'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Productor</label>
                    <select id="id_producer" name="id_producer">
                        <option value="">Sin asignar</option>
                        <?php foreach ($producers as $producer): ?>
                            <option value="<?= (int)$producer['id_producer'] ?>"><?= htmlspecialchars($producer['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Prima estimada</label>
                    <input type="number" step="0.01" min="0" id="estimated_premium" name="estimated_premium" placeholder="0.00" data-validate="decimal" required>
                </div>

                <div class="form-group">
                    <label>Válida hasta</label>
                    <input type="date" id="valid_until" name="valid_until">
                </div>

                <div class="form-group">
                    <label>Notas</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Observaciones de la cotización..." data-validate="letras-numeros"></textarea>
                </div>

                <div class="form-group">
                    <label>Estatus</label>
                    <select id="status" name="status">
                        <?php foreach ($statusOptions as $st): ?>
                            <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="quoteList_closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar cotización</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Guardar Cotización (Cotizador) -->
<div class="modal-overlay" id="saveQuoteModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-save"></i> Guardar Cotización</h3>
      <button class="modal-close" onclick="calc_closeSaveModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="saveQuoteIdRate">
      <div class="form-group">
        <label>Nombre del Cliente <span style="color:#991b1b">*</span></label>
        <input type="text" id="saveClientName" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" id="saveClientEmail">
        </div>
        <div class="form-group">
          <label>Vigencia</label>
          <input type="date" id="saveValidUntil" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Asesor</label>
        <select id="saveProducer">
          <option value="">Sin asesor</option>
          <?php foreach ($producers as $p): ?>
            <option value="<?= $p['id_producer'] ?>"><?= htmlspecialchars($p['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea id="saveNotes" rows="2" placeholder="Notas adicionales..."></textarea>
      </div>
      <div id="saveQuoteSummary" class="calc-summary-box"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-secondary" onclick="calc_closeSaveModal()">Cancelar</button>
      <button type="button" class="btn-primary" id="btnSaveQuote">
        <i class="bi bi-save"></i> Guardar
      </button>
    </div>
  </div>
</div>

<!-- Modal Guardar Cotización (Comparador) -->
<div class="modal-overlay" id="cmpSaveModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-file-earmark-text"></i> Generar Cotización</h3>
      <button class="modal-close" onclick="cmp_closeSaveModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cmpSaveIdx">
      <div id="cmpSaveSummary" style="background:var(--arco-lino);border:1px solid var(--arco-perla);border-radius:10px;padding:14px 16px;font-size:.84rem;line-height:1.5;margin-bottom:16px;"></div>
      <div class="form-group">
        <label>Nombre del Cliente <span style="color:#c0483f">*</span></label>
        <input type="text" id="cmpSaveClientName" data-validate="letras" required placeholder="Nombre completo del cliente">
      </div>
      <div class="form-group">
        <label>Email del Cliente</label>
        <input type="email" id="cmpSaveClientEmail" data-validate="email" placeholder="correo@ejemplo.com">
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-secondary" onclick="cmp_closeSaveModal()">Cancelar</button>
      <button type="button" class="btn-primary" id="cmpSaveBtn">
        <i class="bi bi-save"></i> Guardar Cotización
      </button>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- JAVASCRIPT -->
<!-- ================================================================ -->

<script>
/* ================================================================
   Tab Switching
   ================================================================ */
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.style.display = 'none';
    });
    document.getElementById('tab-' + tab).style.display = 'block';

    document.querySelectorAll('.cotizaciones-tab-nav a').forEach(function(el) {
        el.classList.remove('active');
    });
    var activeLink = document.querySelector('.cotizaciones-tab-nav a[href*="tab=' + tab + '"]');
    if (activeLink) activeLink.classList.add('active');

    var baseUrl = window.location.pathname + '?url=<?= urlencode($panelPrefix ?? "admin") ?>/cotizaciones&tab=' + tab;
    window.history.replaceState(null, '', baseUrl);
}
</script>

<script>
/* ================================================================
   LISTADO – Quotes Management
   ================================================================ */
function quoteList_applyFilters() {
    var term    = document.getElementById('searchQuotes').value.toLowerCase();
    var status  = document.getElementById('filterStatus').value;
    var type    = document.getElementById('filterType').value;
    var company = document.getElementById('filterCompany').value;

    document.querySelectorAll('#quotesTable tbody tr').forEach(function(row) {
        if (!row.dataset.status) return;
        var matchesTerm    = row.textContent.toLowerCase().includes(term);
        var matchesStatus  = !status  || row.dataset.status === status;
        var matchesType    = !type    || row.dataset.type === type;
        var matchesCompany = !company || row.dataset.company === company;
        row.style.display = (matchesTerm && matchesStatus && matchesType && matchesCompany) ? '' : 'none';
    });
}

function quoteList_toggleClientFields() {
    var hasClient = !!document.getElementById('id_user').value;
    document.getElementById('clientNameGroup').style.display  = hasClient ? 'none' : '';
    document.getElementById('clientEmailGroup').style.display = hasClient ? 'none' : '';
}

function quoteList_openModal() {
    document.getElementById('modalTitle').textContent = 'Nueva cotización';
    document.getElementById('quote_id').value = '';
    document.getElementById('quoteForm').reset();
    document.getElementById('status').value = 'Pendiente';
    quoteList_toggleClientFields();
    document.getElementById('quoteModal').classList.add('open');
}

function quoteList_openModalEdit(quote) {
    document.getElementById('modalTitle').textContent = 'Editar cotización';
    document.getElementById('quote_id').value            = quote.id_quote ?? '';
    document.getElementById('id_user').value              = quote.id_user ?? '';
    document.getElementById('client_name').value          = quote.client_name ?? '';
    document.getElementById('client_email').value         = quote.client_email ?? '';
    document.getElementById('id_insurance_company').value = quote.id_insurance_company ?? '';
    document.getElementById('id_insurance_type').value    = quote.id_insurance_type ?? '';
    document.getElementById('id_producer').value          = quote.id_producer ?? '';
    document.getElementById('estimated_premium').value    = quote.estimated_premium ?? '';
    document.getElementById('valid_until').value           = quote.valid_until ?? '';
    document.getElementById('notes').value                 = quote.notes ?? '';
    document.getElementById('status').value                = quote.status ?? 'Pendiente';
    quoteList_toggleClientFields();
    document.getElementById('quoteModal').classList.add('open');
}

function quoteList_closeModal() {
    document.getElementById('quoteModal').classList.remove('open');
}

document.getElementById('quoteModal').addEventListener('click', function (e) {
    if (e.target === this) quoteList_closeModal();
});

function quoteList_submitForm(e) {
    e.preventDefault();
    var id  = document.getElementById('quote_id').value;
    var userId = document.getElementById('id_user').value;
    var clientName = document.getElementById('client_name').value.trim();
    if (!userId && !clientName) {
        showToast('Selecciona un cliente o ingresa el nombre del prospecto.', 'error');
        return;
    }
    var url = id ? '?url=admin/quotes/update' : '?url=admin/quotes/store';
    var btn = e.target.querySelector('.btn-primary');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(url, { method: 'POST', body: new FormData(e.target) })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(function(data) {
            if (data.success) {
                quoteList_closeModal();
                location.reload();
            } else {
                showToast(data.message || 'Ocurrió un error.', 'error');
                btn.disabled = false;
                btn.textContent = 'Guardar cotización';
            }
        })
        .catch(function() {
            showToast('Error de conexión. Intente nuevamente.', 'error');
            btn.disabled = false;
            btn.textContent = 'Guardar cotización';
        });
}

function quoteList_changeStatus(id, status) {
    fetch('?url=admin/quotes/change-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
    .then(function(data) {
        if (data.success) location.reload();
        else showToast(data.message || 'Error al actualizar el estatus.', 'error');
    })
    .catch(function() { showToast('Error de conexión. Intente nuevamente.', 'error'); });
}

function quoteList_deleteQuote(id) {
    showConfirm('¿Eliminar esta cotización? Esta acción no se puede deshacer.').then(function(ok) {
        if (!ok) return;
        fetch('?url=admin/quotes/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(function(data) {
            if (data.success) location.reload();
            else showToast(data.message || 'Error al eliminar la cotización.', 'error');
        })
        .catch(function() { showToast('Error de conexión. Intente nuevamente.', 'error'); });
    });
}
</script>

<script>
/* ================================================================
   COTIZADOR – Quote Calculator
   ================================================================ */
document.addEventListener('DOMContentLoaded', function(){
  var calcForm = document.getElementById('calcForm');
  var btnCalc = document.getElementById('btnCalc');

  calcForm.addEventListener('submit', async function(e){
    e.preventDefault();
    btnCalc.disabled = true;
    btnCalc.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculando...';

    document.getElementById('calcResultsTable').innerHTML = '<div class="calc-loading"><div class="spinner"></div><p style="color:var(--arco-siena)">Calculando primas...</p></div>';
    document.getElementById('calcResultsTable').style.display = 'block';
    document.getElementById('calcResults').style.display = 'none';

    try {
      var fd = new FormData();
      fd.append('id_insurance_type', document.getElementById('calcType').value);
      fd.append('id_insurance_company', document.getElementById('calcCompany').value);
      fd.append('insured_amount', document.getElementById('calcAmount').value);
      fd.append('client_age', document.getElementById('calcAge').value);
      fd.append('deductible_pct', document.getElementById('calcDeductible').value);
      fd.append('_csrf_token', document.querySelector('meta[name="_csrf_token"]')?.content || '');

      var res = await fetch('?url=quote/ajaxCalculate', { method: 'POST', body: fd });
      var text = await res.text();
      var json;
      try { json = JSON.parse(text); } catch(e) { throw new Error('Respuesta del servidor no válida (HTTP ' + res.status + ')'); }

      document.getElementById('calcResults').style.display = json.success && json.data.length ? 'none' : 'block';
      document.getElementById('calcResultsTable').style.display = json.success && json.data.length ? 'block' : 'none';
      document.getElementById('resultCount').textContent = json.count + ' cotizaciones';

      var tbody = document.getElementById('resultsBody');
      tbody.innerHTML = '';

      if (json.success && json.data.length) {
        json.data.forEach(function(r, i){
          var rank = i + 1;
          var isBest = i === 0;
          var rankBg = isBest ? 'background:#dcfce7;color:#16a34a' : 'background:var(--arco-lino);color:var(--arco-siena)';
          var rowClass = isBest ? 'best' : '';
          var bestTag = isBest ? '<span class="calc-best-tag"><i class="bi bi-trophy-fill"></i> MEJOR</span>' : '';

          tbody.innerHTML += '<div class="calc-result-row ' + rowClass + '">' +
            '<div class="calc-result-rank" style="' + rankBg + '">' + rank + '</div>' +
            '<div class="calc-result-info">' +
              '<div class="calc-result-company">' + bestTag + r.company_name + '</div>' +
              '<div class="calc-result-rate">' + r.rate_name + ' · Base ' + r.base_rate_pct + '% · Factor edad ' + r.age_factor + 'x</div>' +
            '</div>' +
            '<div class="calc-result-prices">' +
              '<div class="calc-result-monthly">Q' + calc_numberFormat(r.monthly_premium) + '</div>' +
              '<div class="calc-result-annual">Q' + calc_numberFormat(r.annual_premium) + '/año</div>' +
            '</div>' +
            '<div class="calc-result-action">' +
              '<button class="btn-icon" onclick=\'calc_openSave(' + JSON.stringify(r).replace(/'/g,"&#39;") + ')\' title="Guardar">' +
                '<i class="bi bi-bookmark-plus"></i>' +
              '</button>' +
            '</div>' +
          '</div>';
        });
      }
    } catch(err) {
      showToast('Error al calcular: ' + err.message, 'error');
    } finally {
      btnCalc.disabled = false;
      btnCalc.innerHTML = '<i class="bi bi-calculator"></i> Calcular Primas';
    }
  });
});

function calc_numberFormat(v){ return parseFloat(v).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2}); }

var calc_selectedRate = null;

function calc_openSave(rate){
  calc_selectedRate = rate;
  document.getElementById('saveQuoteIdRate').value = rate.id_quote_rate;
  document.getElementById('saveQuoteSummary').innerHTML =
    '<strong>' + rate.company_name + '</strong> · ' + rate.rate_name + '<br>' +
    'Prima mensual: <strong>Q' + calc_numberFormat(rate.monthly_premium) + '</strong> · Anual: <strong>Q' + calc_numberFormat(rate.annual_premium) + '</strong><br>' +
    'Deducible: ' + rate.deductible_pct + '% (Q' + calc_numberFormat(rate.deductible_amount) + ') · Factor edad: ' + rate.age_factor + 'x';
  document.getElementById('saveQuoteModal').classList.add('open');
}

function calc_closeSaveModal(){
  document.getElementById('saveQuoteModal').classList.remove('open');
}

document.getElementById('saveQuoteModal').addEventListener('click', function(e){
  if (e.target === this) calc_closeSaveModal();
});

document.getElementById('btnSaveQuote').addEventListener('click', async function(){
  var name = document.getElementById('saveClientName').value.trim();
  if (!name) { showToast('Ingresa el nombre del cliente', 'warning'); return; }
  if (!calc_selectedRate) return;
  this.disabled = true;

  var payload = {
    client_name: name,
    client_email: document.getElementById('saveClientEmail').value,
    id_producer: document.getElementById('saveProducer').value || null,
    valid_until: document.getElementById('saveValidUntil').value,
    notes: document.getElementById('saveNotes').value,
    id_insurance_type: document.getElementById('calcType').value,
    id_insurance_company: calc_selectedRate.id_insurance_company,
    id_user: null,
    estimated_premium: calc_selectedRate.annual_premium,
    client_age: document.getElementById('calcAge').value,
    insured_amount: calc_selectedRate.insured_amount,
    deductible: calc_selectedRate.deductible_pct,
    coverage_summary: [calc_selectedRate],
  };

  try {
    var res = await fetch('?url=quote/ajaxSaveFromCalc', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    var _t=await res.text();var json;try{json=JSON.parse(_t)}catch(e){json={success:false}};

    if (json.success) {
      showToast('Cotización Guardada — Folio: ' + json.folio, 'success');
      calc_closeSaveModal();
      document.getElementById('saveClientName').value = '';
      document.getElementById('saveClientEmail').value = '';
      document.getElementById('saveNotes').value = '';
    } else {
      showToast('Error: ' + json.message, 'error');
    }
  } catch(err) {
    showToast('Error: ' + err.message, 'error');
  } finally {
    this.disabled = false;
  }
});
</script>

<script>
/* ================================================================
   COMPARADOR – Insurance Comparator
   ================================================================ */
var cmp_compareData = [];
var cmp_currentView = 'cards';

document.getElementById('compareForm').addEventListener('submit', async function(e){
  e.preventDefault();
  var btn = document.getElementById('btnCompare');
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Comparando...';

  try {
    var fd = new FormData();
    fd.append('id_insurance_type', document.getElementById('cmpType').value);
    fd.append('insured_amount', document.getElementById('cmpAmount').value);
    fd.append('client_age', document.getElementById('cmpAge').value);
    fd.append('deductible_pct', document.getElementById('cmpDeduct').value);

    var res = await fetch('?url=quote/ajaxCompare', {method:'POST', body:fd});
    var _t=await res.text();var json;try{json=JSON.parse(_t)}catch(e){json={success:false}};

    if (!json.success || !json.data.length) {
      document.getElementById('emptyState').innerHTML = '<i class="bi bi-exclamation-triangle"></i><p style="font-weight:600;font-size:1rem;margin-bottom:4px">No se encontraron opciones</p><p style="font-size:.8rem;color:var(--arco-siena)">Intenta con otros parámetros o verifica que existan tarifas para este ramo</p>';
      document.getElementById('emptyState').style.display = 'block';
      document.getElementById('cardsView').style.display = 'none';
      document.getElementById('tableView').style.display = 'none';
      document.getElementById('verdictBar').style.display = 'none';
      return;
    }

    cmp_compareData = json.data;
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('cardsView').style.display = 'block';

    if (json.cheapest && json.best) {
      var cheapestIdx = json.data.indexOf(json.cheapest);
      var bestIdx = json.data.indexOf(json.best);
      document.getElementById('verdictBar').style.display = 'flex';
      document.getElementById('verdictBar').innerHTML = '<i class="bi bi-trophy-fill" style="font-size:1.8rem"></i><div><strong>Mejor relación precio-calidad:</strong> ' + json.best.company_name + ' — Q' + cmp_num(json.best.monthly_premium) + '/mes' + (cheapestIdx !== bestIdx ? '<br><span style="opacity:.85"><i class="bi bi-tag-fill" style="margin-right:4px"></i>Más económico: ' + json.cheapest.company_name + ' — Q' + cmp_num(json.cheapest.monthly_premium) + '/mes</span>' : '') + '</div>';
    }

    cmp_renderCards(json.data);
    cmp_renderTable(json.data);
  } catch(err) {
    showToast('Error al comparar: ' + err.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-arrow-left-right"></i> Comparar Ahora';
  }
});

function cmp_renderCards(data){
  var grid = document.getElementById('compareGrid');
  grid.innerHTML = data.map(function(r, i){
    var isBest = i === 0;
    var isCheapest = data[data.length-1].id_quote_rate === r.id_quote_rate && !isBest;
    var stars = cmp_renderStars(r.rating_score);
    var initials = r.company_name.substring(0,2).toUpperCase();

    var badgeHtml = '';
    if (isBest) badgeHtml = '<span class="compare-badge" style="background:#dcfce7;color:#16a34a"><i class="bi bi-trophy-fill" style="margin-right:4px"></i>MEJOR OPCIÓN</span>';
    else if (isCheapest) badgeHtml = '<span class="compare-badge" style="background:#fef3c7;color:#d97706"><i class="bi bi-tag-fill" style="margin-right:4px"></i>MÁS ECONÓMICO</span>';

    var coverageHtml = r.coverage_items.map(function(c){
      var cls = c.included ? 'yes' : 'no';
      var icon = c.included ? 'bi-check-lg' : 'bi-x-lg';
      return '<div class="compare-feature"><span class="icon ' + cls + '"><i class="bi ' + icon + '"></i></span><span>' + c.name + '</span></div>';
    }).join('');

    var hospitalsHtml = '';
    if (r.hospitals.length) {
      hospitalsHtml = '<div class="compare-section-title">Red Hospitalaria</div>' + r.hospitals.map(function(h){
        var cls = h.included ? 'yes' : 'partial';
        var icon = h.included ? 'bi-check-lg' : 'bi-dash-lg';
        return '<div class="compare-feature"><span class="icon ' + cls + '"><i class="bi ' + icon + '"></i></span><span>' + h.name + '</span></div>';
      }).join('');
    }

    var benefitsHtml = '';
    if (r.benefits.length) {
      benefitsHtml = '<div class="compare-section-title">Beneficios Extra</div>' + r.benefits.map(function(b){
        return '<div class="compare-feature"><span class="icon yes"><i class="bi bi-star-fill"></i></span><span>' + b.name + (b.detail ? ' <small style="color:var(--arco-siena)">· ' + b.detail + '</small>' : '') + '</span></div>';
      }).join('');
    }

    var detailsHtml = '<div class="compare-section-title">Detalles</div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.deductible_pct > 0 ? 'partial' : 'yes') + '"><i class="bi bi-' + (r.deductible_pct > 0 ? 'dash-lg' : 'check-lg') + '"></i></span><span>Deducible: ' + r.deductible_pct + '% (Q' + cmp_num(r.deductible_amount) + ')</span></div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.waiting_period_days === 0 ? 'yes' : 'partial') + '"><i class="bi bi-' + (r.waiting_period_days === 0 ? 'check-lg' : 'clock-history') + '"></i></span><span>Periodo espera: ' + r.waiting_period_days + ' días</span></div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.has_24h_assistance ? 'yes' : 'no') + '"><i class="bi bi-' + (r.has_24h_assistance ? 'check-lg' : 'x-lg') + '"></i></span><span>Asistencia 24h</span></div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.has_dental_coverage ? 'yes' : 'no') + '"><i class="bi bi-' + (r.has_dental_coverage ? 'check-lg' : 'x-lg') + '"></i></span><span>Cobertura dental</span></div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.has_optical_coverage ? 'yes' : 'no') + '"><i class="bi bi-' + (r.has_optical_coverage ? 'check-lg' : 'x-lg') + '"></i></span><span>Cobertura óptica</span></div>';
    detailsHtml += '<div class="compare-feature"><span class="icon ' + (r.has_life_coverage ? 'yes' : 'no') + '"><i class="bi bi-' + (r.has_life_coverage ? 'check-lg' : 'x-lg') + '"></i></span><span>Seguro de vida</span></div>';
    if (r.max_coverage_limit > 0) {
      detailsHtml += '<div class="compare-feature"><span class="icon yes"><i class="bi bi-shield-check"></i></span><span>Tope: Q' + cmp_num(r.max_coverage_limit) + '</span></div>';
    }

    return '<div class="compare-card ' + (isBest ? 'best' : '') + ' ' + (isCheapest ? 'cheapest' : '') + '">' +
      badgeHtml +
      '<div class="cmp-card-body">' +
        '<div class="company-logo-placeholder">' + initials + '</div>' +
        '<h5 style="text-align:center;margin:0 0 4px;font-weight:700;color:var(--arco-carbon)">' + r.company_name + '</h5>' +
        '<p style="text-align:center;color:var(--arco-siena);font-size:.8rem;margin:0">' + r.rate_name + '</p>' +
        '<div style="text-align:center;margin:8px 0">' + stars + ' <small style="color:var(--arco-siena)">(' + r.rating_score + ')</small></div>' +
        '<div class="compare-price">' +
          '<div class="amount">Q' + cmp_num(r.monthly_premium) + '</div>' +
          '<div class="period">mensual · Q' + cmp_num(r.annual_premium) + ' anual</div>' +
        '</div>' +
        '<div class="compare-section-title">Cobertura</div>' +
        coverageHtml +
        hospitalsHtml +
        benefitsHtml +
        detailsHtml +
        '<div style="margin-top:16px"><button class="btn-primary" style="width:100%;justify-content:center;font-size:.85rem" onclick="cmp_saveComparison(' + i + ')"><i class="bi bi-file-earmark-text"></i> Generar Cotización</button></div>' +
      '</div>' +
    '</div>';
  }).join('');
}

function cmp_renderTable(data){
  var allCoverage = [];
  var allHospitals = [];
  var allBenefits = [];
  data.forEach(function(r){ r.coverage_items.forEach(function(c){ if(allCoverage.indexOf(c.name)===-1) allCoverage.push(c.name); }); });
  data.forEach(function(r){ r.hospitals.forEach(function(h){ if(allHospitals.indexOf(h.name)===-1) allHospitals.push(h.name); }); });
  data.forEach(function(r){ r.benefits.forEach(function(b){ if(allBenefits.indexOf(b.name)===-1) allBenefits.push(b.name); }); });

  var headers = data.map(function(r){
    return '<th style="min-width:140px"><div class="company-logo-placeholder" style="width:40px;height:40px;font-size:.9rem;margin:0 auto 6px;border-radius:10px">' + r.company_name.substring(0,2).toUpperCase() + '</div><strong style="color:var(--arco-carbon)">' + r.company_name + '</strong><br><small style="color:var(--arco-siena)">' + r.rate_name + '</small></th>';
  }).join('');

  var priceRow = function(label, key){
    return '<tr><td style="text-align:left;font-weight:700">' + label + '</td>' + data.map(function(r){
      return '<td style="font-weight:700;color:var(--mod-accent,var(--arco-siena))">Q' + cmp_num(r[key]) + '</td>';
    }).join('') + '</tr>';
  };

  var boolRow = function(label, key){
    return '<tr><td style="text-align:left">' + label + '</td>' + data.map(function(r){
      return '<td>' + (r[key] ? '<i class="bi bi-check-circle-fill" style="color:#16a34a"></i>' : '<i class="bi bi-x-circle-fill" style="color:#dc2626"></i>') + '</td>';
    }).join('') + '</tr>';
  };

  var sectionRow = function(label){
    return '<tr class="section-row"><td colspan="' + (data.length+1) + '">' + label + '</td></tr>';
  };

  var html = '<table class="admin-table"><thead><tr><th style="min-width:160px"></th>' + headers + '</tr></thead><tbody>';
  html += sectionRow('<i class="bi bi-currency-dollar" style="margin-right:6px"></i>Precio');
  html += priceRow('Mensual', 'monthly_premium');
  html += priceRow('Anual', 'annual_premium');
  html += '<tr><td style="text-align:left;font-weight:700">Deducible</td>' + data.map(function(r){
    return '<td>' + r.deductible_pct + '% (Q' + cmp_num(r.deductible_amount) + ')</td>';
  }).join('') + '</tr>';

  if (allCoverage.length) {
    html += sectionRow('<i class="bi bi-shield-check" style="margin-right:6px"></i>Coberturas');
    allCoverage.forEach(function(name){
      html += '<tr><td style="text-align:left">' + name + '</td>' + data.map(function(r){
        var item = null;
        r.coverage_items.forEach(function(c){ if(c.name===name) item=c; });
        return '<td>' + (item && item.included ? '<i class="bi bi-check-circle-fill" style="color:#16a34a"></i>' : '<i class="bi bi-x-circle-fill" style="color:#dc2626"></i>') + '</td>';
      }).join('') + '</tr>';
    });
  }

  if (allHospitals.length) {
    html += sectionRow('<i class="bi bi-hospital" style="margin-right:6px"></i>Red Hospitalaria');
    allHospitals.forEach(function(name){
      html += '<tr><td style="text-align:left">' + name + '</td>' + data.map(function(r){
        var item = null;
        r.hospitals.forEach(function(h){ if(h.name===name) item=h; });
        return '<td>' + (item && item.included ? '<i class="bi bi-check-circle-fill" style="color:#16a34a"></i>' : item ? '<i class="bi bi-dash-circle-fill" style="color:#d97706"></i>' : '<i class="bi bi-x-circle-fill" style="color:#dc2626"></i>') + '</td>';
      }).join('') + '</tr>';
    });
  }

  if (allBenefits.length) {
    html += sectionRow('<i class="bi bi-star-fill" style="margin-right:6px"></i>Beneficios');
    allBenefits.forEach(function(name){
      html += '<tr><td style="text-align:left">' + name + '</td>' + data.map(function(r){
        var item = null;
        r.benefits.forEach(function(b){ if(b.name===name) item=b; });
        return '<td>' + (item ? '<i class="bi bi-check-circle-fill" style="color:#16a34a"></i>' : '<i class="bi bi-x-circle-fill" style="color:#dc2626"></i>') + '</td>';
      }).join('') + '</tr>';
    });
  }

  html += sectionRow('<i class="bi bi-info-circle" style="margin-right:6px"></i>Detalles');
  html += boolRow('Asistencia 24h', 'has_24h_assistance');
  html += boolRow('Dental', 'has_dental_coverage');
  html += boolRow('Óptica', 'has_optical_coverage');
  html += boolRow('Vida', 'has_life_coverage');
  html += '<tr><td style="text-align:left">Periodo espera</td>' + data.map(function(r){
    return '<td>' + r.waiting_period_days + ' días</td>';
  }).join('') + '</tr>';
  html += '<tr><td style="text-align:left">Tope cobertura</td>' + data.map(function(r){
    return '<td>' + (r.max_coverage_limit > 0 ? 'Q'+cmp_num(r.max_coverage_limit) : '—') + '</td>';
  }).join('') + '</tr>';
  html += '<tr><td style="text-align:left;font-weight:700">Score valor</td>' + data.map(function(r){
    return '<td><strong>' + r.value_score + '</strong>/25</td>';
  }).join('') + '</tr>';
  html += '</tbody></table>';

  document.getElementById('sideBySide').innerHTML = html;
}

function cmp_renderStars(score){
  var full = Math.floor(score / 2);
  var half = score % 2 >= 1 ? 1 : 0;
  var html = '';
  for(var i=0;i<full;i++) html += '<i class="bi bi-star-fill"></i>';
  if(half) html += '<i class="bi bi-star-half"></i>';
  for(var j=0;j<5-full-half;j++) html += '<i class="bi bi-star"></i>';
  return html;
}

function cmp_num(v){ return parseFloat(v).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function cmp_saveComparison(idx){
  var r = cmp_compareData[idx];
  document.getElementById('cmpSaveIdx').value = idx;
  document.getElementById('cmpSaveSummary').innerHTML = '<strong>' + r.company_name + '</strong> · ' + r.rate_name + '<br>Prima mensual: <strong>Q' + cmp_num(r.monthly_premium) + '</strong> · Anual: <strong>Q' + cmp_num(r.annual_premium) + '</strong><br>Deducible: ' + r.deductible_pct + '% (Q' + cmp_num(r.deductible_amount) + ')';
  document.getElementById('cmpSaveClientName').value = '';
  document.getElementById('cmpSaveClientEmail').value = '';
  document.getElementById('cmpSaveModal').classList.add('open');
}

function cmp_closeSaveModal(){
  document.getElementById('cmpSaveModal').classList.remove('open');
}

document.getElementById('cmpSaveModal').addEventListener('click', function(e){
  if (e.target === this) cmp_closeSaveModal();
});

document.getElementById('cmpSaveBtn').addEventListener('click', function(){
  var name = document.getElementById('cmpSaveClientName').value.trim();
  if (!name) { showToast('Ingresa el nombre del cliente', 'warning'); return; }

  var idx = parseInt(document.getElementById('cmpSaveIdx').value);
  var r = cmp_compareData[idx];
  this.disabled = true;

  var payload = {
    client_name: name,
    client_email: document.getElementById('cmpSaveClientEmail').value,
    id_insurance_type: document.getElementById('cmpType').value,
    id_insurance_company: r.id_insurance_company,
    estimated_premium: r.annual_premium,
    client_age: document.getElementById('cmpAge').value,
    insured_amount: r.insured_amount,
    deductible: r.deductible_pct,
    coverage_summary: [r],
    valid_until: new Date(Date.now()+30*86400000).toISOString().split('T')[0],
  };

  fetch('?url=quote/ajaxSaveFromCalc', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  })
  .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
  .then(function(json){
    if (json.success) {
      showToast('Cotización guardada — Folio: ' + json.folio, 'success');
      cmp_closeSaveModal();
    } else {
      showToast(json.message || 'Error al guardar la cotización.', 'error');
    }
  })
  .catch(function(err){ showToast('Error de conexión: ' + err.message, 'error'); })
  .finally(function(){ document.getElementById('cmpSaveBtn').disabled = false; });
});
</script>

<script src="<?= URL ?>assets/js/table-export.js"></script>
<script src="<?= URL ?>assets/js/toast.js"></script>
<script src="<?= URL ?>assets/js/form-validation.js"></script>
</body>
</html>