<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cuenta;
use Illuminate\Support\Facades\Mail;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        #if user not logged, create new user and auto login this new user.
        //$this->middleware('auth_user');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $etapas_avanzadas = \Artisan::call('simple:avanzar');
        $emails_enviados = \Artisan::call('simple:sendmails');
        $limpieza = \Artisan::call('simple:limpieza');
        $message = 'Se ha ejecutado el cron de etapas vencidas y limpieza en '.env('APP_MAIN_DOMAIN', 'localhost') .'<br>';    
        $cuenta = \Cuenta::cuentaSegunDominio();
        $to = env('DESTINATARIOS_CRON');
        $subject = 'cron de tareas';
        if(!empty($to)){
            Mail::send('emails.send', ['content' => $message], function ($message) use ($subject, $cuenta, $to) {
                $message->subject($subject);
                $mail_from = env('MAIL_FROM_ADDRESS');
                if(empty($mail_from)) {
                    $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                } else {
                    $message->from($mail_from);
                }

                if (!is_null($to)) {
                    foreach (explode(',', $to) as $to) {
                        if (!empty($to)) {
                            $message->to(trim($to));
                        }
                    }
                }
            });
        }
    }
}