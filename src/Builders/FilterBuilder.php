<?php

namespace Novius\ScoutElastic\Builders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;
use Novius\ScoutElastic\Searchable;

class FilterBuilder extends Builder
{
    /** @var Model&Searchable */
    public $model;

    /**
     * The condition array.
     *
     * @var array
     */
    public $wheres = [
        'must' => [],
        'must_not' => [],
    ];

    /**
     * The with array.
     */
    public array|string|null $with;

    /**
     * The offset.
     */
    public ?int $offset = null;

    /**
     * The collapse parameter.
     */
    public ?string $collapse = null;

    /**
     * The select array.
     */
    public array $select = [];

    /**
     * FilterBuilder constructor.
     *
     * @param  callable|null  $callback
     * @param  bool  $softDelete
     * @return void
     */
    public function __construct(Model $model, $callback = null, $softDelete = false)
    {
        /** @var Model&Searchable $model */
        $this->model = $model;
        $this->callback = $callback;

        if ($softDelete) {
            $this->wheres['must'][] = [
                'term' => [
                    '__soft_deleted' => 0,
                ],
            ];
        }

        $this->wheres['must'][] = [
            'term' => [
                'type' => $model->searchableAs(),
            ],
        ];
    }

    /**
     * Add a where condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Term query
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     *
     * @param  string  $field  Field name
     * @param  mixed  $value  Scalar value or an array
     */
    public function where($field, $value): static
    {
        $args = func_get_args();

        if (count($args) === 3) {
            [$field, $operator, $value] = $args;
        } else {
            $operator = '=';
        }

        switch ($operator) {
            case '=':
                $this->wheres['must'][] = [
                    'term' => [
                        $field => $value,
                    ],
                ];

                break;

            case '>':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gt' => $value,
                        ],
                    ],
                ];

                break;

            case '<':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lt' => $value,
                        ],
                    ],
                ];

                break;

            case '>=':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gte' => $value,
                        ],
                    ],
                ];

                break;

            case '<=':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lte' => $value,
                        ],
                    ],
                ];

                break;

            case '!=':
            case '<>':
                $this->wheres['must_not'][] = [
                    'term' => [
                        $field => $value,
                    ],
                ];

                break;
        }

        return $this;
    }

    /**
     * Add a whereIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array|Arrayable  $values
     */
    public function whereIn($field, $values): static
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres['must'][] = [
            'terms' => [
                $field => $values,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereNotIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array|Arrayable  $values
     */
    public function whereNotIn($field, $values): static
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres['must_not'][] = [
            'terms' => [
                $field => $values,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     */
    public function whereBetween(string $field, array $value): static
    {
        $this->wheres['must'][] = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a whereNotBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     */
    public function whereNotBetween(string $field, array $value): static
    {
        $this->wheres['must_not'][] = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a whereExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     */
    public function whereExists(string $field): static
    {
        $this->wheres['must'][] = [
            'exists' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereNotExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     */
    public function whereNotExists(string $field): static
    {
        $this->wheres['must_not'][] = [
            'exists' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereRegexp condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html Regexp query
     */
    public function whereRegexp(string $field, string $value, string $flags = 'ALL'): static
    {
        $this->wheres['must'][] = [
            'regexp' => [
                $field => [
                    'value' => $value,
                    'flags' => $flags,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a whereGeoDistance condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html Geo distance query
     */
    public function whereGeoDistance(string $field, string|array $value, int|string $distance): static
    {
        $this->wheres['must'][] = [
            'geo_distance' => [
                'distance' => $distance,
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereGeoBoundingBox condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html Geo bounding box query
     */
    public function whereGeoBoundingBox(string $field, array $value): static
    {
        $this->wheres['must'][] = [
            'geo_bounding_box' => [
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * Add a whereGeoPolygon condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html Geo polygon query
     */
    public function whereGeoPolygon(string $field, array $points): static
    {
        $this->wheres['must'][] = [
            'geo_polygon' => [
                $field => [
                    'points' => $points,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a whereGeoShape condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html Querying Geo Shapes
     */
    public function whereGeoShape(string $field, array $shape, string $relation = 'INTERSECTS'): static
    {
        $this->wheres['must'][] = [
            'geo_shape' => [
                $field => [
                    'shape' => $shape,
                    'relation' => $relation,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a orderBy clause.
     *
     * @param  string  $column
     * @param  string  $direction
     */
    public function orderBy($column, $direction = 'asc'): static
    {
        $this->orders[] = [
            $column => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Explain the request.
     */
    public function explain(): array
    {
        return $this
            ->engine()
            ->explain($this);
    }

    /**
     * Profile the request.
     */
    public function profile(): array
    {
        return $this
            ->engine()
            ->profile($this);
    }

    /**
     * Build the payload.
     */
    public function buildPayload(): array
    {
        return $this
            ->engine()
            ->buildSearchQueryPayloadCollection($this);
    }

    /**
     * Eager load some some relations.
     */
    public function with(array|string $relations): static
    {
        $this->with = $relations;

        return $this;
    }

    /**
     * Set the query offset.
     */
    public function from(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function get(): Collection
    {
        $collection = parent::get();

        if (isset($this->with) && $collection->count() > 0) {
            $collection->load($this->with);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = parent::paginate($perPage, $pageName, $page);

        if (isset($this->with) && $paginator->total() > 0) {
            /** @var Collection $collection */
            $collection = $paginator->getCollection();
            $collection->load($this->with);
        }

        return $paginator;
    }

    /**
     * Collapse by a field.
     */
    public function collapse(string $field): static
    {
        $this->collapse = $field;

        return $this;
    }

    /**
     * Select one or many fields.
     */
    public function select($fields): static
    {
        $this->select = array_merge(
            $this->select,
            Arr::wrap($fields)
        );

        return $this;
    }

    /**
     * Get the count.
     */
    public function count(): int
    {
        return $this
            ->engine()
            ->count($this);
    }

    /**
     * {@inheritdoc}
     */
    public function withTrashed()
    {
        $this->wheres['must'] = collect($this->wheres['must'])
            ->filter(function ($item) {
                return Arr::get($item, 'term.__soft_deleted') !== 0;
            })
            ->values()
            ->all();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onlyTrashed()
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['must'][] = ['term' => ['__soft_deleted' => 1]];
        });
    }
}
