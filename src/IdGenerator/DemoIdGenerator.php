<?php

namespace Mt\IdGenerator;

use Mt\Lib\IdGenerator;

class DemoIdGenerator extends IdGenerator
{
    protected function _init()
    {
        $this->redis_config = $this->getRedisConfig('redis/main');//redis配置
        $this->work_id = 1; //业务id
        $this->server_id = 1;//服务器id
        $this->offsetTime = "2024-05-01 00:00:00";//基准时间(一旦设定不能更改,否则可能导致重复)
    }
}