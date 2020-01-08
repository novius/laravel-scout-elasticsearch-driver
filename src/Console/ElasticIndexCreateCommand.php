<?php

namespace Novius\ScoutElastic\Console;

use Novius\ScoutElastic\Migratable;
use Illuminate\Console\Command;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Payloads\IndexPayload;
use Novius\ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexCreateCommand extends Command
{
    use RequiresIndexConfiguratorArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elastic:create-index';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create an Elasticsearch index';

    /**
     * Create an index.
     *
     * @return void
     */
    protected function createIndex()
    {
        $configurator = $this->getIndexConfigurator();

        $payload = (new IndexPayload($configurator))
            ->setIfNotEmpty('body.settings', $configurator->getSettings())
            ->setIfNotEmpty('body.mappings', $configurator->getDefaultMapping())
            ->get();

        ElasticClient::indices()
            ->create($payload);

        $this->info(sprintf(
            'The %s index was created!',
            $configurator->getName()
        ));
    }

    /**
     * Create an write alias.
     *
     * @return void
     */
    protected function createWriteAlias()
    {
        $configurator = $this->getIndexConfigurator();

        if (! in_array(Migratable::class, class_uses_recursive($configurator))) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->set('name', $configurator->getWriteAlias())
            ->get();

        ElasticClient::indices()
            ->putAlias($payload);

        $this->info(sprintf(
            'The %s alias for the %s index was created!',
            $configurator->getWriteAlias(),
            $configurator->getName()
        ));
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createIndex();

        $this->createWriteAlias();
    }
}
