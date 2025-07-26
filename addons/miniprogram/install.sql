-- ----------------------------
-- Table structure for __PREFIX__miniprogram_config
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__miniprogram_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `value` text COMMENT '变量值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COMMENT='系统配置';

-- ----------------------------
-- Table structure for __PREFIX__miniprogram_user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__miniprogram_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `openid` varchar(128) NOT NULL COMMENT '微信openid',
  `unionid` varchar(128) DEFAULT '' COMMENT '微信unionid',
  `user_type` varchar(32) DEFAULT NULL DEFAULT 'miniprogram' COMMENT '用户类型',
  `createtime` BIGINT(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户授权表';

CREATE TABLE IF NOT EXISTS `__PREFIX__miniprogram_reply` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `keyword` varchar(1000) NOT NULL DEFAULT '' COMMENT '关键字',
  `reply_type` enum('text','image','link','miniprogrampage') NOT NULL DEFAULT 'text' COMMENT '回复类型:text=文本消息,image=图片消息,link=图文消息,miniprogrampage=小程序卡片',
  `matching_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '匹配方式：1-全匹配；2-模糊匹配',
  `content` text COMMENT '回复数据',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` BIGINT(16) UNSIGNED NULL DEFAULT NULL COMMENT '添加时间',
  `updatetime` BIGINT(16) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `reply_type` (`reply_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='微信回复表';

-- ----------------------------
-- Table structure for __PREFIX__miniprogram_news
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__miniprogram_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '图文消息表',
  `title` varchar(255) NOT NULL COMMENT '图文标题',
  `description` varchar(255) NOT NULL COMMENT '图文简介',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '封面',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'URL',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` BIGINT(16) UNSIGNED NULL DEFAULT NULL COMMENT '添加时间',
  `updatetime` BIGINT(16) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='图文消息管理表';

-- ----------------------------
-- Table structure for __PREFIX__miniprogram_template
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__miniprogram_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `tempkey` char(50) NOT NULL DEFAULT '' COMMENT '场景值',
  `name` char(100) NOT NULL DEFAULT '' COMMENT '模板名',
  `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '回复内容',
  `tempid` char(100) DEFAULT NULL COMMENT '模板ID',
  `pagepath` varchar(100) NOT NULL DEFAULT '' COMMENT '小程序页面path',
  `add_time` BIGINT(16) UNSIGNED NULL DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `tempkey` (`tempkey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='订阅消息模板';
