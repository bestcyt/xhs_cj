<?php

namespace Mt\App\Manage\Controller\Api;

use Mt\App\Manage\Controller\BaseController;

/**
 * 错误页面
 * Class Error
 * @package Mt\App\Manage\Controller\Api
 */

class Error extends BaseController
{
    public function main()
    {
        echo "发生时间：" . date("Y-m-d H:i:s");
        $response = urldecode($this->request->get("response", null, "trim"));
        pre($response);
    }

}