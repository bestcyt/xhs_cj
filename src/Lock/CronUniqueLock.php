<?php

namespace Mt\Lock;

use Mt\Lib\LockService;

/**
 * 定时任务唯一锁
 * Class CronUniqueLock
 * @package Mt\Lock
 */
class CronUniqueLock extends LockService
{
    protected function _init()
    {
        $this->server_config = $this->getServerConfig('redis/main');//redis对应配置
        $this->lock_default_expire_time = 60;//默认加锁时长
    }
}