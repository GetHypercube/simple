<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Doctrine_Query;
use Doctrine_Core;

class TracingController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function index()
    {
        $data ['procesos'] = Doctrine_Query::create()->from('Proceso p, p.Cuenta c')
            ->where('p.activo=1 AND p.estado = "public" AND c.id = ?', Auth::user()->cuenta_id)
            ->orderBy('p.nombre asc')->execute();

        return view('backend.tracing.index', $data);
    }

    /**
     * @param Request $request
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function indexProcess(Request $request, $proceso_id)
    {
        Log::info("Detalle de seguimiento para proceso id: " . $proceso_id);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        $procesos_archivados = $proceso->findProcesosArchivados($proceso->root);
        $id_archivados = array();
        Log::info("Buscando procesos relacionados archivados");

        foreach ($procesos_archivados as $proc_arch) {
            $id_archivados[] = $proc_arch['id'];
        }

        Log::info("Procesos relacionados archivados: " . $this->varDump($id_archivados));

        if (Auth::user()->cuenta_id != $proceso->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if (!is_null(Auth::user()->procesos) && !in_array($proceso_id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para el seguimiento del tramite';
            exit ();
        }

        $query = $request->input('query');
        $order = $request->input('order') ? $request->input('order') : 'updated_at';
        $direction = $request->input('direction') ? $request->input('direction') : 'desc';
        $created_at_desde = $request->input('created_at_desde');
        $created_at_hasta = $request->input('created_at_hasta');
        $updated_at_desde = $request->input('updated_at_desde');
        $updated_at_hasta = $request->input('updated_at_hasta');
        $pendiente = $request->has('pendiente') && is_numeric($request->input('pendiente')) ? $request->input('pendiente') : -1;
        $page = $request->input('page', 1); // Get the ?page=1 from the url
        $per_page = 50;
        $busqueda_avanzada = $request->input('busqueda_avanzada');
        $offset = ($page * $per_page) - $per_page;

        Log::info("Creando query");

        $doctrine_query = Doctrine_Query::create()->from('Tramite t, t.Proceso p, t.Etapas e, e.DatosSeguimiento d')
            ->where('p.activo=1')
            ->andWhereIn('p.root', $id_archivados)
            ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')-> // Mostramos solo los que se han avanzado o tienen datos
            groupBy('t.id')->orderBy($order . ' ' . $direction)->limit($per_page)->offset($offset);

        if ($created_at_desde)
            $doctrine_query->andWhere('created_at >= ?', array(
                date('Y-m-d', strtotime($created_at_desde))
            ));
        if ($created_at_hasta)
            $doctrine_query->andWhere('created_at <= ?', array(
                date('Y-m-d', strtotime($created_at_hasta))
            ));
        if ($updated_at_desde)
            $doctrine_query->andWhere('updated_at >= ?', array(
                date('Y-m-d', strtotime($updated_at_desde))
            ));
        if ($updated_at_hasta)
            $doctrine_query->andWhere('updated_at <= ?', array(
                date('Y-m-d', strtotime($updated_at_hasta))
            ));
        if ($pendiente != -1)
            $doctrine_query->andWhere('pendiente = ?', array(
                $pendiente
            ));

        if ($query) {
            $this->load->library('sphinxclient');
            $this->sphinxclient->setServer($this->config->item('sphinx_host'), $this->config->item('sphinx_port'));
            $this->sphinxclient->setFilter('proceso_id', array(
                $proceso_id
            ));
            $result = $this->sphinxclient->query(json_encode($query), 'tramites');
            if ($result ['total'] > 0) {
                $matches = array_keys($result ['matches']);
                $doctrine_query->whereIn('t.id', $matches);
            } else {
                $doctrine_query->where('0');
            }
        }

        $tramites = $doctrine_query->execute();
        $ntramites = $doctrine_query->count();

        $tramites = new LengthAwarePaginator(
            $tramites, // Only grab the items we need
            $ntramites, // Total items
            $per_page, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // We need this so we can keep all old query parameters from the url
        );

        /*$this->load->library('pagination');
        $this->pagination->initialize(array(
            'base_url' => site_url('backend/seguimiento/index_proceso/' . $proceso_id . '?order=' . $order . '&direction=' . $direction . '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta),
            'total_rows' => $ntramites,
            'per_page' => $per_page
        ));*/

        $data ['query'] = $query;
        $data ['order'] = $order;
        $data ['direction'] = $direction;
        $data ['created_at_desde'] = $created_at_desde;
        $data ['created_at_hasta'] = $created_at_hasta;
        $data ['updated_at_desde'] = $updated_at_desde;
        $data ['updated_at_hasta'] = $updated_at_hasta;
        $data ['pendiente'] = $pendiente;
        $data ['busqueda_avanzada'] = $busqueda_avanzada;
        $data ['proceso'] = $proceso;
        $data ['tramites'] = $tramites;

        $data ['title'] = 'Seguimiento de ' . $proceso->nombre;

        return view('backend.tracing.index_process', $data);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajaxIdProcedure()
    {
        $max = Doctrine_Query::create()->select('MAX(id) as max')->from("Tramite")->fetchOne();
        $data ['max'] = $max->max;

        return view('backend.tracing.ajaxUpdateIdProcedure', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Doctrine_Manager_Exception
     */
    public function ajaxUpdateIdProcedure(Request $request)
    {
        $max = Doctrine_Query::create()->select('MAX(id) as max')->from("Tramite")->fetchOne();
        $max = $max->max + 1;

        $this->validate($request, [
            'id' => 'required|numeric|min:' . $max
        ]);

        $id = $request->input('id');

        $stmt = Doctrine_Manager::getInstance()->connection();
        $sql = "ALTER TABLE tramite AUTO_INCREMENT = " . $id . ";";
        $stmt->execute($sql);


        return redirect()->route('backend.tracing.index');
    }

    /**
     * @param $tramite_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_eliminar_tramite($tramite_id)
    {

        $tramite = Doctrine::getTable("Tramite")->find($tramite_id);
        $data['tramite'] = $tramite;

        return view('backend.tracing.ajax_auditar_eliminar_tramite', $data);
    }

    /**
     * @param Request $request
     * @param $tramite_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function borrar_tramite(Request $request, $tramite_id)
    {

        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);

        $request->validate(['descripcion' => 'required']);

        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (!is_null(Auth::user()->procesos) && !in_array($tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }
        $fecha = new \DateTime ();
        $proceso = $tramite->Proceso;
        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Trámite';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;


        // Detalles
        $tramite_array['proceso'] = $proceso->toArray(false);

        $tramite_array['tramite'] = $tramite->toArray(false);
        unset($tramite_array['tramite']['proceso_id']);

        $registro_auditoria->detalles = json_encode($tramite_array);
        $registro_auditoria->save();

        $tramite->delete();

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/index_proceso/' . $proceso->id)
        ]);
    }

    /**
     * @param $proceso_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function reset_proc_cont($proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        $proceso->proc_cont = 0;
        $proceso->save();

        return redirect($_SERVER['HTTP_REFERER']);
    }


    /**
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_limpiar_proceso($proceso_id)
    {

        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;

        return view('backend.tracing.ajax_auditar_limpiar_proceso', $data);

    }

    /**
     * @param Request $request
     * @param $proceso_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function borrar_proceso(Request $request, $proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);


        $request->validate(['descripcion' => 'required']);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if (!is_null(Auth::user()->procesos) && !in_array($proceso_id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para el seguimiento del tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }
        $fecha = new \DateTime ();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Todos los Trámites';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;


        // Detalles
        $proceso_array['proceso'] = $proceso->toArray(false);

        foreach ($proceso->Tramites as $tramite) {
            $tramite_array = $tramite->toArray(false);
            unset($tramite_array['proceso_id']);
            $proceso_array['tramites'][] = $tramite_array;

        }


        $registro_auditoria->detalles = json_encode($proceso_array);
        $registro_auditoria->save();

        $proceso->Tramites->delete();

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/index_proceso/' . $proceso_id)
        ]);

    }

    /**
     * @param $tramite_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function ver($tramite_id)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (!is_null(Auth::user()->procesos) && !in_array($tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver el tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $data ['tramite'] = $tramite;
        $data ['etapas'] = Doctrine_Query::create()->from('Etapa e, e.Tramite t')->where('t.id = ?', $tramite->id)->orderBy('id desc')->execute();

        $data ['title'] = 'Seguimiento - ' . $tramite->Proceso->nombre;

        return view('backend.tracing.view', $data);
    }

    /**
     * @param $tramite_id
     * @param $tarea_identificador
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function ajax_ver_etapas($tramite_id, $tarea_identificador)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $etapas = Doctrine_Query::create()->from('Etapa e, e.Tarea tar, e.Tramite t')->where('t.id = ? AND tar.identificador = ?', array(
            $tramite_id,
            $tarea_identificador
        ))->execute();

        $data ['etapas'] = $etapas;

        return view('backend.tracing.ajax_ver_etapas', $data);
    }

    /**
     * @param $etapa_id
     * @param int $secuencia
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ver_etapa($etapa_id, $secuencia = 0)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $paso = $etapa->getPasoEjecutable($secuencia);

        if (!is_null(Auth::user()->procesos) && !in_array($etapa->Tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver el tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $etapa->Tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $data ['etapa'] = $etapa;
        $data ['paso'] = $paso;
        $data ['secuencia'] = $secuencia;

        $data ['title'] = 'Seguimiento - ' . $etapa->Tarea->nombre;

        return view('backend.tracing.view_stage', $data);
    }

    /**
     * @param $etapa_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_retroceder_etapa($etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $data ['etapa'] = $etapa;

        return view('backend.tracing.ajax_auditar_retroceder_etapa', $data);
    }

    /**
     *
     * Vuelve a la/s etapa/s anterior/es
     * En caso de ser la última etapa(ya finalizada), vuelve a dejar el trámite en curso
     *
     * @param unknown $etapa_id
     */
    public function retroceder_etapa(Request $request, $etapa_id)
    {
        $request->validate(['descripcion' => 'required']);

        $fecha = new \DateTime ();

        $etapa = Doctrine::getTable("Etapa")->find($etapa_id);
        $tramite = Doctrine::getTable("Tramite")->find($etapa->tramite_id);
        if ($etapa->pendiente == 1) {
            // Tarea anterior de la actual, ordenada por las etapas
            $tareas_anteriores = Doctrine_Query::create()->select("c.tarea_id_origen as id, c.tipo, e.id as etapa_id, e.etapa_ancestro_split_id as origen_paralelo")->from("Conexion c, c.TareaOrigen to, to.Etapas e")->where("c.tarea_id_destino = ?", $etapa->tarea_id)->andWhere("e.tramite_id = ?", $tramite->id)->andWhere("e.id != ?", $etapa->id)->orderBy("e.id DESC")->fetchOne();

            if (count($tareas_anteriores) > 0) {
                // Eliminamos la etapa actual
                $id_etapa_actual = $etapa->id;
                $id_tarea_actual = $etapa->tarea_id;
                $etapa->delete();

                $tipo_conexion = $tareas_anteriores->tipo;

                // Si no es union, debe retroceder solo a la ultima etapa de la tarea anterior
                if ($tipo_conexion != 'union') {
                    $tareas_anteriores = array(
                        $tareas_anteriores
                    );
                } else {
                    $tareas_anteriores = Doctrine_Query::create()->select("c.tarea_id_origen as id, c.tipo, e.id as etapa_id")->from("Conexion c, c.TareaOrigen to, to.Etapas e")->where("c.tarea_id_destino = ?", $etapa->tarea_id)->andWhere("e.tramite_id = ?", $tramite->id)->andWhere("e.id != ?", $etapa->id)->andWhere("e.etapa_ancestro_split_id = ?", $tareas_anteriores->origen_paralelo)->orderBy("e.id DESC")->execute();
                }

                // Si es union va retroceder a todas las etapas de dicha union, sino tareas_anteriores tendra un solo elemento
                foreach ($tareas_anteriores as $tarea_anterior) {
                    if ($etapa_anterior = Doctrine::getTable("Etapa")->find($tarea_anterior->etapa_id)) {
                        // Auditoría de la etapa a la cual se regresa
                        $registro_auditoria = new \AuditoriaOperaciones ();
                        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
                        $registro_auditoria->motivo = $request->input('descripcion');
                        $registro_auditoria->operacion = 'Retroceso a Etapa';
                        $registro_auditoria->proceso = $etapa_anterior->Tramite->Proceso->nombre;
                        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

                        $usuario = Auth::user();
                        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';

                        /* Formatear detalles */
                        $etapa_array['proceso'] = $etapa_anterior->Tramite->Proceso->toArray(false);
                        $etapa_array ['tramite'] = $etapa_anterior->Tramite->toArray(false);

                        $etapa_array['etapa'] = $etapa_anterior->toArray(false);
                        unset ($etapa_array ['etapa']['tarea_id']);
                        unset ($etapa_array ['etapa']['tramite_id']);
                        unset ($etapa_array ['etapa']['usuario_id']);
                        unset ($etapa_array ['etapa']['etapa_ancestro_split_id']);

                        $etapa_array ['tarea'] = $etapa_anterior->Tarea->toArray(false);
                        $etapa_array ['usuario'] = $etapa_anterior->Usuario->toArray(false);
                        unset ($etapa_array ['usuario'] ['password']);
                        unset ($etapa_array['usuario']['salt']);

                        $etapa_array ['datos_seguimiento'] = Doctrine_Query::create()
                            ->from("DatoSeguimiento d")
                            ->where("d.etapa_id = ?", $etapa_anterior->id)
                            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                        $registro_auditoria->detalles = json_encode($etapa_array);
                        $registro_auditoria->save();
                    }

                    $etapas_otra_rama = array();
                    if ($tipo_conexion == 'paralelo' || $tipo_conexion == 'paralelo_evaluacion') {
                        // Select de otras ramas para evitar inconsistencias
                        $etapas_otra_rama = Doctrine_Query::create()->select("c.tarea_id_destino as id")->from("Conexion c, c.TareaDestino to, to.Etapas e")->where("c.tarea_id_origen = ?", $tarea_anterior->id)->andWhere("c.tarea_id_destino != ?", $id_tarea_actual)->andWhere("c.tarea_id_destino != c.tarea_id_origen")->andWhere("e.etapa_ancestro_split_id = ?", $tarea_anterior->etapa_id)->execute();
                    }
                    // Si es en paralelo, y hay etapas en otras ramas, no se pone en pendiente aun
                    if (count($etapas_otra_rama) == 0) {

                        Doctrine_Query::create()->update("Etapa")->set(array(
                            'pendiente' => 1,
                            'ended_at' => null
                        ))->where("id = ?", $tarea_anterior->etapa_id)->execute();


                    }
                }
            }
        } else {

            // Auditoría
            $registro_auditoria = new \AuditoriaOperaciones ();
            $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
            $registro_auditoria->motivo = $request->input('descripcion');
            $registro_auditoria->operacion = 'Retroceso a Etapa';
            $registro_auditoria->proceso = $etapa->Tramite->Proceso->nombre;
            $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

            $usuario = Auth::user();
            $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';

            /* Formatear detalles */

            $etapa_array ['proceso'] = $etapa->Tramite->Proceso->toArray(false);
            $etapa_array ['tramite'] = $etapa->Tramite->toArray(false);

            $etapa_array['etapa'] = $etapa->toArray(false);
            unset ($etapa_array ['etapa']['tarea_id']);
            unset ($etapa_array ['etapa']['tramite_id']);
            unset ($etapa_array ['etapa']['usuario_id']);
            unset ($etapa_array ['etapa']['etapa_ancestro_split_id']);


            $etapa_array ['tarea'] = $etapa->Tarea->toArray(false);

            $etapa_array ['usuario'] = $etapa->Usuario->toArray(false);
            unset ($etapa_array ['usuario'] ['password']);

            $etapa_array ['datos_seguimiento'] = Doctrine_Query::create()->from("DatoSeguimiento d")->where("d.etapa_id = ?", $etapa->id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            $registro_auditoria->detalles = json_encode($etapa_array);
            $registro_auditoria->save();

            $etapa->pendiente = 1;
            $etapa->ended_at = null;
            $etapa->save();
            if ($tramite->pendiente == 0) {
                $tramite->pendiente = 1;
                $tramite->ended_at = null;
                $tramite->save();
            }
        }

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/ver/' . $tramite->id)
        ]);
    }

    /**
     * @param Request $request
     * @param $etapa_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reasignar_form(Request $request, $etapa_id)
    {
        $request->validate(['usuario_id' => 'required']);

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $etapa->asignar($request->input('usuario_id'));

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/ver_etapa/' . $etapa->id)
        ]);
    }

    /**
     * @param $data
     * @return string
     */
    public function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }
}
