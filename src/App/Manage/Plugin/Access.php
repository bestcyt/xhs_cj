<?php

namespace Mt\App\Manage\Plugin;

use Fw\App;
use Fw\Request;
use Mt\Lib\Traits\PluginTrait;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\Manage\ManageRightsModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 权限校验
 */
class Access
{
    use PluginTrait;

    public function preDispatch(Request &$request, App &$app)
    {
        $account_id = ManageLoginService::getInstance()->getCurrentAccountId();
        if (empty($account_id)) {
            return;
        }
        //白名单设置
        $white_page = array("logout/index", "common/get_upload_token", "index/index", "home/index","setting/account/reset_password","login/forget");
        if (in_array($this->uri, $white_page)) {
            return;
        }
        $accountInfo = ManageLoginService::getInstance()->getCurrentAccount();
        //判断是否有权限
        $ManageAccountModel = ManageAccountModel::getInstance();
        $system_id = ManageRightsModel::SYSTEM_MANAGE;
        if (!$ManageAccountModel->hasRights($accountInfo, $this->uri, $system_id)) {
            if ($request->isAjax()) {
                echo api_return_error(1000, "该账号无权做此操作");
                exit;
            } else {
                echo "您无权进行此操作";
                exit;
            }
        }
    }

}