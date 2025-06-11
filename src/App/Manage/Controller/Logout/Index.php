<?php

namespace Mt\App\Manage\Controller\Logout;


use Mt\App\Manage\Controller\BaseController;
use Mt\Service\Manage\ManageLoginService;

class Index extends BaseController
{

    public function main()
    {
        ManageLoginService::getInstance()->deleteCurrentAccount();
        cookie("remember_account", null);
        $this->response->redirect('/login');
    }
}