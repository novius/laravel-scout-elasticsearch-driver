<?php

namespace Novius\ScoutElastic;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable as SourceSearchable;
use Novius\ScoutElastic\Builders\FilterBuilder;
use Novius\ScoutElastic\Builders\SearchBuilder;
use RuntimeException;

/**
 * @mixin Model
 */
trait Searchable
{
    use SourceSearchable {
        SourceSearchable::bootSearchable as sourceBootSearchable;
        SourceSearchable::getScoutKeyName as sourceGetScoutKeyName;
    }

    /**
     * The highligths.
     */
    private ?Highlight $highlight = null;

    /**
     * Defines if the model is searchable.
     */
    protected static bool $isSearchableTraitBooted = false;

    /**
     * Boot the trait.
     */
    public static function bootSearchable(): void
    {
        if (static::$isSearchableTraitBooted) {
            return;
        }

        self::sourceBootSearchable();

        static::$isSearchableTraitBooted = true;
    }

    /**
     * Get the index configurator.
     *
     *
     * @throws Exception
     */
    public function getIndexConfigurator(): IndexConfigurator
    {
        static $indexConfigurator;

        if (! $indexConfigurator) {
            if (! isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
                throw new RuntimeException(sprintf(
                    'An index configurator for the %s model is not specified.',
                    __CLASS__
                ));
            }

            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
        }

        return $indexConfigurator;
    }

    /**
     * Get the search rules.
     */
    public function getSearchRules(): array
    {
        return isset($this->searchRules) && count($this->searchRules) > 0 ?
            $this->searchRules : [SearchRule::class];
    }

    /**
     * Execute the search.
     *
     * @param  string  $query
     * @param  callable|null  $callback
     */
    public static function search($query, $callback = null): SearchBuilder|FilterBuilder
    {
        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        if ($query === '*') {
            return new FilterBuilder(new static, $callback, $softDelete);
        }

        return new SearchBuilder(new static, $query, $callback, $softDelete);
    }

    /**
     * Execute a raw search.
     */
    public static function searchRaw(array $query): array
    {
        $model = new static;

        return $model->searchableUsing()
            ->searchRaw($model, $query)->asArray();
    }

    /**
     * Set the highlight attribute.
     */
    public function setHighlightAttribute(Highlight $value): void
    {
        $this->highlight = $value;
    }

    /**
     * Get the highlight attribute.
     */
    public function getHighlightAttribute(): ?Highlight
    {
        return $this->highlight;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getScoutKey(): string
    {
        return $this->searchableAs().'_'.$this->getKey();
    }
}
