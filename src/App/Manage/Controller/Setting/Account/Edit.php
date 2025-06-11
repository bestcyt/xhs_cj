<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;

class Edit extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $ManageAccountModel = ManageAccountModel::getInstance();
            $id = intval($this->request->post("id"));
            $account = $ManageAccountModel->getOne($id);
            if (!$account) {
                $this->error("非法操作");
            }
            $mobile = trim(strval($this->request->post('mobile')));
            $real_name = trim(strval($this->request->post('real_name')));
            if (!$mobile || empty($real_name)) {
                $this->error('参数错误');
            }
            $oldInfo = $ManageAccountModel->getOneByMobile($mobile);
            if (!empty($oldInfo) && $oldInfo["id"] != $id) {
                $this->error("该手机号已被注册");
            }

            ManageAccountModel::getInstance()->update($id, [
                "mobile" => $mobile,
                "real_name" => $real_name,
            ]);
            $this->success();
        } else {
            $id = intval($this->request->get("id"));
            $account = ManageAccountModel::getInstance()->getOne($id);
            if (!$account) {
                $this->error("非法操作");
            }
            View::getInstance()->assign("account", $account)->render();
        }
    }
}