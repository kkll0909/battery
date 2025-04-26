CREATE TABLE IF NOT EXISTS `__PREFIX__department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `weigh` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `organise_id` int(11) DEFAULT '0' COMMENT '组织id(最顶级)',
  `tags` varchar(200) DEFAULT '' COMMENT '标签属性',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COMMENT='组织部门表';

CREATE TABLE IF NOT EXISTS `__PREFIX__department_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL COMMENT '部门id',
  `organise_id` int(11) DEFAULT NULL COMMENT '组织id(最顶级)',
  `admin_id` int(11) DEFAULT NULL COMMENT '成员id',
  `create_time` bigint(16) NOT NULL COMMENT '加入时间',
  `update_time` bigint(16) NOT NULL COMMENT '更新时间',
  `is_principal` tinyint(1) DEFAULT NULL COMMENT '是否负责人',
  `is_owner` tinyint(1) DEFAULT '0' COMMENT '拥有者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COMMENT='组织部门-成员表';

-- 1.0.3
ALTER TABLE `__PREFIX__admin` ADD `data_scope` tinyint(4)  NULL DEFAULT 0 COMMENT '0默认1全部';



