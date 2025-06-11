<?php
namespace Mt\Lib\Traits;
use Fw\InstanceTrait;

/**
 * 视图助手片段
 */
trait HelperTrait{
    use InstanceTrait;
    //加载视图
    public function render($view,array $data=array()){
        extract($data);
        include(get_view($view));
    }
    //加载视图
    public static function renderStatic($view,array $data=array()){
        extract($data);
        include(get_view($view));
    }
}