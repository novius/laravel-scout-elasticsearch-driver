<?php

namespace Novius\ScoutElastic\Test\Payloads;

use Novius\ScoutElastic\Payloads\IndexPayload;
use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\IndexConfigurator;

class IndexPayloadTest extends AbstractTestCase
{
    use IndexConfigurator;

    public function testDefault()
    {
        $indexConfigurator = $this->mockIndexConfigurator();
        $payload = new IndexPayload($indexConfigurator);

        $this->assertEquals(
            ['index' => 'test'],
            $payload->get()
        );
    }

    public function testSet()
    {
        $indexConfigurator = $this->mockIndexConfigurator([
            'name' => 'foo',
        ]);

        $payload = (new IndexPayload($indexConfigurator))
            ->set('index', 'bar')
            ->set('settings', ['key' => 'value']);

        $this->assertEquals(
            [
                'index' => 'foo',
                'settings' => ['key' => 'value'],
            ],
            $payload->get()
        );
    }
}
