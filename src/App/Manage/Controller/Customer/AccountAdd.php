<?php

namespace Mt\App\Manage\Controller\Customer;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguAccountModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 添加商户
 * Class Add
 * @package Mt\App\Manage\Controller\Customer\AccountAdd
 */
class AccountAdd extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $auth_name = trim(strval($this->request->post("auth_name")));

            $LaiguAccountModel = LaiguAccountModel::getInstance();
            $info = $LaiguAccountModel->getRow(['auth_name' => $auth_name, 'is_delete' => $LaiguAccountModel::DELETE_NO]);
            if ($info) {
                $this->error("这个商家已存在");
            }
            $res = LaiguAccountModel::getInstance()->insert([
                'auth_name' => $auth_name,
                'admin_id' => ManageLoginService::getInstance()->getCurrentAccountId(),
            ]);
            if (empty($res)) {
                $this->error('添加失败');
            }

            $this->success();
        }
    }

    protected function view()
    {
        View::getInstance()
            ->render();
    }
}