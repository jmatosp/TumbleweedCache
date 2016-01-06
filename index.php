<?php

use JPinto\TumbleweedCache\CacheFactory;

require_once "vendor/autoload.php";

$cache = CacheFactory::make(CacheFactory::MEMORY);

$item = $cache->getItem('hello');
$item->set('world');
$cache->save($item);

var_dump($cache->getItem('hello')->get());
