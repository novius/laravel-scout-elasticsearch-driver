<?php

namespace Novius\ScoutElastic\Test\Dependencies;

use Novius\ScoutElastic\Searchable;
use Novius\ScoutElastic\Test\Stubs\Model as StubModel;

trait Model
{
    use IndexConfigurator;

    /**
     * @param  array  $params  Available parameters: key, searchable_as, searchable_array, index_configurator, methods.
     * @return Searchable
     */
    public function mockModel(array $params = [])
    {
        $methods = array_merge(
            $params['methods'] ?? [],
            [
                'getKey',
                'getScoutKey',
                'trashed',
                'searchableAs',
                'toSearchableArray',
                'getIndexConfigurator',
            ]
        );

        $mock = $this
            ->getMockBuilder(StubModel::class)
            ->onlyMethods($methods)
            ->getMock();

        $mock
            ->method('getKey')
            ->willReturn($params['key'] ?? 1);

        $mock
            ->method('getScoutKey')
            ->willReturn($params['key'] ?? '');

        $mock
            ->method('trashed')
            ->willReturn($params['trashed'] ?? false);

        $mock
            ->method('searchableAs')
            ->willReturn($params['searchable_as'] ?? 'test');

        $mock
            ->method('toSearchableArray')
            ->willReturn($params['searchable_array'] ?? []);

        $mock
            ->method('getIndexConfigurator')
            ->willReturn($params['index_configurator'] ?? $this->mockIndexConfigurator());

        return $mock;
    }
}
