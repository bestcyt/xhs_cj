<?php

namespace Mt\Lib\Script;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Redis;
use Mt\Key\ModuleKey;

/**
 * 队列  忽略redis或者卡夫卡,方便以后拓展
 * Class Queue
 * @package Mt\Lib\Script
 */
abstract class Queue
{
    use InstanceTrait;
    const QUEUE_TYPE_REDIS = "redis";
    protected $server_config = [];//主要的配置
    protected $queue_type = self::QUEUE_TYPE_REDIS;
    //redis 设置 redis_key
    protected $redis_key = "";//如果是redis则填写这个
    //存入 取出 是否json处理
    protected $json_handle = false;
    //存入重试次数,默认1次
    protected $produce_try_times = 1;
    /**
     * @var $queue_object Redis
     */
    protected $queue_object = null;
    protected $initialized = false;

    protected $machine_id = 0;

    protected function __construct($machine_id = 0)
    {
        $this->machine_id = $machine_id;
        $this->_init();
        $this->_initServer();
        $this->_initMachineId();
    }

    /**
     *
     * @param string|array $value 入队列的值(目前只支持字符串和数组)
     * @param int $consume_time 指定消费时间戳(如在 1010234598 的时候进行消费)
     * @param boolean $forceProduct 灰度的时候是否强制写到线上
     * @return bool
     */
    public function produceAt($value, $consume_time, $forceProduct = false)
    {
        if (!$this->initialized || !$consume_time) {
            return false;
        }
        //在指定的时刻才进行消费,放入db暂存
        $delay_time = $consume_time - time();
        if ($delay_time < 0) {
            return false;
        }
        return $this->produce($value, $delay_time, $forceProduct);
    }

    /**
     * @param string|array $value 入队列的值(目前只支持字符串和数组)
     * @param int $delay_time 延迟消费时间(比如说要多久之后再消费)
     * @param boolean $forceProduct 灰度的时候是否强制写到线上
     * @param boolean $prior 是否抢占优先（塞入队首，优先消费）
     * @return bool
     */
    public function produce($value, $delay_time = 0, $forceProduct = false, $prior = false)
    {
        if (!$this->initialized) {
            return false;
        }
        //延迟队列加入数据库进行暂存,每分钟定时脚本取出塞入队列重新处理
        if ($delay_time > 0) {
            if (!empty($this->machine_id)) {
                $dataTemp = [
                    "machine_id" => $this->machine_id,
                    "data" => $value,
                ];
                return QueueDelayModel::getInstance()->add(get_called_class(), json_encode($dataTemp, JSON_UNESCAPED_UNICODE), $delay_time, $forceProduct);
            } else {
                return QueueDelayModel::getInstance()->add(get_called_class(), json_encode($value, JSON_UNESCAPED_UNICODE), $delay_time, $forceProduct);
            }
        }
        $oriValue = $value;
        if ($this->json_handle) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $result = false;
        //增加重试机制
        $produce_try_times = $this->produce_try_times;
        while ($produce_try_times--) {
            if ($this->queue_type == self::QUEUE_TYPE_REDIS) {
                $temp_redis_key = $this->redis_key;
                if (isBeta() && $forceProduct) {
                    $temp_redis_key = ltrim($this->redis_key, "B_");
                }
                if ($prior) {
                    $result = $this->queue_object->lPush($temp_redis_key, $value);
                } else {
                    $result = $this->queue_object->rPush($temp_redis_key, $value);
                }
            }
            if ($result) {
                return $result;
            }
        }
        //失败 塞入延迟队列  一分钟后重新处理
        if ($result === false) {
            return $this->produce($oriValue, 60);
        }
        return $result;
    }

//    机器队列区分

    /**
     * @return bool|array|string
     */
    public function consume()
    {
        if (!$this->initialized) {
            return false;
        }
        $result = false;
        if ($this->queue_type == self::QUEUE_TYPE_REDIS) {
            $result = $this->queue_object->lPop($this->redis_key);
        }
        if (false !== $result && $this->json_handle) {
            $result = json_decode($result, true);
        }
        $GLOBALS["queue_consume_info"] = $result;
        return $result;
    }

    public function getInfo()
    {
        return [
            'queue_type' => $this->queue_type,
            'redis_key' => $this->redis_key,
        ];
    }

    protected abstract function _init();

    protected function _initServer()
    {
        if (empty($this->server_config)) {
            return;
        }
        $server_config = $this->server_config;
        //redis
        if ($this->queue_type == self::QUEUE_TYPE_REDIS) {
            if (empty($this->redis_key)) {
                $key_pre = substr(strtoupper(get_called_class()), strlen("Mt\Queue\\"));
                $this->redis_key = ModuleKey::QUEUE_MODULE . str_replace("QUEUE", "", $key_pre) . ":";
                if (isBeta()) {
                    $this->redis_key = "B_" . $this->redis_key;
                }
            }
            $server_config['master']['pconnect'] = true;
            if (isset($server_config['slaves']) && $server_config['slaves']) {
                foreach ($server_config['slaves'] as $key => $value) {
                    $server_config['slaves'][$key]['pconnect'] = true;
                }
            }
            $this->queue_object = Redis::getInstance($server_config);
            $this->initialized = true;
        }
    }

    protected function _initMachineId()
    {
        if (empty($this->machine_id)) {
            return;
        }
        $this->redis_key .= $this->machine_id . ':';
    }

    protected function getRedisServerConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }

}