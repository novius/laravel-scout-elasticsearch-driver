<?php

namespace Novius\ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Novius\ScoutElastic\Searchable;
use RuntimeException;

class DocumentPayload extends TypePayload
{
    /**
     * DocumentPayload constructor.
     *
     * @return void
     *
     * @throws Exception
     */
    public function __construct(Model $model)
    {
        /** @var Model&Searchable $model */
        if ($model->getScoutKey() === null) {
            throw new RuntimeException(sprintf(
                'The key value must be set to construct a payload for the %s instance.',
                get_class($model)
            ));
        }

        parent::__construct($model);

        $this->payload['id'] = $model->getScoutKey();
        $this->protectedKeys[] = 'id';
    }
}
