<?php

namespace app\admin\controller;

use Exception;
use think\Db;
use think\Image;

class Getapp extends Base
{
    var $_update_url;
    var $_build_url;
    var $_apk_url;

    var $_version;

    protected $app_custom_icons;

    public function __construct()
    {
        parent::__construct();
        $this->_param = input();
        $this->_build_url = "https://api.getapp.tv/user/requestBuild";
        $this->_apk_url = "https://api.getapp.tv/user/getBuildResult";
        $this->_update_url = "https://api.getapp.tv/system/";
        $config = parse_ini_file("addons/getapp/info.ini");
        $this->_version = $config['version'];
        $this->assign('version', $this->_version);
        $this->app_custom_icons = [
                [
                    'tab' => '导航栏',
                    'style' => 1,
                    'list' => [
                        ['name' => '首页图标-未选中', 'key' => 'app_custom_icon_home_unselected'],
                        ['name' => '首页图标-选中', 'key' => 'app_custom_icon_home_selected'],
                        ['name' => '排行图标-未选中', 'key' => 'app_custom_icon_rank_unselected'],
                        ['name' => '排行图标-选中', 'key' => 'app_custom_icon_rank_selected'],
                        ['name' => '专题图标-未选中', 'key' => 'app_custom_icon_topic_unselected'],
                        ['name' => '专题图标-选中', 'key' => 'app_custom_icon_topic_selected'],
                        ['name' => '排期图标-未选中', 'key' => 'app_custom_icon_week_unselected'],
                        ['name' => '排期图标-选中', 'key' => 'app_custom_icon_week_selected'],
                        ['name' => '发现图标-未选中', 'key' => 'app_custom_icon_find_unselected'],
                        ['name' => '发现图标-选中', 'key' => 'app_custom_icon_find_selected'],
                        ['name' => '我的图标-未选中', 'key' => 'app_custom_icon_mine_unselected'],
                        ['name' => '我的图标-选中', 'key' => 'app_custom_icon_mine_selected'],

                        ['name' => '[深色]首页图标-未选中', 'key' => 'dark_app_custom_icon_home_unselected'],
                        ['name' => '[深色]首页图标-选中', 'key' => 'dark_app_custom_icon_home_selected'],
                        ['name' => '[深色]排行图标-未选中', 'key' => 'dark_app_custom_icon_rank_unselected'],
                        ['name' => '[深色]排行图标-选中', 'key' => 'dark_app_custom_icon_rank_selected'],
                        ['name' => '[深色]专题图标-未选中', 'key' => 'dark_app_custom_icon_topic_unselected'],
                        ['name' => '[深色]专题图标-选中', 'key' => 'dark_app_custom_icon_topic_selected'],
                        ['name' => '[深色]排期图标-未选中', 'key' => 'dark_app_custom_icon_week_unselected'],
                        ['name' => '[深色]排期图标-选中', 'key' => 'dark_app_custom_icon_week_selected'],
                        ['name' => '[深色]发现图标-未选中', 'key' => 'dark_app_custom_icon_find_unselected'],
                        ['name' => '[深色]发现图标-选中', 'key' => 'dark_app_custom_icon_find_selected'],
                        ['name' => '[深色]我的图标-未选中', 'key' => 'dark_app_custom_icon_mine_unselected'],
                        ['name' => '[深色]我的图标-选中', 'key' => 'dark_app_custom_icon_mine_selected'],
                    ]
                ],
                [
                    'tab' => '首页',
                    'list' => [
                        ['name' => '下载记录', 'key' => 'app_custom_icon_homepage_download'],
                        ['name' => '历史记录', 'key' => 'app_custom_icon_history'],
                        ['name' => '[深色]下载记录', 'key' => 'dark_app_custom_icon_homepage_download'],
                        ['name' => '[深色]历史记录', 'key' => 'dark_app_custom_icon_history'],
                    ]
                ],
                [
                    'tab' => '我的页面',
                    'list' => [
                        ['name' => '设置按钮', 'key' => 'app_custom_icon_setting'],
                        ['name' => 'vip图标-是', 'key' => 'app_custom_icon_vip'],
                        ['name' => 'vip图标-不是', 'key' => 'app_custom_icon_not_vip'],
                        ['name' => '我的收藏', 'key' => 'app_custom_icon_mine_collect'],
                        ['name' => '我的下载', 'key' => 'app_custom_icon_download'],
                        ['name' => '求片找片', 'key' => 'app_custom_icon_find'],
                        ['name' => '反馈报错', 'key' => 'app_custom_icon_report'],
                        ['name' => '消息中心', 'key' => 'app_custom_icon_notice'],
                        ['name' => '分享好友', 'key' => 'app_custom_icon_share'],
                        ['name' => '检查升级', 'key' => 'app_custom_icon_update'],
                        ['name' => '清理缓存', 'key' => 'app_custom_icon_clear'],

                        ['name' => '[深色]设置按钮', 'key' => 'dark_app_custom_icon_setting'],
                        ['name' => '[深色]vip图标-是', 'key' => 'dark_app_custom_icon_vip'],
                        ['name' => '[深色]vip图标-不是', 'key' => 'dark_app_custom_icon_not_vip'],
                        ['name' => '[深色]我的收藏', 'key' => 'dark_app_custom_icon_mine_collect'],
                        ['name' => '[深色]我的下载', 'key' => 'dark_app_custom_icon_download'],
                        ['name' => '[深色]求片找片', 'key' => 'dark_app_custom_icon_find'],
                        ['name' => '[深色]反馈报错', 'key' => 'dark_app_custom_icon_report'],
                        ['name' => '[深色]消息中心', 'key' => 'dark_app_custom_icon_notice'],
                        ['name' => '[深色]分享好友', 'key' => 'dark_app_custom_icon_share'],
                        ['name' => '[深色]检查升级', 'key' => 'dark_app_custom_icon_update'],
                        ['name' => '[深色]清理缓存', 'key' => 'dark_app_custom_icon_clear'],
                    ]
                ],

                [
                    'tab' => '播放页',
                    'list' => [
                        ['name' => '未收藏', 'key' => 'app_custom_icon_un_collect'],
                        ['name' => '已收藏', 'key' => 'app_custom_icon_collect'],
                        ['name' => '分享', 'key' => 'app_custom_icon_detail_share'],
                        ['name' => '下载', 'key' => 'app_custom_icon_detail_download'],
                        ['name' => '催更', 'key' => 'app_custom_icon_cuigeng'],
                        ['name' => '反馈', 'key' => 'app_custom_icon_detail_feedback'],
                        ['name' => '弹幕开', 'key' => 'app_custom_icon_danmu_open'],
                        ['name' => '弹幕关', 'key' => 'app_custom_icon_danmu_close'],
                        ['name' => '评论置顶图标', 'key' => 'app_custom_icon_official'],

                        ['name' => '[深色]未收藏', 'key' => 'dark_app_custom_icon_un_collect'],
                        ['name' => '[深色]已收藏', 'key' => 'dark_app_custom_icon_collect'],
                        ['name' => '[深色]分享', 'key' => 'dark_app_custom_icon_detail_share'],
                        ['name' => '[深色]下载', 'key' => 'dark_app_custom_icon_detail_download'],
                        ['name' => '[深色]催更', 'key' => 'dark_app_custom_icon_cuigeng'],
                        ['name' => '[深色]反馈', 'key' => 'dark_app_custom_icon_detail_feedback'],
                        ['name' => '[深色]弹幕开', 'key' => 'dark_app_custom_icon_danmu_open'],
                        ['name' => '[深色]弹幕关', 'key' => 'dark_app_custom_icon_danmu_close'],
                        ['name' => '[深色]评论置顶图标', 'key' => 'dark_app_custom_icon_official'],
                    ]
                ],
                [
                    'tab' => '播放器',
                    'list' => [
                        ['name' => '播放器加载', 'key' => 'app_custom_icon_player_loading'],
                    ]
                ],

                [
                    'tab' => '其它',
                    'list' => [
                        ['name' => '空视图', 'key' => 'app_custom_icon_empty'],
                        ['name' => '加载失败', 'key' => 'app_custom_icon_load_error'],
                        ['name' => '[深色]空视图', 'key' => 'dark_app_custom_icon_empty'],
                        ['name' => '[深色]加载失败', 'key' => 'dark_app_custom_icon_load_error'],
                    ]
                ],
            ];

    }

    function getHttp()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return "https://";
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return "https://";
        }
        return "http://";
    }

    public function index()
    {
        if (!file_exists("./addons/getapp/install.lock")) {
            return $this->fetch('admin@getapp/init');
        }
        return $this->fetch('admin@getapp/index');
    }



    public function install()
    {
        //导入sql
        $this->importsql('getapp/app.sql');

        //创建播放器
        $this->install_player();

        //开启接口
        $this->install_api();

        //写入接口app列表
        $this->write_app_api();


        //开启其他参数
        $this->install_param();

        fwrite(fopen('./addons/getapp/install.lock', 'wb'), 'installed');
    }

    /**
     * 导入SQL
     *
     * @param string $name 插件名称
     * @return  boolean
     */
    function importsql($name)
    {

        $sqlFile = ADDON_PATH . $name;
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::execute($templine);
                    } catch (Exception $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }


    //开启其他参数

    function install_player()
    {
        //创建播放器
//        $list = config('vodplayer');
//        $param['from'] = 'get_app_player';
//        $param['show'] = "app全局解析";
//        $param['sort'] = '9999';
//        $param['status'] = '1';
//        $param['target'] = '_self';
//        $param['ps'] = '1';
//        $param['id'] = 'get_app_player';
//
//        $already_player = $list['get_app_player'];
//        if ($already_player == null || empty($already_player["parse"])) {
//            $param['parse'] = $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http' . "://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/player.php";
//        } else {
//            $param['parse'] = $already_player["parse"];
//        }
//
//        //获取get_app_player
//        $parse_player = config('getapp_vodplayer_parse');
//        if ($parse_player == null) {
//            $parse_player = [];
//        }
//        if ($parse_player == null || empty($parse_player[$param['from']]["parse_api"])) {
//            $parse_player_param['parse_api'] = $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http' . "://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/player.php";
//        } else {
//            $parse_player_param['parse_api'] = $parse_player[$param['from']]["parse_api"];
//        }
//        if ($parse_player == null || empty($parse_player[$param['from']]["link_features"])) {
//            $parse_player_param['link_features'] = '.mp4,.m3u8,.flv,.avi,.mov,.rmvb,alizyw.com';
//        } else {
//            $parse_player_param['link_features'] = $parse_player[$param['from']]["link_features"];
//        }
//        if ($parse_player == null || empty($parse_player[$param['from']]["un_link_features"])) {
//            $parse_player_param['un_link_features'] = '';
//        } else {
//            $parse_player_param['un_link_features'] = $parse_player[$param['from']]["un_link_features"];
//        }
//        $parse_player[$param['from']] = $parse_player_param;
//        mac_arr2file(APP_PATH . 'extra/' . 'getapp_vodplayer_parse' . '.php', $parse_player);
//
//        $list[$param['from']] = $param;
//        $code = $param['code'];
//        mac_arr2file(APP_PATH . 'extra/' . 'vodplayer' . '.php', $list);
//        fwrite(fopen('./static/player/' . $param['from'] . '.js', 'wb'), $code);
    }

    //创建播放器

    function install_api()
    {
        $config_new['api'] = config('maccms')["api"];
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false ? 'http' : 'https';
        $config_new['api']['vod']['imgurl'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . "/";
        $config_old = config('maccms');
        $config_new = array_merge($config_old, $config_new);
        mac_arr2file(APP_PATH . 'extra/maccms.php', $config_new);
    }

    private function setErrorMsg($msg = '')
    {
        echo json_encode(['code' => 1, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    //开启接口

    function install_param()
    {
        $config_new['getapp_param'] = config('maccms')["getapp_param"];

        $ad_config = [];
        $ad = [];
        $ad["popId"] = "1";
        #gromore
        $ad["sdkId"] = 1;
        $ad["adOpen"] = 0;
        $ad["otherId"] = "1";
        $ad["appId"] = "1";
        $ad["detailId"] = "1";
        $ad["appKey"] = "1";
        $ad["videoId"] = "1";
        $ad["splashId"] = "1";
        array_push($ad_config, $ad);
        $ad = [];
        $ad["popId"] = "1";
        $ad["otherId"] = "1";
        #chuanshanjia
        $ad["sdkId"] = 2;
        $ad["adOpen"] = 0;
        $ad["appId"] = "1";
        $ad["detailId"] = "1";
        $ad["appKey"] = "1";
        $ad["videoId"] = "1";
        $ad["splashId"] = "1";
        array_push($ad_config, $ad);
        $ad = [];
        $ad["popId"] = "4003937984842480";
        $ad["otherId"] = "1";
        #youlianghui
        $ad["sdkId"] = 3;
        $ad["adOpen"] = 1;
        $ad["appId"] = "1200350175";
        $ad["detailId"] = "8003231934446339";
        $ad["appKey"] = "1200350175";
        $ad["videoId"] = "7003530993299201";
        $ad["splashId"] = "3083236953741423";
        array_push($ad_config, $ad);
        $res = [];
        $res["code"] = 1;
        $res["msg"] = "success";
        $res["data"] = $ad_config;
        $config_new['getapp_param']['ad'] = $res;
        $config_new['getapp_param']['discover_url'] = "";
        $config_new['getapp_param']['banner'] = [["name" => "1-首页", "check" => true], ["name" => "2-排行榜", "check" => true], ["name" => "3-发现/专题", "check" => true], ["name" => "4-我的", "check" => true]];
        $config_new['getapp_param']['secret_key'] = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDFYwn1TN0IjphyX5vco24Vl4rHl2kpusMXBOpwp82JZfHxK+nlSZHZAm2N90BETlOE/APW6VlNU1MsaRcd4px0qXgwqvHddB1Wswvcn16/dI3vUq2Oi95+6iN/S6rDNVosMQBxWaX8Lk3AsIYc7FoIpsj4n2Keg13Sg2GXKvahCwIDAQAB";
        $config_new['api']['vod']['pagesize'] = 21;
        $config_old = config('maccms');
        $config_new = array_merge($config_old, $config_new);
        mac_arr2file(APP_PATH . 'extra/maccms.php', $config_new);
    }

    //写入app接口列表

    function write_app_api()
    {
        $config_new['app_api_list'] = config('maccms')["app_api_list"];
        $config_new['app_api_list']['search'] = "搜索";
        $config_new['app_api_list']['index_video'] = "首页推荐";
        $config_new['app_api_list']['nav'] = "顶部导航";
        $config_new['app_api_list']['video'] = "视频列表";
        $config_new['app_api_list']['video_detail'] = "视频详情";
        $config_new['app_api_list']['video_prefer'] = "相关推荐";
        $config_new['app_api_list']['search_hot'] = "热门搜索";
        $config_new['app_api_list']['version'] = "最新版本";
        $config_new['app_api_list']['send_email'] = "发送邮件";
        $config_new['app_api_list']['register'] = "注册";
        $config_new['app_api_list']['login'] = "登录";
        $config_new['app_api_list']['logout'] = "注销";
        $config_new['app_api_list']['reset_password'] = "忘记密码";
        $config_new['app_api_list']['change_password'] = "修改密码";
        $config_new['app_api_list']['user_info'] = "用户详情";
        $config_new['app_api_list']['recharge'] = "充值卡充值";
        $config_new['app_api_list']['trade_record'] = "账单";
        $config_new['app_api_list']['recharge'] = "充值卡充值";
        $config_new['app_api_list']['vip_list'] = "会员列表";
        $config_new['app_api_list']['pay_vip'] = "购买会员";
        $config_new['app_api_list']['save_leave_msg'] = "提交留言";
        $config_new['app_api_list']['codepay'] = "码支付";
        $config_new['app_api_list']['app_config'] = "APP配置";
        $config_new['app_api_list']['advert'] = "广告";
        $config_new['app_api_list']['leave_msg_list'] = "我的留言";
        $config_new['app_api_list']['delete_leave_msg'] = "删除单留言";
        $config_new['app_api_list']['clear_mine_msg'] = "清空留言";
        $config_new['app_api_list']['notice'] = "公告消息列表";
        $config_new['app_api_list']['top_notice'] = "置顶公告";

        $config_old = config('maccms');
        $config_new = array_merge($config_old, $config_new);
        mac_arr2file(APP_PATH . 'extra/maccms.php', $config_new);
    }


    //欢迎
    public function view_welcome_form()
    {
        $getapp_all_setting = config('getapp_all_setting');
        $this->assign('getapp_client_auto_update', $getapp_all_setting['getapp_client_auto_update']);

        $config = config('maccms');
        $getapp_build = $config['getapp_build'];
        $this->assign('api_secret_key', $getapp_build['api_secret_key']);

        return $this->fetch('admin@getapp/welcome_form');
    }

    public function save_welcome_form()
    {

        $getapp_all_setting = config('getapp_all_setting');
        $request = $this->_param;

        if (strlen($request['api_secret_key']) != 16) {
            $response = ['code' => 1, 'msg' => '接口加密key为16位字符串!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }

        $getapp_all_setting['getapp_client_auto_update'] = $request['getapp_client_auto_update'];
        $res = mac_arr2file(APP_PATH . 'extra/getapp_all_setting.php', $getapp_all_setting);

        $config = config('maccms');
        $config['getapp_build']['api_secret_key'] = $request['api_secret_key'];
        $res = mac_arr2file(APP_PATH . 'extra/maccms.php', $config);

        if ($res === false) {
            $response = ['code' => 1, 'msg' => '保存配置文件失败，请重试!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }
        $response = ['code' => 0, 'msg' => '保存成功'];
        $json = json_encode($response);
        echo $json;
        exit;

    }

    //版本页面
    public function view_version()
    {
        return $this->fetch('admin@getapp/version_list');
    }

    //版本表单页面
    public function view_version_form()
    {

        if (!empty($this->_param['id'])) {
            //查询
            $field = '*';
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_update')->field($field)->where($where)->find();
            $version_arr = explode(".", $info['version_name']);
            $info['version_code'] = $version_arr[0];
            $info['version_code2'] = $version_arr[1];
            $info['version_code3'] = $version_arr[2];
            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/version_form');
    }

    //版本列表数据
    public function view_version_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'id desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_update')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_update')->where($where)->count();

        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //版本表单保存
    public function version_form_save()
    {
        $this->_param['version_name'] = $this->_param['version_code'] . "." . $this->_param['version_code2'] . "." . $this->_param['version_code3'];
        $this->_param['version_code'] = $this->_param['version_code'] . $this->_param['version_code2'] . $this->_param['version_code3'];

        if (empty($this->_param['id'])) {
            //插入
            $this->_param['create_time'] = time();
            Db::table('getapp_update')->insert($this->_param);
        } else {
            //更新
            Db::table('getapp_update')->update($this->_param);
        }
        cache('getapp_update', null);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
    }

    //版本删除
    public function version_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_update')->delete($v);
        }
        cache('getapp_update', null);
        echo $json;
        exit;
    }

    // 上传逻辑
    public function upload()
    {

        $param = input();
        $param['from'] = empty($param['from']) ? 'input' : $param['from'];
        $param['input'] = empty($param['input']) ? 'file' : $param['input'];
        $param['flag'] = empty($param['flag']) ? 'vod' : $param['flag'];
        $param['thumb'] = empty($param['thumb']) ? '0' : $param['thumb'];
        $param['thumb_class'] = empty($param['thumb_class']) ? '' : $param['thumb_class'];
        $param['user_id'] = empty($param['user_id']) ? '0' : $param['user_id'];

        $config = config('maccms.site');
        $pre = $config['install_dir'];

        switch ($param['from']) {
            case 'kindeditor':
                $param['input'] = 'imgFile';
                break;
            case 'umeditor':
                $param['input'] = 'upfile';
                break;
            case 'ckeditor':
                $param['input'] = 'upload';
                break;
            case 'ueditor':
                $param['input'] = 'upfile';
                if (isset($_GET['action']) && $_GET['action'] == 'config') {
                    $UE_CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents('./static/ueditor/config.json')), true);
                    echo json_encode($UE_CONFIG);
                    exit;
                }
                break;
            default: // 默认使用layui.upload上传控件
                $pre = '';
                break;
        }

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($param['input']);

        $data = [];
        if (empty($file)) {
            return self::upload_return('未找到上传的文件(原因：表单名可能错误，默认表单名“file”)！', $param['from']);
        }
        if ($file->getMime() == 'text/x-php') {
            return self::upload_return('禁止上传php,html文件！', $param['from']);
        }

        $upload_image_ext = 'jpg,jpeg,png,gif,xml';

        $upload_file_ext = 'doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip,torrent,jks,keystore,p12,mobileprovision';
        $upload_media_ext = 'rm,rmvb,avi,mkv,mp4,mp3';
        $sys_max_filesize = ini_get('upload_max_filesize');
        $config = config('maccms.upload');
        // 格式、大小校验
        if ($file->checkExt($upload_image_ext)) {
            $type = 'image';
        } elseif ($file->checkExt($upload_file_ext)) {
            $type = 'file';
        } elseif ($file->checkExt($upload_media_ext)) {
            $type = 'media';
        } else {
            return self::upload_return('非系统允许的上传格式！', $param['from']);
        }

        if ($param['flag'] == 'user') {
            $uniq = $param['user_id'] % 10;
            // 上传附件路径
            $_upload_path = ROOT_PATH . 'upload' . '/user/' . $uniq . '/';
            // 附件访问路径
            $_save_path = 'upload' . '/user/' . $uniq . '/';
            $_save_name = $param['user_id'] . '.jpg';

            if (!file_exists($_save_path)) {
                mac_mkdirss($_save_path);
            }

            $upfile = $file->move($_upload_path, $_save_name);

            if (!is_file($_upload_path . $_save_name)) {
                return self::upload_return('文件上传失败！', $param['from']);
            }
            $file = $_save_path . str_replace('\\', '/', $_save_name);
            $config = [
                'thumb_type' => 6,
                'thumb_size' => $GLOBALS['config']['user']['portrait_size'],
            ];

            $new_thumb = $param['user_id'] . '.jpg';
            $new_file = $_save_path . $new_thumb;
            try {
                $image = Image::open('./' . $file);
                $t_size = explode('x', strtolower($GLOBALS['config']['user']['portrait_size']));
                if (!isset($t_size[1])) {
                    $t_size[1] = $t_size[0];
                }
                $res = $image->thumb($t_size[0], $t_size[1], 6)->save('./' . $new_file);

                $file_count = 1;
                $file_size = round($upfile->getInfo('size') / 1024, 2);
                $data = [
                    'file' => $new_file,
                    'type' => $type,
                    'size' => $file_size,
                    'flag' => $param['flag'],
                    'ctime' => request()->time(),
                    'thumb_class' => $param['thumb_class'],
                ];


                return self::upload_return('文件上传成功', $param['from'], 1, $data);
            } catch (Exception $e) {
                return self::upload_return('生成缩放头像图片文件失败！', $param['from']);
            }
            exit;
        }
        // 上传附件路径
        $_upload_path = ROOT_PATH . 'upload' . '/' . $param['flag'] . '/';
        // 附件访问路径
        $_save_path = 'upload' . '/' . $param['flag'] . '/';
        $ymd = date('Ymd');

        $n_dir = $ymd;
        for ($i = 1; $i <= 100; $i++) {
            $n_dir = $ymd . '-' . $i;
            $path1 = $_upload_path . $n_dir . '/';
            if (file_exists($path1)) {
                $farr = glob($path1 . '*.*');
                if ($farr) {
                    $fcount = count($farr);
                    if ($fcount > 999) {
                        continue;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        $savename = $n_dir . '/' . md5(microtime(true));



        $upfile = $file->move($_upload_path, $savename);

        if (!is_file($_upload_path . $upfile->getSaveName())) {
            return self::upload_return('文件上传失败！', $param['from']);
        }

        //附件访问地址
        //$_file_path = $_save_path.$upfile->getSaveName();

        $file_count = 1;
        $file_size = round($upfile->getInfo('size') / 1024, 2);
        $upload_file = $_save_path . str_replace('\\', '/', $upfile->getSaveName());

        $data = [
            'file' => $upload_file,
            'type' => $type,
            'size' => $file_size,
            'flag' => $param['flag'],
            'ctime' => request()->time(),
            'thumb_class' => $param['thumb_class'],
        ];

        $data['thumb'] = [];
        if ($type == 'image') {
            //水印
            if ($config['watermark'] == 1) {
                model('Image')->watermark($data['file'], $config, $param['flag']);
            }
            // 缩略图
            if ($param['thumb'] == 1 && $config['thumb'] == 1) {
                $dd = model('Image')->makethumb($data['file'], $config, $param['flag']);
                if (is_array($dd)) {
                    $data = array_merge($data, $dd);
                }
            }
        }
        unset($upfile);

        if ($config['mode'] == 2) {
            $config['mode'] = 'upyun';
        } elseif ($config['mode'] == 3) {
            $config['mode'] = 'qiniu';
        } elseif ($config['mode'] == 4) {
            $config['mode'] = 'ftp';
        } elseif ($config['mode'] == 5) {
            $config['mode'] = 'weibo';
        }

        $config['mode'] = strtolower($config['mode']);

        if (!in_array($config['mode'], ['local', 'remote'])) {
            $data['file'] = model('Upload')->api($data['file'], $config);
            if (!empty($data['thumb'])) {
                $data['thumb'][0]['file'] = model('Upload')->api($data['thumb'][0]['file'], $config);
            }
        }

        if (in_array($param['from'], ['ueditor', 'umeditor', 'kindeditor', 'ckeditor'])) {
            if (substr($data['file'], 0, 4) != 'http' && substr($data['file'], 0, 4) != 'mac:') {
                $data['file'] = $pre . $data['file'];
            } else {
                $data['file'] = mac_url_content_img($data['file']);
            }
        }
        return self::upload_return('文件上传成功', $param['from'], 1, $data);
    }

    private function upload_return($info = '', $from = 'input', $status = 0, $data = [])
    {
        $arr = [];
        switch ($from) {
            case 'kindeditor':
                if ($status == 0) {
                    $arr['error'] = 1;
                    $arr['message'] = $info;
                } else {
                    $arr['error'] = 0;
                    $arr['url'] = $data['file'];
                }
                echo json_encode($arr, 1);
                exit;
                break;
            case 'ckeditor':
                if ($status == 1) {
                    $arr['uploaded'] = 1;
                    $arr['fileName'] = '';
                    $arr['url'] = $data['file'];
                    //echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(1, "'.$data['file'].'", "");</script>';
                } else {
                    $arr['uploaded'] = 0;
                    $arr['error']['msg'] = $info;
                    //echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(1, "", "'.$info.'");</script>';
                }
                echo json_encode($arr, 1);
                exit;
                break;
            case 'umeditor':
            case 'ueditor':
                if ($status == 0) {
                    $arr['message'] = $info;
                    $arr['state'] = 'ERROR';
                } else {
                    $arr['message'] = $info;
                    $arr['url'] = $data['file'];
                    $arr['state'] = 'SUCCESS';
                }
                echo json_encode($arr, 1);
                exit;
                break;

            default:
                $arr['msg'] = $info;
                $arr['code'] = $status;
                $arr['data'] = $data;
                break;
        }
        return $arr;
    }

    //播放器解析配置页面
    public function view_player_parse()
    {
        return $this->fetch('admin@getapp/player_parse');
    }

    //播放器解析配置列表
    public function view_player_parse_list_json()
    {
        $playerParseList = config("vodplayer");
        $getapp_parseList = config("getapp_vodplayer_parse");
        $sort = [];
        foreach ($playerParseList as $k => $v) {
            $player_sort = $v['sort'];
            $player_sort = $player_sort == "" ? 0 : $player_sort;

            if ($getapp_parseList[$k]) {
                $getapp_parseList_item = $getapp_parseList[$k];
                foreach ($getapp_parseList_item as $parse_key => $parse_item) {
                    switch ($parse_item['jx_type']) {
                        case 0:
                            $jx_type_name = "直链播放";
                            break;
                        case 1:
                            $jx_type_name = "JSON解析";
                            break;
                        case 2:
                            $jx_type_name = "HTML嗅探";
                            break;
                        default:
                            $jx_type_name = "其它";
                            break;

                    }
                    $getapp_parseList_item[$parse_key]['jx_type_name'] = $jx_type_name;
                }

                usort($getapp_parseList_item, function ($a, $b) {
                    return $a['app_is_show'] - $b['app_is_show'];
                });
                $playerParseList[$k]["parse_api"] = $getapp_parseList_item;
            } else {
                $playerParseList[$k]["parse_api"] = $getapp_parseList[$k];
            }

            $playerParseList[$k]["headers"] = $getapp_parseList[$k];
            $playerParseList[$k]["user_agent"] = $getapp_parseList[$k];
            $playerParseList[$k]["link_features"] = $getapp_parseList[$k];
            $playerParseList[$k]["un_link_features"] = $getapp_parseList[$k];
            $playerParseList[$k]["referrer_policy"] = $getapp_parseList[$k];
            $sort[] = $player_sort;
        }
        array_multisort($sort, SORT_DESC, $playerParseList);
        $response = ['code' => 0, 'msg' => '', 'data' => $playerParseList];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //播放器解析配置详情
    public function view_player_parse_form()
    {
        if (!empty($this->_param['id'])) {
            $getapp_parseList = config("getapp_vodplayer_parse");
            $get_parse_info = $getapp_parseList[$this->_param['id']][$this->_param['key']];

            if (isset($this->_param['key'])){
                $info["player_parse_type"] = $get_parse_info["player_parse_type"] == null ? 2 : $get_parse_info["player_parse_type"];
            }else{
                $info["player_parse_type"] = $get_parse_info["player_parse_type"] == null ? 1 : $get_parse_info["player_parse_type"];
            }

            $info["id"] = $this->_param['id'];
            $info["parse_api"] = $get_parse_info["parse_api"];
            $info["player_parse_key"] = $get_parse_info["player_parse_key"];
            $info["player_kernel_type"] = $get_parse_info["player_kernel_type"];
            $info["headers"] = $get_parse_info["headers"];
            $info["jx_type"] = $get_parse_info["jx_type"];
            $info["player_name"] = $get_parse_info["player_name"];
            $info["user_agent"] = $get_parse_info["user_agent"];
            $info["link_features"] = $get_parse_info["link_features"];
            $info["un_link_features"] = $get_parse_info["un_link_features"];
            $info["referrer_policy"] = $get_parse_info["referrer_policy"];
            $info["app_is_show"] = $get_parse_info["app_is_show"] == null ? 1 : $get_parse_info["app_is_show"];
            $this->assign('key', $this->_param['key']);
            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/player_parse_form');
    }

    //播放器解析配置保存
    public function player_parse_form_save()
    {
        $request = $this->_param;
        $list = config("getapp_vodplayer_parse");
        $key = 0;

        if (!empty($list) && $list[$this->_param['id']]) {
            $key = count($list[$this->_param['id']]);
            if ($this->_param['key'] !== "") {
                $key = $this->_param['key'];
            }

        }

        $request['key'] = $key;
        $request['id'] = $key;

        $new_list = [];
        foreach ($request as $k => $v) {
            $new_list[$k] = $v;
        }

        $list[$this->_param['id']][$key] = $new_list;

        $res = mac_arr2file(APP_PATH . 'extra/getapp_vodplayer_parse.php', $list);
        if ($res === false) {
            $response = ['code' => 1, 'msg' => '保存配置文件失败，请重试!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }

        cache('getapp_config', null);

        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //解析接口删除
    public function player_parse_delete()
    {
        $request = $this->_param;
        $getapp_parseList = config("getapp_vodplayer_parse");


        unset($getapp_parseList[$request['id']][$request['key']]);

        if (!empty($getapp_parseList[$request['id']])) {

            $key = 0;
            $new_list = [];
            foreach ($getapp_parseList[$request['id']] as &$value) {
                $value['id'] = $key;
                $value['key'] = $key;
                $new_list[$key] = $value;
                $key++;
            }

            $getapp_parseList[$request['id']] = $new_list;
        }

        $res = mac_arr2file(APP_PATH . 'extra/getapp_vodplayer_parse.php', $getapp_parseList);
        if ($res === false) {
            $response = ['code' => 1, 'msg' => '保存配置文件失败，请重试!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }

        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //系统配置
    public function view_system_form()
    {
        $config = Db::table('getapp_config')->select();
        $info = array_column($config, 'value', 'param_name');
        $this->assign('info', $info);

        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];
        if (!$getapp_system_config['system_config_xun_search_key']) {
            $search_key = substr(md5(uniqid(mt_rand(), true)), 0, 16);
            $getapp_system_config['system_config_xun_search_key'] =  $search_key;
            $config['getapp_system_config'] = $getapp_system_config;
            mac_arr2file(APP_PATH . 'extra/maccms.php', $config);
        }

        $getapp_system_config['system_config_top_comment_avtar_url'] = $this->getImgUrl($getapp_system_config['system_config_top_comment_avtar']);

        $this->assign('system_config', $getapp_system_config);
        $order='group_id asc';
        $where=[];
        $res = model('Group')->listData($where,$order);
        $this->assign('group_list',$res['list']);
        $this->assign('xun_search_build_index_url',  request()->domain() . "/api.php/getappapi.search/buildIndex?key=" . $getapp_system_config['system_config_xun_search_key']);
        $this->assign('xun_search_build_update_url',  request()->domain() . "/api.php/getappapi.search/update?key=" . $getapp_system_config['system_config_xun_search_key']);
        return $this->fetch('admin@getapp/system_form');
    }

    //系统配置保存
    public function save_system_form()
    {
        cache('getapp_init_data', null);
        $config = config('maccms');
        $request = $this->_param;
        foreach ($request as $k => $v) {
            Db::table('getapp_config')->where(['param_name' => $k])->update(['value' => $v]);
            $config['getapp_system_config'][$k] = $v;
        }
        cache('getapp_config', null);
        mac_arr2file(APP_PATH . 'extra/maccms.php', $config);


        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //系统配置保存
    public function clear_system_cache()
    {
        cache('getapp_init_data', null);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }


    //banner广告列表页面
    public function view_advert_list_banner()
    {
        return $this->fetch('admin@getapp/advert_list_banner');
    }


    //广告详情
    public function view_advert_form()
    {
        $info = [];
        $info["status"] = 1;
        $info["end_time"] = 32503651199;
        $info["time"] = 60;
        $info["skip_time"] = 10;
        if ($this->_param['key'] == "start") {
            $info["time"] = 5;
        }
        $this->assign('info', $info);
        if (!empty($this->_param['id'])) {
            //查询
            $field = '*';
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_advert')->field($field)->where($where)->find();
            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/advert_form_' . $this->_param['key']);
    }

    //广告列表数据
    public function view_advert_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['name'])) {
            $where['name'] = ['like', "%" . $this->_param['name'] . "%"];
        }
        if (is_numeric($this->_param['status'])) {
            $where['status'] = ['eq', $this->_param['status']];
        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'sort';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_advert')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_advert')->where($where)->count();
        $position_text = ['首页轮播下方广告', '首页轮播下方图标', '首页轮播', '播放页选集下方', '播放页详情顶部', '播放页评论顶部'];
        $ui_mode_text = ['不区分（全部模式）', '正常模式', '深色模式（暗黑模式）'];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["start_time"] = date('Y-m-d H:i:s', $v["start_time"]);
                $v["end_time"] = date('Y-m-d H:i:s', $v["end_time"]);
                $v['position_text'] = isset($position_text[$v['position']]) ? $position_text[$v['position']] : "-";
                $v['ui_mode_text'] = isset($ui_mode_text[$v['ui_mode']]) ? $ui_mode_text[$v['ui_mode']] : "-";
                $list[$k] = $v;
            }
        }

        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //广告保存
    public function advert_form_save()
    {
        if (isset($this->_param['start_time']) && !is_numeric($this->_param['start_time'])) {
            $this->_param['start_time'] = strtotime($this->_param['start_time']);
        }
        if (isset($this->_param['end_time']) && !is_numeric($this->_param['end_time'])) {
            $this->_param['end_time'] = strtotime($this->_param['end_time']);
        }
        if (empty($this->_param['id'])) {
            //插入
            Db::table('getapp_advert')->insert($this->_param);
        } else {
            //更新
            Db::table('getapp_advert')->update($this->_param);
        }
        cache('getapp_init_data', null);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
    }

    //广告删除
    public function advert_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_advert')->delete($v);
        }
        cache('getapp_init_data', null);
        echo $json;
        exit;
    }

    //广告排序页面
    public function view_advert_sort()
    {
        //查询
        $field = '*';
        $where = [];
        $where["id"] = $this->_param['id'];
        $info = Db::table('getapp_advert')->field($field)->where($where)->find();
        $this->assign('info', $info);
        return $this->fetch('admin@getapp/advert_form_sort');
    }

    //广告排序更新
    public function advert_form_save_sort()
    {
        //更新
        Db::table('getapp_advert')->update($this->_param);
        cache('getapp_init_data', null);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
    }

    //广告启用
    public function advert_form_status_enable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 1;
            Db::table('getapp_advert')->update($info);
        }
        cache('getapp_init_data', null);
        echo $json;
        exit;
    }

    //广告禁用
    public function advert_form_status_disable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 0;
            Db::table('getapp_advert')->update($info);
        }
        cache('getapp_init_data', null);
        echo $json;
        exit;
    }

    //广告配置页面
    public function view_advert_config_form()
    {
        $config = Db::table('getapp_config')->select();
        $info = array_column($config, 'value', 'param_name');
        $this->assign('info', $info);

        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];
        $this->assign('system_config', $getapp_system_config);

        return $this->fetch('admin@getapp/advert_config');
    }

    //广告配置保存
    public function save_advert_config_form()
    {
        $config = config('maccms');
        $request = $this->_param;
        foreach ($request as $k => $v) {
            Db::table('getapp_config')->where(['param_name' => $k])->update(['value' => $v]);
            $config['getapp_system_config'][$k] = $v;
        }

        mac_arr2file(APP_PATH . 'extra/maccms.php', $config);

        cache('getapp_config', null);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //公告列表页面
    public function view_notice_list()
    {
        return $this->fetch('admin@getapp/notice_list');
    }

    //公告详情
    public function view_notice_form()
    {
        $info = [];
        $info["is_top"] = 0;
        $info["status"] = 0;
        $this->assign('info', $info);
        if (!empty($this->_param['id'])) {
            //查询
            $field = '*';
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_notice')->field($field)->where($where)->find();
            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/notice_form');
    }

    //公告列表数据
    public function view_notice_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['title'])) {
            $where['title'] = ['like', "%" . $this->_param['title'] . "%"];
        }
        if (is_numeric($this->_param['is_top'])) {
            $where['is_top'] = ['eq', $this->_param['is_top']];
        }
        if (is_numeric($this->_param['status'])) {
            $where['status'] = ['eq', $this->_param['status']];
        }
        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'is_top desc,sort,create_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_notice')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_notice')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $list[$k] = $v;
            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //公告保存
    public function notice_form_save()
    {
        if (empty($this->_param['id'])) {
            //插入
            $this->_param["create_time"] = time();
            Db::table('getapp_notice')->insert($this->_param);
        } else {
            //更新
            Db::table('getapp_notice')->update($this->_param);
        }
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        cache('getapp_notice', null);
        echo $json;
    }

    //公告删除
    public function notice_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_notice')->delete($v);
        }
        cache('getapp_notice', null);
        echo $json;
        exit;
    }

    //公告排序页面
    public function view_notice_sort()
    {
        //查询
        $field = '*';
        $where = [];
        $where["id"] = $this->_param['id'];
        $info = Db::table('getapp_notice')->field($field)->where($where)->find();
        $this->assign('info', $info);
        return $this->fetch('admin@getapp/notice_form_sort');
    }

    //公告排序更新
    public function notice_form_save_sort()
    {
        //更新
        Db::table('getapp_notice')->update($this->_param);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        cache('getapp_notice', null);
        echo $json;
    }

    //公告置顶页面
    public function view_notice_top()
    {
        //查询
        $field = '*';
        $where = [];
        $where["id"] = $this->_param['id'];
        $info = Db::table('getapp_notice')->field($field)->where($where)->find();
        $this->assign('info', $info);
        return $this->fetch('admin@getapp/notice_form_top');
    }

    //公告置顶更新
    public function notice_form_save_top()
    {
        //更新
        Db::table('getapp_notice')->update($this->_param);
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        cache('getapp_notice', null);
        echo $json;
    }

    //公告启用
    public function notice_form_status_enable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 1;
            Db::table('getapp_notice')->update($info);
        }
        cache('getapp_notice', null);
        echo $json;
        exit;
    }

    //公告禁用
    public function notice_form_status_disable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 0;
            Db::table('getapp_notice')->update($info);
        }
        cache('getapp_notice', null);
        echo $json;
        exit;
    }

    //app打包
    public function view_app_build_form()
    {
        $app_build = config('maccms')["getapp_build"];
        if (!isset($app_build['platform_type'])) {
            $app_build['platform_type'] = 0;
        }

        if (!isset($app_build['build_type'])) {
            $app_build['build_type'] = 0;
        }

        if (!isset($app_build['theme_style'])) {
            $app_build['theme_style'] = 2;
        }

        if ($app_build['ad_union'] == 0) {
            $app_build['ad_union'] = 2;
        }
        $this->assign('info', $app_build);
        $data = $this->curlPostData($this->_apk_url, ['host' => $_SERVER['HTTP_HOST']], 5, false);
        $apk_data = json_decode($data, true);
        $this->assign('apk_data', $apk_data);

        $app_custom_icons = $this->app_custom_icons;
        foreach ($app_custom_icons as $key => $tab) {
           foreach ($tab['list'] as $tab_key => $icon) {
               $app_custom_icons[$key]['list'][$tab_key]['value'] = $app_build[$icon['key']];
           }
        }

        $this->assign('app_custom_icons', $app_custom_icons);


        return $this->fetch('admin@getapp/app_build_form');
    }

    //app打包配置
    public function save_app_build_form()
    {
        $config_new['getapp_build'] = config('maccms')["getapp_build"];
        $request = $this->_param;
        foreach ($request as $k => $v) {
            $config_new['getapp_build'][$k] = $v;
        }
        $config_old = config('maccms');
        $config_new = array_merge($config_old, $config_new);
        $version = $this->versionToNumber($this->_version);
        $getapp_build = $config_new['getapp_build'];
        $app_key_store = $getapp_build['app_key_store'];
        $app_key_store_password = $getapp_build['app_key_store_password'];
        $app_key_alias = $getapp_build['app_key_alias'];
        $app_key_alias_password = $getapp_build['app_key_alias_password'];

        $cert_url = $getapp_build['cert_url'];
        $provision_url = $getapp_build['provision_url'];


        if (!(($app_key_store && $app_key_store_password && $app_key_alias && $app_key_alias_password) || (empty($app_key_store) && empty($app_key_store_password) && empty($app_key_alias) && empty($app_key_alias_password)))) {
            echo json_encode(['code' => 1, 'msg' => '签名文件，签名密码，密钥别名，密钥密码四项，要么都填，要么都不填']);
            exit;
        }
        mac_arr2file(APP_PATH . 'extra/maccms.php', $config_new);


        try {

            $build_data = [
                'platform_type' => $getapp_build['platform_type'],
                'build_type' => $getapp_build['build_type'],
                'app_name' => $getapp_build['app_name'],
                'app_icon' => curl_file_create($config_new['getapp_build']['app_icon']),
                'app_splash_img' => curl_file_create($config_new['getapp_build']['splash_img']),
                'app_key_store' => '',
                'app_key_store_password' => $app_key_store_password,
                'app_key_alias' => $app_key_alias,
                'app_key_alias_password' => $app_key_alias_password,
                'app_theme_color' => $config_new['getapp_build']['theme_color'],
                'app_theme_style' => $config_new['getapp_build']['theme_style'],
                'app_version_code' => $config_new['getapp_build']['app_version'] . $config_new['getapp_build']['app_version2'] . $config_new['getapp_build']['app_version3'],
                'app_version_name' => $config_new['getapp_build']['app_version'] . "." . $config_new['getapp_build']['app_version2'] . "." . $config_new['getapp_build']['app_version3'],
                'create_time' => time(),
                'app_id' => $config_new['getapp_build']['app_id'],
                'app_id_ios' => $config_new['getapp_build']['app_id_ios'],
                'app_key' => $config_new['getapp_build']['app_key'],
                'app_key_ios' => $config_new['getapp_build']['app_key_ios'],
                'api_secret_key' => $config_new['getapp_build']['api_secret_key'],
                'cp_id' => $config_new['getapp_build']['cp_id'],
                'splash_id' => $config_new['getapp_build']['splash_id'],
                'insert_id' => $config_new['getapp_build']['insert_id'],
                'reward_id' => $config_new['getapp_build']['reward_id'],
                'banner_id' => $config_new['getapp_build']['banner_id'],
                'feed_id' => $config_new['getapp_build']['feed_id'],
                'cp_id_ios' => $config_new['getapp_build']['cp_id_ios'],
                'splash_id_ios' => $config_new['getapp_build']['splash_id_ios'],
                'insert_id_ios' => $config_new['getapp_build']['insert_id_ios'],
                'reward_id_ios' => $config_new['getapp_build']['reward_id_ios'],
                'banner_id_ios' => $config_new['getapp_build']['banner_id_ios'],
                'feed_id_ios' => $config_new['getapp_build']['feed_id_ios'],
                'um_key' => $config_new['getapp_build']['um_key'],
                'um_key_ios' => $config_new['getapp_build']['um_key_ios'],
                'host' => $_SERVER['HTTP_HOST'],
                'app_package_name' => $config_new['getapp_build']['app_package_name'],
                'scheme' => request()->scheme(),
                'talking_data_key' => $config_new['getapp_build']['talking_data_key'],
                'talking_data_key_ios' => $config_new['getapp_build']['talking_data_key_ios'],
                'ad_union' =>  $getapp_build['ad_union'] == 0 ? 2 : $getapp_build['ad_union'],
                'dynamic_domain' =>  $config_new['getapp_build']['dynamic_domain'],
                'version' => $version,
                'aliyun_license_key' => $getapp_build['aliyun_license_key'],
                'tencent_license_url' => $getapp_build['tencent_license_url'],
                'tencent_license_key' => $getapp_build['tencent_license_key'],
                'is_dark_mode' => $getapp_build['is_dark_mode'],
                'aliyun_license_is_sea' => $getapp_build['aliyun_license_is_sea'],
                'statistic_channel' => $getapp_build['statistic_channel'],
                'platform_type' => $getapp_build['platform_type'],
                'build_type' => $getapp_build['build_type'],
                'cert_password' => $getapp_build['cert_password'],

            ];
            if ($app_key_store) {
                $build_data['app_key_store'] = curl_file_create($app_key_store);
            }

            if ($cert_url) {
                $build_data['cert_url'] = curl_file_create($cert_url);
            }

            if ($provision_url) {
                $build_data['provision_url'] = curl_file_create($provision_url);
            }

            if ($getapp_build['app_custom_icon'] && $getapp_build['platform_type'] == 0) {
                $this->createIconZip($getapp_build);
                $build_data['app_zip_file'] = curl_file_create("upload/getapp/app-file.zip");
                $build_data['navbar_color_unselected'] = $getapp_build['navbar_color_unselected'];
                $build_data['navbar_color_selected'] = $getapp_build['navbar_color_selected'];
                $build_data['dark_navbar_color_unselected'] = $getapp_build['dark_navbar_color_unselected'];
                $build_data['dark_navbar_color_selected'] = $getapp_build['dark_navbar_color_selected'];
                $build_data['app_custom_empty_text'] = $getapp_build['app_custom_empty_text'];
            }


            $build_data_res = $this->curlPostData($this->_build_url, $build_data);

            $build_res = json_decode($build_data_res, true);

            if (!$build_res['code']) {
                $response = ['code' => 1, 'msg' => $build_res['msg']];
                $json = json_encode($response);
                echo $json;
                exit;
            }


            $response = ['code' => 0, 'msg' => $build_res['msg']];
            $json = json_encode($response);
            echo $json;
            exit;
        } catch (\Exception $e) {
            $this->setErrorMsg($e->getMessage());
        }

    }

    public function curlPostData($url, $postData, $connect_timeout = 0, $is_upload = true)
    {
        // 创建cURL资源
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        if ($connect_timeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout); // 设置连接的最大等待时间为 5 秒
        }

        // 设置其他cURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 执行cURL请求并获取响应
        $response = curl_exec($ch);

        // 检查请求是否成功
        if ($response === false) {
            if ($is_upload) {
                $this->setErrorMsg("上传文件失败,需本地上传后再打包：" . curl_error($ch));
            } else {
                $this->setErrorMsg("连接到打包服务器失败，请重试或者切换本地或国外服务器打包，再同步接口加密key，" . curl_error($ch));
            }

        }

        // 关闭cURL资源
        curl_close($ch);

        // 返回响应数据
        return $response;
    }

    //评论列表页面
    public function view_comment_list()
    {
        return $this->fetch('admin@getapp/comment_list');
    }

    //评论列表数据
    public function view_comment_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['comment'])) {
            $where['comment'] = ['like', "%" . $this->_param['comment'] . "%"];
        }

        if (is_numeric($this->_param['status'])) {
            $where['status'] = ['eq', $this->_param['status']];
        }

        if (is_numeric($this->_param['is_report'])) {
            if ($this->_param['is_report'] == 1) {
                $where['comment_report'] = ['>', 0];
            } else {
                $where['comment_report'] = ['<', 0];
            }

        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'create_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_vod_comment')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_vod_comment')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $v['vod_name'] = Db::table('mac_vod')->where(['vod_id' => $v['vod_id']])->value('vod_name');
                $v['user_name'] = Db::name('user')->where(['user_id' => $v['user_id']])->value('user_name');
                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //评论拒绝
    public function comment_form_status_disable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 2;
            Db::table('getapp_vod_comment')->update($info);
        }
        echo $json;
        exit;
    }

    //评论通过
    public function comment_form_status_enable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 1;
            Db::table('getapp_vod_comment')->update($info);
        }
        echo $json;
        exit;
    }

    //评论删除
    public function comment_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_vod_comment')->delete($v);
        }
        echo $json;
        exit;
    }

    //弹幕列表页面
    public function view_danmu_list()
    {
        return $this->fetch('admin@getapp/danmu_list');
    }

    //弹幕列表数据
    public function view_danmu_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['text'])) {
            $where['text'] = ['like', "%" . $this->_param['text'] . "%"];
        }

        if (is_numeric($this->_param['status'])) {
            $where['status'] = ['eq', $this->_param['status']];
        }

        if (is_numeric($this->_param['report'])) {
            if ($this->_param['report'] > 0) {
                $where['report_times'] = ['>', 0];
            } else {
                $where['report_times'] = ['eq', 0];
            }
        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'id desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_vod_danmu')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_vod_danmu')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $v['vod_name'] = Db::name('vod')->where(['vod_id' => $v['vod_id']])->value('vod_name');
                if ($v['user_id']) {
                    $v['user_name'] = Db::name('user')->where(['user_id' => $v['user_id']])->value('user_name');
                } else {
                    $v['user_name'] = "-";
                }
                $v['url_position'] += 1;
                $v['show_time'] = gmdate("H:i:s",  $v['time'] / 1000);

                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //弹幕拒绝
    public function danmu_form_status_disable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 2;
            Db::table('getapp_vod_danmu')->update($info);
        }
        echo $json;
        exit;
    }

    //弹幕通过
    public function danmu_form_status_enable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 1;
            Db::table('getapp_vod_danmu')->update($info);
        }
        echo $json;
        exit;
    }

    //弹幕删除
    public function danmu_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_vod_danmu')->delete($v);
        }
        echo $json;
        exit;
    }

    /**
     * 弹幕设为未举报
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function danmu_form_status_unreport()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['report_times'] = 0;
            Db::table('getapp_vod_danmu')->update($info);
        }
        echo $json;
        exit;
    }

    //用户列表页面
    public function view_user_list()
    {
        return $this->fetch('admin@getapp/user_list');
    }

    //弹幕列表数据
    public function view_user_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['user_name'])) {
            $where['user_name'] = ['like', "%" . $this->_param['user_name'] . "%"];
        }

        if (is_numeric($this->_param['status'])) {
            $where['status'] = ['eq', $this->_param['status']];
        }
        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'create_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_user')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_user')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //用户拒绝
    public function user_form_status_disable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        $user = Db::table('getapp_user')->where(['id' => $this->_param['ids']])->find();
        cache($user['auth_token'], null);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 1;
            Db::table('getapp_user')->update($info);
        }
        echo $json;
        exit;
    }

    //用户通过
    public function user_form_status_enable()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        $user = Db::table('getapp_user')->where(['id' => $this->_param['ids']])->fetchSql()->find();
        cache($user['auth_token'], null);
        foreach ($idList as $k => $v) {
            $info = [];
            $info['id'] = $v;
            $info['status'] = 0;
            Db::table('getapp_user')->update($info);
        }

        echo $json;
        exit;
    }

    //用户删除
    public function user_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        $user = Db::table('getapp_user')->where(['id' => $this->_param['ids']])->find();
        cache($user['auth_token'], null);
        foreach ($idList as $k => $v) {
            Db::table('getapp_user')->delete($v);
        }
        echo $json;
        exit;
    }


    //求片列表页面
    public function view_piece_list()
    {
        return $this->fetch('admin@getapp/piece_list');
    }

    //求片列表数据
    public function view_piece_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['name'])) {
            $where['name'] = ['like', "%" . $this->_param['name'] . "%"];
        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'create_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_user_find')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_user_find')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $v['user_name'] = Db::name('user')->where(['user_id' => $v['user_id']])->value('user_name');

                $notice = Db::table('getapp_user_notice')->where([
                    'from_id' => $v['id'],
                    'from_type' => 2
                ])->find();
                $v['reply_content'] = empty($notice) ? "" : $notice['reply_content'];
                $v['reply_link'] = empty($notice) ? "" : $notice['reply_link'];
                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //求片删除
    public function piece_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_user_find')->delete($v);
            Db::table('getapp_user_notice')->where([
                'from_id' => $v,
                'from_type' => 2
            ])->delete();
        }
        echo $json;
        exit;
    }

    //反馈表单页面
    public function view_piece_form()
    {

        if (!empty($this->_param['id'])) {

            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_user_find')->where($where)->find();

            $notice = Db::table('getapp_user_notice')->where([
                'from_id' => $info['id'],
                'from_type' => 2
            ])->find();
            $info["create_time"] = date('Y-m-d H:i:s', $info["create_time"]);
            $info['user_name'] = Db::name('user')->where(['user_id' => $info['user_id']])->value('user_name');
            $info['reply_content'] = empty($notice) ? "" : $notice['reply_content'];
            $info['reply_link'] = empty($notice) ? "" : $notice['reply_link'];

            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/piece_form');
    }

    public function piece_form_save()
    {
        if (empty($this->_param['id']) || empty($this->_param['reply_content'])) {
            $response = ['code' => 0, 'msg' => ''];
            $json = json_encode($response);
            echo $json;
        } else {
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_user_find')->where($where)->find();

            $notice = Db::table('getapp_user_notice')->where([
                'from_id' => $this->_param['id'],
                'from_type' => 2
            ])->find();
            if ($notice) {
                Db::table('getapp_user_notice')->where([
                    'id' => $notice['id']
                ])->update([
                    'reply_content' => $this->_param['reply_content'],
                    'reply_link' => $this->_param['reply_link'],
                    'is_read' => 0,
                    'create_time' => time()
                ]);
            } else {
                Db::table('getapp_user_notice')->insert([
                    'user_id' => $info['user_id'],
                    'from_id' => $info['id'],
                    'from_type' => 2,
                    'reply_content' => $this->_param['reply_content'],
                    'reply_link' => $this->_param['reply_link'],
                    'create_time' => time()
                ]);
            }
        }
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
    }


    //意见反馈列表页面
    public function view_feedback_list()
    {
        return $this->fetch('admin@getapp/feedback_list');
    }

    //意见反馈列表数据
    public function view_feedback_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['content'])) {
            $where['content'] = ['like', "%" . $this->_param['content'] . "%"];
        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'create_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_user_suggest')
            ->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_user_suggest')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $notice = Db::table('getapp_user_notice')->where([
                    'from_id' => $v['id'],
                    'from_type' => 1
                ])->find();
                $v["create_time"] = date('Y-m-d H:i:s', $v["create_time"]);
                $v['user_name'] = Db::name('user')->where(['user_id' => $v['user_id']])->value('user_name');
                $v['reply_content'] = empty($notice) ? "" : $notice['reply_content'];
                $v['reply_link'] = empty($notice) ? "" : $notice['reply_link'];
                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //意见反馈删除
    public function feedback_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_user_suggest')->delete($v);
            Db::table('getapp_user_notice')->where([
                'from_id' => $v,
                'from_type' => 1
            ])->delete();
        }
        echo $json;
        exit;
    }

    //反馈表单页面
    public function view_feedback_form()
    {

        if (!empty($this->_param['id'])) {
            //查询
            $field = '*';
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_user_suggest')->field($field)->where($where)->find();

            $notice = Db::table('getapp_user_notice')->where([
                'from_id' => $info['id'],
                'from_type' => 1
            ])->find();
            $info["create_time"] = date('Y-m-d H:i:s', $info["create_time"]);
            $info['user_name'] = Db::name('user')->where(['user_id' => $info['user_id']])->value('user_name');
            $info['reply_content'] = empty($notice) ? "" : $notice['reply_content'];
            $info['reply_link'] = empty($notice) ? "" : $notice['reply_link'];
            $this->assign('info', $info);
        }
        return $this->fetch('admin@getapp/feedback_form');
    }

    public function feedback_form_save()
    {
        if (empty($this->_param['id']) || empty($this->_param['reply_content'])) {
            $response = ['code' => 0, 'msg' => ''];
            $json = json_encode($response);
            echo $json;
        } else {
            $where = [];
            $where["id"] = $this->_param['id'];
            $info = Db::table('getapp_user_suggest')->where($where)->find();

            $notice = Db::table('getapp_user_notice')->where([
                'from_id' => $this->_param['id'],
                'from_type' => 1
            ])->find();
            if ($notice) {
                Db::table('getapp_user_notice')->where([
                    'id' => $notice['id']
                ])->update([
                    'reply_content' => $this->_param['reply_content'],
                    'reply_link' => $this->_param['reply_link'],
                    'is_read' => 0,
                    'create_time' => time()
                ]);
            } else {
                Db::table('getapp_user_notice')->insert([
                    'user_id' => $info['user_id'],
                    'from_id' => $info['id'],
                    'from_type' => 1,
                    'reply_content' => $this->_param['reply_content'],
                    'reply_link' => $this->_param['reply_link'],
                    'create_time' => time()
                ]);
            }
        }
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
    }


    //崔更列表页面
    public function view_update_list()
    {
        return $this->fetch('admin@getapp/update_list');
    }

    //崔更列表数据
    public function view_update_list_json()
    {
        $json = '';
        $where = [];
        $limit = null;
        $page = null;


        if (!empty($this->_param['content'])) {
            $where['content'] = ['like', "%" . $this->_param['content'] . "%"];
        }

        if (empty($this->_param['page'])) {
            $this->_param['page'] = 1;
        }
        if (empty($this->_param['limit'])) {
            $this->_param['limit'] = 10;
        }

        $page = $this->_param['page'];
        $limit = $this->_param['limit'];

        $order = 'update_time desc';
        $field = '*';

        $limit_str = ($limit * ($page - 1)) . "," . $limit;
        $list = Db::table('getapp_request_update')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $total = Db::table('getapp_request_update')->where($where)->count();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v['update_time'] = date('Y-m-d H:i:s', $v["update_time"]);
                $v['vod_name'] = Db::table('mac_vod')->where(['vod_id' => $v['vod_id']])->value('vod_name');
                $v['user_name'] = Db::name('user')->where(['user_id' => $v['user_id']])->value('user_name');
                $list[$k] = $v;

            }
        }
        $response = ['code' => 0, 'msg' => '', 'data' => $list, 'count' => $total];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    //崔更删除
    public function update_form_delete()
    {
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        if (empty($this->_param['ids'])) {
            echo $json;
            exit;
        }
        $idList = explode(',', $this->_param['ids']);
        foreach ($idList as $k => $v) {
            Db::table('getapp_request_update')->delete($v);
        }
        echo $json;
        exit;
    }

    public function checkUpdate()
    {

        $version = $this->versionToNumber($this->_version);

        if ($version <= 100) {
            exit();
        }

        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ],
            'http' => [
                'timeout' => 5 // 设置超时
            ]
        ]);
        $new_version = file_get_contents($this->_update_url . "checkUpdate?version=" . $version, false, $context);

        $data['new_version'] = $new_version;
        $data['is_update'] = 0;
        if ($new_version > $version) {
            $data['is_update'] = 1;
        }

        return json(['code' => 0, 'msg' => '检测到新版本V' . implode('.', str_split($new_version)) . ',是否更新？ <br> <a style="text-decoration: underline" href="https://getapp.tv/docs/movie/update.html" target="_blank">查看更新日志</a>', 'data' => $data]);

    }


    public function update()
    {
        $version = $this->versionToNumber($this->_version);
        $url = $this->_update_url . "update?version=" . $version;
        // 下载更新包并保存到本地
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ]);
        $content = file_get_contents($url, false, $context);
        $path = "addons/getapp/update.zip";
        file_put_contents($path, $content);

        // 如果下载的是压缩包，解压缩后递归覆盖子目录
        if (substr($path, -4) == ".zip") {
            $zip = new \ZipArchive();
            if ($zip->open($path) === TRUE) {

                $zip->extractTo(dirname($path));
                $zip->close();

                //更新sql
                $sql_file = $_SERVER['DOCUMENT_ROOT'] . "/addons/getapp/update.sql";
                $this->updateSql($sql_file);

                // 遍历子目录并递归覆盖
                $this->recurse_copy($_SERVER['DOCUMENT_ROOT'] . "/addons/getapp/src/getapp/", $_SERVER['DOCUMENT_ROOT'] . "/application/admin/view/getapp/");
                $this->recurse_copy($_SERVER['DOCUMENT_ROOT'] . "/addons/getapp/src/getappapi/", $_SERVER['DOCUMENT_ROOT'] . "/application/api/controller/getappapi/");
                copy($_SERVER['DOCUMENT_ROOT'] . '/addons/getapp/src/Getapp.php', $_SERVER['DOCUMENT_ROOT'] . "/application/admin/controller/Getapp.php");

                // 检查文件是否存在
                if (file_exists($path)) {
                    //删除更新文件
                    unlink($path);
                }

                return json(['code' => 0, 'msg' => '更新成功', 'data' => '']);

            } else {
                return json(['code' => 1, 'msg' => '更新失败', 'data' => '']);
            }
        } else {
            return json(['code' => 1, 'msg' => '更新失败', 'data' => '']);
        }

    }

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    function versionToNumber($version)
    {
        $parts = explode('.', $version);
        $number = '';
        foreach ($parts as $part) {
            $number .= $part;
        }
        return intval($number);
    }

    function updateSql($sql_file)
    {

        if (is_file($sql_file)) {
            $lines = file($sql_file);
            $templine = '';

            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    try {
                        Db::execute($templine);
                    } catch (Exception $e) {
                        file_put_contents(__DIR__ . "/getapp_sql_error.txt", date("Y-m-d H:i:s") . "\r\n" . $e->getMessage() . "\r\n", 8);
                    }
                    $templine = '';
                }
            }
        }
    }

    //app页面设置
    public function view_app_page_setting()
    {

        $this->assign('info',config('getapp_all_setting')['app_page_setting']);
        $this->assign('index_exist', $this->getVodIndexIsExist());
        return $this->fetch('admin@getapp/app_page_setting');

    }

    private function getVodIndexIsExist()
    {
        $vod_table_name = config('database.prefix') . "vod";
        $sql = "SHOW INDEX FROM {$vod_table_name} WHERE column_name = 'vod_weekday'";
        $result = Db::query($sql);
        return !empty($result);
    }
    //
    public function save_app_page_setting_form()
    {
        if (!$this->getVodIndexIsExist()) {
            $vod_table_name = config('database.prefix') . "vod";
            $sql = "ALTER TABLE {$vod_table_name} ADD INDEX `vod_weekday` (`vod_weekday`)";
            $result = Db::execute($sql);
            if (!$result) {
                $response = ['code' => 1, 'msg' => '索引添加失败，请自行添加！'];
                $json = json_encode($response);
                echo $json;
                exit;
            }
        }

        $getapp_all_setting = config('getapp_all_setting');

        $request = $this->_param;

        $getapp_all_setting['app_page_setting'] = $request;

        $res = mac_arr2file(APP_PATH . 'extra/getapp_all_setting.php', $getapp_all_setting);
        if ($res === false) {
            $response = ['code' => 1, 'msg' => '保存配置文件失败，请重试!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    private function createIconZip($app_build)
    {
        $this->clearIconZip();
        $destinationFolder = $_SERVER['DOCUMENT_ROOT'] . "/upload/getapp/app-file/app-image";
        if (!is_dir($destinationFolder)) {
            if (!mkdir($destinationFolder, 0755, true)) {
                $response = ['code' => 0, 'msg' => "创建文件夹失败"];
                $json = json_encode($response);
                echo $json;
                exit;
            }
        }
        foreach ($this->app_custom_icons as $tab) {
            foreach ($tab['list'] as $icon) {
                $icon_path = $app_build[$icon['key']];
                if (empty($icon_path)) {
//               $response = ['code' => 0, 'msg' => '图标：' . $icon['name'] . "，未上传"];
//               $json = json_encode($response);
//               echo $json;
//               exit;
                } else {
                    $sourceFile = $_SERVER['DOCUMENT_ROOT'] . "/" . $icon_path;
                    // 获取文件信息
                    $fileInfo = pathinfo($sourceFile);
                    $fileExtension = isset($fileInfo['extension']) ? $fileInfo['extension'] : 'jpg';
                    $destinationFile = $destinationFolder . "/" . $icon['key'] . "." . $fileExtension;
                    if (!copy($sourceFile, $destinationFile)) {
                        $response = ['code' => 0, 'msg' => "文件复制失败"];
                        $json = json_encode($response);
                        echo $json;
                        exit;
                    }
                }
            }

        }

        // 使用示例
        $source = $_SERVER['DOCUMENT_ROOT'] . '/upload/getapp/app-file'; // 要压缩的文件夹路径
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/upload/getapp/app-file.zip'; // 压缩后生成的zip文件路径
        $this->zipFolder($source, $destination);
    }

    private function clearIconZip($dir = "")
    {
        if (!$dir) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/getapp';
        }

        if (is_dir($dir)) {
            // 递归遍历目录
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $filePath = $dir . '/' . $file;
                if (is_dir($filePath)) {
                    // 如果是目录，递归清空
                    $this->clearIconZip($filePath);
                    // 删除空目录
                    rmdir($filePath);
                } else {
                    // 如果是文件，删除文件
                    unlink($filePath);
                }
            }
        }
    }


   private function zipFolder($source, $destination) {
        // 创建一个新的ZipArchive对象
        $zip = new \ZipArchive();

        // 打开要创建的zip文件
        if ($zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            exit("无法打开 <$destination>\n");
        }

        // 递归地添加文件和文件夹到zip文件中
        $source = rtrim($source, '/') . '/';
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // 跳过目录（只处理文件）
            if (!$file->isDir()) {
                // 获取文件的相对路径
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source));

                // 将文件添加到zip文件中
                $zip->addFile($filePath, $relativePath);
            }
        }
        // 关闭zip文件
        $zip->close();
    }


    public function view_vip_points_form()
    {
        $this->assign('info',config('getapp_all_setting')['vip_points']);
        return $this->fetch('admin@getapp/vip_points_form');
    }


    public function save_vip_points_form()
    {

        $getapp_all_setting = config('getapp_all_setting');

        $request = $this->_param;

        $getapp_all_setting['vip_points'] = $request;

        $res = mac_arr2file(APP_PATH . 'extra/getapp_all_setting.php', $getapp_all_setting);
        if ($res === false) {
            $response = ['code' => 1, 'msg' => '保存配置文件失败，请重试!'];
            $json = json_encode($response);
            echo $json;
            exit;
        }
        $response = ['code' => 0, 'msg' => ''];
        $json = json_encode($response);
        echo $json;
        exit;
    }

    private function getImgUrl($img_path)
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

}
