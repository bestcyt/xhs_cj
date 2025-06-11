<?php

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Redis;
use Mt\Key\ModuleKey;

/**
 * redis bitmap抽象类
 * 用于做开关位标记
 * Class BitmapService
 * @package Mt\Lib
 */
abstract class BitmapService
{
    use InstanceTrait;
    protected $server_config = [];//redis配置
    protected $shard_split = 10000000;//一个缓存key多少数据
    protected $key_pre = "";//key前缀

    protected function __construct()
    {
        $this->_init();
        $key_pre = substr(strtoupper(get_called_class()), strlen("Mt\Bitmap\\"));
        $this->key_pre = ModuleKey::BITMAP_MODULE . str_replace("BITMAP", "", $key_pre) . ":";
    }

    protected abstract function _init();

    /**
     * 获取开关位  0或则1 false代表出错
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $keyAndOffset = $this->getCacheKeyAndOffset($key);
        $cache = $keyAndOffset["key"];
        $offset = $keyAndOffset["offset"];
        return $this->getRedisConn()->getBit($cache, $offset);
    }

    /**
     * 获取key和偏移量
     * @param $key
     * @return array
     */
    public function getCacheKeyAndOffset($key)
    {
        if (!is_numeric($key)) {
            $key = crc32($key);
        }
        return [
            'key' => $this->key_pre . intval($key / $this->shard_split),
            'offset' => $key % $this->shard_split
        ];
    }

    /**
     * @return Redis
     */
    protected function getRedisConn()
    {
        $redis_server_config = $this->server_config;
        if (!empty($redis_server_config['master']['pconnect']) && isset($redis_server_config['slaves']) && $redis_server_config['slaves']) {
            foreach ($redis_server_config['slaves'] as $key => $value) {
                $redis_server_config['slaves'][$key]['pconnect'] = true;
            }
        }
        return Redis::getInstance($redis_server_config);
    }

    /**
     * 设置开关位
     * @param $key
     * @return bool
     */
    public function setOn($key)
    {
        $keyAndOffset = $this->getCacheKeyAndOffset($key);
        $cache = $keyAndOffset["key"];
        $offset = $keyAndOffset["offset"];
        $result = $this->getRedisConn()->setBit($cache, $offset, 1);
        return is_numeric($result) ? true : false;
    }

    /**
     * 设置开关位
     * @param $key
     * @return bool
     */
    public function setOff($key)
    {
        $keyAndOffset = $this->getCacheKeyAndOffset($key);
        $cache = $keyAndOffset["key"];
        $offset = $keyAndOffset["offset"];
        $result = $this->getRedisConn()->setBit($cache, $offset, 0);
        return is_numeric($result) ? true : false;
    }

    protected function getServerConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }
}