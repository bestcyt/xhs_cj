<?php

namespace Mt\App\Manage\Controller\Common;

use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Oss;

class Upload extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $Oss = Oss::getInstance();
            $nameArr = explode(".", $_FILES["file"]["name"]);
            $ext = end($nameArr);
            $url = $Oss->upload($prefixDir = "common", $_FILES["file"]["tmp_name"], "", $ext);

            echo json_encode([
                "code" => 0,
                "msg" => "",
                "data" => [
                    "src" => $url,
                    "title" => $_FILES["file"]["name"],
                ],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}