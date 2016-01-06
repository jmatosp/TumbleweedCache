<?php

use JPinto\TumbleweedCache\CacheItemPoolFactory;
use JPinto\TumbleweedCache\Item;

require_once "vendor/autoload.php";

$cache = CacheItemPoolFactory::make(CacheItemPoolFactory::MEMORY);

$item = $cache->getItem('hello');
$item->set('world');
$cache->save($item);

var_dump($cache->getItem('hello')->get());
