<?php

namespace Mt\App\Manage\Controller\Login;


use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Service\Manage\ManageLoginService;

class Index extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $code = $this->request->post('code');
            if (!$code) {
                $this->error("请输入验证码", [], 4001);
            }
            $verifyCode = ManageLoginService::getInstance()->getVerifyCode();
            // if (!$verifyCode) {
            //     $this->error("请获取验证码后再登录", [], 4001);
            // }
            // if (empty(cookie("remember_account")) && strtolower($verifyCode) != strtolower($code)) {
            //     $this->error("验证码错误", [], 4001);
            // }

            $mobile = trim(strval($this->request->post("mobile")));
            if (!$mobile || !isMobile($mobile)) {
                $this->error("请填写正确的手机号码", [], 4001);
            }
            $ManageAccountModel = ManageAccountModel::getInstance();
            $acctInfo = $ManageAccountModel->getOneByMobile($mobile);
            if (empty($acctInfo)) {
                $this->error("账号还没激活,请联系该后台系统的管理员", [], 4001);
            }
            if ($acctInfo['status'] == ManageAccountModel::STATUS_DISABLED) {
                $this->error("该账号已被禁用,请联系该后台系统的管理员", [], 4001);
            }
            //验证密码
            $password = trim(strval($this->request->post("password")));
            if (!$password) {
                $this->error("请填写密码", [], 4001);
            }
            if ($acctInfo["password"] != ManageAccountModel::encrypt_password($password)) {
                $this->error("密码错误", [], 4001);
            }
            //是否记住密码
            if (trim($this->request->post("remember"))) {
                cookie("remember_account", json_encode([
                    "account" => $mobile,
                    "password" => $password,
                ]), 86400 * 7);
            } else {
                cookie("remember_account", null);
            }
            ManageLoginService::getInstance()->setCurrentAccount($acctInfo);
            $url = ManageLoginService::getInstance()->getCallbackUrl() ?: "/";
            echo api_return_output([
                "url" => $url,
            ]);
        } else {
            $remember_account = cookie("remember_account") ?: "";
            View::getInstance()
                ->assign("remember_account", json_decode($remember_account, true))
                ->render();
        }
    }
}