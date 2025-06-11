<?php

namespace Mt\Lib\Mail;

use Mt\Lib\LogType;

include_once __DIR__ . '/class.phpmailer.php';
include_once __DIR__ . '/class.smtp.php';

class Mail extends \PHPMailer
{
//    public function _send_mail($site, $username, $password, $fromname, $email, $subject, $content, $port = 25, $smtp_auth = true)
//    {
//        try {
//            $this->IsSMTP();
//            $this->IsHTML();
//            $this->SMTPAuth = $smtp_auth;
//            if ($site == 'smtp.gmail.com') {
//                $this->SMTPSecure = "ssl";                 // sets the prefix to the servier
//            }
//            //网易企业邮箱不支持tls
//            if ($site == 'smtp.qiye.163.com') {
//                $this->SMTPAutoTLS = false;
//            }
//            $this->Port = $port;
//            $this->Host = $site;
//            $this->Username = $username;
//            $this->Password = $password;
//            $this->From = $username;
//            $this->FromName = $fromname;
//            $this->CharSet = "UTF-8";
//            $email_arr = explode(',', $email);
//            if (count($email_arr) > 0) {
//                foreach ($email_arr as $_email) {
//                    if (!strpos($_email, '@')) continue;
//                    $this->AddAddress($_email);
//                }
//            } else {
//                $this->AddAddress($email);
//            }
//            $this->SetFrom($username, $fromname);
//            $this->Subject = $subject;
//            $this->MsgHTML($content);
//            $this->Send();
//            return true;
//        } catch (\phpmailerException $e) {
//            \Fw\App::getInstance()->getLogger()->error($e, LogType::FATAL_ALERT_EMAIL);
//            $error_message ['error_code'] = '20005';
//            $error_message ['error'] = '邮件发送失败';
//            return $error_message;
//        }
//    }
//坑逼阿里云的时候可以用下面的方法
    public function _send_mail($site, $username, $password, $fromname, $email, $subject, $content, $port = 465, $smtp_auth = true)
    {
        try {
            $this->IsSMTP();
            $this->IsHTML();
            //坑逼的 阿里云centos smtp只能用465端口,而且必须是ssl(正常默认25端口,不需要ssl)
            $this->SMTPAuth = $smtp_auth;
//            if($site =='smtp.gmail.com' ){
            $this->SMTPSecure = "ssl";                 // sets the prefix to the servier
//            }
            //网易企业邮箱不支持tls
            if ($site == 'smtp.qiye.163.com') {
                $this->SMTPAutoTLS = false;
            }
            $this->Port = $port;
            $this->Host = $site;
            $this->Username = $username;
            $this->Password = $password;
            $this->From = $username;
            $this->FromName = $fromname;
            $this->CharSet = "UTF-8";
            $email_arr = explode(',', $email);
            if (count($email_arr) > 0) {
                foreach ($email_arr as $_email) {
                    if (!strpos($_email, '@')) continue;
                    $this->AddAddress($_email);
                }
            } else {
                $this->AddAddress($email);
            }
            $this->SetFrom($username, $fromname);
            $this->Subject = $subject;
            $this->MsgHTML($content);
            $this->Send();
            return true;
        } catch (\phpmailerException $e) {
            \Fw\App::getInstance()->getLogger()->error($e, LogType::FATAL_ALERT_EMAIL);
            $error_message ['error_code'] = '20005';
            $error_message ['error'] = '邮件发送失败';
            return $error_message;
        }
    }

}
