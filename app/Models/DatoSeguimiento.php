<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatoSeguimiento extends Model
{
    protected $table = 'dato_seguimiento';

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }
}
