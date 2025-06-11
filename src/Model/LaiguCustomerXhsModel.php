<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 来鼓商户小红书账号
 * Class LaiguCustomerModel
 * @package Mt\Model
 */
class LaiguCustomerXhsModel extends Model
{
    use TableTrait;
    use InstanceTrait;
    //是否删除
    const DELETE_YES = 1;
    const DELETE_NO = 2;
    const DELETE = [
        self::DELETE_YES => "是",
        self::DELETE_NO => "否",
    ];


    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'laigu_account_xhs';
    }
}