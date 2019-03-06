<?php

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id, $cuenta='localhost',$matches="0",$buscar="0",$inicio=0,$limite=0){
        $tareas = DB::table('etapa')
            ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite_id',
                'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at','etapa.vencimiento_at')
            ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
            ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
            ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
            ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
            ->where('cuenta.nombre',$cuenta->nombre)
            ->whereNull('etapa.usuario_id')
            ->get()->toArray();

        foreach($tareas as $key=>$t)
            if(!$this->canUsuarioAsignarsela($usuario_id,$t->acceso_modo,$t->grupos_usuarios,$t->etapa_id))
                unset($tareas[$key]);

        return $tareas;
    }
    
    public function findAllSinAsignar($usuario_id, $cuenta='localhost'){
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Tramite t, e.Tramite.Proceso.Cuenta c')
                //Si la etapa no se encuentra asignada
                ->where('e.usuario_id IS NULL')
                //Si el usuario tiene permisos de acceso
                //->andWhere('(tar.acceso_modo="grupos_usuarios" AND g.id IN (SELECT gru.id FROM GrupoUsuarios gru, gru.Usuarios usr WHERE usr.id = ?)) OR (tar.acceso_modo = "registrados" AND 1 = ?) OR (tar.acceso_modo = "claveunica" AND 1 = ?) OR (tar.acceso_modo="publico")',array($usuario->id,$usuario->registrado,$usuario->open_id))
                ->orderBy('e.updated_at desc');
        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        $tareas=$query->execute();
        
        //Chequeamos los permisos de acceso
        foreach($tareas as $key=>$t)
            if(!$t->canUsuarioAsignarsela($usuario_id))
                unset($tareas[$key]);
        
        return $tareas;
    }
    
    //busca las etapas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id,$cuenta='localhost',$orderby='updated_at',$direction='desc',$matches="0",$buscar="0"){        
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Usuario u, e.Tramite t, t.Etapas hermanas, t.Proceso p, p.Cuenta c')
                ->select('e.*,COUNT(hermanas.id) as netapas, p.nombre as proceso_nombre, tar.nombre as tarea_nombre')
                ->groupBy('e.id') 
                //Si la etapa se encuentra pendiente y asignada al usuario
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                //Si la tarea se encuentra activa
                ->andWhere('1!=(tar.activacion="no" OR ( tar.activacion="entre_fechas" AND ((tar.activacion_inicio IS NOT NULL AND tar.activacion_inicio>NOW()) OR (tar.activacion_fin IS NOT NULL AND NOW()>tar.activacion_fin) )))')
                ->orderBy($orderby.' '.$direction);

        if($buscar){ 
            $query->whereIn('t.id',$matches);
        }

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
