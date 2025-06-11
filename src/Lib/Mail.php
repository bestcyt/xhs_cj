<?php

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Mt\Lib\Mail\Mail as MailBase;
use Mt\Queue\Alert\AlertMailQueue;

class Mail
{
    use InstanceTrait;

    /**
     * 发送邮件
     * @param string $email 收件人,多个用,隔开
     * @param string $subject 主题
     * @param string $content 内容
     * @param bool $async 是否异步
     * @return bool
     */
    public function send_common($email, $subject, $content, $async = false)
    {
        if ($async) {
            $AlertMailQueue = AlertMailQueue::getInstance();
            $AlertMailQueue->produce([
                "email" => $email,
                "subject" => $subject,
                "content" => $content,
                "time" => time(),
            ]);
            return true;
        }
        $fromname = app_env("mail.fromname");
        $post_data = array("tomail" => $email, "fromname" => $fromname, "subject" => $subject, "content" => $content);
        return $this->send($post_data);
    }

    /**
     * 发送邮件
     * @param $post_data
     * @return bool
     */
    protected function send($post_data)
    {
        if (!is_array($post_data) || !isset($post_data['tomail'])) {
            return false;
        }
        $email = $post_data['tomail'];
        $fromname = trim($post_data['fromname']);
        $subject = trim($post_data['subject']);
        $content = trim($post_data['content']);
        if (empty($fromname)) {
            $error_message['error_code'] = '20002';
            $error_message['error'] = '邮件主题为空';
            return $error_message;
        }
        if (empty($subject)) {
            $error_message['error_code'] = '20003';
            $error_message['error'] = '邮件标题为空';
            return $error_message;
        }
        if (empty($content)) {
            $error_message['error_code'] = '20004';
            $error_message['error'] = '邮件内容为空';
            return $error_message;
        }

        $current_meitu_smtp_config = App::getInstance()->env("mail");

        $site = $current_meitu_smtp_config['site'];
        $username = $current_meitu_smtp_config['username'];
        $password = $current_meitu_smtp_config['password'];

        $mail = new MailBase();
        $result = $mail->_send_mail($site, $username, $password, $fromname, $email, $subject, $content);
        if ($result === true) {
            return true;
        }
        //失败重试
        if ($result['error_code']) {
            $result = $mail->_send_mail($site, $username, $password, $fromname, $email, $subject, $content);
        }
        if ($result['error_code']) {
            $error_message['error_code'] = '20005';
            $error_message['error'] = '邮件发送失败';
            return $error_message;
        } else {
            return true;
        }
    }
}