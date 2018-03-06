<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Query;

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
        }

        $qs = $request->getQueryString();
        return redirect('etapas/ejecutar/' . $tramite->getEtapasActuales()->get(0)->id . ($qs ? '?' . $qs : ''));

    }
}
