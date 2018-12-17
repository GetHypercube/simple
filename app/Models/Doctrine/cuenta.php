<?php

use App\Helpers\Doctrine;
use Illuminate\Support\Facades\Log;

class Cuenta extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('nombre_largo');
        $this->hasColumn('mensaje');
        $this->hasColumn('logo');
        $this->hasColumn('logof');
        $this->hasColumn('api_token');
        $this->hasColumn('descarga_masiva');
        $this->hasColumn('client_id');
        $this->hasColumn('client_secret');
        $this->hasColumn('ambiente');
        $this->hasColumn('vinculo_produccion');
        if(\Schema::hasColumn('cuenta', 'entidad')){
            $this->hasColumn('entidad');
        }
        $this->hasColumn('estilo');
        $this->hasColumn('header');
        $this->hasColumn('footer');
        $this->hasColumn('personalizacion');
        $this->hasColumn('personalizacion_estado');
    }

    function setUp()
    {
        parent::setUp();

        $this->hasMany('UsuarioBackend as UsuariosBackend', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Usuario as Usuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('GrupoUsuarios as GruposUsuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Proceso as Procesos', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Widget as Widgets', array(
            'local' => 'id',
            'foreign' => 'cuenta_id',
            'orderBy' => 'posicion'
        ));

        $this->hasMany('HsmConfiguracion as HsmConfiguraciones', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));
    }

    public function updatePosicionesWidgetsFromJSON($json)
    {
        $posiciones = json_decode($json);

        Doctrine_Manager::connection()->beginTransaction();
        foreach ($this->Widgets as $c) {
            $c->posicion = array_search($c->id, $posiciones);
            $c->save();
        }
        Doctrine_Manager::connection()->commit();
    }

    // Retorna el objecto cuenta perteneciente a este dominio.
    // Retorna null si no estamos en ninguna cuenta valida.
    public static function cuentaSegunDominio()
    {
        static $firstTime = true;
        static $cuentaSegunDominio = null;
        if ($firstTime) {
            $firstTime = false;
            $host = Request::server('HTTP_HOST');
            Log::debug('$host: ' . $host);
            $main_domain = env('APP_MAIN_DOMAIN');

            if ($main_domain) {
                Log::debug('$main_domain2: ' . $main_domain);
                $main_domain = addcslashes($main_domain, '.');
                preg_match('/(.+)\.' . $main_domain . '/', $host, $matches);
                Log::debug('$main_domain2: ' . $main_domain);

                if (isset ($matches[1])) {
                    Log::debug('$matches: ' . $matches[1]);
                    $cuentaSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($matches[1]);
                } else {
                    $cuentaSegunDominio = Doctrine_Query::create()->from('Cuenta c')->limit(1)->fetchOne();
                }

            } else {
                $cuentaSegunDominio = Doctrine_Query::create()->from('Cuenta c')->limit(1)->fetchOne();
            }
        }

        return $cuentaSegunDominio;
    }

    public function getLogoADesplegar()
    {
        if ($this->logo)
            return asset('logos/' . $this->logo);
        else
            return asset('img/logo.png');
    }

    public function getLogofADesplegar()
    {
        if ($this->logof)
            return asset('logos/' . $this->logof);
        else
            return asset('img/logof.png');
    }

    public function usesClaveUnicaOnly()
    {
        foreach ($this->Procesos as $p) {
            $tareaInicial = $p->getTareaInicial();
            if ($tareaInicial && $tareaInicial->acceso_modo != 'claveunica')
                return false;
        }

        return true;
    }

    public function getAmbienteDev($cuenta_prod_id)
    {

        $cuenta_dev = Doctrine_Query::create()
            ->from('Cuenta c')
            ->where('c.vinculo_produccion = ?', $cuenta_prod_id)
            ->execute();

        return $cuenta_dev;
    }

    public function getProcesosActivos()
    {

        Log::debug('getProcesosActivos: ' . $this->id);

        $procesos = Doctrine_Query::create()
            ->from('Proceso p, p.Cuenta c')
            ->where('p.estado="public" AND p.activo=1 AND c.id = ?', $this->id)
            ->execute();

        return $procesos;
    }

    // Retorna el valor de header, footer, css  perteneciente a este dominio.
    // Retorna null en personalizacion si no esta activado (1) .
    public static function configSegunDominio()
    {
        static $firstTime = true;
        static $configSegunDominio = null;
        if ($firstTime) {
            $firstTime = false;
            $host = Request::server('HTTP_HOST');
            Log::debug('$host: ' . $host);
            $main_domain = env('APP_MAIN_DOMAIN');

            if ($main_domain) {
                Log::debug('$main_domain2: ' . $main_domain);
                $main_domain = addcslashes($main_domain, '.');
                preg_match('/(.+)\.' . $main_domain . '/', $host, $matches);
                Log::debug('$main_domain2: ' . $main_domain);

                if (isset ($matches[1])) {
                    Log::debug('$matches: ' . $matches[1]);
                    $configSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($matches[1]);
                } else {
                    $configSegunDominio = Doctrine_Query::create()->from('Cuenta c')->limit(1)->fetchOne();
                }

            } else {
                $configSegunDominio = Doctrine_Query::create()
                                    ->from('Cuenta c')
                                    ->limit(1)->fetchOne();
            }
        }
        $configDominio['estilo'] = $configSegunDominio->estilo;
        $configDominio['dominio_header'] = $configSegunDominio->header;
        $configDominio['dominio_footer'] = $configSegunDominio->footer;
        $configDominio['personalizacion'] = ("1" == $configSegunDominio->personalizacion_estado) ? $configSegunDominio->personalizacion : '';
        $configDominio['personalizacion_estado'] = $configSegunDominio->personalizacion_estado;
        return $configDominio;
    }

}