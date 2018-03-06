<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioBackend extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario_backend';

    protected $table = 'usuario_backend';

    public function Cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }
}
