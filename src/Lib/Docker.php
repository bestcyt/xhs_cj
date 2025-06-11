<?php

namespace Mt\Lib;

use Fw\InstanceTrait;

class Docker
{
    use InstanceTrait;
    //只要这个文件存在，说明容器正在关闭，不可以运行一些如队列、定时脚本等进程
    const SHUTDOWN_SWITCH_FILE = '/tmp/docker_shutdown.switch';
    //只要这个文件存在，说明在容器中
    const CONTAINER_FILE = '/dev/mtstdout';

    /**
     * 容器是否准备下线
     * @return bool
     */
    public function isShutdown()
    {
        return is_file(self::SHUTDOWN_SWITCH_FILE);
    }

    public function isDocker()
    {
        if (file_exists(self::CONTAINER_FILE)) {
            return true;
        }
        return false;
    }

    /*
     * 获取宿主机IP
     */
    public function getContainerHostIp()
    {
        return empty($_SERVER['CONTAINER_HOST_IP']) ? '0.0.0.0' : $_SERVER['CONTAINER_HOST_IP'];
    }
}