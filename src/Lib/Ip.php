<?php

namespace Mt\Lib;

use Fw\InstanceTrait;
use Mt\Cache\IpAddressCache;

/**
 * ip归属地查询
 */
class Ip
{
    use InstanceTrait;

    public function ipAddress($ip)
    {
        //优先从db获取
        $result = $this->get_ip_from_db($ip);
        if (!empty($result)) {
            return $result;
        }
        //其次从缓存获取
        $IpAddressCache = IpAddressCache::getInstance();
        $result = $IpAddressCache->get($ip);
        if (!empty($result)) {
            return $result;
        }
        //最后从接口获取（缓慢且受制于人）
        $result = $this->get_ip_from_api($ip);
        $IpAddressCache->set($ip, $result);
        return $result;
    }

    protected function get_ip_from_db($ip)
    {
        try {
            $ip2region = new \Ip2Region();
            $result = $ip2region->memorySearch($ip);
            $arr = explode("|", $result["region"]);
            $result = [
                "ip" => $ip,
                "pro" => $arr[2],
                "proCode" => "",
                "cityCode" => "",
                "city" => $arr[3],
                "region" => $arr[0],
                "regionCode" => $arr[1],
                "addr" => $arr[4],
                "regionNames" => $arr[4],
                "err" => 0,
            ];
        } catch (\Exception $exception) {
            return false;
        }
        return $result;
    }

    protected function get_ip_from_api($ip)
    {
        $ch = curl_init();
        $url = 'https://whois.pconline.com.cn/ipJson.jsp?ip=' . $ip;
        //用curl发送接收数据
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //请求为https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $location = curl_exec($ch);
        curl_close($ch);
        //转码
        $location = mb_convert_encoding($location, 'utf-8', 'GB2312');
        //截取{}中的字符串
        $location = substr($location, strlen('({') + strpos($location, '({'), (strlen($location) - strpos($location, '})')) * (-1));
        //将截取的字符串$location中的‘，’替换成‘&’   将字符串中的‘：‘替换成‘=’
        $location = str_replace('"', "", str_replace(":", "=", str_replace(",", "&", $location)));
        //php内置函数，将处理成类似于url参数的格式的字符串  转换成数组
        parse_str($location, $ip_location);
        // return $ip_location['addr']; # 返回字符串
        return $ip_location; # 返回数组
    }
}