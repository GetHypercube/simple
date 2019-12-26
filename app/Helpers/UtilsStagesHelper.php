<?php

use App\Models\Cuenta;
use App\Models\Etapa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

function getPrevisualization($e)
{
   $previsualizacion = '';
    if(!empty($e->previsualizacion))
    {
        $r = new Regla($e->previsualizacion);
        $previsualizacion = $r->getExpresionParaOutput($e->etapa_id);
    }
    return $previsualizacion;
}
function getValorDatoSeguimiento($e, $tipo)
{
    $tramite_nro = '';
    foreach ($e->datoSeguimientos as $dato) {
        if ($dato->nombre == $tipo) {
            $tramite_nro = $dato->valor;
        }
    }
    return $tramite_nro != '' ? $tramite_nro : $e->tramite->proceso->nombre;
}
function getCuenta()
{
    return Cuenta::find(1)->toArray();
}

function getTotalUnnasigned()
{
    if (!Auth::user()->open_id) 
    {
        $grupos = Auth::user()->grupo_usuarios()->pluck('grupo_usuarios_id');
        $cuenta=\Cuenta::cuentaSegunDominio();
        return Etapa::
        whereHas('tramite')
        ->whereHas('tarea', function($q) use ($grupos,$cuenta){
            $q->where(function($q) use ($grupos){
                $q->whereIn('grupos_usuarios',$grupos)
                ->orWhere('grupos_usuarios','LIKE','%@@%');
            })
            ->whereHas('proceso', function($q) use ($cuenta){
                $q->whereHas('cuenta', function($q) use ($cuenta){
                    $q->where('cuenta.nombre',$cuenta->nombre);         
                });
            });       
        })           
        ->whereNull('usuario_id')
        ->orderBy('tarea_id', 'ASC')
        ->count();
    }
}

function getTotalAssigned()
{
    return Etapa::where('etapa.usuario_id', Auth::user()->id)->where('etapa.pendiente', 1)
        ->whereHas('tramite')
        ->whereHas('tarea', function($q){
            $q->where('activacion', "si")
            ->orWhere(function($q)
            {
                $q->where('activacion', "entre_fechas")
                ->where('activacion_inicio', '<=', Carbon::now())
                ->where('activacion_fin', '>=', Carbon::now());   
            });
        })
    ->count();
}

function getTotalHistory()
{
    $cuenta=\Cuenta::cuentaSegunDominio();
    return Etapa::where('pendiente', 0)
        ->whereHas('tramite', function($q) use ($cuenta){
            $q->whereHas('proceso', function($q) use ($cuenta){
                $q->whereHas('cuenta', function($q) use ($cuenta){
                    if($cuenta != 'localhost')
                    {
                        $q->where('nombre', $cuenta->nombre);
                    }
                });
            });
        })
        ->where('usuario_id', Auth::user()->id)
        ->count();
}

function linkActive($path)
{
    return Request::path() == $path ? 'active':'';
}

function getUrlSortUnassigned($request, $sortValue)
{
    $path = Request::path();
    $sort = $request->input('sort') == 'asc' ? 'desc':'asc';
    return  "/".$path.'?query='.$request->input('query').'&sortValue='.$sortValue."&sort=".$sort;
}

function getDateFormat($date, $type = 'update')
{
    return $date == null || !$date ? '' : Carbon::parse($date)->format('d-m-Y '.($type == 'update' ? 'H:i:s': ''));
}

function hasFiles($etapas)
{
    foreach ($etapas as $e)      
    {
        if($e->tramite->files->count() > 0)
        {
            return true;
        }
    }
    return false;
}
function getLastTask($etapa)
{

    return $etapa->tramite->etapas()->where('pendiente', 0)->orderBy('id', 'desc')->first() ? 
    getDateFormat($etapa->tramite->etapas()->where('pendiente', 0)->orderBy('id', 'desc')->first()->ended_at) : 'N/A';
}