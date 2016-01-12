<?php

namespace JPinto\TumbleweedCache;

use DateTimeInterface;

interface CacheItemObserverInterface
{
    public function update(CacheItemSubjectInterface $cacheItemSubject, DateTimeInterface $expiresAt);
}