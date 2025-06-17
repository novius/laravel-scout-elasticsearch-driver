<?php

namespace Novius\ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Novius\ScoutElastic\Searchable;
use RuntimeException;

class TypePayload extends IndexPayload
{
    /**
     * The model.
     */
    protected Model $model;

    /**
     * @throws Exception
     */
    public function __construct(Model $model)
    {
        if (! in_array(Searchable::class, class_uses_recursive($model), true)) {
            throw new RuntimeException(sprintf(
                'The %s model must use the %s trait.',
                get_class($model),
                Searchable::class
            ));
        }
        /** @var Model&Searchable $model */
        $this->model = $model;

        parent::__construct($model->getIndexConfigurator());

        $this->protectedKeys[] = 'type';
    }
}
