<?php
/**
 * 计数类
 */

namespace Mt\Counter\Error;

use Fw\Counter;
use Mt\Key\ErrorModuleKey;
use Mt\Lib\Traits\CounterTrait;

class ErrorDbCounter
{
    use CounterTrait;

    private function __construct()
    {
        $this->counterType = Counter::COUNTER_TYPE_REDIS;
        $this->counterConfig = $this->getCounterConfig("redis/main");
        $this->ttl = 60;
        $this->prefixKey = $this->getPrefixKey(ErrorModuleKey::DB_ERROR);
    }
}
