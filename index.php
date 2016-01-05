<?php

use JPinto\TumbleweedCache\CacheItemPoolFactory;
use JPinto\TumbleweedCache\Item;

require_once "vendor/autoload.php";

$cache = CacheItemPoolFactory::make();

$object = new stdClass();
$object->bla = 1;

$item = new Item('hello', $object, 30);

$cache->save($item);

var_dump($cache->getItem('hello')->get());
