<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 来鼓授权账号
 * Class LaiguAccountModel
 * @package Mt\Model
 */
class LaiguAccountModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    //是否删除
    const DELETE_YES = 1;
    const DELETE_NO = 2;
    const DELETE = [
        self::DELETE_NO => "否",
        self::DELETE_YES => "是",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'laigu_account';
    }
}