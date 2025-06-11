<?php

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Redis;
use Mt\Key\ModuleKey;

/**
 * 发号器 业务直接继承使用
 * Class IdGenerator
 * @package Fw
 */
abstract class IdGenerator
{
    use InstanceTrait;
    protected $redis_config = null;//redis配置
    protected $work_id = 1;//业务id(0-31)
    protected $server_id = 1;//服务器id(0-31)
    protected $key = "";//缓存key
    protected $offsetTime = "2024-12-11 00:00:00";//基准时间(一旦设定不能更改,否则可能导致重复)

    /**
     * @var $idGenerator \Fw\IdGenerator
     */
    protected $idGenerator = null;
    protected $initialized = false;

    protected function __construct()
    {
        $this->_init();
        $key_name = substr(strtoupper(get_called_class()), strlen("MT\IDGENERATOR\\"));
        $this->key = ModuleKey::ID_GENERATOR_MODULE . str_replace("IDGENERATOR", "", $key_name) . ":";
        $this->initServer();
    }

    protected abstract function _init();

    protected function initServer()
    {
        if (!$this->redis_config || !$this->key || !$this->work_id || !$this->server_id || !$this->offsetTime) {
            return;
        }
        if (!$this->idGenerator) {
            $this->idGenerator = new \Fw\IdGenerator(Redis::getInstance($this->redis_config));
            $this->idGenerator->setWorkerId($this->work_id)
                ->setCacheKey($this->key)
                ->setOffsetTime($this->offsetTime)
                ->setServerId($this->server_id);
        }
        $this->initialized = true;
    }

    public function get()
    {
        if (!$this->initialized) {
            return false;
        }
        return $this->idGenerator->getNumber();
    }

    public function reverseId($id)
    {
        if (!$this->initialized) {
            return false;
        }
        return $this->idGenerator->reverseNumber($id);
    }

    protected function getRedisConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }
}