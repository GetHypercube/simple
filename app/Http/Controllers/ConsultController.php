<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsultController extends Controller
{
    public function index(Request $request)
    {
        $query = 0;
        $data = array();
        $data['vacio'] = '';
        $data['title'] = 'Consultas de Documentos';
        $data['titulo'] = 'Seguimiento de Trámites en Línea';
        $resp = '<br/><div class="alert alert-warning"><strong>Sin datos Disponibles</strong></div>';

        $fecha = $request->input('fecha', old('fecha'));
        $nrotramite = trim($request->input('nrotramite', old('nrotramite')));

        if ($request->isMethod('post')) {
            $fecha = $request->input('fecha');
            $nrotramite = trim($request->input('nrotramite'));

            $request->validate([
                'fecha' => 'required|digits_between:1,30|numeric',
                'nrotramite' => 'required|digits_between:1,30|numeric'
            ], [
                'nrotramite.numeric' => 'El campo <b>Nro. de Trámite</b> debe ser un número.',
                'nrotramite.required' => 'El campo <b>Nro. de Trámite</b> es obligatorio.',
                'fecha.numeric' => 'El campo <b>Fecha</b> debe ser un número.',
                'fecha.required' => 'El campo <b>Fecha</b> es obligatorio.'
            ]);

            if (is_numeric($nrotramite) && is_numeric($fecha)) {
                $query = (new \Consultas)->listDatoSeguimiento($nrotramite, $fecha, \Cuenta::cuentaSegunDominio());
                $data['vacio'] = $resp;
            }

        };

        $data['fecha'] = $fecha;
        $data['nrotramite'] = $nrotramite;
        $data['tareas'] = $query;
        $data['content'] = view('consult.index', $data);

        return view('layouts.app', $data);
    }

    public function ver_etapas($id_etapa)
    {
        $query = (new \Consultas)->detalleEtapa($id_etapa);

        $data['etapa'] = $query[0];
        $data['content'] = view('consult.consult_info', $data);

        return view('layouts.app', $data);
    }
}
