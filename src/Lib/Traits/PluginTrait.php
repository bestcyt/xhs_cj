<?php

namespace Mt\Lib\Traits;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Request;
use Fw\Exception\NotFoundException;

/**
 * 钩子片段
 */
trait PluginTrait
{
    use InstanceTrait;
    private $controller = null;
    private $action = null;
    private $module = null;
    private $uri = null;

    protected function __construct()
    {
        if (!isset($GLOBALS["plugin_dispatch_handle"]) || !$GLOBALS["plugin_dispatch_handle"]) {
            $app = App::getInstance();
            $routePath = Request::getInstance()->getRoutePath();
            $modules = $app->getModules();
            $path = $app->getControllerPath();

            //1.解析module,controller,action
            //seg1是指定支持的module的话,则以/module/controller/action进行匹配action文件,
            //否则以/controller/action进行匹配action文件。
            $uriSegments = explode('/', trim($routePath, '/'));
            $module = '';
            $controller = '';
            $action = '';
            $defaultSeg = 'index';
            $defaultFormattedSeg = 'Index';
            $seg1 = strtolower(trim(array_shift($uriSegments)));
            $seg2 = strtolower(trim(array_shift($uriSegments)));
            $seg3 = strtolower(trim(array_shift($uriSegments)));
            $formattedSeg1 = $seg1 == '' ? $defaultFormattedSeg : $app->formatUnderScoreToStudlyCaps($seg1);
            $formattedSeg2 = $seg2 == '' ? $defaultFormattedSeg : $app->formatUnderScoreToStudlyCaps($seg2);
            $formattedSeg3 = $seg3 == '' ? $defaultFormattedSeg : $app->formatUnderScoreToStudlyCaps($seg3);
            if ($seg1 != '' && in_array($seg1, $modules)) {
                //匹配/module/controller/action
                //下划线开头的action不允许通过url直接访问
                if ($formattedSeg3[0] == '_') {
                    throw new NotFoundException('disallowed action.');
                } elseif (is_file($path . '/' . $formattedSeg1 . '/' . $formattedSeg2 . '/' . $formattedSeg3 . '.php')) {
                    if ($seg2 != '' && $seg3 != '') {
                        $module = $seg1;
                        $controller = $seg2;
                        $action = $seg3;
                    } elseif ($seg2 != '') {
                        $module = $seg1;
                        $controller = $seg2;
                        $action = $defaultSeg;
                    } else {
                        $module = $seg1;
                        $controller = $defaultSeg;
                        $action = $defaultSeg;
                    }
                }
            } else {
                //匹配/controller/action
                //下划线开头的action不允许通过url直接访问
                if ($formattedSeg2[0] == '_') {
                    throw new NotFoundException('disallowed action.');
                } elseif (is_file($path . '/' . $formattedSeg1 . '/' . $formattedSeg2 . '.php')) {
                    if ($seg1 != '' && $seg2 != '') {
                        $controller = $seg1;
                        $action = $seg2;
                    } elseif ($seg1 != '') {
                        $controller = $seg1;
                        $action = $defaultSeg;
                    } else {
                        $controller = $defaultSeg;
                        $action = $defaultSeg;
                    }
                    $seg3 == '' || array_unshift($uriSegments, $seg3);
                }
            }

            //module允许为空,但controller和action不允许为空
            if ($controller == '' || $action == '') {
                throw new NotFoundException('action not exists.');
            }
            $GLOBALS["plugin_dispatch_handle"] = [
                'module' => strtolower($module),
                'controller' => strtolower($controller),
                'action' => strtolower($action),
            ];
        }

        $this->module = $GLOBALS["plugin_dispatch_handle"]["module"];
        $this->controller = $GLOBALS["plugin_dispatch_handle"]["controller"];
        $this->action = $GLOBALS["plugin_dispatch_handle"]["action"];
        $url = $this->controller . "/" . $this->action;
        $this->uri = $this->module ? $this->module . "/" . $url : $url;
    }
}