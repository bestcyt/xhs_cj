<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;

/**
 * 重置密码
 * Class SetPassword
 * @package Mt\App\Manage\Controller\Setting\Account
 */
class SetPassword extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $accountId = intval($this->request->post("id"));
            $password = trim(strval($this->request->post("password")));
            $repassword = trim(strval($this->request->post("repassword")));
            if (empty($accountId) || empty($password) || empty($repassword)) {
                $this->error("非法操作");
            }
            if ($password != $repassword) {
                $this->error("新密码两次输入不同");
            }
            $ManageAccountModel = ManageAccountModel::getInstance();
            if (!$ManageAccountModel::checkPasswordFormat($password)) {
                $this->error("新密码不符合要求");
            }
            $ManageAccountModel->update($accountId, [
                "password" => ManageAccountModel::encrypt_password($password),
            ]);
            $this->success();
        } else {
            $accountId = intval($this->request->get("id"));
            $ManageAccountModel = ManageAccountModel::getInstance();
            $accountRow = $ManageAccountModel->getOne($accountId);
            View::getInstance()
                ->assign("account", $accountRow)
                ->render();
        }
    }
}