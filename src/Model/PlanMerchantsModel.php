<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 商家计划关联表
 * Class XiaohongshuAccountModel
 * @package Mt\Model
 */
class PlanMerchantsModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    const TYPE_MAJIA = 1;
    const TYPE_NOTE = 2;
    const TYPE = [
        self::TYPE_MAJIA => "马甲计划",
        self::TYPE_NOTE => "养贴计划",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'plan_merchants';
    }

}