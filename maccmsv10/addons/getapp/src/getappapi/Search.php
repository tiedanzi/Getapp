<?php
namespace app\api\controller\getappapi;


use think\Db;

/**
 * 搜索
 */
class Search extends Base
{
    private $xs_path = "addons/getapp/extra/XS.php";

    private $xs_ini_path = "addons/getapp/extra/search_vod.ini";
    public function index()
    {

    }

    public function buildIndex()
    {
        $key = input('key/s', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 0);

        if (empty($key) || $key != $this->getConfig('system_config_xun_search_key')) {
            exit("接口key值不存在");
        }


        $start_time = microtime(true);
        if (!file_exists($this->xs_path)) {
           return;
        }
        require_once $this->xs_path;
        $xs = new \XS($this->xs_ini_path);
        //获取索引对像 增删改
        $index=$xs->index;


        $type_list = Db::name('type')
            ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => ['>', 0]])
            ->order('type_sort asc')
            ->column('type_pid', 'type_id');


        $vods = Db::name('vod')
            ->where(['vod_status' => 1])
            ->field('vod_id, vod_name, vod_en, vod_blurb, vod_actor, vod_hits, type_id,  vod_pic, vod_pic_slide, vod_remarks, vod_sub, vod_class, vod_score, vod_year, vod_lang, vod_area')
            ->order('vod_id asc')
            ->select();

        if (empty($vods)) {
            echo "重建索引完成";
            exit();
        }

        if ($page == 1) {
            $index->beginRebuild();
        }


        // 开启缓冲区，默认 4MB，如 $index->openBuffer(8) 则表示 8MB
        $index->openBuffer();

        foreach ($vods as $vod) {
            if (isset($type_list[$vod['type_id']])) {
                $vod['type_id'] = $type_list[$vod['type_id']];
            }
            $doc = new \XSDocument($vod);
            $index->add($doc);
        }

        // 关闭缓冲区，必须和 openBuffer 成对使用
        $index->closeBuffer();
        $index->endRebuild();

        $getapp_setting = config('getapp_all_setting');
        $getapp_setting['max_search_id'] = intval($vod['vod_id']);
        mac_arr2file(APP_PATH . 'extra/getapp_all_setting.php', $getapp_setting);

        $count = count($vods);
        $use_time =  (microtime(true) - $start_time);
        echo "初始化数据完成，数据量：{$count}条，耗时：" . round($use_time, 2) . "秒";
        exit();

        $page += 1;
        $redirect_url = request()->domain() . "/api.php/getappapi.search/buildIndex?key={$key}&page={$page}";


        $delay = 1;
        $size = $page * $limit;
        echo "<!DOCTYPE html>";
        echo "<html lang='en'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta http-equiv='refresh' content='$delay; URL=$redirect_url'>";
        echo "<title>延迟跳转</title>";
        echo "</head>";
        echo "<body>";
        echo "<h1>耗时：{$use_time}，总索引数：{$size}，当前页：{$page}，页面将在 $delay 秒后跳转...</h1>";
        echo "</body>";
        echo "</html>";
    }

    public function update()
    {

        $key = input('key/s', '');
        if (empty($key) || $key != $this->getConfig('system_config_xun_search_key')) {
            exit("接口key值不存在");
        }
        $start_time = microtime(true);
        if (!file_exists($this->xs_path)) {
            return;
        }
        require_once $this->xs_path;
        $xs = new \XS($this->xs_ini_path);
        //获取索引对像 增删改
        $index=$xs->index;

        $getapp_setting = config('getapp_all_setting');
        $max_search_id = $getapp_setting['max_search_id'];

        if ($max_search_id <= 0) {
            exit("请先执行初始化链接");
        }

        $type_list = Db::name('type')
            ->where(['type_mid' => 1, 'type_status' => 1, 'type_pid' => ['>', 0]])
            ->order('type_sort asc')
            ->column('type_pid', 'type_id');

        $vods = Db::name('vod')
            ->where(['vod_status' => 1, 'vod_id' => ['>',  $max_search_id]])
            ->field('vod_id, vod_name, vod_en, vod_blurb, vod_actor, vod_hits, type_id,  vod_pic, vod_pic_slide, vod_remarks, vod_sub, vod_class, vod_score, vod_year, vod_lang, vod_area')
            ->order('vod_id asc')
            ->select();
        if (empty($vods)) {
            exit("无新增数据");
        }
        $count = count($vods);

        foreach ($vods as $vod) {
            if (isset($type_list[$vod['type_id']])) {
                $vod['type_id'] = $type_list[$vod['type_id']];
            }
            $doc = new \XSDocument($vod);
            $index->add($doc);
        }
        $getapp_setting = config('getapp_all_setting');
        $getapp_setting['max_search_id'] = intval($vod['vod_id']);
        mac_arr2file(APP_PATH . 'extra/getapp_all_setting.php', $getapp_setting);


        $use_time =  (microtime(true) - $start_time);
        echo "新增数据同步完成，数据量：{$count}条，耗时：" . round($use_time, 2) . "秒";
        exit();

    }

    public function search()
    {
        $type_id = input('type_id/d', 0);
        $keywords = input('keywords/s', '');
        $page = input('page/d', 1);
        $limit = 20;


        $start_time = microtime(true);
        if (!file_exists($this->xs_path)) {
            return;
        }
        require_once $this->xs_path;
        $xs = new \XS($this->xs_ini_path);
        $search = $xs->search;
        $total = $search->getDbTotal();
        var_dump($total);

//        $tokenizer = new \XSTokenizerScws;
//        $res = $search->getExpandedQuery($keywords);
//        echo "扩展<br>";
//        var_dump($res);
//        $words = $tokenizer->getResult($keywords);
//        var_dump($words);
//        $corrected = $search->getCorrectedQuery();
//        var_dump($corrected);

        $query = "";
        if ($type_id > 0) {
            $query .= 'type_id:' . $type_id . ' ';
        }
        $query .= $keywords;
        $docs = $search
            ->setFuzzy(true)
            ->setQuery($query)
            ->setLimit($limit, ($page - 1) * $limit)
            ->search();



        $data = [];
        foreach ($docs as $doc)
        {
            $data[] = [
                'vod_id' => $doc->vod_id,
                'vod_name' => $doc->vod_name,
                'vod_en' => $doc->vod_en,
                'vod_blurb' => $doc->vod_blurb,
                'vod_actor' => $doc->vod_actor,
                'vod_hits' => $doc->vod_hits,
                'vod_pic' => $doc->vod_pic,
                'vod_pic_slide' => $doc->vod_pic_slide,
                'vod_remarks' => $doc->vod_remarks,
                'vod_class' => $doc->vod_class,
                'vod_score' => $doc->vod_score,
                'vod_year' => $doc->vod_year,
                'vod_lang' => $doc->vod_lang,
                'vod_area' => $doc->vod_area,
            ];
        }

        var_dump($data);
    }
}