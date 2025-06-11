<?php

namespace Mt\App\Manage\Controller\Customer;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguCustomerXhsModel;

/**
 * 删除
 * Class Delete
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class XhsDel extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $id = intval($this->request->post("id"));
            $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
            $row = $LaiguCustomerXhsModel->getOne($id);
            if (empty($row)) {
                $this->error('数据不存在');
            }

            $res = $LaiguCustomerXhsModel->update($id, ['is_delete' => LaiguCustomerXhsModel::DELETE_YES]);
            if (empty($res)) {
                $this->error('删除失败哈');
            }

            $this->success();
        }
    }
}