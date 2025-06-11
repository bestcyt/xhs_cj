<?php

namespace Mt\Bitmap;

use Mt\Lib\BitmapService;


class DemoBitmap extends BitmapService
{
    protected function _init()
    {
        $this->server_config = $this->getServerConfig('redis/main');//redis对应配置
        $this->shard_split = 10000000;//一个缓存key多少数据
    }
}