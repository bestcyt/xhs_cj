<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;

class Status extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $id = intval($this->request->post('id'));
            if ($id <= 0) {
                $this->error("非法操作");
            }
            $ManageAccountModel = ManageAccountModel::getInstance();
            $checkRow = $ManageAccountModel->getOne($id);
            $status = $checkRow["status"] == $ManageAccountModel::STATUS_NORMAL ? $ManageAccountModel::STATUS_DISABLED : $ManageAccountModel::STATUS_NORMAL;
            $info = ['status' => $status];
            if (!$ManageAccountModel->update($id, $info)) {
                $this->error("操作失败");
            }
            $this->success();
        }
    }

}