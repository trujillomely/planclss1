<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Csrf::metaTag() ?>
    <title>Mis Envíos | Arco Seguros</title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/client-form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .submissions-table{width:100%;border-collapse:collapse;font-size:.875rem}
        .submissions-table th{text-align:left;padding:10px 12px;background:var(--arco-lino,#F1EBE1);color:var(--arco-carbon,#1C1C1E);font-weight:600;border-bottom:2px solid var(--arco-perla,#E2DCDA);white-space:nowrap}
        .submissions-table td{padding:10px 12px;border-bottom:1px solid var(--arco-perla,#E2DCDA);vertical-align:middle}
        .submissions-table tr:hover{background:var(--arco-lino,#F1EBE1);cursor:pointer}
        .submission-status-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:.75rem;font-weight:600}
        .status-pendiente{background:#fef3c7;color:#92400e}
        .status-enviado{background:#dbeafe;color:#1e40af}
        .status-revisado{background:#d1fae5;color:#065f46}
        .status-rechazado{background:#fee2e2;color:#991b1b}
        .btn-sm{padding:4px 10px;border-radius:6px;font-size:.75rem;font-weight:500;border:1px solid var(--arco-perla,#E2DCDA);background:var(--arco-blanco,#F8F6F3);color:var(--arco-carbon,#1C1C1E);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
        .btn-sm:hover{background:var(--arco-lino,#F1EBE1)}
        .btn-sm.primary{background:var(--arco-siena,#8C7B6E);color:#fff;border-color:var(--arco-siena,#8C7B6E)}
        .btn-sm.primary:hover{background:#7a6a5e}
        .empty-state{text-align:center;padding:40px 20px;color:#888}
        .empty-state i{font-size:2.5rem;display:block;margin-bottom:10px;color:var(--arco-perla,#E2DCDA)}
        .filters-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px}
        .filters-row input,.filters-row select{padding:6px 10px;border:1px solid var(--arco-perla,#E2DCDA);border-radius:6px;font-size:.8rem;background:var(--arco-blanco,#F8F6F3);color:var(--arco-carbon,#1C1C1E)}
    </style>
</head>
<body>
    <div class="dash-layout">
    <?php include ROOT_PATH . '/app/views/layouts/sidebar_cliente.php'; ?>

    <div class="dash-main">
        <?php
        $pageTitle    = 'Mis Envíos';
        $pageSubtitle = 'Historial de formularios enviados';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">
            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title">Envíos realizados</div>
                        <div class="content-card-sub">Consulta el estado y descarga tus formularios enviados</div>
                    </div>
                    <a href="?url=cliente/form" class="btn-sm primary"><i class="bi bi-arrow-left"></i> Volver</a>
                </div>
                <div class="content-card-body">
                    <?php if (!empty($submissions)): ?>
                    <div class="filters-row">
                        <input type="text" id="filterSearch" placeholder="Buscar por número o formulario..." oninput="filterTable()">
                        <select id="filterStatus" onchange="filterTable()">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Enviado">Enviado</option>
                            <option value="Revisado">Revisado</option>
                            <option value="Rechazado">Rechazado</option>
                        </select>
                    </div>
                    <table class="submissions-table" id="submissionsTable">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Formulario</th>
                                <th>Fecha</th>
                                <th>Versión</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                            <tr data-status="<?= htmlspecialchars($sub['status']) ?>" data-search="<?= strtolower(htmlspecialchars($sub['submission_number'] . ' ' . $sub['form_name'])) ?>">
                                <td><strong><?= htmlspecialchars($sub['submission_number']) ?></strong></td>
                                <td><?= htmlspecialchars($sub['form_name']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'] ?: $sub['created_at'])) ?></td>
                                <td>v<?= (int)($sub['version_number'] ?? 1) ?></td>
                                <td><span class="submission-status-badge status-<?= strtolower($sub['status']) ?>"><?= htmlspecialchars($sub['status']) ?></span></td>
                                <td>
                                    <a href="?url=cliente/form/submission-detail&id=<?= (int)$sub['id_form_submission'] ?>" class="btn-sm"><i class="bi bi-eye"></i> Ver</a>
                                    <a href="?url=cliente/form/download-filled-pdf&id=<?= (int)$sub['id_form_submission'] ?>" class="btn-sm primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No tienes envíos registrados aún.</p>
                        <a href="?url=cliente/form" class="btn-sm primary"><i class="bi bi-file-earmark-text"></i> Ver formularios</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
    function filterTable(){
        var search = document.getElementById('filterSearch').value.toLowerCase();
        var status = document.getElementById('filterStatus').value;
        var rows = document.querySelectorAll('#submissionsTable tbody tr');
        rows.forEach(function(row){
            var matchSearch = !search || (row.getAttribute('data-search') || '').indexOf(search) !== -1;
            var matchStatus = !status || row.getAttribute('data-status') === status;
            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
