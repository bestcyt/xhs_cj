<?php

namespace Mt\Lib;

use Fw\InstanceTrait;

/**
 * Class Token
 * @package Mt\Lib
 */
class Token
{
    use InstanceTrait;
    const KEY = "sss:ccc:xhs";//加密解密秘钥
    const TYPE_MANAGE_FORGET = 1;//管理后台忘记密码
    const TYPE_MANAGE_FORGET_ACCOUNT = 2;//管理后台忘记密码-验证账号
    const TYPE_MANAGE_REDIRECT_LOGIN = 3;//控制台跳转登录
    const TYPE = [
        self::TYPE_MANAGE_FORGET => "manage_forget",
        self::TYPE_MANAGE_FORGET_ACCOUNT => "manage_forget_account",
    ];
    protected $salt = "";

    protected function __construct($salt = "common")
    {
        $this->salt = $salt;
    }

    /**
     * 生成token
     * @param array $data
     * @param int $expire_second 有效s数
     * @param int $type 类型
     * @return mixed|string
     */
    public function create(array $data, $expire_second = 120, $type = self::TYPE_MANAGE_FORGET)
    {
        $header = json_encode([
            "type" => $type,
            "method" => "AES-256-CBC",
            "expire" => time() + $expire_second,
        ], JSON_UNESCAPED_UNICODE);
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $str = $header . "#$#" . $content;
        $token = Crypt::encrypt($str, self::KEY . "_" . $this->salt);
        return $token;
    }

    /**
     * 解析token
     * @param string $token
     * @param int $type
     * @return bool|mixed
     */
    public function analysis($token, $type = self::TYPE_MANAGE_FORGET)
    {
        $str = @Crypt::decrypt($token, self::KEY . "_" . $this->salt);
        if (!$str) {
            return false;
        }
        $arr = explode("#$#", $str);
        $header = @json_decode($arr[0], true);
        $content = @json_decode($arr[1], true);
        if (!isset($header["type"]) || $header["type"] != $type || time() > $header["expire"]) {
            return false;
        }
        return $content;
    }
}