<?php

namespace Mt\Lib;

use Fw\Http;
use Fw\InstanceTrait;
use Mt\Queue\Alert\AlertFeishuQueue;

/**
 * 飞书 群自定义机器人
 * Class FeishuRobot
 * @package Mt\Lib
 */
class FeishuRobot
{
    use InstanceTrait;

    /**
     * 发送消息
     * @param $title
     * @param $message
     * @param $async
     * @param array $user_mobile
     * @return mixed|null
     */
    public function send($title, $message, $async = false, array $user_mobile = [])
    {
        if ($async) {
            $AlertFeishuQueue = AlertFeishuQueue::getInstance();
            $AlertFeishuQueue->produce([
                "title" => $title,
                "message" => $message,
                "time" => time(),
            ]);
            return true;
        }
        //技术团队  小萝卜 机器人
        $webhook = "https://open.feishu.cn/open-apis/bot/v2/hook/78b96a3d-7e7c-45d3-9141-a87bea2b5b1d";
        $secret = "RcZcEJoyP4MWtXGCabszjf";
        if (empty($webhook)) {
            return;
        }
        if (!is_string($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }
        $timestamp = time();
        $sign = base64_encode(hash_hmac('sha256', "", $timestamp . "\n" . $secret, true));
        //默认@所有人
        $notice_arr = [
            'tag' => 'at',
            'user_id' => 'all',
            'user_name' => '所有人',
        ];
        //通知对应人
        if (!empty($user_mobile)) {
            //目前没有开通飞书应用（很贵 先不开）
            //todo
            //todo
        }
        $params = [
            "timestamp" => $timestamp,
            "sign" => $sign,
            "msg_type" => "post",
            "content" => [
                'post' => [
                    'zh_cn' => [
                        'title' => $title,
                        'content' => [[[
                            'tag' => 'text',
                            'text' => $message
                        ], $notice_arr]]]
                ]
            ],
        ];
        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $Http = Http::getInstance($webhook, $params);
        $result = $Http->setHeaders([
            "Content-Type: application/json; charset=utf-8",
        ])->postByMultiPart()->getJsonResult();
        return $result;
    }
}