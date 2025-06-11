<?php
// json_encode 精度问题 https://blog.csdn.net/m0_46266407/article/details/105556444
ini_set('serialize_precision', 14);
ini_set('precision', 14);

//设置数据库默认报错记录,记日志和发邮件
\Fw\Db\Mysql::setErrorLog("dbErrorCallback");

//设置redis默认报错记录,记日志和发邮件
\Fw\Redis::setErrorLog("redisErrorCallback");

//设置memcache默认报错记录,记日志和发邮件
\Fw\Memcache::setErrorLog("memcacheErrorCallback");

//post请求 json转成form
post_json_to_request();
