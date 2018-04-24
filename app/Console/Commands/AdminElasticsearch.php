<?php

namespace App\Console\Commands;

use App\Suggestion;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class AdminElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:admin {operation} {model?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los indices en elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $client = new Client();

        $operation = $this->argument('operation');
        $model = $this->argument('model');

        if ($operation == 'create') {
            try {
                $this->info('Se borra índice');
                $response = $client->request('DELETE', env('ELASTICSEARCH_HOST') . '/' . env('ELASTICSEARCH_INDEX'));
            } catch (ClientException $e) {
                $this->line($e->getMessage());
            }

            $this->info('Se crea índice');
            $response = $client->request('PUT', env('ELASTICSEARCH_HOST') . '/' . env('ELASTICSEARCH_INDEX'), [
                'json' => [
                    'mappings' => [
                        'tramite' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'integer'
                                ],
                                'proceso_id' => [
                                    'type' => 'integer'
                                ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                    //'fielddata' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        } elseif ($operation == 'index') {
            if (!$model || $model == 'tramite') {
                $this->call('scout:import', ['model' => 'App\Models\Tramite']);
            }

            if (!$model || $model == 'procesos') {
                $this->call('scout:import', ['model' => 'App\Models\Proceso']);
            }

        }


    }
}