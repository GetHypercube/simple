<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Doctrine;
use Doctrine_Query;

class LimpiezaTramitesUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:limpieza';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpieza de tŕamites sin modificarse y usuarios no registrados sin actividad';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Limpia los tramites que que llevan mas de 1 dia sin modificarse, sin avanzar de etapa y sin datos ingresados (En blanco).
        $tramites_en_blanco=Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u, e.DatosSeguimiento d')
                ->where('t.updated_at < DATE_SUB(NOW(),INTERVAL 1 DAY) AND t.pendiente = 1')
                ->groupBy('t.id')
                ->having('COUNT(e.id) = 1 AND COUNT(d.id) = 0')
                ->execute();
        $this->info('tramites en blanco eliminados--'.count($tramites_en_blanco));
        \Log::info('tramites en blanco eliminados--'.count($tramites_en_blanco));
        $tramites_en_blanco->delete();

        //Limpia los tramites que han sido iniciados por usuarios no registrados, y que llevan mas de 1 dia sin modificarse, y sin avanzar de etapa.
        $tramites_en_primera_etapa=Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('t.updated_at < DATE_SUB(NOW(),INTERVAL 1 DAY) AND t.pendiente = 1')
                ->groupBy('t.id')
                ->having('COUNT(e.id) = 1')
                ->execute();
        foreach($tramites_en_primera_etapa as $t){
            $cantidad_tramites_primera_etapa = 0;
            if($t->Etapas[0]->Usuario->registrado == 0){
                $this->info('tramite--'.$t->id);
                $t->delete();
                $cantidad_tramites_primera_etapa++;
            }
        }
        $this->info('tramites en primera etapa eliminados--'.$cantidad_tramites_primera_etapa);
        \Log::info('tramites en primera etapa eliminados--'.$cantidad_tramites_primera_etapa);

        //Elimina los usuarios no registrados con mas de 1 dia de antiguedad y que no hayan iniciado etapas
        $noregistrados=Doctrine_Query::create()
                ->from('Usuario u, u.Etapas e')
                ->where('u.registrado = 0 AND DATEDIFF(NOW(),u.updated_at) >= 1')
                ->groupBy('u.id')
                ->having('COUNT(e.id) = 0')
                ->execute();
        $this->info('usuarios no registrados sin actividad eliminados--'.count($noregistrados));
        \Log::info('usuarios no registrados sin actividad eliminados--'.count($noregistrados));
        $noregistrados->delete();
    }
}
