<?php

namespace Novius\ScoutElastic\Console\Features;

use InvalidArgumentException;
use Novius\ScoutElastic\IndexConfigurator;

trait RequiresIndexConfiguratorArgument
{
    /**
     * Get the index configurator.
     *
     * @return \ScoutElastic\IndexConfigurator
     */
    protected function getIndexConfigurator()
    {
        $configuratorClass = trim($this->argument('index-configurator'));

        $configuratorInstance = new $configuratorClass();

        if (! ($configuratorInstance instanceof IndexConfigurator)) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $configuratorClass,
                IndexConfigurator::class
            ));
        }

        return new $configuratorClass();
    }
}
