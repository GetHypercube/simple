<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Proceso;
use App\Models\Tramite;
use App\Models\Job;
use App\Models\File;
use App\Models\Campo;
use App\Rules\Captcha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Illuminate\Support\Facades\URL;
use Cuenta;
use ZipArchive;
use App\Jobs\IndexStages;
use App\Jobs\FilesDownload;
use Carbon\Carbon;
use Doctrine_Query;
use App\Models\DatoSeguimiento;


class StagesController extends Controller
{
    public function run(Request $request, $etapa_id, $secuencia = 0)
    {
        $iframe = $request->input('iframe');
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        $data = \Cuenta::configSegunDominio();
        $data['num_pasos'] = $etapa === false ? 0 : self::num_pasos($etapa->Tarea->id);

        if (!$etapa) {
            return abort(404);
        }
        if ($etapa->usuario_id != Auth::user()->id) {
            if (!Auth::user()->registrado) {
                return redirect()->route('home');
            }
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        if ($etapa->vencida()) {
            echo 'Esta etapa se encuentra vencida';
            exit;
        }

        $qs = $request->getQueryString();
        $paso = $etapa->getPasoEjecutable($secuencia);
        $pasosEjecutables = $etapa->getPasosEjecutables();
        Log::info("Ejecutando paso: " . $paso);
        if (!$paso) {
            Log::info("Entra en no paso: ");
            return redirect('etapas/ejecutar_fin/' . $etapa->id . ($qs ? '?' . $qs : ''));
        } else if (($etapa->Tarea->final || !$etapa->Tarea->paso_confirmacion) && $paso->getReadonly() && end($pasosEjecutables) == $paso) { // No se requiere mas input
            $etapa->iniciarPaso($paso);
            $etapa->finalizarPaso($paso);
            $etapa->avanzar();
            //Job para indexar contenido cada vez que se avanza de etapa
            $this->dispatch(new IndexStages($etapa->Tramite->id));
            return redirect('etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables()) - 1));
        } else {
            $etapa->iniciarPaso($paso);

            $data['secuencia'] = $secuencia;
            $data['etapa'] = $etapa;
            $data['paso'] = $paso;
            $data['qs'] = $request->getQueryString();

            $data['sidebar'] = Auth::user()->registrado ? 'inbox' : 'disponibles';
            $data['title'] = $etapa->Tarea->nombre;
            $template = $request->has('iframe') ? 'template_iframe' : 'template';

            return view('stages.run', $data);
        }
    }

    public function num_pasos($tarea_id)
    {
        Log::debug('$etapa->Tarea->id [' . $tarea_id . ']');

        $stmn = Doctrine_Manager::getInstance()->connection();
        $sql_pasos = "SELECT COUNT(*) AS total FROM paso WHERE tarea_id=" . $tarea_id;
        $result = $stmn->prepare($sql_pasos);
        $result->execute();
        $num_pasos = $result->fetchAll();
        Log::debug('$num_pasos [' . $num_pasos[0][0] . ']');

        return $num_pasos[0][0];
    }

    public function inbox(Request $request)
    {
        $buscar = $request->input('buscar');
        $orderby = $request->has('orderby') ? $request->input('orderby') : 'updated_at';
        $direction = $request->has('direction') ? $request->input('direction') : 'desc';

        $matches = "";
        $rowetapas = "";
        $resultotal = "";

        if ($buscar) {
            $result = Tramite::search($buscar)->get();
            if (!$result->isEmpty()) {
                $resultotal = "true";
            } else {
                $resultotal = "false";
            }
        }

        if ($resultotal == "true") {
            $matches = $result->groupBy('id')->keys()->toArray();
            $rowetapas = Doctrine::getTable('Etapa')->findPendientes(Auth::user()->id, \Cuenta::cuentaSegunDominio(), $orderby, $direction, $matches, $buscar);
        } else {
            $rowetapas = Doctrine::getTable('Etapa')->findPendientes(Auth::user()->id, \Cuenta::cuentaSegunDominio(), $orderby, $direction, "0", $buscar);
        }

        $data = \Cuenta::configSegunDominio();

        $data['etapas'] = $rowetapas;
        $data['buscar'] = $buscar;
        $data['orderby'] = $orderby;
        $data['direction'] = $direction;
        $data['sidebar'] = 'inbox';
        $data['title'] = 'Bandeja de Entrada';

        return view('stages.inbox', $data);
    }

    public function sinasignar(Request $request, $offset = 0)
    {

        if (!Auth::user()->registrado) {
            $request->session()->put('claveunica_redirect', URL::current());
            return redirect()->route('login.claveunica');
        }

        //$this->load->library('pagination');
        $buscar = $request->input('query');

        $matches = "";
        $rowetapas = "";
        $resultotal = false;
        $contador = "0";
        $perpage = 50;
        $page = $request->input('page', 1); // Get the ?page=1 from the url
        $offset = ($page * $perpage) - $perpage;


        if ($buscar) {
            $result = Proceso::search($buscar)->get();
            if (!$result->isEmpty()) {
                $resultotal = true;
            } else {
                $resultotal = false;
            }
        }

        if ($resultotal == true) {
            $matches = $result->groupBy('id')->keys()->toArray();
            $contador = Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio(), $matches, $buscar, 0, $perpage)->count();
            $rowetapas = Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio(), $matches, $buscar, 0, $perpage);
            error_log("true" . " cantidad " . $contador);

        } else {
            $contador = Doctrine::getTable('Etapa')->findAllSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
            $rowetapas = Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio(), "0", $buscar, $offset, $perpage);
            error_log("false" . " cantidad " . $contador);
        }

        $config['base_url'] = url('etapas/sinasignar');
        $config['total_rows'] = $contador;
        $config['per_page'] = $perpage;
        $config['full_tag_open'] = '<div class="pagination pagination-centered"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['page_query_string'] = false;
        $config['query_string_segment'] = 'offset';
        $config['first_link'] = 'Primero';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Último';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '»';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '«';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $data = \Cuenta::configSegunDominio();
        // $data['etapas'] = Doctrine::getTable('Etapa')->findSinAsignar(Auth::self::user()->id, Cuenta::cuentaSegunDominio());
        $data['etapas'] = new LengthAwarePaginator(
            $rowetapas, // Only grab the items we need
            $contador, // Total items
            $perpage, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // We need this so we can keep all old query parameters from the url
        );
        $data['query'] = $buscar;
        $data['sidebar'] = 'sinasignar';
        $data['content'] = view('stages.unassigned', $data);
        $data['title'] = 'Sin Asignar';

        return view('layouts.procedure', $data);
    }

    public function ejecutar_form(Request $request, $etapa_id, $secuencia)
    {
        Log::info('ejecutar_form ($etapa_id [' . $etapa_id . '], $secuencia [' . $secuencia . '])');

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        if ($etapa->vencida()) {
            echo 'Esta etapa se encuentra vencida';
            exit;
        }

        $paso = $etapa->getPasoEjecutable($secuencia);
        $formulario = $paso->Formulario;
        $modo = $paso->modo;
        $respuesta = new \stdClass();
        $validations = [];

        if ($modo == 'edicion') {
            $campos_nombre_etiqueta = [];
            foreach ($formulario->Campos as $c) {
                // Validamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                if ($c->isEditableWithCurrentPOST($request, $etapa_id)) {
                    $validate = $c->formValidate($request, $etapa->id);
                    if (!empty($validate[0]) && !empty($validate[1])) {
                        $validations[$validate[0]] = $validate[1];
                        $campos_nombre_etiqueta[$validate[0]] = "\"$c->etiqueta\"";
                    }
                }
                if ($c->tipo == 'recaptcha') {
                    $validations['g-recaptcha-response'] = ['required', new Captcha];
                }
            }

            $request->validate( $validations, [], $campos_nombre_etiqueta );

            // Almacenamos los campos
            foreach ($formulario->Campos as $c) {
                // Almacenamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)

                if ($c->isEditableWithCurrentPOST($request, $etapa_id)) {
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($c->nombre, $etapa->id);
                    if (!$dato)
                        $dato = new \DatoSeguimiento();
                    $dato->nombre = $c->nombre;
                    $dato->valor = $request->input($c->nombre) === false ? '' : $request->input($c->nombre);

                    if (!is_object($dato->valor) && !is_array($dato->valor)) {
                        if (preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)) {
                            $dato->valor = preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i", "$3-$2-$1", $dato->valor);
                        }
                    }

                    $dato->etapa_id = $etapa->id;
                    $dato->save();
                }
            }
            $etapa->save();

            $etapa->finalizarPaso($paso);

            $respuesta->validacion = TRUE;

            $qs = $request->getQueryString();
            $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
            $pasosEjecutables = $etapa->getPasosEjecutables();
            if (!$prox_paso) {
                $respuesta->redirect = '/etapas/ejecutar_fin/' . $etapa_id . ($qs ? '?' . $qs : '');
            } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($pasosEjecutables) == $prox_paso) { //Cerrado automatico
                $etapa->iniciarPaso($prox_paso);
                $etapa->finalizarPaso($prox_paso);
                $etapa->avanzar();
                //Job para indexar contenido cada vez que se avanza de etapa
                $this->dispatch(new IndexStages($etapa->Tramite->id));
                $respuesta->redirect = '/etapas/ver/' . $etapa->id . '/' . (count($pasosEjecutables) - 1);
            } else {
                $respuesta->redirect = '/etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1) . ($qs ? '?' . $qs : '');
            }

        } else if ($modo == 'visualizacion') {
            $respuesta->validacion = TRUE;

            $qs = $request->getQueryString();
            $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
            $pasosEjecutables = $etapa->getPasosEjecutables();
            if (!$prox_paso) {
                $respuesta->redirect = '/etapas/ejecutar_fin/' . $etapa_id . ($qs ? '?' . $qs : '');
            } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($pasosEjecutables) == $prox_paso) { //Cerrado automatico
                $etapa->iniciarPaso($prox_paso);
                $etapa->finalizarPaso($prox_paso);
                $etapa->avanzar();
                //Job para indexar contenido cada vez que se avanza de etapa
                $this->dispatch(new IndexStages($etapa->Tramite->id));
                $respuesta->redirect = '/etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables()) - 1);
            } else {
                $respuesta->redirect = '/etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1) . ($qs ? '?' . $qs : '');
            }
        }

        return response()->json([
            'validacion' => true,
            'redirect' => $respuesta->redirect
        ]);
    }

    public function asignar($etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id) {
            echo 'Etapa ya fue asignada.';
            exit;
        }

        if (!$etapa->canUsuarioAsignarsela(Auth::user()->id)) {
            echo 'Usuario no puede asignarse esta etapa.';
            exit;
        }

        $etapa->asignar(Auth::user()->id);

        return redirect('etapas/inbox');
    }

    public function ejecutar_fin(Request $request, $etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        //if ($etapa->Tarea->asignacion!='manual') {
        //    $etapa->Tramite->avanzarEtapa();
        //    redirect();
        //    exit;
        //}
        $data = \Cuenta::configSegunDominio();
        $data['etapa'] = $etapa;
        $data['tareas_proximas'] = $etapa->getTareasProximas();
        $data['qs'] = $request->getQueryString();

        $data['sidebar'] = Auth::user()->registrado ? 'inbox' : 'disponibles';
        $data['title'] = $etapa->Tarea->nombre;
        $template = $request->input('iframe') ? 'template_iframe' : 'template_newhome';

        return view('stages.ejecutar_fin', $data);
    }

    public function ejecutar_fin_form(Request $request, $etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        // $etapa->avanzar($request->input('usuarios_a_asignar'));
        try {
            $agenda = new AppointmentController();
            $appointments = $agenda->obtener_citas_de_tramite($etapa_id);
            if (isset($appointments) && is_array($appointments) && (count($appointments) >= 1)) {
                $json = '{"ids":[';
                $i = 0;
                foreach ($appointments as $item) {
                    if ($i == 0) {
                        $json = $json . '"' . $item . '"';
                    } else {
                        $json = $json . ',"' . $item . '"';
                    }
                    $i++;
                }
                $json = $json . ']}';
                $agenda->confirmar_citas_grupo($json);
                $etapa->avanzar($request->input('usuarios_a_asignar'));
            } else {
                $etapa->avanzar($request->input('usuarios_a_asignar'));
            }

            $proximas = $etapa->getTareasProximas();

            Log::info("###Id etapa despues de avanzar: " . $etapa->id);
            Log::info("###Id tarea despues de avanzar: " . $etapa->tarea_id);
            $cola = new \ColaContinuarTramite();
            $tareas_encoladas = $cola->findTareasEncoladas($etapa->tramite_id);
            if ($proximas->estado === 'pendiente') {
                Log::debug("pendiente");
                foreach ($proximas->tareas as $tarea) {
                    Log::debug('Ejecutando continuar de etapa ' . $tarea->id . " en trámite " . $etapa->tramite_id);
                    $etapa->ejecutarColaContinuarTarea($tarea->id, $tareas_encoladas);
                }
            }
        } catch (Exception $err) {
            Log::error($err->getMessage());
        }

        //Job para indexar contenido cada vez que se avanza de etapa
        $this->dispatch(new IndexStages($etapa->Tramite->id));

        if ($request->input('iframe')) {
            return response()->json([
                'validacion' => true,
                'redirect' => route('stage.ejecutar_exito')
            ]);
        }

        return response()->json([
            'validacion' => true,
            'redirect' => route('home'),
        ]);
    }

    //Pagina que indica que la etapa se completo con exito. Solamente la ven los que acceden mediante iframe.
    public function ejecutar_exito()
    {
        $data = \Cuenta::configSegunDominio();
        $data['title'] = 'Etapa completada con éxito';

        return view('backend.stages.ejecutar_exito', $data);
    }

    public function ver($etapa_id, $secuencia = 0)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if (Auth::user()->id != $etapa->usuario_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $paso = $etapa->getPasoEjecutable($secuencia);

        $data = \Cuenta::configSegunDominio();
        $data['etapa'] = $etapa;
        $data['paso'] = $paso;
        $data['secuencia'] = $secuencia;

        $data['sidebar'] = 'participados';
        $data['title'] = 'Historial - ' . $etapa->Tarea->nombre;
        //$data['content'] = 'etapas/ver';

        return view('stages.view', $data);
    }

    public function descargar($tramites)
    {
        $data['tramites'] = $tramites;
        return view('stages.download', $data);
    }

    public function descargar_form(Request $request)
    {
        if (!Cuenta::cuentaSegunDominio()->descarga_masiva) {
            $request->session()->flash('error', 'Servicio no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (!Auth::user()->registrado) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }
        $tramites = $request->input('tramites');
        $opcionesDescarga = $request->input('opcionesDescarga');
        $tramites = explode(",", $tramites);
        $ruta_documentos = 'uploads/documentos/';
        $ruta_generados = 'uploads/datos/';
        $ruta_tmp = 'uploads/tmp/';
        $fecha_obj = new \DateTime();
        $fecha = date_format($fecha_obj, "Y-m-d");
        $time_stamp = date_format($fecha_obj, "Y-m-d_His");

        $tipoDocumento = "";
        switch ($opcionesDescarga) {
            case 'documento':
                $tipoDocumento = ['documento'];
                break;
            case 'dato': // s3 son archivos subidos al igual que los dato
                $tipoDocumento = ['dato', 's3'];
                break;
        }

        // Recorriendo los trámites
        $zip_path_filename = public_path($ruta_tmp).'tramites_'.$time_stamp.'.zip';
        $files_list = ['documento' => [], 'dato'=> [], 's3' => []];
        $non_existant_files = [];
        $docs_total_space = 0;
        $s3_missing_file_info_ids = [];
        $cuenta = null;
        foreach ($tramites as $t) {
            if (empty($tipoDocumento)) {
                $files = Doctrine::getTable('File')->findByTramiteId($t);
            } else {
                $files = \Doctrine_Query::create()->from('File f')->where('f.tramite_id=?', $t)->andWhereIn('tipo', $tipoDocumento)->execute();
            }
            
            if (count($files) > 0) {
                // Recorriendo los archivos
                foreach ($files as $f) {
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $participado = $tr->usuarioHaParticipado(Auth::user()->id);
                    if (!$participado) {
                        $request->session()->flash('error', 'Usuario no ha participado en el trámite.');
                        return redirect()->back();
                    }
                    if( (is_null($cuenta)|| $cuenta === FALSE) && $tr !== FALSE){
                        $cuenta = $tr->Proceso->Cuenta;
                    }
                    $nombre_documento = $tr->id;
                    $tramite_nro = '';
                    foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                        if ($tra_nro->valor == $f->filename) {
                            $nombre_documento = $tra_nro->nombre;
                        }
                        if ($tra_nro->nombre == 'tramite_ref') {
                            $tramite_nro = $tra_nro->valor;
                        }
                    }

                    $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                    $tramite_nro = str_replace(" ", "", $tramite_nro);

                    if (empty($nombre_documento)){
                        continue;
                    }
                    if ($f->tipo == 'documento') {
                        $ruta_base = $ruta_documentos;
                    } elseif ($f->tipo == 'dato') {
                        $ruta_base = $ruta_generados;
                    }else if($f->tipo == 's3'){
                        $ruta_base = 's3';
                    }
                    
                    $path = $ruta_base . $f->filename;
                    $proceso_nombre = str_replace(' ', '_', $tr->Proceso->nombre);
                    $proceso_nombre = \App\Helpers\FileS3Uploader::filenameToAscii($proceso_nombre);
                    $directory = "{$proceso_nombre}/{$tr->id}/{$f->tipo}";
                    if( $f->tipo == 's3' ){
                        $extra = $f->extra;
                        if( ! $extra ){
                            $s3_missing_file_info_ids[] = $f->id;
                        }else{
                            $docs_total_space += $extra->s3_file_size;
                            $files_list[$f->tipo][] = ['file_name' => $f->filename,
                                                       'bucket' => $extra->s3_bucket,
                                                       'file_path' => $extra->s3_filepath,
                                                       'tramite' => $tr->Proceso->nombre,
                                                       'proceso' => $directory,
                                                       'tramite_id' => $tr->id,
                                                       'directory' => $directory];
                        }
                    }else if(file_exists($path)){
                        $docs_total_space += filesize($path);
                        $files_list[$f->tipo][] = [
                            'ori_path' => $path,
                            'nice_name' => $f->filename, 
                            'directory' => $directory, 
                            'tramite_id' => $tr->id,
                            'tramite' => $tr->Proceso->nombre
                        ];
                    }else{
                        $non_existant_files[] = $path;
                    }
                }
            }
        }
        
        $max_space_before_email_link = env('DOWNLOADS_FILE_MAX_SIZE', 500) * 1024 * 1024;
        if( ( array_key_exists('s3', $files_list) && count($files_list['s3']) > 0 ) 
                || $docs_total_space > $max_space_before_email_link ) {
            $running_jobs = Job::where('user_id', Auth::user()->id)
                               ->whereIn('status', [Job::$running, Job::$created])
                               ->where('user_type', Auth::user()->user_type)
                               ->count();
            if($running_jobs >= env('DOWNLOADS_MAX_JOBS_PER_USER', 1)){
                $request->session()->flash('error', 
                    "Ya tiene trabajos en ejecuci&oacute;n pendientes, por favor espere a que este termine.");
                return redirect()->back();
            }
            $http_host = request()->getSchemeAndHttpHost();
            
            if(strpos(url()->current(), 'https://') === 0){
                $http_host = str_replace('http://', 'https://', $http_host);
            }
            
            $email_to = Auth::user()->email;
            $validator = \Validator::make(
                [ 'email' => $email_to ], [ 'email' => 'required|email' ]
            );
            if ($validator->fails()) {
                if( empty( $email_to ) ){
                    $msg = 'No posee una direcci&oacute;n de correo electr&oacute;nico configurada.';
                }else{
                    $msg = 'Su direcci&oacute;n de correo electr&oacute;nico: '.$email_to.' no es v&aacute;lida.';
                }
                $request->session()->flash('error', $msg);
                return redirect()->back();
            }
            $name_to = Auth::user()->nombres;
            $email_subject = 'Enlace para descargar archivos.';
            $this->dispatch(new FilesDownload(Auth::user()->id, Auth::user()->user_type, $files_list, $email_to, 
                                              $name_to, $email_subject, $http_host, $cuenta));
            
            $request->session()->flash('success', "Se enviar&aacute; un enlace para la descarga de los documentos una vez est&eacute; listo a la direcci&oacute;n: {$email_to}");
            return redirect()->back();
        }
        
        $files_to_compress_not_empty = false;
        foreach($files_list as $tipo => $f_array ){
            if( count($files_list[$tipo]) > 0 ){
                $files_to_compress_not_empty = true;
                break;
            }
        }

        if($files_to_compress_not_empty){
            $zip = new ZipArchive;
            $opened = $zip->open($zip_path_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach($files_list as $tipo => $f_array ){
                if( count($files_list[$tipo]) === 0){
                    continue;
                }
                foreach($f_array as $file){
                    $dir = "{$file['tramite']}/{$file['tramite_id']}/{$tipo}/";
                    if($zip->locateName($dir) === FALSE){
                        $zip->addEmptyDir($dir);
                    }
                    $zip->addFile(public_path($file['ori_path']), $dir.$file['nice_name']);
                    $zip->setCompressionName($dir.$file['nice_name'], ZipArchive::CM_STORE);
                }
            }
            $zip->close();
            if(count($non_existant_files)> 0)
                $request->session()->flash('warning', 'No se pudieron encontrar todos los archivos requeridos para descargar.');
            // archivo $zip tiene al menos 1 archivo
            return response()
                ->download($zip_path_filename, 'tramites_'.$fecha.'.zip', ['Content-Type' => 'application/octet-stream'])
                ->deleteFileAfterSend(true);
        }else{
            $request->session()->flash('error', 'No se encontraron archivos para descargar.');
            return redirect()->back();
        }
    }

    public function descargar_archivo(Request $request, $user_id, $job_id, $file_name){
        if (!Cuenta::cuentaSegunDominio()->descarga_masiva) {
            $request->session()->flash('error', 'Servicio no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (!Auth::user()->registrado) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }
        
        if (Auth::user()->id != $user_id) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }

        // validar que user_id y job_id sean enteros

        $job_info = Job::where('user_id', Auth::user()->id)
                        ->where('id', $job_id)
                        ->where('filename', $file_name)->first();
        
        $full_path = $job_info->filepath.DIRECTORY_SEPARATOR.$job_info->filename;
        if(file_exists($full_path)){
            $job_info->downloads += 1;
            $job_info->save();
            
            $time_stamp = Carbon::now()->format("Y-m-d_His");
            return response()
                ->download($full_path, 'tramites_'.$time_stamp.'.zip', ['Content-Type' => 'application/octet-stream'])
                ->deleteFileAfterSend(true);
        }else{
            abort(404);
        }
    }

    public function estados($tramite_id)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);
        $datos = $tramite->getValorDatoSeguimientoAll();
        foreach ($datos as $dato) {
            if ($dato->nombre == 'historial_estados') {
                $historial = $dato->valor;
            }
        }
        $data['historial'] = $historial;
        return view('stages.estados',$data);
    }

    public function saveForm(Request $request,$etapa_id){

        //Se guardan los datos del formulario en la etapa correspondiente
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $input = $request->all();
        $protected_vars = array('_token','_method','paso','btn_async');
        foreach($input as $key => $value){
            if($key=='paso')
                $paso = $etapa->getPasoEjecutable($value);       
            if($key=='btn_async'){
                $campo = Doctrine_Query::create()
                    ->from("Campo")
                    ->where("id = ?", $value)
                    ->fetchOne();
            } 
            if(!in_array($key,$protected_vars) && !is_null($value)){
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa_id);
                if (!$dato)
                    $dato = new \DatoSeguimiento();
                $dato->nombre = $key;
                $dato->valor = $value;

                if (!is_object($dato->valor) && !is_array($dato->valor)) {
                    if (preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)) {
                        $dato->valor = preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i", "$3-$2-$1", $dato->valor);
                    }
                }
                $dato->etapa_id = $etapa_id;
                $dato->save();
            }
        }

        //se ejecutan acciones durante el paso
        $etapa->ejecutarPaso($paso,$campo);

        //se genera respuesta con los datos que la etapa tiene hasta el momento
        $datos = DatoSeguimiento::where('etapa_id',$etapa->id)
                ->select('nombre','valor')
                ->get();
        $response = $datos->toArray();
        
        //se genera arreglo con los datos procesados en la etapa
        $array_datos = [];
        foreach ($datos as $dato) {
            $array_datos[$dato->nombre] = $dato->valor;
        }

        //se obtienen todos los campos del formulario que está consultando
        $campos = Campo::where('formulario_id',$campo->Formulario->id)->get();
        
        //se recorren los campos del formulario para verificar que existan coincidencias con los datos obtenidos en la etapa
        foreach($campos as $campo){
            //en caso que no exista valor por defecto, continua el recorrido sin agregar datos al arreglo
            if(empty($campo->valor_default)){
                continue;
            }
            $var = str_replace('@@', '', $campo->valor_default);
            //si existe el campo valor por defecto dentro de los datos de la etapa los agrega a la respuesta para setear los datos
            //se setea como valor por defecto(para los que tienen) el valor del dato para el campo del formulario
            if(array_key_exists($var, $array_datos)){
               $response[] = ['nombre'=>$campo->nombre, 'valor' =>$array_datos[$var] ];
            }
        }

        return response()->json($response);
    }
}
