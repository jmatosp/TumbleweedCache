<?php

namespace JPinto\TumbleweedCache;

interface CacheItemSubjectInterface
{
    public function attach(CacheItemObserverInterface $observer);
    public function detach(CacheItemObserverInterface $observer);
    public function notify();
}
