<?php

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Mt\Key\ModuleKey;

abstract class BloomFilter
{
    use InstanceTrait;
    protected $server_config = [];//redis配置
    protected $shard_split = 10000000;//一个缓存key多少数据
    protected $hashFunction = [];//过滤器hash函数名数组
    protected $key_pre = "";//key前缀
    /**
     * @var $bloomFilter \Fw\BloomFilter
     */
    protected $bloomFilter = null;
    protected $tenant_store_id = 0;

    protected function __construct()
    {
        $this->_init();
        $key_pre = substr(strtoupper(get_called_class()), strlen("Mt\BloomFilter\\"));
        $this->key_pre = ModuleKey::BLOOM_MODULE . str_replace("BloomFilter", "", $key_pre) . ":";
        $this->hashFunction = [
            "JSHash",
            "PJWHash",
            "ELFHash",
            "BKDRHash",
        ];
        $this->_initServer();
    }

    protected abstract function _init();

    protected function _initServer()
    {
        $this->bloomFilter = \Fw\BloomFilter::getInstance($this->server_config, $this->hashFunction, $this->key_pre, $this->shard_split);
    }

    /**
     * 设置
     * @param $string
     * @return mixed
     */
    public function add($string)
    {
        return $this->bloomFilter->add($string);
    }

    /**
     * 是否存在
     * @param $string
     * @return bool
     */
    public function exists($string)
    {
        return $this->bloomFilter->exists($string);
    }

    /**
     * 只能删除 $string 所在的key
     * 慎重操作,key非常大
     * @param $string
     * @return int
     */
    public function deleteAllByString($string)
    {
        return $this->bloomFilter->deleteAllByString($string);
    }

    protected function getServerConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }
}