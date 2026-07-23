<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Csrf::metaTag() ?>
    <title>Detalle de Envío | Arco Seguros</title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/client-form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
        .detail-field{background:var(--arco-lino,#F1EBE1);border-radius:8px;padding:12px 16px}
        .detail-field .label{font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#888;margin-bottom:4px}
        .detail-field .value{font-size:.9rem;font-weight:500;color:var(--arco-carbon,#1C1C1E)}
        .section-block{margin-bottom:20px}
        .section-block h3{font-size:.95rem;color:var(--arco-carbon,#1C1C1E);margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid var(--arco-perla,#E2DCDA)}
        .value-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--arco-perla,#E2DCDA)}
        .value-row:last-child{border-bottom:none}
        .value-row .field-label{font-size:.85rem;color:#666}
        .value-row .field-value{font-size:.85rem;font-weight:500;color:var(--arco-carbon,#1C1C1E);text-align:right;max-width:60%}
        .attachment-list{list-style:none;padding:0;margin:0}
        .attachment-list li{padding:6px 0;display:flex;align-items:center;gap:8px;font-size:.85rem}
        .attachment-list li a{color:var(--arco-siena,#8C7B6E);text-decoration:none;font-weight:500}
        .attachment-list li a:hover{text-decoration:underline}
        .history-item{padding:8px 0;border-bottom:1px solid var(--arco-perla,#E2DCDA);font-size:.8rem}
        .history-item:last-child{border-bottom:none}
        .history-item .hist-date{color:#999}
        .sig-img{max-width:200px;max-height:120px;border:1px solid var(--arco-perla,#E2DCDA);border-radius:6px}
        .submission-status-badge{display:inline-block;padding:3px 12px;border-radius:12px;font-size:.8rem;font-weight:600}
        .status-pendiente{background:#fef3c7;color:#92400e}
        .status-enviado{background:#dbeafe;color:#1e40af}
        .status-revisado{background:#d1fae5;color:#065f46}
        .status-rechazado{background:#fee2e2;color:#991b1b}
        .btn-sm{padding:5px 12px;border-radius:6px;font-size:.8rem;font-weight:500;border:1px solid var(--arco-perla,#E2DCDA);background:var(--arco-blanco,#F8F6F3);color:var(--arco-carbon,#1C1C1E);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
        .btn-sm:hover{background:var(--arco-lino,#F1EBE1)}
        .btn-sm.primary{background:var(--arco-siena,#8C7B6E);color:#fff;border-color:var(--arco-siena,#8C7B6E)}
        .btn-sm.primary:hover{background:#7a6a5e}
        @media(max-width:768px){.detail-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="dash-layout">
    <?php include ROOT_PATH . '/app/views/layouts/sidebar_cliente.php'; ?>

    <div class="dash-main">
        <?php
        $pageTitle    = 'Detalle de Envío';
        $pageSubtitle = $submission['submission_number'] ?? '';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">
            <div class="content-card">
                <div class="content-card-header">
                    <div>
                        <div class="content-card-title"><?= htmlspecialchars($submission['form_name'] ?? '') ?></div>
                        <div class="content-card-sub">
                            <?= htmlspecialchars($submission['submission_number'] ?? '') ?>
                            &middot;
                            <span class="submission-status-badge status-<?= strtolower($submission['status']) ?>"><?= htmlspecialchars($submission['status']) ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <a href="?url=cliente/form/download-filled-pdf&id=<?= (int)$submission['id_form_submission'] ?>" class="btn-sm primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                        <a href="?url=cliente/form/submissions" class="btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
                    </div>
                </div>
                <div class="content-card-body">
                    <div class="detail-grid">
                        <div class="detail-field">
                            <div class="label">Número de envío</div>
                            <div class="value"><?= htmlspecialchars($submission['submission_number'] ?? '') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="label">Fecha de envío</div>
                            <div class="value"><?= !empty($submission['submitted_at']) ? date('d/m/Y H:i', strtotime($submission['submitted_at'])) : 'Sin fecha' ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="label">Versión</div>
                            <div class="value">v<?= (int)($submission['version_number'] ?? 1) ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="label">Estado</div>
                            <div class="value"><span class="submission-status-badge status-<?= strtolower($submission['status']) ?>"><?= htmlspecialchars($submission['status']) ?></span></div>
                        </div>
                    </div>

                    <?php if (!empty($submission['values'])): ?>
                    <?php
                    $sections = [];
                    foreach ($submission['values'] as $v) {
                        $secName = $v['section_name'] ?? 'Sin sección';
                        $sections[$secName][] = $v;
                    }
                    foreach ($sections as $secName => $fields):
                    ?>
                    <div class="section-block">
                        <h3><i class="bi bi-folder2-open"></i> <?= htmlspecialchars($secName) ?></h3>
                        <?php foreach ($fields as $f): ?>
                        <div class="value-row">
                            <span class="field-label"><?= htmlspecialchars($f['field_label'] ?? $f['field_name'] ?? '') ?></span>
                            <span class="field-value">
                                <?php
                                $val = $f['field_value'] ?? '';
                                $type = strtolower($f['field_key'] ?? '');
                                if ($type === 'signature' && !empty($val)):
                                ?>
                                    <img src="<?= htmlspecialchars($val) ?>" class="sig-img" alt="Firma">
                                <?php elseif ($type === 'checkbox'):
                                    $vals = array_map('trim', explode(',', $val));
                                    echo htmlspecialchars(implode(', ', $vals));
                                elseif ($type === 'file'):
                                    if (!empty($val)):
                                        $fileParts = json_decode($val, true);
                                        if (is_array($fileParts)):
                                            foreach ($fileParts as $fp):
                                                $fname = $fp['name'] ?? $fp['file_name'] ?? 'archivo';
                                                echo htmlspecialchars($fname) . '<br>';
                                            endforeach;
                                        else:
                                            echo htmlspecialchars($val);
                                        endif;
                                    else:
                                        echo '<em style="color:#999">Sin archivo</em>';
                                    endif;
                                else:
                                    echo nl2br(htmlspecialchars($val)) ?: '<em style="color:#999">Vacío</em>';
                                endif;
                                ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="color:#999;text-align:center;padding:20px">No hay valores registrados para este envío.</p>
                    <?php endif; ?>

                    <?php if (!empty($submission['attachments'])): ?>
                    <div class="section-block">
                        <h3><i class="bi bi-paperclip"></i> Archivos adjuntos</h3>
                        <ul class="attachment-list">
                            <?php foreach ($submission['attachments'] as $att): ?>
                            <li>
                                <i class="bi bi-file-earmark"></i>
                                <a href="<?= htmlspecialchars($att['file_url']) ?>" target="_blank"><?= htmlspecialchars($att['file_name']) ?></a>
                                <span style="color:#999;font-size:.75rem">(<?= number_format(($att['file_size'] ?? 0) / 1024, 1) ?> KB)</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($submission['history'])): ?>
                    <div class="section-block">
                        <h3><i class="bi bi-clock-history"></i> Historial</h3>
                        <?php foreach ($submission['history'] as $h): ?>
                        <div class="history-item">
                            <strong><?= htmlspecialchars(($h['username'] ?? '') . ' ' . ($h['lastname'] ?? '')) ?></strong>
                            cambió de <em><?= htmlspecialchars($h['previous_status'] ?? '') ?></em> a <em><?= htmlspecialchars($h['new_status'] ?? '') ?></em>
                            <?php if (!empty($h['notes'])): ?>
                                — <?= htmlspecialchars($h['notes']) ?>
                            <?php endif; ?>
                            <span class="hist-date"><?= date('d/m/Y H:i', strtotime($h['changed_at'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
