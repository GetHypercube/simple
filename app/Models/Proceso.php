<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Proceso extends Model
{
    use Searchable;

    protected $table = 'proceso';
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->with("tramites")
            ->with('tramites.etapas')
            ->with('tramites.etapas.datoSeguimientos')
            ->where("id", $this->id)
            ->first()
            ->toArray();

        return $array;
    }

    public function tramites()
    {
        return $this->hasMany(Tramite::class);
    }

}
