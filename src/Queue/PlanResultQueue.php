<?php

namespace Mt\Queue;

use Mt\Lib\Script\Queue;

/**
 * 生成马甲结果
 * Class PlanResultQueue
 * @package Mt\Queue
 */
class PlanResultQueue extends Queue
{
    protected function _init()
    {
        $this->queue_type = self::QUEUE_TYPE_REDIS;
        $this->server_config = $this->getRedisServerConfig("redis/script");
        $this->json_handle = true;
    }
}