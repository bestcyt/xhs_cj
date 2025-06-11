<?php

namespace Mt\Queue;

use Mt\Lib\Script\Queue;

/**
 * 异步任务
 * Class TaskQueue
 * @package Mt\Queue
 */
class TaskQueue extends Queue
{
    protected function _init()
    {
        $this->queue_type = self::QUEUE_TYPE_REDIS;
        $this->server_config = $this->getRedisServerConfig("redis/script");
        $this->json_handle = false;
    }
}