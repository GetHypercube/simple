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

    public function CuentaSegunDominio()
    {
        static $firstTime = true;
        static $cuentaSegunDominio = null;
        if ($firstTime) {
            $firstTime = false;
            $request = new Request();
            $host = $request->getHttpHost();
            $main_domain = env('APP_MAIN_DOMAIN');

            if ($main_domain) {
                $main_domain = addcslashes($main_domain, '.');
                preg_match('/(.+)\.' . $main_domain . '/', $host, $matches);

                if (isset ($matches[1])) {
                    $cuentaSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($matches[1]);
                } else {
                    $cuentaSegunDominio = Cuenta::find(1);
                }

            } else {
                $cuentaSegunDominio = Doctrine_Query::create()->from('Cuenta c')->limit(1)->fetchOne();
            }
        }

        return $cuentaSegunDominio;
    }
}
