<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function dominio() {

        $dominio = $_SERVER['HTTP_HOST'] ;
        $dominio= preg_replace("/((http|https|www)[^\s]+)/", '<a href="$1">$0</a>', $dominio);
        //$dominio = $request->getHost();
        //$dominio = \Request::getHost();

        switch ($dominio) {
            case('ssff.super.gob.cl'):
            case('isl.super.gob.cl:8000'):
                $header = 'layouts.header_super';
                $footer = 'layouts.footer_super';
            break;
            case('carabineros.gob.cl'):
                $header = 'layouts.header_carabineros';
                $footer = 'layouts.footer_carabineros';
            break;
            default:
                $header = 'layouts.header';
                $footer = 'layouts.footer';
        }

        //$data = array ();
        $data['dominio_header'] = $header;
        $data['dominio_footer'] = $footer;

        return $data;
    }    
}
