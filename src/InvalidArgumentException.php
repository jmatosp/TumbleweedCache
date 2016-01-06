<?php

namespace JPinto\TumbleweedCache;

use Exception;

class InvalidArgumentException extends Exception implements \Psr\Cache\InvalidArgumentException
{
}
