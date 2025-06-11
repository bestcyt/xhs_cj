<?php
/**
 * 马甲执行未找到评论区次数
 */

namespace Mt\Counter;

use Fw\Counter;
use Mt\Key\MussyModuleKey;
use Mt\Lib\Traits\CounterTrait;

class MajiaPlanNoCommentCounter
{
    use CounterTrait;

    private function __construct()
    {
        $this->counterType = Counter::COUNTER_TYPE_REDIS;
        $this->counterConfig = $this->getCounterConfig("redis/main");
        $this->ttl = 86400 * 7;
        $this->prefixKey = $this->getPrefixKey(MussyModuleKey::MAJIA_PLAN_NO_COMMENT);
    }
}
