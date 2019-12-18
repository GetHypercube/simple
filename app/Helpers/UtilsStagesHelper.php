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

function getUrlSortUnassigned($request, $sortValue)
{
    $path = Request::path();
    $sort = $request->input('sort') == 'asc' ? 'desc':'asc';
    return  "/".$path.'?query='.$request->input('query').'&sortValue='.$sortValue."&sort=".$sort;
}

function getUpdateAtFormat($updated_at)
{
    return $updated_at == null || !$updated_at ? '' : Carbon::parse($updated_at)->diffForHumans();
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
    getUpdateAtFormat($etapa->tramite->etapas()->where('pendiente', 0)->orderBy('id', 'desc')->first()->ended_at) : 'N/A';
}