<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>小红书点赞</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">

    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    <script src="/layuiadmin/layui/layui.js"></script>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script>
        /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
    </script>
    <script src="/js/jquery-1.11.1.min.js"></script>
</head>
<body class="layui-layout-body">
<?php $header=\Mt\App\Manage\Helper\Layout::headerLayUi(true);?>
<div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <!-- 头部区域 -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item layadmin-flexible" lay-unselect>
                    <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                        <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;" layadmin-event="refresh" title="刷新">
                        <i class="layui-icon layui-icon-refresh-3"></i>
                    </a>
                </li>
            </ul>
            <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="fullscreen">
                        <i class="layui-icon layui-icon-screen-full"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;">
                        <cite><?= !empty($header["cur_acct_info"]['real_name']) ? $header["cur_acct_info"]['real_name'] : (!empty($header["cur_acct_info"]['email']) ? $header["cur_acct_info"]['email'] : '');?></cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a lay-href="<?php echo U('/setting/account/reset_password');?>">修改密码</a></dd>
                        <hr/>
                        <dd id="logout_btn" layadmin-event="logout" logout-url="<?php echo U('/logout/index');?>" style="text-align: center;"><a>退出</a></dd>
                    </dl>
                </li>

                <li class="layui-nav-item layui-hide-xs" lay-unselect style="width: 6px;">
                    <a href="javascript:;"><i class="layui-icon layui-icon-more-vertical"></i></a>
                </li>
                <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>
                    <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>
                </li>
            </ul>
        </div>

        <!-- 侧边菜单 -->
        <div class="layui-side layui-side-menu">
            <div class="layui-side-scroll">
                <div class="layui-logo">
                    <span>小红书点赞</span>
                </div>

                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
                    <?php foreach($header['menus'] as $nav_menu):?>
                        <li data-name="<?php echo $nav_menu['id'];?>" class="layui-nav-item">
                            <a <?php if(!empty($nav_menu['controller_menus'])):?>href="javascript:;"<?php else:?>lay-href="<?php echo $nav_menu['url'];?>"<?php endif;?> lay-tips="<?php echo $nav_menu['name'];?>" lay-direction="2">
                                <i class="layui-icon <?php echo $nav_menu['icon'];?>"></i>
                                <cite><?php echo $nav_menu['name'];?></cite>
                            </a>
                            <?php if(!empty($nav_menu['controller_menus'])):?>
                            <dl class="layui-nav-child">
                                <?php foreach($nav_menu['controller_menus'] as $controller_menus):?>
                                <dd data-name="<?php echo $controller_menus['id'];?>">
                                    <?php if(empty($controller_menus['action_menus'])):?>
                                    <a lay-href="<?php echo $controller_menus['url'];?>"><?php echo $controller_menus['name'];?></a>
                                    <?php else:?>
                                    <a href="javascript:;"><?php echo $controller_menus['name'];?></a>
                                    <dl class="layui-nav-child">
                                        <?php foreach($controller_menus['action_menus'] as $action_menus):?>
                                        <dd data-name="<?php echo $action_menus['id'];?>">
                                            <a lay-href="<?php echo $action_menus['url'];?>"><?php echo $action_menus['name'];?></a>
                                        </dd>
                                        <?php endforeach;?>
                                    </dl>
                                    <?php endif;?>
                                </dd>
                                <?php endforeach;?>
                            </dl>
                            <?php endif;?>
                        </li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>

        <!-- 页面标签 -->
        <div class="layadmin-pagetabs" id="LAY_app_tabs" style="display: none;">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="home/console.html" lay-attr="home/console.html" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>


        <!-- 主体内容 -->
        <div class="layui-body" id="LAY_app_body" style="top: 50px;">
            <div class="layadmin-tabsbody-item layui-show">
                <iframe src="<?php echo U('/home/index');?>" frameborder="0" class="layadmin-iframe"></iframe>
            </div>
        </div>

        <!-- 辅助元素，一般用于移动设备下遮罩 -->
        <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
</div>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use('index');
</script>
<style>
    .layui-nav-tree .layui-nav-child dd.layui-this, .layui-nav-tree .layui-nav-child dd.layui-this a, .layui-nav-tree .layui-this, .layui-nav-tree .layui-this>a, .layui-nav-tree .layui-this>a:hover,.layui-this{
        background-color: #42a6cd!important;
    }
</style>
</body>
</html>