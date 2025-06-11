<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 修改密码
 * Class ResetPassword
 * @package Mt\App\Manage\Controller\Setting\Account
 */
class ResetPassword extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $oldPassword = trim(strval($this->request->post("oldPassword")));
            $password = trim(strval($this->request->post("password")));
            $repassword = trim(strval($this->request->post("repassword")));
            if (empty($oldPassword) || empty($password) || empty($repassword)) {
                $this->error("非法操作");
            }
            if ($password != $repassword) {
                $this->error("新密码两次输入不同");
            }
            $ManageAccountModel = ManageAccountModel::getInstance();
            if (!$ManageAccountModel::checkPasswordFormat($password)) {
                $this->error("新密码不符合要求");
            }
            $accountId = ManageLoginService::getInstance()->getCurrentAccountId();
            $accountInfo = $ManageAccountModel->getOne($accountId);
            if ($accountInfo["password"] != ManageAccountModel::encrypt_password($oldPassword)) {
                $this->error("旧密码错误");
            }
            $ManageAccountModel->update($accountId, [
                "password" => ManageAccountModel::encrypt_password($password),
            ]);
            $this->success();
        } else {
            View::getInstance()->render();
        }
    }
}