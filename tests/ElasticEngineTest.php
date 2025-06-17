<?php

namespace Novius\ScoutElastic\Test;

use Novius\ScoutElastic\Builders\FilterBuilder;
use Novius\ScoutElastic\Builders\SearchBuilder;
use Novius\ScoutElastic\ElasticEngine;
use Novius\ScoutElastic\Facades\ElasticClient;
use Novius\ScoutElastic\Test\Dependencies\Model;
use Novius\ScoutElastic\Test\Stubs\SearchRule;
use stdClass;

class ElasticEngineTest extends AbstractTestCase
{
    use Model;

    /**
     * @var ElasticEngine
     */
    private $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->engine = $this
            ->getMockBuilder(ElasticEngine::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    public function test_build_search_query_payload_collection()
    {
        $model = $this->mockModel();

        $searchBuilder = (new SearchBuilder($model, 'foo'))
            ->rule(SearchRule::class)
            ->rule(function (SearchBuilder $searchBuilder) {
                return [
                    'must' => [
                        'match' => [
                            'bar' => $searchBuilder->query,
                        ],
                    ],
                ];
            })
            ->select('title')
            ->select(['price', 'color'])
            ->where('id', '>', 20)
            ->orderBy('id', 'asc')
            ->collapse('brand')
            ->take(10)
            ->from(100);

        $payloadCollection = $this
            ->engine
            ->buildSearchQueryPayloadCollection($searchBuilder);

        $this->assertEquals(
            [
                [
                    'index' => 'test',
                    'body' => [
                        '_source' => [
                            'title',
                            'price',
                            'color',
                        ],
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'query_string' => [
                                        'query' => 'foo',
                                    ],
                                ],
                                'filter' => [
                                    ['term' => ['type' => 'test']],
                                    [
                                        'range' => [
                                            'id' => [
                                                'gt' => 20,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'highlight' => [
                            'fields' => [
                                'title' => [
                                    'type' => 'plain',
                                ],
                                'price' => [
                                    'type' => 'plain',
                                ],
                                'color' => [
                                    'type' => 'plain',
                                ],
                            ],
                        ],
                        'collapse' => [
                            'field' => 'brand',
                        ],
                        'sort' => [
                            [
                                'id' => 'asc',
                            ],
                        ],
                        'from' => 100,
                        'size' => 10,
                    ],
                ],
                [
                    'index' => 'test',
                    'body' => [
                        '_source' => [
                            'title',
                            'price',
                            'color',
                        ],
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'match' => [
                                        'bar' => 'foo',
                                    ],
                                ],
                                'filter' => [
                                    ['term' => ['type' => 'test']],
                                    [
                                        'range' => [
                                            'id' => [
                                                'gt' => 20,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'collapse' => [
                            'field' => 'brand',
                        ],
                        'sort' => [
                            [
                                'id' => 'asc',
                            ],
                        ],
                        'from' => 100,
                        'size' => 10,
                    ],
                ],
            ],
            $payloadCollection->all()
        );
    }

    public function test_build_filter_query_payload_collection()
    {
        $model = $this->mockModel();

        $filterBuilder = (new FilterBuilder($model))
            ->where('foo', 'bar')
            ->orderBy('foo', 'desc')
            ->take(1)
            ->from(30);

        $payloadCollection = $this
            ->engine
            ->buildSearchQueryPayloadCollection($filterBuilder);

        $this->assertEquals(
            [
                [
                    'index' => 'test',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'match_all' => new stdClass,
                                ],
                                'filter' => [
                                    ['term' => ['type' => 'test']],
                                    [
                                        'term' => [
                                            'foo' => 'bar',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'sort' => [
                            [
                                'foo' => 'desc',
                            ],
                        ],
                        'from' => 30,
                        'size' => 1,
                    ],
                ],
            ],
            $payloadCollection->all()
        );
    }

    public function test_count()
    {
        ElasticClient::shouldReceive('count')
            ->once()
            ->with([
                'index' => 'test',
                'body' => [
                    '_source' => [
                        'title',
                    ],
                    'query' => [
                        'bool' => [
                            'must' => [
                                'query_string' => [
                                    'query' => 'foo',
                                ],
                            ],
                            'filter' => [
                                ['term' => ['type' => 'test']],
                            ],
                        ],
                    ],
                ],
            ]);

        $model = $this->mockModel();

        $searchBuilder = (new SearchBuilder($model, 'foo'))
            ->rule(SearchRule::class)
            ->select('title');

        $this
            ->engine
            ->count($searchBuilder);

        $this->addToAssertionCount(1);
    }

    public function test_search_raw()
    {
        ElasticClient::shouldReceive('search')
            ->once()
            ->with([
                'index' => 'test',
                'body' => [
                    'query' => [
                        'match' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ]);

        $model = $this->mockModel();

        $query = [
            'query' => [
                'match' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $this
            ->engine
            ->searchRaw(
                $model,
                $query
            );

        $this->addToAssertionCount(1);
    }

    public function test_map_ids()
    {
        $results = [
            'hits' => [
                'hits' => [
                    ['_id' => 'test_1', '_source' => ['type' => 'test']],
                    ['_id' => 'test_2', '_source' => ['type' => 'test']],
                ],
            ],
        ];

        $this->assertEquals(
            [1, 2],
            $this->engine->mapIds($results)->all()
        );
    }

    public function test_map_without_trashed()
    {
        $this->markTestSkipped();

        $results = [
            'hits' => [
                'total' => 2,
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => [
                            'title' => 'foo',
                        ],
                    ],
                    [
                        '_id' => 2,
                        '_source' => [
                            'title' => 'bar',
                        ],
                    ],
                ],
            ],
        ];

        $model = $this->mockModel([
            'key' => 2,
            'methods' => [
                'usesSoftDelete',
                'newQuery',
                'whereIn',
                'get',
                'keyBy',
            ],
        ]);

        $model
            ->method('usesSoftDelete')
            ->willReturn(false);

        $model
            ->method('newQuery')
            ->willReturn($model);

        $model
            ->method('whereIn')
            ->willReturn($model);

        $model
            ->method('get')
            ->willReturn($model);

        $model
            ->method('keyBy')
            ->willReturn([
                2 => $model,
            ]);

        $builder = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            [$model],
            $this->engine->map($builder, $results, $model)->all()
        );
    }

    public function test_map_with_trashed()
    {
        $this->markTestSkipped();

        $results = [
            'hits' => [
                'total' => 2,
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => [
                            'title' => 'foo',
                        ],
                    ],
                    [
                        '_id' => 2,
                        '_source' => [
                            'title' => 'bar',
                        ],
                    ],
                ],
            ],
        ];

        $model = $this->mockModel([
            'key' => 2,
            'methods' => [
                'usesSoftDelete',
                'withTrashed',
                'whereIn',
                'get',
                'keyBy',
            ],
        ]);

        $model
            ->method('usesSoftDelete')
            ->willReturn(true);

        $model
            ->method('withTrashed')
            ->willReturn($model);

        $model
            ->method('whereIn')
            ->willReturn($model);

        $model
            ->method('get')
            ->willReturn($model);

        $model
            ->method('keyBy')
            ->willReturn([
                2 => $model,
            ]);

        $builder = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            [$model],
            $this->engine->map($builder, $results, $model)->all()
        );
    }

    public function test_get_total_count()
    {
        $results = [
            'hits' => [
                'total' => [
                    'value' => 100,
                    'relation' => 'eq',
                ],
            ],
        ];

        $this->assertEquals(
            100,
            $this->engine->getTotalCount($results)
        );
    }
}
