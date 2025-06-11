<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Script\QueueDispatchModel;
use Mt\Lib\Script\QueueModel;

class Index extends BaseController
{
    protected $view = null;

    public function main()
    {
        $this->view = View::getInstance();
        $options = [];
        $switch_type = $this->request->get('switch_type') ?: 'online';
        $this->view->assign("switch_type", $switch_type);
        $QueueModel = QueueModel::getInstance();
        if ($switch_type == 'offline') {
            $options['status'] = $QueueModel::STATUS_DELETED;
        } else {
            $status = isset($_GET['status']) ? intval($this->request->get('status')) : -1;
            if (in_array($status, [0, 1])) {
                $options['status'] = $status;
            } else {
                $options['status'] = [0, 1];
            }
        }

        $queue_list = $QueueModel->load($options);

        $QueueDispatchModel = QueueDispatchModel::getInstance();
        foreach ($queue_list as $k => $v) {
            $script_path = app_root_path() . "/src/App/Script/Queue/" . $v['file'] . '.php';
            $queue_list[$k]['set_check_alive'] = $QueueDispatchModel::isSetCheckAlive($script_path);
            $queue_list[$k]['set_use_queue'] = $QueueDispatchModel::isUseQueue($script_path);
        }

        $total_queue = count($queue_list);
        $total_run_process = array_sum(array_column($queue_list, 'status'));
        $total_halt_process = $total_queue - $total_run_process;
        $total_process = 0;
        foreach ($queue_list as $id => $queue) {
            if ($queue['status'] == 1) {
                $total_process += $queue["number"];
            }
            //查找queue_key
            $queue_obj = $QueueModel->findAndReturnQueue($queue["file"]);
            $queue_list[$id]["queue_key"] = $queue_obj ? $queue_obj->getInfo() : [];
        }
        $this->view->assign("total_queue", $total_queue);
        $this->view->assign("total_halt_process", $total_halt_process);
        $this->view->assign("total_run_process", $total_run_process);
        $this->view->assign("total_process", $total_process);
        $this->view->assign("queue_list", $queue_list);
        $this->view->render();
    }
}