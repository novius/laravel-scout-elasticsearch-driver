<?php

namespace Novius\ScoutElastic\Console;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
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
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function handle(): void
    {
        $this->configurator = $this->getIndexConfigurator();
        $alias = $this->configurator->getName();
        $this->info(sprintf('Searching for existing alias : %s.', $alias));

        if (! $this->aliasAlreadyExists()) {
            $this->info(sprintf('No index found for alias : %s.', $alias));
        } else {
            $currentIndex = $this->findIndexNameByAlias($alias);
            $this->info(sprintf('An index already exists : %s. Create another.', $currentIndex));
        }

        $this->call('elastic:create-index', [
            'index-configurator' => $this->argument('index-configurator'),
            '--populate' => 1,
        ]);
    }
}
