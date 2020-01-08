<?php

namespace Novius\ScoutElastic\Test\Indexers;

use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Indexers\SingleIndexer;

class SingleIndexerTest extends AbstractIndexerTest
{
    public function testUpdateWithDisabledSoftDelete()
    {
        app('config')->set('scout.soft_delete', false);

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_1',
                'body' => [
                    'type' => 'test',
                    'name' => 'foo',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_2',
                'body' => [
                    'type' => 'test',
                    'name' => 'bar',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_3',
                'body' => [
                    'type' => 'test',
                ],
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithEnabledSoftDelete()
    {
        app('config')->set('scout.soft_delete', true);

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_1',
                'body' => [
                    'type' => 'test',
                    'name' => 'foo',
                    '__soft_deleted' => 1,
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_2',
                'body' => [
                    'type' => 'test',
                    'name' => 'bar',
                    '__soft_deleted' => 0,
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_3',
                'body' => [
                    'type' => 'test',
                    '__soft_deleted' => 0,
                ],
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithSpecifiedDocumentRefreshOption()
    {
        app('config')->set('scout.soft_delete', false);
        app('config')->set('scout_elastic.document_refresh', true);

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'refresh' => 'true',
                'id' => 'test_1',
                'body' => [
                    'type' => 'test',
                    'name' => 'foo',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'refresh' => 'true',
                'id' => 'test_2',
                'body' => [
                    'type' => 'test',
                    'name' => 'bar',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'refresh' => 'true',
                'id' => 'test_3',
                'body' => [
                    'type' => 'test',
                ],
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDelete()
    {
        ElasticClient
            ::shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_1',
                'client' => [
                    'ignore' => 404,
                ],
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_2',
                'client' => [
                    'ignore' => 404,
                ],
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'id' => 'test_3',
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new SingleIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}
