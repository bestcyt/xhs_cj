<?php

namespace Mt\Lib\Script;

use Fw\App;
use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

/**
 * 队列延迟处理
 * Class QueueDelayModel
 * @package Mt\Lib\Script
 */
class QueueDelayModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    protected function __construct()
    {
        $this->table = 'queue_delay';
        $this->dbGroup = $this->getScriptDbGroup();
    }

    public function add($class, $data, $delay_time, $forceProduct = false)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        if (isBeta() && $forceProduct) {
            $pool = "product";
        }
        $insert_data = array(
            "queue_class" => $class,
            "data" => $data,
            "run_time" => time() + $delay_time,
            "pool" => $pool,
        );
        //重试三次
        $i = 3;
        while ($i--) {
            $result = $this->insert($insert_data);
            if ($result) {
                return true;
            }
        }
        return false;
    }

}