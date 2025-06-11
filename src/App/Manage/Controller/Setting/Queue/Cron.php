<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Script\CronHealthMonitor;

class Cron extends BaseController
{
    public function main()
    {
        $date = trim($this->request->get("date")) ?: date("Ymd");
        //意外退出和内存暂用情况等
        $data = CronHealthMonitor::getInfoByDate($date);
        $fatal = $data["fatal"] ?: array();
        $warn = $data["warn"] ?: array();
        foreach ($fatal as $key => $value) {
            $fatal[$key] = json_decode($value, true);
            $fatal[$key]["file"] = $key;
            $fatal[$key]["start_at"] = date("Y-m-d H:i:s",$fatal[$key]["start_at"]);
        }
        foreach ($warn as $key => $value) {
            $warn[$key] = json_decode($value, true);
            $warn[$key]["file"] = $key;
        }
        $this->success([
            "fatal" => array_values($fatal),
            "warn" => array_values($warn),
        ]);
    }

    protected function view()
    {
        $date_arr = array();
        for ($i = 0; $i <= 10; $i++) {
            $time = time() - $i * 86400;
            $date_arr[date("Ymd", $time)] = date("Y-m-d", $time);
        }
        View::getInstance()
            ->assign("date_arr", $date_arr)
            ->render();
    }
}