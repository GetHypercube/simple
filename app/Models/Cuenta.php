<?php

namespace App\Models;

use App\Helpers\Doctrine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cuenta extends Model
{
    protected $table = 'cuenta';

    public function UsuarioBackend()
    {
        return $this->hasMany(UsuarioBackend::class);
    }

}
