<?php

namespace Mt\Lib\Traits;

use Fw\App;

/**
 * 缓存
 */
trait CacheTrait
{
    use \Fw\CacheTrait;

    protected function getCacheConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }

    protected function getPrefixKey($prefixKey)
    {
        return $prefixKey . ":";
    }

}