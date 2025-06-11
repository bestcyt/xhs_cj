<?php

namespace Mt\Lib\Script;

use Fw\App;
use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Lib\LogType;
use Mt\Lib\Mussy;
use Mt\Model\Model;

class QueueDispatchModel extends Model
{
    use TableTrait {
        insert as private _insert;
        update as private _update;
        delete as private _delete;
        getOne as private _getOne;
        getMulti as private _getMulti;
    }
    use InstanceTrait;

    protected function __construct()
    {
        $this->table = 'queue_dispatch';
        $this->dbGroup = $this->getScriptDbGroup();
    }

    public function insertOrUpdate($data)
    {
        if ($data['id']) {
            $config_data = $this->getOne($data['id']);
            if ($config_data) {
                $result = $this->update($data['id'], $data);
                return $result;
            }
        }

        $result = $this->insert($data);

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $result;
    }

    //获取一个队列分发信息

    public function getOne($queue_id)
    {
        $data = $this->getMulti($queue_id);
        return $data[$queue_id] ? $data[$queue_id] : array();
    }

    //批量获取多个队列分发信息
    public function getMulti($queue_ids)
    {
        if (!$queue_ids) return [];
        $queue_ids = is_array($queue_ids) ? $queue_ids : [$queue_ids];
        $data = $this->db()->from($this->table)->select("*")->where("id", $queue_ids, "IN")->fetchAll();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return preKey($data, 'id');
    }

    //添加一个队列分发

    public function update($queue_id, $data)
    {
        $r = $this->db()->where("id", $queue_id)->update($this->table, $data)->exec();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    //添加多个队列分发

    public function insert($data)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $data['created_at'] = App::getCurrentTime();
        $data['pool'] = $pool;
        $r = $this->db()->insert($this->table, $data)->exec();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    //更新一个队列分发

    public function delete($id)
    {
        $r = $this->db()->where('id IN', array($id))->delete($this->table)->exec();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    //插入/更新一个队列分发

    /**
     * 获取队列数量
     */
    public function getCounts()
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $data = array();
        $where = ["pool" => $pool];
        $data_total = $this->db()->select("queue_id,count(file) as count,pool")->groupBy("queue_id,pool")->multiWhere($where)->from($this->table)->fetchAll();
        $data_alive = $this->db()->select("queue_id,count(file) as count,pool")->where("alive_at", 0, ">")->multiWhere($where)->groupBy("queue_id,pool")->from($this->table)->fetchAll();

        foreach ($data_total as $v) {
            $data[$v['queue_id']][$v['pool']]['total'] = $v['count'];
            $data[$v['queue_id']][$v['pool']]['alive'] = 0;
        }

        foreach ($data_alive as $v) {
            $data[$v['queue_id']][$v['pool']]['alive'] = $v['count'];
        }
        return $data;
    }

    //删除一个队列分发

    public function deleteByQueueId($queue_id)
    {
        return $this->deleteByQueueIds($queue_id);
    }

    //删除多个队列分发

    public function deleteByQueueIds($queue_ids)
    {
        $queue_ids = is_array($queue_ids) ? $queue_ids : splitNumber($queue_ids);
        if (empty($queue_ids)) return true;
        $r = $this->db()->where('queue_id', $queue_ids, "IN")->delete($this->table)->exec();

        //用完关闭，因为几K个队列会占用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    public function updateQueueDispatchByNum($queue_info_or_id, $task_num = [])
    {
        if (is_numeric($queue_info_or_id)) {
            $queue_id = $queue_info_or_id;
            $QueueModel = QueueModel::getInstance();
            $queue_info = $QueueModel->getOne($queue_id);
        } else {
            $queue_info = $queue_info_or_id;
            $queue_id = $queue_info['id'];
        }
        if (!$queue_info) {
            return false;
        }

        if (!$task_num) {
            $task_num = $queue_info['number'];
        }
        if (!is_array($task_num)) {
            $pool = App::getInstance()->getEnvironment();
            if (isDevelop()) {
                $pool = "pre";
            }
            $task_num = array(
                $pool => $task_num,
            );
        }

        $dispatch_data = $this->load(['queue_id' => $queue_id]);
        $dispatch_data = preKey($dispatch_data, 'pool', 'id');
        $delete_ids = $insert_data = [];
        foreach ($task_num as $pool => $need_count) {
            if (!isset($dispatch_data[$pool])) {
                $dispatch_data[$pool] = [];
            }
            $current_count = count($dispatch_data[$pool]);
            if ($current_count > $need_count) {  //移除
                if ($need_count) {
                    $delete_ids = array_keys(array_slice($dispatch_data[$pool], 0, $current_count - $need_count, true)); //从末端删除
                } else {
                    $delete_ids = array_merge($delete_ids, array_keys($dispatch_data[$pool]));
                }
            }
            if ($current_count < $need_count) {  //增加
                $add_count = $need_count - $current_count;
                for ($i = 0; $i < $add_count; $i++) {
                    $insert_data[] = array(
                        'file' => $queue_info['file'],
                        'pool' => $pool,
                        'queue_id' => $queue_id,
                    );
                }
            }
        }

        if ($delete_ids) {
            $this->deleteMulti($delete_ids);
        }
        if ($insert_data) {
            $this->insertMulti($insert_data);
        }

        return true;
    }


    //-----------------------------------功能函数-------------------------------------------

    //移除某个队列的分发

    /**
     * 分页查询队列分发
     * @param array $options
     * @return array
     */
    public function load($options = [])
    {
        if (isset($options['page']) && isset($options['count'])) {
            $this->db()->page($options["page"])->count($options["count"]);
            unset($options["page"], $options["count"]);
        }
        $fields = '*';
        if (isset($options['fields'])) {
            $fields = $options['fields'];
            unset($options["fields"]);
        }
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $where = ['pool' => $pool];
        $where = array_merge($where, $options);
        $data = $this->db()->forceMaster()->multiWhere($where)->from($this->table)->select($fields)->orderBy("created_at desc")->fetchAll();
        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return empty($data) ? [] : preKey($data, 'id');
    }

    //移除多个队列的分发

    public function deleteMulti(array $ids)
    {
        if (!$ids) return false;
        $r = $this->db()->where('id IN', $ids)->delete($this->table)->exec();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    //根据数量更新队列分配

    public function insertMulti($data)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        foreach ($data as $k => $_data) {
            $data[$k]['created_at'] = App::getCurrentTime();
            $data[$k]['pool'] = $pool;
        }

        $r = $this->db()->insertBatch($this->table, $data)->exec();

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $r;
    }

    function getAwaitTasks($num = 50)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $await_tasks = $this->db()->from($this->table)->select("*")
            ->multiWhere([
                'alive_at' => 0,
                'pool' => $pool,
            ])->limit(intval($num))->fetchAll();
        if ($await_tasks) {
            shuffle($await_tasks);
        }

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return $await_tasks;
    }

    function activeTask($dispatch_info = [])
    {
        $script = app_root_path() . "/src/App/Script/Queue/" . $dispatch_info['file'] . '.php';
        if (!file_exists($script)) {
            return false;
        }
        $r = $this->getOne($dispatch_info['id']);
        if (self::isSetCheckAlive($script) == false) {
            $error = [
                'script' => $script,
                'tip' => '未设置checkalive(),修复方式请在while内调用QueueMonitor::checkAlive()'
            ];
            App::getInstance()->getLogger()->error($error, LogType::QUEUE_CHECK_ALIVE);
            $Mussy = Mussy::getInstance();
            $Mussy->fatal_alert_email("队列未开启", var_export($error, true), "警告");
            $Mussy->fatal_alert_feishu("队列未开启", var_export($error, true), "警告");
            return false;
        }
        if (self::isUseQueue($script) == false) {
            $error = [
                'script' => $script,
                'tip' => '未使用标准队列,修复方式请参考src/App/Script/Queue/demo.php'
            ];
            App::getInstance()->getLogger()->error($error, LogType::QUEUE_CHECK_USE);
            $Mussy = Mussy::getInstance();
            $Mussy->fatal_alert_email("队列未开启", var_export($error, true), "警告");
            $Mussy->fatal_alert_feishu("队列未开启", var_export($error, true), "警告");
            return false;
        }

        if (!empty($r['process_pid'])) {
            return false;
        }

        $cmd = 'php ' . $script . ' --queue_dispatch_id=' . $dispatch_info['id'] . '  > /dev/null 2>&1 &';
        $handle = popen($cmd, "r");
        if ($handle) {
            pclose($handle);
            return true;
        }

        return false;
    }

    static function isSetCheckAlive($script_path = '/www/xxxx/cron/xxx.php')
    {
        if (is_file($script_path) == false) {
            return false;
        }
        $key_word = 'QueueMonitor::checkAlive(';
        $content = file_get_contents($script_path);
        return strpos($content, $key_word);
    }

    static function isUseQueue($script_path = '/www/xxxx/cron/xxx.php')
    {
        if (is_file($script_path) == false) {
            return false;
        }
        return preg_match("/\\\Mt\\\Queue\\\.*Queue::getInstance\(.*\)/", file_get_contents($script_path));
    }

    /**
     * 抢占任务/更新任务alive状态
     * @param $id
     * @param $pid
     * @param $machine
     * @param $alive_at
     * @param $mem_used
     * @param $cpu_used
     * @param $server_ip
     * @param $hostname
     * @param $is_begin_run
     * @return bool 返回false表示抢占成功，脚本应该退出
     */
    function updateAliveInfoByTaskProcess($id, $pid, $machine, $alive_at, $mem_used, $cpu_used, $server_ip, $hostname, $is_begin_run)
    {
        //pid=:pid and machine=:machine表示被自己认领的，pid=0说明还没有人认领
        $update_params = [
            'process_pid' => $pid,
            'machine' => $machine,
            'alive_at' => $alive_at,
            'mem_used' => $mem_used,
            'cpu_used' => $cpu_used,
            'server_ip' => $server_ip,
            'hostname' => $hostname
        ];
        if ($is_begin_run) {
            $update_params["process_alive_at"] = $alive_at;
            $update_params["process_start_alive"] = $alive_at;
        }
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $effect_rows = $this->db()->whereSql("id={$id} 
                    and pool='{$pool}' 
                    and ( 
                        (process_pid='{$pid}' and machine='{$machine}' ) 
                        or process_pid=0
                    ) ")->update($this->table, $update_params)->exec();
        $effect_rows = $effect_rows ? $this->db()->affectedRows() : 0;

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return is_numeric($effect_rows) && $effect_rows == 1;
    }

    function updateDieInfoByTaskProcess($id, $pid, $machine)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $effect_rows = $this->db()->multiWhere([
            'id' => $id,
            'process_pid' => $pid,
            'pool' => $pool,
            'machine' => $machine,
        ])->update($this->table, [
            'machine' => '',
            'alive_at' => 0,
            'process_pid' => 0,
            'mem_used' => 0,
            'cpu_used' => 0,
            'server_ip' => '',
            'hostname' => '',
        ])->exec();
        $effect_rows = $effect_rows ? $this->db()->affectedRows() : 0;

        //用完关闭，因为几K个队列会让用非常大的db连接数
        $this->db()->close();

        return is_numeric($effect_rows) && $effect_rows == 1;
    }

    /**
     * 系统负载高就不开进程了
     * @return bool
     */
    function isHighLoad()
    {
        $read = file_get_contents('/proc/meminfo');
        if (preg_match('/MemFree:\s+(\d+).*?Cached:\s+(\d+)/is', $read, $match)) {
            $mem_free_mb = ($match[1] + $match[2]) / 1024;//free+cached。cache和buffer区别 http://blog.csdn.net/xifeijian/article/details/8209758
            if ($mem_free_mb < 50) { //内存空闲小于100m
                return true;
            }
        }

        return false;
    }

}