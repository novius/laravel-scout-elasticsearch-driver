<?php

namespace Novius\ScoutElastic\Console;

use Illuminate\Console\Command;
use Novius\ScoutElastic\Console\Features\HasConfigurator;
use Novius\ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexReindexCommand extends Command
{
    use HasConfigurator;
    use RequiresIndexConfiguratorArgument;

    protected $signature = 'elastic:reindex
                            {index-configurator : The index configurator class}';

    protected $description = 'Reindex an Elasticsearch index with 0 downtime';

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
            $this->call('elastic:create-index', [
                'index-configurator' => $this->argument('index-configurator'),
            ]);
        } else {
            $currentIndex = $this->findIndexNameByAlias($alias);
            $this->info(sprintf('An index already exists : %s. Create another.', $currentIndex));

            $this->call('elastic:create-index', [
                'index-configurator' => $this->argument('index-configurator'),
                '--populate' => 1,
            ]);
        }
    }
}
