<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'file';

    protected $fillable = ['filename', 'tipo', 'tramite_id', 'validez_habiles', 'extra', 'campo_id'];

    static $type_folder = ['dato' =>  'datos'];

    public static function boot()
    {
        parent::boot();
        static::deleted(function(File $file){
            $folder = isset(File::$type_folder[$file->tipo]) ? File::$type_folder[$file->tipo] : '';
            $path = public_path("uploads/{$folder}/{$file->filename}");
            if($folder != '' && file_exists($path))
                unlink($path);
        });
    }

    public function tramite()
    {
        return $this->belongsTo(Tramite::class);
    }

    public function campo()
    {
        return $this->belongsTo(Campo::class);
    }

}
