<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Tramite extends Model
{
    use Searchable;

    protected $table = 'tramite';

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'proceso_id' => $this->proceso_id,
            'pendiente' => $this->pendiente,
            'tramite_proc_cont' => $this->tramite_proc_cont,
        ];
    }

    public function proceso()
    {
        return $this->belongsToMany('App\Proceso');
    }
}
