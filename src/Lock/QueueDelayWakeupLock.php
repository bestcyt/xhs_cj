<?php

namespace Mt\Lock;

use Mt\Lib\LockService;

/**
 * 队列延时 恢复 锁
 * Class QueueDelayWakeupLock
 * @package Mt\Lock
 */
class QueueDelayWakeupLock extends LockService
{
    protected function _init()
    {
        $this->server_config = $this->getServerConfig('redis/main');//redis对应配置
        $this->lock_default_expire_time = 60;//默认加锁时长
    }
}