<?php

namespace Novius\ScoutElastic\Payloads;

use Exception;
use Novius\ScoutElastic\IndexConfigurator;
use Novius\ScoutElastic\Payloads\Features\HasProtectedKeys;
use RuntimeException;

class IndexPayload extends RawPayload
{
    use HasProtectedKeys;

    /**
     * The protected keys.
     */
    protected array $protectedKeys = [
        'index',
    ];

    /**
     * The index configurator.
     */
    protected IndexConfigurator $indexConfigurator;

    /**
     * IndexPayload constructor.
     */
    public function __construct(IndexConfigurator $indexConfigurator, $isCreationPayload = false)
    {
        $this->indexConfigurator = $indexConfigurator;

        $this->payload['index'] = $indexConfigurator->getName($isCreationPayload);
    }

    /**
     * Use a specific index.
     */
    public function useIndex(string $indexName): static
    {
        $this->payload['index'] = $indexName;

        return $this;
    }

    /**
     * Use an alias.
     *
     *
     * @throws Exception
     */
    public function useAlias(string $alias): static
    {
        $aliasGetter = 'get'.ucfirst($alias).'Alias';

        if (! method_exists($this->indexConfigurator, $aliasGetter)) {
            throw new RuntimeException(sprintf(
                'The index configurator %s doesn\'t have getter for the %s alias.',
                get_class($this->indexConfigurator),
                $alias
            ));
        }

        $this->payload['index'] = call_user_func([$this->indexConfigurator, $aliasGetter]);

        return $this;
    }
}
