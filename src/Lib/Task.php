<?php

namespace Mt\Lib;

use Fw\InstanceTrait;

/**
 * 异步任务
 * Class Task
 * @package Mt\Lib
 */
class Task
{
    use InstanceTrait {
        getInstance as protected _getInstance;
    }

    protected $timeOut = 50;//等待异步结果的超时时间s

    protected function __construct($timeOutSecond = 50)
    {
        $this->timeOut = $timeOutSecond;
    }

    public static function getInstance($timeOutSecond = 50)
    {
        return self::_getInstance($timeOutSecond);
    }

    /**
     * 异步执行并等待返回结果
     * @param string|array $className 类名
     * @param string $method 方法名
     * @param mixed ...$args 参数
     * @return bool|mixed
     */
    public function asyncWait($className, $method, ...$args)
    {
        $task_id = $this->delay(0, $className, $method, ...$args);
        if (!$task_id) {
            return false;
        }
        $time = time();
        $TaskResultCache = \Mt\Cache\TaskResultCache::getInstance();
        $result = false;
        while (time() - $time <= $this->timeOut) {
            usleep(300000);
            if ($TaskResultCache->get($task_id . ":handle")) {
                $result = $TaskResultCache->get($task_id);
                if ($result) {
                    $result = unserialize($result);
                }
                break;
            }
        }
        $TaskResultCache->delete($task_id);
        $TaskResultCache->delete($task_id . ":handle");
        return $result;
    }

    /**
     * 延迟执行任务
     * @param int $delaySecond 延迟秒数
     * @param string|array $className 类名
     * @param string $method 方法名
     * @param mixed ...$args 参数
     * @return bool|string
     */
    public function delay($delaySecond, $className, $method, ...$args)
    {
        $TaskQueue = \Mt\Queue\TaskQueue::getInstance();
        $task_id = uniqid() . rand(100, 999);
        $data = serialize([
            "task_id" => $task_id,
            "class" => $className,
            "method" => $method,
            "args" => $args,
        ]);
        $delaySecond = $delaySecond >= 0 ? $delaySecond : 0;
        return $TaskQueue->produce($data, $delaySecond) ? $task_id : false;
    }

    /**
     * 异步执行 不等返回结果
     * @param $className
     * @param $method
     * @param mixed ...$args
     * @return bool|string
     */
    public function async($className, $method, ...$args)
    {
        return $this->delay(0, $className, $method, ...$args);
    }
}
