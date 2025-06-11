<?php

namespace Mt\Lib\Traits;

use Fw\App;

/**
 * 计数器
 */
trait CounterTrait
{
    use \Fw\CounterTrait;

    protected function getCounterConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }

    protected function getPrefixKey($prefixKey)
    {
        return $prefixKey . ":";
    }

}