<?php

namespace app\api\controller\getappapi;

use think\Db;
use think\Exception;
use think\Request;
use think\captcha\Captcha;

class Index extends Base
{

    private $xs_path = "addons/getapp/extra/XS.php";

    private $xs_ini_path = "addons/getapp/extra/search_vod.ini";
    protected $simple_vod_fields = "vod_id, vod_name, vod_pic, vod_remarks, vod_actor, vod_blurb, vod_pic_slide";
    protected $vod_fields = 'vod_id, vod_name, vod_pic, vod_pic_slide, vod_remarks, vod_sub, vod_class, vod_actor, vod_score, vod_year, vod_lang, vod_area, vod_blurb';

    protected $init_vod_fields = "vod_id, vod_name, vod_pic, vod_remarks, vod_pic_slide";

    public function verify()
    {
        exit();
    }


    /**
     * 初始化
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function init()
    {
        $maccms_config = config('maccms');
        $app_config = $maccms_config['app'];
        $getapp_system_config = $maccms_config['getapp_system_config'];

        $init_data = cache('getapp_init_data');
        if ($init_data) {
            $banner_list = $init_data['banner_list'];
            $recommend_list = $init_data['recommend_list'];
            $type_list = $init_data['type_list'];
            $week_list = $init_data['week_list'];
        } else {
            //全部分类
            $type_list = Db::name('type')
                ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => 0])
                ->order('type_sort asc')
                ->field('type_id, type_name, type_extend')
                ->select();
            //子分类
            $type_ids = array_column($type_list, 'type_id');
            $child_type_list = Db::name('type')
                ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => ['in', $type_ids]])
                ->order('type_sort asc')
                ->field('type_id, type_name, type_pid')
                ->select();
            $child_types = [];
            foreach ($child_type_list as $child_type) {
                $child_types[$child_type['type_pid']][] = $child_type['type_id'];
            }


            //首页banner-默认推荐9
            $system_banner_level = $getapp_system_config['system_banner_level'] ?: 9;
            $banner_list = Db::name('vod')
                ->where(['vod_level' => ['eq', $system_banner_level]])
                ->field($this->vod_fields)
                ->order('vod_hits desc')
                ->limit(10)
                ->select();

            $advert = Db::table('getapp_advert')
                ->where(['status' => 1, 'position' => 2, 'start_time' => ['<', $this->time], 'end_time' => ['>', $this->time]])
                ->field('id as vod_id, name as vod_name, content as vod_pic, req_content as vod_link')
                ->order('sort desc')
                ->select();
            if (!empty($advert)) {
                $banner_list = array_merge($advert, $banner_list);
            }


            //当前热播-默认推荐8
            $system_hot_level = $getapp_system_config['system_hot_level'] ?: 8;
            $recommend_list = Db::name('vod')
                ->where(['vod_level' => ['eq', $system_hot_level]])
                ->field($this->vod_fields)
                ->order('vod_hits desc')
                ->limit(20)
                ->select();


            foreach ($banner_list as &$banner) {
                if (!empty($banner["vod_pic_slide"])) {
                    $banner['vod_pic'] = $banner['vod_pic_slide'];
                }
                $banner['vod_pic'] = $this->getImgUrl($banner['vod_pic']);
            }

            foreach ($recommend_list as &$recommend) {
                if (!empty($recommend["vod_pic_slide"])) {
                    $recommend['vod_pic'] = $recommend['vod_pic_slide'];
                }
                if (empty($recommend["vod_sub"])) {
                    $recommend['vod_sub'] = $recommend['vod_blurb'];
                }
                $recommend['vod_pic'] = $this->getImgUrl($recommend['vod_pic']);
            }

            foreach ($type_list as &$type) {
                //分类banner-推荐7
                $child_type_ids = isset($child_types[$type['type_id']]) ? $child_types[$type['type_id']] : [];
                $where_type_ids = array_merge([$type['type_id']], $child_type_ids);

                $type_recommend_list = Db::name('vod')
                    ->where(['type_id' => ['in', $where_type_ids]])
                    ->order('vod_time desc')
                    ->field($this->simple_vod_fields)
                    ->limit(30)
                    ->select();
                $type_recommend_list = $this->replaceVodPic($type_recommend_list);


                $vod_extend_class = $app_config['vod_extend_class'] ? explode(",", $app_config['vod_extend_class']) : [];
                $vod_extend_area = $app_config['vod_extend_area'] ? explode(",", $app_config['vod_extend_area']) : [];
                $vod_extend_lang = $app_config['vod_extend_lang'] ? explode(",", $app_config['vod_extend_lang']) : [];
                $vod_extend_year = $app_config['vod_extend_year'] ? explode(",", $app_config['vod_extend_year']) : [];
                $vod_extend_sort = ["最新", "最热", "最赞"];
                $type_extends = json_decode($type['type_extend'], true);

                if ($type_extends['class'] || $type_extends['area'] || $type_extends['lang'] || $type_extends['year']) {
                    $vod_extend_class = $type_extends['class'] ? explode(",", $type_extends['class']) : [];
                    $vod_extend_area = $type_extends['area'] ? explode(",", $type_extends['area']) : [];
                    $vod_extend_lang = $type_extends['lang'] ? explode(",", $type_extends['lang']) : [];
                    $vod_extend_year = $type_extends['year'] ? explode(",", $type_extends['year']) : [];
                }

                $filter_type_list = [];

                if ($vod_extend_class) {
                    $vod_extend_class = array_merge(["全部"], $vod_extend_class);
                    $filter_type_list[] = ["name" => "class", "list" => $vod_extend_class];
                }
                if ($vod_extend_area) {
                    $vod_extend_area = array_merge(["全部"], $vod_extend_area);
                    $filter_type_list[] = ["name" => "area", "list" => $vod_extend_area];
                }
                if ($vod_extend_lang) {
                    $vod_extend_lang = array_merge(["全部"], $vod_extend_lang);
                    $filter_type_list[] = ["name" => "lang", "list" => $vod_extend_lang];
                }
                if ($vod_extend_year) {
                    $vod_extend_year = array_merge(["全部"], $vod_extend_year);
                    $filter_type_list[] = ["name" => "year", "list" => $vod_extend_year];
                }
                $filter_type_list[] = ["name" => "sort", "list" => $vod_extend_sort];

                $type['banner_list'] = [];
                $type['recommend_list'] = $type_recommend_list;
                $type['filter_type_list'] = $filter_type_list;
            }
            array_unshift($type_list, ['type_name' => '全部', 'type_id' => 0, 'recommend_list' => []]);
            $cache_time = $getapp_system_config['init_cache_time'];

            //排期表
            $week_list = $this->getInitVodWeekList();
            if ($cache_time > 0) {
                cache('getapp_init_data', compact('banner_list', 'recommend_list', 'type_list', 'week_list'), $cache_time);
            }

        }


        //热门搜索
        $hot_search_list = explode(",", str_replace("，", ",", $app_config['search_hot']));

        //版本更新
        $update = $this->getUpdate();

        //置顶公告
        $notice = $this->getTopNotice();

        //user_info
        $user_info = $this->user_info;

        //页面设置
        $app_page_setting = $this->getAppPageSetting();

        //config
        $config = $this->getConfig();
        $config = $this->getVipConfig($config);

        return $this->setData(compact('banner_list', 'recommend_list', 'type_list', 'hot_search_list', 'update', 'notice', 'user_info', 'config', 'week_list', 'app_page_setting'));
    }

    public function initV119()
    {
        $maccms_config = config('maccms');
        $app_config = $maccms_config['app'];
        $getapp_system_config = $maccms_config['getapp_system_config'];

        $init_data = cache('getapp_init_data');
        $app_page_setting = config('getapp_all_setting')['app_page_setting'];
        $app_page_rank_list_type = $app_page_setting['app_page_rank_list_type'];

        if ($init_data) {
            $banner_list = $init_data['banner_list'];
            $recommend_list = $init_data['recommend_list'];
            $type_list = $init_data['type_list'];
            $week_list = $init_data['week_list'];
            $icon_advert = $init_data['icon_advert'];
            $home_advert = $init_data['home_advert'];
        } else {
            //全部分类
            $type_list = Db::name('type')
                ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => 0])
                ->order('type_sort asc')
                ->field('type_id, type_name, type_extend')
                ->select();
            //子分类
            $type_ids = array_column($type_list, 'type_id');
            $child_type_list = Db::name('type')
                ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => ['in', $type_ids]])
                ->order('type_sort asc')
                ->field('type_id, type_name, type_pid')
                ->select();
            $child_types = [];
            foreach ($child_type_list as $child_type) {
                $child_types[$child_type['type_pid']][] = $child_type['type_id'];
            }


            //首页banner-默认推荐9
            $system_banner_level = $getapp_system_config['system_banner_level'] ?: 9;
            $banner_list = Db::name('vod')
                ->where(['vod_level' => ['eq', $system_banner_level]])
                ->field($this->init_vod_fields)
                ->order('vod_hits desc')
                ->limit(10)
                ->select();

            $advert = $this->getAdvert(2, true);
            if (!empty($advert)) {
                $banner_list = array_merge($advert, $banner_list);
            }

            $home_advert = $this->getAdvert(0);
            if ($home_advert) {
                $home_advert['vod_pic'] = $this->getImgUrl($home_advert['vod_pic']);
            }

            $icon_advert = $this->getAdvert(1, true);

            foreach ($icon_advert as &$icon) {
                $icon['vod_pic'] = $this->getImgUrl($icon['vod_pic']);
            }

            //当前热播-默认推荐8
            $system_hot_level = $getapp_system_config['system_hot_level'] ?: 8;
            $recommend_list = Db::name('vod')
                ->where(['vod_level' => ['eq', $system_hot_level]])
                ->field($this->init_vod_fields)
                ->order('vod_hits desc')
                ->limit(20)
                ->select();


            foreach ($banner_list as &$banner) {
                if (!empty($banner["vod_pic_slide"])) {
                    $banner['vod_pic'] = $banner['vod_pic_slide'];
                }
                $banner['vod_pic'] = $this->getImgUrl($banner['vod_pic']);
            }

            foreach ($recommend_list as &$recommend) {
                if (!empty($recommend["vod_pic_slide"])) {
                    $recommend['vod_pic'] = $recommend['vod_pic_slide'];
                }
                if (empty($recommend["vod_sub"])) {
                    $recommend['vod_sub'] = $recommend['vod_blurb'];
                }
                $recommend['vod_pic'] = $this->getImgUrl($recommend['vod_pic']);
            }

            foreach ($type_list as &$type) {
                //分类banner-推荐7
                $child_type_ids = isset($child_types[$type['type_id']]) ? $child_types[$type['type_id']] : [];
                $where_type_ids = array_merge([$type['type_id']], $child_type_ids);

                $type_recommend_list = Db::name('vod')
                    ->where(['type_id' => ['in', $where_type_ids]])
                    ->order('vod_time desc')
                    ->field($this->init_vod_fields)
                    ->limit(30)
                    ->select();
                $type_recommend_list = $this->replaceVodPic($type_recommend_list);


                $vod_extend_class = $app_config['vod_extend_class'] ? explode(",", $app_config['vod_extend_class']) : [];
                $vod_extend_area = $app_config['vod_extend_area'] ? explode(",", $app_config['vod_extend_area']) : [];
                $vod_extend_lang = $app_config['vod_extend_lang'] ? explode(",", $app_config['vod_extend_lang']) : [];
                $vod_extend_year = $app_config['vod_extend_year'] ? explode(",", $app_config['vod_extend_year']) : [];
                if ($app_page_rank_list_type <= 0) {
                    $vod_extend_sort = ["最新", "最热", "最赞", "日榜", "周榜", "月榜"];
                } else {
                    $vod_extend_sort = ["最新", "最热", "最赞"];
                }

                $type_extends = json_decode($type['type_extend'], true);

                if ($type_extends['class'] || $type_extends['area'] || $type_extends['lang'] || $type_extends['year']) {
                    $vod_extend_class = $type_extends['class'] ? explode(",", $type_extends['class']) : [];
                    $vod_extend_area = $type_extends['area'] ? explode(",", $type_extends['area']) : [];
                    $vod_extend_lang = $type_extends['lang'] ? explode(",", $type_extends['lang']) : [];
                    $vod_extend_year = $type_extends['year'] ? explode(",", $type_extends['year']) : [];
                }

                $filter_type_list = [];

                if ($vod_extend_class) {
                    $vod_extend_class = array_merge(["全部"], $vod_extend_class);
                    $filter_type_list[] = ["name" => "class", "list" => $vod_extend_class];
                }
                if ($vod_extend_area) {
                    $vod_extend_area = array_merge(["全部"], $vod_extend_area);
                    $filter_type_list[] = ["name" => "area", "list" => $vod_extend_area];
                }
                if ($vod_extend_lang) {
                    $vod_extend_lang = array_merge(["全部"], $vod_extend_lang);
                    $filter_type_list[] = ["name" => "lang", "list" => $vod_extend_lang];
                }
                if ($vod_extend_year) {
                    $vod_extend_year = array_merge(["全部"], $vod_extend_year);
                    $filter_type_list[] = ["name" => "year", "list" => $vod_extend_year];
                }
                $filter_type_list[] = ["name" => "sort", "list" => $vod_extend_sort];

                $type['banner_list'] = [];
                $type['recommend_list'] = $type_recommend_list;
                $type['filter_type_list'] = $filter_type_list;
            }
            array_unshift($type_list, ['type_name' => '全部', 'type_id' => 0, 'recommend_list' => []]);
            $cache_time = $getapp_system_config['init_cache_time'];

            //排期表
            $week_list = $this->getInitVodWeekList();

            if ($cache_time > 0) {
                cache('getapp_init_data', compact('banner_list', 'recommend_list', 'type_list', 'week_list', 'icon_advert', 'home_advert'), $cache_time);
            }

        }


        //热门搜索
        $hot_search_list = explode(",", str_replace("，", ",", $app_config['search_hot']));

        //版本更新
        $update = $this->getUpdate();

        //置顶公告
        $notice = $this->getTopNotice();

        //user_info
        $user_info = $this->user_info;

        //页面设置
        $app_page_setting = $this->getAppPageSettingV119();

        //config
        $config = $this->getConfig();
        $config = $this->getVipConfig($config);

        //广告配置
        $system_ad_config = $this->getConfig();
        $vip_ad_config = $this->getVipAdConfig();

        //消息提醒
        $notice_count = $this->getNoticeCount();

        //过滤
        $filter_words = $app_config['filter_words'];

        return $this->setData(compact('banner_list', 'recommend_list', 'type_list', 'hot_search_list', 'update', 'notice', 'user_info', 'config', 'week_list', 'app_page_setting', 'system_ad_config', 'vip_ad_config', 'notice_count', 'icon_advert', 'home_advert', 'filter_words'));
    }

    /**
     * 分类推荐
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function typeRecommend()
    {
        $page = input('page/d', 1);
        $type_id = input('type_id/d', '');
        $type_name = '';
        $banner_list = [];

        if ($page == 1) {
            $banner_list = Db::name('vod')
                ->where(['type_id' => $type_id])
                ->where(['vod_level' => ['eq', 7]])
                ->order('vod_hits desc')
                ->field($this->simple_vod_fields)
                ->limit(10)
                ->select();
        }

        $recommend_list = Db::name('vod')->where(['type_id' => $type_id])
            ->order('vod_hits desc')
            ->field($this->simple_vod_fields)
            ->page($page, 15)
            ->select();
        $recommend_list = $this->replaceVodPic($recommend_list);
        return $this->setData(compact('type_id', 'type_name', 'banner_list', 'recommend_list'));
    }

    /**
     * 分类筛选列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function typeFilterVodList()
    {

        $page = input('page/d', 1);
        $type_id = input('type_id/d', 0);
        $class = input('class/s', '');
        $area = input('area/s', '');
        $lang = input('lang/s', '');
        $year = input('year/s', '');
        $sort = input('sort/s', '');
        $where = [
            'vod_status' => 1
        ];
        if (!empty($class) && $class != "全部") {
            $where['vod_class'] = ["like", "%$class%"];
        }
        if (!empty($area) && $area != "全部") {
            $where['vod_area'] = $area;
        }
        if (!empty($lang) && $lang != "全部") {
            $where['vod_lang'] = $lang;
        }
        if (!empty($year) && $year != "全部") {
            $where['vod_year'] = $year;
        }

        $order = "vod_time desc";
        switch ($sort) {
            case "最新":
                $order = "vod_time desc";
                break;
            case "最热":
                $order = "vod_hits desc";
                break;
            case "最赞":
                $order = "vod_score desc";
                break;
            case "日榜":
                $order = "vod_hits_day desc";
                break;
            case "周榜":
                $order = "vod_hits_week desc";
                break;
            case "月榜":
                $order = "vod_hits_month desc";
                break;
            default:
                break;
        }

        $type_name = '';

        $child_type_ids = Db::name('type')
            ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => $type_id])
            ->order('type_sort asc')
            ->column('type_id');
        $child_type_ids = $child_type_ids ?: [];
        $where_type_ids = array_merge([$type_id], $child_type_ids);

        $recommend_list = Db::name('vod')
            ->where($where)
            ->where(['type_id' => ['in', $where_type_ids]])
            ->order($order)
            ->field($this->simple_vod_fields)
            ->page($page, 30)
            ->select();
        $recommend_list = $this->replaceVodPic($recommend_list);
        return $this->setData(compact('type_id', 'type_name', 'recommend_list'));
    }

    /**
     * 详情
     * @return void
     */
    public function vodDetail()
    {

        $vod_id = input('vod_id/d', 0);
        $vod = Db::name('vod')
            ->where(['vod_id' => $vod_id])
            ->find();
        if ($vod) {
            $vod['vod_content'] = strip_tags($vod['vod_content']);
            $vod['vod_pic'] = $this->getImgUrl($vod['vod_pic']);
            Db::name('vod')
                ->where(['vod_id' => $vod_id])
                ->update([
                    'vod_hits' => $vod['vod_hits'] + 1,
                    'vod_hits_day' => $vod['vod_hits_day'] + 1,
                    'vod_hits_week' => $vod['vod_hits_week'] + 1,
                    'vod_hits_month' => $vod['vod_hits_month'] + 1,
                ]);
        }


        $comment = Db::name('comment')
            ->where(['comment_mid' => 1, 'comment_rid' => $vod['vod_id']])
            ->limit(10)
            ->select();

        $same_list = Db::name('vod')
            ->where(['type_id' => $vod['type_id']])
            ->field($this->vod_fields)
            ->limit(9)
            ->select();


        $vod_play_list = mac_play_list($vod['vod_play_from'], $vod['vod_play_url'], $vod['vod_play_server'], $vod['vod_play_note']);
        $app_vod_play_list = [];
        $getapp_player_list = config('getapp_vodplayer_parse');


        foreach ($vod_play_list as $key => &$vod_play) {
            unset($vod_play['url']);
            $vod_play_urls = array_values($vod_play['urls']);

            foreach ($getapp_player_list as $getapp_player_key => $getapp_players) {
                if ($getapp_player_key != $vod_play['from']) {
                    continue;
                }

                foreach ($getapp_players as $getapp_player) {
                    $headers = [];
                    if ($getapp_player['app_is_show']) {
                        if ($getapp_player['headers']) {
                            $lines = explode("\n", $getapp_player['headers']);
                            foreach ($lines as $line) {
                                $equals_pos = strpos($line, '=');
                                if ($equals_pos !== false) {
                                    $key = substr($line, 0, $equals_pos);
                                    $value = substr($line, $equals_pos + 1);
                                    $headers[$key] = $value;
                                }
                            }
                        }

                        $link_features = $getapp_player['link_features'] ? explode(",", $getapp_player['link_features']) : null;
                        $player_parse_type = isset($getapp_player['player_parse_type']) ? $getapp_player['player_parse_type'] : 2;
                        $parse_api = ($player_parse_type == 1 && $getapp_player['jx_type'] == 1) ? md5($getapp_player['parse_api']) : $getapp_player['parse_api'];
                        if ($getapp_player['player_parse_key']) {
                            foreach ($vod_play_urls as $key => $vod_play_url) {
                                $vod_play_urls[$key]['token'] = md5($vod_play_url['url'] . $getapp_player['player_parse_key']);
                                $vod_play_urls[$key]['parse_api_url'] = $parse_api . $vod_play_url['url'];
                            }
                        } else {
                            foreach ($vod_play_urls as $key => $vod_play_url) {
                                $vod_play_urls[$key]['token'] = "";
                                $vod_play_urls[$key]['parse_api_url'] = $parse_api . $vod_play_url['url'];
                            }
                        }


                        $app_vod_play_list[] = [
                            'player_info' => [
                                'show' => $getapp_player['player_name'],
                                'parse' => ($player_parse_type == 1 && $getapp_player['jx_type'] == 1) ? md5($getapp_player['parse_api']) : $getapp_player['parse_api'],
                                'parse_type' => $getapp_player['jx_type'],
                                'player_kernel_type' => $getapp_player['player_kernel_type'],
                                'user_agent' => $getapp_player['user_agent'],
                                'headers' => $headers ?: null,
                                'link_features' => $link_features,
                                'player_parse_type' => $player_parse_type
                            ],
                            'url_count' => $vod_play['url_count'],
                            'urls' => $vod_play_urls
                        ];
                    }

                }
            }

            $vod_play['urls'] = array_values($vod_play['urls']);
        }
        $vod_play_list = $app_vod_play_list;
        $comment_list = $this->getCommentList($vod_id, 1);
        $is_collect = $this->getIsCollect($vod_id);
        $comment_count = Db::name('comment')
            ->where([
                'comment_mid' => 1,
                'comment_status' => 1,
                'comment_rid' => $vod_id
            ])->count();
        unset($vod['vod_play_url']);
        $same_list = $this->replaceVodPic($same_list);

        $advert = $this->getAdvert(3);

        $detail_advert = $this->getAdvert(4);


        $comment_advert = $this->getAdvert(5);
        if ($advert) {
            $advert['vod_pic'] = $this->getImgUrl($advert['vod_pic']);
        }
        if ($detail_advert) {
            $detail_advert['vod_pic'] = $this->getImgUrl($detail_advert['vod_pic']);
        }
        if ($comment_advert) {
            $comment_advert['vod_pic'] = $this->getImgUrl($comment_advert['vod_pic']);
        }

        $official_comment = null;
        $system_config = $this->getConfig();
        if ($system_config['system_config_top_comment_status']) {
            $official_comment = [
                'is_official' => true,
                'user_avatar' => $system_config['system_config_top_comment_avtar'] ? $this->getImgUrl($system_config['system_config_top_comment_avtar']) : "",
                'user_name' => $system_config['system_config_top_comment_name'],
                'comment_content' => $system_config['system_config_top_comment_content'],
                'comment' => $system_config['system_config_top_comment_content'],
            ];
        }

        return $this->setData(compact('vod', 'comment', 'same_list', 'vod_play_list', 'comment_list', 'is_collect', 'comment_count', 'advert', 'detail_advert', 'comment_advert', 'official_comment'));
    }

    public function vodParse()
    {
        $url = input('url/s', '');
        $token = input('token/s', '');
        $parse_api = input('parse_api/s', '');
        $getapp_player_list = config('getapp_vodplayer_parse');

        $config = config('maccms');
        $build_config = $config['getapp_build'];
        $key = $build_config['api_secret_key'];

        $url = openssl_decrypt($url, 'AES-128-CBC', $key, false, $key);
        if (!$url) {
            return $this->setMsg();
        }
        $player_info = null;
        foreach ($getapp_player_list as $getapp_players) {
            foreach ($getapp_players as $getapp_player) {
                if (md5($getapp_player['parse_api']) == $parse_api) {
                    $player_info = $getapp_player;
                }
            }
        }
        if (!$player_info) {
            return $this->setMsg();
        }
        $parse_api = $player_info['parse_api'];
        $player_parse_key = $player_info['player_parse_key'];
        if ($player_parse_key) {
            if ($token != md5($url . $player_parse_key)) {
                return $this->setMsg();
            }
        }
        if (empty($token)) {
            $get_url = $parse_api . $url;
        } else {
            $after = substr(strrchr($parse_api, '?'), 1);
            $before = substr($parse_api, 0, strrpos($parse_api, "?"));
            $get_url = $before . "?token=" . $token . "&" . $after . $url;
        }

        $json = $this->curl($get_url);
        return $this->setData(['json' => $json], '');
    }

    private function curl($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            exit();
        }
        curl_close($ch);
        return $response;
    }


    public function danmuList()
    {
        $vod_id = input('vod_id/d', 0);
        $url_position = input('url_position/d', 0);

        $danmu_list = Db::table('getapp_vod_danmu')
            ->where([
                'status' => 1,
                'vod_id' => $vod_id,
                'url_position' => $url_position
            ])
            ->select();


        $vod = Db::name('vod')
            ->where([
                'vod_id' => $vod_id,
            ])
            ->find();
        $official_url = "";
        $danmu_url = "";
        if ($vod) {
            $system_third_danmu_url_type = $this->getConfig('system_third_danmu_url_type');
            $third_danmu_sort = $this->getConfig('third_danmu_sort');
            if ($system_third_danmu_url_type) {
                $danmu_url = "&douban_id={$vod['vod_douban_id']}&url=";
            } else {
                $third_danmu_sort = str_replace("，", ",", $third_danmu_sort);

                $vod_play_url = $vod['vod_play_url'];
                $vod_play_url = explode("$$$", $vod_play_url);
                $official_domains = ["www.bilibili.com", "v.qq.com", "www.mgtv.com", "www.iqiyi.com", "v.youku.com", "www.sohu.com", "www.letv.com", "www.pptv.com", "www.xigua.com"];
                if ($third_danmu_sort) {
                    $third_danmu_sort = explode(",", $third_danmu_sort);
                    $official_domains = array_merge($third_danmu_sort, $official_domains);
                }
                foreach ($official_domains as $official_domain) {
                    foreach ($vod_play_url as $play_url) {
                        if (stripos($play_url, $official_domain) !== false) {
                            $url = explode("#", $play_url);
                            if (isset($url[$url_position])) {
                                $url = explode("$", $url[$url_position]);
                                $official_url = $url[1];
                                break 2;
                            }
                        }
                    }
                }
                $danmu_url = "&douban_id={$vod['vod_douban_id']}&url={$official_url}";
            }


        }

        return $this->setData(compact('danmu_list', 'official_url', 'danmu_url'));
    }


    public function filterVodList()
    {

        $type_id = input('type_id/d', 0);
        $class = input('class/s', '');
        $area = input('area/s', '');
        $lang = input('lang/s', '');
        $year = input('year/s', '');
        $sort = input('sort/s', '');
        $page = input('page/d', 1);
        $where = [
            'vod_status' => 1
        ];
        if (!empty($type_id)) {
            $where['type_id'] = $type_id;
        }
        if (!empty($class) && $class != "全部") {
            $where['vod_class'] = ["like", "%$class%"];
        }
        if (!empty($area) && $area != "全部") {
            $where['vod_area'] = $area;
        }
        if (!empty($lang) && $lang != "全部") {
            $where['vod_lang'] = $lang;
        }
        if (!empty($year) && $year != "全部") {
            $where['vod_year'] = $year;
        }

        $order = "vod_id desc";
        switch ($sort) {
            case "最新":
                $order = "vod_time desc";
                break;
            case "最热":
                $order = "vod_hits desc";
                break;
            case "最赞":
                $order = "vod_score desc";
                break;
            default:
                break;
        }

        $vod_list = Db::name('vod')
            ->field($this->vod_fields)
            ->where($where)
            ->order($order)
            ->page($page, 30)
            ->select();
        $vod_list = $this->replaceVodPic($vod_list);
        return $this->setData(compact('vod_list'));
    }

    public function searchList()
    {
        $type_id = input('type_id/d', 0);
        $keywords = input('keywords/s', '');
        $page = input('page/d', 1);

        if ($this->getConfig('system_config_search_list_type') == 1) {
            $search_list = $this->xunSearch($keywords, $type_id, $page);
        } else {
            $where = [
                'vod_status' => 1
            ];
            if (!empty($type_id)) {
                $child_type_ids = Db::name('type')
                    ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => $type_id])
                    ->order('type_sort asc')
                    ->column('type_id');
                $child_type_ids = $child_type_ids ?: [];
                $where_type_ids = array_merge([$type_id], $child_type_ids);
                $where['type_id'] = ['in', $where_type_ids];
            }
            if (!empty($keywords)) {
                $mac_config = config('maccms');
                $role = 'vod_name';
                if (!empty($mac_config['app']['search_vod_rule'])) {
                    $role .= '|' . $mac_config['app']['search_vod_rule'];
                }
                $where[$role] = ['like', '%' . $keywords . '%'];
            }
            $search_list = Db::name('vod')
                ->field($this->vod_fields)
                ->where($where)
                ->page($page, 20)
                ->order('vod_hits desc')
                ->select();
        }

        $search_list = $this->replaceVodPic($search_list);
        return $this->setData(compact('search_list'));
    }

    /**
     * 评论
     * @return \think\response\Json
     */
    public function commentList()
    {
        $vod_id = input('vod_id/d', 1);
        $page = input('page/d', 1);
        $sort = input('sort/d', 1);
        $comment_list = $this->getCommentList($vod_id, $page, $sort);

        $official_comment = null;
        $system_config = $this->getConfig();
        if ($page == 1 && $system_config['system_config_top_comment_status']) {
            $official_comment = [
                'is_official' => true,
                'user_name' => $system_config['system_config_top_comment_name'],
                'comment_content' => $system_config['system_config_top_comment_content'],
                'comment' => $system_config['system_config_top_comment_content'],
                'user_avatar' => $system_config['system_config_top_comment_avatar'] ? $this->getImgUrl($system_config['system_config_top_comment_avatar']) : "",
            ];
        }

        return $this->setData(compact('comment_list', 'official_comment'));
    }


    private function getCommentList($vod_id, $page, $sort = 1)
    {
//        $comment_list = Db::name('comment')
//            ->where([
//                'comment_mid' => 1,
//                'user_id' => ['>', 0],
//                'comment_status' => 1,
//                'comment_rid' => $vod_id
//            ])
//            ->order('comment_id desc')
//            ->page($page, 20)
//            ->select();
//        if (!empty($comment_list)) {
//            $comment_user_ids = array_column($comment_list, 'user_id');
//            $users = Db::name('user')
//                ->whereIn('user_id', $comment_user_ids)
//                ->column('*', 'user_id');
//            foreach ($comment_list as $key => &$comment) {
//                if (!isset($users[$comment['user_id']])) {
//                    continue;
//                }
//                $user = $users[$comment['user_id']];
//
//                $comment_name = empty($user['user_nick_name']) ? $user['user_name'] : $user['user_nick_name'];
//                $comment_name = $this->maskPhoneNumber($comment_name);
//
//                $comment['comment'] = $comment['comment_content'];
//                $comment['user_name'] = $comment_name;
//                $comment['user_avatar'] = $this->getUserAvatar($comment['user_id']);
//                $comment['create_time'] = date("Y-m-d H:i", $comment['comment_time']);
//            }
//        }

        if ($sort == 1) {
            $order = 'comment_id desc';
        } else {
            $order = 'comment_reply desc';
        }

        $comment_list = Db::name('comment')
            ->where([
                'comment_pid' => 0,
                'comment_mid' => 1,
                'comment_status' => 1,
                'comment_rid' => $vod_id
            ])
            ->order($order)
            ->page($page, 10)
            ->select();

        $config = config('maccms');
        $filter_words = explode(",", $config['app']['filter_words']);
        if (!empty($comment_list)) {
            $comment_list_ids = array_column($comment_list, 'comment_id');
            $child_comment_list = Db::name('comment')->whereIn('comment_pid', $comment_list_ids)
                ->order('comment_id asc')
                ->column('*', 'comment_pid');
            foreach ($comment_list as $key => $comment) {
                if (isset($child_comment_list[$comment['comment_id']])) {

                    $child_comment_content = $child_comment_list[$comment['comment_id']]['comment_content'];
                    $child_comment_content = $this->filterWords($child_comment_content, $filter_words);
                    $comment_list[$key]['child_comment_content'] = $child_comment_content;
                    $comment_list[$key]['child_user_id'] = $child_comment_list[$comment['comment_id']]['user_id'];
                } else {
                    $comment_list[$key]['child_comment_content'] = '';
                    $comment_list[$key]['child_user_id'] = 0;
                }
            }


            $comment_user_ids = array_column($comment_list, 'user_id');
            $child_user_id = array_column($comment_list, 'child_user_id');
            $comment_user_ids = array_unique(array_merge($comment_user_ids, $child_user_id));

            $users = Db::name('user')
                ->whereIn('user_id', $comment_user_ids)
                ->column('*', 'user_id');
            $users_extra = Db::table('getapp_mac_user_extra')
                ->whereIn('user_id', $comment_user_ids)
                ->column('*', 'user_id');

            $users[0] = ['user_nick_name' => '游客', 'user_name' => '游客'];
            $users_extra[0] = ['avatar_update_time' => 0];


            foreach ($comment_list as $key => &$comment) {
                $user = $users[$comment['user_id']];
                $user_extra = $users_extra[$comment['user_id']];
                $child_user = $users[$comment['child_user_id']];

                $comment_name = empty($user['user_nick_name']) ? $user['user_name'] : $user['user_nick_name'];
                $child_user_name = empty($child_user['user_nick_name']) ? $child_user['user_name'] : $child_user['user_nick_name'];

                $comment_name = $this->maskPhoneNumber($comment_name);
                $child_user_name = $this->maskPhoneNumber($child_user_name);

                $comment['comment_content'] = $this->filterWords($comment['comment_content'], $filter_words);
                $comment['comment'] = $comment['comment_content'];

                $comment['user_name'] = $comment_name;
                $comment['user_is_vip'] = $this->isVip($user);
                $user['avatar_update_time'] = $user_extra['avatar_update_time'];
                $comment['user_avatar'] = $this->getUserAvatar($user);
                $comment['create_time'] = date("Y-m-d H:i", $comment['comment_time']);
                $comment['child_user_name'] = $child_user_name;

                $parse_arr = $this->parseTimeByText($comment['comment']);
                $comment['time_str'] = $parse_arr['time_str'];
                $comment['seek_to_time'] = $parse_arr['seek_to_time'];

            }
        }

        return $comment_list;
    }


    /**
     * 排行榜
     * @return void
     */
    public function rankList()
    {
        $type_id = input('type_id/d', 0);
        $page = input('page/d', 0);

        $where = [
            'vod_status' => 1
        ];
        $order = 'id desc';
        switch ($type_id) {
            case 1:
                $order = 'vod_hits_day desc';
                break;
            case 2:
                $order = 'vod_hits_week desc';
                break;
            case 3:
                $order = 'vod_hits_month desc';
                break;
            default:
                break;
        }

        $rank_list = Db::name('vod')
            ->field($this->vod_fields)
            ->where($where)
            ->order($order)
            ->page($page, 20)
            ->select();
        $rank_list = $this->replaceVodPic($rank_list);
        return $this->setData(compact('rank_list'));
    }
    /**
     * 排行榜
     * @return void
     */
    public function rankListV134()
    {
        $type_id = input('type_id/d', 0);
        $page = input('page/d', 0);

        $where = [
            'vod_status' => 1
        ];
        $order = 'id desc';

        $app_page_setting = config('getapp_all_setting')['app_page_setting'];
        $app_page_rank_list_type = $app_page_setting['app_page_rank_list_type'];

        if ($app_page_rank_list_type <= 0) {
            switch ($type_id) {
                case 1:
                    $order = 'vod_hits_day desc';
                    break;
                case 2:
                    $order = 'vod_hits_week desc';
                    break;
                case 3:
                    $order = 'vod_hits_month desc';
                    break;
                default:
                    break;
            }
        } else {
            switch ($app_page_rank_list_type) {
                case 1:
                    $order = 'vod_hits_day desc';
                    break;
                case 2:
                    $order = 'vod_hits_week desc';
                    break;
                case 3:
                    $order = 'vod_hits_month desc';
                    break;
                case 4:
                    $order = 'vod_hits desc';
                    break;
                default:
                    break;
            }

            if (!empty($type_id)) {
                $child_type_ids = Db::name('type')
                    ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => $type_id])
                    ->order('type_sort asc')
                    ->column('type_id');
                $child_type_ids = $child_type_ids ?: [];
                $where_type_ids = array_merge([$type_id], $child_type_ids);
                $where['type_id'] = ['in', $where_type_ids];
            }
        }


        $rank_list = Db::name('vod')
            ->field($this->vod_fields)
            ->where($where)
            ->order($order)
            ->page($page, 20)
            ->select();
        $rank_list = $this->replaceVodPic($rank_list);
        return $this->setData(compact('rank_list'));
    }

    /**
     * 是否收藏
     * @return bool|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getIsCollect($vod_id)
    {
        $collect = Db::name('ulog')->where([
            'user_id' => $this->user_id,
            'ulog_mid' => 1,
            'ulog_type' => 2,
            'ulog_rid' => $vod_id
        ])->find();
        return !empty($collect);
    }


    /**
     * 收藏列表
     */
    public function collectList()
    {
        $page = input('page/d', 1);


//        $collect_list = Db::table('getapp_vod_collect')
//            ->where([
//                'user_id' => $this->user_id,
//            ])
//            ->page($page, 20)
//            ->select();

        $collect_list = Db::name('ulog')
            ->where([
                'user_id' => $this->user_id,
                'ulog_mid' => 1,
                'ulog_type' => 2
            ])
            ->order('ulog_id desc')
            ->field("ulog_id as id, user_id, ulog_rid as vod_id, ulog_time as create_time")
            ->page($page, 20)
            ->select();
        if ($collect_list) {
            $vod_ids = array_column($collect_list, 'vod_id');
            $vod_list = Db::name('vod')
                ->field($this->vod_fields)
                ->where([
                    'vod_id' => ['in', $vod_ids]
                ])
                ->column($this->simple_vod_fields, 'vod_id');

            $vod_list = $this->replaceVodPic($vod_list);
            foreach ($collect_list as $key => $collect) {
                if ($vod_list[$collect['vod_id']]) {
                    $collect_list[$key]['vod'] = $vod_list[$collect['vod_id']];
                } else {
                    $collect_list[$key]['vod'] = ["vod_name" => "视频已失效", "vod_actor" => "-", "vod_blurb" => "-"];
//                    unset($collect_list[$key]);
                }
            }
        }

        return $this->setData(compact('collect_list'));
    }

    /**
     * 取消收藏
     */
    public function deleteCollect()
    {

        $vod_id = input('vod_id/d', 0);
        $where = [
            'user_id' => $this->user_id,
            'ulog_mid' => 1,
            'ulog_type' => 2
        ];
        if (!empty($vod_id)) {
            $where['ulog_rid'] = $vod_id;
        }
        $res = Db::name('ulog')->where($where)->delete();
        return $this->setMsg($res ? "取消成功" : "取消失败", 1);
    }

    /**
     * 反馈建议
     */
    public function suggest()
    {

        $content = input('content/s', '');
        $content = $this->macFilterXss($content);

        $key = input('key/s', '');
        $code = input('code/s', '');

        if (!empty($key)) {
            if (empty($code)) {
                return $this->setMsg("验证码不正确");
            }
            $cache_code = cache($key);
            if ($cache_code != $this->authcode($code)) {
                return $this->setMsg("验证码不正确");
            }
            cache($key, null);
        }

        Db::table('getapp_user_suggest')
            ->insert([
                'user_id' => $this->user_id,
                'content' => $content,
                'create_time' => $this->time
            ]);

        return $this->setMsg('提交成功', 1);
    }

    /**
     * 求片
     */
    public function find()
    {

        $name = input('name/s', '');
        $remark = input('remark/s', '');
        $name = $this->macFilterXss($name);
        $remark = $this->macFilterXss($remark);

        $key = input('key/s', '');
        $code = input('code/s', '');

        if (!empty($key)) {
            if (empty($code)) {
                return $this->setMsg("验证码不正确");
            }
            $cache_code = cache($key);
            if ($cache_code != $this->authcode($code)) {
                return $this->setMsg("验证码不正确");
            }
            cache($key, null);
        }

        Db::table('getapp_user_find')
            ->insert([
                'user_id' => $this->user_id,
                'name' => $name,
                'remark' => $remark,
                'create_time' => $this->time
            ]);
        return $this->setMsg('提交成功', 1);
    }

    /**
     * 更新
     */
    public function appUpdate()
    {
        $update = $this->getUpdate();
        if ($update) {
            return $this->setData(compact('update'));
        } else {
            return $this->setMsg('已是最新版本');
        }
    }

    public function appUpdateV2()
    {
        $update = $this->getUpdate();
        if ($update) {
            return json(['data' => $update, 'msg' => '', 'code' => 1]);;
        } else {
            return $this->setMsg('已是最新版本');
        }
    }

    private function getUpdate()
    {
        $headers = Request::instance()->header();
        $app_version_code = $headers['app-version-code'];

        $update = cache('getapp_update');
        if (empty($update)) {
            $update = Db::table('getapp_update')->order('id desc')->find();
            if (empty($update)) {
                cache('getapp_update', 'no_data');
                return null;
            }
            cache('getapp_update', $update);
        } elseif ($update == 'no_data') {
            return null;
        }

        if ($update['version_code'] > $app_version_code) {
            $update['is_force'] = $update['is_force'] == 1;
            return $update;
        } else {
            return null;
        }
    }


    /**
     * 修改密码
     */
    public function modifyPassword()
    {


        $password = input('password/s', '');
        $new_password = input('new_password/s', '');

        $password = $this->macFilterXss($password);
        $new_password = $this->macFilterXss($new_password);

        $user = Db::name('user')->where('user_id', $this->user_id)->find();

        if (md5($password) != $user['user_pwd']) {
            return $this->setMsg("旧密码错误");
        }
        if (empty($new_password)) {
            return $this->setMsg("密码格式不正确");
        }
        Db::name('user')->where('user_id', $this->user_id)->update([
            'user_pwd' => md5($new_password)
        ]);

        return $this->setMsg('修改成功', 1);


    }

    public function isCollect()
    {
        $vod_id = input('vod_id/d', 0);
        if (empty($vod_id)) {
            return $this->setMsg("参数错误");
        }
        $is_collect = $this->getIsCollect($vod_id);
        return $this->setData(compact('is_collect'));
    }

    /**
     * 收藏
     *
     */
    public function collect()
    {
        $vod_id = input('vod_id/d', 0);
        if (empty($vod_id)) {
            return $this->setMsg("参数错误");
        }

        $collect = Db::name('ulog')->where([
            'user_id' => $this->user_id,
            'ulog_mid' => 1,
            'ulog_type' => 2,
            'ulog_rid' => $vod_id
        ])->find();
        if ($collect) {
            Db::name('ulog')->where(['ulog_id' => $collect['ulog_id']])->delete();
        } else {
            Db::name('ulog')->insert([
                'user_id' => $this->user_id,
                'ulog_mid' => 1,
                'ulog_type' => 2,
                'ulog_rid' => $vod_id,
                'ulog_time' => $this->time
            ]);
        }
        return $this->setData(['msg' => $collect ? "取消成功" : "收藏成功"]);
    }

    /**
     * 发送弹幕
     * @return \think\response\Json
     */
    public function sendDanmu()
    {

        $danmu = input('danmu/s', '');
        $vod_id = input('vod_id/d', '');
        $url_position = input('url_position/d', '');
        $color = input('color/s', '#ffffff');
        $time = input('time/s', '');
        $position = input('position/d', 0);
        $danmu = $this->macFilterXss($danmu);
        $color = $this->macFilterXss($color);

        if (empty($danmu)) {
            return $this->setMsg("请输入弹幕~");
        }
        if (mb_strlen($danmu) > 30) {
            return $this->setMsg("弹幕文字过长~");
        }
        if (!$this->user_info['user_status']) {
            return $this->setMsg("用户状态异常!");
        }
        $config = config('maccms');
        $filter = $config['app']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $danmu);
            if ($filter_name != $danmu) {
                return $this->setMsg("弹幕包含非法字符");
            }
        }


        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return $this->setMsg("弹幕颜色值不正确");
        }

        $parse_arr = $this->parseTimeByText($danmu);
        $danmu_data = [
            'vod_id' => $vod_id,
            'url_position' => $url_position,
            'text' => $danmu,
            'color' => $color,
            'time' => $time,
            'status' => !$this->getConfig('system_danmu_status'),
            'create_time' => time(),
            'user_id' => $this->user_id,
            'position' => $position,
            'seek_to_time' => $parse_arr['seek_to_time'],
        ];

        $danmu_id = Db::table('getapp_vod_danmu')->insertGetId($danmu_data);
        $danmu_data['id'] = $danmu_id;
        return $this->setData([
            'msg' => $this->getConfig('system_danmu_status') ? "弹幕审核中！" : "弹幕发送成功！",
            "status" => !$this->getConfig('system_danmu_status'),
            "danmu" => $danmu_data,
        ]);
    }

    /**
     * 发送弹幕
     * @return \think\response\Json
     */
    public function sendDanmuV2()
    {
        $data = input('data/s', '');
        if (empty($data)) {
            return $this->setMsg("发送失败：参数错误");
        }

        $config = config('maccms');
        $build_config = $config['getapp_build'];
        $key = $build_config['api_secret_key'];

        $data = input('data/s', '');
        $data = openssl_decrypt($data, 'AES-128-CBC', $key, false, $key);
        $data = json_decode($data,true);
        if (empty($data)) {
            return $this->setMsg("");
        }

        $danmu = $data["danmu"];
        $vod_id = $data['vod_id'];
        $url_position = $data['url_position'];
        $color = $data['color'];
        $time = $data['time'];
        $position = isset($data['position']) ? $data['position'] : "";

        $danmu = $this->macFilterXss($danmu);
        $color = $this->macFilterXss($color);

        if (empty($danmu)) {
            return $this->setMsg("请输入弹幕~");
        }
        if (mb_strlen($danmu) > 30) {
            return $this->setMsg("弹幕文字过长~");
        }
        if (!$this->user_info['user_status']) {
            return $this->setMsg("用户状态异常!");
        }
        $config = config('maccms');
        $filter = $config['app']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $danmu);
            if ($filter_name != $danmu) {
                return $this->setMsg("弹幕包含非法字符");
            }
        }


        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return $this->setMsg("弹幕颜色值不正确");
        }

        $parse_arr = $this->parseTimeByText($danmu);
        $danmu_data = [
            'vod_id' => $vod_id,
            'url_position' => $url_position,
            'text' => $danmu,
            'color' => $color,
            'time' => $time,
            'status' => !$this->getConfig('system_danmu_status'),
            'create_time' => time(),
            'user_id' => $this->user_id,
            'position' => $position,
            'seek_to_time' => $parse_arr['seek_to_time'],
        ];

        $danmu_id = Db::table('getapp_vod_danmu')->insertGetId($danmu_data);
        $danmu_data['id'] = $danmu_id;
        return $this->setData([
            'msg' => $this->getConfig('system_danmu_status') ? "弹幕审核中！" : "弹幕发送成功！",
            "status" => !$this->getConfig('system_danmu_status'),
            "danmu" => $danmu_data,
        ]);
    }



    /**
     * 发送评论
     * @return \think\response\Json
     */
    public function sendComment()
    {

        $config = config('maccms');
        $comment_config = $config['comment'];
        $comment_text = input('comment/s', '');
        $vod_id = input('vod_id/d', 0);
        $reply_comment_id = input('comment_id/d', 0);
        $comment_text = $this->macFilterXss($comment_text);
        if (!$comment_config['status']) {
            return $this->setMsg('评论未开启');
        }
        if (empty($comment_text)) {
            return $this->setMsg('评论不能为空');
        }
        if (!$this->user_info['user_status']) {
            return $this->setMsg("用户状态异常");
        }
        if (mb_strlen($comment_text) > 200) {
            return $this->setMsg("评论文字过长");
        }

        $filter = $config['app']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $comment_text);
            if ($filter_name != $comment_text) {
                return $this->setMsg("评论包含非法字符");
            }
        }

        $comment_status = $comment_config['audit'] == 1 ? 0 : 1;
        $comment_name = $this->user_info['user_name'];
        if (!empty($this->user_info['user_nick_name'])) {
            $comment_name = $this->user_info['user_nick_name'];
        }
        $comment = [
            'comment_mid' => 1,
            'comment_rid' => intval($vod_id),
            'user_id' => $this->user_id,
            'comment_time' => $this->time,
            'comment_content' => $this->filterWords($comment_text),
            'comment_name' => $comment_name,
            'comment_status' => $comment_status,
            'comment_pid' => $reply_comment_id,
            'comment_ip' => $this->mac_get_ip_long()
        ];

        $comment_id = Db::name('comment')->insertGetId($comment);

        $is_reply = false;
        if ($reply_comment_id > 0) {
            $is_reply = true;
            Db::name('comment')->where(['comment_id' => $reply_comment_id])->setInc('comment_reply');

        }

        $parse_arr = $this->parseTimeByText($comment_text);

        $comment['comment_id'] = $comment_id;
        $comment['create_time'] = date("Y-m-d H:i");
        $comment['user_name'] = $this->maskPhoneNumber($comment_name);
        $comment['user_avatar'] = $this->user_info['user_avatar'];
        $comment['comment'] = $comment_text;
        $comment['is_reply'] = $is_reply;
        $comment['user_is_vip'] = $this->user_info['is_vip'];
        $comment['time_str'] = $parse_arr['time_str'];
        $comment['seek_to_time'] = $parse_arr['seek_to_time'];

        return $this->setData(['msg' => $comment_status ? "评论发送成功！" : "评论审核中！", "status" => !empty($comment_status), 'comment' => $comment]);


    }

    public function childrenCommentList()
    {
        $reply_comment_id = input('comment_id/d', 0);
        $page = input('page/d', 1);
        $parent_comment = Db::name('comment')->where(['comment_id' => $reply_comment_id])->find();
        if (empty($parent_comment)) {
            return $this->setMsg('评论不存在');
        }
        $children_comment_list = Db::name('comment')
            ->where([
                'comment_mid' => 1,
                'comment_pid' => $reply_comment_id,
                'comment_status' => 1
            ])
            ->order('comment_id desc')
            ->page($page, 10)
            ->select();

        if ($page == 1) {
            array_unshift($children_comment_list, $parent_comment);
        }

        if (!empty($children_comment_list)) {
            $comment_user_ids = array_column($children_comment_list, 'user_id');
            $users = Db::name('user')
                ->whereIn('user_id', $comment_user_ids)
                ->column('*', 'user_id');

            $users[0] = ['user_nick_name' => '游客', 'user_name' => '游客'];
            $users_extra[0] = ['avatar_update_time' => 0];

            foreach ($children_comment_list as $key => &$comment) {
                $user = $users[$comment['user_id']];
                $user_extra = $users_extra[$comment['user_id']];
                $child_user = $users[$comment['child_user_id']];

                $comment_name = empty($user['user_nick_name']) ? $user['user_name'] : $user['user_nick_name'];
                $child_user_name = empty($child_user['user_nick_name']) ? $child_user['user_name'] : $child_user['user_nick_name'];

                $comment_name = $this->maskPhoneNumber($comment_name);
                $child_user_name = $this->maskPhoneNumber($child_user_name);
                $comment['comment'] = $comment['comment_content'];
                $comment['user_name'] = $comment_name;
                $comment['user_is_vip'] = $this->isVip($user);
                $user['avatar_update_time'] = $user_extra['avatar_update_time'];
                $comment['user_avatar'] = $this->getUserAvatar($user);
                $comment['create_time'] = date("Y-m-d H:i", $comment['comment_time']);
                $comment['child_user_name'] = $child_user_name;
                $parse_arr = $this->parseTimeByText($comment['comment']);
                $comment['time_str'] = $parse_arr['time_str'];
                $comment['seek_to_time'] = $parse_arr['seek_to_time'];
            }
        }
        return $this->setData(compact('children_comment_list'));
    }

    public function requestUpdate()
    {
        $vod_id = input('vod_id/d', 0);
        $update = Db::table('getapp_request_update')
            ->where(['vod_id' => $vod_id, 'user_id' => $this->user_id])
            ->find();
        if ($update) {
            Db::table('getapp_request_update')
                ->where(['id' => $update['id']])
                ->update(['update_time' => time(), 'times' => $update['times'] + 1]);
        } else {
            Db::table('getapp_request_update')
                ->insert([
                    'user_id' => $this->user_id,
                    'vod_id' => $vod_id,
                    'update_time' => time(),
                    'times' => 1
                ]);
        }
        return $this->setMsg("催更成功");
    }

    /**
     * 公告列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function noticeList()
    {
        $page = input('page/d', 1);

        $top_notice_ids = [];
        if ($page == 1) {
            $top_notice = Db::table('getapp_notice')
                ->where(['is_top' => 1, 'status' => 1])
                ->field('content', true)
                ->order('sort desc')
                ->select();
            $top_notice_ids = array_column($top_notice, 'id');
        }
        $where = ['is_top' => 0, 'status' => 1];
        if ($top_notice_ids) {
            $where['id'] = ['not in', $top_notice_ids];
        }
        $notice_list = Db::table('getapp_notice')
            ->where($where)
            ->field('content', true)
            ->order('sort desc')
            ->page($page, 15)
            ->select();
        if ($page == 1 && !empty($top_notice)) {
            $notice_list = array_merge($top_notice, $notice_list);
        }
        foreach ($notice_list as &$notice) {
            $notice['create_time'] = date("Y-m-d H:i", $notice['create_time']);
        }

        return $this->setData(compact('notice_list'));
    }

    /**
     * 公告详情
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function noticeDetail()
    {
        $notice_id = input('notice_id/d', 0);
        if (empty($notice_id)) {
            return $this->error("参数错误");
        }
        $notice = Db::table('getapp_notice')
            ->where(['id' => $notice_id, 'status' => 1])
            ->find();
        if (empty($notice)) {
            return $this->error("参数错误");
        }

        $notice['create_time'] = date("Y-m-d H:i", $notice['create_time']);

        return $this->setData(compact('notice'));
    }

    private function getTopNotice()
    {
        $top_notice = cache('getapp_notice');
        if (empty($top_notice)) {
            $top_notice = Db::table('getapp_notice')
                ->where(['is_top' => 1, 'status' => 1])
                ->order('sort desc')
                ->find();
            if (!empty($top_notice)) {
                $top_notice['create_time'] = date("Y-m-d H:i", $top_notice['create_time']);
                cache('getapp_notice', $top_notice);
            } else {
                cache('getapp_notice', 'no_data');
                return null;
            }
        } elseif ($top_notice == 'no_data') {
            return null;
        }
        return $top_notice;
    }


    /**
     * 和网站统一注册
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function appRegister()
    {
        $config = config('maccms');
        $user_config = $config['user'];
        if ($user_config['status'] == 0 || $user_config['reg_open'] == 0) {
            return $this->setMsg("暂未开放注册");
        }

        $user_name = input('user_name/s', '');
        $password = input('password/s', '');
        $key = input('key/s', '');
        $code = input('code/s', '');
        $invite_code = input('invite_code/s', '');
        $user_name = $this->macFilterXss($user_name);
        $password = $this->macFilterXss($password);

        if (empty($user_name) || empty($password)) {
            return $this->setMsg("注册失败：参数错误");
        }
        if (mb_strlen($user_name) < 6) {
            return $this->setMsg("注册失败：用户名最少为6位字符");
        }

        $filter = $config['user']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $user_name);
            if ($filter_name != $user_name) {
                return $this->setMsg("注册失败：账号包含非法字符");
            }
        }

        $row = Db::name('user')->where('user_name', $user_name)->find();
        if (!empty($row)) {
            return $this->setMsg(lang('model/user/haved_reg'));
        }

        $ip = $this->mac_get_ip_long();
        $limit_reg_num = $config['user']['reg_num'];
        if ($limit_reg_num > 0) {
            $today = strtotime(date("Y-m-d"));
            $reg_times = Db::name('user')->where(['user_reg_ip' => $ip, 'user_reg_time' => ['>', $today]])->count();
            if ($reg_times >= $limit_reg_num) {
                return $this->setMsg("注册失败：每个IP每日限制注册{$limit_reg_num}次");
            }

        }

        if (intval($user_config['reg_verify']) && !empty($key)) {
            if (empty($code)) {
                return $this->setMsg("注册失败：验证码不正确");
            }
            $cache_code = cache($key);
            if ($cache_code != $this->authcode($code)) {
                return $this->setMsg("注册失败：验证码不正确");
            }
            cache($key, null);
        }


        $user = [];
        $user['user_name'] = $user_name;
        $user['user_pwd'] = md5($password);
        $user['group_id'] = 2;
        $user['user_points'] = intval($user_config['reg_points']);
        $user['user_status'] = intval($user_config['reg_status']);
        $user['user_reg_time'] = $this->time;
        $user['user_reg_ip'] = $ip;
        $user['user_login_time'] = $this->time;
        $user['user_login_ip'] = $ip;
        $user_id = Db::name('user')->insertGetId($user);

        $auth_token = md5($user_name . $this->time) . md5($this->time);
        $invite_code = $this->createInviteCode();
        Db::table('getapp_mac_user_extra')
            ->insert([
                'user_id' => $user_id,
                'auth_token' => $auth_token,
                'invite_code' => $invite_code
            ]);
        $user['id'] = $user_id;
        $user['status'] = $user['user_status'];
        $user['user_avatar'] = $this->getFullUrl("");
        $user['auth_token'] = $auth_token;
        $user['is_vip'] = $this->isVip($user);
        $user['user_nick_name'] = $user_name;
        $user['invite_code'] = $invite_code;

        return $this->setData(compact('user'));
    }

    public function appRegisterV133()
    {
        $config = config('maccms');
        $user_config = $config['user'];
        if ($user_config['status'] == 0 || $user_config['reg_open'] == 0) {
            return $this->setMsg("注册失败：暂未开放注册");
        }

        $data = input('data/s', '');
        if (empty($data)) {
            return $this->setMsg("注册失败：参数错误");
        }

        $build_config = $config['getapp_build'];
        $key = $build_config['api_secret_key'];

        $data = openssl_decrypt($data, 'AES-128-CBC', $key, false, $key);

        $data = json_decode($data, true);
        if (empty($data)) {
            return $this->setMsg("注册失败：参数错误");
        }
        $user_name = $data['user_name'];
        $password = $data['password'];
        $key = $data['key'];
        $code = $data['code'];
        $invite_code = $data['invite_code'];
        $device_id = $data['device_id'];

        $user_name = $this->macFilterXss($user_name);
        $password = $this->macFilterXss($password);

        if (empty($user_name) || empty($password)) {
            return $this->setMsg("注册失败：参数错误");
        }
        if (mb_strlen($user_name) < 6) {
            return $this->setMsg("注册失败：用户名最少为6位字符");
        }

        $filter = $config['user']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $user_name);
            if ($filter_name != $user_name) {
                return $this->setMsg("账号包含非法字符");
            }
        }

        $row = Db::name('user')->where('user_name', $user_name)->find();
        if (!empty($row)) {
            return $this->setMsg(lang('model/user/haved_reg'));
        }


        if (intval($user_config['reg_verify']) && !empty($key)) {
            if (empty($code)) {
                return $this->setMsg("验证码不正确");
            }
            $cache_code = cache($key);
            if ($cache_code != $this->authcode($code)) {
                return $this->setMsg("验证码不正确");
            }
            cache($key, null);
        }

        $ip = $this->mac_get_ip_long();
        $limit_reg_num = $config['user']['reg_num'];
        if ($limit_reg_num > 0) {
            $today = strtotime(date("Y-m-d"));
            $reg_times = Db::name('user')->where(['user_reg_ip' => $ip, 'user_reg_time' => ['>', $today]])->count();
            if ($reg_times >= $limit_reg_num) {
                return $this->setMsg("注册失败：每个IP每日限制注册{$limit_reg_num}次");
            }

        }




        $user = [];
        $user['user_name'] = $user_name;
        $user['user_pwd'] = md5($password);
        $user['group_id'] = 2;
        $user['user_points'] = intval($user_config['reg_points']);
        $user['user_status'] = intval($user_config['reg_status']);
        $user['user_reg_time'] = $this->time;
        $user['user_reg_ip'] = $ip;
        $user['user_login_time'] = $this->time;
        $user['user_login_ip'] = $ip;
        $user_id = Db::name('user')->insertGetId($user);

        if ($user_config['reg_points'] > 0) {
            Db::name('plog')->insert([
                'user_id' => $user_id,
                'plog_type' => 2,
                'plog_points' => $user_config['reg_points'],
                'plog_time' => $this->time,
                'plog_remarks' => '注册赠送'
            ]);
        }

        $auth_token = md5($user_name . $this->time) . md5($this->time);
        $user_invite_code = $this->createInviteCode();
        Db::table('getapp_mac_user_extra')
            ->insert([
                'user_id' => $user_id,
                'auth_token' => $auth_token,
                'invite_code' => $user_invite_code
            ]);
        $user['id'] = $user_id;
        $user['status'] = $user['user_status'];
        $user['user_avatar'] = $this->getFullUrl("");
        $user['auth_token'] = $auth_token;
        $user['is_vip'] = $this->isVip($user);
        $user['user_nick_name'] = $user_name;
        $user['invite_code'] = $user_invite_code;

        $has_invite = false;
        if ($device_id && $invite_code) {
            try {
                $res = $this->doVerifyInviteCode($device_id, $invite_code);
                $has_invite = $res;
            } catch (\Exception $e) {

            }

        }
        return $this->setData(compact('user', 'has_invite'));
    }

    /**
     * 图片上传
     */
    public function appAvatarUpload()
    {
        if ($GLOBALS['config']['user']['portrait_status'] == 0) {
            return $this->setMsg(lang('index/portrait_tip1'));
        }

        $data = [
            'flag' => 'user',
            'user_id' => $this->user_id,
            'input' => 'avatar'
        ];
        $res = model('Upload')->upload($data);
        if (!$res['code']) {
            return $this->setMsg($res['msg']);
        }
        $user_portrait = $res['data']['file'];
        if (empty($user_portrait)) {
            return $this->setMsg("上传失败");
        }
        Db::name('user')->where(['user_id' => $this->user_id])->update(['user_portrait' => $user_portrait]);
        Db::table('getapp_mac_user_extra')->where(['user_id' => $this->user_id])->update(['avatar_update_time' => $this->time]);
        $this->user_info['avatar_update_time'] = $this->time;
        $this->user_info['user_avatar'] = $this->getUserAvatar($this->user_info);
        return $this->setData(['user' => $this->user_info], '修改成功');
    }

    /**
     * 登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function appLogin()
    {
        $config = config('maccms');
        $user_config = $config['user'];
        $user_name = input('user_name/s', '');
        $password = input('password/s', '');
        $key = input('key/s', '');
        $code = input('code/s', '');
        if (empty($user_name) || empty($password)) {
            return $this->setMsg("参数错误");
        }
        $user = Db::name('user')
            ->where(['user_name' => $user_name])
            ->find();
        if (!$user || md5($password) != $user['user_pwd']) {
            return $this->setMsg("用户或密码错误");
        }
        if (!$user['user_status']) {
            return $this->setMsg("用户状态异常!");
        }

        if (intval($user_config['login_verify']) && !empty($key)) {
            if (empty($code)) {
                return $this->setMsg("验证码不正确");
            }
            $cache_code = cache($key);
            if ($cache_code != $this->authcode($code)) {
                return $this->setMsg("验证码不正确");
            }
            cache($key, null);
        }

        $user_extra = Db::table('getapp_mac_user_extra')
            ->where(['user_id' => $user['user_id']])
            ->find();
        $auth_token = md5($user_name . $this->time) . md5($this->time);
        $user['auth_token'] = $auth_token;
        if ($user_extra) {
            Db::table('getapp_mac_user_extra')
                ->where(['user_id' => $user['user_id']])
                ->update([
                    'auth_token' => $auth_token
                ]);
        } else {
            Db::table('getapp_mac_user_extra')
                ->insert([
                    'user_id' => $user['user_id'],
                    'auth_token' => $auth_token
                ]);
        }
        Db::name('user')->where(['user_id' => $user['user_id']])->update(['user_login_time' => $this->time]);
        $user['is_vip'] = $this->isVip($user);
        $user['avatar_update_time'] = $user_extra['avatar_update_time'];
        $user['user_avatar'] = $this->getUserAvatar($user);
        $user['id'] = $user['user_id'];
        $user['status'] = $user['user_status'];
        $user['create_time'] = $user['user_reg_time'];
        $user['vip_days'] = $this->getVipDays($user);
        return $this->setData(compact('user'));
    }

    private function getAppPageSetting()
    {
        $app_page_setting = config('getapp_all_setting')['app_page_setting'];
        $mac_config = config('maccms');
        $app_tab_setting_list = [];
        if ($app_page_setting['app_tab_rank'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 0,
                'sort' => (int)$app_page_setting['app_tab_rank_sort'],
                'name' => $app_page_setting['app_tab_rank_name']
            ];
        }
        if ($app_page_setting['app_tab_week'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 1,
                'sort' => (int)$app_page_setting['app_tab_week_sort'],
                'name' => $app_page_setting['app_tab_week_name']
            ];
        }

        if ($app_page_setting['app_tab_find'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 2,
                'sort' => (int)$app_page_setting['app_tab_find_sort'],
                'name' => $app_page_setting['app_tab_find_name'],
                'url' => $app_page_setting['app_tab_find_url'],
            ];
        }

        if (!empty($app_tab_setting_list)) {
            $sort = array_column($app_tab_setting_list, 'sort');
            array_multisort($sort, SORT_ASC, $app_tab_setting_list);
        }

        //搜索提示
        $mac_config = config('maccms');
        $search_vod_rule = ["搜索名称"];
        if (!empty($mac_config['app']['search_vod_rule'])) {
            $search_vod_extra_rule = explode("|", $mac_config['app']['search_vod_rule']);
            if (in_array("vod_sub", $search_vod_extra_rule)) {
                $search_vod_rule[] = "简介";
            }
            if (in_array("vod_tag", $search_vod_extra_rule)) {
                $search_vod_rule[] = "标签";
            }
            if (in_array("vod_actor", $search_vod_extra_rule)) {
                $search_vod_rule[] = "演员";
            }
            if (in_array("vod_director", $search_vod_extra_rule)) {
                $search_vod_rule[] = "导演";
            }
        }
        $search_vod_rule = implode(" ", $search_vod_rule);
        $app_page_setting['app_vod_source_type'] = intval($app_page_setting['app_vod_source_type']);
        $app_page_setting['app_page_version_hide'] = boolval(intval($app_page_setting['app_page_version_hide']));
        $app_page_setting['app_page_vod_detail_pic_hide'] = boolval(intval($app_page_setting['app_page_vod_detail_pic_hide']));
        $app_page_setting['app_page_mine_bg_hide'] = boolval(intval($app_page_setting['app_page_mine_bg_hide']));
        $app_page_setting['app_page_homepage_indicator'] = boolval(intval($app_page_setting['app_page_homepage_indicator']));

        return compact('app_page_setting', 'app_tab_setting_list', 'search_vod_rule');
    }

    private function getAppPageSettingV119()
    {
        $app_page_setting = config('getapp_all_setting')['app_page_setting'];
        $app_tab_setting_list = [];
        if ($app_page_setting['app_tab_rank'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 0,
                'sort' => (int)$app_page_setting['app_tab_rank_sort'],
                'name' => $app_page_setting['app_tab_rank_name']
            ];
        }
        if ($app_page_setting['app_tab_week'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 1,
                'sort' => (int)$app_page_setting['app_tab_week_sort'],
                'name' => $app_page_setting['app_tab_week_name']
            ];
        }

        if ($app_page_setting['app_tab_find'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 2,
                'sort' => (int)$app_page_setting['app_tab_find_sort'],
                'name' => $app_page_setting['app_tab_find_name'],
                'url' => $app_page_setting['app_tab_find_url'],
            ];
        }

        if ($app_page_setting['app_tab_topic'] == 1) {
            $app_tab_setting_list[] = [
                'type' => 3,
                'sort' => (int)$app_page_setting['app_tab_topic_sort'],
                'name' => $app_page_setting['app_tab_topic_name'],
            ];
        }

        if (!empty($app_tab_setting_list)) {
            $sort = array_column($app_tab_setting_list, 'sort');
            $type = array_column($app_tab_setting_list, 'type');
            array_multisort($sort, SORT_ASC, $type, SORT_ASC, $app_tab_setting_list);
        }

        //搜索提示
        $mac_config = config('maccms');
        $search_vod_rule = ["搜索名称"];
        if (!empty($mac_config['app']['search_vod_rule'])) {
            $search_vod_extra_rule = explode("|", $mac_config['app']['search_vod_rule']);
            if (in_array("vod_sub", $search_vod_extra_rule)) {
                $search_vod_rule[] = "简介";
            }
            if (in_array("vod_tag", $search_vod_extra_rule)) {
                $search_vod_rule[] = "标签";
            }
            if (in_array("vod_actor", $search_vod_extra_rule)) {
                $search_vod_rule[] = "演员";
            }
            if (in_array("vod_director", $search_vod_extra_rule)) {
                $search_vod_rule[] = "导演";
            }
        }
        $search_vod_rule = implode(" ", $search_vod_rule);


        $app_page_setting['app_tab_setting_list'] = $app_tab_setting_list;
        $app_page_setting['search_vod_rule'] = $search_vod_rule;
//        $app_page_setting['app_vod_source_type'] = intval($app_page_setting['app_vod_source_type']);
        $app_page_setting['app_login_verify'] = boolval(intval($mac_config['user']['login_verify']));
        $app_page_setting['app_register_verify'] = boolval(intval($mac_config['user']['reg_verify']));
        return $app_page_setting;
    }

    /**
     * 排期表
     * @return void
     */
    public function vodWeekList()
    {
        $week = input('week/d', 1);
        $page = input('page/d', 1);

        $week_list = $this->getVodWeekList($week, $page);
        return $this->setData(compact('week_list'));
    }

    private function getInitVodWeekList()
    {
        $week_list = [];
        // 星期几名称数组
        $weekdays = ['一', '二', '三', '四', '五', '六', '日'];
        for ($week = 1; $week <= 7; $week++) {
            $week_list[] = [
                'week_name' => $weekdays[$week - 1],
                'week_num' => $week,
                'week_list' => []
            ];
        }
        return $week_list;
    }

    private function getVodWeekList($week = 1, $page = 1)
    {
        $weekdays = ['一', '二', '三', '四', '五', '六', '日'];
        $where = [
            'vod_status' => 1,
        ];

        $cn_week = $weekdays[$week - 1];
        $order = 'vod_time desc';
        $week_list = Db::name('vod')
            ->field($this->vod_fields)
            ->where($where)
            ->where(function ($query) use ($cn_week, $week) {
                $query->where('vod_weekday', 'like', "%{$week}%")->whereor('vod_weekday', 'like', "%{$cn_week}%");
            })
            ->order($order)
            ->page($page, 30)
            ->select();
        return $this->replaceVodPic($week_list);
    }


    public function userInfo()
    {
        $user_info = $this->user_info;
        return $this->setData(compact('user_info'));
    }

    public function mineInfo()
    {
        $user = $this->user_info;
        $user_notice_unread_count = 0;
        if ($this->user_id) {
            $user_notice_unread_count = Db::table('getapp_user_notice')
                ->where([
                    'user_id' => $this->user_id,
                    'is_read' => 0
                ])
                ->count();
        }

        return $this->setData(compact('user', 'user_notice_unread_count'));
    }

    public function topicList()
    {
        $page = input('page/d', 1);

        $topic_list = $this->getTopicList($page);

        return $this->setData(compact('topic_list'));
    }

    private function getTopicList($page = 1)
    {
        $topic_list = Db::name('topic')
            ->where(['topic_status' => 1])
            ->page($page, 10)
            ->order('topic_time desc')
            ->field('topic_id, topic_name, topic_pic, topic_blurb, topic_rel_vod')
            ->select();
        $all_topic_rel_vod_ids = [];
        if (!empty($topic_list)) {
            foreach ($topic_list as $topic) {
                $topic_rel_vod_ids = explode(",", $topic['topic_rel_vod']);
                $all_topic_rel_vod_ids = array_merge($all_topic_rel_vod_ids, $topic_rel_vod_ids);
            }
            $vod_names = [];
            if ($all_topic_rel_vod_ids) {
                $vod_names = Db::name('vod')
                    ->whereIn('vod_id', array_unique($all_topic_rel_vod_ids))
                    ->where(['vod_status' => 1])
                    ->order('vod_hits desc')
                    ->column('vod_name', 'vod_id');
            }

            foreach ($topic_list as &$topic) {
                $topic_vod_names = [];
                $topic_rel_vod_ids = explode(",", $topic['topic_rel_vod']);
                foreach ($topic_rel_vod_ids as $vod_id) {
                    if (isset($vod_names[$vod_id])) {
                        $topic_vod_names[] = "《" . $vod_names[$vod_id] . "》";
                    }

                }
                $topic['topic_pic'] = $this->getImgUrl($topic['topic_pic']);
                $topic['topic_vod_names'] = implode(",", $topic_vod_names);
            }
        }

        return $topic_list;
    }

    public function topicVodList()
    {
        $page = input('page/d', 1);
        $topic_id = input('topic_id/d', 1);

        $topic_vod_list = [];
        $topic = Db::name('topic')
            ->where('topic_id', $topic_id)
            ->field('topic_id, topic_name, topic_pic, topic_blurb, topic_rel_vod')
            ->find();
        if (!$topic) {
            return $this->setData(compact('topic_vod_list'));
        }

        $topic_rel_vod_ids = explode(",", $topic['topic_rel_vod']);
        if (!$topic_rel_vod_ids) {
            return $this->setData(compact('topic_vod_list'));
        }
        $topic_vod_list = Db::name('vod')
            ->whereIn('vod_id', $topic_rel_vod_ids)
            ->where(['vod_status' => 1])
            ->field($this->vod_fields)
            ->page($page, 10)
            ->order('vod_hits desc')
            ->select();
        $topic_vod_list = $this->replaceVodPic($topic_vod_list);
        return $this->setData(compact('topic_vod_list'));
    }

    public function modifyUserNickName()
    {
        $user_nick_name = input('user_nick_name/s', '');
        $user_nick_name = $this->macFilterXss($user_nick_name);
        $user_nick_name_len = strlen($user_nick_name);
        if ($user_nick_name_len < 2) {
            return $this->setMsg("昵称应大于2位字符");
        }
        if ($user_nick_name_len > 18) {
            return $this->setMsg("昵称应小于18位字符");
        }
        $config = config('maccms');
        $filter = $config['user']['filter_words'];
        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
            $filter_name = str_replace($filter_arr, '', $user_nick_name);
            if ($filter_name != $user_nick_name) {
                return $this->setMsg("昵称包含非法字符");
            }
        }

        Db::name('user')->where('user_id', $this->user_id)->update(['user_nick_name' => $user_nick_name]);
        $user = $this->user_info;
        $user['user_nick_name'] = $user_nick_name;
        return $this->setData(compact('user'));
    }

    public function userNoticeList()
    {
        $page = input('page/d', 1);
        $type = input('type/d', 1);
        $user_notice_list = Db::table('getapp_user_notice')
            ->where([
                'user_id' => $this->user_id,
                'from_type' => $type
            ])
            ->order('id desc')
            ->page($page, 10)
            ->select();
        $user_notice_unread_count = 0;
        if (!empty($user_notice_list)) {
            $from_id = array_column($user_notice_list, 'from_id');
            $ids = array_column($user_notice_list, 'id');
            if ($type == 1) {
                $infos = Db::table('getapp_user_suggest')
                    ->whereIn('id', $from_id)
                    ->column("*", 'id');
            } else {
                $infos = Db::table('getapp_user_find')
                    ->whereIn('id', $from_id)
                    ->column("*", 'id');
            }
            foreach ($user_notice_list as &$notice) {
                $notice['reply_content'] = "官方回复：" . $notice['reply_content'];
                $from_id = $notice['from_id'];
                if ($type == 1) {
                    $notice['content'] = isset($infos[$from_id]) ? "反馈内容：" . $infos[$from_id]['content'] : '';
                } else {
                    $notice['title'] = isset($infos[$from_id]) ? "片名：" . $infos[$from_id]['name'] : '';
                    $notice['content'] = isset($infos[$from_id]) ? "备注：" . $infos[$from_id]['remark'] : '';

                }
            }
            Db::table('getapp_user_notice')
                ->whereIn('id', $ids)
                ->update(['is_read' => 1]);

            $user_notice_unread_count = Db::table('getapp_user_notice')
                ->where([
                    'user_id' => $this->user_id,
                    'is_read' => 0
                ])
                ->count();
        }

        return $this->setData(compact('user_notice_list', 'user_notice_unread_count'));
    }

    public function userNoticeType()
    {
        $suggest_count = Db::table('getapp_user_notice')
            ->where([
                'user_id' => $this->user_id,
                'from_type' => 1,
                'is_read' => 0,
            ])->count();

        $find_count = Db::table('getapp_user_notice')
            ->where([
                'user_id' => $this->user_id,
                'from_type' => 2,
                'is_read' => 0,
            ])->count();

        return $this->setData(compact('suggest_count', 'find_count'));
    }

    /**
     * 评论举报
     */
    public function commentTipOff()
    {
        $comment_id = input('comment_id/d', 0);
        Db::name('comment')->where(['comment_id' => $comment_id])->setInc('comment_report');
        return $this->setMsg("举报成功", 1);
    }

    /**
     * 弹幕举报
     */
    public function danmuReport()
    {
        $danmu_id = input('danmu_id/d', 0);
        if (empty($danmu_id)) {
            $report_content = input('danmu_content/s', '');
            $vod_id = input('vod_id/s', '');
            $url_position = input('url_position/s', '');
            $create_time = $this->time;
            $report_type = 1;
            $report_content = $this->macFilterXss($report_content);
            Db::table('getapp_vod_third_report')->insert(compact('report_type', 'report_content', 'url_position', 'vod_id', 'create_time'));
        } else {
            Db::table('getapp_vod_danmu')->where(['id' => $danmu_id])->setInc('report_times');
        }
        return $this->setData(['msg' => '举报成功']);
    }


    /**
     * xunsearch
     */
    private function xunSearch($keywords, $type_id, $page)
    {
        $limit = 20;

        if (!file_exists($this->xs_path)) {
            return [];
        }
        require_once $this->xs_path;
        $xs = new \XS($this->xs_ini_path);
        $search = $xs->search;

        $query = "";
        if ($type_id > 0) {
            $query .= 'type_id:' . $type_id . ' ';
        }
        $query .= $keywords;
        $docs = $search
            ->setFuzzy(true)
            ->setQuery($query)
            ->setLimit($limit, ($page - 1) * $limit)
//            ->setSort('vod_hits')
            ->search();

        $data = [];
        foreach ($docs as $doc) {
            $data[] = [
                'vod_id' => $doc->vod_id ?: '',
                'vod_name' => $doc->vod_name ?: '',
                'vod_en' => $doc->vod_en ?: '',
                'vod_blurb' => $doc->vod_blurb ?: '',
                'vod_actor' => $doc->vod_actor ?: '',
                'vod_hits' => $doc->vod_hits ?: '',
                'vod_pic' => $doc->vod_pic ?: '',
                'vod_pic_slide' => $doc->vod_pic_slide ?: '',
                'vod_remarks' => $doc->vod_remarks ?: '',
                'vod_class' => $doc->vod_class ?: '',
                'vod_score' => $doc->vod_score ?: '',
                'vod_year' => $doc->vod_year ?: '',
                'vod_lang' => $doc->vod_lang ?: '',
                'vod_area' => $doc->vod_area ?: '',
            ];
        }
        return $data;
    }

    /**
     * 邀请码
     * @return \think\response\Json
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function verifyInviteCode()
    {
//        $data = input('data/s', '');
//        if (empty($data)) {
//            return $this->setMsg("");
//        }
//        $config = config('maccms');
//
//
//        $build_config = $config['getapp_build'];
//        $key = $build_config['api_secret_key'];
//
//        $data = openssl_decrypt($data, 'AES-128-CBC', $key, false, $key);
//        $data = json_decode($data, true);
//        if (empty($data)) {
//            return $this->setMsg("");
//        }
//
//        $device_id = $data['device_id'];
//        $invite_code = $data['invite_code'];
//
//        if (!$device_id || !$invite_code) {
//            return $this->setMsg("");
//        }
//        $this->doVerifyInviteCode($device_id, $invite_code);
//        return $this->setMsg("", 1);

    }

    /**
     * 邀请记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function inviteLogs()
    {
        $page = input('page/d', 1);
        $invite_logs = Db::table('getapp_invite_logs')->where([
            'from_user_id' => $this->user_id
        ])
            ->page($page, 20)
            ->order('id desc')
            ->select();
        foreach ($invite_logs as &$invite_log) {
            $invite_log['device_id'] = substr($invite_log['device_id'], 0, 3) . "***" . substr($invite_log['device_id'], -3);
            $invite_log['content'] = "邀请用户：" . $invite_log['device_id'] . "注册";
        }

        $invite_count = $this->user_info['invite_count'];
        $intro = "";
        if ($page == 1) {
            $config = config('maccms');
            $user_config = $config['user'];
            $invite_reg_points = $user_config['invite_reg_points'];
            $limit_invite_reg_num = $user_config['reg_num'];
            $intro = "1.同一设备仅算作一名用户\n\n2.每邀请一名用户注册，奖励{$invite_reg_points}积分\n\n3.每个ip每天最多注册{$limit_invite_reg_num}次";
        }

        return $this->setData(compact('invite_logs', 'invite_count', 'intro'));
    }

    /**
     * 积分记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userPointsLogs()
    {
        $page = input('page/d', 1);
        $plogs = Db::name('plog')->where([
            'user_id' => $this->user_id
        ])
            ->order('plog_id desc')
            ->page($page, 20)
            ->select();
        foreach ($plogs as &$plog) {
            switch ($plog['plog_type']) {
                case 1:
                    $plog['content'] = '积分充值';
                    $plog['plog_points'] = "+" . $plog['plog_points'];
                    break;
                case 2:
                    $plog['content'] = '注册推广';
                    $plog['plog_points'] = "+" . $plog['plog_points'];
                    break;
                case 8:
                    $plog['content'] = '积分消费';
                    $plog['plog_points'] = "-" . $plog['plog_points'];
                    break;
                default:
                    $plog['content'] = '其它';
                    break;

            }
            if ($plog['plog_remarks']) {
                $plog['content'] = $plog['plog_remarks'];
            }
        }


        $user_points = $this->user_info['user_points'];
        $intro = "";
        $remain_watch_times = 0;
        if ($page == 1) {
            $config = config('maccms');
            $user_config = $config['user'];
            $invite_reg_points = $user_config['invite_reg_points'];
            $limit_invite_reg_num = $user_config['reg_num'];
            $intro = "1.同一设备仅算作一名用户\n\n2.每邀请一名用户注册，奖励{$invite_reg_points}积分\n\n3.每个ip每天最多注册{$limit_invite_reg_num}次";

            $system_config = $this->getConfig();
            if ($system_config['ad_watch_reward_points'] > 0 && $system_config['ad_watch_reward_times'] > 0) {
                $intro .= "\n\n4.每天可观看{$system_config['ad_watch_reward_times']}次激励视频，每次获得{$system_config['ad_watch_reward_points']}积分";
                $today = date("Ymd", $this->time);
                $count = Db::table('getapp_watch_reward_ad_logs')->where([
                    'user_id' => $this->user_id,
                    'watch_date' => $today
                ])->count();
                $remain_watch_times = $system_config['ad_watch_reward_times'] - $count;
            }
        }



        return $this->setData(compact('plogs', 'user_points', 'intro', 'remain_watch_times'));
    }


    public function userVipCenter()
    {
        $user = $this->user_info;

        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];

        $vip_group_id = $getapp_system_config['vip_group_id'];
        $vip_groups = Db::table('mac_group')->where(['group_id' => $vip_group_id])->find();
        if (empty($vip_groups)) {
            $this->setMsg("会员价格不存在");
        }
        $vip_group_list = [];
        if ($vip_groups['group_points_day'] > 0) {
            $vip_group_list[] = [
                'index' => 0,
                'name' => '包天(1天)',
                'points' => $vip_groups['group_points_day'] . "积分"
            ];
        }

        if ($vip_groups['group_points_week'] > 0) {
            $vip_group_list[] = [
                'index' => 1,
                'name' => '包周(7天)',
                'points' => $vip_groups['group_points_week'] . "积分"
            ];
        }

        if ($vip_groups['group_points_month'] > 0) {
            $vip_group_list[] = [
                'index' => 2,
                'name' => '包月(30天)',
                'points' => $vip_groups['group_points_month'] . "积分"
            ];
        }

        if ($vip_groups['group_points_year'] > 0) {
            $vip_group_list[] = [
                'index' => 3,
                'name' => '包年(365天)',
                'points' => $vip_groups['group_points_year'] . "积分"
            ];
        }


        return $this->setData(compact('user', 'vip_group_list'));
    }

    /**
     * 购买vip
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userBuyVip()
    {
        $index = input('index/d', 0);

        $config = config('maccms');
        $getapp_system_config = $config['getapp_system_config'];

        $vip_group_id = $getapp_system_config['vip_group_id'];
        $vip_groups = Db::table('mac_group')->where(['group_id' => $vip_group_id])->find();
        $group_points_groups = [
            ['field' => 'group_points_day', 'days' => 1, 'name' => '包天(1天)'],
            ['field' => 'group_points_week', 'days' => 7, 'name' => '包周(7天)'],
            ['field' => 'group_points_month','days' => 30, 'name' => '包月(30天)'],
            ['field' => 'group_points_year','days' => 365, 'name' => '包年(365天)'],
        ];


        if (empty($vip_groups)) {
            return $this->setMsg("会员价格不存在");
        }
        if (!isset($group_points_groups[$index])) {
            return $this->setMsg("会员周期选择错误");
        }

        $need_points = $vip_groups[$group_points_groups[$index]['field']];
        if ($need_points <= 0) {
            return $this->setMsg("会员价格不存在");
        }

        Db::startTrans();
        try {
            $user = Db::name('user')->where(['user_id' => $this->user_id])->lock(true)->find();
            if ($user['user_points'] < $need_points) {
                return $this->setMsg("当前积分不足");
            }

            $user_end_time = strtotime(date("Y-m-d", $this->time)) - 1 + $group_points_groups[$index]['days'] * 86400;
            if ($this->isVip($user)) {
                $user_end_time = $user['user_end_time'] + $group_points_groups[$index]['days'] * 86400;
            }

            Db::name('user')->where(['user_id' => $this->user_id])
                ->update([
                    'user_points' => $user['user_points'] - $need_points,
                    'user_end_time' => $user_end_time,
                    'group_id' => $vip_group_id
                ]);
            Db::name('plog')->insert([
                'user_id' => $this->user_id,
                'plog_type' => 8,
                'plog_points' => $need_points,
                'plog_time' => $this->time,
                'plog_remarks' => '购买会员：' . $group_points_groups[$index]['name']
            ]);
            Db::commit();
            $user = $this->getUser($this->user_info['auth_token']);
            return $this->setData(compact('user'), "购买成功");
        } catch (\Exception $e) {
            Db::rollback();
            return $this->setMsg("会员价格不存在");
        }
    }

    /**
     * 看激励视频
     * @return \think\response\Json
     * @throws Exception
     */
    public function watchRewardAd()
    {
        $data = input('data/s', '');
        if (empty($data)) {
            return $this->setMsg("");
        }
        $config = config('maccms');


        $build_config = $config['getapp_build'];
        $key = $build_config['api_secret_key'];

        $data = openssl_decrypt($data, 'AES-128-CBC', $key, false, $key);
        $data = json_decode($data, true);
        if (empty($data)) {
            return $this->setMsg("");
        }

        $uuid = $data['uuid'];
        if (empty($uuid)) {
            return $this->setMsg("");
        }
        $system_config = $this->getConfig();
        if ($system_config['ad_watch_reward_points'] <= 0 || $system_config['ad_watch_reward_times'] <= 0) {
            return $this->setMsg("该活动已关闭");
        }
        $today = date("Ymd", $this->time);


        $count = Db::table('getapp_watch_reward_ad_logs')->where([
                'user_id' => $this->user_id,
                'watch_date' => $today
            ])->count();

        if ($count >= $system_config['ad_watch_reward_times']) {
            return $this->setMsg("今日奖励已领取完毕");
        }

        Db::table('getapp_watch_reward_ad_logs')->insert([
            'user_id' => $this->user_id,
            'uuid' => $uuid,
            'watch_date' => $today,
            'create_time' => $this->time
        ]);

        Db::name('user')->where(['user_id' => $this->user_id])->setInc('user_points', $system_config['ad_watch_reward_points']);

        Db::name('plog')->insert([
            'user_id' => $this->user_id,
            'plog_type' => 1,
            'plog_points' => $system_config['ad_watch_reward_points'],
            'plog_time' => $this->time,
            'plog_remarks' => '观看激励视频'
        ]);

        return $this->setMsg("领取成功", 1);
    }
}