<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Regla;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:sendmails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificación de etapas por vencer';

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
        $etapas = Doctrine_Query::create()
            ->from('Etapa e, e.Tarea t')
            ->where('e.pendiente = 1 AND t.vencimiento_notificar = 1')
            ->execute();
        foreach ($etapas as $e){
            $vencimiento=$e->vencimiento_at;
            if($vencimiento!=''){
                
                $dias_por_vencer=ceil((strtotime($e->vencimiento_at)-time())/60/60/24);
                $dias_no_habiles = 0;
                if ($e->Tarea->vencimiento_habiles == 1)
                    $dias_no_habiles = (new \App\Helpers\dateHelper())->get_working_days_count(date('Y-m-d'), $e->vencimiento_at);
                
                $regla=new Regla($e->Tarea->vencimiento_notificar_email);
                $email=$regla->getExpresionParaOutput($e->id);
                
                if ($dias_por_vencer > 0)
                    $dias_por_vencer-=$dias_no_habiles;                 
                
                if ($dias_por_vencer <= $e->Tarea->vencimiento_notificar_dias){
                    $this->info('Enviando correo de notificacion para etapa ' . $e->id);
                    $subject = 'Etapa se encuentra ' . ($dias_por_vencer>0 ?'por vencer':'vencida');
                    $cuenta=$e->Tramite->Proceso->Cuenta;
                    $url_final = empty(env('APP_MAIN_DOMAIN')) ? url("/etapas/ejecutar/{$e->id}") : "https://".$cuenta->nombre.".".env('APP_MAIN_DOMAIN')."/etapas/ejecutar/{$e->id}";
                    $message = '<p>La etapa "' . $e->Tarea->nombre . '" del proceso "'.$e->Tramite->Proceso->nombre.'" se encuentra '
                            .($dias_por_vencer>0?'a '.$dias_por_vencer. (abs($dias_por_vencer)==1?' día ':' días ') .($e->Tarea->vencimiento_habiles == 1 ? 'habiles ' : '') .
                                    'por vencer':('vencida '.($dias_por_vencer<0 ? 'hace '.abs($dias_por_vencer).(abs($dias_por_vencer)==1?' día ':' días ') : 'hoy'))).' ('.date('d/m/Y',strtotime($e->vencimiento_at)).').' . "</p><br>" . 
                            '<p>Usuario asignado: ' . $e->Usuario->usuario .'</p>'.($dias_por_vencer > 0 ? '<p>Para realizar la etapa, hacer click en el siguiente link: '. $url_final .'</p>':'');

                    \Mail::send('emails.send', ['content' => $message], function ($message) use ($e, $subject, $cuenta, $email) {
                        $message->subject($subject);
                        $mail_from = env('MAIL_FROM_ADDRESS');
                        if(empty($mail_from))
                            $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                        else
                            $message->from($mail_from);
                        
                        $message->to($email);
                    });
                }
            }
        }
    }
}
