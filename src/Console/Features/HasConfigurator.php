<?php

namespace Novius\ScoutElastic\Console\Features;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\IndexConfigurator;
use Novius\ScoutElastic\Payloads\RawPayload;

trait HasConfigurator
{
    protected IndexConfigurator $configurator;

    protected function aliasAlreadyExists(): bool
    {
        $alias = $this->configurator->getName();
        $indices = ElasticClient::indices();
        $existsPayload = (new RawPayload)
            ->set('name', $alias)
            ->get();

        if ($indices->existsAlias($existsPayload)->asBool()) {
            return true;
        }

        return false;
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function findIndexNameByAlias($aliasName)
    {
        $aliases = ElasticClient::indices()->getAlias()->asArray();
        foreach ($aliases as $index => $aliasMapping) {
            if (array_key_exists($aliasName, $aliasMapping['aliases'])) {
                return $index;
            }
        }

        return null;
    }
}
