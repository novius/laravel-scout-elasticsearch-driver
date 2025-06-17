<?php

namespace Novius\ScoutElastic\Facades;

use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin Client
 */
class ElasticClient extends Facade
{
    /**
     * Get the facade.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'scout_elastic.client';
    }
}
