<?php

namespace App\Http\Controllers;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use Doctrine_Query;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FileUploader;
use App\Helpers\FileS3Uploader;
use App\Helpers\File;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use App\Models\DatoSeguimiento;

class UploadController extends Controller
{

    public function datos_s3(Request $request, $campo_id, $etapa_id, $multipart=null, $part_number=null, $total_segments=null){
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if (Auth::user()->id != $etapa->usuario_id) {
            echo 'Usuario no tiene permisos para subir archivos en esta etapa';
            exit;
        }

        $tramite_id = $etapa->tramite_id;

        $campo = Doctrine_Query::create()
            ->from('Campo c, c.Formulario.Pasos.Tarea.Etapas e')
            ->where('c.id = ? AND e.id = ?', array($campo_id, $etapa_id))
            ->fetchOne();
        if (!$campo) {
            echo 'Campo no existe';
            exit;
        }

        // Tiempo de duracion por defecto es 1440 minutos
        $file_expire_minutes = isset($campo->extra->expire_minutes) ? $campo->extra->expire_minutes: 1440;

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('gif', 'jpg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx', 'mpp', 'vsd', 'odt', 'odp', 'ods', 'odg');
        if (isset($campo->extra->filetypes)) {
            $allowedExtensions = $campo->extra->filetypes;
        }
        
        if($request->headers->has('filename')){
            $filename = urldecode($request->header('filename'));
        }else{
            die('No se envio el nombre de archivo.');
        }

        
        if($request->header('content-length') > FileS3Uploader::$sizeLimit){
            echo json_encode(['error' => 'El archivo es muy grande'], JSON_UNESCAPED_UNICODE);
            exit;
        }else if(! $request->hasHeader('content-length')){
            echo json_encode(['error'=>'Cabeceras no contienen content-length'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if( ! is_null($multipart) && $multipart == 'multi'){
            $data = self::readFromSTDIN();
            if(strlen($data) > FileS3Uploader::$sizeLimit){
                echo json_encode(['error'=>'Parte demasiado grande.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $s3_uploader = new FileS3Uploader($file_expire_minutes, $allowedExtensions, $tramite_id, $filename);
            $result = $s3_uploader->uploadPart($data, $etapa_id, $part_number, $total_segments);
        }else{
            $s3_uploader = new FileS3Uploader($file_expire_minutes, $allowedExtensions, $tramite_id, $filename);
            $result = $s3_uploader->handleUpload();
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function datos(Request $request, $campo_id, $etapa_id, $multipart=null, $part_number=null)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if (Auth::user()->id != $etapa->usuario_id) {
            echo 'Usuario no tiene permisos para subir archivos en esta etapa';
            exit;
        }

        $tramite_id = $etapa->tramite_id;

        $campo = Doctrine_Query::create()
            ->from('Campo c, c.Formulario.Pasos.Tarea.Etapas e')
            ->where('c.id = ? AND e.id = ?', array($campo_id, $etapa_id))
            ->fetchOne();
        if (!$campo) {
            echo 'Campo no existe';
            exit;
        }

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('gif', 'jpg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx', 'mpp', 'vsd', 'odt', 'odp', 'ods', 'odg');
        if (isset($campo->extra->filetypes)) {
            $allowedExtensions = $campo->extra->filetypes;
        }
        
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new FileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/datos/');

        if (isset($result['success'])) {
            $file = new \File();
            $file->tramite_id = $etapa->Tramite->id;
            $archivo = $result['file_name'];
            $archivo = trim($archivo);
            $archivo = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $archivo);
            $archivo = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $archivo);
            $archivo = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $archivo);
            $archivo = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $archivo);
            $archivo = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $archivo);
            $archivo = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $archivo);
            $archivo = str_replace(array("\\", "¨", "º", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", " "), '', $archivo);
            //$file->filename=$result['file_name'];
            $file->filename = $archivo;
            $result['file_name'] = $archivo;
            $file->tipo = 'dato';
            $file->llave = strtolower(str_random(12));
            $file->save();

            $result['id'] = $file->id;
            $result['llave'] = $file->llave;
        }
        // to pass data through iframe you will need to encode all html tags
        //echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
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

    public function datos_get($id, $token)
    {
        //Chequeamos los permisos en el frontend
        $file = Doctrine_Query::create()
            ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
            ->where('f.id = ? AND f.llave = ? AND u.id = ?', array($id, $token, Auth::user()->id))
            ->fetchOne();
        if (!$file) {
            //Chequeamos permisos en el backend
            $file = Doctrine_Query::create()
                ->from('File f, f.Tramite.Proceso.Cuenta.UsuariosBackend u')
                ->where('f.id = ? AND f.llave = ? AND u.id = ? AND (u.rol like "%super%" OR u.rol like "%operacion%" OR u.rol like "%seguimiento%")', array($id, $token, UsuarioBackendSesion::usuario()->id))
                ->fetchOne();

            if (!$file) {
                echo 'Usuario no tiene permisos para ver este archivo.';
                exit;
            }
        }

        $path = public_path('uploads/datos/' . $file->filename);
        if (preg_match('/^\.\./', $file->filename)) {
            echo 'Archivo invalido';
            exit;
        }

        if (!file_exists($path)) {
            echo 'Archivo no existe';
            exit;
        }

        header('Content-Type: ' . get_mime_by_extension($path));
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
