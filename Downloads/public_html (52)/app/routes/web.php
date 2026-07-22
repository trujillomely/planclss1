<?php
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// Table Export — PDF y Excel con diseño Arco Seguros
// Rutas: admin/table-export/pdf/{entity}  y  admin/table-export/excel/{entity}
if (preg_match('#^admin/table-export/(pdf|excel)/(\w+)$#', $url, $m)) {
    require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
    require_once ROOT_PATH . '/app/controllers/TableExportController.php';
    $controller = new TableExportController();
    $controller->handle($m[2], $m[1]);
    exit;
}

switch($url){

    /*
    |--------------------------------------------------------------------------
    | HOME
    |--------------------------------------------------------------------------
    */

    case 'home':
        require_once ROOT_PATH . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
    break;

    case 'acerca-de':
    case 'acerca_de':
        require_once ROOT_PATH . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->acercaDe();
    break;

    case 'aseguradoras':
        require_once ROOT_PATH . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->aseguradoras();
    break;

    case 'servicios':
        require_once ROOT_PATH . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->servicios();
    break;

    case 'contacto':
        require_once ROOT_PATH . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->contacto();
    break;

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    case 'login':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
    break;

    case 'authenticate':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->authenticate();
    break;

    case 'activate-account':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->activateAccount();
    break;

    case 'logout':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
    break;

    case 'forgot-password':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPassword();
    break;

    case 'forgot-password-send':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPasswordSend();
    break;

    case 'reset-password':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resetPassword();
    break;

    case 'reset-password-save':
        require_once ROOT_PATH . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resetPasswordSave();
    break;

    /*
    |--------------------------------------------------------------------------
    | ADMIN — Vistas (requieren AdminMiddleware)
    |--------------------------------------------------------------------------
    */

    case 'admin/dashboard':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
    break;

    case 'admin/users':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
    break;

    case 'admin/profile':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->profile();
    break;

    case 'admin/roles':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->roles();
    break;

    case 'admin/permissions':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->permissions();
    break;

    case 'admin/insurance-companies':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceCompanies();
    break;

    case 'admin/insurance-types':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceTypes();
    break;

    case 'admin/insurance-categories':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceCategories();
    break;

    case 'admin/providers':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->providers();
    break;

    case 'admin/producers':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->producers();
    break;

    case 'admin/commissions':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->commissions();
    break;

    case 'admin/quotes':
    case 'admin/cotizaciones':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->cotizacionesUnified();
    break;

    case 'admin/policy':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->policy();
    break;

    case 'admin/coverages':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->coverages();
    break;

    case 'admin/beneficiaries':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->beneficiaries();
    break;

    case 'admin/policy-documents':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->policyDocuments();
    break;

    case 'admin/notifications':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->notifications();
    break;

    case 'admin/settings':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settings();
    break;

    case 'admin/form-types':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->formTypes();
    break;

    case 'admin/form-builder':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->formBuilder();
    break;

    case 'admin/form-versions':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->formVersions();
    break;

    case 'admin/form-submissions':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->formSubmissions();
    break;

    case 'admin/reportes':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->reportes();
    break;

    // Renovaciones
    case 'admin/renewals':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->index();
    break;

    case 'admin/renewals/detail':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->detail();
    break;

    case 'admin/renewals/scan':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->scan();
    break;

    case 'admin/renewals/approve':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->approve();
    break;

    case 'admin/renewals/decline':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->decline();
    break;

    case 'admin/renewals/update-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->updateStatus();
    break;

    // CRM
    case 'admin/crm-clients':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->clients();
    break;

    case 'admin/crm-clients/detail':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->clientDetail();
    break;

    case 'admin/crm-clients/update-stage':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateStage();
    break;

    case 'admin/crm-clients/update-notes':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateNotes();
    break;

    case 'admin/crm-clients/update-follow-up':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateFollowUp();
    break;

    case 'admin/crm-clients/assign-agent':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->assignAgent();
    break;

    case 'admin/crm-clients/add-activity':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->addActivity();
    break;

    case 'admin/crm-clients/activities':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getActivities();
    break;

    case 'admin/crm-clients/delete-activity':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->deleteActivity();
    break;

    case 'admin/crm-clients/send-email':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->sendEmail();
    break;

    case 'admin/crm-clients/emails':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getEmails();
    break;

    case 'admin/crm-clients/add-reminder':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->addReminder();
    break;

    case 'admin/crm-clients/complete-reminder':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->completeReminder();
    break;

    case 'admin/crm-clients/cancel-reminder':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->cancelReminder();
    break;

    case 'admin/crm-clients/reminders':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getReminders();
    break;

    case 'admin/crm-clients/timeline':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getTimeline();
    break;

    /*
    |--------------------------------------------------------------------------
    | ADMIN — Settings API
    |--------------------------------------------------------------------------
    */

    case 'admin/settings/save':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settingsSave();
    break;

    case 'admin/settings/test-smtp':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settingsTestSmtp();
    break;

    /*
    |--------------------------------------------------------------------------
    | ADMIN — CRUD API (requieren AdminMiddleware + CSRF via controller)
    |--------------------------------------------------------------------------
    */

    // Users
    case 'admin/users/get-all':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->getAll();
    break;

    case 'admin/users/store':
    case 'admin/users/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->store();
    break;

    case 'admin/users/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->update();
    break;

    case 'admin/users/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->delete();
    break;

    case 'admin/users/toggle-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->toggleStatus();
    break;

    case 'admin/users/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->getById();
    break;

    case 'admin/users/profile':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->getProfile();
    break;

    case 'admin/users/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->downloadProfilePdf();
    break;

    case 'admin/profile/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->updateSelf();
    break;

    case 'admin/profile/change-password':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->changePasswordSelf();
    break;

    // Roles
    case 'admin/roles/get-all':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->getAll();
    break;

    case 'admin/roles/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->getById();
    break;

    case 'admin/roles/store':
    case 'admin/roles/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->store();
    break;

    case 'admin/roles/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->update();
    break;

    case 'admin/roles/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->delete();
    break;

    case 'admin/roles/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/RoleController.php';
        $controller = new RoleController();
        $controller->reactivate();
    break;

    // Permissions
    case 'admin/permissions/get-all':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->getAll();
    break;

    case 'admin/permissions/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->getById();
    break;

    case 'admin/permissions/store':
    case 'admin/permissions/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->store();
    break;

    case 'admin/permissions/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->update();
    break;

    case 'admin/permissions/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->delete();
    break;

    case 'admin/permissions/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->reactivate();
    break;

    case 'admin/permissions/matrix':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->matrix();
    break;

    case 'admin/permissions/toggle':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PermissionController.php';
        $controller = new PermissionController();
        $controller->toggle();
    break;

    // Insurance Companies
    case 'admin/insurance-companies/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCompanyController.php';
        $controller = new InsuranceCompanyController();
        $controller->getById();
    break;

    case 'admin/insurance-companies/store':
    case 'admin/insurance-companies/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCompanyController.php';
        $controller = new InsuranceCompanyController();
        $controller->store();
    break;

    case 'admin/insurance-companies/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCompanyController.php';
        $controller = new InsuranceCompanyController();
        $controller->update();
    break;

    case 'admin/insurance-companies/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCompanyController.php';
        $controller = new InsuranceCompanyController();
        $controller->delete();
    break;

    case 'admin/insurance-companies/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCompanyController.php';
        $controller = new InsuranceCompanyController();
        $controller->reactivate();
    break;

    // Insurance Types
    case 'admin/insurance-types/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceTypeController.php';
        $controller = new InsuranceTypeController();
        $controller->getById();
    break;

    case 'admin/insurance-types/store':
    case 'admin/insurance-types/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceTypeController.php';
        $controller = new InsuranceTypeController();
        $controller->store();
    break;

    case 'admin/insurance-types/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceTypeController.php';
        $controller = new InsuranceTypeController();
        $controller->update();
    break;

    case 'admin/insurance-types/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceTypeController.php';
        $controller = new InsuranceTypeController();
        $controller->delete();
    break;

    case 'admin/insurance-types/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceTypeController.php';
        $controller = new InsuranceTypeController();
        $controller->reactivate();
    break;

    // Insurance Categories
    case 'admin/insurance-categories/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCategoryController.php';
        $controller = new InsuranceCategoryController();
        $controller->getById();
    break;

    case 'admin/insurance-categories/store':
    case 'admin/insurance-categories/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCategoryController.php';
        $controller = new InsuranceCategoryController();
        $controller->store();
    break;

    case 'admin/insurance-categories/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCategoryController.php';
        $controller = new InsuranceCategoryController();
        $controller->update();
    break;

    case 'admin/insurance-categories/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCategoryController.php';
        $controller = new InsuranceCategoryController();
        $controller->delete();
    break;

    case 'admin/insurance-categories/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/InsuranceCategoryController.php';
        $controller = new InsuranceCategoryController();
        $controller->reactivate();
    break;

    // Providers
    case 'admin/providers/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceProviderController.php';
        $controller = new ServiceProviderController();
        $controller->getById();
    break;

    case 'admin/providers/store':
    case 'admin/providers/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceProviderController.php';
        $controller = new ServiceProviderController();
        $controller->store();
    break;

    case 'admin/providers/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceProviderController.php';
        $controller = new ServiceProviderController();
        $controller->update();
    break;

    case 'admin/providers/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceProviderController.php';
        $controller = new ServiceProviderController();
        $controller->delete();
    break;

    case 'admin/providers/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceProviderController.php';
        $controller = new ServiceProviderController();
        $controller->reactivate();
    break;

    /*
    |--------------------------------------------------------------------------
    | PRODUCTORES Y COMISIONES
    |--------------------------------------------------------------------------
    */

    case 'admin/producers/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ProducerController.php';
        $controller = new ProducerController();
        $controller->getById();
    break;

    case 'admin/producers/store':
    case 'admin/producers/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ProducerController.php';
        $controller = new ProducerController();
        $controller->store();
    break;

    case 'admin/producers/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ProducerController.php';
        $controller = new ProducerController();
        $controller->update();
    break;

    case 'admin/producers/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ProducerController.php';
        $controller = new ProducerController();
        $controller->delete();
    break;

    case 'admin/producers/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ProducerController.php';
        $controller = new ProducerController();
        $controller->reactivate();
    break;

    case 'admin/commissions/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CommissionController.php';
        $controller = new CommissionController();
        $controller->getById();
    break;

    case 'admin/commissions/store':
    case 'admin/commissions/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CommissionController.php';
        $controller = new CommissionController();
        $controller->store();
    break;

    case 'admin/commissions/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CommissionController.php';
        $controller = new CommissionController();
        $controller->update();
    break;

    case 'admin/commissions/mark-paid':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CommissionController.php';
        $controller = new CommissionController();
        $controller->markPaid();
    break;

    case 'admin/commissions/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CommissionController.php';
        $controller = new CommissionController();
        $controller->delete();
    break;

    /*
    |--------------------------------------------------------------------------
    | COTIZACIONES
    |--------------------------------------------------------------------------
    */

    case 'admin/quotes/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->getById();
    break;

    case 'admin/quotes/store':
    case 'admin/quotes/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->store();
    break;

    case 'admin/quotes/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->update();
    break;

    case 'admin/quotes/change-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->changeStatus();
    break;

    case 'admin/quotes/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->delete();
    break;

    /*
    |--------------------------------------------------------------------------
    | COTIZADOR AUTOMÁTICO (FASE 8)
    |--------------------------------------------------------------------------
    */

    case 'admin/quoteCalculator':
    case 'admin/quote-calculator':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $_GET['tab'] = 'cotizador';
        $controller->cotizacionesUnified();
    break;

    case 'admin/quoteRates':
    case 'admin/quote-rates':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->rates();
    break;

    case 'quote/ajaxCalculate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxCalculate();
    break;

    case 'quote/ajaxSaveFromCalc':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxSaveFromCalc();
    break;

    case 'quote/ajaxGetRates':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxGetRates();
    break;

    case 'quote/ajaxSaveRate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxSaveRate();
    break;

    case 'quote/ajaxDeleteRate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxDeleteRate();
    break;

    case 'quote/ajaxGeneratePdf':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxGeneratePdf();
    break;

    /*
    |--------------------------------------------------------------------------
    | COMPARADOR DE ASEGURADORAS (FASE 9)
    |--------------------------------------------------------------------------
    */

    case 'admin/comparator':
    case 'admin/insuranceComparator':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $_GET['tab'] = 'comparador';
        $controller->cotizacionesUnified();
    break;

    case 'quote/ajaxCompare':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxCompare();
    break;

    case 'quote/ajaxUpdateRateComparison':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->ajaxUpdateRateComparison();
    break;

    /*
    |--------------------------------------------------------------------------
    | PÓLIZAS
    |--------------------------------------------------------------------------
    */

    case 'admin/policy/store':
    case 'admin/policy/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyController.php';
        $controller = new PolicyController();
        $controller->store();
    break;

    case 'admin/policy/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyController.php';
        $controller = new PolicyController();
        $controller->update();
    break;

    case 'admin/policy/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyController.php';
        $controller = new PolicyController();
        $controller->delete();
    break;

    case 'admin/policy/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyController.php';
        $controller = new PolicyController();
        $controller->reactivate();
    break;

    case 'admin/policy/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyController.php';
        $controller = new PolicyController();
        $controller->getById();
    break;

    // Coverages
    case 'admin/coverages/store':
    case 'admin/coverages/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyCoverageController.php';
        $controller = new PolicyCoverageController();
        $controller->store();
    break;

    case 'admin/coverages/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyCoverageController.php';
        $controller = new PolicyCoverageController();
        $controller->update();
    break;

    case 'admin/coverages/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyCoverageController.php';
        $controller = new PolicyCoverageController();
        $controller->delete();
    break;

    case 'admin/coverages/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyCoverageController.php';
        $controller = new PolicyCoverageController();
        $controller->reactivate();
    break;

    // Beneficiaries
    case 'admin/beneficiaries/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyBeneficiaryController.php';
        $controller = new PolicyBeneficiaryController();
        $controller->getById();
    break;

    case 'admin/beneficiaries/store':
    case 'admin/beneficiaries/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyBeneficiaryController.php';
        $controller = new PolicyBeneficiaryController();
        $controller->store();
    break;

    case 'admin/beneficiaries/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyBeneficiaryController.php';
        $controller = new PolicyBeneficiaryController();
        $controller->update();
    break;

    case 'admin/beneficiaries/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyBeneficiaryController.php';
        $controller = new PolicyBeneficiaryController();
        $controller->delete();
    break;

    case 'admin/beneficiaries/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyBeneficiaryController.php';
        $controller = new PolicyBeneficiaryController();
        $controller->reactivate();
    break;

    // Policy Documents
    case 'admin/policy-documents/store':
    case 'admin/policy-documents/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyDocumentController.php';
        $controller = new PolicyDocumentController();
        $controller->store();
    break;

    case 'admin/policy-documents/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyDocumentController.php';
        $controller = new PolicyDocumentController();
        $controller->update();
    break;

    case 'admin/policy-documents/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyDocumentController.php';
        $controller = new PolicyDocumentController();
        $controller->delete();
    break;

    /*
    |--------------------------------------------------------------------------
    | PAGOS
    |--------------------------------------------------------------------------
    */

    case 'gerente/payment-calendar':
    case 'admin/payment-calendar':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'pagos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->paymentCalendar();
    break;

    case 'admin/payment-calendar/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentScheduleController.php';
        $controller = new PaymentScheduleController();
        $controller->store();
    break;

    case 'admin/payment-calendar/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentScheduleController.php';
        $controller = new PaymentScheduleController();
        $controller->update();
    break;

    case 'admin/payment-calendar/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentScheduleController.php';
        $controller = new PaymentScheduleController();
        $controller->delete();
    break;

    case 'admin/payment-calendar/mark-paid':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentScheduleController.php';
        $controller = new PaymentScheduleController();
        $controller->markPaid();
    break;

    case 'admin/payment-calendar/get':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentScheduleController.php';
        $controller = new PaymentScheduleController();
        $controller->getById();
    break;

    /*
    |--------------------------------------------------------------------------
    | CALENDAR EVENTS API (eventos manuales del calendario)
    |--------------------------------------------------------------------------
    */

    case 'calendar-events/store':
    case 'calendar-events/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->store();
    break;

    case 'calendar-events/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->update();
    break;

    case 'calendar-events/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->delete();
    break;

    case 'calendar-events/change-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->changeStatus();
    break;

    case 'calendar-events/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->getById();
    break;

    /*
    |--------------------------------------------------------------------------
    | AGENDA
    |--------------------------------------------------------------------------
    */

    case 'admin/agenda':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'agenda';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->agenda();
    break;

    case 'gerente/agenda':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'agenda';
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->agenda();
    break;

    case 'cliente/agenda':
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->agenda();
    break;

    case 'agenda/update-priority':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->updatePriority();
    break;

    case 'agenda/complete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->complete();
    break;

    case 'agenda/reopen':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CalendarEventController.php';
        $controller = new CalendarEventController();
        $controller->reopen();
    break;

    // Transactions
    case 'gerente/transactions':
    case 'admin/transactions':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'pagos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->transactions();
    break;

    case 'admin/transactions/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->store();
    break;

    case 'admin/transactions/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->update();
    break;

    case 'admin/transactions/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->delete();
    break;

    case 'admin/transactions/confirm':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->confirm();
    break;

    case 'admin/transactions/get':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->getById();
    break;

    // Payment Methods
    case 'gerente/payment-methods':
    case 'admin/payment-methods':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'pagos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->paymentMethods();
    break;

    case 'admin/payment-methods/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentMethodController.php';
        $controller = new PaymentMethodController();
        $controller->store();
    break;

    case 'admin/payment-methods/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentMethodController.php';
        $controller = new PaymentMethodController();
        $controller->update();
    break;

    case 'admin/payment-methods/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentMethodController.php';
        $controller = new PaymentMethodController();
        $controller->delete();
    break;

    case 'admin/payment-methods/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentMethodController.php';
        $controller = new PaymentMethodController();
        $controller->reactivate();
    break;

    case 'admin/payment-methods/get':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentMethodController.php';
        $controller = new PaymentMethodController();
        $controller->getById();
    break;

    // Payment Frequencies
    case 'gerente/payment-frequencies':
    case 'admin/payment-frequencies':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        $moduleKey = 'pagos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->paymentFrequencies();
    break;

    case 'admin/payment-frequencies/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentFrequencyController.php';
        $controller = new PaymentFrequencyController();
        $controller->store();
    break;

    case 'admin/payment-frequencies/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentFrequencyController.php';
        $controller = new PaymentFrequencyController();
        $controller->update();
    break;

    case 'admin/payment-frequencies/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentFrequencyController.php';
        $controller = new PaymentFrequencyController();
        $controller->delete();
    break;

    case 'admin/payment-frequencies/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentFrequencyController.php';
        $controller = new PaymentFrequencyController();
        $controller->reactivate();
    break;

    case 'admin/payment-frequencies/get':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PaymentFrequencyController.php';
        $controller = new PaymentFrequencyController();
        $controller->getById();
    break;

    // Audit Log
    case 'admin/audit-log':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AuditController.php';
        $controller = new AuditController();
        $controller->index();
    break;

    // Export CSV
    case 'admin/export/users':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->users();
    break;

    case 'admin/export/policies':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->policies();
    break;

    case 'admin/export/quotes':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->quotes();
    break;

    case 'admin/export/claims':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->claims();
    break;

    case 'admin/export/payments':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->payments();
    break;

    case 'admin/export/renewals':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->renewals();
    break;

    // Reportes AJAX (dynamic filters)
    case 'admin/reportes/data':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->reportesData();
    break;

    // Global Search (Ctrl+K)
    case 'admin/search':
    case 'gerente/search':
    case 'cliente/search':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->search();
    break;

    /*
    |--------------------------------------------------------------------------
    | RECLAMOS
    |--------------------------------------------------------------------------
    */

    case 'admin/claims':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->claims();
    break;

    case 'admin/claims/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->getById();
    break;

    case 'admin/claims/store':
    case 'admin/claims/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->store();
    break;

    case 'admin/claims/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->update();
    break;

    case 'admin/claims/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->delete();
    break;

    case 'admin/claims/change-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->changeStatus();
    break;

    // Claim Types
    case 'admin/claim-types/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimTypeController.php';
        $controller = new ClaimTypeController();
        $controller->getById();
    break;

    case 'admin/claim-types/store':
    case 'admin/claim-types/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimTypeController.php';
        $controller = new ClaimTypeController();
        $controller->store();
    break;

    case 'admin/claim-types/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimTypeController.php';
        $controller = new ClaimTypeController();
        $controller->update();
    break;

    case 'admin/claim-types/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimTypeController.php';
        $controller = new ClaimTypeController();
        $controller->delete();
    break;

    case 'admin/claim-types/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimTypeController.php';
        $controller = new ClaimTypeController();
        $controller->reactivate();
    break;

    // Claim Payments
    case 'admin/claim-payments':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->claimPayments();
    break;

    case 'admin/claim-payments/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimPaymentController.php';
        $controller = new ClaimPaymentController();
        $controller->getById();
    break;

    case 'admin/claim-payments/store':
    case 'admin/claim-payments/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimPaymentController.php';
        $controller = new ClaimPaymentController();
        $controller->store();
    break;

    case 'admin/claim-payments/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimPaymentController.php';
        $controller = new ClaimPaymentController();
        $controller->update();
    break;

    case 'admin/claim-payments/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimPaymentController.php';
        $controller = new ClaimPaymentController();
        $controller->delete();
    break;

    /*
    |--------------------------------------------------------------------------
    | SERVICIOS
    |--------------------------------------------------------------------------
    */

    case 'admin/services':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->services();
    break;

    case 'admin/services/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceController.php';
        $controller = new ServiceController();
        $controller->getById();
    break;

    case 'admin/services/store':
    case 'admin/services/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceController.php';
        $controller = new ServiceController();
        $controller->store();
    break;

    case 'admin/services/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceController.php';
        $controller = new ServiceController();
        $controller->update();
    break;

    case 'admin/services/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceController.php';
        $controller = new ServiceController();
        $controller->delete();
    break;

    case 'admin/services/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceController.php';
        $controller = new ServiceController();
        $controller->reactivate();
    break;

    // Service Categories
    case 'admin/service-categories':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->serviceCategories();
    break;

    case 'admin/service-categories/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceCategoryController.php';
        $controller = new ServiceCategoryController();
        $controller->getById();
    break;

    case 'admin/service-categories/store':
    case 'admin/service-categories/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceCategoryController.php';
        $controller = new ServiceCategoryController();
        $controller->store();
    break;

    case 'admin/service-categories/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceCategoryController.php';
        $controller = new ServiceCategoryController();
        $controller->update();
    break;

    case 'admin/service-categories/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceCategoryController.php';
        $controller = new ServiceCategoryController();
        $controller->delete();
    break;

    case 'admin/service-categories/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ServiceCategoryController.php';
        $controller = new ServiceCategoryController();
        $controller->reactivate();
    break;

    // Locations
    case 'admin/locations':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->locations();
    break;

    case 'admin/locations/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->getById();
    break;

    case 'admin/locations/departments/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->storeDepartment();
    break;

    case 'admin/locations/departments/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->updateDepartment();
    break;

    case 'admin/locations/departments/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->deleteDepartment();
    break;

    case 'admin/locations/municipalities/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->storeMunicipality();
    break;

    case 'admin/locations/municipalities/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->updateMunicipality();
    break;

    case 'admin/locations/municipalities/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->deleteMunicipality();
    break;

    case 'admin/locations/localities/store':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->storeLocality();
    break;

    case 'admin/locations/localities/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->updateLocality();
    break;

    case 'admin/locations/localities/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->deleteLocality();
    break;

    // Form Types
    case 'admin/form-types/get-by-id':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormTypeController.php';
        $controller = new FormTypeController();
        $controller->getById();
    break;

    case 'admin/form-types/store':
    case 'admin/form-types/create':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormTypeController.php';
        $controller = new FormTypeController();
        $controller->store();
    break;

    case 'admin/form-types/update':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormTypeController.php';
        $controller = new FormTypeController();
        $controller->update();
    break;

    case 'admin/form-types/delete':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormTypeController.php';
        $controller = new FormTypeController();
        $controller->delete();
    break;

    case 'admin/form-types/reactivate':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormTypeController.php';
        $controller = new FormTypeController();
        $controller->reactivate();
    break;

    // Form Builder
    case 'admin/form-builder/save-structure':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormBuilderController.php';
        $controller = new FormBuilderController();
        $controller->saveStructure();
    break;

    // Form Submissions (Admin AJAX)
    case 'admin/form-submissions/list':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminList();
    break;

    case 'admin/form-submissions/detail':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminDetail();
    break;

    case 'admin/form-submissions/update-status':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminUpdateStatus();
    break;

    /*
    |--------------------------------------------------------------------------
    | GERENTE
    |--------------------------------------------------------------------------
    */

    case 'gerente/dashboard':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/GerenteController.php';
        $controller = new GerenteController();
        $controller->dashboard();
    break;

    case 'gerente/users':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
    break;

    case 'gerente/users/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->downloadProfilePdf();
    break;

    case 'gerente/profile':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->profile();
    break;

    case 'gerente/profile/update':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->updateSelf();
    break;

    case 'gerente/profile/change-password':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->changePasswordSelf();
    break;

    case 'gerente/insurance-companies':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceCompanies();
    break;

    case 'gerente/insurance-types':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceTypes();
    break;

    case 'gerente/insurance-categories':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->insuranceCategories();
    break;

    case 'gerente/providers':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->providers();
    break;

    case 'gerente/producers':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->producers();
    break;

    case 'gerente/commissions':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->commissions();
    break;

    case 'gerente/quotes':
    case 'gerente/cotizaciones':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->cotizacionesUnified();
    break;

    case 'gerente/quoteCalculator':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $_GET['tab'] = 'cotizador';
        $controller->cotizacionesUnified();
    break;

    case 'gerente/quoteRates':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->rates();
    break;

    case 'gerente/comparator':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $_GET['tab'] = 'comparador';
        $controller->cotizacionesUnified();
    break;

    case 'gerente/policy':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->policy();
    break;

    case 'gerente/coverages':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->coverages();
    break;

    case 'gerente/beneficiaries':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->beneficiaries();
    break;

    case 'gerente/policy-documents':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->policyDocuments();
    break;

    case 'gerente/notifications':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->notifications();
    break;

    case 'gerente/settings':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settings();
    break;

    case 'gerente/settings/save':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settingsSave();
    break;

    case 'gerente/settings/test-smtp':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->settingsTestSmtp();
    break;

    case 'gerente/claims':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'reclamos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->claims();
    break;

    case 'gerente/claim-types':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'reclamos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->claimTypes();
    break;

    case 'gerente/claim-payments':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'reclamos';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->claimPayments();
    break;

    case 'gerente/services':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'servicios';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->services();
    break;

    case 'gerente/service-categories':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'servicios';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->serviceCategories();
    break;

    case 'gerente/locations':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        $moduleKey = 'servicios';
        require_once ROOT_PATH . '/app/middlewares/ModuleMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->locations();
    break;

    case 'gerente/reportes':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->reportes();
    break;

    // Renovaciones gerente
    case 'gerente/renewals':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->index();
    break;

    case 'gerente/renewals/detail':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->detail();
    break;

    case 'gerente/renewals/scan':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->scan();
    break;

    case 'gerente/renewals/approve':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->approve();
    break;

    case 'gerente/renewals/decline':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->decline();
    break;

    case 'gerente/renewals/update-status':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->updateStatus();
    break;

    case 'gerente/form-submissions':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->formSubmissions();
    break;

    case 'gerente/form-submissions/list':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminList();
    break;

    case 'gerente/form-submissions/detail':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminDetail();
    break;

    case 'gerente/form-submissions/update-status':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->adminUpdateStatus();
    break;

    // CRM Gerente
    case 'gerente/crm-clients':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->clients();
    break;

    case 'gerente/crm-clients/detail':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->clientDetail();
    break;

    case 'gerente/crm-clients/update-stage':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateStage();
    break;

    case 'gerente/crm-clients/update-notes':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateNotes();
    break;

    case 'gerente/crm-clients/update-follow-up':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->updateFollowUp();
    break;

    case 'gerente/crm-clients/assign-agent':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->assignAgent();
    break;

    case 'gerente/crm-clients/add-activity':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->addActivity();
    break;

    case 'gerente/crm-clients/activities':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getActivities();
    break;

    case 'gerente/crm-clients/delete-activity':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->deleteActivity();
    break;

    case 'gerente/crm-clients/send-email':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->sendEmail();
    break;

    case 'gerente/crm-clients/emails':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getEmails();
    break;

    case 'gerente/crm-clients/add-reminder':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->addReminder();
    break;

    case 'gerente/crm-clients/complete-reminder':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->completeReminder();
    break;

    case 'gerente/crm-clients/cancel-reminder':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->cancelReminder();
    break;

    case 'gerente/crm-clients/reminders':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getReminders();
    break;

    case 'gerente/crm-clients/timeline':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/CrmController.php';
        $controller = new CrmController();
        $controller->getTimeline();
    break;

    /*
    |--------------------------------------------------------------------------
    | CLIENTE
    |--------------------------------------------------------------------------
    */

    case 'cliente/dashboard':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->dashboard();
    break;

    case 'cliente/policies':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->policies();
    break;

    case 'cliente/renewals':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->renewals();
    break;

    case 'cliente/renewals/accept':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->clientAccept();
    break;

    case 'cliente/renewals/decline':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/PolicyRenewalController.php';
        $controller = new PolicyRenewalController();
        $controller->clientDecline();
    break;

    case 'cliente/quotes':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->quotes();
    break;

    case 'cliente/quotes/store':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/QuoteController.php';
        $controller = new QuoteController();
        $controller->clientStore();
    break;

    case 'cliente/claims':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->claims();
    break;

    case 'cliente/claims/store':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->clientStore();
    break;

    case 'cliente/claims/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClaimController.php';
        $controller = new ClaimController();
        $controller->downloadPdf();
    break;

    case 'cliente/payments':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->payments();
    break;

    case 'cliente/payment-calendar':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->paymentCalendar();
    break;

    case 'cliente/payments/store':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/models/PaymentSchedule.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->clientStore();
    break;

    case 'cliente/payments/pay-card':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/models/PaymentSchedule.php';
        require_once ROOT_PATH . '/app/controllers/PaymentTransactionController.php';
        $controller = new PaymentTransactionController();
        $controller->payWithCard();
    break;

    case 'cliente/form':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->form();
    break;

    case 'cliente/form/detail':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->formDetail();
    break;

    case 'cliente/form/submit':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->submit();
    break;

    case 'cliente/form/my-submissions':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->mySubmissions();
    break;

    case 'cliente/form/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->downloadPdf();
    break;

    case 'cliente/form/preview-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->previewPdf();
    break;

    case 'cliente/form/get-submission':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/FormSubmissionController.php';
        $controller = new FormSubmissionController();
        $controller->getSubmission();
    break;

    case 'cliente/services':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->services();
    break;

    case 'cliente/reports':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->reports();
    break;

    case 'cliente/reports/export':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->reportsExport();
    break;

    case 'cliente/profile':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->profile();
    break;

    case 'cliente/profile/update':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->updateSelf();
    break;

    case 'cliente/profile/change-password':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->changePasswordSelf();
    break;

    case 'cliente/profile/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/UserController.php';
        $controller = new UserController();
        $controller->downloadProfilePdf();
    break;

    case 'cliente/notifications':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->notifications();
    break;

    case 'cliente/settings':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->settings();
    break;

    case 'cliente/notifications/mark-read':
    case 'admin/notifications/mark-read':
    case 'gerente/notifications/mark-read':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->markRead();
    break;

    case 'cliente/notifications/mark-all-read':
    case 'admin/notifications/mark-all-read':
    case 'gerente/notifications/mark-all-read':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->markAllRead();
    break;

    /*
    |--------------------------------------------------------------------------
    | CHAT — Mensajería interna
    |--------------------------------------------------------------------------
    */

    case 'admin/chat':
    case 'gerente/chat':
    case 'cliente/chat':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->index();
    break;

    case 'admin/chat/conversations':
    case 'gerente/chat/conversations':
    case 'cliente/chat/conversations':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->conversations();
    break;

    case 'admin/chat/get-or-create':
    case 'gerente/chat/get-or-create':
    case 'cliente/chat/get-or-create':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->getOrCreate();
    break;

    case 'admin/chat/messages':
    case 'gerente/chat/messages':
    case 'cliente/chat/messages':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->messages();
    break;

    case 'admin/chat/send':
    case 'gerente/chat/send':
    case 'cliente/chat/send':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->send();
    break;

    case 'admin/chat/poll':
    case 'gerente/chat/poll':
    case 'cliente/chat/poll':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->poll();
    break;

    case 'admin/chat/unread-count':
    case 'gerente/chat/unread-count':
    case 'cliente/chat/unread-count':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->unreadCount();
    break;

    case 'admin/chat/search-users':
    case 'gerente/chat/search-users':
    case 'cliente/chat/search-users':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->searchUsers();
    break;

    case 'admin/chat/mark-read':
    case 'gerente/chat/mark-read':
    case 'cliente/chat/mark-read':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->markRead();
    break;

    /*
    |--------------------------------------------------------------------------
    | FIRMA DIGITAL
    |--------------------------------------------------------------------------
    */

    case 'admin/signatures/sign':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->sign();
    break;

    case 'gerente/signatures/sign':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->sign();
    break;

    case 'cliente/signatures/sign':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->sign();
    break;

    case 'admin/signatures/get-by-policy':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->getByPolicy();
    break;

    case 'gerente/signatures/get-by-policy':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->getByPolicy();
    break;

    case 'cliente/signatures/get-by-policy':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->getByPolicy();
    break;

    case 'admin/signatures/verify':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->verify();
    break;

    case 'gerente/signatures/verify':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->verify();
    break;

    case 'cliente/signatures/verify':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->verify();
    break;

    case 'admin/signatures/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->downloadPdf();
    break;

    case 'gerente/signatures/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->downloadPdf();
    break;

    case 'cliente/signatures/download-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->downloadPdf();
    break;

    case 'admin/signatures/preview-pdf':
        require_once ROOT_PATH . '/app/middlewares/AdminMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->previewPdf();
    break;

    case 'gerente/signatures/preview-pdf':
        require_once ROOT_PATH . '/app/middlewares/GerenteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->previewPdf();
    break;

    case 'cliente/signatures/preview-pdf':
        require_once ROOT_PATH . '/app/middlewares/ClienteMiddleware.php';
        require_once ROOT_PATH . '/app/controllers/DigitalSignatureController.php';
        $controller = new DigitalSignatureController();
        $controller->previewPdf();
    break;

    /*
    |--------------------------------------------------------------------------
    | SOCIAL AUTH
    |--------------------------------------------------------------------------
    */

    case 'social-callback':
        require_once ROOT_PATH . '/app/controllers/SocialAuthController.php';
        $controller = new SocialAuthController();
        $controller->callback();
    break;

    /*
    |--------------------------------------------------------------------------
    | DEFAULT 404
    |--------------------------------------------------------------------------
    */

    default:
        require_once ROOT_PATH . '/app/views/errors/404.php';
    break;
}
