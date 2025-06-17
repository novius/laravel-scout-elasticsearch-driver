<?php

namespace Novius\ScoutElastic;

use Novius\ScoutElastic\Builders\SearchBuilder;

class SearchRule
{
    protected SearchBuilder $builder;

    /**
     * SearchRule constructor.
     *
     * @return void
     */
    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Check if this is applicable.
     */
    public function isApplicable(): bool
    {
        return true;
    }

    /**
     * Build the highlight payload.
     */
    public function buildHighlightPayload() {}

    /**
     * Build the query payload.
     */
    public function buildQueryPayload(): array
    {
        return [
            'must' => [
                'query_string' => [
                    'query' => $this->builder->query,
                ],
            ],
        ];
    }
}
