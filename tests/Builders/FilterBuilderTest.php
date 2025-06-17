<?php

namespace Novius\ScoutElastic\Test\Builders;

use Novius\ScoutElastic\Builders\FilterBuilder;
use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\Model;

class FilterBuilderTest extends AbstractTestCase
{
    use Model;

    public function test_creation_with_soft_delete()
    {
        $builder = new FilterBuilder($this->mockModel(), null, true);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            '__soft_deleted' => 0,
                        ],
                    ],
                    [
                        'term' => [
                            'type' => 'test',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_creation_without_soft_delete()
    {
        $builder = new FilterBuilder($this->mockModel(), null, false);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            'type' => 'test',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_eq()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', 0)
            ->where('bar', '=', 1);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['term' => ['foo' => 0]],
                    ['term' => ['bar' => 1]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_not_eq()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '!=', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                ],
                'must_not' => [
                    ['term' => ['foo' => 'bar']],
                ],
            ],
            $builder->wheres
        );
    }

    public function test_where_gt()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['range' => ['foo' => ['gt' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_gte()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_lt()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '<', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['range' => ['foo' => ['lt' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_lte()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_in()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['terms' => ['foo' => [0, 1]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_not_in()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                ],
                'must_not' => [
                    ['terms' => ['foo' => [0, 1]]],
                ],
            ],
            $builder->wheres
        );
    }

    public function test_where_between()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_not_between()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                ],
                'must_not' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
            ],
            $builder->wheres
        );
    }

    public function test_where_exists()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereExists('foo');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['exists' => ['field' => 'foo']],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_not_exists()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotExists('foo');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                ],
                'must_not' => [
                    ['exists' => ['field' => 'foo']],
                ],
            ],
            $builder->wheres
        );
    }

    public function test_where_regexp()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereRegexp('foo', '.*')
            ->whereRegexp('bar', '^test.*', 'EMPTY|NONE');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['regexp' => ['foo' => ['value' => '.*', 'flags' => 'ALL']]],
                    ['regexp' => ['bar' => ['value' => '^test.*', 'flags' => 'EMPTY|NONE']]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_when()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->when(
                false,
                function (FilterBuilder $builder) {
                    return $builder->where('case0', 0);
                }
            )
            ->when(
                false,
                function (FilterBuilder $builder) {
                    return $builder->where('case1', 1);
                },
                function (FilterBuilder $builder) {
                    return $builder->where('case2', 2);
                }
            )
            ->when(
                true,
                function (FilterBuilder $builder) {
                    return $builder->where('case3', 3);
                }
            );

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['term' => ['case2' => 2]],
                    ['term' => ['case3' => 3]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_geo_distance()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoDistance('foo', [-20, 30], '10m');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['geo_distance' => ['distance' => '10m', 'foo' => [-20, 30]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_geo_bounding_box()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoBoundingBox('foo', ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['geo_bounding_box' => ['foo' => ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_geo_polygon()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoPolygon('foo', [[-70, 40], [-80, 30], [-90, 20]]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    ['geo_polygon' => ['foo' => ['points' => [[-70, 40], [-80, 30], [-90, 20]]]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_where_geo_shape()
    {
        $shape = [
            'type' => 'circle',
            'radius' => '1km',
            'coordinates' => [
                4.89994,
                52.37815,
            ],
        ];

        $relation = 'WITHIN';

        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoShape('foo', $shape, $relation);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    [
                        'geo_shape' => [
                            'foo' => [
                                'shape' => $shape,
                                'relation' => $relation,
                            ],
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_order_by()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->orderBy('foo')
            ->orderBy('bar', 'DESC');

        $this->assertEquals(
            [
                ['foo' => 'asc'],
                ['bar' => 'desc'],
            ],
            $builder->orders
        );
    }

    public function test_with()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->with('RelatedModel');

        $this->assertEquals(
            'RelatedModel',
            $builder->with
        );
    }

    public function test_from()
    {
        $builder = new FilterBuilder($this->mockModel());

        $this->assertEquals(
            0,
            $builder->offset
        );

        $builder->from(100);

        $this->assertEquals(
            100,
            $builder->offset
        );
    }

    public function test_collapse()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->collapse('foo');

        $this->assertEquals(
            'foo',
            $builder->collapse
        );
    }

    public function test_select()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->select(['foo', 'bar']);

        $this->assertEquals(
            ['foo', 'bar'],
            $builder->select
        );
    }

    public function test_with_trashed()
    {
        $builder = (new FilterBuilder($this->mockModel(), null, true))
            ->withTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function test_only_trashed()
    {
        $builder = (new FilterBuilder($this->mockModel(), null, true))
            ->onlyTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['type' => 'test']],
                    [
                        'term' => [
                            '__soft_deleted' => 1,
                        ],
                    ],
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }
}
