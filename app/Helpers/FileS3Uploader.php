<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use function GuzzleHttp\json_decode;


class FileS3Uploader
{
    private $multipart_key = null;
    private $allowedExtensions = array();
    public static $sizeLimit = 20 * 1024 * 1024;
    private $file_expire_minutes;
    private $file;
    private $tramite_id;
    private $file_tipo = 's3';
    public static $amazon_algo = 'md5';
    public $filename = null;

    function __construct($file_expire_minutes, array $allowedExtensions = array(), $tramite_id, $filename=null)
    {
      $this->tramite_id = $tramite_id;
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;

        $this->file = false;
        $this->filename = self::filenameToAscii($filename);
        $this->multipart_key = $tramite_id.'/'.$this->filename;
        $this->filename = $tramite_id.'/'.$this->filename;
        $this->file_expire_minutes = $file_expire_minutes;
    }

    private function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        $val = preg_replace('/[^0-9]/', '', $val);

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    private function createMultiPartId($client){
        $response = $client->CreateMultipartUpload(
            [
                'Bucket'=> env('AWS_BUCKET'),
                'Key'=> $this->multipart_key
            ]
        );

        if($response) {
            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->multipart_key, $this->file_tipo, $this->tramite_id);
            // $permanent_url = Storage::disk('s3')->url($this->multipart_key);
            if( ! $file ){
                $file = new \File();
                $file->filename = $this->multipart_key;
                $file->llave = strtolower(str_random(12));
                $file->tramite_id = $this->tramite_id;
                $file->tipo = $this->file_tipo;
            }
            $multipart_id = $response['UploadId'];
            $aux = json_decode(json_encode($file->extra), true);
            $aux['multipart_id'] = $multipart_id;
            // $aux['permanent_url'] = $permanent_url;
            $file->extra = $aux;
            $file->save();
            return $multipart_id;
        }

        return ['error'=>'ERROR al obtener UploadId', 'success' => false];
    }

    private function getMultiPartId(){
        $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->multipart_key, $this->file_tipo, $this->tramite_id);
        if($file && isset($file->extra->multipart_id)){
            return $file->extra->multipart_id;
        }
        
        return array('error'=>'ERROR al obtener el UploadId guardado', 'success'=> false);
    }

    function uploadPart($data, $etapa_id, $part_number, $total_segments){
        $disk = Storage::disk('s3');
        $driver = $disk->getDriver();
        $client = $driver->getAdapter()->getClient();
        
        if($part_number==1){
            $multipart_id = $this->createMultiPartId($client);
        }else{
            $multipart_id = $this->getMultiPartId();
        }
        
        $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->multipart_key, $this->file_tipo, $this->tramite_id);
        $result = $client->uploadPart([
            'Bucket'=> env('AWS_BUCKET'),
            'Key'   => $this->multipart_key,
            'UploadId'   => $multipart_id,
            'PartNumber' => $part_number,
            'Body'       => $data
        ]);
        
        $extra_arr = json_decode(json_encode($file->extra),true);
        
        $extra_arr['parts'][$part_number] = ['ETag' => str_replace('"', '', $result['ETag']),
                                                'PartNumber' => intval($part_number)];
        
        $file->extra = $extra_arr;
        $file->save();
        $temporaryUrl = '';
        if($part_number == $total_segments){
            $result = $client->CompleteMultipartUpload([
                'Bucket'=> env('AWS_BUCKET'),
                'Key'=> $this->multipart_key,
                'UploadId'   => $multipart_id,
                'MultipartUpload' => ['Parts' => json_decode(json_encode($file->extra->parts), true)]
            ]);
            $temporaryUrl = $disk->temporaryUrl($this->multipart_key, Carbon::now()->addMinutes($this->file_expire_minutes));
            if(array_key_exists('success', $result) && $result['success'] == true){
                $result['id'] = $file->id;
                $result['llave'] = $file->llave;
                $result['file_name'] = $file->filename;
            }
            
            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->multipart_key, $this->file_tipo, $this->tramite_id);
            // $permanent_url = Storage::disk('s3')->url($this->multipart_key);
            if( ! $file ){
                $file = new \File();
                $file->filename = $this->multipart_key;
                $file->llave = strtolower(str_random(12));
                $file->tramite_id = $this->tramite_id;
                $file->tipo = $this->file_tipo;
            }
            /*
            // $aux['permanent_url'] = $permanent_url;
            $aux = json_decode(json_encode($file->extra), true);
            $file->extra = $aux;
            */
            
            $file->save();
        }
        return [
            'success'=> $result['@metadata']['statusCode'] === 200 ? true: false,
            'URL'=>$temporaryUrl
        ];
    }

    private static function readFromSTDIN(){
        $f_input = fopen("php://input", "rb");
        $buff = [];
        while (!feof($f_input)) {
            $buff[] = fread($f_input, 4096);
        }
        fclose($f_input);
        return implode($buff);
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload()
    {
        $ext = pathinfo($this->filename, PATHINFO_EXTENSION);
        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'Extensión de archivo inválida. Solo puedes subir archivos con estas extensiones: ' . $these . '.',
                        'success' => false);
        }

        $full_path_arr = [$this->tramite_id, $this->filename];
        $full_path = implode('/', $full_path_arr);
        $f_input = fopen("php://input", "r");

        $disk = Storage::disk('s3');
        $driver = $disk->getDriver();
        
        $metadata = ['Metadata' => ['tramite_id' => $this->tramite_id]];
        $status_bool = $disk->put($full_path, $f_input, $metadata);
        
        if ($status_bool) {
            // $url = $disk->url($full_path);
            // $path = $disk->path($full_path); // 2/hello.jpg
            $temporaryUrl = $disk->temporaryUrl($this->filename, Carbon::now()->addMinutes($this->file_expire_minutes));

            $aws_metadata = $driver->getAdapter()->getMetadata($full_path);
            
            $result_success = ['success' => true, 'file_name' => $this->filename, 'full_path' => $full_path, 'status'=> $status_bool];
            $result_success['hash'] = str_replace('"', '',$aws_metadata['etag']);
            $result_success['algo'] = self::$amazon_algo;
            $result_success['filename'] = $aws_metadata['filename'];
            $result_success['extension'] = $aws_metadata['extension'];
            $result_success['URL'] = $temporaryUrl;
            
            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, $this->file_tipo, $this->tramite_id);
            // $permanent_url = Storage::disk('s3')->url($this->filename);
            if( ! $file ){
                $file = new \File();
                $file->tipo = $this->file_tipo;
                $file->llave = strtolower(str_random(12));
                $file->tramite_id = $this->tramite_id;
                $file->filename = $this->filename;
            }
            $extra = [
                'URL' => $temporaryUrl
            ];
            $file->extra = $extra;
            $file->save();
          return $result_success;
        } else {
            return ['error' => 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered', 
                    'success' => false];
        }
    }

    public static function filenameToAscii($filename){
        $filename = mb_strtolower($filename);   //Lo convertimos a minusculas
        $filename = preg_replace('/\s+/', ' ', $filename);  //Le hacemos un trim
        //$filename = sha1(uniqid(mt_rand(),true));
        $filename = trim($filename);
        $filename = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),$filename);
        $filename = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),$filename);
        $filename = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),$filename);
        $filename = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $filename);
        $filename = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),$filename);
        $filename = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'),array('n', 'N', 'c', 'C',), $filename);
        $filename = str_replace(array("\\","¨","º","-","~","#","@","|","!","\"","·","$","%","&","/","(", ")","?","'","¡","¿","[","^","`","]","+","}","{","¨","´",">","< ",";", ",",":"," "),'',$filename);
        return $filename;
    }
}