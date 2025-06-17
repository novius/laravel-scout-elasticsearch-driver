<?php

namespace Novius\ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class IndexConfiguratorMakeCommand extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'make:index-configurator';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new Elasticsearch index configurator';

    /**
     * {@inheritdoc}
     */
    protected $type = 'Configurator';

    /**
     * {@inheritdoc}
     */
    public function getStub(): string
    {
        return __DIR__.'/stubs/index_configurator.stub';
    }
}
