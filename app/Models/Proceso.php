<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Proceso extends Model
{

    use Searchable;

    protected $table = 'proceso';

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
        ];
    }

    public function tramite()
    {
        return $this->belongsToMany('App\Tramite');
    }
}
