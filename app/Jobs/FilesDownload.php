<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use \App\Helpers\FileS3Uploader;
use \App\Models\Job;

class FilesDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file_list;
    protected $email_to;
    protected $email_subject;
    protected $email_content;
    protected $email_message;
    protected $email_name;
    protected $link_host;
    protected $user_id;
    protected $user_type;
    protected $unique_dir_name;
    protected $_base_dir;
    protected $temp_path;
    protected $zip_filename;
    protected $zip_hash;
    protected $zip_name_type;
    protected $as_date = 1;
    protected $as_hash = 2;
    protected $zip_new_name;
    protected $job_id;
    protected $arguments;
    protected $max_running_jobs = 1;
    protected $job_info;
    protected $added_files_path;
    protected $cuenta;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $user_type, $file_list, $email_to, $email_name, $email_subject, $host, $cuenta)
    {
        $this->user_id = $user_id;
        $this->file_list = $file_list; // array ['folder' => 'foo_12', 'file' => '']
        $this->email_to = $email_to; // string
        $this->email_name = $email_name; // string
        $this->email_subject = $email_subject; // string
        $this->link_host = $host;
        $this->cuenta = $cuenta;

        $this->_base_dir = public_path('uploads/tmp/async_downloader');
        if( ! file_exists($this->_base_dir) ) {
            mkdir($this->_base_dir, 0777, true);
        }
        $this->added_files_path = [];
        $this->zip_name_type = $this->as_hash;
        $this->user_type = $user_type;
        $this->arguments = serialize([$user_id, $user_type, $file_list, $email_to, $email_name, $email_subject, $host]);
        $this->job_info = new Job();
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

        $this->create_temp_directory();

        $this->zip_filename = $this->_base_dir.DIRECTORY_SEPARATOR.$this->unique_dir_name.'.zip';

        //tipo documento y tipo dato
        self::copy_local_files_to_zip_folder($this->temp_path, $this->file_list, $this->added_files_path);
        
        // descargar los archivos
        self::download_s3_files_to_zip_folder($this->temp_path, $this->file_list, $this->added_files_path);
        
        // empaquetar los archivos
        $compress_status = self::zip_dir_recursive($this->temp_path, $this->zip_filename, FALSE, \ZipArchive::CM_STORE);
        if( $compress_status === FALSE){
            echo "Error al crear el archivo zip: $this->zip_filename\n";
        }
        
        switch($this->zip_name_type){
            case $this->as_date:
                $fecha_obj = new \DateTime();
                $time_stamp = date_format($fecha_obj, "Y-m-d_His.u");
                $this->zip_new_name = "{$time_stamp}_{$this->user_id}.zip";
                break;
            case $this->as_hash:
                $this->zip_new_name = hash_file('sha256', $this->zip_filename).'.zip';
                break;
            default:
                $this->zip_new_name = hash_file('sha256', $this->zip_filename).'.zip';
                break;
        }
        
        rename($this->zip_filename, $this->_base_dir.DIRECTORY_SEPARATOR.$this->zip_new_name);
        $this->job_info->filename = $this->zip_new_name;
        try{
            $this->send_notification();
            $this->job_info->status = Job::$finished;
        }catch(\Exception $e){
            Log::error("FilesDownload::handle() Error al enviar notificacion: " . $e->getMessage());
            $this->job_info->status = Job::$error;
        }
        $this->remove_all_tmp($this->added_files_path);
        $this->job_info->filepath = $this->_base_dir;
        $this->job_info->save();
    }

    public static function copy_local_files_to_zip_folder($out_dir, $file_list, &$copied_files){
        $errors_copying = [];
        $ignore_non_local_file_type = ['s3'];
        foreach($file_list as $tipo => $f_array ){
            if( in_array($tipo, $ignore_non_local_file_type) )
                continue;
            
            foreach ($f_array as $file) {
                $dir = "{$out_dir}/{$file['directory']}";
                if( ! file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                $ori_full_path = public_path($file['ori_path']);
                $f = $dir.DIRECTORY_SEPARATOR.$file['nice_name'];
                if( ! copy($ori_full_path, $f) ){
                    $errors_copying[] = $file;
                }else{
                    $copied_files[] = $f;
                } 
            }
        }
    }

    private function remove_all_tmp(&$file_list){
        foreach($file_list as $file){
            $for_unlink = $this->temp_path.DIRECTORY_SEPARATOR.$file;
            if( ! empty($for_unlink) && strpos($for_unlink, '..') === FALSE && trim($for_unlink) !== '.' && file_exists($for_unlink) ){
                unlink($for_unlink);
            }
        }

        $master_directory = $this->_base_dir . DIRECTORY_SEPARATOR . $this->unique_dir_name;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($master_directory, \RecursiveIteratorIterator::SELF_FIRST)
        );
        $dirs_delete = [];
        foreach ($iterator as $info) {
            if( ! in_array($info->getPath(), $dirs_delete))
                $dirs_delete[] = $info->getPath();
        }
        
        rsort($dirs_delete);
        foreach($dirs_delete as $dir){
            if(file_exists($dir) && $this->is_dir_empty($dir) ){
                @rmdir($dir);
            }else{
                $error = "Directorio temporal '{$dir}' no existe o no esta vacio. No se puede borrar.";
                Log::error($error);
            }
        }
    }

    private function is_dir_empty($dir){
        $files = scandir($dir);
        foreach($files as $file){
            if($file !== '.' && $file !== '..'){
                return false;
            }
        }

        return true;
    }

    private function create_temp_directory(){
        do {
            $this->unique_dir_name = $this->user_id.'_'.str_replace([' ', '-'], ['_', ''], microtime());
            $this->unique_dir_name = FileS3Uploader::filenameToAscii($this->unique_dir_name);
            $this->unique_dir_name = trim($this->unique_dir_name);
            $this->unique_dir_name = str_replace('.', '', $this->unique_dir_name);
            $this->temp_path = $this->_base_dir.DIRECTORY_SEPARATOR.$this->unique_dir_name;
            usleep(1);
        } while( file_exists($this->temp_path) );
        mkdir($this->temp_path, 0777, true);
    }

    private function send_notification(){
      // enviar por correo un link a la descarga
      $link = "{$this->link_host}/descargar_archivo/{$this->user_id}/{$this->job_info->id}/{$this->zip_new_name}";
      $data = ['content' => $this->email_content, 'link' => $link];
      $cuenta = $this->cuenta;
      Mail::send('emails.download_link', $data, function($message) use ($cuenta){
        
        $message->subject($this->email_subject);
        
        $mail_from = env('MAIL_FROM_ADDRESS');
        if(empty($mail_from)) {
            $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
        } else {
            $message->from($mail_from);
        }

        $message->to($this->email_to);
        
      });
    }
    public static function download_s3_files_to_zip_folder($out_dir, $file_list, &$copied_files){
        if(array_key_exists('s3', $file_list) && count($file_list['s3']) > 0 ){
            $disk = \Storage::disk('s3');
            
            $driver = $disk->getDriver();
            $client = $driver->getAdapter()->getClient();
            
            $client->registerStreamWrapper();
            foreach ($file_list['s3'] as $file) {
                
                $dir = "{$out_dir}/{$file['directory']}";
                if( ! file_exists($dir) ){
                    mkdir($dir, 0777, true);
                }
                $full_path_filename = 's3://'.implode('/', [$file['bucket'], $file['file_path'], $file['file_name'] ] );
                $dest_file = $dir.DIRECTORY_SEPARATOR.$file['file_name'];
                if( ! file_exists($full_path_filename) ){
                    continue;
                }
                $f_w = fopen($dest_file, 'w');
                $f_r = fopen($full_path_filename, 'r');
                while(!feof($f_r)){
                    fwrite($f_w, fread($f_r, 4096 * 16));
                }
                fclose($f_w);
                fclose($f_r);
                $copied_files[] = $file['directory'].DIRECTORY_SEPARATOR.$file['file_name'];
            }
        }
    }

    public static function zip_dir_recursive($source, $destination, $include_dir, $compression){
        if ( ! file_exists($source)) {
            echo "Origen a comprimir: $source no existe.\n";
            return false;
        }
    
        if (file_exists($destination)) {
            unlink ($destination);
        }
    
        $zip = new \ZipArchive();
        if ( $zip->open($destination, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) !== TRUE) {
            echo "Error abriendo el zip :-(\n";
            return false;
        }
        $source = realpath($source);
    
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
    
        if ($include_dir) {
            $start_last_dir = strrpos($source, DIRECTORY_SEPARATOR) + 1;
            $maindir = substr($source, $start_last_dir);
            $source = substr($source, 0, $start_last_dir );
            $zip->addEmptyDir($maindir);
            $source_long_directories = strlen($source) - 1;
            $source_long_files = strlen($source);
        }else{
            $source_long_directories = strlen($source);
            $source_long_files = strlen($source);
        }
        
        $omitted_directories = ['.', '..'];
        foreach ($files as $file){
            if( in_array($file->getFilename(), $omitted_directories) ){
                continue;
            }
    
            $file = $file->getRealPath();        
            if (is_dir($file) === TRUE){
                $zip->addEmptyDir(substr($file, $source_long_directories));
            }else if (is_file($file) === TRUE ){ //&& file_exists($file)){
                $f_name_dest = substr($file, $source_long_files);
                try{
                    $zip->addFile($file, $f_name_dest);
                }catch(\Exception $e){
                    $this->failed($e);
                }
                $zip->setCompressionName($f_name_dest, $compression);
            }
            
        }
        try{
            return $zip->close(); // TRUE o FALSE
        }catch(\Exception $e){
            $this->failed($e);
        }
    }

    public function failed(){
        $this->job_info->status = Job::$error;
        $this->job_info->save();
    }
}
