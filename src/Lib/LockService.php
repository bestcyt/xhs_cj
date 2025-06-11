<?php

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Redis;
use Fw\Request;
use Mt\Key\ModuleKey;

/**
 * 加锁解锁服务,利用这个可以做一些高并发场景需求
 * 用法 先加锁,加锁成功运行后续业务(业务结束后解锁,不可再次执行的操作可以不解锁等待自动解锁),加锁失败重试 加锁流程(具体重试由业务方自己控制)
 * Class LockService
 * @package Mt\Lib
 */
abstract class LockService
{
    use InstanceTrait;
    /**
     * 单据锁 key模板
     */
    protected $lock_name = "";

    /**
     * 单据锁默认超时时间（秒）
     */
    protected $lock_default_expire_time = 3600;

    protected $server_config = [];//redis配置

    protected function __construct()
    {
        $this->_init();
        $lock_name = substr(strtoupper(get_called_class()), strlen("MT\LOCK\\"));
        $this->lock_name = ModuleKey::LOCK_MODULE . str_replace("LOCK", "", $lock_name) . "_%s";
    }

    protected abstract function _init();

    /**
     * 加单据锁
     * @param int $intOrderId 单据ID
     * @param int $intExpireTime 锁过期时间（秒）
     * @return bool|int 加锁成功返回唯一锁ID，加锁失败返回false
     */
    public function addLock($intOrderId, $intExpireTime = 0)
    {
        //参数校验
        if (empty($intOrderId)) {
            return false;
        }
        if ($intExpireTime <= 0) {
            $intExpireTime = $this->lock_default_expire_time;
        }

        //获取Redis连接
        $objRedisConn = $this->getRedisConn();

        //生成唯一锁ID，解锁需持有此ID
        $intUniqueLockId = $this->generateUniqueLockId();

        //根据模板，结合单据ID，生成唯一Redis key（一般来说，单据ID在业务中系统中唯一的）
        $strKey = $this->getLockKey($intOrderId);

        try {
            //加锁（通过Redis setnx指令实现，从Redis 2.6.12开始，通过set指令可选参数也可以实现setnx，同时可原子化地设置超时时间）
            $bolRes = $objRedisConn->set($strKey, $intUniqueLockId, ['nx', 'ex' => $intExpireTime]);
            //加锁成功返回锁ID，加锁失败返回false
            if (!$objRedisConn->getErrorCode()) {
                return $bolRes ? $intUniqueLockId : $bolRes;
            }
        } catch (\Exception $exception) {
            App::getInstance()->getLogger()->error(get_called_class(), LogType::LOCK_FAIL);
        }

        return false;
    }

    /**
     * @return Redis
     */
    protected function getRedisConn()
    {
        $redis_server_config = $this->server_config;
        $redis_server_config['master']['pconnect'] = true;
        if (isset($redis_server_config['slaves']) && $redis_server_config['slaves']) {
            foreach ($redis_server_config['slaves'] as $key => $value) {
                $redis_server_config['slaves'][$key]['pconnect'] = true;
            }
        }
        return Redis::getInstance($redis_server_config);
    }

    /**
     * 生成锁唯一ID
     * @return mixed
     */
    protected function generateUniqueLockId()
    {
        return md5(time() . Request::getInstance()->getClientIp() . RandString::string(8));
    }

    /**
     * 获取缓存的key
     * @param $intOrderId
     * @return string
     */
    public function getLockKey($intOrderId)
    {
        return sprintf($this->lock_name, $intOrderId);
    }

    /**
     * 解单据锁
     * @param int $intOrderId 单据ID
     * @param int $intLockId 锁唯一ID
     * @return bool
     */
    public function releaseLock($intOrderId, $intLockId)
    {
        //参数校验
        if (empty($intOrderId) || empty($intLockId)) {
            return false;
        }

        //获取Redis连接
        $objRedisConn = $this->getRedisConn();

        //生成Redis key
        $strKey = sprintf($this->lock_name, $intOrderId);

        //监听Redis key防止在【比对lock id】与【解锁事务执行过程中】被修改或删除，提交事务后会自动取消监控，其他情况需手动解除监控
        $objRedisConn->watch($strKey);
        if ($intLockId == $objRedisConn->get($strKey)) {
            $objRedisConn->multi()->del($strKey)->exec();
            return true;
        }
        $objRedisConn->unwatch();
        return false;
    }

    protected function getServerConfig($redisKey)
    {
        return App::getInstance()->env($redisKey);
    }
}