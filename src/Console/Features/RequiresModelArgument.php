<?php

namespace Novius\ScoutElastic\Console\Features;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Novius\ScoutElastic\Searchable;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @mixin Command
 */
trait RequiresModelArgument
{
    /**
     * Get the model.
     */
    protected function getModel(): Model
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (
            ! ($modelInstance instanceof Model) ||
            ! in_array(Searchable::class, class_uses_recursive($modelClass))
        ) {
            throw new InvalidArgumentException(sprintf(
                'The %s class must extend %s and use the %s trait.',
                $modelClass,
                Model::class,
                Searchable::class
            ));
        }

        return $modelInstance;
    }

    /**
     * Get the arguments.
     */
    protected function getArguments(): array
    {
        return [
            [
                'model',
                InputArgument::REQUIRED,
                'The model class',
            ],
        ];
    }
}
