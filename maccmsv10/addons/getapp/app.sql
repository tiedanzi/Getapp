CREATE TABLE IF NOT EXISTS `getapp_advert` (
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL DEFAULT '' COMMENT '广告名称',
     `position` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '位置',
     `content` varchar(2048) NOT NULL DEFAULT '' COMMENT '广告内容（图片地址、视频地址）',
     `req_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '跳转方式',
     `req_content` varchar(2048) NOT NULL DEFAULT '' COMMENT '跳转链接（视频id或地址）',
     `start_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '开始时间',
     `end_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '到期时间',
     `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态(0. 禁用  1.启用)',
     `sort` int(11) NOT NULL DEFAULT '0' COMMENT '顺序',
    `ui_mode` int(10) unsigned NOT NULL DEFAULT '0',
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='轮播广告';

--


CREATE TABLE IF NOT EXISTS `getapp_config`  (
                                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                 `name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置名',
                                 `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
                                 `param_name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置参数名',
                                 `value` varchar(255) NOT NULL DEFAULT '' COMMENT '值',
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='getapp系统广告配置参数';
--

INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (1, '开屏广告', '是否开启，0否1是', 'ad_splash_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (2, '首页插屏广告', '是否开启，0否1是', 'ad_home_page_insert_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (3, '后台返回app展示插屏的间隔', '时间，秒，最大99999999', 'ad_back_insert_interval_time', '30');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (4, '我的页面banner广告', '是否开启，0否1是', 'ad_mine_page_banner_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (5, '详情页banner广告', '是否开启，0否1是', 'ad_detail_page_banner_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (6, '搜索页面banner广告', '是否开启，0否1是', 'ad_search_page_banner_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (7, '播放页激励视频观看间隔', '时间，秒，-1为关闭', 'ad_detail_page_reward_interval_time', '3600');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (8, '弹幕审核', '是否开启，0否1是', 'system_danmu_status', '0');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (9, '评论审核', '是否开启，0否1是', 'system_comment_status', '0');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (10, '注册用户审核', '是否开启，0否1是', 'system_register_user_status', '0');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (11, '用户自定义头像', '是否开启，0否1是', 'system_user_avatar_status', '1');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (12, '分享文案', '分享文案', 'app_share_text', '我正在使用免费追剧app，下载地址：');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (13, '联系方式文案', '联系方式文案', 'app_contact_text', 'QQ:666666');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`, `value`) VALUES (14, '联系方式链接', '联系方式链接', 'app_contact_url', 'https://www.google.com');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`) VALUES (15, '三方弹幕地址', '三方弹幕地址', 'third_danmu_url');
INSERT INTO `getapp_config` (`id`, `name`, `desc`, `param_name`) VALUES (16, '发现页地址', '留空首页不显示，填写后首页会多一个发现页tab', 'app_extra_find_url');

--


CREATE TABLE IF NOT EXISTS `getapp_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40)  NOT NULL DEFAULT ''  COMMENT '公告标题',
  `intro` varchar(80) COMMENT '简介',
  `content` text COMMENT '公告内容',
  `create_time` bigint NOT NULL DEFAULT 0 COMMENT '发布时间',
  `is_top` tinyint NOT NULL DEFAULT 0 COMMENT '置顶（0.否 1.是）',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态（0. 禁用 1.启用）',
  `is_force` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '公告表';

--

CREATE TABLE IF NOT EXISTS `getapp_request_update` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vod_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `times` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '催更次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--

CREATE TABLE IF NOT EXISTS `getapp_update` (
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `version_name` varchar(255) NOT NULL DEFAULT '',
    `version_code` int(10) NOT NULL,
    `download_url` varchar(255) NOT NULL,
    `app_size` double unsigned NOT NULL DEFAULT '0',
    `description` varchar(255) NOT NULL DEFAULT '',
    `is_force` int(10) unsigned NOT NULL DEFAULT '0',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0',
     `browser_download_url` VARCHAR(500) NOT NULL DEFAULT '',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;;

--

CREATE TABLE IF NOT EXISTS `getapp_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `auth_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='getapp用户';

--

CREATE TABLE IF NOT EXISTS `getapp_user_find` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--

CREATE TABLE IF NOT EXISTS `getapp_user_suggest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--

CREATE TABLE IF NOT EXISTS `getapp_vod_collect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vod_id` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`vod_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='getapp视频收藏';

--

CREATE TABLE IF NOT EXISTS `getapp_vod_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vod_id` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='getapp视频评论';

--

CREATE TABLE IF NOT EXISTS `getapp_vod_danmu` (
     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `vod_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '影视id',
    `url_position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '集数索引位置',
    `text` varchar(500) NOT NULL DEFAULT '' COMMENT '弹幕内容',
    `color` varchar(255) NOT NULL DEFAULT '' COMMENT '颜色值#eeeeee',
    `time` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '弹幕时间',
    `status` int(10) unsigned NOT NULL COMMENT '状态',
    `create_time` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
    `position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹幕位置：0滚动；1上方；2下方',
    `report_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '举报次数',
    `seek_to_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '跳转时间',
    PRIMARY KEY (`id`),
    KEY `vod_position_status` (`vod_id`,`url_position`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='getapp视频弹幕';

CREATE TABLE IF NOT EXISTS `getapp_mac_user_extra` (
    `user_id` int unsigned NOT NULL DEFAULT '0',
    `auth_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    `avatar_update_time` int unsigned NOT NULL DEFAULT '0',
    `invite_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    `invite_count` int unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`user_id`),
    KEY `auth_token` (`auth_token`),
    KEY `invite_code` (`invite_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `getapp_user_notice` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL DEFAULT '0',
          `from_id` int(10) unsigned NOT NULL DEFAULT '0',
          `from_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '类型：1反馈报错；2求片找片',
          `reply_content` varchar(500) NOT NULL DEFAULT '' COMMENT '回复',
          `is_read` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已读状态：0未读；1已读',
          `create_time` int(10) unsigned NOT NULL DEFAULT '0',
          `reply_link` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`,`is_read`,`from_type`) USING BTREE,
          KEY `from_type` (`user_id`,`from_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消息';


CREATE TABLE IF NOT EXISTS `getapp_watch_reward_ad_logs` (
                                               `id` int unsigned NOT NULL AUTO_INCREMENT,
                                               `user_id` int unsigned NOT NULL DEFAULT '0',
                                               `uuid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
                                               `watch_date` int unsigned NOT NULL DEFAULT '0',
                                               `create_time` int NOT NULL,
                                               PRIMARY KEY (`id`),
                                               UNIQUE KEY `uuid` (`uuid`,`watch_date`) USING BTREE,
                                               KEY `user_id` (`user_id`,`watch_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `getapp_vod_third_report` (
                                                         `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                         `report_type` int unsigned NOT NULL DEFAULT '1' COMMENT '举报类型：1弹幕',
                                                         `vod_id` int unsigned NOT NULL DEFAULT '0',
                                                         `url_position` int unsigned NOT NULL DEFAULT '0',
                                                         `report_content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    `create_time` int unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='三方数据举报';

CREATE TABLE IF NOT EXISTS `getapp_invite_logs` (
                                      `id` int unsigned NOT NULL AUTO_INCREMENT,
                                      `from_user_id` int unsigned NOT NULL DEFAULT '0',
                                      `device_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
                                      `event_type` int unsigned NOT NULL DEFAULT '1',
                                      `create_time` int unsigned NOT NULL DEFAULT '0',
                                      PRIMARY KEY (`id`),
                                      KEY `create_time` (`create_time`),
                                      KEY `from_user_id` (`from_user_id`),
                                      KEY `device_id` (`device_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;