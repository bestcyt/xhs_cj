<?php
/**
 * ip归属地
 */

namespace Mt\Cache;

use Fw\Cache;
use Mt\Key\MussyModuleKey;
use Mt\Lib\Traits\CacheTrait;

class IpAddressCache
{
    use CacheTrait;

    private function __construct()
    {
        $this->cacheType = Cache::CACHE_TYPE_REDIS;
        $this->cacheConfig = $this->getCacheConfig('redis/main');
        $this->useSerialize = true;
        $this->ttl = 86400 * 30;
        $this->prefixKey = $this->getPrefixKey(MussyModuleKey::IP_ADDRESS);
        $this->useLevelOneCache = false;//开启一级缓存(变量缓存,不穿透到缓存层)
    }

}
