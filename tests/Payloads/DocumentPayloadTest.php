<?php

namespace Novius\ScoutElastic\Test\Payloads;

use Novius\ScoutElastic\Payloads\DocumentPayload;
use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Test\Dependencies\Model;

class DocumentPayloadTest extends AbstractTestCase
{
    use Model;

    public function test_default()
    {
        $model = $this->mockModel();

        $payload = new DocumentPayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
                'id' => '',
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

        $payload = (new DocumentPayload($model))
            ->set('index', 'test_index')
            ->set('type', 'test_type')
            ->set('id', 2)
            ->set('body', []);

        $this->assertEquals(
            [
                'index' => 'foo',
                'id' => '',
                'body' => [],
            ],
            $payload->get()
        );
    }
}
