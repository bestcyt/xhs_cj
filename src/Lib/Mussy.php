<?php
/**
 * 各种杂项功能
 */

namespace Mt\Lib;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Request;

class Mussy
{
    use InstanceTrait;

    /**
     * 发送致命/严重错误提醒邮件（异步）
     * @param string $subject
     * @param string $content
     * @param string $severity 严重程度说明，参考取值 信息<警告<严重<灾难
     * @param boolean $async 是否异步
     * @return boolean
     */
    public function fatal_alert_email($subject, $content, $severity = '未分类', $async = true)
    {
        $content = substr($content, 0, 5000);//出现过mc 压缩内容超过1M，然后set失败，内容解压后的字符串几十M干进来，把redis_queue容量搞死了，做个限制
        $subject = App::getInstance()->env("mail.flag") . '【' . $severity . '】' . $subject;

        //给content加点信息，方便快速排查(比如记录用户信息等)
        $other_content = "";

        //方便全局排障
        $request_id = Request::getInstance()->getReqId();

        $ext_content = [];
        $developer_str = $this->getDeveloper();
        $developer_str = $developer_str ? ' | 开发者:' . $developer_str : "";
        $ext_content[] = "request_id:" . $request_id . ' | ' . "ip:" . getIP() . $developer_str;
        if ($other_content) {
            $ext_content[] = $other_content;
        }
        $ext_content[] = '宿主IP:' . Docker::getInstance()->getContainerHostIp() . ' | ' . 'hostname:' . gethostname() . ' | ' . 'pid:' . getmypid() . " | " . "time:" . date("Y-m-d H:i:s", App::getCurrentTime());

        if (empty($_SERVER['argv'])) {
            $ext_content[] = "script:'http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'";
        } else {
            $ext_content[] = 'logname:' . (empty($_SERVER['LOGNAME']) ? "" : $_SERVER['LOGNAME']);
            // 判断是否绝对路径
            preg_match('/^\/.*\.php$/', $_SERVER['argv'][0], $match);
            if (empty($match)) {
                $ext_content[] = "script:" . $_SERVER['PWD'] . DIRECTORY_SEPARATOR . implode(" ", $_SERVER['argv']);
            } else {
                $ext_content[] = "script:" . implode(" ", $_SERVER['argv']);
            }
        }
        $content = implode("\n", $ext_content) . "\n\n" . $content;
        $content = '<pre>' . $content . '</pre>';

        $to_users = App::getInstance()->env("mail.mtc_error");
        if (isset($GLOBALS["to_users"])) {
            $to_users = $GLOBALS["to_users"];
        }

        //记排障日志
        App::getInstance()->getLogger()->debug([$subject, $content], LogType::FATAL_ALERT_EMAIL);
        //开启调试就不发送报警了
        if (isDebug() && $async) {
            return true;
        }
        $Mail_model = Mail::getInstance();
        return $Mail_model->send_common($to_users, $subject, $content, $async);
    }

    /**
     * 获取开发环境本地开发者
     * @return array|false|string
     */
    protected function getDeveloper()
    {
        if (!isDevelop()) {
            return "";
        }
        return getenv("PHP_DEVELOPER") ?: "";
    }

    /**
     * 发送致命/严重错误提醒飞书机器人（异步）
     * @param string $subject
     * @param string $content
     * @param string $severity 严重程度说明，参考取值 信息<警告<严重<灾难
     * @param boolean $async 是否异步
     * @return boolean
     */
    public function fatal_alert_feishu($subject, $content, $severity = '未分类', $async = true)
    {
        $content = substr($content, 0, 5000);//出现过mc 压缩内容超过1M，然后set失败，内容解压后的字符串几十M干进来，把redis_queue容量搞死了，做个限制
        $subject = App::getInstance()->env("mail.flag") . '【' . $severity . '】' . $subject;

        //给content加点信息，方便快速排查(比如记录用户信息等)
        $other_content = "";

        //方便全局排障
        $request_id = Request::getInstance()->getReqId();

        $ext_content = [];
        $developer_str = $this->getDeveloper();
        $developer_str = $developer_str ? ' | 开发者:' . $developer_str : "";
        $ext_content[] = "request_id:" . $request_id . ' | ' . "ip:" . getIP() . $developer_str;
        if ($other_content) {
            $ext_content[] = $other_content;
        }
        $ext_content[] = '宿主IP:' . Docker::getInstance()->getContainerHostIp() . ' | ' . 'hostname:' . gethostname() . ' | ' . 'pid:' . getmypid() . " | " . "time:" . date("Y-m-d H:i:s", App::getCurrentTime());

        if (empty($_SERVER['argv'])) {
            $ext_content[] = "script:'http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'";
        } else {
            $ext_content[] = 'logname:' . (empty($_SERVER['LOGNAME']) ? "" : $_SERVER['LOGNAME']);
            // 判断是否绝对路径
            preg_match('/^\/.*\.php$/', $_SERVER['argv'][0], $match);
            if (empty($match)) {
                $ext_content[] = "script:" . $_SERVER['PWD'] . DIRECTORY_SEPARATOR . implode(" ", $_SERVER['argv']);
            } else {
                $ext_content[] = "script:" . implode(" ", $_SERVER['argv']);
            }
        }
        $content = implode("\n", $ext_content) . "\n\n" . $content;

        //记排障日志
        App::getInstance()->getLogger()->debug([$subject, $content], LogType::FATAL_ALERT_FEISHU);
        //开启调试就不发送报警了
        if (isDebug() && $async) {
            return true;
        }
        $FeishuRobot = FeishuRobot::getInstance();
        return $FeishuRobot->send($subject, $content, $async);
    }

}