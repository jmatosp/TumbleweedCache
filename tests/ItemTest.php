<?php

use JPinto\TumbleweedCache\Item;

class ItemTest extends PHPUnit_Framework_TestCase
{
    public function testCreateNullValue()
    {
        $item = new Item('my_key');
        $this->assertNull($item->get());
    }

    public function testKeyUntouched()
    {
        $item = new Item('my_key');
        $this->assertSame('my_key', $item->getKey());
    }

    public function testNewItemIsCacheMiss()
    {
        $item = new Item('my_key');
        $this->assertFalse($item->isHit());
    }

    public function testSetString()
    {
        $item = new Item('my_key', 'value');
        $this->assertSame('value', $item->get());
    }

    public function testSetInt()
    {
        $item = new Item('my_key', 1);
        $this->assertSame(1, $item->get());
    }

    public function testSetArray()
    {
        $item = new Item('my_key', [1 => 'hello', 'world' => 2]);
        $this->assertSame([1 => 'hello', 'world' => 2], $item->get());
    }

    public function testSetObject()
    {
        $item = new Item('my_key', new stdClass());
        $this->assertEquals(new stdClass(), $item->get());
    }
}
