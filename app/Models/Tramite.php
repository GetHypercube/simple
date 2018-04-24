<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Tramite extends Model
{
    use Searchable;

    protected $table = 'tramite';

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        return $array;
    }

    public function proceso()
    {
        return $this->belongsToMany('App\Proceso');
    }
}
