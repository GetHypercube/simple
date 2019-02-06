<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioManager extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario_manager';

    protected $table = 'usuario_manager';
    
    public $user_type = 'manager';
}
