<?php

namespace Mt\Logic;

use Fw\InstanceTrait;
use Mt\Service\Manage\ManageLoginService;

trait LogicTrait
{
    use InstanceTrait {
        getInstance as protected _getInstance;
    }

    protected $manage_id = 0;//后台管理员账号

    /**
     * @return static
     */
    public static function getInstance()
    {
        $obj = self::_getInstance();
        if (method_exists($obj, "_init")) {
            $obj->_init();
        }
        return $obj;
    }

    public function _init()
    {
        if (currentAppName() == "Manage") {
            $this->manage_id = ManageLoginService::getInstance()->getCurrentAccountId();
        }
    }
}