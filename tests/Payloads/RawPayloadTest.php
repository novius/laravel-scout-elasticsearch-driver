<?php

namespace Novius\ScoutElastic\Test\Payloads;

use Novius\ScoutElastic\Payloads\RawPayload;
use Novius\ScoutElastic\Test\AbstractTestCase;

class RawPayloadTest extends AbstractTestCase
{
    public function test_set()
    {
        $payload = (new RawPayload)
            ->set('foo.bar', 10);

        $this->assertEquals(
            ['foo' => ['bar' => 10]],
            $payload->get()
        );
    }

    public function test_set_if_not_empty()
    {
        $payload = (new RawPayload)
            ->setIfNotEmpty('null', null)
            ->setIfNotEmpty('false', false)
            ->setIfNotEmpty('zero', 0)
            ->setIfNotEmpty('empty_array', [])
            ->setIfNotEmpty('empty_string', '')
            ->setIfNotEmpty('foo', 'bar');

        $this->assertEquals(
            ['foo' => 'bar'],
            $payload->get()
        );
    }

    public function test_set_if_not_null()
    {
        $payload = (new RawPayload)
            ->setIfNotNull('null', null)
            ->setIfNotNull('false', false)
            ->setIfNotNull('zero', 0)
            ->setIfNotNull('empty_array', [])
            ->setIfNotNull('empty_string', '')
            ->setIfNotNull('foo', 'bar');

        $this->assertEquals(
            [
                'false' => false,
                'zero' => 0,
                'empty_array' => [],
                'empty_string' => '',
                'foo' => 'bar',
            ],
            $payload->get()
        );
    }

    public function test_has()
    {
        $payload = (new RawPayload)
            ->set('foo.bar', 100);

        $this->assertTrue($payload->has('foo'));
        $this->assertTrue($payload->has('foo.bar'));
        $this->assertFalse($payload->has('not_exist'));
    }

    public function test_add()
    {
        $payload = (new RawPayload)
            ->set('foo', 0)
            ->add('foo', 1);

        $this->assertEquals(
            ['foo' => [0, 1]],
            $payload->get()
        );
    }

    public function test_add_if_not_empty()
    {
        $payload = (new RawPayload)
            ->addIfNotEmpty('foo', 0)
            ->addIfNotEmpty('foo', 1);

        $this->assertEquals(
            ['foo' => [1]],
            $payload->get()
        );
    }

    public function test_get()
    {
        $payload = (new RawPayload)
            ->set('foo.bar', 0);

        $this->assertEquals(
            ['bar' => 0],
            $payload->get('foo')
        );

        $this->assertEquals(
            ['foo' => ['bar' => 0]],
            $payload->get()
        );

        $this->assertEquals(
            ['value' => 1],
            $payload->get('default', ['value' => 1])
        );
    }
}
