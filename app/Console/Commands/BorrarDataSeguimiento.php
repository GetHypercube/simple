<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Carbon\Carbon;

class BorrarDataSeguimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:borrar_data {cuenta_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite eliminar la data de los trámites de una cuenta en particular';

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
        $cuenta_id = $this->argument('cuenta_id');
        $this->info('Inicio ejecución: '.Carbon::now());
        $data_count = 0;
        $etapas = DB::table('etapa')
                    ->select('etapa.id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                    ->where('proceso.cuenta_id',$cuenta_id)
                    ->take(10000)
                    ->orderBy('etapa.id', 'DESC')
                    ->get()->toArray();
        $etapas = json_decode(json_encode($etapas), true);
        $data_count = DB::table('dato_seguimiento')->whereIn('etapa_id', $etapas)->count();
        $eliminados = DB::table('dato_seguimiento')->whereIn('etapa_id', $etapas)->delete();
        $this->info("Registros eliminados de dato_seguimiento: ".$data_count);
        $this->info('Fin ejecución: '.Carbon::now());
    }
}
