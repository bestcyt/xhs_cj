<?php

namespace Mt\App\Manage\Controller;

use Fw\Controller;

class BaseController extends Controller
{
    /**
     * main前的操作
     */
    public function before()
    {
        $export_data = intval($this->request->get("export_data"));
        if (!$this->request->isAjax() && empty($export_data)) {
            if (method_exists($this, "view")) {
                $this->view();
                exit;
            }
        }
    }

    protected function success($response = [])
    {
        echo api_return_output($response);
        exit;
    }

    protected function error($msg = "未知错误", array $params = [], $errCode = 10010)
    {
        echo api_return_error($errCode, $msg, $params);
        exit;
    }
}