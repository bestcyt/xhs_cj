<?php

namespace Mt\App\Manage\Controller\Login;


use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Mail;
use Mt\Lib\RandString;
use Mt\Lib\Token;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 忘记密码
 * Class Forget
 * @package Mt\App\Manage\Controller\Login
 */
class Forget extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $step = intval($this->request->get("type")) ?: 1;
            if ($step == 1) {  //发送邮件
                $email = trim(strval($this->request->post("email")));
                $code = $this->request->post('code');
                if (!$code) {
                    $this->error("请输入图形验证码");
                }
                $verifyCode = ManageLoginService::getInstance()->getVerifyCode();
                if (!$verifyCode) {
                    $this->error("图形验证码错误");
                }
                if (strtolower($verifyCode) != strtolower($code)) {
                    $this->error("图形验证码错误");
                }
                $ManageAccountModel = ManageAccountModel::getInstance();
                $checkRow = $ManageAccountModel->getOneByEmail($email);
                if (empty($checkRow)) {
                    $this->error("邮箱错误，该账号不存在");
                }
                $verify_code = RandString::string(6);
                $token = Token::getInstance()->create([
                    "id" => $checkRow["id"],
                    "verify_code" => $verify_code,
                ], 600, Token::TYPE_MANAGE_FORGET_ACCOUNT);
                //发送邮件验证码
                $Mail_model = Mail::getInstance();
                $Mail_model->send_common($email, "找回密码", "您的验证码为：{$verify_code}，请及时输入验证", false);
                $this->success($token);
            } elseif ($step == 2) {  //验证验证码
                $verify_code = trim(strval($this->request->post('verify_code')));
                $token = trim(strval($this->request->post("token")));
                if (empty($verify_code) || empty($token)) {
                    $this->error("邮件验证码错误");
                }
                $data = Token::getInstance()->analysis($token, Token::TYPE_MANAGE_FORGET_ACCOUNT);
                if (empty($data)) {
                    $this->error("邮件验证码过期，请重新获取");
                }
                if ($verify_code != $data["verify_code"]) {
                    $this->error("邮件验证码错误");
                }
                $token = Token::getInstance()->create($data, 600, Token::TYPE_MANAGE_FORGET);
                $this->success($token);
            } else { //重置密码
                $token = trim(strval($this->request->post("token")));
                if (empty($token)) {
                    $this->error("非法操作");
                }
                $data = Token::getInstance()->analysis($token, Token::TYPE_MANAGE_FORGET);
                if (empty($data)) {
                    $this->error("验证超时，请重新操作");
                }
                $password = trim(strval($this->request->post("password")));
                $repassword = trim(strval($this->request->post("repassword")));
                if (empty($password) || empty($repassword)) {
                    $this->error("非法操作");
                }
                if ($password != $repassword) {
                    $this->error("新密码两次输入不同");
                }
                $ManageAccountModel = ManageAccountModel::getInstance();
                if (!$ManageAccountModel::checkPasswordFormat($password)) {
                    $this->error("新密码不符合要求");
                }
                $ManageAccountModel->update($data["id"], [
                    "password" => ManageAccountModel::encrypt_password($password),
                ]);
                $this->success();
            }
        } else {
            View::getInstance()
                ->render();
        }
    }
}