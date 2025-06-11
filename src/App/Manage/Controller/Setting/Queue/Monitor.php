<?php

namespace Mt\App\Manage\Controller\Setting\Queue;

//队列监控
use Fw\App;
use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Script\QueueDispatchModel;
use Mt\Lib\Script\QueueModel;

class Monitor extends BaseController
{

    protected $view = null;
    private $red_start_elapsed_time = 12 * 3600;
    private $red_update_elapsed_time = 2 * 3600;
    private $red_mem_used = 157286400;
    private $red_cpu_used = 25;
    private $yellow_start_elapsed_time = 6 * 3600;
    private $yellow_update_elapsed_time = 3600;
    private $yellow_mem_used = 104857600;
    private $yellow_cpu_used = 15;

    public function main()
    {

        $this->view = View::getInstance();
        $queue_id = trim($this->request->get('queue_id'));
        $server_ip = trim($this->request->get('server_ip'));
        $show_type = $this->request->get('show_type');
        if ($show_type != 'server_ip') {
            $show_type = 'queue';
        }

        //报警参数--方便前后端统一配置
        $red_start_elapsed_time = $this->red_start_elapsed_time;
        $red_update_elapsed_time = $this->red_update_elapsed_time;
        $red_mem_used = $this->red_mem_used;
        $red_cpu_used = $this->red_cpu_used;
        $yellow_start_elapsed_time = $this->yellow_start_elapsed_time;
        $yellow_update_elapsed_time = $this->yellow_update_elapsed_time;
        $yellow_mem_used = $this->yellow_mem_used;
        $yellow_cpu_used = $this->yellow_cpu_used;

        //获取队列
        $QueueModel = QueueModel::getInstance();
        $queue_list = $QueueModel->load();

        //获取队列进程列表
        $QueueDispatchModel = QueueDispatchModel::getInstance();
        $data = $QueueDispatchModel->load(['alive_at >' => 0]);

        //机器筛选列表
        $server_data = array_filter(array_column($data, 'hostname', 'server_ip'));

        //队列筛选列表
        $queue_data = array_filter(array_column($data, 'file', 'queue_id'));

        //过滤队列
        if ($queue_id) {
            $data = preKey($data, 'queue_id', 'id');
            $data = isset($data[$queue_id]) ? $data[$queue_id] : array();
        }

        //过滤机器
        if ($server_ip) {
            $data = preKey($data, 'server_ip', 'id');
            $data = $data[$server_ip];
        }

        //变量初始化准备
        $monitor_data = [];
        $total_process_num = 0;
        $total_red_num = $total_yellow_num = 0;
        $red_arr = $yellow_arr = [];
        $now = App::getCurrentTime();
        foreach ($data as $_data) {
            //---使用时间
            $elapsed_time = $now - $_data['process_alive_at'];
            $hour = floor($elapsed_time / 3600);
            $minute = floor(($elapsed_time - $hour * 3600) / 60);
            $second = (int)$elapsed_time - $hour * 3600 - $minute * 60;
            $_data['process_check_elapsed'] = $hour . 'h ' . $minute . 'm ' . $second . 's';
            $_data["process_at"] = date("m-d H:i:s", $_data["process_alive_at"]);

            $update_elapsed_time = $now - $_data['alive_at'];
            $hour = floor($update_elapsed_time / 3600);
            $minute = floor(($update_elapsed_time - $hour * 3600) / 60);
            $second = (int)$update_elapsed_time - $hour * 3600 - $minute * 60;
            $_data['update_elapsed'] = $hour . 'h ' . $minute . 'm ' . $second . 's';
            $_data['update_at'] = date('m-d H:i:s', $_data['alive_at']);

            $tmp_server_ip = $_data['server_ip'];
            $tmp_hostname = $_data['hostname'];
            $tmp_queue = $_data['file'];
            $tmp_queue_id = $_data['queue_id'];

            //如果是已经下线，但是仍然还在跑，写入红色警告
            $elapsed_ignore = $elapsed_time > 3 * 60;
            if (empty($queue_list[$tmp_queue_id]) && $elapsed_ignore
                && (empty($queue_id) || $queue_id != $tmp_queue_id)    //单独筛选某队列
                && (empty($server_ip))    //单独筛选某机器
            ) {
                $_data['is_red'] = true;
                $_data['alert_msg'] = '队列已下线/不存在，仍然在跑';
                $red_arr[] = $_data;

                //如果是已经下线，但是仍然还在跑，写入红色警告
            } else if ($queue_list[$tmp_queue_id]['status'] != $QueueModel::STATUS_ON && $elapsed_ignore
                && (empty($queue_id) || $queue_id != $tmp_queue_id)    //单独筛选某队列
                && (empty($server_ip))    //单独筛选某机器
            ) {
                $_data['is_red'] = true;
                $_data['alert_msg'] = '队列已关闭，仍然在跑';
                $red_arr[] = $_data;

            } else {

                $match_filter = ((empty($queue_id) || $queue_id == $tmp_queue_id) && (empty($server_ip) || $server_ip == $tmp_server_ip));
                //红色字体：启动超过12小时，最后update时间超过3小时, 或内存>100m, 或CPU超过20%
                if (($elapsed_time > $red_start_elapsed_time) && $match_filter) {
                    $_data['is_red'] = true;
                    $_data['alert_msg'] = '超过' . round($red_start_elapsed_time / 3600) . '小时没重启';
                    $red_arr[] = $_data;
                } else if (($update_elapsed_time > $red_update_elapsed_time) && $match_filter) {
                    $_data['is_red'] = true;
                    $_data['alert_msg'] = '超过' . round($red_update_elapsed_time / 3600) . '小时没更新pid文件';
                    $red_arr[] = $_data;
                } else if (($_data['mem_used'] > $red_mem_used) && $match_filter) {
                    $_data['is_red'] = true;
                    $_data['alert_msg'] = '内存使用超过' . round($red_mem_used / 1024 / 1024, 4) . 'MB';
                    $red_arr[] = $_data;
                } else if (($_data['cpu_used'] > $red_cpu_used) && $match_filter) {
                    $_data['is_red'] = true;
                    $_data['alert_msg'] = 'cpu使用超过' . $red_cpu_used . '%';
                    $red_arr[] = $_data;
                } else {
                    $_data['is_red'] = false;
                }

                //橙色字体：启动超过6小时，最后update时间超过60分钟, 或内存>50m, 或CPU>10%
                if (empty($_data['is_red'])) {
                    if (($elapsed_time > $yellow_start_elapsed_time) && $match_filter) {
                        $_data['is_yellow'] = true;
                        $_data['alert_msg'] = '超过' . round($yellow_start_elapsed_time / 3600) . '小时没重启';
                        $yellow_arr[] = $_data;
                    } else if (($update_elapsed_time > $yellow_update_elapsed_time) && $match_filter) {
                        $_data['is_yellow'] = true;
                        $_data['alert_msg'] = '超过' . round($yellow_update_elapsed_time / 3600) . '小时没更新pid文件';
                        $yellow_arr[] = $_data;
                    } else if (($_data['mem_used'] > $yellow_mem_used) && $match_filter) {
                        $_data['is_yellow'] = true;
                        $_data['alert_msg'] = '内存使用超过' . round($yellow_mem_used / 1024 / 1024, 4) . 'MB';
                        $yellow_arr[] = $_data;
                    } else if (($_data['cpu_used'] > $yellow_cpu_used) && $match_filter) {
                        $_data['is_yellow'] = true;
                        $_data['alert_msg'] = 'cpu使用超过' . $yellow_cpu_used . '%';
                        $yellow_arr[] = $_data;
                    } else {
                        $_data['is_yellow'] = false;
                    }
                } else {
                    $_data['is_yellow'] = false;
                }

            }

            if (!empty($server_ip) && $tmp_server_ip != $server_ip) {
                continue;
            }
            if (!empty($queue_id) && $tmp_queue_id != $queue_id) {
                continue;
            }

            if (!empty($_data['is_red'])) {
                $total_red_num++;
            }
            if (!empty($_data['is_yellow'])) {
                $total_yellow_num++;
            }


            $total_process_num++;

            //---按server_ip维度
            if ($show_type == 'server_ip') {
                if (!isset($monitor_data[$tmp_server_ip])) {
                    $monitor_data[$tmp_server_ip] = [
                        'server_ip' => $tmp_server_ip,
                        'hostname' => $tmp_hostname,
                        'queue_num' => 1,
                        'process_num' => 1,
                        'red_num' => !empty($_data['is_red']) ? 1 : 0,
                        'yellow_num' => !empty($_data['is_yellow']) ? 1 : 0,
                        'queue_list' => [$tmp_queue => [
                            'name' => $tmp_queue,
                            'process_num' => 1,
                            'pid_list' => [$_data]
                        ],
                        ],
                    ];
                } else {
                    $monitor_data[$tmp_server_ip]['process_num']++;
                    if (!empty($_data['is_red'])) {
                        $monitor_data[$tmp_server_ip]['red_num']++;
                    }
                    if (!empty($_data['is_yellow'])) {
                        $monitor_data[$tmp_server_ip]['yellow_num']++;
                    }

                    if (!isset($monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue])) {
                        $monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue] = [
                            'name' => $tmp_queue,
                            'process_num' => 1,
                            'pid_list' => [$_data]
                        ];

                    } else {
                        $monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['process_num']++;
                        $monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['pid_list'][] = $_data;
                    }

                    if (empty($monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['is_yellow']) && $_data['is_yellow']) {
                        $monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['is_yellow'] = $_data['is_yellow'];
                    }
                    if (empty($monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['is_red']) && $_data['is_red']) {
                        $monitor_data[$tmp_server_ip]['queue_list'][$tmp_queue]['is_red'] = $_data['is_red'];
                    }


                    $monitor_data[$tmp_server_ip]['queue_num'] = count($monitor_data[$tmp_server_ip]['queue_list']);
                }

                //pr($monitor_data);


                //---按队列类型维度
            } else {

                if (!isset($monitor_data[$tmp_queue])) {
                    $monitor_data[$tmp_queue] = [
                        'name' => $tmp_queue,
                        'server_num' => 1,
                        'process_num' => 1,
                        'red_num' => !empty($_data['is_red']) ? 1 : 0,
                        'yellow_num' => !empty($_data['is_yellow']) ? 1 : 0,
                        'server_list' => [$tmp_server_ip => [
                            'server_ip' => $tmp_server_ip,
                            'hostname' => $tmp_hostname,
                            'process_num' => 1,
                            'pid_list' => [$_data]
                        ],
                        ],
                    ];

                } else {
                    $monitor_data[$tmp_queue]['process_num']++;
                    if (!empty($_data['is_red'])) {
                        $monitor_data[$tmp_queue]['red_num']++;
                    }
                    if (!empty($_data['is_yellow'])) {
                        $monitor_data[$tmp_queue]['yellow_num']++;
                    }

                    if (!isset($monitor_data[$tmp_queue]['server_list'][$tmp_server_ip])) {
                        $monitor_data[$tmp_queue]['server_list'][$tmp_server_ip] = [
                            'server_ip' => $tmp_server_ip,
                            'hostname' => $tmp_hostname,
                            'process_num' => 1,
                            'pid_list' => [$_data]
                        ];

                    } else {
                        $monitor_data[$tmp_queue]['server_list'][$tmp_server_ip]['process_num']++;
                        $monitor_data[$tmp_queue]['server_list'][$tmp_server_ip]['pid_list'][] = $_data;
                    }
                    $monitor_data[$tmp_queue]['server_num'] = count($monitor_data[$tmp_queue]['server_list']);
                }


                if (empty($monitor_data[$tmp_queue]['is_yellow']) && $_data['is_yellow']) {
                    $monitor_data[$tmp_queue]['is_yellow'] = $_data['is_yellow'];
                }
                if (empty($monitor_data[$tmp_queue]['is_red']) && $_data['is_red']) {
                    $monitor_data[$tmp_queue]['is_red'] = $_data['is_red'];
                }
            }

        }

        //计算筛选过后的机器数量 和 队列数量
        if ($show_type == 'server_ip') {
            $tmp_arr = [];
            foreach ($monitor_data as $mv) {
                $tmp_arr = array_merge($tmp_arr, array_keys($mv['queue_list']));
            }
            $total_queue_num = $tmp_arr ? count(array_unique($tmp_arr)) : 0;
            $total_server_num = count($monitor_data);
        } else {
            $tmp_arr = [];
            foreach ($monitor_data as $mv) {
                $tmp_arr = array_merge($tmp_arr, array_keys($mv['server_list']));
            }
            $total_server_num = $tmp_arr ? count(array_unique($tmp_arr)) : 0;
            $total_queue_num = count($monitor_data);
        }

        unset($cache_data);
        ksort($server_data);
        ksort($queue_data);


        // 检查已经开启开关，但是还没跑的队列
        foreach ($queue_list as $qv) {
            if (!empty($queue_id) && $queue_id != $qv['id']) {
                continue;
            }
            if (!empty($server_ip)) {
                continue;
            }
            if ($qv['status'] == $QueueModel::STATUS_ON && !isset($queue_data[$qv['id']])) {
                $yellow_arr[] = [
                    'key' => $qv['file'],
                    'id' => $qv['id'],
                    'is_yellow' => true,
                    'is_red' => false,
                    'alert_msg' => '队列已开启，但没有进程启动',
                    'no_start' => true,
                ];

                $total_yellow_num++;
            }
        }
        $this->view->assign("total_server_num", $total_server_num);
        $this->view->assign("yellow_update_elapsed_time", $yellow_update_elapsed_time);
        $this->view->assign("total_queue_num", $total_queue_num);
        $this->view->assign("total_process_num", $total_process_num);
        $this->view->assign("total_red_num", $total_red_num);
        $this->view->assign("total_yellow_num", $total_yellow_num);
        $this->view->assign("server_data", $server_data);
        $this->view->assign("server_ip", $server_ip);
        $this->view->assign("queue_data", $queue_data);
        $this->view->assign("queue_id", $queue_id);
        $this->view->assign("show_type", $show_type);
        $this->view->assign("yellow_start_elapsed_time", $yellow_start_elapsed_time);
        $this->view->assign("yellow_mem_used", $yellow_mem_used);
        $this->view->assign("yellow_cpu_used", $yellow_cpu_used);
        $this->view->assign("total_yellow_num", $total_yellow_num);
        $this->view->assign("red_start_elapsed_time", $red_start_elapsed_time);
        $this->view->assign("red_update_elapsed_time", $red_update_elapsed_time);
        $this->view->assign("red_mem_used", $red_mem_used);
        $this->view->assign("red_cpu_used", $red_cpu_used);
        $this->view->assign("total_red_num", $total_red_num);
        $this->view->assign("monitor_data", $monitor_data);
        $this->view->assign("red_arr", $red_arr);
        $this->view->assign("yellow_arr", $yellow_arr);
        $this->view->assign("from", trim($this->request->get("from")));
        $this->view->render();
    }
}