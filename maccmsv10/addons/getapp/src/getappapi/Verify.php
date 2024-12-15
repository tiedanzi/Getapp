<?php


namespace app\api\controller\getappapi;

use think\captcha\Captcha;
use think\Cache;
use think\Config;
use think\Session;

/**
 * 验证码
 */
class Verify extends Base
{
    public function create()
    {
        $key = input('key/s', '');
        if (empty($key)) {
            exit();
        }
        ob_end_clean();
        $config = (array)Config::get('captcha');
        $config['imageH'] = 60;
        $config['imageW'] = 200;
        $config['fontSize'] = 28;
        $config['length'] = 4;
        $captcha = new Captcha($config);
        $result = $captcha->entry($key);
        $captcha_key = $this->authcode() . $key;
        $captcha_data = $_SESSION[$captcha_key];
        cache($key, $captcha_data['verify_code'], 300);
        $result->send();
        exit;
    }
}