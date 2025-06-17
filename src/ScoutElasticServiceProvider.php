<?php

namespace Novius\ScoutElastic;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Novius\ScoutElastic\Console\ElasticIndexCreateCommand;
use Novius\ScoutElastic\Console\ElasticIndexDropCommand;
use Novius\ScoutElastic\Console\ElasticIndexReindexCommand;
use Novius\ScoutElastic\Console\IndexConfiguratorMakeCommand;
use Novius\ScoutElastic\Console\SearchableModelMakeCommand;
use Novius\ScoutElastic\Console\SearchRuleMakeCommand;
use Novius\ScoutElastic\Indexers\BulkIndexer;

class ScoutElasticServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/scout_elastic.php' => config_path('scout_elastic.php'),
        ]);

        $this->commands([
            // make commands
            IndexConfiguratorMakeCommand::class,
            SearchableModelMakeCommand::class,
            SearchRuleMakeCommand::class,

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexDropCommand::class,
            ElasticIndexReindexCommand::class,
        ]);

        $this
            ->app
            ->make(EngineManager::class)
            ->extend('elastic', function () {
                return new ElasticEngine(new BulkIndexer);
            });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this
            ->app
            ->singleton('scout_elastic.client', function () {
                $config = Config::get('scout_elastic.client');

                $logChannels = config('scout_elastic.log_channels', []);
                if (is_array($logChannels) && ! empty($logChannels) && config('scout_elastic.log_enabled', false)) {
                    $config['logger'] = Log::stack($logChannels);
                } else {
                    Arr::forget($config, 'logger');
                }

                return ClientBuilder::fromConfig($config);
            });
    }
}
