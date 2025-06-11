<?php

namespace Mt\Queue\Alert;

use Mt\Lib\Script\Queue;

/**
 * 飞书机器人发送队列
 * Class AlertFeishuQueue
 * @package Mt\Queue\Alert
 */
class AlertFeishuQueue extends Queue
{
    protected function _init()
    {
        $this->queue_type = self::QUEUE_TYPE_REDIS;
        $this->server_config = $this->getRedisServerConfig("redis/script");
        $this->json_handle = true;
    }
}