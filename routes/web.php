<?php

if (!App::environment('local')) URL::forceScheme('https');

//Integración
Route::get('integracion/especificacion/servicio/proceso/{id_proceso}/tarea/{id_tarea}', 'Integration\SpecificationController@servicio_get');

Route::middleware(['auth_user'])->group(function () {
    Route::get('/', 'HomeController@index')->name('home');

    Auth::routes();

    Route::post('/uploader/datos/{campo_id}/{etapa_id}', 'UploadController@datos');

    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::get('login/claveunica', 'Auth\LoginController@redirectToProvider')->name('login.claveunica');
    Route::get('login/claveunica/callback', 'Auth\LoginController@handleProviderCallback')->name('login.claveunica.callback');

    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/home/procesos/{categoria_id}', 'HomeController@procesos')->name('home.procesos');

    Route::get('/tramites/iniciar/{proceso_id}', 'TramitesController@iniciar')->name('tramites.iniciar');
    Route::get('/tramites/participados', 'TramitesController@participados')->name('tramites.participados');
    Route::get('/tramites/disponibles', 'TramitesController@disponibles')->name('tramites.disponibles');
    Route::get('/etapas/ejecutar/{etapa_id}/{secuencia?}', 'StagesController@run')->name('stage.run');
    Route::post('/etapas/ejecutar_form/{etapa_id}/{secuencia}', 'StagesController@ejecutar_form')->name('stage.run');
    Route::get('/etapas/ver/{etapa_id}/{secuencia}', 'StagesController@ver')->name('stage.view');
    Route::get('/etapas/inbox', 'StagesController@inbox')->name('stage.inbox');
    Route::get('/etapas/sinasignar', 'StagesController@sinasignar')->name('stage.unassigned');
    Route::get('/etapas/ejecutar_fin/{etapa_id}', 'StagesController@ejecutar_fin')->name('stage.ejecutar_fin');
    Route::post('/etapas/ejecutar_fin_form/{etapa_id}', 'StagesController@ejecutar_fin_form')->name('stage.ejecutar_fin_form');
    Route::get('/etapas/ejecutar_exito', 'StagesController@ejecutar_exito')->name('stage.ejecutar_exito');
    Route::get('/etapas/descargar/{tramites}', 'StagesController@descargar')->name('stage.download');
    Route::post('/etapas/descargar_form', 'StagesController@descargar_form')->name('stage.descargar_form');
    Route::get('/documentos/get/{filename}', 'DocumentController@get')->name('document.get');

    Route::get('/consultas', 'ConsultController@index')->name('consulta');
    Route::post('/consultas', 'ConsultController@index')->name('consulta');

    //Test URL's
    Route::get('/home', 'HomeController@index')->name('autenticacion.login_openid');
    Route::get('/agenda/miagenda', 'HomeController@index')->name('agenda.miagenda');
});

Route::prefix('backend')->namespace('Backend')->name('backend.')->group(function () {
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::get('/salir', 'Auth\LoginController@logout')->name('logout');
    Route::post('/login', 'Auth\LoginController@login')->name('login.submit');
    // Password reset link request routes...
    Route::get('password/email', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.email');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    // Password reset routes...
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset.get');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.reset.post');


    Route::middleware('auth:usuario_backend')->group(function () {
        Route::get('/', 'HomeController@index')->name('home');
        Route::post('/uploader/logo', 'ConfigurationController@mySiteUploadLogo')->name('uploader.logo');
        Route::post('/uploader/themes', 'ConfigurationController@mySiteUploadTheme')->name('uploader');
        Route::post('/uploader/timbre', 'UploaderController@timbre')->name('uploader.timbre');
        Route::get('/uploader/timbre_get/{filename}', 'UploaderController@timbre_get')->name('uploader.timbre_get');
        Route::post('/uploader/firma', 'UploaderController@firma')->name('uploader.firma');
        Route::get('/uploader/firma_get/{filename}', 'UploaderController@firma_get')->name('uploader.firma_get');
        Route::post('/uploader/logo_certificado', 'UploaderController@logo_certificado')->name('uploader.logo_certificado');
        Route::get('/uploader/logo_certificado_get/{filename}', 'UploaderController@logo_certificado_get')->name('uploader.logo_certificado_get');
        Route::view('/cuentas', 'backend.cuentas')->name('cuentas');
        Route::post('/cuentas', 'ConfigurationController@saveMyAccount')->name('cuentas.save');

        //Home
        Route::get('gestion/widget_load/{widget_id}', 'ManagementController@widget_load')->name('management.widget_load');
        Route::get('gestion/widget_create/{tipo}', 'ManagementController@widget_create')->name('management.widget_create');
        Route::post('gestion/widget_config_form/{widget_id}', 'ManagementController@widget_config_form')->name('management.widget_config_form');
        Route::get('gestion/widget_remove/{widget_id}', 'ManagementController@widget_remove')->name('management.widget_remove');

        //Modelador de Procesos
        Route::get('procesos/{id}/export', 'ProcessController@export')->name('procesos.export');
        Route::post('procesos/import', 'ProcessController@import')->name('procesos.import');
        Route::get('procesos/ajax_auditar_eliminar_proceso/{proceso_id}', 'ProcessController@ajax_auditar_eliminar_proceso');
        Route::get('procesos/ajax_publicar_proceso/{proceso_id}', 'ProcessController@ajax_publicar_proceso');
        Route::get('procesos/ajax_auditar_activar_proceso/{proceso_id}', 'ProcessController@ajax_auditar_activar_proceso');
        Route::get('procesos/ajax_editar_tarea/{proceso_id}/{tarea_identificador}', 'ProcessController@ajax_editar_tarea');
        Route::get('procesos/ajax_editar/{proceso_id}', 'ProcessController@ajax_editar');
        Route::post('procesos/editar_form/{proceso_id}', 'ProcessController@editar_form');
        Route::post('procesos/ajax_crear_tarea/{proceso_id}/{tarea_identificador}', 'ProcessController@ajax_crear_tarea');
        Route::post('procesos/ajax_editar_modelo/{proceso_id}', 'ProcessController@ajax_editar_modelo');
        Route::get('procesos/ajax_editar_conexiones/{proceso_id}/{tarea_origen_identificador}/{union?}', 'ProcessController@ajax_editar_conexiones');
        Route::get('procesos/eliminar_conexiones/{tarea_id}', 'ProcessController@eliminar_conexiones');
        Route::post('procesos/editar_conexiones_form/{tarea_id}', 'ProcessController@editar_conexiones_form');
        Route::post('procesos/ajax_crear_conexion/{proceso_id}', 'ProcessController@ajax_crear_conexion');
        Route::post('procesos/editar_tarea_form/{tarea_id}', 'ProcessController@editar_tarea_form')->name('procesos.editar_tarea_form');
        Route::get('procesos/eliminar_tarea/{tarea_id}', 'ProcessController@eliminar_tarea');
        Route::get('procesos/seleccionar_icono', 'ProcessController@seleccionar_icono');
        Route::post('procesos/activar/{proceso_id}', 'ProcessController@activar')->name('procesos.activar');
        Route::post('procesos/publicar/{proceso_draft_id}', 'ProcessController@publicar')->name('procesos.publicar');
        Route::resource('procesos', 'ProcessController');

        Route::post('formularios/importar', 'FormsController@import')->name('forms.import');
        Route::get('formularios/exportar/{proceso_id}', 'FormsController@exportar')->name('forms.export');
        Route::get('formularios/listar/{proceso_id}', 'FormsController@list')->name('forms.list');
        Route::get('formularios/create/{formulario_id}', 'FormsController@create')->name('forms.create');
        Route::get('formularios/editar/{formulario_id}', 'FormsController@edit')->name('forms.edit');
        Route::get('formularios/eliminar/{formulario_id}', 'FormsController@destroy')->name('forms.delete');
        Route::get('formularios/eliminar_campo/{campo_id}', 'FormsController@deleteField')->name('forms.delete_field');
        Route::get('formularios/ajax_editar/{formulario_id}', 'FormsController@ajax_editar')->name('forms.ajax_editar');
        Route::get('formularios/ajax_editar_campo/{campo_id}', 'FormsController@ajax_editar_campo')->name('forms.ajax_editar_campo');
        Route::get('formularios/ajax_agregar_campo/{formulario_id}/{tipo}', 'FormsController@ajax_agregar_campo')->name('forms.ajax_agregar_campo');
        Route::post('formularios/editar_form/{formulario_id}', 'FormsController@editar_form')->name('forms.editar_form');
        Route::post('formularios/editar_campo_form/{campo_id?}', 'FormsController@editar_campo_form')->name('forms.editar_campo_form');
        Route::get('formularios/listarPertenece', 'FormsController@listarPertenece')->name('forms.listarPertenece');
        Route::post('formularios/editar_posicion_campos/{formulario_id}', 'FormsController@editar_posicion_campos')->name('forms.editar_posicion_campos');
        Route::get('formularios/ajax_mi_calendario', 'FormsController@ajax_mi_calendario')->name('forms.ajax_mi_calendario');

        Route::get('documentos/listar/{proceso_id}', 'DocumentController@list')->name('document.list');
        Route::get('documentos/crear/{proceso_id}', 'DocumentController@create')->name('document.create');
        Route::get('documentos/editar/{documento_id}', 'DocumentController@edit')->name('document.edit');
        Route::post('documentos/editar_form/{documento_id?}', 'DocumentController@edit_form')->name('document.edit_form');
        Route::get('documentos/previsualizar/{documento_id}', 'DocumentController@preview')->name('document.preview');
        Route::get('documentos/eliminar/{documento_id}', 'DocumentController@destroy')->name('document.destroy');
        Route::post('documentos/importar', 'DocumentController@import')->name('document.import');
        Route::get('documentos/exportar/{documento_id}', 'DocumentController@export')->name('document.export');

        Route::get('acciones/listar/{proceso_id}', 'ActionController@list')->name('action.list');
        Route::get('acciones/crear/{proceso_id}/{tipo}', 'ActionController@create')->name('action.create');
        Route::get('acciones/editar/{accion_id}', 'ActionController@edit')->name('action.edit');
        Route::post('acciones/edit_form/{accion_id?}', 'ActionController@edit_form')->name('action.edit_form');
        Route::get('acciones/exportar/{accion_id}', 'ActionController@export')->name('action.export');
        Route::post('acciones/importar', 'ActionController@import')->name('action.import');
        Route::get('acciones/eliminar/{accion_id}', 'ActionController@eliminar')->name('action.eliminar');
        Route::get('acciones/ajax_seleccionar/{proceso_id}', 'ActionController@ajax_seleccionar')->name('action.ajax_seleccionar');
        Route::post('acciones/seleccionar_form/{proceso_id}', 'ActionController@seleccionar_form')->name('action.seleccionar_form');
        Route::post('acciones/functions_soap', 'ActionController@functions_soap')->name('action.functions_soap');

        Route::get('Admseguridad/listar/{proceso_id}', 'AdmSecurityController@list')->name('security.list');
        Route::get('Admseguridad/create/{proceso_id}', 'AdmSecurityController@create')->name('security.create');
        Route::get('Admseguridad/editar/{seguridad_id}', 'AdmSecurityController@edit')->name('security.edit');
        Route::post('Admseguridad/editar_form/{seguridad_id?}', 'AdmSecurityController@edit_form')->name('security.edit_form');
        Route::get('Admseguridad/export/{seguridad_id}', 'AdmSecurityController@export')->name('security.export');
        Route::get('Admseguridad/eliminar/{seguridad_id}', 'AdmSecurityController@eliminar')->name('security.eliminar');
        Route::post('Admseguridad/import', 'AdmSecurityController@import')->name('security.import');

        Route::get('suscriptores/listar/{proceso_id}', 'SubscribersController@list')->name('subscribers.list');
        Route::get('suscriptores/create/{proceso_id}', 'SubscribersController@create')->name('subscribers.create');
        Route::get('suscriptores/editar/{seguridad_id}', 'SubscribersController@edit')->name('subscribers.edit');
        Route::post('suscriptores/editar_form/{seguridad_id?}', 'SubscribersController@edit_form')->name('subscribers.edit_form');
        Route::get('suscriptores/export/{seguridad_id}', 'SubscribersController@export')->name('subscribers.export');
        Route::get('suscriptores/eliminar/{seguridad_id}', 'SubscribersController@eliminar')->name('subscribers.delete');
        Route::post('suscriptores/import', 'SubscribersController@import')->name('subscribers.import');

        //Test
        Route::post('formularios/obtener_agenda', 'FormsController@editar_campo_form')->name('forms.obtener_agenda');
        Route::post('documentos/crear/{proceso_id}', 'ProcessController@publicar')->name('documentos.listar');

        //Agenda
        Route::get('agendas', 'AppointmentController@index')->name('appointment.index');
        Route::get('agendas/ajax_back_nueva_agenda', 'AppointmentController@ajax_back_nueva_agenda')->name('appointment.ajax_back_nueva_agenda');
        Route::get('agendas/ajax_grabar_agenda_back', 'AppointmentController@ajax_grabar_agenda_back')->name('appointment.ajax_grabar_agenda_back');
        Route::get('agendas/ajax_back_editar_agenda/{id}', 'AppointmentController@ajax_back_editar_agenda')->name('appointment.ajax_back_editar_agenda');
        Route::get('agendas/ajax_back_eliminar_agenda', 'AppointmentController@ajax_back_eliminar_agenda')->name('appointment.ajax_back_eliminar_agenda');
        Route::get('agendas/ajax_eliminar_agenda', 'AppointmentController@ajax_eliminar_agenda')->name('appointment.ajax_eliminar_agenda');
        Route::get('agendas/ajax_cargarDatosAgenda/{id}', 'AppointmentController@ajax_cargarDatosAgenda')->name('appointment.ajax_cargarDatosAgenda');

        //Seguimiento
        Route::get('seguimiento', 'TracingController@index')->name('tracing.index');
        Route::get('seguimiento/index_proceso/{proceso}', 'TracingController@indexProcess')->name('tracing.list');
        Route::get('seguimiento/ajax_actualizar_id_tramite', 'TracingController@ajaxIdProcedure')->name('tracing.ajaxIdProcedure');
        Route::post('seguimiento/ajax_actualizar_id_tramite', 'TracingController@ajaxUpdateIdProcedure')->name('tracing.ajaxUpdateIdProcedure');
        Route::get('seguimiento/ajax_auditar_eliminar_tramite/{tramite_id}', 'TracingController@ajax_auditar_eliminar_tramite')->name('tracing.ajax_auditar_eliminar_tramite');
        Route::post('seguimiento/borrar_tramite/{tramite_id}', 'TracingController@borrar_tramite')->name('tracing.borrar_tramite');
        Route::get('seguimiento/reset_proc_cont/{proceso_id}', 'TracingController@reset_proc_cont')->name('tracing.reset_proc_cont');
        Route::get('seguimiento/ajax_auditar_limpiar_proceso/{proceso_id}', 'TracingController@ajax_auditar_limpiar_proceso')->name('tracing.ajax_auditar_limpiar_proceso');
        Route::post('seguimiento/borrar_proceso/{proceso_id}', 'TracingController@borrar_proceso')->name('tracing.borrar_proceso');
        Route::get('seguimiento/ver/{tramite_id}', 'TracingController@ver')->name('tracing.ver');
        Route::get('seguimiento/ajax_ver_etapas/{tramite_id}/{tarea_identificador}', 'TracingController@ajax_ver_etapas')->name('tracing.ajax_ver_etapas');
        Route::get('seguimiento/ver_etapa/{etapa_id}/{secuencia?}', 'TracingController@ver_etapa')->name('tracing.ver_etapa');
        Route::get('seguimiento/ajax_auditar_retroceder_etapa/{etapa_id}/{secuencia?}', 'TracingController@ajax_auditar_retroceder_etapa')->name('tracing.ajax_auditar_retroceder_etapa');
        Route::post('seguimiento/retroceder_etapa/{etapa_id}', 'TracingController@retroceder_etapa')->name('tracing.retroceder_etapa');
        Route::post('seguimiento/reasignar_form/{etapa_id}', 'TracingController@reasignar_form')->name('tracing.reasignar_form');

        //Gestión
        Route::get('reportes', 'ReportController@index')->name('report');
        Route::get('reportes/listar/{id}', 'ReportController@list')->name('report.list');
        Route::get('reportes/ver/{reporte_id}', 'ReportController@view')->name('report.view');
        Route::get('reportes/create/{id}', 'ReportController@create')->name('report.create');
        Route::post('reportes/create', 'ReportController@store')->name('report.store');
        Route::get('reportes/editar/{reporte_id}', 'ReportController@edit')->name('report.edit');
        Route::post('reportes/editar/{reporte_id}', 'ReportController@store')->name('report.update');
        Route::get('reportes/eliminar/{reporte_id}', 'ReportController@delete')->name('report.delete');

        //Auditoría
        Route::get('auditoria', 'AuditController@index')->name('audit');
        Route::get('auditoria/ver_detalles/{id}', 'AuditController@view')->name('audit.view');

        //API
        Route::view('/api', 'backend.api.index')->name('api');
        Route::view('/api/token', 'backend.api.token')->name('api.token');
        Route::post('/api/token', 'ApiController@updateToken')->name('api.token.update');
        Route::view('/api/tramites_recurso', 'backend.api.tramites_recurso')->name('api.tramites_recurso');
        Route::view('/api/tramites_obtener', 'backend.api.tramites_obtener')->name('api.tramites_obtener');
        Route::view('/api/tramites_listar', 'backend.api.tramites_listar')->name('api.tramites_listar');
        Route::view('/api/tramites_listarporproceso', 'backend.api.tramites_listarporproceso')->name('api.tramites_listarporproceso');
        Route::view('/api/procesos_recurso', 'backend.api.procesos_recurso')->name('api.procesos_recurso');
        Route::view('/api/procesos_obtener', 'backend.api.procesos_obtener')->name('api.procesos_obtener');
        Route::view('/api/procesos_listar', 'backend.api.procesos_listar')->name('api.procesos_listar');
        Route::get('/api/procesos_disponibles', 'ApiController@procesos_disponibles')->name('api.procesos_disponibles');
        Route::get('/api/notificar/{tramite_id}', 'ApiController@notificar')->name('api.notificar');

        //Configuración
        Route::get('/configuracion', 'ConfigurationController@mySite')->name('configuration.my_site');
        Route::post('/configuracion', 'ConfigurationController@saveMySite')->name('configuration.my_site.save');
        Route::get('/configuracion/plantilla_seleccion/{plantilla_id?}', 'ConfigurationController@templates')->name('configuration.template');
        Route::post('/configuracion/plantilla_seleccion', 'ConfigurationController@storeTemplate')->name('configuration.template.store');
        Route::get('/configuracion/plantillas', 'ConfigurationController@addTemplates')->name('configuration.template.add');
        Route::get('/configuracion/plantilla_eliminar/{plantilla_id}', 'ConfigurationController@deleteTemplate')->name('configuration.template.delete');
        Route::get('/configuracion/modelador/{conector_id?}', 'ConfigurationController@modeler')->name('configuration.modeler');


        Route::get('/configuracion/backend_usuarios', 'ConfigurationController@backendUsers')->name('configuration.backend_users');
        Route::get('/configuracion/backend_usuario_editar', 'ConfigurationController@addBackendUsers')->name('configuration.backend_users.add');
        Route::post('/configuracion/backend_usuario_editar', 'ConfigurationController@storeBackendUsers')->name('configuration.backend_users.store');
        Route::get('/configuracion/backend_usuario_editar/{id}', 'ConfigurationController@editBackendUsers')->name('configuration.backend_users.edit');
        Route::put('/configuracion/backend_usuario_editar/{id}', 'ConfigurationController@updateBackendUsers')->name('configuration.backend_users.update');
        Route::delete('/configuracion/backend_usuario_editar/{id}', 'ConfigurationController@deleteBackendUsers')->name('configuration.backend_users.delete');

        Route::get('/configuracion/usuarios', 'ConfigurationController@frontendUsers')->name('configuration.frontend_users');
        Route::get('/configuracion/usuario_editar', 'ConfigurationController@addFrontendUsers')->name('configuration.frontend_users.add');
        Route::post('/configuracion/usuario_editar', 'ConfigurationController@storeFrontendUsers')->name('configuration.frontend_users.store');
        Route::get('/configuracion/usuario_editar/{id}', 'ConfigurationController@editFrontendUsers')->name('configuration.frontend_users.edit');
        Route::put('/configuracion/usuario_editar/{id}', 'ConfigurationController@updateFrontendUsers')->name('configuration.frontend_users.update');
        Route::delete('/configuracion/usuario_editar/{id}', 'ConfigurationController@deleteFrontendUsers')->name('configuration.frontend_users.delete');

        Route::get('/configuracion/grupos_usuarios', 'ConfigurationController@groupUsers')->name('configuration.group_users');
        Route::get('/configuracion/grupo_usuarios_editar', 'ConfigurationController@addGroupUsers')->name('configuration.group_users.add');
        Route::get('/configuracion/grupo_usuarios_editar/{id}', 'ConfigurationController@editGroupUsers')->name('configuration.group_users.edit');
        Route::post('/configuracion/grupo_usuarios_editar', 'ConfigurationController@storeGroupUsers')->name('configuration.group_users.store');
        Route::put('/configuracion/grupo_usuarios_editar/{id}', 'ConfigurationController@updateGroupUsers')->name('configuration.group_users.update');
        Route::delete('/configuracion/grupo_usuarios_editar/{id}', 'ConfigurationController@deleteGroupUsers')->name('configuration.group_users.delete');
    });
});

Route::prefix('manager')->namespace('Manager')->name('manager.')->group(function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/home', 'HomeController@index')->name('home2');
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login')->name('login.submit');
    // Password reset link request routes...
    //Route::get('password/email', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.email');
    //Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    // Password reset routes...
    //Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset.get');
    //Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.reset.post');

    Route::middleware('auth:usuario_manager')->group(function () {
        Route::post('/uploader/logo', 'CategoryController@mySiteUploadLogo')->name('uploader.logo');
        Route::get('/salir', 'Auth\LoginController@logout')->name('logout');

        //Cuentas
        Route::get('/cuentas', 'AccountController@index')->name('account.index');
        Route::get('/cuentas/editar/{cuenta_id?}', 'AccountController@edit')->name('account.edit');
        Route::post('/cuentas/editar_form/{cuenta_id?}', 'AccountController@edit_form')->name('account.edit_form');
        Route::get('/cuentas/eliminar/{cuenta_id?}', 'AccountController@delete')->name('account.delete');

        //Usuarios Backend
        Route::get('/usuarios', 'UsersController@index')->name('users.index');
        Route::get('/usuarios/editar/{usuario_id?}', 'UsersController@edit')->name('users.edit');
        Route::post('/usuarios/editar_form/{usuario_id?}', 'UsersController@edit_form')->name('users.edit_form');
        Route::get('/usuarios/eliminar/{usuario_id?}', 'UsersController@delete')->name('users.delete');

        //Día Feriado
        Route::get('/diaferiado', 'HolidayController@index')->name('holiday.index');
        Route::get('/diaferiado/diasFeriados', 'HolidayController@diasFeriados')->name('holiday.diasFeriados');

        //Categorías
        Route::get('/categorias', 'CategoryController@index')->name('category.index');
        Route::get('/categorias/editar/{id?}', 'CategoryController@edit')->name('category.edit');
        Route::post('/categorias/editar_form/{id?}', 'CategoryController@edit_form')->name('category.edit_form');
        Route::get('/categorias/eliminar/{id?}', 'CategoryController@delete')->name('category.delete');

        //Estadisticas
        Route::get('/estadisticas', 'StatisticsController@index')->name('statistics.index');
        Route::get('/estadisticas/cuentas/{cuenta_id?}/{proceso_id?}', 'StatisticsController@accounts')->name('statistics.accounts');

        //Consultas
        Route::get('/tramites_expuestos', 'ProceduresExposedController@index')->name('procedures_exposed.index');
        Route::post('/tramites_expuestos/buscar_cuenta', 'ProceduresExposedController@searchAccount')->name('tramites_expuestos.search_account');


    });
});
