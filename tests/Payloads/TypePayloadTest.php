<?php

namespace Novius\ScoutElastic\Test\Payloads;

use Novius\ScoutElastic\Payloads\TypePayload;
use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\Model;

class TypePayloadTest extends AbstractTestCase
{
    use Model;

    public function test_default()
    {
        $model = $this->mockModel();
        $payload = new TypePayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
            ],
            $payload->get()
        );
    }

    public function test_set()
    {
        $indexConfigurator = $this->mockIndexConfigurator([
            'name' => 'foo',
        ]);

        $model = $this->mockModel([
            'searchable_as' => 'bar',
            'index_configurator' => $indexConfigurator,
        ]);

        $payload = (new TypePayload($model))
            ->set('index', 'test_index')
            ->set('type', 'test_type')
            ->set('body', []);

        $this->assertEquals(
            [
                'index' => 'foo',
                'body' => [],
            ],
            $payload->get()
        );
    }
}
