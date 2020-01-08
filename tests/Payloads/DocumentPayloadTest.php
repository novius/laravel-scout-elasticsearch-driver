<?php

namespace Novius\ScoutElastic\Test\Payloads;

use Novius\ScoutElastic\Test\AbstractTestCase;
use Novius\ScoutElastic\Payloads\DocumentPayload;
use Novius\ScoutElastic\Test\Dependencies\Model;

class DocumentPayloadTest extends AbstractTestCase
{
    use Model;

    public function testDefault()
    {
        $model = $this->mockModel();

        $payload = new DocumentPayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
                'id' => 1,
            ],
            $payload->get()
        );
    }

    public function testSet()
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
                'id' => 1,
                'body' => [],
            ],
            $payload->get()
        );
    }
}
