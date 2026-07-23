<?php
$formTypes  = $formTypes ?? [];
$fieldTypes = $fieldTypes ?? [];
$formType   = $formType ?? null;
$sections   = $sections ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>(function(){try{var t=localStorage.getItem('arco_theme')||'system';var dark=(t==='dark')||(t==='system'&&window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(dark){document.documentElement.setAttribute('data-theme','dark');}if(localStorage.getItem('arco_animations')==='off'){document.documentElement.classList.add('arco-no-animations');}}catch(e){}})();</script>
    
    <?= Csrf::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constructor de Formularios | Panel <?= $panelLabel ?? 'Administrador' ?></title>
    <link rel="stylesheet" href="<?= URL ?>assets/css/panel.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/toast.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/admin-pages.css">
    <link rel="stylesheet" href="<?= URL ?>assets/css/form-builder.css">

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
        $pageTitle    = 'Constructor de Formularios';
        $pageModule   = 'formularios';
        $pageSubtitle = $formType ? 'Diseña las secciones y campos de "' . $formType['name'] . '"' : 'Elige un formulario para diseñar sus campos';
        include ROOT_PATH . '/app/views/layouts/topbar_admin.php';
        ?>

        <div class="dash-content">

            <?php if (!$formType): ?>

                <!-- Selector de formulario -->
                <div class="content-card">
                    <div class="content-card-header">
                        <div>
                            <div class="content-card-title">¿Qué formulario deseas construir?</div>
                            <div class="content-card-sub">Selecciona un formulario existente o crea uno nuevo</div>
                        </div>
                        <?php if (Auth::can('formularios', 'crear')): ?>
                        <button class="btn-agregar" onclick="document.getElementById('quickCreateModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Nuevo Formulario</button>
                        <?php endif; ?>
                    </div>
                    <div class="content-card-body">
                        <?php if (!empty($formTypes)): ?>
                        <div class="fb-picker-grid">
                            <?php foreach ($formTypes as $ft): ?>
                            <a class="fb-picker-card" href="?url=admin/form-builder&id=<?= (int) $ft['id_form_type'] ?>">
                                <h4><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($ft['name']) ?></h4>
                                <p><?= (int) ($ft['total_fields'] ?? 0) ?> campo(s) · versión <?= (int) ($ft['current_version_number'] ?? 1) ?></p>
                                <span class="badge <?= ($ft['status'] ?? 1) == 1 ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= ($ft['status'] ?? 1) == 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-file-earmark-text"></i>
                            <p>Todavía no hay formularios creados. Usa "Nuevo Formulario" para crear el primero.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modal creación rápida -->
                <?php if (Auth::can('formularios', 'crear')): ?>
                <div class="modal-overlay" id="quickCreateModal">
                    <div class="modal-box" style="max-width:480px;">
                        <div class="modal-header" style="border-top:3px solid var(--mod-accent, var(--arco-siena));">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:38px;height:38px;border-radius:10px;background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena));display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                                    <i class="bi bi-plus-circle"></i>
                                </div>
                                <h3 style="margin:0;font-size:1.05rem;">Nuevo Formulario</h3>
                            </div>
                            <button class="modal-close" onclick="document.getElementById('quickCreateModal').classList.remove('open')"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <form id="quickCreateForm" onsubmit="quickCreateSubmit(event)">
                            <div class="modal-body" style="padding:20px 24px;">
                                <div class="form-group" style="margin-bottom:16px;">
                                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Nombre del formulario</label>
                                    <input type="text" id="qc_name" name="name" placeholder="Ej. Solicitud de Reclamo" required oninput="document.getElementById('qc_key').value = qcSlug(this.value)" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                                </div>
                                <div class="form-group" style="margin-bottom:16px;">
                                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Clave interna</label>
                                    <input type="text" id="qc_key" name="form_key" placeholder="Ej. solicitud_reclamo" required style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Categoría (opcional)</label>
                                    <input type="text" name="category" placeholder="Ej. Reclamos" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                                </div>
                            </div>
                            <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--arco-perla);">
                                <button type="button" class="btn-secondary" onclick="document.getElementById('quickCreateModal').classList.remove('open')">Cancelar</button>
                                <button type="submit" class="btn-primary" style="padding:10px 24px;font-weight:700;">Crear y Diseñar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>
                function qcSlug(t) {
                    return (t || '').toString().trim().toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9\s_-]/g, '').replace(/\s+/g, '_');
                }
                function quickCreateSubmit(e) {
                    e.preventDefault();
                    const btn = e.target.querySelector('.btn-primary');
                    btn.disabled = true;
                    btn.textContent = 'Creando...';
                    fetch('?url=admin/form-types/store', { method: 'POST', body: new FormData(e.target) })
                        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
                        .then(data => {
                            if (data.success && data.data && data.data.id_form_type) {
                                window.location.href = '?url=admin/form-builder&id=' + data.data.id_form_type;
                            } else {
                                showToast(data.message || 'Ocurrió un error.', 'error');
                                btn.disabled = false;
                                btn.textContent = 'Crear y Diseñar';
                            }
                        })
                        .catch(() => {
                            showToast('Error de conexión. Intente nuevamente.', 'error');
                            btn.disabled = false;
                            btn.textContent = 'Crear y Diseñar';
                        });
                }
                </script>
                <?php endif; ?>

            <?php else: ?>

                <!-- Encabezado del formulario seleccionado -->
                <div class="fb-header">
                    <div class="fb-header-info">
                        <h3><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($formType['name']) ?></h3>
                        <div class="fb-meta">
                            <span><code><?= htmlspecialchars($formType['form_key']) ?></code></span>
                            <span><i class="bi bi-folder" style="margin-right:3px;"></i><?= htmlspecialchars($formType['category'] ?: 'General') ?></span>
                            <span id="fbFieldCount">0 campos</span>
                        </div>
                    </div>
                    <div class="fb-header-actions">
                        <a href="?url=admin/form-types" class="btn-secondary" style="display:flex;align-items:center;gap:6px;"><i class="bi bi-arrow-left"></i> Todos los formularios</a>
                        <?php if (Auth::can('formularios', 'editar')): ?>
                        <button class="btn-primary" id="fbSaveBtn" onclick="fbSave()" style="display:flex;align-items:center;gap:6px;padding:10px 20px;font-weight:700;"><i class="bi bi-save"></i> Guardar Formulario</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="fb-layout">

                    <!-- Paleta de tipos de dato -->
                    <?php if (Auth::can('formularios', 'editar')): ?>
                    <aside class="fb-palette">
                        <h4>Tipo de dato a capturar</h4>
                        <?php foreach ($fieldTypes as $type): ?>
                        <button type="button" class="fb-field-type-btn" onclick="fbAddField(<?= (int) $type['id_form_field_type'] ?>)">
                            <i class="bi <?= htmlspecialchars($type['icon'] ?: 'bi-input-cursor-text') ?>"></i>
                            <?= htmlspecialchars($type['name']) ?>
                        </button>
                        <?php endforeach; ?>
                        <?php if (empty($fieldTypes)): ?>
                        <p class="fb-palette-hint">No hay tipos de campo configurados. Ejecuta la migración <code>FormBuilderMigration.sql</code>.</p>
                        <?php endif; ?>
                        <p class="fb-palette-hint">Elige el tipo de dato y se agregará a la sección seleccionada. Luego edítalo para definir cómo se ingresa (obligatorio, opciones, formato, etc.).</p>
                    </aside>
                    <?php endif; ?>

                    <!-- Lienzo de secciones -->
                    <div class="fb-canvas">
                        <div id="fbSectionsContainer"></div>
                        <?php if (Auth::can('formularios', 'editar')): ?>
                        <button type="button" class="fb-add-section-btn" onclick="fbAddSection()">
                            <i class="bi bi-plus-lg"></i> Agregar sección
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Vista previa en vivo (lado cliente) -->
                    <div class="fb-preview">
                        <div class="fb-preview-topbar">
                            <span class="fb-preview-dots"><span></span><span></span><span></span></span>
                            <i class="bi bi-eye"></i> Vista previa · Lado del cliente
                        </div>
                        <div class="fb-preview-body" id="fbPreviewBody"></div>
                    </div>

                </div>

            <?php endif; ?>

        </div><!-- /dash-content -->
    </div><!-- /dash-main -->
</div><!-- /dash-layout -->

<?php if ($formType): ?>
<!-- Modal editar campo -->
<div class="modal-overlay" id="fbFieldModal">
    <div class="modal-box" style="max-width:580px;">
        <div class="modal-header" style="border-top:3px solid var(--mod-accent, var(--arco-siena));">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:var(--arco-lino);color:var(--mod-accent,var(--arco-siena));display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="bi bi-gear"></i>
                </div>
                <h3 style="margin:0;font-size:1.05rem;">Configurar Campo</h3>
            </div>
            <button class="modal-close" onclick="fbCloseFieldModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" style="padding:20px 24px;">

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Tipo de dato / cómo se ingresa</label>
                <select id="fbFieldType" onchange="fbFieldTypeChanged()" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;background:var(--arco-card-bg,#fff);">
                    <?php foreach ($fieldTypes as $type): ?>
                    <option value="<?= (int) $type['id_form_field_type'] ?>"><?= htmlspecialchars($type['name']) ?> — <?= htmlspecialchars($type['description']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Etiqueta (lo que verá el usuario)</label>
                <input type="text" id="fbFieldLabel" placeholder="Ej. Nombre completo" oninput="fbSuggestFieldName()" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
            </div>
            <div style="display:flex;gap:14px;margin-bottom:16px;">
                <div class="form-group" style="flex:1;">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Nombre técnico</label>
                    <input type="text" id="fbFieldName" placeholder="Ej. nombre_completo" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                </div>
                <div class="form-group" style="flex:1;">
                    <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Placeholder</label>
                    <input type="text" id="fbFieldPlaceholder" placeholder="Texto de ejemplo" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Texto de ayuda</label>
                <input type="text" id="fbFieldHelp" placeholder="Instrucciones adicionales (opcional)" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
            </div>
            <div class="form-group" id="fbFieldFileTypesGroup" style="margin-bottom:16px;display:none;">
                <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Tipos de archivo permitidos</label>
                <input type="text" id="fbFieldFileTypes" placeholder="Ej. pdf,jpg,png" style="width:100%;padding:10px 14px;border:1px solid var(--arco-perla);border-radius:8px;font-size:.88rem;">
            </div>
            <div class="form-group" id="fbFieldOptionsGroup" style="margin-bottom:16px;display:none;">
                <label style="font-weight:600;font-size:.84rem;color:var(--arco-carbon);margin-bottom:5px;display:block;">Opciones</label>
                <div id="fbOptionsList"></div>
                <button type="button" class="btn-secondary" style="margin-top:6px;padding:8px 14px;font-size:.82rem;" onclick="fbAddOption()"><i class="bi bi-plus-lg"></i> Agregar opción</button>
            </div>
            <label id="fbFieldRequiredWrap" style="display:flex;align-items:center;gap:8px;font-size:.84rem;font-weight:600;color:var(--arco-carbon);cursor:pointer;margin-top:12px;">
                <input type="checkbox" id="fbFieldRequired" style="accent-color:var(--mod-accent,var(--arco-siena));">
                Campo obligatorio
            </label>
        </div>
        <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--arco-perla);">
            <button type="button" class="btn-secondary" onclick="fbCloseFieldModal()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="fbSaveFieldModal()" style="padding:10px 24px;font-weight:700;">Guardar Campo</button>
        </div>
    </div>
</div>

<script>
(function () {
    const canEdit = <?= Auth::can('formularios', 'editar') ? 'true' : 'false' ?>;
    const fieldTypes = <?= json_encode(array_map(fn($t) => [
        'id'                => (int) $t['id_form_field_type'],
        'name'              => $t['name'],
        'field_key'         => $t['field_key'],
        'icon'              => $t['icon'] ?: 'bi-input-cursor-text',
        'allows_option'     => (bool) $t['allows_option'],
        'allows_validation' => (bool) $t['allows_validation'],
    ], $fieldTypes)) ?>;
    const fieldTypesById = {};
    fieldTypes.forEach(t => fieldTypesById[t.id] = t);
    const defaultTypeId = fieldTypes.length ? fieldTypes[0].id : 0;

    const initialSections = <?= json_encode(array_map(function ($s) {
        return [
            'section_name'        => $s['section_name'],
            'section_description' => $s['section_description'],
            'is_repeatable'       => (bool) $s['is_repeatable'],
            'fields' => array_map(function ($f) {
                return [
                    'id_form_field_type' => (int) $f['id_form_field_type'],
                    'name'                => $f['name'],
                    'label'               => $f['label'],
                    'placeholder'         => $f['placeholder'],
                    'help_text'           => $f['help_text'],
                    'default_value'       => $f['default_value'],
                    'is_required'         => (bool) $f['is_required'],
                    'is_visible'          => (bool) $f['is_visible'],
                    'allowed_file_types'  => $f['allowed_file_types'],
                    'options'             => array_map(fn($o) => [
                        'option_label' => $o['option_label'],
                        'option_value' => $o['option_value'],
                    ], $f['options'] ?? []),
                ];
            }, $s['fields'] ?? []),
        ];
    }, $sections)) ?>;

    const idFormType    = <?= (int) $formType['id_form_type'] ?>;
    const idFormVersion = <?= (int) ($formType['id_current_version'] ?? 0) ?>;
    const formName        = <?= json_encode($formType['name']) ?>;
    const formDescription = <?= json_encode($formType['description'] ?? '') ?>;

    let uidCounter = 1;
    const nextUid = () => 'u' + (uidCounter++);

    let state = {
        sections: initialSections.map(s => ({ ...s, _uid: nextUid(), fields: s.fields.map(f => ({ ...f, _uid: nextUid() })) }))
    };
    let activeSectionUid = state.sections.length ? state.sections[0]._uid : null;
    let editingFieldRef = null; // { sectionUid, fieldUid }

    function slugify(text) {
        return (text || '').toString().trim().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s_-]/g, '')
            .replace(/\s+/g, '_');
    }

    function findSection(uid) { return state.sections.find(s => s._uid === uid); }
    function findField(sUid, fUid) {
        const s = findSection(sUid);
        return s ? s.fields.find(f => f._uid === fUid) : null;
    }

    window.fbAddSection = function () {
        if (!canEdit) return;
        const s = { _uid: nextUid(), section_name: 'Nueva sección', section_description: '', is_repeatable: false, fields: [] };
        state.sections.push(s);
        activeSectionUid = s._uid;
        render();
    };

    window.fbRemoveSection = function (uid) {
        showConfirm('¿Eliminar esta sección y todos sus campos?').then(function(ok) {
            if (!ok) return;
            state.sections = state.sections.filter(s => s._uid !== uid);
            if (activeSectionUid === uid) activeSectionUid = state.sections.length ? state.sections[0]._uid : null;
            render();
        });
    };

    window.fbMoveSection = function (uid, dir) {
        const idx = state.sections.findIndex(s => s._uid === uid);
        const newIdx = idx + dir;
        if (newIdx < 0 || newIdx >= state.sections.length) return;
        const [item] = state.sections.splice(idx, 1);
        state.sections.splice(newIdx, 0, item);
        render();
    };

    window.fbSetActiveSection = function (uid) {
        activeSectionUid = uid;
        render();
    };

    window.fbAddField = function (typeId) {
        if (!canEdit) return;
        if (!activeSectionUid || !findSection(activeSectionUid)) {
            if (!state.sections.length) fbAddSection();
            activeSectionUid = state.sections[0]._uid;
        }
        const section = findSection(activeSectionUid);
        const type = fieldTypesById[typeId];
        const field = {
            _uid: nextUid(),
            id_form_field_type: typeId,
            name: '',
            label: type ? type.name : 'Campo',
            placeholder: '',
            help_text: '',
            default_value: '',
            is_required: false,
            is_visible: true,
            allowed_file_types: '',
            options: type && type.allows_option ? [{ option_label: 'Opción 1', option_value: 'opcion_1' }] : [],
        };
        section.fields.push(field);
        render();
        fbOpenFieldModal(section._uid, field._uid);
    };

    window.fbRemoveField = function (sUid, fUid) {
        showConfirm('¿Eliminar este campo?').then(function(ok) {
            if (!ok) return;
            const s = findSection(sUid);
            s.fields = s.fields.filter(f => f._uid !== fUid);
            render();
        });
    };

    window.fbMoveField = function (sUid, fUid, dir) {
        const s = findSection(sUid);
        const idx = s.fields.findIndex(f => f._uid === fUid);
        const newIdx = idx + dir;
        if (newIdx < 0 || newIdx >= s.fields.length) return;
        const [item] = s.fields.splice(idx, 1);
        s.fields.splice(newIdx, 0, item);
        render();
    };

    window.fbOpenFieldModal = function (sUid, fUid) {
        const field = findField(sUid, fUid);
        if (!field) return;
        editingFieldRef = { sectionUid: sUid, fieldUid: fUid };

        document.getElementById('fbFieldType').value = field.id_form_field_type;
        document.getElementById('fbFieldLabel').value = field.label || '';
        document.getElementById('fbFieldName').value = field.name || '';
        document.getElementById('fbFieldName').dataset.touched = field.name ? '1' : '';
        document.getElementById('fbFieldPlaceholder').value = field.placeholder || '';
        document.getElementById('fbFieldHelp').value = field.help_text || '';
        document.getElementById('fbFieldRequired').checked = !!field.is_required;
        document.getElementById('fbFieldFileTypes').value = field.allowed_file_types || '';

        applyTypeVisibility(field.id_form_field_type);
        renderOptionsList(field.options || []);

        document.getElementById('fbFieldModal').classList.add('open');
    };

    window.fbCloseFieldModal = function () {
        document.getElementById('fbFieldModal').classList.remove('open');
        editingFieldRef = null;
    };

    function applyTypeVisibility(typeId) {
        const type = fieldTypesById[typeId];
        const isFile = type && type.field_key === 'file';
        const isHeading = type && type.field_key === 'heading';
        const showOptions = type && type.allows_option;

        document.getElementById('fbFieldFileTypesGroup').style.display = isFile ? '' : 'none';
        document.getElementById('fbFieldOptionsGroup').style.display = showOptions ? '' : 'none';
        document.getElementById('fbFieldRequiredWrap').style.display = (type && type.allows_validation) ? '' : 'none';
        document.getElementById('fbFieldPlaceholder').closest('.form-group').style.display = isHeading ? 'none' : '';
    }

    window.fbFieldTypeChanged = function () {
        if (!editingFieldRef) return;
        const field = findField(editingFieldRef.sectionUid, editingFieldRef.fieldUid);
        const newTypeId = parseInt(document.getElementById('fbFieldType').value, 10);
        const type = fieldTypesById[newTypeId];
        applyTypeVisibility(newTypeId);
        if (type && type.allows_option && (!field.options || !field.options.length)) {
            field.options = [{ option_label: 'Opción 1', option_value: 'opcion_1' }];
            renderOptionsList(field.options);
        }
    };

    function renderOptionsList(options) {
        const wrap = document.getElementById('fbOptionsList');
        wrap.innerHTML = '';
        options.forEach((opt, i) => {
            const row = document.createElement('div');
            row.className = 'fb-option-row';
            row.innerHTML = `
                <input type="text" value="${escapeAttr(opt.option_label)}" placeholder="Etiqueta de la opción" data-opt-label="${i}">
                <button type="button" class="fb-btn-tiny danger" onclick="fbRemoveOption(${i})"><i class="bi bi-trash"></i></button>
            `;
            wrap.appendChild(row);
        });
    }

    window.fbAddOption = function () {
        const field = findField(editingFieldRef.sectionUid, editingFieldRef.fieldUid);
        const n = (field.options || []).length + 1;
        field.options = field.options || [];
        field.options.push({ option_label: 'Opción ' + n, option_value: 'opcion_' + n });
        renderOptionsList(field.options);
    };

    window.fbRemoveOption = function (idx) {
        const field = findField(editingFieldRef.sectionUid, editingFieldRef.fieldUid);
        field.options.splice(idx, 1);
        renderOptionsList(field.options);
    };

    window.fbSaveFieldModal = function () {
        if (!editingFieldRef) return;
        const field = findField(editingFieldRef.sectionUid, editingFieldRef.fieldUid);
        if (!field) return;

        const label = document.getElementById('fbFieldLabel').value.trim();
        if (!label) { showToast('La etiqueta del campo es obligatoria.', 'error'); return; }

        field.id_form_field_type = parseInt(document.getElementById('fbFieldType').value, 10);
        field.label = label;
        field.name = slugify(document.getElementById('fbFieldName').value.trim() || label);
        field.placeholder = document.getElementById('fbFieldPlaceholder').value.trim();
        field.help_text = document.getElementById('fbFieldHelp').value.trim();
        field.is_required = document.getElementById('fbFieldRequired').checked;
        field.allowed_file_types = document.getElementById('fbFieldFileTypes').value.trim();

        const type = fieldTypesById[field.id_form_field_type];
        if (type && type.allows_option) {
            document.querySelectorAll('#fbOptionsList [data-opt-label]').forEach((input, i) => {
                if (field.options[i]) {
                    field.options[i].option_label = input.value.trim();
                    field.options[i].option_value = slugify(input.value.trim());
                }
            });
        } else {
            field.options = [];
        }

        fbCloseFieldModal();
        render();
    };

    window.fbSuggestFieldName = function () {
        const nameInput = document.getElementById('fbFieldName');
        if (nameInput.dataset.touched) return;
        nameInput.value = slugify(document.getElementById('fbFieldLabel').value);
    };
    document.getElementById('fbFieldName').addEventListener('input', function () { this.dataset.touched = '1'; });

    function escapeHtml(str) {
        return (str || '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }
    function escapeAttr(str) { return escapeHtml(str); }

    /* ── Editor (columna central) ─────────────────────────────── */

    function renderEditor() {
        const container = document.getElementById('fbSectionsContainer');
        container.innerHTML = '';
        let totalFields = 0;

        state.sections.forEach((section) => {
            totalFields += section.fields.length;
            const box = document.createElement('div');
            box.className = 'fb-section';
            if (section._uid === activeSectionUid) box.style.borderColor = 'var(--mod-accent, #4A5A8C)';

            const head = document.createElement('div');
            head.className = 'fb-section-head';
            head.innerHTML = `
                <div class="fb-drag-handle"><i class="bi bi-grip-vertical"></i></div>
                <div class="fb-section-titles">
                    <input class="fb-section-name" value="${escapeAttr(section.section_name)}" ${canEdit ? '' : 'disabled'} placeholder="Nombre de la sección">
                    <input class="fb-section-desc" value="${escapeAttr(section.section_description || '')}" ${canEdit ? '' : 'disabled'} placeholder="Descripción (opcional)">
                </div>
                <div class="fb-section-actions">
                    ${canEdit ? `
                    <button type="button" class="fb-btn-tiny" title="Mover arriba" onclick="fbMoveSection('${section._uid}', -1)"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="fb-btn-tiny" title="Mover abajo" onclick="fbMoveSection('${section._uid}', 1)"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="fb-btn-tiny danger" title="Eliminar sección" onclick="fbRemoveSection('${section._uid}')"><i class="bi bi-trash"></i></button>
                    ` : ''}
                </div>
            `;
            head.querySelector('.fb-section-name').addEventListener('click', () => fbSetActiveSection(section._uid));
            head.querySelector('.fb-section-name').addEventListener('input', (e) => { section.section_name = e.target.value; renderPreview(); });
            head.querySelector('.fb-section-desc').addEventListener('click', () => fbSetActiveSection(section._uid));
            head.querySelector('.fb-section-desc').addEventListener('input', (e) => { section.section_description = e.target.value; renderPreview(); });
            box.appendChild(head);

            const body = document.createElement('div');
            body.className = 'fb-section-body';

            if (!section.fields.length) {
                body.innerHTML = '<div class="fb-empty-section">Esta sección no tiene campos todavía. Elige un tipo de dato en el panel izquierdo.</div>';
            } else {
                section.fields.forEach((field) => {
                    const type = fieldTypesById[field.id_form_field_type] || { icon: 'bi-input-cursor-text', name: 'Campo' };
                    const row = document.createElement('div');
                    row.className = 'fb-field-row';
                    row.innerHTML = `
                        <div class="fb-drag-handle"><i class="bi bi-grip-vertical"></i></div>
                        <div class="fb-field-icon"><i class="bi ${type.icon}"></i></div>
                        <div class="fb-field-info">
                            <div class="fb-field-label">${escapeHtml(field.label || 'Sin etiqueta')}</div>
                            <div class="fb-field-sub">${type.name}${field.name ? ' · ' + escapeHtml(field.name) : ''}</div>
                        </div>
                        ${field.is_required ? '<span class="fb-field-required">Obligatorio</span>' : ''}
                        <div class="fb-field-actions">
                            ${canEdit ? `
                            <button type="button" class="fb-btn-tiny" title="Mover arriba" onclick="fbMoveField('${section._uid}','${field._uid}', -1)"><i class="bi bi-arrow-up"></i></button>
                            <button type="button" class="fb-btn-tiny" title="Mover abajo" onclick="fbMoveField('${section._uid}','${field._uid}', 1)"><i class="bi bi-arrow-down"></i></button>
                            <button type="button" class="fb-btn-tiny" title="Configurar" onclick="fbOpenFieldModal('${section._uid}','${field._uid}')"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="fb-btn-tiny danger" title="Eliminar" onclick="fbRemoveField('${section._uid}','${field._uid}')"><i class="bi bi-trash"></i></button>
                            ` : ''}
                        </div>
                    `;
                    body.appendChild(row);
                });
            }

            box.appendChild(body);
            container.appendChild(box);
        });

        document.getElementById('fbFieldCount').textContent = totalFields + (totalFields === 1 ? ' campo' : ' campos');
    }

    /* ── Vista previa (lado cliente, en vivo) ─────────────────── */

    function renderPreviewField(field) {
        const type = fieldTypesById[field.id_form_field_type];
        const key = type ? type.field_key : 'text';
        const label = escapeHtml(field.label || '');
        const req = field.is_required ? ' <span class="req">*</span>' : '';
        const help = field.help_text ? `<div class="fb-preview-help">${escapeHtml(field.help_text)}</div>` : '';

        if (key === 'heading') {
            return `<div class="fb-preview-heading">${label}</div>${field.help_text ? `<div class="fb-preview-help" style="margin-bottom:8px;">${escapeHtml(field.help_text)}</div>` : ''}`;
        }

        let control = '';
        switch (key) {
            case 'textarea':
                control = `<textarea rows="3" placeholder="${escapeAttr(field.placeholder)}" disabled></textarea>`;
                break;
            case 'number':
                control = `<input type="number" placeholder="${escapeAttr(field.placeholder)}" disabled>`;
                break;
            case 'email':
                control = `<input type="email" placeholder="${escapeAttr(field.placeholder || 'correo@ejemplo.com')}" disabled>`;
                break;
            case 'phone':
                control = `<input type="tel" placeholder="${escapeAttr(field.placeholder || '0000-0000')}" disabled>`;
                break;
            case 'date':
                control = `<input type="date" disabled>`;
                break;
            case 'select':
                control = `<select disabled><option>Selecciona una opción</option>${(field.options || []).map(o => `<option>${escapeHtml(o.option_label)}</option>`).join('')}</select>`;
                break;
            case 'radio':
                control = (field.options || []).map(o => `<div class="fb-preview-choice-row"><input type="radio" disabled> ${escapeHtml(o.option_label)}</div>`).join('') || '<div class="fb-preview-help">Agrega opciones a este campo.</div>';
                break;
            case 'checkbox':
                control = (field.options || []).map(o => `<div class="fb-preview-choice-row"><input type="checkbox" disabled> ${escapeHtml(o.option_label)}</div>`).join('') || '<div class="fb-preview-help">Agrega opciones a este campo.</div>';
                break;
            case 'file':
                control = `<div class="fb-preview-file"><i class="bi bi-cloud-arrow-up"></i><br>Arrastra un archivo o haz clic para subir${field.allowed_file_types ? ' (' + escapeHtml(field.allowed_file_types) + ')' : ''}</div>`;
                break;
            case 'signature':
                control = `<div class="fb-preview-signature"><i class="bi bi-vector-pen"></i><br>Área de firma digital</div>`;
                break;
            default:
                control = `<input type="text" placeholder="${escapeAttr(field.placeholder)}" disabled>`;
        }

        return `<div class="fb-preview-field"><label>${label}${req}</label>${control}${help}</div>`;
    }

    function renderPreview() {
        const body = document.getElementById('fbPreviewBody');
        const totalFields = state.sections.reduce((n, s) => n + s.fields.length, 0);

        if (!totalFields) {
            body.innerHTML = `
                <div class="fb-preview-title">${escapeHtml(formName)}</div>
                ${formDescription ? `<div class="fb-preview-desc">${escapeHtml(formDescription)}</div>` : ''}
                <div class="fb-preview-empty"><i class="bi bi-eye" style="font-size:1.6rem;display:block;margin-bottom:8px;"></i>Agrega campos para ver aquí cómo lo verá el cliente.</div>
            `;
            return;
        }

        let html = `<div class="fb-preview-title">${escapeHtml(formName)}</div>`;
        if (formDescription) html += `<div class="fb-preview-desc">${escapeHtml(formDescription)}</div>`;

        state.sections.forEach(section => {
            if (!section.fields.length) return;
            html += `<div class="fb-preview-section">`;
            html += `<h5>${escapeHtml(section.section_name || 'Sección')}</h5>`;
            if (section.section_description) html += `<div class="fb-preview-section-desc">${escapeHtml(section.section_description)}</div>`;
            section.fields.forEach(f => html += renderPreviewField(f));
            html += `</div>`;
        });

        html += `<button type="button" class="fb-preview-submit" disabled>Enviar formulario</button>`;
        body.innerHTML = html;
    }

    function render() {
        renderEditor();
        renderPreview();
    }

    window.fbSave = function () {
        if (!state.sections.length) {
            showToast('Agrega al menos una sección con un campo antes de guardar.', 'error');
            return;
        }
        const hasField = state.sections.some(s => s.fields.length > 0);
        if (!hasField) {
            showToast('Agrega al menos un campo antes de guardar.', 'error');
            return;
        }
        for (const s of state.sections) {
            if (!s.section_name.trim()) {
                showToast('Todas las secciones deben tener un nombre.', 'error');
                return;
            }
        }

        const payload = {
            id_form_type: idFormType,
            id_form_version: idFormVersion,
            sections: state.sections.map(s => ({
                section_name: s.section_name,
                section_description: s.section_description,
                is_repeatable: s.is_repeatable,
                fields: s.fields.map(f => ({
                    id_form_field_type: f.id_form_field_type,
                    name: f.name || slugify(f.label),
                    label: f.label,
                    placeholder: f.placeholder,
                    help_text: f.help_text,
                    default_value: f.default_value,
                    is_required: f.is_required,
                    is_visible: f.is_visible,
                    allowed_file_types: f.allowed_file_types,
                    options: f.options || [],
                })),
            })),
        };

        const btn = document.getElementById('fbSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

        fetch('?url=admin/form-builder/save-structure', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        })
        .then(function(r){return r.text()}).then(function(t){try{return JSON.parse(t)}catch(e){return{success:false}}})
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar Formulario';
            if (data.success) {
                showToast(data.message || 'Formulario guardado exitosamente.', 'success');
            } else {
                showToast(data.message || 'Ocurrió un error al guardar.', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar Formulario';
            showToast('Error de conexión. Intente nuevamente.', 'error');
        });
    };

    document.getElementById('fbFieldModal').addEventListener('click', function (e) {
        if (e.target === this) fbCloseFieldModal();
    });

    render();
})();
</script>
<?php endif; ?>

<script src="<?= URL ?>assets/js/toast.js"></script>
</body>
</html>