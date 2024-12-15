<?php
$config_array = array (
    0 =>
        array (
            'name' => 'api',
            'title' => '插件官网',
            'type' => 'string',
            'content' =>
                array (),
            'value' => 'https://getapp.tv',
            'class' => 'id',
            'rule' => 'required',
            'msg' => '',
            'tip' => '无需配置，请直接点击配置后，在后台首页左侧的快捷菜单处进入管理',
            'ok' => '',
            'extend' => 'style="width:500px;height:38px;line-height: 34px;"',
        )
);

function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

recurse_copy($_SERVER['DOCUMENT_ROOT'] . "/addons/getapp/src/getapp/", $_SERVER['DOCUMENT_ROOT'] . "/application/admin/view/getapp/");
recurse_copy($_SERVER['DOCUMENT_ROOT'] . "/addons/getapp/src/getappapi/", $_SERVER['DOCUMENT_ROOT'] . "/application/api/controller/getappapi/");
copy($_SERVER['DOCUMENT_ROOT'] . '/addons/getapp/src/Getapp.php', $_SERVER['DOCUMENT_ROOT'] . "/application/admin/controller/Getapp.php");
//copy($_SERVER['DOCUMENT_ROOT'] . '/addons/getapp/src/App.php', $_SERVER['DOCUMENT_ROOT'] . "/application/api/controller/App.php");
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/player.php")) {
    copy($_SERVER['DOCUMENT_ROOT'] . '/addons/Getapp/player.php', $_SERVER['DOCUMENT_ROOT'] . "/player.php");
}
$path = $_SERVER['DOCUMENT_ROOT'] . '/application/data/config/quickmenu.txt';
$getapp = @require($_SERVER['DOCUMENT_ROOT'] . '/application/extra/maccms.php');
$info = 'APP管理,' . $getapp['site']['install_dir'] . 'addons/getapp/index.php';
$config_menu = config('quickmenu');

if (in_array($info, $config_menu)) {
    return $config_array;
}

$quickmenu = '';
if (!empty($config_menu)) {
    $quickmenu = array_values($config_menu);
    $quickmenu = join(chr(13), $quickmenu);
    $quickmenu = $quickmenu . chr(13) . chr(10) . $info;
} else {
    $quickmenu = $info;
}
$quickmenu = str_replace(chr(10), '', $quickmenu);
$menu_arr = explode(chr(13), $quickmenu);
mac_arr2file(APP_PATH . 'extra/quickmenu.php', $menu_arr);

return $config_array;
