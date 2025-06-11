<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Fw\App;
use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Mussy;
use Mt\Lib\Script\QueueDispatchModel;
use Mt\Lib\Script\QueueModel;
use Mt\Service\Manage\ManageLoginService;

//更新或创建队列
class Edit extends BaseController
{

    const MODE_INSERT = 1;   //  从数据库读取的数据
    const MODE_UPDATE = 2;   // 操作者进行改变的数据
    protected $view = null;       // 操作方式 新增或修改
    private $origin_data = [];
    private $op_chg_data = [];
    private $op_mode;

    public function main()
    {
        $this->view = View::getInstance();
        $QueueModel = QueueModel::getInstance();
        $QueueDispatchModel = QueueDispatchModel::getInstance();

        $id = $this->request->get('id');  //有则更新，无则创建
        if ($id) {
            $info = $QueueModel->getOne($id);
            $this->origin_data = $info;
        }
        $this->op_mode = empty($info) ? self::MODE_INSERT : self::MODE_UPDATE;

        if ($this->request->isPost()) {
            $file = trim($this->request->post('file'));
            if (!$file) {
                echo api_return_error(1001, "请填写文件");
                exit;
            }

            //队列机数量
            $number = intval($this->request->post('number'));
            $_origin_num = $this->origin_data ? $this->origin_data['number'] : 0;
            if ($this->op_mode == self::MODE_UPDATE && $_origin_num != $number) {
                // 调整了进程数
                $this->op_chg_data['number'] = [$_origin_num, $number];
            }

            //备注
            $remark = trim($this->request->post('remark'));

            $data = [
                'file' => $file,
                'number' => $number,
                'remark' => $remark ?: '',
            ];

            if ($id) {
                $data['id'] = $id;
            }

            $result = $QueueModel->insertOrUpdate($data);
            if (!$result) {
                echo api_return_error(1001, "系统错误");
                exit;
            }

            //更新分发数
            if ($id) {
                $queue_info = $QueueModel->getOne($id);
                if ($queue_info && $queue_info['status']) {
                    $QueueDispatchModel->updateQueueDispatchByNum($id, $number);
                }
            }
            $this->genReport();
            echo api_return_status_ok();
            exit;
        } else {
            $this->view->assign("id", $id);
            $this->view->assign("info", $this->origin_data);
            $this->view->render();
        }
    }

    private function genReport()
    {
        if (empty($this->op_chg_data) || empty($this->origin_data)) {
            return;
        }
        $str = '';
        $env = App::getInstance()->getEnvironment();
        $author = ManageLoginService::getInstance()->getCurrentAccount()["real_name"];
        $str .= "【队列变更】" . PHP_EOL . "{$this->origin_data['file']}" . PHP_EOL . "操作者:{$author}" . PHP_EOL;
        $str .= "**{$env}环境**" . PHP_EOL;
        // 检测进程数的改变
        if (!empty($this->op_chg_data['number'])) {
            $str .= "**进程数发生变更**" . PHP_EOL;
            $str .= ":由{$this->op_chg_data['number'][0]}变为{$this->op_chg_data['number'][1]}" . PHP_EOL;
        }

        $Mussy = Mussy::getInstance();
        $Mussy->fatal_alert_email("队列变更", $str, "信息");
        $Mussy->fatal_alert_feishu("队列变更", $str, "信息");
    }
}
