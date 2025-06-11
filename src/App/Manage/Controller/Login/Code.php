<?php

namespace Mt\App\Manage\Controller\Login;

use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\RandString;
use Mt\Lib\VerifyCode;
use Mt\Service\Manage\ManageLoginService;

/**
 * 生成验证码
 * Class Code
 * @package Mt\App\Manage\Controller\Login
 */
class Code extends BaseController
{
    public function main()
    {
        $width = intval($this->request->get("width")) ?: 100;
        $height = intval($this->request->get("height")) ?: 35;
        $pure = intval($this->request->get("pure"));

        if ($pure) {
            $codeValue = $code = RandString::letters(4);
            ManageLoginService::getInstance()->setVerifyCode($codeValue);

            VerifyCode::display($code, $width, $height, 22);
        } else {
            $a = rand(50, 99);
            $b = rand(0, 50);
            $handle = rand(1, 2);
            if ($handle == 1) {
                $codeValue = $a + $b;
                $code = $a . " + " . $b . " =";
            } else {
                $codeValue = $a - $b;
                $code = $a . " - " . $b . " =";
            }
            ManageLoginService::getInstance()->setVerifyCode($codeValue);

            VerifyCode::display($code, $width, $height, 22, true);
        }
    }
}