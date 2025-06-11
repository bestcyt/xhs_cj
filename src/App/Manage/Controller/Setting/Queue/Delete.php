<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Script\QueueModel;

class Delete extends BaseController
{

    public function main()
    {
        $QueueModel = QueueModel::getInstance();

        $id = intval($this->request->post('id'));
        if (!$id) {
            echo api_return_error(1001, "删除失败");
            exit;
        }

        $checkRow = $QueueModel->getOne($id);
        if ($checkRow && $checkRow["status"] == 2) {
            $result = $QueueModel->delete($id);
            if (!$result) {
                echo api_return_error(1001, "系统错误");
                exit;
            }

            echo api_return_status_ok();
        }
    }

}