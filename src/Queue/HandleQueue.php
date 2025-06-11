<?php

namespace Mt\Queue;

use Mt\Lib\Script\Queue;

/**
 * 操作下发队列
 * Class HandleQueue
 * @package Mt\Queue
 */
class HandleQueue extends Queue
{
    protected function _init()
    {
        $this->queue_type = self::QUEUE_TYPE_REDIS;
        $this->server_config = $this->getRedisServerConfig("redis/script");
        $this->json_handle = true;
    }
}