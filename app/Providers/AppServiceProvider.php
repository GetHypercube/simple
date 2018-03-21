<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\ScoutEngines\Elasticsearch\ElasticsearchEngine;
use Laravel\Scout\EngineManager;
use Elasticsearch\ClientBuilder as ElasticBuilder;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_ALL, env('PHP_LOCALE'));

        Schema::defaultStringLength(191);

        $this->bootClaveUnicaSocialite();

        $this->bootElasticsearch();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDoctrineOrmServiceProvider();
        $this->registerRollbarServiceProvider();
    }

    public function registerDoctrineOrmServiceProvider()
    {
        $this->app->register(DoctrineOrmServiceProvider::class);
    }

    public function registerRollbarServiceProvider()
    {
        if ($this->app->environment('production')) {
            $this->app->register(RollbarServiceProvider::class);
        }
    }

    public function bootClaveUnicaSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'claveunica',
            function ($app) use ($socialite) {
                //$config = $app['config']['services.claveunica'];

                $redirect = env('APP_MAIN_DOMAIN') == 'localhost' ?
                    env('APP_URL') . '/login/claveunica/callback' :
                    secure_url('login/claveunica/callback');

                $config = [
                    'client_id' => \Cuenta::cuentaSegunDominio()->client_id,
                    'client_secret' => \Cuenta::cuentaSegunDominio()->client_secret,
                    'redirect' => $redirect
                ];
                return $socialite->buildProvider(\App\Socialite\Two\ClaveUnicaProvider::class, $config);
            }
        );
    }

    private function bootElasticsearch()
    {
        app(EngineManager::class)->extend('elasticsearch', function ($app) {
            return new ElasticsearchEngine(ElasticBuilder::create()
                ->setHosts(config('scout.elasticsearch.hosts'))
                ->build(),
                config('scout.elasticsearch.index')
            );
        });
    }

}
