<?php

namespace Mt\Lib;
/**
 * 验证码
 * Class VerifyCode
 * @package Mt\Lib
 */
class VerifyCode
{
    /**
     * 获取随机code
     * @param int $num 验证码位数
     * @param bool $needChar 是否需要字母
     * @return string
     */
    public static function getCode($num = 4, $needChar = false)
    {
        if ($num <= 0 || $num > 10) return '';

        $code = '';
        do {
            if ($needChar) {
                $randType = rand(0, 2);
                switch ($randType) {
                    case 0:
                        $code .= rand(3, 9);    //排除0，1，2有可能歧义的
                        break;
                    case 1:
                        $char = chr(rand(65, 89));//A-Y
                        if (!in_array($char, ['I', 'O'])) { //排除几项可能有歧义的
                            $code .= $char;
                        }
                        break;
                    default:
                        $char = chr(rand(97, 121));//a-y
                        if (!in_array($char, ['i', 'l', 'o'])) {    //排除几项可能有歧义的
                            $code .= $char;
                        }
                        break;
                }
            } else {
                $code .= rand(0, 9);
            }
        } while (strlen($code) < $num);
        return $code;
    }

    /**
     * 生成验证码图片
     * @param $code
     * @param int $width
     * @param int $height
     * @param int $fontSize
     * @param boolean $normal
     */
    public static function display($code, $width = 60, $height = 60, $fontSize = 22, $normal = false)
    {
        //======创建背景======
        //创建画布 给一个资源jubing
        $img = imagecreatetruecolor($width, $height);
        //背景颜色
        $color = imagecolorallocate($img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        //画出一个矩形
        imagefilledrectangle($img, 0, $height, $width, 0, $color);

        //======加载线条======
        //随机线条
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color);
        }
        //随机雪花
        for ($i = 0; $i < 45; $i++) {
            $color = imagecolorallocate($img, mt_rand(220, 255), mt_rand(220, 255), mt_rand(220, 255));
            imagestring($img, mt_rand(1, 5), mt_rand(0, $width), mt_rand(0, $height), '*', $color);
        }

        //======加载字体======
        $font = __DIR__ . "/font.ttf";//字体文件
        $codeLen = strlen($code);
        $_x = ($width / ($codeLen + 2));   //字体长度
        for ($i = 0; $i < $codeLen; $i++) {
            //文字颜色
            $color = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            //资源句柄 字体大小 倾斜度 字体长度  字体高度  字体颜色  字体  具体文本
            if ($normal) {
                imagettftext($img, $fontSize, 0, $_x * ($i + 1), $height / 1.4, $color, $font, $code[$i]);
            } else {
                imagettftext($img, $fontSize, mt_rand(-30, 30), $_x * ($i + 1) + mt_rand(1, 2), $height / 1.4, $color, $font, $code[$i]);
            }
        }

        //生成标头
        header('Content-type:image/png');
        //输出图片
        imagepng($img);
        //销毁结果集
        imagedestroy($img);
    }

}