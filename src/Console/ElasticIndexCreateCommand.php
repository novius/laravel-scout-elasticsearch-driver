<?php

namespace Novius\ScoutElastic\Console;

use Illuminate\Console\Command;
use Novius\ScoutElastic\Console\Features\HasConfigurator;
use Novius\ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Payloads\IndexPayload;
use Novius\ScoutElastic\Payloads\RawPayload;

class ElasticIndexCreateCommand extends Command
{
    use HasConfigurator;
    use RequiresIndexConfiguratorArgument;

    protected $signature = 'elastic:create-index
                            {index-configurator : The index configurator class}
                            {--populate : populate index with indexable models}';

    protected $description = 'Create an Elasticsearch index';

    /**
     * @var string
     */
    protected $indexName = '';

    protected $aliasCreated = false;

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->configurator = $this->getIndexConfigurator();
        $aliasAlreadyExists = $this->aliasAlreadyExists();

        if ($aliasAlreadyExists && ! $this->option('populate')) {
            $this->error(sprintf('An index with alias "%s" already exists. Please use elastic:reindex command.', $this->configurator->getName()));

            return;
        }

        $this->createIndex();

        if (! $aliasAlreadyExists) {
            $this->createAlias();
        }

        if ($this->option('populate')) {
            if ($aliasAlreadyExists) {
                // Hack to populate the good index (the newest) instead of the current with alias
                app()->bind('elasticIndexCreated', function () {
                    return $this->indexName;
                });
            }

            collect(config('scout_elastic.searchable_models', []))
                ->filter(function ($indexableClass) {
                    $model = new $indexableClass;

                    return method_exists($model, 'getIndexConfigurator') && get_class($model->getIndexConfigurator()) === get_class($this->configurator);
                })->each(function ($class) {
                    $this->call('scout:import', [
                        'model' => $class,
                    ]);
                });
        }

        if ($aliasAlreadyExists && ! $this->aliasCreated) {
            $oldIndex = $this->findIndexNameByAlias($this->configurator->getName());
            $payloadDeleteOldIndex = (new RawPayload)
                ->set('index', $oldIndex)
                ->get();

            ElasticClient::indices()
                ->delete($payloadDeleteOldIndex);

            $this->info(sprintf('The old index %s was deleted.', $oldIndex));
        }

        if (! $this->aliasCreated) {
            $this->createAlias();
        }
    }

    /**
     * Create an index.
     *
     * @return void
     */
    protected function createIndex()
    {
        $indexCreationPayload = new IndexPayload($this->configurator, true);
        $this->indexName = $indexCreationPayload->get('index');

        $payload = $indexCreationPayload
            ->setIfNotEmpty('body.settings', $this->configurator->getSettings())
            ->setIfNotEmpty('body.mappings', $this->configurator->getDefaultMapping())
            ->get();

        ElasticClient::indices()
            ->create($payload);

        $this->info(sprintf(
            'The %s index was created!',
            $this->indexName
        ));
    }

    /**
     * Create an write alias.
     *
     * @return void
     */
    protected function createAlias()
    {
        $payload = ((new IndexPayload($this->configurator))
            ->useIndex($this->indexName))
            ->set('name', $this->configurator->getName())
            ->get();

        ElasticClient::indices()
            ->putAlias($payload);

        $this->info(sprintf(
            'The %s alias for the %s index was created!',
            $this->configurator->getName(),
            $this->indexName
        ));

        $this->aliasCreated = true;
    }
}
