<?php

namespace Novius\ScoutElastic\Console\Features;

use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Payloads\RawPayload;

trait HasConfigurator
{
    /**
     * @var \Novius\ScoutElastic\IndexConfigurator
     */
    protected $configurator;

    protected function aliasAlreadyExists(): bool
    {
        $alias = $this->configurator->getName();
        $indices = ElasticClient::indices();
        $existsPayload = (new RawPayload())
            ->set('name', $alias)
            ->get();

        if ($indices->existsAlias($existsPayload)) {
            return true;
        }

        return false;
    }

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
