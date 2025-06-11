<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Script\QueueDispatchModel;
use Mt\Lib\Script\QueueModel;

class GetRunInfo extends BaseController
{

    public function main()
    {

        $QueueModel = QueueModel::getInstance();
        $QueueDispatchModel = QueueDispatchModel::getInstance();
        $queue_list = $QueueModel->load([]);
        $data = $QueueDispatchModel->getCounts();
        foreach ($queue_list as $k => $v) {
            if (empty($data[$v['id']])) {
                $queue_list[$k]['counts'] = [];
            } else {
                $queue_list[$k]['counts'] = $data[$v['id']];
            }
        }

        echo json_encode($queue_list);
        exit;
    }

}