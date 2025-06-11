<?php

namespace Mt\App\Manage\Controller\Index;


use Fw\View;
use Mt\App\Manage\Controller\BaseController;

class Index extends BaseController
{

    public function main()
    {
        View::getInstance()->render();
    }
}