<?php

namespace Mt\App\Script\Console;

use Fw\Controller;

class Error extends Controller
{
    /**
     * 整个Script目录只是遵循框架规范,将script的报错都引导过来
     * @param \Exception $e
     */
    public function main($e)
    {
        echo "<pre>".$e->getMessage()."</pre>";
        echo "<pre>" . ($e->getTraceAsString()) . "</pre>";
        phpErrorCallback($e);
    }
}