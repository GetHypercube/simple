<?php

namespace App\ScoutEngines\Elasticsearch;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;

class ElasticsearchEngine extends \ScoutEngines\Elasticsearch\ElasticsearchEngine
{

    /**
     * Perform the given search on the engine.
     *
     * @param  Builder $builder
     * @param  array $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $builder->index ?: $builder->model->searchableAs(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => "*{$builder->query}*",
                    ],
                ]
            ]
        ];
        if ($sort = $this->sort($builder)) {
            $params['body']['sort'] = $sort;
        }
        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }
        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }
        if (isset($options['numericFilters']) && count($options['numericFilters'])) {
            $params['body']['query']['bool']['must'] = array_merge($params['body']['query']['bool']['must'],
                $options['numericFilters']);
        }

        return $this->elastic->search($params);
    }


    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return Collection
     */
    public function map($results, $model)
    {
        //dd($results['hits']);
        if ($results['hits']['total'] == 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])
            ->pluck('_id')->values()->all();
        $models = $model->whereIn(
            $model->getKeyName(), $keys
        )->get()->keyBy($model->getKeyName());

        return collect($results['hits']['hits'])->map(function ($hit) use ($model, $models) {
            $result = isset($models[$hit['_id']]) ? $models[$hit['_id']] : null;
            $result->highlight = isset($hit['highlight']) ? $hit['highlight'] : null;
            return $result;
        })->filter()->values();
    }

}