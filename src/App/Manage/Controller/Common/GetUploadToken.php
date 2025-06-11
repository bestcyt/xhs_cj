<?php

namespace Mt\App\Manage\Controller\Common;

use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Oss;

class GetUploadToken extends BaseController
{
    public function main()
    {
        $data = Oss::getInstance()->getUploadToken();
        echo api_return_output($data);
    }
}