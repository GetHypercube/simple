<?php

namespace App\Models;

use App\Notifications\UserBackendResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioBackend extends Authenticatable
{
    use Notifiable;

    protected $guarded = 'usuario_backend';

    protected $table = 'usuario_backend';

    protected $fillable = [
        'email',
    ];

    public function Cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserBackendResetPasswordNotification($token));
    }
}
