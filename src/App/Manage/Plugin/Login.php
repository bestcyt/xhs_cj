<?php

namespace Mt\App\Manage\Plugin;

use Fw\App;
use Fw\Request;
use Mt\Lib\Token;
use Mt\Lib\Traits\PluginTrait;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 登录验证
 */
class Login
{
    use PluginTrait;

    public function preDispatch(Request &$request, App &$app)
    {
        //白名单设置
        $white_page = [
            "login/index",
            "login/code",
            "login/forget",
            'api/plugin_download',
            'api/get',
            'api/set',
            'api/heartbeat',
            'api/error',
        ];
        if (in_array($this->uri, $white_page)) {
            return;
        }
        //登录判断
        $currentAccount = ManageLoginService::getInstance()->getCurrentAccount();
        //兼容token方式
        if (empty($currentAccount)) {
            $token = $request->get('token');
            if (!empty($token)) {
                $Token = Token::getInstance();
                $account_info = $Token->analysis($token, $Token::TYPE_MANAGE_REDIRECT_LOGIN);
                if (!empty($account_info)) {
                    $ManageAccountModel = ManageAccountModel::getInstance();
                    $currentAccount = $ManageAccountModel->getOne($account_info["id"]);
                    ManageLoginService::getInstance()->setCurrentAccount($currentAccount);
                }
            }
        }
        $errorMsg = "";
        if (empty($currentAccount)) {
            $errorMsg = "登录超时,请重新登录!";
        } else {
            if ($currentAccount["status"] == ManageAccountModel::STATUS_DISABLED) {
                $errorMsg = "该账号已被禁用,请联系该后台系统的管理员!";
            }
        }
        if (!empty($errorMsg)) {
            if ($request->isAjax()) {
                echo api_return_error(401, $errorMsg);
                exit;
            } else {
                $url = getScheme() . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "") . $_SERVER['REQUEST_URI'];
                ManageLoginService::getInstance()->setCallbackUrl($url);
                redirect(U("login/index"));
            }
        }
    }
}