<?php
/**
 * 任务结果
 */

namespace Mt\Cache;

use Fw\Cache;
use Mt\Key\TaskModuleKey;
use Mt\Lib\Traits\CacheTrait;

class TaskResultCache
{
    use CacheTrait;

    private function __construct()
    {
        $this->cacheType = Cache::CACHE_TYPE_REDIS;
        $this->cacheConfig = $this->getCacheConfig('redis/main');
        $this->useSerialize = true;
        $this->ttl = 1800;
        $this->prefixKey = $this->getPrefixKey(TaskModuleKey::RESULT);
        $this->useLevelOneCache = false;//开启一级缓存(变量缓存,不穿透到缓存层)
    }

}
