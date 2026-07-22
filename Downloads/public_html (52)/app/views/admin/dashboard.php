<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | Arco Seguros</title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">

    <link rel="stylesheet" href="<?= URL ?>assets/css/dashboard-admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dash-layout">
    <?php include ROOT_PATH . '/app/views/layouts/sidebar_admin.php'; ?>
    <!-- ── Main ── -->
    <div class="dash-main">

        <?php
        $pageTitle    = 'Dashboard';
        $pageModule   = 'dashboard';
        $pageSubtitle = 'Resumen general del sistema';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>
        <!-- Content -->
        <div class="dash-content">

            <!-- KPIs -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon lino"><i class="bi bi-file-earmark-check"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Pólizas Activas</div>
                        <div class="kpi-value"><?= number_format($activePolicies ?? 0) ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon perla"><i class="bi bi-clipboard-x"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Reclamos Pendientes</div>
                        <div class="kpi-value"><?= number_format($pendingClaims ?? 0) ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon dorado"><i class="bi bi-currency-dollar"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Primas Este Mes</div>
                        <div class="kpi-value" style="font-size:1.25rem;">Q <?= number_format($totalMonthlyPremium ?? 0, 0) ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon pizarra"><i class="bi bi-people"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-label">Clientes Activos</div>
                        <div class="kpi-value"><?= number_format($activeClients ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success']) && strpos($_SESSION['success'] ?? '', 'DB Fix') !== false): ?>
            <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#065f46;">
                <i class="bi bi-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error']) && strpos($_SESSION['error'] ?? '', 'DB Fix') !== false): ?>
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;">
                <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <!-- ── DB Fix (one-time) ── -->
            <div id="dbFixBanner" style="background:linear-gradient(135deg,#8C7B6E,#C7B6A8);border-radius:10px;padding:16px 20px;margin-bottom:16px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div>
                    <strong style="font-size:1rem;"><i class="bi bi-database-gear"></i> Base de Datos — Fix de Firma y Pagos</strong>
                    <p style="margin:4px 0 0;font-size:.82rem;opacity:.9;">Ejecuta una sola vez para crear tablas faltantes (digital_signature, payment_method, payment_schedule).</p>
                </div>
                <form method="POST" action="?url=admin/run-db-fix" onsubmit="return confirm('Ejecutar migración de base de datos? Esto crea tablas faltantes.');" style="margin:0;">
                    <?= Csrf::field() ?>
                    <button type="submit" style="background:#fff;color:#8C7B6E;border:none;border-radius:6px;padding:8px 18px;font-weight:700;cursor:pointer;font-size:.85rem;">Ejecutar Fix</button>
                </form>
            </div>
            <script>
            (function(){
                var banner = document.getElementById('dbFixBanner');
                if(banner && localStorage.getItem('arco_dbfix_done')==='1'){ banner.style.display='none'; }
                var form = banner ? banner.querySelector('form') : null;
                if(form){ form.addEventListener('submit', function(){ localStorage.setItem('arco_dbfix_done','1'); }); }
            })();
            </script>

            <!-- ── Filtros de fecha ─────────────────────────────────────── -->
            <div class="dashboard-filters">
                <div class="filter-pills">
                    <button class="filter-pill active" data-range="month" onclick="setDateRange('month')">Este mes</button>
                    <button class="filter-pill" data-range="quarter" onclick="setDateRange('quarter')">Este trimestre</button>
                    <button class="filter-pill" data-range="year" onclick="setDateRange('year')">Este año</button>
                    <button class="filter-pill" data-range="custom" onclick="toggleCustomDate()">Personalizado</button>
                </div>
                <div class="custom-date-range" id="customDateRange" style="display:none;">
                    <input type="date" id="dateFrom" class="date-input">
                    <span style="color:var(--arco-siena);">a</span>
                    <input type="date" id="dateTo" class="date-input">
                    <button class="btn-primary" style="padding:8px 14px;font-size:.78rem;" onclick="applyCustomDate()">Aplicar</button>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-row">
                <!-- Pólizas por estado -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-card-title">Pólizas por Estado</span>
                    </div>
                    <div class="donut-wrap">
                        <canvas id="chartPolizas" height="180"></canvas>
                        <?php
                        $totalPol = array_sum($policiesByStatus ?? []);
                        ?>
                        <div class="donut-center">
                            <span class="dc-val"><?= number_format($totalPol) ?></span>
                            <span class="dc-lbl">Total</span>
                        </div>
                    </div>
                    <?php
                    $polTotal = max(array_sum($policiesByStatus ?? []), 1);
                    ?>
                    <ul class="legend-list">
                        <li><span class="legend-left"><span class="legend-dot" style="background:#8C7B6E"></span>Activas</span><span class="legend-pct"><?= round(($policiesByStatus['Activo'] ?? 0) / $polTotal * 100, 1) ?>%</span></li>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#f2bf4a"></span>Expiradas</span><span class="legend-pct"><?= round(($policiesByStatus['Expirado'] ?? 0) / $polTotal * 100, 1) ?>%</span></li>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#ef4444"></span>Canceladas</span><span class="legend-pct"><?= round(($policiesByStatus['Cancelado'] ?? 0) / $polTotal * 100, 1) ?>%</span></li>
                    </ul>
                </div>

                <!-- Primas por mes -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-card-title">Primas por Mes (GTQ)</span>
                        <span class="chart-year-badge">Este año ▾</span>
                    </div>
                    <canvas id="chartPrimas" height="190"></canvas>
                </div>

                <!-- Reclamos por estado -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-card-title">Reclamos por Estado</span>
                    </div>
                    <div class="donut-wrap">
                        <canvas id="chartReclamos" height="180"></canvas>
                        <?php
                        $totalClaims = array_sum($claimsByStatus ?? []);
                        ?>
                        <div class="donut-center">
                            <span class="dc-val"><?= number_format($totalClaims) ?></span>
                            <span class="dc-lbl">Total</span>
                        </div>
                    </div>
                    <?php
                    $clTotal = max(array_sum($claimsByStatus ?? []), 1);
                    ?>
                    <ul class="legend-list">
                        <?php
                        $clPendiente = ($claimsByStatus['Pendiente'] ?? 0) + ($claimsByStatus['Reportado'] ?? 0) + ($claimsByStatus['En evaluación'] ?? 0);
                        $clAprobado  = ($claimsByStatus['Aprovado'] ?? 0) + ($claimsByStatus['Aprobado'] ?? 0) + ($claimsByStatus['En proceso'] ?? 0);
                        $clActivo    = ($claimsByStatus['Activo'] ?? 0);
                        $clInactivo  = ($claimsByStatus['Inactivo'] ?? 0) + ($claimsByStatus['Cerrado'] ?? 0) + ($claimsByStatus['Rechazado'] ?? 0);
                        ?>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#f2bf4a"></span>Pendientes</span><span class="legend-pct"><?= round($clPendiente / $clTotal * 100, 1) ?>%</span></li>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#4A4A52"></span>Aprobados</span><span class="legend-pct"><?= round($clAprobado / $clTotal * 100, 1) ?>%</span></li>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#8C7B6E"></span>Activos</span><span class="legend-pct"><?= round($clActivo / $clTotal * 100, 1) ?>%</span></li>
                        <li><span class="legend-left"><span class="legend-dot" style="background:#ef4444"></span>Cerrados</span><span class="legend-pct"><?= round($clInactivo / $clTotal * 100, 1) ?>%</span></li>
                    </ul>
                </div>
            </div>

            <!-- Lower: Accesos rápidos + Vencimientos + Actividad -->
            <div class="lower-grid">
                <!-- Accesos rápidos (span 1, but styled as full on small) -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Accesos Rápidos</span>
                    </div>
                    <div class="quick-grid">
                        <a href="?url=admin/policy" class="quick-item"><i class="bi bi-file-earmark-plus"></i>Nueva Póliza</a>
                        <a href="?url=admin/claims" class="quick-item"><i class="bi bi-clipboard-plus"></i>Nuevo Reclamo</a>
                        <a href="?url=admin/transactions" class="quick-item"><i class="bi bi-cash-coin"></i>Cobros</a>
                        <a href="?url=admin/users" class="quick-item"><i class="bi bi-person-plus"></i>Clientes</a>
                        <a href="?url=admin/insurance-companies" class="quick-item"><i class="bi bi-building"></i>Aseguradoras</a>
                        <a href="?url=admin/services" class="quick-item"><i class="bi bi-headset"></i>Servicios</a>
                        <a href="?url=admin/providers" class="quick-item"><i class="bi bi-briefcase"></i>Proveedores</a>
                        <a href="?url=admin/form-builder" class="quick-item"><i class="bi bi-file-earmark-check"></i>Formularios</a>
                        <a href="?url=admin/crm-clients" class="quick-item"><i class="bi bi-people"></i>CRM</a>
                        <a href="?url=admin/reportes" class="quick-item"><i class="bi bi-bar-chart-line"></i>Reportes</a>
                        <a href="?url=admin/payment-calendar" class="quick-item"><i class="bi bi-calendar3"></i>Calendario</a>
                    </div>
                </div>

                <!-- Próximos vencimientos -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Próximos Vencimientos</span>
                    </div>
                    <div class="venc-list">
                        <?php
                        $expirations = $upcomingExpirations ?? [];
                        if (empty($expirations)):
                        ?>
                            <div class="venc-item">
                                <div class="venc-info" style="width:100%">
                                    <div class="venc-name" style="color:var(--arco-siena);">No hay vencimientos próximos</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($expirations, 0, 4) as $exp):
                                $daysLeft = (int) ((strtotime($exp['date_expiration'] ?? '') - time()) / 86400);
                                $urgency = $daysLeft <= 3 ? 'urgent' : ($daysLeft <= 7 ? 'soon' : 'ok');
                            ?>
                            <div class="venc-item">
                                <div class="venc-avatar"><i class="bi bi-person"></i></div>
                                <div class="venc-info">
                                    <div class="venc-pol"><?= htmlspecialchars($exp['policy_number'] ?? '') ?></div>
                                    <div class="venc-name"><?= htmlspecialchars(($exp['client_name'] ?? '') . ' ' . ($exp['client_lastname'] ?? '')) ?></div>
                                </div>
                                <div class="venc-right">
                                    <span class="venc-tipo"><?= htmlspecialchars($exp['insurance_type_name'] ?? '') ?></span>
                                    <span class="venc-badge <?= $urgency ?>"><?= $daysLeft ?> días</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="?url=admin/policy" class="venc-link">Ver todas las pólizas próximas a vencer →</a>
                </div>

                <!-- Actividad reciente -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Actividad Reciente</span>
                    </div>
                    <div class="activity-list">
                        <?php
                        $recentItems = $recentPolicies ?? [];
                        if (empty($recentItems)):
                        ?>
                            <div class="activity-item">
                                <div class="activity-body" style="width:100%">
                                    <div class="activity-title" style="color:var(--arco-siena);">No hay actividad reciente</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($recentItems, 0, 4) as $rp): ?>
                            <div class="activity-item">
                                <div class="activity-icon green"><i class="bi bi-file-earmark-plus"></i></div>
                                <div class="activity-body">
                                    <div class="activity-title">Póliza creada</div>
                                    <div class="activity-desc"><?= htmlspecialchars($rp['policy_number'] ?? '') ?> — <?= htmlspecialchars(($rp['client_name'] ?? '') . ' ' . ($rp['client_lastname'] ?? '')) ?></div>
                                    <div class="activity-time"><?= htmlspecialchars($rp['date_start'] ?? '') ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cumpleaños próximos -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Cumpleaños Próximos</span>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($upcomingBirthdays)): ?>
                            <div class="activity-item">
                                <div class="activity-body" style="width:100%">
                                    <div class="activity-title" style="color:#16a34a;">No hay cumpleaños en los próximos 7 días</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($upcomingBirthdays, 0, 5) as $bday):
                                $bdate = new DateTime($bday['birth_date']);
                                $todayDt = new DateTime();
                                $thisYear = (int)$todayDt->format('Y');
                                $bdayThisYear = $bdate->setDate($thisYear);
                                if ($bdayThisYear < $todayDt) $bdayThisYear->modify('+1 year');
                                $daysUntil = (int)$todayDt->diff($bdayThisYear)->format('%a');
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#fef3c7; color:#b45309;"><i class="bi bi-gift"></i></div>
                                <div class="activity-body">
                                    <div class="activity-title"><?= htmlspecialchars(($bday['username'] ?? '') . ' ' . ($bday['lastname'] ?? '')) ?></div>
                                    <div class="activity-desc">Cumple <?= $bdayThisYear->format('d') ?> de <?= ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][(int)$bdayThisYear->format('n')] ?></div>
                                    <div class="activity-time" style="color:#b45309;font-weight:600;">En <?= $daysUntil ?> día<?= $daysUntil !== 1 ? 's' : '' ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Renovaciones -->
            <?php if (($renewalStats['pendiente'] ?? 0) > 0 || ($renewalStats['expiring_30d'] ?? 0) > 0): ?>
            <div>
                <p class="section-heading">Renovaciones</p>
            </div>
            <div class="lower-grid renewal-lower-grid">
                <div class="panel-card" style="border-left:3px solid #dc2626;">
                    <div class="panel-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="panel-card-title">Pendientes</span>
                        <a href="?url=admin/renewals&status=pendiente" style="font-size:.78rem;color:var(--arco-siena);text-decoration:none;">Gestionar →</a>
                    </div>
                    <div style="text-align:center;padding:8px 0;">
                        <div style="font-size:2rem;font-weight:700;color:#dc2626;"><?= $renewalStats['pendiente'] ?? 0 ?></div>
                        <div style="font-size:.78rem;color:#6b7280;">renovaciones esperando acción</div>
                    </div>
                </div>
                <div class="panel-card" style="border-left:3px solid #f59e0b;">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Vencen en 30 días</span>
                    </div>
                    <div style="text-align:center;padding:8px 0;">
                        <div style="font-size:2rem;font-weight:700;color:#f59e0b;"><?= $renewalStats['expiring_30d'] ?? 0 ?></div>
                        <div style="font-size:.78rem;color:#6b7280;">pólizas próximas a vencer</div>
                    </div>
                </div>
                <div class="panel-card" style="border-left:3px solid var(--arco-siena);">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Vencen en 60 días</span>
                    </div>
                    <div style="text-align:center;padding:8px 0;">
                        <div style="font-size:2rem;font-weight:700;color:var(--arco-siena);"><?= $renewalStats['expiring_60d'] ?? 0 ?></div>
                        <div style="font-size:.78rem;color:#6b7280;">pólizas a renovar</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CRM Resumen -->
            <div style="margin-top:4px;">
                <p class="section-heading">CRM — Resumen</p>
            </div>
            <div class="lower-grid">
                <div class="panel-card">
                    <div class="panel-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="panel-card-title">Pipeline de Clientes</span>
                        <a href="?url=admin/crm-clients" style="font-size:.78rem;color:var(--arco-siena);text-decoration:none;">Ver todos →</a>
                    </div>
                    <div class="crm-pipeline-grid">
                        <div style="text-align:center;">
                            <div style="font-size:1.3rem;font-weight:700;color:#f2bf4a;"><?= $crmStats['leads'] ?? 0 ?></div>
                            <div style="font-size:.72rem;color:#6b7280;">Leads</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.3rem;font-weight:700;color:#4A4A52;"><?= $crmStats['prospectos'] ?? 0 ?></div>
                            <div style="font-size:.72rem;color:#6b7280;">Prospectos</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.3rem;font-weight:700;color:#16a34a;"><?= $crmStats['activos'] ?? 0 ?></div>
                            <div style="font-size:.72rem;color:#6b7280;">Activos</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.3rem;font-weight:700;color:#9ca3af;"><?= $crmStats['inactivos'] ?? 0 ?></div>
                            <div style="font-size:.72rem;color:#6b7280;">Inactivos</div>
                        </div>
                    </div>
                    <div style="margin-top:8px;padding:8px 16px 4px;border-top:1px solid var(--arco-perla);">
                        <div style="font-size:.78rem;color:#6b7280;margin-bottom:4px;">Actividades este mes: <strong style="color:var(--arco-siena);"><?= $crmStats['activities_month'] ?? 0 ?></strong></div>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Seguimientos Vencidos</span>
                    </div>
                    <div class="venc-list">
                        <?php if (empty($overdueFollowUps)): ?>
                            <div class="venc-item">
                                <div class="venc-info" style="width:100%">
                                    <div class="venc-name" style="color:#16a34a;">No hay seguimientos vencidos</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($overdueFollowUps, 0, 4) as $ofu):
                                $overdueDays = (int) ((time() - strtotime($ofu['next_follow_up'])) / 86400);
                            ?>
                            <div class="venc-item">
                                <div class="venc-avatar"><i class="bi bi-person"></i></div>
                                <div class="venc-info">
                                    <div class="venc-name"><?= htmlspecialchars(($ofu['username'] ?? '') . ' ' . ($ofu['lastname'] ?? '')) ?></div>
                                    <div class="venc-pol" style="font-size:.75rem;color:#6b7280;"><?= htmlspecialchars($ofu['client_stage'] ?? '') ?></div>
                                </div>
                                <div class="venc-right">
                                    <span class="venc-badge urgent">vencido <?= $overdueDays ?>d</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Recordatorios Pendientes</span>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($pendingReminders)): ?>
                            <div class="activity-item">
                                <div class="activity-body" style="width:100%">
                                    <div class="activity-title" style="color:#16a34a;">No hay recordatorios pendientes</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($pendingReminders, 0, 4) as $rem):
                                $isOverdue = strtotime($rem['reminder_date']) < time();
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= $isOverdue ? '' : 'green' ?>"><i class="bi bi-bell<?= $isOverdue ? '-fill' : '' ?>"></i></div>
                                <div class="activity-body">
                                    <div class="activity-title"><?= htmlspecialchars($rem['title']) ?></div>
                                    <div class="activity-desc"><?= htmlspecialchars($rem['client_name'] ?? '') ?> · <?= ucfirst($rem['priority'] ?? '') ?></div>
                                    <div class="activity-time" style="<?= $isOverdue ? 'color:#dc2626;font-weight:700;' : '' ?>"><?= date('d/m/Y H:i', strtotime($rem['reminder_date'])) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen financiero -->
            <div>
                <p class="section-heading">Resumen Financiero del Mes</p>
            </div>
            <div class="fin-grid">
                <div class="fin-card">
                    <div class="fin-label">Primas Totales</div>
                    <div class="fin-value">Q <?= number_format($totalMonthlyPremium ?? 0, 0) ?></div>
                </div>
                <div class="fin-card">
                    <div class="fin-label">Pagos Recibidos</div>
                    <div class="fin-value">Q <?= number_format($totalPaid ?? 0, 0) ?></div>
                </div>
                <div class="fin-card">
                    <div class="fin-label">Reclamos Pagados</div>
                    <div class="fin-value">Q <?= number_format($totalClaimsPaid ?? 0, 0) ?></div>
                </div>
                <div class="fin-card" style="border-left: 3px solid var(--arco-siena);">
                    <div class="fin-label">Saldo Neto</div>
                    <div class="fin-value" style="color:var(--arco-siena);">Q <?= number_format($netBalance ?? 0, 0) ?></div>
                </div>
                <div class="fin-card" style="border-left: 3px solid #2F6F72;">
                    <div class="fin-label">Comisiones del Mes</div>
                    <div class="fin-value" style="color:#2F6F72;">Q <?= number_format($commissionSummary['total_commissions'] ?? 0, 0) ?></div>
                    <div style="font-size:.72rem;color:var(--arco-siena);margin-top:4px;">
                        <?= $commissionSummary['total_records'] ?? 0 ?> registros · <?= number_format($commissionSummary['paid'] ?? 0, 0) ?> pagados
                    </div>
                </div>
            </div>

        </div><!-- /dash-content -->
    </div><!-- /dash-main -->
</div><!-- /dash-layout -->

<style>
.crm-pipeline-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 8px 16px;
}
.renewal-lower-grid {
    grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 640px) {
    .crm-pipeline-grid { grid-template-columns: repeat(2, 1fr); }
    .renewal-lower-grid { grid-template-columns: 1fr; }
}
</style>

<script>

// ── Charts (Chart.js) ──
const siena   = '#8C7B6E';
const dorado  = '#f2bf4a';
const pizarra = '#4A4A52';
const perla   = '#E2DCDA';
const red     = '#ef4444';
const carbon  = '#1C1C1E';

// Donut – Pólizas
new Chart(document.getElementById('chartPolizas'), {
    type: 'doughnut',
    data: {
        labels: ['Activas', 'Expiradas', 'Canceladas'],
        datasets: [{ data: [<?= ($policiesByStatus['Activo'] ?? 0) ?>, <?= ($policiesByStatus['Expirado'] ?? 0) ?>, <?= ($policiesByStatus['Cancelado'] ?? 0) ?>], backgroundColor: [siena, dorado, red], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { cutout: '72%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } } }
});

// Line – Primas
new Chart(document.getElementById('chartPrimas'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($premiumsByMonth ?? [], 'label')) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($v) => round($v, 2), array_column($premiumsByMonth ?? [], 'total'))) ?>,
            borderColor: siena,
            backgroundColor: 'rgba(140,123,110,.08)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: siena,
            fill: true,
            tension: .4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { color: pizarra, callback: v => 'Q ' + (v/1000).toFixed(0) + 'K', font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { color: pizarra, font: { size: 11 } } }
        }
    }
});

// Donut – Reclamos
new Chart(document.getElementById('chartReclamos'), {
    type: 'doughnut',
    data: {
        labels: ['Pendientes', 'Aprobados', 'Activos', 'Cerrados'],
        datasets: [{ data: [<?= $clPendiente ?>, <?= $clAprobado ?>, <?= $clActivo ?>, <?= $clInactivo ?>], backgroundColor: [dorado, pizarra, siena, red], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { cutout: '72%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } } }
});

// ── Date range filter ──
function setDateRange(range){
    document.querySelectorAll('.filter-pill').forEach(function(p){
        p.classList.toggle('active', p.dataset.range === range);
    });
    document.getElementById('customDateRange').style.display = range === 'custom' ? 'flex' : 'none';

    var params = new URLSearchParams(window.location.search);
    params.set('range', range);

    var today = new Date();
    if(range === 'month'){
        params.set('date_from', today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-01');
        params.set('date_to', today.toISOString().split('T')[0]);
    } else if(range === 'quarter'){
        var qStart = Math.floor(today.getMonth()/3)*3+1;
        params.set('date_from', today.getFullYear() + '-' + String(qStart).padStart(2,'0') + '-01');
        params.set('date_to', today.toISOString().split('T')[0]);
    } else if(range === 'year'){
        params.set('date_from', today.getFullYear() + '-01-01');
        params.set('date_to', today.toISOString().split('T')[0]);
    } else {
        return; // custom: wait for apply button
    }
    window.location.search = params.toString();
}

function toggleCustomDate(){
    var el = document.getElementById('customDateRange');
    el.style.display = el.style.display === 'none' ? 'flex' : 'none';
}

function applyCustomDate(){
    var from = document.getElementById('dateFrom').value;
    var to = document.getElementById('dateTo').value;
    if(!from || !to){ showToast('Selecciona ambas fechas.', 'warning'); return; }
    var params = new URLSearchParams(window.location.search);
    params.set('range', 'custom');
    params.set('date_from', from);
    params.set('date_to', to);
    window.location.search = params.toString();
}
</script>
</body>
</html>
