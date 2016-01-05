<?php

use JPinto\TumbleweedCache\Item;
use JPinto\TumbleweedCache\APCuCacheItemPool;

require_once "vendor/autoload.php";

$cache = new APCuCacheItemPool();

$item = new Item('hello', new stdClass(), 30);

$cache->save($item);

var_dump($cache->getItem('hello')->get());
