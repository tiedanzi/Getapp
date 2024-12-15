<?php

#解析地址设置
# 比如 你购买的蓝光影片地址 (可以点击 视频->我的视频 点击任意的视频，点击编辑，查看视频播放地址就能看到前缀了) 为 BYGA-xxxxxxxx 那么 api 可以写 为 $api = ["BYGA"=>"蓝光的解析地址"];
# 比如官方的json接口 可以统一写到 _default_ 里面 比如 有两个官方的地址 那么 为 $api = ["_default_"=>"解析地址1|解析地址2"];
#  如果同时有蓝光的地址 和官方的地址 那么可以合并为一个 如   $api = ["BYGA"=>"蓝光的解析地址","*"=>"解析地址1|解析地址2"];
$api = [

    "BYGA" => "解析地址", "_default_" => "解析地址1|解析地址1"

];


/**
 * RSA签名类
 */
class Rsa
{

    public $privateKey = '';
    private $_privKey;

    public function __construct($privateKey = null)
    {

        $this->setKey($privateKey);
    }

    public function setKey($privateKey = null)
    {

        if (!is_null($privateKey)) {
            $this->privateKey = $privateKey;
        }
    }

    public function privDecrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $this->setupPrivKey();
        $encrypted = base64_decode($encrypted);
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    private function setupPrivKey()
    {
        if (is_resource($this->_privKey)) {
            return true;
        }
        $pem = chunk_split($this->privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";

        $this->_privKey = openssl_pkey_get_private($pem);
        return true;
    }


}

$privateKey = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMVjCfVM3QiOmHJfm9yjbhWXiseXaSm6wxcE6nCnzYll8fEr6eVJkdkCbY33QEROU4T8A9bpWU1TUyxpFx3inHSpeDCq8d10HVazC9yfXr90je9SrY6L3n7qI39LqsM1WiwxAHFZpfwuTcCwhhzsWgimyPifYp6DXdKDYZcq9qELAgMBAAECgYBuL2MXs9iPNkqTThvLhs+k7ftif3sM+Fx/NRdJ2+I4mNf+MO1YOUFZSqmtXiBaAP9OdQAPsCNRrvn3CJMG88ExwtIf/kJ66gwHUdO+h52hycf5C2aWJsKLahoRDT9z+0+BgdX+cHUpME6CSkKLKUkbdMreb3mM3TWfObWmlkcvgQJBAPLE5LJuyS378GMdC7Q4Jkmu4EWUdjt/7aMaTokjf/bjSDg/QbWkMZVJvjdi4/qvaH8Jrmr2ND4lSNvq2HHt51kCQQDQJPlXKH5+ZgXCrAdO73KLe1ZY17LW4uQKXtGnQyAf7jUwvSdPc2u6Yd8IMMmu9quXDHb5FYCr30X6ZKlBMeMDAkEAuWtGnT3ebD+7t4ess8YbADYP1zTwJLutveBO0ZGKn/+x3jv6LQiuUi6TmOvv4jzs2/KCA/HtrvV9M3KoREQHaQJBAJ3lrpED0xGn627GebTTyJ0vL02uM6j37e5AB+NO9KvEVO1oUM3gzTRS0pKwEA9+aKTpe8dxHG9FrRxKCGvAoGkCQFN9+sUc5cNnhoKi/hZXLFh8C3EavDNTO/dTIlNfAo62yMLU5f2N3/WHG7xMFv7D+DmKNUpXsueX56xkF9UthBg=';


$rsa = new Rsa($privateKey);


$str = $_REQUEST["url"];


$res = $rsa->privDecrypt($str);


if (empty($res)) {
    exit("解密失败");
}


$uls = "";

foreach ($api as $k => $v) {

    if (preg_match('/' . $k . '/', $res)) {
        $uls = $v;
        break;
    }


}


if (empty($uls)) {
    $uls = $api["_default_"];
}


$jsons = ["code" => 400];

foreach (explode('|', $uls) as $k) {

    $data = curl($k . $res);


    if (!empty($data['url'])) {

        $jsons["code"] = 200;
        $jsons["url"] = $data['url'];
        exit(json_encode($jsons));
    }


}


echo json_encode($jsons);


function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (preg_match('/mgtv/', $url)) {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1');

    } elseif (preg_match('/bilivideo/', $url)){
        curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36');
    };

    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res;
}








