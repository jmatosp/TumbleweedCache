<?php

use JPinto\TumbleweedCache\Item;

class ItemTest extends PHPUnit_Framework_TestCase
{
    public function testCreateNullValue()
    {
        $item = new Item('my_key');
        $this->assertNull($item->get());
    }

    public function testNewItemIsCacheMiss()
    {
        $item = new Item('my_key');
        $this->assertFalse($item->isHit());
    }

    public function testSetString()
    {
        $item = new Item('my_key');
        $item->set('value');
        $this->assertSame('value', $item->get());
    }

    public function testSetInt()
    {
        $item = new Item('my_key');
        $item->set(1);
        $this->assertSame(1, $item->get());
    }

    public function testSetArray()
    {
        $item = new Item('my_key');
        $item->set([1 => 'hello', 'world' => 2]);
        $this->assertSame([1 => 'hello', 'world' => 2], $item->get());
    }

    public function testSetObject()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $this->assertEquals(new stdClass(), $item->get());
    }

    public function testSetOverwriteValue()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $item->set('overwrite');
        $this->assertSame('overwrite', $item->get());
    }

    public function testConstructorExpiresAfterSeconds()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $item->expiresAfter(10);
        $this->assertTrue($item->isHit());
    }

    public function testExpiresAfterDateInterval()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $tomorrow = (new DateTime('now'))->add(DateInterval::createFromDateString('1 day'));
        $item->expiresAfter($tomorrow);
        $this->assertTrue($item->isHit());
    }

    public function testExpiresAtTomorrowIsHit()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $tomorrow = (new DateTime('now'))->add(DateInterval::createFromDateString('1 day'));
        $item->expiresAt($tomorrow);
        $this->assertTrue($item->isHit());
    }

    public function testExpiresAtYesterdayIsNotHit()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $yesterday = (new DateTime('now'))->add(DateInterval::createFromDateString('-1 day'));
        $item->expiresAt($yesterday);
        $this->assertFalse($item->isHit());
    }

    public function testExpiresAfterIsHit()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $item->expiresAfter(60);
        $this->assertTrue($item->isHit());
    }

    public function testExpiresAfter0IsNotHit()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $item->expiresAfter(0);
        $this->assertFalse($item->isHit());
    }

    public function testExpiresAfterDefaultIsHit()
    {
        $item = new Item('my_key');
        $item->set(new stdClass());
        $item->expiresAfter(null);
        $this->assertTrue($item->isHit());
    }
}
