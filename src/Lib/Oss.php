<?php

namespace Mt\Lib;

use Fw\InstanceTrait;
use OSS\Core\OssException;
use OSS\OssClient;

/**
 * 阿里oss
 * https://help.aliyun.com/document_detail/91771.html
 */
class Oss
{
    use InstanceTrait;
    protected $AccessKeyId = "";
    protected $AccessKeySecret = "";
    protected $endpoint = "";
    protected $bucket = "";

    protected function __construct()
    {
        $this->AccessKeyId = app_env("oss.access_id");
        $this->AccessKeySecret = app_env("oss.access_key");
        $this->endpoint = app_env("oss.endpoint");
        $this->bucket = app_env("oss.bucket");
    }

    /**
     * 获取前端直传的token
     * @param string $prefixDir 目录前缀,默认为common,店铺为各自的三级域名前缀domain_no
     * @return array
     */
    public function getUploadToken($prefixDir = "common")
    {
        $id = $this->AccessKeyId;          // 请填写您的AccessKeyId。
        $key = $this->AccessKeySecret;     // 请填写您的AccessKeySecret。
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $host = 'https://' . $this->bucket . '.' . $this->endpoint;
        // $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
//        $callbackUrl = 'http://88.88.88.88:8888/aliyun-oss-appserver-php/php/callback.php';
        $callbackUrl = "";
        $dir = $prefixDir . '/';        // 用户上传文件时指定的前缀。
        $fileKey = $dir . date("YmdHis") . "/" . uniqid();

        $callback_param = [
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ];
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 120;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);
        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;
        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;

        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['OSSAccessKeyId'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
//        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['key'] = $fileKey;
//        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
        return $response;
    }

    /**
     * 文件上传
     * @param string $prefixDir 目录前缀,默认为common,店铺为各自的三级域名前缀domain_no
     * @param string $filePath 要上传文件在本地的完整路径
     * @param string 下载文件名 $downloadName
     * @param string 指定后缀名 $setExt
     * @return string 上传后的访问路径，为空就是上传失败
     */
    public function upload($prefixDir = "common", $filePath, $downloadName = "", $setExt = null)
    {
        // 阿里云账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM用户进行API访问或日常运维，请登录RAM控制台创建RAM用户。
        $accessKeyId = $this->AccessKeyId;
        $accessKeySecret = $this->AccessKeySecret;
        // yourEndpoint填写Bucket所在地域对应的Endpoint。以华东1（杭州）为例，Endpoint填写为https://oss-cn-hangzhou.aliyuncs.com。
        $endpoint = $this->endpoint;
        // 填写Bucket名称，例如examplebucket。
        $bucket = $this->bucket;
        // 填写Object完整路径，例如exampledir/exampleobject.txt。Object完整路径中不能包含Bucket名称。
        $dir = $prefixDir . '/';        // 用户上传文件时指定的前缀。
        $fileKey = $dir . date("YmdHis") . "/" . uniqid();
        $ext = $setExt ?: pathinfo($filePath, PATHINFO_EXTENSION);
        $object = $fileKey . "." . $ext;
        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt。
        // 填写本地文件的完整路径，例如D:\\localpath\\examplefile.txt。如果未指定本地路径，则默认从示例程序所属项目对应本地路径中上传文件。
        $options = null;
        if (!empty($downloadName)) {
            $options = [
                OssClient::OSS_HEADERS => [
                    'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                ],
            ];
        }

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $object, $filePath, $options);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return "";
        }
        return "https://" . $this->bucket . "." . $this->endpoint . "/" . $object;
    }

    /**
     * 删除文件
     * @param $url
     * @return bool|null
     */
    public function delete($url)
    {
        $path_arr = parse_url($url);
        $object = ltrim($path_arr["path"], "/");
        $accessKeyId = $this->AccessKeyId;
        $accessKeySecret = $this->AccessKeySecret;
        $endpoint = $this->endpoint;
        $bucket = $this->bucket;

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $result = $ossClient->deleteObject($bucket, $object, $options = NULL);
            return $result;
        } catch (\Exception $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
        }
        return false;
    }

}