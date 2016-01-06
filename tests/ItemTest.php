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
}
