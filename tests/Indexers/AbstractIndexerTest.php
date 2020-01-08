<?php

namespace Novius\ScoutElastic\Test\Indexers;

use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractIndexerTest extends AbstractTestCase
{
    use Model;

    /**
     * @var Collection
     */
    protected $models;

    protected function setUp(): void
    {
        parent::setUp();

        $this->models = new Collection([
            $this->mockModel([
                'key' => 'test_1',
                'trashed' => true,
                'searchable_array' => [
                    'name' => 'foo',
                ],
            ]),
            $this->mockModel([
                'key' => 'test_2',
                'trashed' => false,
                'searchable_array' => [
                    'name' => 'bar',
                ],
            ]),
            $this->mockModel([
                'key' => 'test_3',
                'trashed' => false,
                'searchable_array' => [],
            ]),
        ]);
    }
}
