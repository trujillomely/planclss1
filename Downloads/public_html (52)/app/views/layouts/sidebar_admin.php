<!-- ── Sidebar Admin ── -->
<aside class="dash-sidebar" id="dashSidebar">
    <div class="sidebar-brand">
        <img src="<?= URL ?>assets/img/logotipo-removebg-preview.png" alt="Arco">
        <div class="sidebar-brand-text">
            <h2>Arco Seguros</h2>
            <p>Sistema de Gestión</p>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Menú principal">
        <?php if (Auth::can('dashboard', 'ver')): ?>
        <a href="?url=admin/dashboard"><i class="bi bi-house-door"></i>Dashboard</a>
        <?php endif; ?>

        <?php if (Auth::can('usuarios', 'ver') || Auth::can('roles', 'ver') || Auth::can('permisos', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-people"></i>Usuarios y Roles</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <?php if (Auth::can('usuarios', 'ver')): ?><a href="?url=admin/users">Usuarios</a><?php endif; ?>
                <?php if (Auth::can('roles', 'ver')): ?><a href="?url=admin/roles">Roles</a><?php endif; ?>
                <?php if (Auth::can('permisos', 'ver')): ?><a href="?url=admin/permissions">Permisos</a><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('aseguradoras', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-building"></i>Aseguradoras</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/insurance-companies">Aseguradoras</a>
                <a href="?url=admin/insurance-types">Tipos de Seguro</a>
                <a href="?url=admin/insurance-categories">Categorías</a>
                <a href="?url=admin/providers">Proveedores</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('cotizaciones', 'ver')): ?>
        <a href="?url=admin/cotizaciones"><i class="bi bi-calculator"></i>Cotizaciones</a>
        <?php endif; ?>

        <?php if (Auth::can('agenda', 'ver')): ?>
        <a href="?url=admin/agenda"><i class="bi bi-calendar-check"></i>Agenda</a>
        <?php endif; ?>

        <?php if (Auth::can('mensajes', 'ver')): ?>
        <a href="#" onclick="document.querySelector('.chat-open-btn').click();return false;"><i class="bi bi-chat-dots"></i>Mensajes</a>
        <?php endif; ?>

        <?php if (Auth::can('polizas', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-file-earmark-check"></i>Pólizas</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/policy">Pólizas</a>
                <a href="?url=admin/renewals"><i class="bi bi-arrow-repeat" style="font-size:.7rem;"></i> Renovaciones</a>
                <a href="?url=admin/coverages">Coberturas</a>
                <a href="?url=admin/beneficiaries">Beneficiarios</a>
                <a href="?url=admin/policy-documents">Documentos</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('pagos', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-credit-card"></i>Pagos y Cobros</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/payment-calendar">Calendario</a>
                <a href="?url=admin/transactions">Transacciones</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('reclamos', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-shield-exclamation"></i>Reclamos</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/claims">Reclamos</a>
                <a href="?url=admin/claim-payments">Pagos</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('servicios', 'ver')): ?>
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-hospital"></i>Servicios</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/services">Servicios</a>
                <a href="?url=admin/service-categories">Categorías</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Auth::can('formularios', 'ver')): ?>
        <a href="?url=admin/form-builder"><i class="bi bi-file-earmark-text"></i>Formularios</a>
        <a href="?url=admin/form-submissions"><i class="bi bi-inboxes"></i>Envios</a>
        <?php endif; ?>

        <?php if (Auth::can('reportes', 'ver')): ?>
        <a href="?url=admin/reportes"><i class="bi bi-bar-chart-line"></i>Reportes</a>
        <?php endif; ?>

        <!-- ═══ CONFIGURACIÓN ═══ -->
        <div class="sidebar-section" data-section>
            <button type="button" class="sidebar-section-title" data-toggle="subnav" aria-expanded="false">
                <span class="title-left"><i class="bi bi-gear"></i>Configuración</span>
                <i class="bi bi-caret-right sidebar-section-caret"></i>
            </button>
            <div class="sidebar-subnav">
                <a href="?url=admin/settings"><i class="bi bi-sliders" style="margin-right:6px;font-size:.8rem;"></i>General</a>
                <a href="?url=admin/payment-frequencies"><i class="bi bi-percent" style="margin-right:6px;font-size:.8rem;"></i>Frecuencias de Pago</a>
            </div>
        </div>

        <a href="?url=admin/profile"><i class="bi bi-person-circle"></i>Mi Perfil</a>
    </nav>

    <div class="sidebar-footer">
        <a href="?url=logout"><i class="bi bi-box-arrow-right"></i>Cerrar sesión</a>
    </div>
</aside>
