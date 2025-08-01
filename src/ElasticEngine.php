<?php

namespace Novius\ScoutElastic;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Novius\ScoutElastic\Builders\FilterBuilder;
use Novius\ScoutElastic\Builders\SearchBuilder;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Indexers\IndexerInterface;
use Novius\ScoutElastic\Payloads\TypePayload;
use stdClass;

class ElasticEngine extends Engine
{
    /**
     * The indexer interface.
     */
    protected IndexerInterface $indexer;

    /**
     * ElasticEngine constructor.
     *
     * @return void
     */
    public function __construct(IndexerInterface $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    public function update($models): void
    {
        $this
            ->indexer
            ->update($models);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($models): void
    {
        $this->indexer->delete($models);
    }

    /**
     * Build the payload collection.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws Exception
     */
    public function buildSearchQueryPayloadCollection(Builder $builder, array $options = [])
    {
        /** @var FilterBuilder $builder */
        $payloadCollection = collect();

        if ($builder instanceof SearchBuilder) {
            $searchRules = $builder->rules ?: $builder->model->getSearchRules();

            foreach ($searchRules as $rule) {
                $payload = new TypePayload($builder->model);

                if (is_callable($rule)) {
                    $payload->setIfNotEmpty('body.query.bool', call_user_func($rule, $builder));
                } else {
                    /** @var SearchRule $ruleEntity */
                    $ruleEntity = new $rule($builder);

                    if ($ruleEntity->isApplicable()) {
                        $payload->setIfNotEmpty('body.query.bool', $ruleEntity->buildQueryPayload());

                        if ($options['highlight'] ?? true) {
                            $payload->setIfNotEmpty('body.highlight', $ruleEntity->buildHighlightPayload());
                        }
                    } else {
                        continue;
                    }
                }

                $payloadCollection->push($payload);
            }
        } else {
            $payload = (new TypePayload($builder->model))
                ->setIfNotEmpty('body.query.bool.must.match_all', new stdClass);

            $payloadCollection->push($payload);
        }

        return $payloadCollection->map(function (TypePayload $payload) use ($builder, $options) {
            $payload
                ->setIfNotEmpty('body._source', $builder->select)
                ->setIfNotEmpty('body.collapse.field', $builder->collapse)
                ->setIfNotEmpty('body.sort', $builder->orders)
                ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
                ->setIfNotEmpty('body.profile', $options['profile'] ?? null)
                ->setIfNotNull('body.from', $builder->offset)
                ->setIfNotNull('body.size', $builder->limit);

            foreach ($builder->wheres as $filters) {
                $clauseKey = 'body.query.bool.filter';

                $clauseValue = array_merge(
                    $payload->get($clauseKey, []),
                    $filters
                );

                $payload->setIfNotEmpty($clauseKey, $clauseValue);
            }

            return $payload->get();
        });
    }

    /**
     * Perform the search.
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                ElasticClient::getFacadeRoot(),
                $builder->query,
                $options
            );
        }

        $results = [];

        $this
            ->buildSearchQueryPayloadCollection($builder, $options)
            ->each(function ($payload) use (&$results) {
                $results = ElasticClient::search($payload)->asArray();

                $results['_payload'] = $payload;

                if ($this->getTotalCount($results) > 0) {
                    return false;
                }
            });

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        /** @var FilterBuilder $builder */
        $builder
            ->from(($page - 1) * $perPage)
            ->take($perPage);

        return $this->performSearch($builder);
    }

    /**
     * Explain the search.
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    public function explain(Builder $builder)
    {
        return $this->performSearch($builder, [
            'explain' => true,
        ]);
    }

    /**
     * Profile the search.
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    public function profile(Builder $builder)
    {
        return $this->performSearch($builder, [
            'profile' => true,
        ]);
    }

    /**
     * Return the number of documents found.
     *
     * @throws Exception
     */
    public function count(Builder $builder): int
    {
        $count = 0;

        $this
            ->buildSearchQueryPayloadCollection($builder, ['highlight' => false])
            ->each(function ($payload) use (&$count) {
                $result = ElasticClient::count($payload);

                $count = $result['count'] ?? 0;

                if ($count > 0) {
                    return false;
                }
            });

        return $count;
    }

    /**
     * Make a raw search.
     *
     * @param  array  $query
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws Exception
     */
    public function searchRaw(Model $model, $query)
    {
        $payload = (new TypePayload($model))
            ->setIfNotEmpty('body', $query)
            ->get();

        return ElasticClient::search($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->map(function ($result) {
            $result['_id'] = $this->getModelIDFromHit($result);

            return $result;
        })->pluck('_id');
    }

    /**
     * {@inheritdoc}
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return Collection::make();
        }

        /** @var Model&Searchable $model */
        $scoutKeyName = $model->getScoutKeyName();

        $columns = Arr::get($results, '_payload.body._source');

        if (is_null($columns)) {
            $columns = ['*'];
        } else {
            $columns[] = $scoutKeyName;
        }

        $ids = $this->mapIds($results)->all();

        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $models = $query
            ->whereIn($scoutKeyName, $ids)
            ->get($columns)
            ->keyBy($scoutKeyName);

        return new Collection(collect($results['hits']['hits'])
            ->map(function ($hit) use ($models) {
                $id = $this->getModelIDFromHit($hit);

                if (isset($models[$id])) {
                    $model = $models[$id];
                    $model->_score = $hit['_score'];

                    if (isset($hit['highlight'])) {
                        $model->highlight = new Highlight($hit['highlight']);
                    }

                    return $model;
                }

                return null;
            })
            ->filter()
            ->values()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'] ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function flush($model): void
    {
        /** @var Model&Searchable $model */
        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $query
            ->orderBy($model->getScoutKeyName())
            ->unsearchable();
    }

    /**
     * Extract model ID from hit (by removing prefix type)
     *
     * @return mixed
     */
    protected function getModelIDFromHit($hit)
    {
        return str_replace($hit['_source']['type'].'_', '', $hit['_id']);
    }

    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        // TODO: Implement lazyMap() method.

        return new LazyCollection;
    }

    public function createIndex($name, array $options = [])
    {
        // TODO: Implement createIndex() method.
    }

    public function deleteIndex($name)
    {
        // TODO: Implement deleteIndex() method.
    }
}
