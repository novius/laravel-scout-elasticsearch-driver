<?php

namespace Novius\ScoutElastic\Indexers;

use Novius\ScoutElastic\Console\Features\HasConfigurator;
use Novius\ScoutElastic\Payloads\RawPayload;
use Novius\ScoutElastic\Payloads\TypePayload;
use Novius\ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;

class BulkIndexer implements IndexerInterface
{
    use HasConfigurator;
    /**
     * {@inheritdoc}
     */
    public function update(Collection $models)
    {
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();
        $this->configurator = $indexConfigurator;

        if (! $this->aliasAlreadyExists()) {
            throw new \Exception(sprintf('ES indice with aliase %s does not exists. Please run elastic:create-index command before.', $indexConfigurator->getName()));
        }

        $bulkPayload = (new TypePayload($model))->useIndex($indexConfigurator->getName());

        if ($documentRefresh = config('scout_elastic.document_refresh')) {
            $bulkPayload->set('refresh', $documentRefresh);
        }

        $models->each(function ($model) use ($bulkPayload) {
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

            if (empty($modelData)) {
                return true;
            }

            $actionPayload = (new RawPayload())
                ->set('index._id', $model->getScoutKey());

            $bulkPayload
                ->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        ElasticClient::bulk($bulkPayload->get());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Collection $models)
    {
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();
        $this->configurator = $indexConfigurator;

        if (! $this->aliasAlreadyExists()) {
            return;
        }

        $bulkPayload = new TypePayload($model);

        $models->each(function ($model) use ($bulkPayload) {
            $actionPayload = (new RawPayload())
                ->set('delete._id', $model->getScoutKey());

            $bulkPayload->add('body', $actionPayload->get());
        });

        $bulkPayload->set('client.ignore', 404);

        ElasticClient::bulk($bulkPayload->get());
    }
}
