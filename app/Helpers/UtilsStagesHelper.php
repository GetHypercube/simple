<?php

use App\Models\Cuenta;
use App\Models\Etapa;

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