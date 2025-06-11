<?php

namespace Mt\Lib;

class RandString
{
    private static $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; //数字+大小写字母
    private static $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; //大小写字母
    private static $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~!@#$%^&*()'; //数字+大小写字母+特殊字符
    private static $number = '0123456789';

    public static function string($minLength, $maxLength = 0)
    {
        return self::generate($minLength, $maxLength, self::$string);
    }

    public static function letters($minLength, $maxLength = 0)
    {
        return self::generate($minLength, $maxLength, self::$letters);
    }

    public static function chars($minLength, $maxLength = 0)
    {
        return self::generate($minLength, $maxLength, self::$chars);
    }

    public static function number($minLength, $maxLength = 0)
    {
        return self::generate($minLength, $maxLength, self::$number);
    }

    private static function generate($minLength, $maxLength, $seedString)
    {
        if ($minLength <= 0) {
            return '';
        }
        if ($minLength > $maxLength) {
            $len = $minLength;
        } else {
            $len = mt_rand($minLength, $maxLength);
        }
        $strCnt = strlen($seedString);
        $strMaxIdx = $strCnt - 1;
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $idx = mt_rand(0, $strMaxIdx);
            $result .= $seedString[$idx];
        }
        return $result;
    }
}