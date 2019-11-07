<?php
namespace App\Helpers;

use App\Models\Anuncio;

class Utils{
    
    function get_anuncio_activo(){
        $anuncio = Anuncio::where('activo',1)->first();
        $anuncio = $anuncio === null ? $anuncio : null;
        return $anuncio;
    }
}