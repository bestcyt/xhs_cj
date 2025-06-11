<?php

namespace Mt\Lib;
/**
 * 类命名 变量命名 文件命名等规范化检测
 * Class CodeSniffer
 * @package Mt\Lib
 */
class CodeSniffer
{
    private static $filterFunction = null;

    /**
     * 设置检测过滤规则(某些文件或者特殊目录等不检测)
     * @param callable $fun
     */
    public static function setFilterFunction(callable $fun)
    {
        self::$filterFunction = $fun;
    }

    /**
     * @param $dir
     * @param $result
     * @return bool
     */
    static function check($dir, &$result)
    {
        if (!is_dir($dir)) return false;

        $handle = opendir($dir);

        if ($handle) {
            while (($fl = readdir($handle)) !== false) {
                $temp = $dir . DIRECTORY_SEPARATOR . $fl;
                //如果不加  $fl!='.' && $fl != '..'  则会造成把$dir的父级目录也读取出来
                if (is_dir($temp) && $fl != '.' && $fl != '..' && $fl != '.git') {
                    //echo 'dir----'.$temp."\r\n";
                    self::check($temp, $result);
                } else {
                    if ($fl != '.' && $fl != '..' && substr($fl, -4, 4) == '.php') {
                        $content = file_get_contents($temp);
                        $_result = self::_checkFile($temp, $content);
                        $result = array_merge($result, $_result);
                    }
                }
            }
        }
        return true;
    }

    static function _checkFile($file_path, $content)
    {
        $return_data = [];
        //当前文件不检测
        if (strpos($file_path, 'CodeSniffer.php') > 0) {
            return $return_data;
        }
        if (self::$filterFunction) {
            //命中过滤不检测
            if (call_user_func(self::$filterFunction, $file_path)) {
                return $return_data;
            }
        }

        /*******Queue类*******/
        $file_result = self::QueueFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::QueueVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Model类*******/
        $file_result = self::ModelFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::ModelVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Cache类*******/
        $file_result = self::CacheFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::CacheVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Counter类*******/
        $file_result = self::CounterFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::CounterVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Lock类*******/
        $file_result = self::LockFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::LockVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Key类*******/
        $file_result = self::KeyFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        /*******Service类*******/
        $file_result = self::ServiceFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::ServiceVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Bitmap类*******/
        $file_result = self::BitmapFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::BitmapVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******IdGenerator类*******/
        $file_result = self::IdGeneratorFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::IdGeneratorVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******BloomFilter类*******/
        $file_result = self::BloomFilterFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::BloomFilterVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Logic类*******/
        $file_result = self::LogicFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        $variable_result = self::LogicVariableName($file_path, $content);
        if ($variable_result) {
            $return_data = array_merge($return_data, $variable_result);
        }

        /*******Lib类*******/
        $file_result = self::LibFileName($file_path);
        if ($file_result) {
            $return_data[] = $file_result;
        }

        /**
         * script脚本必须放在特定的文件夹和引入对应的init.php
         */
        $script_result = self::ScriptFile($file_path, $content);
        if ($script_result) {
            $return_data[] = $script_result;
        }

        return $return_data;
    }

    static function LibFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Lib');
    }

    static function IdGeneratorFileName($file_path)
    {
        return self::_standardFileName($file_path, 'IdGenerator');
    }

    static function IdGeneratorVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'IdGenerator');
    }

    static function BloomFilterFileName($file_path)
    {
        return self::_standardFileName($file_path, 'BloomFilter');
    }

    static function BloomFilterVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'BloomFilter');
    }

    static function BitmapFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Bitmap');
    }

    static function BitmapVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Bitmap');
    }

    static function ServiceFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Service');
    }

    static function ServiceVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Service');
    }

    static function LogicFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Logic');
    }

    static function LogicVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Logic');
    }

    static function KeyFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Key');
    }

    static function LockFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Lock');
    }

    static function LockVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Lock');
    }

    static function CacheFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Cache');
    }

    static function CacheVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Cache');
    }

    static function CounterFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Counter');
    }

    static function CounterVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Counter');
    }

    static function ModelFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Model');
    }

    static function ModelVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Model');
    }

    static function ScriptFile($file_path, $content)
    {
        if (strpos($file_path, "/src/App/Script/") !== false) {
            $preDir = substr($file_path, 0, strpos($file_path, "/src/App/Script/") + strlen("/src/App/Script/"));
            $extDir = str_replace($preDir, "", $file_path);
            $ext_arr = explode("/", $extDir);
            if ($ext_arr[0] == "Console") {
                return [];
            } elseif (in_array($ext_arr[0], ["Cron", "Queue", "Temp"])) {
                if (end($ext_arr) == "init.php") {
                    return [];
                }
                $dirCount = count($ext_arr) - 1;
                if (!preg_match("#(include|require|include_once|require_once)\s*\(?" . str_repeat("dirname\s*\(\s*", $dirCount) . "\_\_FILE\_\_" . str_repeat("\s*\)", $dirCount) . "\s*\.\s*[\"\']\/init\.php[\"\']\s*\)?#", $content)) {
                    return [
                        'type' => 'script',
                        'file' => $file_path,
                        'origin' => $file_path,
                        'expect' => '应引入init.php文件,include(' . str_repeat("dirname(", $dirCount) . '__FILE__' . str_repeat(")", $dirCount) . '."/init.php"); 如已引入,请注意dirname层级'
                    ];
                }
            } else {
                return [
                    'type' => 'script',
                    'file' => $file_path,
                    'origin' => $file_path,
                    'expect' => "请放在 {$preDir}Cron|Queue|Temp目录下 并包含对应的init.php文件"
                ];
            }
        }
        return [];
    }

    /**
     * 判断队列类名是否规范
     * @param string $file_path
     * @return array {'type':'file','file':'/www/abc.com/src/Queue/WangAn/WangAnBuKong.php',"origin":"/www/abc.com/src/Queue/WangAn/WangAnBuKong.php","expect":"/www/abc.com/src/Queue/WangAn/WangAn****Queue.php"}
     */
    static function QueueFileName($file_path)
    {
        return self::_standardFileName($file_path, 'Queue');
    }

    static function QueueVariableName($file_path, $content)
    {
        return self::_standardVariableName($file_path, $content, 'Queue');
    }

    /**
     * 目前是只检测src目录,而且都要符合目录格式,如 src/Model/xxx/xxx/AdminAccountModelModel.php
     * @param $file_path
     * @param string $fun_type
     * @return array
     */
    static function _standardFileName($file_path, $fun_type = 'Model')
    {
        $path_arr = explode('/', $file_path);
        if (!in_array($fun_type, $path_arr)) {
            return [];
        }
        $fun_dir_index = array_search($fun_type, $path_arr);
        if ($path_arr[$fun_dir_index - 1] != "src") {
            return [];
        }
        $path_count = count($path_arr) - 1;
        $path_inner = [];
        for ($i = 0; $i < $path_count; $i++) {
            if ($i > $fun_dir_index) {
                $path_inner[] = $path_arr[$i];
            }
        }
        $file_name = array_pop($path_arr);

        if ($fun_type == "Key") {
            $fun_type = "ModuleKey";
        } elseif ($fun_type == "Lib") {
            $fun_type = "";
            $path_inner = [];
        }

        $uc_file_name = false;
        if (preg_match('/^' . implode("", $path_inner) . '[A-Za-z0-9]*' . $fun_type . '\.php$/', $file_name)) {
            //首字母要大写
            if (ucfirst($file_name) == $file_name) {
                return [];
            }
            $uc_file_name = ucfirst($file_name);
        }

        return [
            'type' => 'file',
            'file' => $file_path,
            'origin' => $file_path,
            'expect' => implode('/', $path_arr) . '/' . ($uc_file_name ?: implode("", $path_inner) . '****' . $fun_type . '.php (文件名由数字和字母组成,首字母需大写)')
        ];

    }

    /**
     * 类实例赋值变量检测
     * @param $file_path
     * @param $content
     * @param string $fun_type
     * @return array
     */
    static function _standardVariableName($file_path, $content, $fun_type = 'Model')
    {
        //依赖QueueFileName()先规范，才成立
        $pattern_var = '(\$\w+)';
        $pattern_eq = '\s*=+\s*';
        $pattern_name_space = '([\w\\\]*?)';
        $pattern_class_call = '([\da-zA-Z]+' . $fun_type . ')(::getInstance\()[^\)]*\)\s*;';

        //正规写法$AgainstBrushQueue = \Mt\Queue\AgainstBrush\AgainstBrushQueue::getInstance()
        if (!preg_match_all('#' . $pattern_var . '' . $pattern_eq . '' . $pattern_name_space . '' . $pattern_class_call . '#', $content, $match)) {
            return [];
        }

        $return = [];
        foreach ($match[0] as $k => $v) {
            if ($match[1][$k] == '$' . $match[3][$k]) {
                continue;
            }
            $return[] = [
                'type' => 'variable',
                'file' => $file_path,
                'origin' => preg_replace('/\s+/', ' ', $match[1][$k] . ' = ' . $match[2][$k] . $match[3][$k] . $match[4][$k]) . '...',
                'expect' => preg_replace('/\s+/', ' ', '$' . $match[3][$k] . ' = ' . $match[2][$k] . $match[3][$k] . $match[4][$k]) . '...',
            ];
        }

        return $return;
    }

}