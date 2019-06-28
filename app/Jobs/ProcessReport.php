<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Job;
use App\Models\Reporte;
use Cuenta;
use App\Helpers\Doctrine;
use Doctrine_Query;
use DB;
use Carbon\Carbon;

class ProcessReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $user_type;
    protected $proceso_id;
    protected $reporte_id;
    protected $max_running_jobs = 1;
    protected $tries = 1;
    protected $job_info;
    protected $reporte_tabla;
    protected $link_host;
    protected $email_to;
    protected $email_subject;
    protected $email_message;
    protected $email_name;
    protected $_base_dir;
    protected $nombre_reporte;
    protected $desde;
    protected $hasta;
    protected $pendiente;
    protected $cuenta;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $user_id,
        $user_type,
        $proceso_id,
        $reporte_id,
        $reporte_tabla,
        $host,
        $email_to,
        $email_name,
        $email_subject,
        $desde,
        $hasta,
        $pendiente,
        $cuenta
    )
    {
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->proceso_id = $proceso_id;
        $this->reporte_id = $reporte_id;
        $this->reporte_tabla = $reporte_tabla;
        $this->link_host = $host;
        $this->email_to = $email_to;
        $this->email_name = $email_name;
        $this->email_subject = $email_subject;
        $this->_base_dir = public_path('uploads/tmp');
        if(!file_exists($this->_base_dir) ) {
            mkdir($this->_base_dir, 0777, true);
        }
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->pendiente = $pendiente;
        $this->cuenta = $cuenta;
        
        $this->job_info = new Job();
        $this->arguments = serialize([$user_id, $user_type, $proceso_id, $reporte_id]);
        $this->job_info->user_id = $this->user_id;
        $this->job_info->user_type = $this->user_type;
        $this->job_info->arguments = $this->arguments;
        $this->job_info->status = Job::$created;
        $this->job_info->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $this->job_info->status = Job::$running;
        $this->job_info->save();

        $this->generarExcel();

        $this->job_info->filename = $this->nombre_reporte.'.xls';
        $this->job_info->filepath = $this->_base_dir;
        
        try{
            $this->send_notification();
            $this->job_info->status = Job::$finished;
        }catch(\Exception $e){
            Log::error("ProcessReport::handle() Error al enviar notificacion: " . $e->getMessage());
            $this->job_info->status = Job::$error;
        }
        $this->job_info->save();
    }

    private function generarExcel(){

        $excel_row = $this->reporte_tabla;

        $this->nombre_reporte = 'reporte-'.$this->reporte_id.'-'.Carbon::now('America/Santiago')->format('dmYHis');
        Excel::create($this->nombre_reporte, function ($excel) use ($excel_row) {
            $excel->sheet('reporte', function ($sheet) use ($excel_row) {
                $sheet->fromArray($excel_row, null, 'A1', false, false);
            });
        })->store('xls', $this->_base_dir);  
    }

    private function send_notification(){
        $link = "{$this->link_host}/backend/reportes/descargar_archivo/{$this->user_id}/{$this->job_info->id}/{$this->job_info->filename}";
        $data = ['link' => $link];
        $email_to = $this->email_to;
        $email_subject = $this->email_subject;
        $cuenta = $this->cuenta;
        Mail::send('emails.download_link', $data, function($message) use ($cuenta, $link, $email_to, $email_subject){

            $message->subject($email_subject);
            $mail_from = env('MAIL_FROM_ADDRESS');
            if(empty($mail_from))
                $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
            else
                $message->from($mail_from);

            $message->to($email_to);
        });
    }

    
}
