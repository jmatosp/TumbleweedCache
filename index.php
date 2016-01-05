<?php

use JPinto\TumbleweedCache\CacheItemPoolFactory;
use JPinto\TumbleweedCache\Item;

require_once "vendor/autoload.php";

$cache = CacheItemPoolFactory::make();

$item = new Item('hello', get_class($cache), 30);

$cache->save($item);

var_dump($cache->getItem('hello')->get());
