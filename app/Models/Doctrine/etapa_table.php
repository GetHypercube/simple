<?php

use App\Models\DatoSeguimiento;
use App\Models\Etapa;
use App\Models\File;
use App\Models\Tramie;
use App\Models\Regla;
use Carbon\Carbon;

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id, $cuenta='localhost',$matches="0",$query=null,$limite=2000, $inicio=0,
                                   $returnCount=false){
       $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
       if(!$usuario->open_id){
            $grupos =  DB::table('grupo_usuarios_has_usuario')
                        ->select('grupo_usuarios_id')
                        ->where('usuario_id',$usuario->id)
                        ->get()
                        ->toArray();

            $grupos = json_decode(json_encode($grupos), true);

            if($grupos){
                $tareas = DB::table('etapa')
                    ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                        'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at',
                        'etapa.vencimiento_at')
                    ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                    ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                    ->where('cuenta.nombre',$cuenta->nombre)
                    ->whereIn('tarea.grupos_usuarios',$grupos)
                    ->whereNull('etapa.usuario_id');

                if (isset($query['option'])) {
                    $tareas = $this->evalQueryParams($sqlQuery=$tareas, $params=$query);
                }

                if (!is_null($limite)) {
                    $tareas->limit($limite)->offset($inicio);
                }

                $tareas->orderBy('etapa.tarea_id', 'ASC');

                if ($returnCount) {
                    $tareas = $tareas->count();
                } else {
                    $tareas = $tareas->get()->toArray();
                    $tareas = $this->genrateTareasArray($tareas);
                }

                //se buscan etapas cuyas tareas que por nivel de acceso esten configuradas por nombre
                //de grupo como variables @@
                $tareas_aa = DB::table('etapa')
                    ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                        'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at',
                        'etapa.vencimiento_at')
                    ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                    ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                    ->where('cuenta.nombre',$cuenta->nombre)
                    ->where('tarea.grupos_usuarios','LIKE','%@@%')
                    ->whereNull('etapa.usuario_id')
                    ->orderBy('etapa.tarea_id', 'ASC');

                if (isset($query['option'])) {
                    $tareas_aa = $this->evalQueryParams($sqlQuery=$tareas_aa, $params=$query);
                }

                if (!is_null($limite)) {
                    $tareas_aa->limit($limite);
                }

                $tareas_aa = $tareas_aa->get()->toArray();

                if (!$returnCount) {
                    if(count($tareas_aa)) {
                        foreach($tareas_aa as $key=>$t) {
                            if(!$this->canUsuarioAsignarsela(
                                $usuario_id,
                                $t->acceso_modo,
                                $t->grupos_usuarios,
                                $t->etapa_id)
                            ) {
                                unset($tareas_aa[$key]);
                            }
                        }

                        $tareas_aa = $this->genrateTareasArray($tareas_aa);

                        //se agregan al listado original de etapas solo las que cumplen los nombres de grupo
                        // como variables @@
                        foreach($tareas_aa as $tarea) {
                            array_push($tareas,$tarea);
                        }

                    }
                } else {
                    if(count($tareas_aa)){
                        foreach($tareas_aa as $key=>$t)
                            if(!$this->canUsuarioAsignarsela(
                                $usuario_id,
                                $t->acceso_modo,
                                $t->grupos_usuarios,
                                $t->etapa_id)
                            ) {
                                unset($tareas_aa[$key]);
                            }

                        //se contabilizan las que cumplen los nombres de grupo como variables @@
                        foreach($tareas_aa as $tarea)
                            $tareas++;
                    }
                }
            }
            else{
                $tareas = array();
            }
        }else{
            $tareas = array();
        } 
         
     
        return $tareas;
    }

    private function evalQueryParams($sqlQuery, $params)
    {
        switch ($params['option']) {
            case 'option1':
                $sqlQuery->where('tramite.id', $params['tramite_id']);
                break;
            case 'option3':
                $refProcessed = DatoSeguimiento::addFormatNames(strtolower($params['ref']));
                $arrayDatos = [];
                $datosNombre = DatoSeguimiento::where('nombre')
                    ->select('dato_seguimiento.etapa_id')
                    ->where('valor', 'like', '%' . $refProcessed. '%')
                    ->get()
                    ->toArray();

                foreach ($datosNombre as $dato) {
                    $arrayDatos[] = $dato['etapa_id'];
                }

                if (count($arrayDatos) > 0) {
                    $sqlQuery->whereIn('ds.etapa_id', $arrayDatos);
                } else {
                    $sqlQuery->where('proceso.nombre', 'like', '%' . $refProcessed. '%');
                }
                break;
            case 'option4':
                $arrayDatos = [];
                $nameProcessed = DatoSeguimiento::addFormatNames(strtolower($params['name']));
                $datosNombre = DatoSeguimiento::where('nombre', 'tramite_descripcion')
                    ->select('dato_seguimiento.etapa_id')
                    ->where('valor', 'like', '%'.urldecode($nameProcessed).'%')
                    ->get()
                    ->toArray();

                foreach ($datosNombre as $dato) {
                    $arrayDatos[] = $dato['etapa_id'];
                }

                if (count($arrayDatos) > 0) {
                    $sqlQuery->whereIn('ds.etapa_id', $arrayDatos);
                } else {
                    $sqlQuery->where('proceso.nombre', 'like', '%' . $nameProcessed. '%');
                }
                break;
        }

        if (isset($params['updated_date_from'])) {
            $sqlQuery->where('tramite.updated_at', '>=', date('Y-m-d', strtotime($params['updated_date_from'])));
        }

        if (isset($params['updated_date_to'])) {
            $sqlQuery->where('tramite.updated_at', '<=', date('Y-m-d', strtotime($params['updated_date_to'])));
        }

        if (isset($params['deleted_date_from'])) {
            $sqlQuery->where('tramite.deleted_at', '>=', date('Y-m-d', strtotime($params['deleted_date_from'])));
        }

        if (isset($params['deleted_date_to'])) {
            $sqlQuery->where('tramite.deleted_at', '<=', date('Y-m-d', strtotime($params['deleted_date_to'])));
        }

        return $sqlQuery;
    }

    private function genrateTareasArray($tareas)
    {
        $tareasAux = [];
        foreach ($tareas as $t) {
            $item = [
                'id' => $t->id,
                'etapa_id' => $t->etapa_id,
                'acceso_modo' => $t->acceso_modo,
                'grupos_usuarios' => $t->grupos_usuarios,
                'previsualizacion' => $t->previsualizacion,
                'p_nombre' => $t->p_nombre,
                't_nombre' => $t->t_nombre,
                'updated_at' => Carbon::parse($t->updated_at)->format('d-m-Y H:i:s'),
                'vencimiento_at' => ($t->vencimiento_at != null) ? Carbon::parse($t->vencimiento_at)
                    ->format('d-m-Y H:i:s'): 'N/A',
                'ref' => null,
                'nombre' => null,
            ];

            $item['file'] = (File::where('tramite_id', $t->id)->count() > 0) ? true:false;

            if (!empty($t->previsualizacion)) {
                $r = new Regla($t->previsualizacion);
                $item['previsualizacion'] = $r->getExpresionParaOutput($t->etapa_id);
            }

            $etapasTramite = Etapa::where('tramite_id', $t->id)->get();
            $etapasTramiteIds = [];

            foreach ($etapasTramite as $etapaTramite) {
                $etapasTramiteIds[] = $etapaTramite->id;
            }

            $datosSeguimiento = DatoSeguimiento::whereIn('etapa_id', $etapasTramiteIds)->get();

            foreach ($datosSeguimiento as $datoSeg) {
                if ($datoSeg->nombre == 'tramite_ref') {
                    $item['ref'] = DatoSeguimiento::removeFormatNames($datoSeg->valor);
                }
                if ($datoSeg->nombre == 'tramite_descripcion') {
                    $item['nombre'] = DatoSeguimiento::removeFormatNames($datoSeg->valor);
                }
            }

            $etapasTareaList = Etapa::where('pendiente', true)
                ->join('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                ->where('tramite_id', $t->id)
                ->get();

            foreach ($etapasTareaList as $et) {
                $item['etapas'][] = $et->nombre;
            }

            $tareasAux[] = $item;
        }

        return $tareasAux;
    }
    
   public function findSinAsignarMatch($usuario_id, $cuenta='localhost',$matches="0",$query="0"){
       $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
       if(!$usuario->open_id){
            $grupos =  DB::table('grupo_usuarios_has_usuario')
                        ->select('grupo_usuarios_id')
                        ->where('usuario_id',$usuario->id)
                        ->get()
                        ->toArray();
            $grupos = json_decode(json_encode($grupos), true);

            if($grupos){
                $tareas = DB::table('etapa')
                ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at','etapa.vencimiento_at')
                ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                ->where('cuenta.nombre',$cuenta->nombre)
                ->whereIn('tarea.grupos_usuarios',[$grupos])
                ->whereIn('tramite.id',[$matches])
                ->whereNull('etapa.usuario_id')
                ->orderBy('etapa.tarea_id', 'ASC')
                ->get()->toArray();
            }
            else{
                $tareas = array();
            }
        }else{
            $tareas = array();
        }  
        return $tareas;
    }
    
    //busca las etapas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id,$cuenta='localhost',$orderby='updated_at',$direction='desc',$matches="0",$buscar="0", $limite=0, $inicio=0){        
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Usuario u, e.Tramite t, t.Etapas hermanas, t.Proceso p, p.Cuenta c')
                ->select('e.*,COUNT(hermanas.id) as netapas, p.nombre as proceso_nombre, tar.nombre as tarea_nombre')
                ->groupBy('e.id') 
                //Si la etapa se encuentra pendiente y asignada al usuario
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                //Si la tarea se encuentra activa
                ->andWhere('1!=(tar.activacion="no" OR ( tar.activacion="entre_fechas" AND ((tar.activacion_inicio IS NOT NULL AND tar.activacion_inicio>NOW()) OR (tar.activacion_fin IS NOT NULL AND NOW()>tar.activacion_fin) )))')
                ->andWhere('t.deleted_at is NULL')
                ->limit($limite)
                ->offset($inicio)
                ->orderBy($orderby.' '.$direction);

        if($buscar){ 
            $query->whereIn('t.id',$matches);
        }

        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        return $query->execute();
    }

    public function findPendientesALL($usuario_id, $cuenta='localhost', $orderby='updated_at',$direction='desc',$matches="0",$buscar="0"){        
        $query=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso.Cuenta c, t.Etapas e, e.Usuario u')
                ->where('u.id = ?',$usuario_id)
                ->andWhere('e.pendiente=1')
                ->limit(3000)
                ->andWhere('t.deleted_at is NULL')
                ->orderBy('t.updated_at desc');
        
        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);        
        return $query->execute();
    }

    public function canUsuarioAsignarsela($usuario_id, $acceso_modo, $grupos_usuarios, $etapa_id)
    {
        static $usuario;

        if (!$usuario || ($usuario->id != $usuario_id)) {
            $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
        }

        if ($acceso_modo == 'publico')
            return true;

        if ($acceso_modo == 'claveunica' && $usuario->open_id)
            return true;

        if ($acceso_modo == 'registrados' && $usuario->registrado)
            return true;

        if ($acceso_modo == 'grupos_usuarios') {
            $r = new Regla($grupos_usuarios);
            $grupos_arr = explode(',', $r->getExpresionParaOutput($etapa_id));
            foreach ($usuario->GruposUsuarios as $g)
                if (in_array($g->id, $grupos_arr))
                    return true;
        }

        return false;
    }
    
}
