<?php

namespace Mt\Logic;
use Mt\Cache\XhsWebSessionCache;

/**
 * 小红书业务
 * Class XiaohongshuLogic
 * @package Mt\Logic
 */
class XiaohongshuLogic
{
    use LogicTrait;
    protected $token = null;

    public function getNoteUrlByShareLink($share_link)
    {
        if (strpos($share_link, "/discovery/item/") !== false) {
            preg_match("/(https:\/\/www.xiaohongshu.com\/discovery\/item\/[a-zA-Z0-9].+)/", $share_link, $matches);
            if ($matches) {
                return $matches[0];
            }
        } elseif (strpos($share_link, "/explore/") !== false) {
            preg_match("/(https:\/\/www.xiaohongshu.com\/explore\/[a-zA-Z0-9].+)/", $share_link, $matches);
            if ($matches) {
                return $matches[0];
            }
        } else {
            preg_match("/(http\:\/\/xhslink\.com\/[A-Za-z]\/[0-9A-Za-z]\w+)/", $share_link, $matches);
            if ($matches) {
                return $matches[0];
            }
        }
        return '';
    }

    /**
     * 笔记分享链接获取笔记id
     * @param $noteUrl
     * @return mixed
     */
    public function getNoteIdByShareUrl($noteUrl)
    {
        if (strpos($noteUrl, "/discovery/item/") !== false || strpos($noteUrl, "/explore/") !== false) {
            $noteUrl = explode("?", $noteUrl)[0];
        } else {
            preg_match('/https?:\/\/[^，]+/', $noteUrl, $matches);
            $noteUrl = $matches[0];
            $header = get_headers($noteUrl, 1);
            $realUrl = $header['Location'];
            if (is_array($realUrl)) {
                $realUrl = $realUrl[0];
            }
            $arr = parse_url($realUrl);
            $noteUrl = $arr["path"];
        }
        $noteUrl_arr = explode("/", $noteUrl);
        return end($noteUrl_arr);
    }

    /**
     * 判断笔记是否存在
     * @param $noteUrl
     * @return bool
     */
    public function noteExists($noteUrl)
    {
        //非手机端分享链接，直接判定存在，因为容易设备风控，从而导致404
        if (strpos($noteUrl, "/discovery/item/") === false || strpos($noteUrl, "/explore/") === false) {
            return true;
        }
        $header = get_headers($noteUrl, 1);
        $noteUrl = $header['Location'];
        if (is_array($noteUrl)) {
            $noteUrl = $noteUrl[0];
        }
        $header = get_headers($noteUrl, 1);
        if (!empty($header["Location"])) {
            if (is_array($header["Location"])) {
                $header["Location"] = $header["Location"][0];
            }
            if (strpos($header["Location"], "/404") !== false) {
                //有些笔记需要加上 web_session 才能访问
                //web_session 在cookie里面
                $XhsWebSessionCache = XhsWebSessionCache::getInstance();
                $web_session = $XhsWebSessionCache->get("web_session");
                if (empty($web_session)) {
                    $default_web_id_arr = [
                        "030037a000481130e8f1f4b43c2f4a2550d539",
                        "030037af7653c71e5fcacb563d2f4a4ca3d9b5",
                        "04006953d5090f39e03ec15c703a4bdb3ca5aa",
                        "030037af767fee133e96d66d3d2f4abb515997",
                    ];
                    $web_session = $default_web_id_arr[array_rand($default_web_id_arr)];
                }
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "Cookie: web_session=" . $web_session
                    ]
                ]);
                $header = get_headers($noteUrl, 1, $context);
                if (empty($header["Location"])) {
                    return true;
                }
                if (is_array($header["Location"])) {
                    $header["Location"] = $header["Location"][0];
                }
                if (strpos($header["Location"], "/404") === false) {
                    return true;
                }
                return false;
            }
        }
        return true;
    }
}