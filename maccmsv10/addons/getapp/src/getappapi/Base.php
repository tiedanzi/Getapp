<?php

namespace app\api\controller\getappapi;

use think\Cache;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;

class Base extends Controller
{
    protected $is_login = false;

    protected $user_id = 0;

    protected $user_info = null;

    protected $time;


    public function _initialize()
    {
        parent::_initialize();
        $this->time = time();
        $headers = Request::instance()->header();
        $is_need_login = $this->isNeedLoginAction();
        $is_need_userinfo = $this->isNeedGetUserinfo();
        $is_need_verify = $this->isNeedVerifyAction();
        if ($is_need_verify) {
            $api_request_verify = $this->getConfig('api_request_verify');
            if ($api_request_verify && isset($headers['app-api-verify-sign']) && isset($headers['app-api-verify-time'])) {
                if (abs($headers['app-api-verify-time'] - $this->time) > 300) {
                    $this->jsonError("请求超时！");
                }
                if ($this->dataEncrypt($headers['app-api-verify-time']) != $headers['app-api-verify-sign']) {
                    $this->jsonError("请求校验失败！");
                }
            }
        }
        if ($is_need_login) {
            if (!isset($headers['app-user-token'])) {
                $this->jsonError("请先登录！");
            } else {
                $user_token = $headers['app-user-token'];
                if (empty($user_token)) {
                    $this->jsonError("请先登录！");
                }
                $user_info = $this->getUser($user_token);
                if ($user_info) {
                    if (!$user_info['user_status']) {
                        $this->jsonError("用户状态异常！");
                    }
                    $this->is_login = true;
                    $user_info['user_avatar'] = $this->getUserAvatar($user_info);
                    $user_info['id'] = $user_info['user_id'];
                    $user_info['status'] = $user_info['user_status'];
                    $user_info['create_time'] = $user_info['user_reg_time'];
                    $this->user_info = $user_info;
                    $this->user_id = $user_info['user_id'];
                } else {
                    $this->jsonError("请先登录！");
                }
            }
        } else if ($is_need_userinfo) {
            if (isset($headers['app-user-token'])) {
                $user_token = $headers['app-user-token'];
                if (!empty($user_token)) {
                    $user_info = $this->getUser($user_token);
                    if ($user_info) {
                        $this->is_login = true;
                        $this->user_info = $user_info;
                        $this->user_id = $user_info['user_id'];
                    }
                }

            }
        }
    }

    protected function getUser($user_token)
    {
        $user_extra = Db::table('getapp_mac_user_extra')->where([
            'auth_token' => $user_token
        ])->find();
        if (empty($user_extra)) {
            return [];
        }
        if (!$user_extra['invite_code']) {
            $invite_code = $this->createInviteCode();
            Db::table('getapp_mac_user_extra')->where([
                'user_id' => $user_extra['user_id']
            ])->update(['invite_code' => $invite_code]);
        } else {
            $invite_code = $user_extra['invite_code'];
        }

        $user_info = Db::name('user')->where(['user_id' => $user_extra['user_id']])->find();
        $user_info['auth_token'] = $user_extra['auth_token'];
        $user_info['avatar_update_time'] = $user_extra['avatar_update_time'];
        $user_info['is_vip'] = $this->isVip($user_info);
        $user_info['user_nick_name'] = empty($user_info['user_nick_name']) ? $user_info['user_name'] : $user_info['user_nick_name'];
        $user_info['invite_code'] = $invite_code;
        $user_info['invite_count'] = $user_extra['invite_count'];
        $user_info['vip_days'] = $this->getVipDays($user_info);
        $user_info['user_avatar'] = $this->getUserAvatar($user_info);
        $user_info['id'] = $user_info['user_id'];
        $user_info['status'] = $user_info['user_status'];
        $user_info['create_time'] = $user_info['user_reg_time'];

        return $user_info;
    }

    protected function isVip($user_info)
    {
        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];

        if ($user_info && isset($user_info['user_status']) && $user_info['user_status'] && $getapp_system_config['vip_group_id'] == $user_info['group_id'] && $user_info['user_end_time'] > $this->time) {
            return true;
        } else {
            return false;
        }
    }

    protected function getVipDays($user_info)
    {
        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];

        if ($user_info && isset($user_info['user_status']) && $user_info['user_status'] && $getapp_system_config['vip_group_id'] == $user_info['group_id'] && $user_info['user_end_time'] > $this->time) {
            return ($user_info['user_end_time'] - $this->time) / 86400;
        } else {
            return 0;
        }
    }

    private function isNeedLoginAction()
    {
        $action = strtolower(request()->action());
        $need_login_actions = [
            'senddanmu',
            'sendcomment',
            'iscollect',
            'collect',
            'collectlist',
            'deletecollect',
            'suggest',
            'find',
            'requestupdate',
            'avatarupload',
            'modifypassword',
            'appavatarupload',
            'modifyusernickname',
            'usernoticelist',
            'usernoticetype',
            'commenttipoff',
            'danmureport',
            'invitelogs',
            'userpointslogs',
            'uservipcenter',
            'userbuyvip',
            'watchrewardad'
        ];
        if (in_array($action, $need_login_actions)) {
            return true;
        }
        return false;
    }


    private function isNeedGetUserinfo()
    {
        $action = strtolower(request()->action());
        $need_get_userinfo_actions = [
            'init',
            'initv119',
            'voddetail',
            'userinfo',
            'mineinfo'
        ];
        if (in_array($action, $need_get_userinfo_actions)) {
            return true;
        }
        return false;
    }

    private function isNeedVerifyAction()
    {
        $action = strtolower(request()->action());
        $not_need_actions = [
            'init',
            'initv119',
            'appupdate',
            'appupdatev2'
        ];
        if (in_array($action, $not_need_actions)) {
            return false;
        }
        return true;
    }

    private function dataEncrypt($data)
    {
        $config = config('maccms');
        $build_config = $config['getapp_build'];
        $key = $build_config['api_secret_key'];

        return openssl_encrypt($data, 'AES-128-CBC', $key, false, $key);
    }

    protected function setMsg($msg = '', $code = 0)
    {
        return json(['msg' => $msg, 'code' => $code, 'data' => []]);
    }

    protected function setData($data = [], $msg = '', $code = 1)
    {
        if ($data) {
            $data = $this->dataEncrypt(json_encode($data));
        }
        return json(['data' => $data, 'msg' => $msg, 'code' => $code]);
    }

    protected function jsonError($msg = '')
    {
        json(['msg' => $msg, 'code' => 0, 'data' => []])->send();
        exit();
    }


    protected function getFullUrl($path)
    {
        if (!empty($path)) {
            return \request()->domain() . "/" . $path;
        } else {
            return "";
        }

    }

    protected function macFilterXss($str)
    {
        return trim(htmlspecialchars(strip_tags($str), ENT_QUOTES));
    }

    protected function getConfig($config_name = '')
    {

        $getapp_config = cache('getapp_config');
        if (empty($getapp_config)) {
            $getapp_config = Db::table('getapp_config')
                ->column('value', 'param_name');

            $getapp_config['ad_splash_status'] = boolval($getapp_config['ad_splash_status']);
            $getapp_config['ad_home_page_insert_status'] = boolval($getapp_config['ad_home_page_insert_status']);
            $getapp_config['ad_back_insert_interval_time'] = intval($getapp_config['ad_back_insert_interval_time']);
            $getapp_config['ad_mine_page_banner_status'] = boolval($getapp_config['ad_mine_page_banner_status']);
            $getapp_config['ad_search_page_banner_status'] = boolval($getapp_config['ad_search_page_banner_status']);
            $getapp_config['ad_detail_page_banner_status'] = boolval($getapp_config['ad_detail_page_banner_status']);
            $getapp_config['ad_detail_page_reward_interval_time'] = intval($getapp_config['ad_detail_page_reward_interval_time']);

            cache('getapp_config', $getapp_config);
        }
        $mac_config = config('maccms');
        $getapp_system_config = $mac_config['getapp_system_config'];
        if ($getapp_system_config) {
            $getapp_system_config['system_third_danmu_status'] = boolval(intval($getapp_system_config['system_third_danmu_status']));
            $getapp_system_config['system_vpn_check_status'] = boolval(intval($getapp_system_config['system_vpn_check_status']));
            $getapp_system_config['system_third_danmu_url_type'] = boolval(intval($getapp_system_config['system_third_danmu_url_type']));
            $getapp_system_config['system_config_top_comment_status'] = boolval(intval($getapp_system_config['system_config_top_comment_status']));

            $getapp_config = array_merge($getapp_system_config, $getapp_config);
        }

        if ($config_name) {
            return isset($getapp_config[$config_name]) ? $getapp_config[$config_name] : 0;
        } else {
            unset($getapp_config['system_config_xun_search_key']);
            return $getapp_config;
        }
    }

    protected function getVipConfig($getapp_config)
    {
        if ($this->user_info && $this->user_info['is_vip']) {
            $getapp_config['ad_splash_status'] = false;
            $getapp_config['ad_home_page_insert_status'] = false;
            $getapp_config['ad_back_insert_interval_time'] = -1;
            $getapp_config['ad_mine_page_banner_status'] = false;
            $getapp_config['ad_search_page_banner_status'] = false;
            $getapp_config['ad_detail_page_banner_status'] = false;
            $getapp_config['ad_detail_page_reward_interval_time'] = -1;
            $getapp_config['ad_detail_page_insert_times'] = 0;
            $getapp_config['ad_detail_page_download_reward_interval_time'] = -1;
            $getapp_config['ad_detail_page_cuigeng_reward_interval_time'] = -1;
        }
        return $getapp_config;
    }

    protected function getVipAdConfig()
    {
        $getapp_config['ad_splash_status'] = false;
        $getapp_config['ad_home_page_insert_status'] = false;
        $getapp_config['ad_back_insert_interval_time'] = -1;
        $getapp_config['ad_mine_page_banner_status'] = false;
        $getapp_config['ad_search_page_banner_status'] = false;
        $getapp_config['ad_detail_page_banner_status'] = false;
        $getapp_config['ad_detail_page_reward_interval_time'] = -1;
        $getapp_config['ad_detail_page_insert_times'] = 0;
        $getapp_config['ad_detail_page_download_reward_interval_time'] = -1;
        $getapp_config['ad_detail_page_cuigeng_reward_interval_time'] = -1;
        return $getapp_config;
    }

    protected function replaceVodPic($vod_list)
    {
        foreach ($vod_list as &$vod) {
            $vod['vod_pic'] = $this->getImgUrl($vod['vod_pic']);
        }
        return $vod_list;
    }

    protected function getImgUrl($img_path)
    {
        $config = config('maccms');
        $upload_config = $config['upload'];
        $img_path = str_replace("mac://", "{$upload_config['protocol']}://", $img_path);
        if (!stristr($img_path, "http")) {
            if (stristr($img_path, "//")) {
                $img_path = "http:" . $img_path;
            } else {
                $img_path = \request()->domain() . "/" . $img_path;
            }

        }
        return $img_path;
    }

    protected function getUserAvatar($user_info)
    {
        $avatar = "";
        if ($user_info['user_portrait']) {
            $config = config('maccms');
            $upload_config = $config['upload'];
            $user_info['user_portrait'] = str_replace("mac://", "{$upload_config['protocol']}://", $user_info['user_portrait']);
            $res = $user_info['user_portrait'];
            if (stristr($res, "http:") || stristr($res, "https:")) {
               return $res;
            }

        } else {
            $user_id = $user_info['user_id'];
            $res = 'upload/user/' . ($user_id % 10) . '/' . $user_id . '.jpg';
        }
        if (file_exists(ROOT_PATH . $res)) {
            $avatar = $this->getImgUrl($res . "?t=" . $user_info['avatar_update_time']);
        }
        return $avatar;
    }


    protected function maskPhoneNumber($user_name)
    {
        if (preg_match('/^1\d{10}$/', $user_name)) {
            return substr($user_name, 0, 3) . '****' . substr($user_name, -4);
        }
        return $user_name;
    }

    protected function getNoticeCount()
    {
        if ($this->user_id <= 0) {
            return 0;
        }
        return Db::table('getapp_user_notice')->where([
            'user_id' => $this->user_id,
            'is_read' => 0
        ])->count();
    }

    protected function mac_get_ip_long($ip_addr = '')
    {
        $ip_addr = !empty($ip_addr) ? $ip_addr : $this->mac_get_client_ip();
        $ip_long = sprintf('%u', ip2long($ip_addr));
        // 排除不正确的IP
        if ($ip_long < 0 || $ip_long >= 0xFFFFFFFF) {
            $ip_long = 0;
        }
        return $ip_long;
    }

    protected function mac_get_client_ip()
    {
        static $final;
        if (!is_null($final)) {
            return $final;
        }
        $ips = [];
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ips[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_ALI_CDN_REAL_IP'])) {
            $ips[] = $_SERVER['HTTP_ALI_CDN_REAL_IP'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ips[] = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_PROXY_USER'])) {
            $ips[] = $_SERVER['HTTP_PROXY_USER'];
        }
        $real_ip = getenv('HTTP_X_REAL_IP');
        if (!empty($real_ip)) {
            $ips[] = $real_ip;
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }
        // 选第一个最合法的，或最后一个正常的IP
        foreach ($ips as $ip) {
            $verifyResult = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
            if (!$verifyResult) {
                continue;
            }
            $verifyResult && $final = $ip;
        }
        empty($final) && $final = '0.0.0.0';
        return $final;
    }

    /* 加密验证码 */
    protected function authcode($str = "ThinkPHP.CN")
    {
        $key = substr(md5("ThinkPHP.CN"), 5, 8);
        $str = substr(md5($str), 8, 10);
        return md5($key . $str);
    }

    protected function getAdvert($position, $more = false)
    {
        $headers = Request::instance()->header();
        $where_in = [0, 1];
        if (isset($headers['app-ui-mode']) && $headers['app-ui-mode'] == 'dark') {
            $where_in = [0, 2];
        }

        $query = Db::table('getapp_advert')
            ->where(['status' => 1, 'position' => $position, 'start_time' => ['<', $this->time], 'end_time' => ['>', $this->time]])
            ->whereIn('ui_mode', $where_in)
            ->field('id as vod_id, name as vod_name, content as vod_pic, req_content as vod_link')
            ->order('sort asc, id desc');

        if ($more) {
            return $query->select();
        } else {
            return $query->find();
        }

    }

    /**
     * 关键词过滤
     * @param $p
     * @return array|mixed|string|string[]
     */
    protected function filterWords($words, $filter_words = [], $type = 'app')
    {
        if (empty($filter_words)) {
            $config = config('maccms');
            $filter_words = explode(",", $config[$type]['filter_words']);
        }

        return str_replace($filter_words, "***", $words);
    }

    protected function parseTimeByText($text)
    {
        $pattern = '/(?:(\d{1,2})[:：])?(\d{1,2})[:：](\d{1,2})/u';

        // 数组用于存储匹配的结果
        $result_array = [
            'time_str' => '',
            'seek_to_time' => 0,
        ];

        // 使用 preg_match_all 来匹配所有符合条件的时间格式
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[0] as $match) {
                // 解析时间
                $timeParts = explode(':', str_replace("：", ":", $match));
                $seconds = 0;

                // 计算总秒数
                if (count($timeParts) === 3) {
                    $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2]; // HH:MM:SS
                } elseif (count($timeParts) === 2) {
                    $seconds = ($timeParts[0] * 60) + $timeParts[1]; // MM:SS
                } elseif (count($timeParts) === 1) {
                    $seconds = $timeParts[0]; // SS
                }

                // 将结果添加到数组中
                $result_array = [
                    'time_str' => $match,
                    'seek_to_time' => $seconds * 1000
                ];
            }
        }

        return $result_array; // 返回结果数组
    }

    protected function createInviteCode()
    {
        $characters = 'abcdefghijkmnpqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $randomIndex = mt_rand(0, $charactersLength - 1);
            $randomString .= $characters[$randomIndex];
        }
        $res = Db::table('getapp_mac_user_extra')->where(['invite_code' => $randomString])->find();
        if ($res) {
            return $this->createInviteCode();
        }
        return $randomString;
    }

    /**
     * @param $device_id
     * @param $invite_code
     * @param $event_type 1安装 2注册
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function doVerifyInviteCode($device_id, $invite_code, $event_type = 1)
    {
        $from_user_id = Db::table('getapp_mac_user_extra')->where(['invite_code' => $invite_code])->value('user_id');
        if (!$from_user_id) {
            return true;
        }

        $logs = Db::table('getapp_invite_logs')->where([
            'device_id' => $device_id,
            'event_type' => $event_type
        ])->find();
        if ($logs) {
            return true;
        }

        $config = config('maccms');
        $user_config = $config['user'];
        if ($event_type == 1) {
            Db::table('getapp_invite_logs')->insert([
                'from_user_id' => $from_user_id,
                'device_id' => $device_id,
                'event_type' => $event_type,
                'create_time' => $this->time
            ]);
            Db::table('getapp_mac_user_extra')->where([
                'user_id' => $from_user_id
            ])->setInc('invite_count');


            if ($user_config['invite_reg_points'] > 0) {
                Db::name('user')->where(['user_id' => $from_user_id])->setInc('user_points', $user_config['invite_reg_points']);

                Db::name('plog')->insert([
                    'user_id' => $from_user_id,
                    'plog_type' => 2,
                    'plog_points' => $user_config['invite_reg_points'],
                    'plog_time' => $this->time
                ]);
            }

        }

        return true;
    }
}