<?php

namespace Mt\Queue\Alert;

use Mt\Lib\Script\Queue;

/**
 * 邮件发送队列
 * Class AlertMailQueue
 * @package Mt\Queue\Alert
 */
class AlertMailQueue extends Queue
{
    protected function _init()
    {
        $this->queue_type = self::QUEUE_TYPE_REDIS;
        $this->server_config = $this->getRedisServerConfig("redis/script");
        $this->json_handle = true;
    }
}