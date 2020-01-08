<?php

namespace Novius\ScoutElastic\Test\Indexers;

use Novius\ScoutElastic\Indexers\BulkIndexer;
use Novius\ScoutElastic\Facades\ElasticClient;

class BulkIndexerTest extends AbstractIndexerTest
{
    public function testUpdateWithDisabledSoftDelete()
    {
        app('config')->set('scout.soft_delete', false);

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'body' => [
                    ['index' => ['_id' => 'test_1']],
                    ['name' => 'foo', 'type' => 'test'],
                    ['index' => ['_id' => 'test_2']],
                    ['name' => 'bar', 'type' => 'test'],
                    ['index' => ['_id' => 'test_3']],
                    ['type' => 'test'],
                ],
            ]);

        (new BulkIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithEnabledSoftDelete()
    {
        app('config')->set('scout.soft_delete', true);

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'body' => [
                    ['index' => ['_id' => 'test_1']],
                    ['type' => 'test', 'name' => 'foo', '__soft_deleted' => 1],
                    ['index' => ['_id' => 'test_2']],
                    ['type' => 'test', 'name' => 'bar', '__soft_deleted' => 0],
                    ['index' => ['_id' => 'test_3']],
                    ['type' => 'test', '__soft_deleted' => 0],
                ],
            ]);

        (new BulkIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithSpecifiedDocumentRefreshOption()
    {
        app('config')->set('scout_elastic.document_refresh', true);

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'refresh' => 'true',
                'body' => [
                    ['index' => ['_id' => 'test_1']],
                    ['type' => 'test', 'name' => 'foo'],
                    ['index' => ['_id' => 'test_2']],
                    ['type' => 'test', 'name' => 'bar'],
                    ['index' => ['_id' => 'test_3']],
                    ['type' => 'test'],
                ],
            ]);

        (new BulkIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDelete()
    {
        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'body' => [
                    ['delete' => ['_id' => 'test_1']],
                    ['delete' => ['_id' => 'test_2']],
                    ['delete' => ['_id' => 'test_3']],
                ],
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new BulkIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}
