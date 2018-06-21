<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Proceso;
use App\Models\Tramite;
use App\Rules\Captcha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Illuminate\Support\Facades\URL;
use Cuenta;
use ZipArchive;

class StagesController extends Controller
{
    public function run(Request $request, $etapa_id, $secuencia = 0)
    {
        $iframe = $request->input('iframe');
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

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
            foreach ($formulario->Campos as $c) {
                // Validamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                if ($c->isEditableWithCurrentPOST($request, $etapa_id)) {
                    $validate = $c->formValidate($request, $etapa->id);
                    if (!empty($validate[0]) && !empty($validate[1])) {
                        $validations[$validate[0]] = $validate[1];
                    }
                }
                if ($c->tipo == 'recaptcha') {
                    $validations['g-recaptcha-response'] = ['required', new Captcha];
                }
            }

            $request->validate($validations);

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
            echo 'Servicio no tiene permisos para descargar.';
            exit;
        }

        if (!Auth::user()->registrado) {
            echo 'Usuario no tiene permisos para descargar.';
            exit;
        }
        $tramites = $request->input('tramites');
        $opcionesDescarga = $request->input('opcionesDescarga');
        $tramites = explode(",", $tramites);
        $ruta_documentos = 'uploads/documentos/';
        $ruta_generados = 'uploads/datos/';
        $ruta_tmp = 'uploads/tmp/';
        $fecha = new \DateTime();
        $fecha = date_format($fecha, "Y-m-d");

        $tipoDocumento = "";
        switch ($opcionesDescarga) {
            case 'documento':
                $tipoDocumento = 'documento';
                break;
            case 'dato':
                $tipoDocumento = 'dato';
                break;
        }

        // Set Header
        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );

        // Recorriendo los trámites
        $zip = new ZipArchive;
        foreach ($tramites as $t) {

            if (empty($tipoDocumento)) {
                $files = Doctrine::getTable('File')->findByTramiteId($t);
            } else {
                $files = Doctrine::getTable('File')->findByTramiteIdAndTipo($t, $tipoDocumento);
            }
            if (count($files) > 0) {
                // Recorriendo los archivos
                foreach ($files as $f) {
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $participado = $tr->usuarioHaParticipado(Auth::user()->id);
                    if (!$participado) {
                        echo 'Usuario no ha participado en el trámite.';
                        exit;
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
                    if ($f->tipo == 'documento' && !empty($nombre_documento)) {
                        $path = $ruta_documentos . $f->filename;
                        $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                        $tramite_nro = str_replace(" ", "", $tramite_nro);
                        $nombre_archivo = pathinfo($path, PATHINFO_FILENAME);
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $nombre = $fecha . "_" . $t . "_" . $tramite_nro;
                        $new_file = $ruta_tmp . $nombre_documento . "." . $nombre_archivo . "." . $tramite_nro . "." . $ext;
                        copy($path, $new_file);
                        //$zipName=
                        $zip->open(public_path($ruta_tmp . $nombre) . '.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
                        $zip->addFile($new_file);
                        $zip->close();
                        //Eliminación del archivo para no ocupar espacio en disco
                        unlink($new_file);
                    } elseif ($f->tipo == 'dato' && !empty($nombre_documento)) {
                        $path = $ruta_generados . $f->filename;
                        $zip->addFile($path);
                    }
                }
                if (count($tramites) > 1) {
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $tramite_nro = '';
                    foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                        if ($tra_nro->nombre == 'tramite_ref') {
                            $tramite_nro = $tra_nro->valor;
                        }
                    }
                    $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                    $nombre = $fecha . "_" . $t . "_" . $tramite_nro;
                    //creando un zip por cada trámite
                    //$this->zip->archive($ruta_tmp . $nombre . '.zip');
                    $zip->open($ruta_tmp . $nombre . '.zip', ZipArchive::CREATE);
                    // Close ZipArchive
                    $zip->close();
                }
            }
        }
        if (count($tramites) > 1) {
            foreach ($tramites as $t) {
                $tr = Doctrine::getTable('Tramite')->find($t);
                $tramite_nro = '';
                foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                    if ($tra_nro->nombre == 'tramite_ref') {
                        $tramite_nro = $tra_nro->valor;
                    }
                }
                $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                $nombre = $fecha . "_" . $t . "_" . $tramite_nro;
                $this->zip->read_file($ruta_tmp . $nombre . '.zip');
            }

            //Eliminando los archivos antes de descargar
            foreach ($tramites as $t) {
                $tr = Doctrine::getTable('Tramite')->find($t);
                $tramite_nro = '';
                foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                    if ($tra_nro->nombre == 'tramite_ref') {
                        $tramite_nro = $tra_nro->valor;
                    }
                }
                $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                $nombre = $fecha . "_" . $t . "_" . $tramite_nro;
                unlink($ruta_tmp . $nombre . '.zip');
            }

            $this->zip->download('tramites.zip');
        } else {
            $tr = Doctrine::getTable('Tramite')->find($tramites);
            $tramite_nro = '';
            foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                if ($tra_nro->nombre == 'tramite_ref') {
                    $tramite_nro = $tra_nro->valor;
                }
            }
            $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
            $nombre = str_replace(' ', '', $fecha . "_" . $t . "_" . $tramite_nro);


            // Create Download Response
            if (file_exists(public_path($ruta_tmp . $nombre . ".zip"))) {
                return response()->download(public_path($ruta_tmp . $nombre . '.zip'), "{$nombre}.zip", $headers);
            }
        }
    }

}
