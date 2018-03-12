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
                        'pages' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'keyword',
                                ],
                                'nombre' => [
                                    'type' => 'text',
                                    'analyzer' => 'spanish'
                                ],
                                'master' => [
                                    'type' => 'boolean'
                                ],
                                'master_id' => [
                                    'type' => 'integer'
                                ],
                                'master_published' => [
                                    'type' => 'boolean'
                                ],
                                'published' => [
                                    'type' => 'boolean',
                                ],
                                'keywords' => [
                                    'type' => 'text',
                                    'analyzer' => 'spanish'
                                ],
                                'objective' => [
                                    'type' => 'text',
                                    'analyzer' => 'spanish'
                                ],
                                'hit_count' => [
                                    'type' => 'integer'
                                ],
                                'boost' => [
                                    'type' => 'integer'
                                ],
                                'ministry_id' => [
                                    'type' => 'keyword'
                                ],
                                'institution_id' => [
                                    'type' => 'keyword'
                                ],
                                'category_id' => [
                                    'type' => 'integer'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        } elseif ($operation == 'index') {
            if (!$model || $model == 'tramites') {
                $this->call('scout:import', ['model' => 'App\Models\Tramite']);
            }

            if (!$model || $model == 'procesos') {
                $this->call('scout:import', ['model' => 'App\Models\Proceso']);
            }

        }


    }
}