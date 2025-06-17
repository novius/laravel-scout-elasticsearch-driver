<?php

namespace Novius\ScoutElastic\Indexers;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Novius\ScoutElastic\Console\Features\HasConfigurator;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Payloads\RawPayload;
use Novius\ScoutElastic\Payloads\TypePayload;
use Novius\ScoutElastic\Searchable;
use RuntimeException;

class BulkIndexer implements IndexerInterface
{
    use HasConfigurator;

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws Exception
     */
    public function update(Collection $models): array
    {
        /** @var Model&Searchable $model */
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();
        $this->configurator = $indexConfigurator;

        try {
            // Use name of new index created by elastic:create-index command
            $indexName = resolve('elasticIndexCreated');
        } catch (BindingResolutionException) {
            $indexName = $indexConfigurator->getName();
        }

        if (! $this->aliasAlreadyExists()) {
            throw new RuntimeException(sprintf('ES indice with aliase %s does not exists. Please run elastic:create-index command before.', $indexConfigurator->getName()));
        }

        $bulkPayload = (new TypePayload($model))->useIndex($indexName);

        if ($documentRefresh = config('scout_elastic.document_refresh')) {
            $bulkPayload->set('refresh', $documentRefresh);
        }

        $models->each(function ($model) use ($bulkPayload) {
            /** @var Model&Searchable $model */
            if ($model::usesSoftDelete() && config('scout.soft_delete', false)) {
                $model->pushSoftDeleteMetadata();
            }

            $modelData = array_merge(
                $model->toSearchableArray(),
                $model->scoutMetadata(),
                [
                    'type' => $model->searchableAs(),
                ]
            );

            $actionPayload = (new RawPayload)
                ->set('index._id', $model->getScoutKey());

            $bulkPayload
                ->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        return ElasticClient::bulk($bulkPayload->get())->asArray();
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws Exception
     */
    public function delete(Collection $models): array
    {
        /** @var Model&Searchable $model */
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();
        $this->configurator = $indexConfigurator;

        if (! $this->aliasAlreadyExists()) {
            return [];
        }

        $bulkPayload = new TypePayload($model);

        $models->each(function ($model) use ($bulkPayload) {
            /** @var Model&Searchable $model */
            $actionPayload = (new RawPayload)
                ->set('delete._id', $model->getScoutKey());

            $bulkPayload->add('body', $actionPayload->get());
        });

        $bulkPayload->set('client.ignore', 404);

        return ElasticClient::bulk($bulkPayload->get())->asArray();
    }
}
