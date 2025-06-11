<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Cache\HeartBeatCache;
use Mt\Model\XiaohongshuAccountModel;
use Mt\Service\PythonService;

class Python extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $PythonService = PythonService::getInstance();
            $PythonService->restart();
            $this->success();
        }
    }

    protected function view()
    {
        $PythonService = PythonService::getInstance();
        $data = $PythonService->getScriptRunning();
        $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
        $matchine_list = $XiaohongshuAccountModel::MACHINE;
        $HeartBeatCache = HeartBeatCache::getInstance();
        foreach ($data as $key => $value) {
            $heart = [];
            foreach ($matchine_list as $k => $v) {
                $heart[] = [
                    "name" => $v,
                    "time" => intval($HeartBeatCache->get($k . ":" . $value["key"])),
                ];
            }
            $data[$key]["heart"] = $heart;
        }
        View::getInstance()
            ->assign("data", $data)
            ->render();
    }
}