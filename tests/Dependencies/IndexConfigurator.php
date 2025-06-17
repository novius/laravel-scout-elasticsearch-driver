<?php

namespace Novius\ScoutElastic\Test\Dependencies;

use Novius\ScoutElastic\IndexConfigurator as ElasticIndexConfigurator;

trait IndexConfigurator
{
    /**
     * @param  array  $params  Available parameters: name, settings, default_mapping, methods.
     * @return ElasticIndexConfigurator
     */
    public function mockIndexConfigurator(array $params = [])
    {
        $name = $params['name'] ?? 'test';

        $methods = array_merge($params['methods'] ?? [], [
            'getName',
            'getSettings',
            'getDefaultMapping',
        ]);

        $mock = $this->getMockBuilder(ElasticIndexConfigurator::class)
            ->onlyMethods($methods)->getMock();

        $mock->method('getName')
            ->willReturn($name);

        $mock->method('getSettings')
            ->willReturn($params['settings'] ?? []);

        $mock->method('getDefaultMapping')
            ->willReturn($params['default_mapping'] ?? []);

        return $mock;
    }
}
