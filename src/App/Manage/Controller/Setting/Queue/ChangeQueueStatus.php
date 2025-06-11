<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Fw\App;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Mussy;
use Mt\Lib\Script\QueueModel;
use Mt\Service\Manage\ManageLoginService;

class ChangeQueueStatus extends BaseController
{

    private $id;
    private $status;
    private $op_chg_data;

    public function main()
    {
        $QueueModel = QueueModel::getInstance();

        $this->id = splitNumber($this->request->post('id'));
        if (!$this->id) {
            echo api_return_error(1001, "更新失败");
            exit;
        }

        $this->status = intval($this->request->post('status'));

        $result = $QueueModel->changeQueueStatus($this->id, $this->status);
        if (!$result) {
            echo api_return_error(1001, "系统错误");
            exit;
        }

        $this->monitorOps();
        echo api_return_status_ok();
    }

    // 队列操作记录
    private function monitorOps()
    {
        $map = [
            -1 => '重启',
            0 => '关闭',
            1 => '开启'
        ];
        if (!in_array($this->status, array_keys($map))) {
            return;
        }
        $QueueModel = QueueModel::getInstance();
        $this->op_chg_data['action'] = "全部{$map[$this->status]}";

        $this->op_chg_data['env'] = App::getInstance()->getEnvironment();
        $this->op_chg_data['author'] = ManageLoginService::getInstance()->getCurrentAccount()["real_name"];

        $str = <<<str
***Warning***
【队列变更】|【{$this->op_chg_data['env']}环境】
{$this->op_chg_data['author']}【{$this->op_chg_data['action']}了】队列
str;

        if (count($this->id) == 1) {
            $id = current($this->id);
            $info = ($QueueModel->getOne($id));
            $this->op_chg_data['name'] = $info['file'];
            $this->op_chg_data['action'] = $map[$this->status];
            $str = <<<str
【队列变更】|【{$this->op_chg_data['env']}环境】
{$this->op_chg_data['author']}【{$this->op_chg_data['action']}了】{$this->op_chg_data['name']}
str;
        }
        $Mussy = Mussy::getInstance();
        $Mussy->fatal_alert_email("队列变更", $str, "信息");
        $Mussy->fatal_alert_feishu("队列变更", $str, "信息");
    }
}