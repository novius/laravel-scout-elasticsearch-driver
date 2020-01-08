<?php

namespace Novius\ScoutElastic\Indexers;

use Novius\ScoutElastic\Migratable;
use Novius\ScoutElastic\Payloads\RawPayload;
use Novius\ScoutElastic\Payloads\TypePayload;
use Novius\ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;

class BulkIndexer implements IndexerInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(Collection $models)
    {
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();

        $bulkPayload = new TypePayload($model);

        if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
            $bulkPayload->useAlias('write');
        }

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
