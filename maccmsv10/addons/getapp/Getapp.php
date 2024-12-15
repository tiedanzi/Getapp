<?php

namespace addons\getapp;

use think\Addons;

class Getapp extends Addons
{
	/**
	 * 安装方法
	 * @return bool
	 */
	public function install()
	{

		if (file_exists("application/admin/controller/Getapp.php")) {
			return true;
		}
		$this->recurse_copy("addons/getapp/src/getapp/", "application/admin/view/getapp/");
		copy('addons/getapp/src/Getapp.php', "application/admin/controller/Getapp.php");
        $this->recurse_copy("addons/getapp/src/getappapi/", "application/api/controller/getappapi/");
//		copy('addons/getapp/src/Encrypt.php', "application/api/controller/Encrypt.php");

		$path = 'application/data/config/quickmenu.txt';
		$getapp = @require('application/extra/maccms.php');
		$info = 'APP管理,' . $getapp['site']['install_dir'] . 'addons/getapp/index.php';
		if (stristr(file_get_contents($path), $info))
			return true;
		elseif (file_put_contents($path, chr(13) . chr(10) . $info, FILE_APPEND))
			return true;
		return true;
	}

	/**
	 * 卸载方法
	 * @return bool
	 */
	public function uninstall()
	{
		//调用函数，传入路径
		$this->deldir('application/admin/view/getapp');
		unlink('application/admin/controller/Getapp.php');
		unlink('application/api/controller/App.php');
		unlink('application/api/controller/Encrypt.php');
		unlink('application/admin/view/getapp');

		$path = 'application/data/config/quickmenu.txt';
		$getapp = @require('application/extra/maccms.php');
		$info = 'APP管理,' . $getapp['site']['install_dir'] . 'addons/getapp/index.php';
		$content = str_replace(chr(13) . chr(10) . $info, '', file_get_contents($path));
		file_put_contents($path, $content);
		$config_menu = config('quickmenu');
		if (!empty($config_menu)) {
			$quickmenu = array_values($config_menu);
			$quickmenu = join(chr(13),$quickmenu);
			$quickmenu = str_replace($info, '', $quickmenu);
			$quickmenu = str_replace(chr(10),'',$quickmenu);
            $menu_arr = explode(chr(13),$quickmenu);
			mac_arr2file(APP_PATH . 'extra/quickmenu.php', $menu_arr);
		}
		return true;
	}
	function deldir($dir)
	{
		//先删除目录下的文件：
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$fullpath = $dir . "/" . $file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					$this->deldir($fullpath);
				}
			}
		}

		closedir($dh);
		//删除当前文件夹：
		if (rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}

	function recurse_copy($src, $dst)
	{  // 原目录，复制到的目录

		$dir = opendir($src);
		@mkdir($dst);
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
}
