<?php

namespace Mt\Service\Manage;

use Fw\InstanceTrait;
use Fw\Session;
use Fw\TableTrait;
use Mt\Model\Manage\ManageAccountModel;

class ManageLoginService
{
    use InstanceTrait;
    use TableTrait;
    protected $session = null;

    protected function __construct()
    {
        if (!isCli()) {
            $this->session = Session::getInstance(app_env('redis/session'));
        }
    }

    public function getCurrentAccountId()
    {
        return $this->getCurrentAccount() ? $this->getCurrentAccount()["id"] : 0;
    }

    public function getCurrentAccount()
    {
        if (empty($this->session)) {
            return null;
        }
        $current_account = $this->session->get("current_account");
        //增加5分钟的缓存，用于状态值判断
        if (!empty($current_account)) {
            if (empty($current_account["session_login_time"])) {
                $current_account["session_login_time"] = time();
                $this->setCurrentAccount($current_account);
            }
            if (time() - $current_account["session_login_time"] > 300) {
                $ManageAccountModel = ManageAccountModel::getInstance();
                $current_account = $ManageAccountModel->getOne($current_account["id"]);
                $current_account["session_login_time"] = time();
                $this->setCurrentAccount($current_account);
            }
        }
        return $current_account;
    }

    public function setCurrentAccount(array $accountInfo)
    {
        $this->session && $this->session->set("current_account", $accountInfo);
    }

    public function deleteCurrentAccount()
    {
        $this->session && $this->session->delete("current_account");
    }

    public function setCallbackUrl($callback_url)
    {
        $this->session && $this->session->set("callback_url", $callback_url);
    }

    public function getCallbackUrl()
    {
        return $this->session ? $this->session->get("callback_url") : "";
    }

    public function setVerifyCode($code)
    {
        $this->session && $this->session->set("verify_code", $code);
    }

    public function getVerifyCode()
    {
        return $this->session ? $this->session->get("verify_code") : "";
    }

}