<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'cuenta';

    public function UsuarioBackend()
    {
        return $this->hasMany(UsuarioBackend::class);
    }
}
