<?php

namespace Novius\ScoutElastic\Console;

use Illuminate\Console\Command;
use Novius\ScoutElastic\Console\Features\HasConfigurator;
use Novius\ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Payloads\RawPayload;

class ElasticIndexDropCommand extends Command
{
    use HasConfigurator;
    use RequiresIndexConfiguratorArgument;

    protected $signature = 'elastic:drop-index
                            {index-configurator : The index configurator class}';

    protected $description = 'Drop an Elasticsearch index';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->configurator = $this->getIndexConfigurator();
        $alias = $this->configurator->getName();

        if (! $this->aliasAlreadyExists()) {
            $this->error(sprintf('There is no index with alias "%s".', $alias));

            return;
        }

        $indexName = $this->findIndexNameByAlias($this->configurator->getName());

        if (! $this->confirm(sprintf('This command will remove "%s" index. Do you wish to continue?', $alias))) {
            return;
        }

        $payload = (new RawPayload())
            ->set('index', $indexName)
            ->get();

        ElasticClient::indices()
            ->delete($payload);

        $this->info(sprintf(
            'The index %s was deleted!',
            $alias
        ));
    }
}
