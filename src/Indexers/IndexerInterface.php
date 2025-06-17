<?php

namespace Novius\ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
    /**
     * Update documents.
     */
    public function update(Collection $models): array;

    /**
     * Delete documents.
     */
    public function delete(Collection $models): array;
}
