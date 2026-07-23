<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($formType['name']) ?> | Arco Seguros</title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/client-form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <div class="dash-layout">
    <?php include ROOT_PATH . '/app/views/layouts/sidebar_cliente.php'; ?>

    <div class="dash-main">
        <?php
        $pageTitle    = htmlspecialchars($formType['name']);
        $pageSubtitle = 'Llenado digital del formulario';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">

            <a href="?url=cliente/form" class="btn-back-form" style="margin-bottom: 18px;">
                <i class="bi bi-arrow-left"></i> Volver a formularios
            </a>

            <div class="content-card">
                <div class="content-card-body" style="padding: 28px 32px;">

                    <!-- Success message (hidden by default) -->
                    <div class="form-success-msg" id="formSuccess">
                        <i class="bi bi-check-circle-fill"></i>
                        <h3>Formulario enviado exitosamente</h3>
                        <p>Tu envío ha sido registrado correctamente.</p>
                        <div class="success-number" id="successNumber"></div>
                        <div style="margin-top: 20px;">
                            <a href="?url=cliente/form" class="btn-back-form" style="display: inline-flex;">
                                <i class="bi bi-arrow-left"></i> Volver a formularios
                            </a>
                        </div>
                    </div>

                    <!-- Form render -->
                    <div class="form-render" id="formRender">
                        <div class="form-render-header">
                            <h2><?= htmlspecialchars($formType['name']) ?></h2>
                            <?php if (!empty($formType['description'])): ?>
                                <p><?= htmlspecialchars($formType['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="id_form_version" value="<?= (int)$formType['id_current_version'] ?>">

                        <div id="formFields"></div>

                        <div class="form-submit-area" id="formSubmitArea">
                            <button type="button" class="btn-submit-form" id="btnSubmit">
                                <span class="spinner"></span>
                                <span class="btn-text"><i class="bi bi-send"></i> Enviar formulario</span>
                            </button>
                            <?php if (!empty($formType['allow_download'])): ?>
                            <a href="?url=cliente/form/download-pdf&id_form_type=<?= (int)$formType['id_form_type'] ?>" class="btn-back-form" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Previous submissions -->
                    <?php if (!empty($prevSubmissions)): ?>
                    <div class="form-history-section" style="margin-top: 28px;">
                        <h3><i class="bi bi-clock-history"></i> Mis envíos anteriores de este formulario</h3>
                        <div class="form-history-list">
                            <?php foreach (array_slice($prevSubmissions, 0, 5) as $sub): ?>
                            <div class="form-history-item">
                                <div class="hist-info">
                                    <span class="hist-name"><?= htmlspecialchars($formType['name']) ?></span>
                                    <span class="hist-meta">
                                        <?= date('d/m/Y H:i', strtotime($sub['submitted_at'] ?: $sub['created_at'])) ?>
                                        · <span class="submission-status-badge status-<?= strtolower($sub['status']) ?>"><?= htmlspecialchars($sub['status']) ?></span>
                                    </span>
                                </div>
                                <span class="hist-number"><?= htmlspecialchars($sub['submission_number']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
    </div>

    <script src="<?= URL ?>assets/js/utils.js"></script>
    <script src="<?= URL ?>assets/js/toast.js"></script>
    <script src="<?= URL ?>assets/js/client-form.js"></script>
    <script>
    (function() {
        const formFields = document.getElementById('formFields');
        const btnSubmit  = document.getElementById('btnSubmit');
        const formRender = document.getElementById('formRender');
        const formSuccess = document.getElementById('formSuccess');
        const submitArea = document.getElementById('formSubmitArea');

        // Render fields from server-side structure
        const structure = <?= json_encode($structure, JSON_UNESCAPED_UNICODE) ?>;
        renderFormFields(formFields, structure);

        const dependencies = <?= json_encode($dependencies ?? []) ?>;
        if (dependencies.length) {
            dependencies.forEach(function(dep) {
                const source = document.querySelector('[data-field-id="' + dep.id_source_field + '"]');
                const target = document.querySelector('[data-field-id="' + dep.id_dependent_field + '"]');
                if (!source || !target) return;
                function evaluate() {
                    const val = source.querySelector('input, select, textarea') ? source.querySelector('input, select, textarea').value : '';
                    const match = dep.condition_operator === 'equals' ? val === dep.condition_value :
                                 dep.condition_operator === 'not_equals' ? val !== dep.condition_value :
                                 dep.condition_operator === 'contains' ? val.indexOf(dep.condition_value) !== -1 :
                                 dep.condition_operator === 'not_empty' ? val.trim() !== '' : false;
                    target.style.display = match ? '' : 'none';
                }
                source.addEventListener('input', evaluate);
                source.addEventListener('change', evaluate);
                evaluate();
            });
        }

        // Submit handler
        btnSubmit.addEventListener('click', function() {
            submitFormAjax(formFields, btnSubmit, function(res) {
                formRender.style.display = 'none';
                formSuccess.classList.add('visible');
                document.getElementById('successNumber').textContent = res.submission_number;
            });
        });
    })();
    </script>
</body>
</html>
