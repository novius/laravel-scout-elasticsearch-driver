<?php

namespace Novius\ScoutElastic;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Laravel\Scout\EngineManager;
use Novius\ScoutElastic\Console\ElasticIndexCreateCommand;
use Novius\ScoutElastic\Console\ElasticIndexDropCommand;
use Novius\ScoutElastic\Console\ElasticIndexUpdateCommand;
use Novius\ScoutElastic\Console\IndexConfiguratorMakeCommand;
use Novius\ScoutElastic\Console\SearchableModelMakeCommand;
use Novius\ScoutElastic\Console\SearchRuleMakeCommand;

class ScoutElasticServiceProvider extends ServiceProvider
{
    public function boot()
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
            ElasticIndexUpdateCommand::class,
            ElasticIndexDropCommand::class,
        ]);

        $this
            ->app
            ->make(EngineManager::class)
            ->extend('elastic', function () {
                $indexerType = config('scout_elastic.indexer', 'single');
                $indexerClass = '\\Novius\\ScoutElastic\\Indexers\\'.ucfirst($indexerType).'Indexer';

                if (! class_exists($indexerClass)) {
                    throw new InvalidArgumentException(sprintf(
                        'The %s indexer doesn\'t exist.',
                        $indexerType
                    ));
                }

                return new ElasticEngine(new $indexerClass());
            });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this
            ->app
            ->singleton('scout_elastic.client', function () {
                $config = Config::get('scout_elastic.client');

                return ClientBuilder::fromConfig($config);
            });
    }
}
