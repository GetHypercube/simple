<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Cuenta;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Tramite;

class TramitesController extends Controller
{
    public function iniciar(Request $request, $proceso_id)
    {
        Log::info('Iniciando proceso ' . $proceso_id);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        //echo Auth::user()->id;
        //exit;
        if (!$proceso->canUsuarioIniciarlo(Auth::user()->id)) {
            echo 'Usuario no puede iniciar este proceso';
            exit;
        }

        //Vemos si es que usuario ya tiene un tramite de proceso_id ya iniciado, y que se encuentre en su primera etapa.
        //Si es asi, hacemos que lo continue. Si no, creamos uno nuevo
        $tramite = Doctrine_Query::create()
            ->from('Tramite t, t.Proceso p, t.Etapas e, e.Tramite.Etapas hermanas')
            ->where('t.pendiente=1 AND p.activo=1 AND p.id = ? AND e.usuario_id = ?', array($proceso_id, Auth::user()->id))
            ->groupBy('t.id')
            ->having('COUNT(hermanas.id) = 1')
            ->fetchOne();

        if (!$tramite) {
            $tramite = new \Tramite();
            $tramite->iniciar($proceso->id);

            if(session()->has('redirect_url')){
                return redirect()->away(session()->get('redirect_url'));
            }
        }

        $qs = $request->getQueryString();

        return redirect('etapas/ejecutar/' . $tramite->getEtapasActuales()->get(0)->id . ($qs ? '?' . $qs : ''));
    }

    public function participados(Request $request, $offset = 0)
    {
        $query = $request->input('query');
        $matches = "";
        $rowtramites = "";
        $contador = "0";
        $resultotal = "false";
        $perpage = 50;

        $page = $request->input('page', 1); // Get the ?page=1 from the url
        $offset = ($page * $perpage) - $perpage;


        if ($query) {
            $result = Tramite::search($query)->get();
            $matches = array();
            foreach($result as $resultado){
                array_push($matches, $resultado->id);
            }
            if(count($result) > 0){
                $resultotal = "true";
            }else{
                $resultotal = "false";
            }
        }

        if ($resultotal == 'true') {
            $contador = Doctrine::getTable('Tramite')->findParticipadosMatched(Auth::user()->id, Cuenta::cuentaSegunDominio(), $matches, $query)->count();
            $rowtramites = Doctrine::getTable('Tramite')->findParticipados(Auth::user()->id, Cuenta::cuentaSegunDominio(), $perpage, $offset, $matches, $query);
        } else {
            $rowtramites = Doctrine::getTable('Tramite')->findParticipados(Auth::user()->id, Cuenta::cuentaSegunDominio(), $perpage, $offset, '0', $query);
            $contador = Doctrine::getTable('Tramite')->findParticipadosALL(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
        }

        $config['base_url'] = url('tramites/participados');
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

        $data = \Cuenta::configSegunDominio();

        $data['tramites'] = new LengthAwarePaginator(
            $rowtramites, // Only grab the items we need
            $contador, // Total items
            $perpage, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // We need this so we can keep all old query parameters from the url
        );
        $data['query'] = $query;
        $data['sidebar'] = 'participados';
        $data['content'] = view('tramites.participados', $data);
        $data['title'] = 'Bienvenido';

        return view('layouts.procedure', $data);
    }

    public function disponibles()
    {
        $data = \Cuenta::configSegunDominio();
        $data['procesos'] = Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciar(Auth::user()->id, Cuenta::cuentaSegunDominio(), 'nombre', 'asc');

        $data['sidebar'] = 'disponibles';
        $data['content'] = view('tramites.disponibles', $data);
        $data['title'] = 'Trámites disponibles a iniciar';

        return view('layouts.app', $data);
    }

    public function eliminar($tramite_id)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if ($tramite->Etapas->count() > 1) {
            echo 'Tramite no se puede eliminar, ya ha avanzado mas de una etapa';
            exit;
        }

        if (Auth::user()->id != $tramite->Etapas[0]->usuario_id) {
            echo 'Usuario no tiene permisos para eliminar este tramite';
            exit;
        }

        $tramite->delete();

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
