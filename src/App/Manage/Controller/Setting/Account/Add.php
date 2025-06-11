<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;

class Add extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $mobile = trim(strval($this->request->post('mobile')));
            $real_name = trim(strval($this->request->post('real_name')));
            $password = trim(strval($this->request->post('password')));
            if (!$mobile || empty($real_name) || empty($password) || !isMobile($mobile)) {
                $this->error('参数错误');
            }
            if (!ManageAccountModel::checkPasswordFormat($password)) {
                $this->error('密码格式由6-15位的 数字、字母、下划线、@、!、#、$、%、^、&、*、(、) 至少两种组合');
            }
            $ManageAccountModel = ManageAccountModel::getInstance();
            $oldInfo = $ManageAccountModel->getOneByMobile($mobile);
            if (!empty($oldInfo)) {
                $this->error("该手机号已被注册");
            }

            $info = [
                'mobile' => $mobile,
                'real_name' => $real_name,
                'status' => ManageAccountModel::STATUS_NORMAL,
                'password' => ManageAccountModel::encrypt_password($password),
                "create_time" => time(),
            ];

            if (!$ManageAccountModel->insert($info)) {
                $this->error("添加失败");
            }
            $this->success();
        } else {
            View::getInstance()
                ->assign("account", [])
                ->render("setting/account/edit");
        }
    }
}