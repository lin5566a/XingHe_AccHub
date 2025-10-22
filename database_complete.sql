/*
 星河支付系统 - 完整数据库结构
 适用于 PHP 5.6 + MySQL 5.6
 
 创建时间: 2025-01-22
 版本: 1.0
 字符集: utf8
 排序规则: utf8_general_ci
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for epay_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `epay_admin_log`;
CREATE TABLE `epay_admin_log`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `operator` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作人',
  `admin_id` int(11) UNSIGNED NOT NULL COMMENT '管理员ID',
  `operation_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作类型',
  `module` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作模块',
  `ip_address` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户代理',
  `device_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设备信息',
  `operation_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作描述',
  `status` enum('成功','失败') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '状态',
  `operation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  PRIMARY KEY (`log_id`) USING BTREE,
  INDEX `idx_operator`(`operator`) USING BTREE,
  INDEX `idx_module`(`module`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_admin_id`(`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '后台操作日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_announcement
-- ----------------------------
DROP TABLE IF EXISTS `epay_announcement`;
CREATE TABLE `epay_announcement`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '公告标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '公告内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0=禁用，1=启用',
  `token` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '公告token，前端用来判断是否已关闭',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `publish_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '公告表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_channel_configs
-- ----------------------------
DROP TABLE IF EXISTS `epay_channel_configs`;
CREATE TABLE `epay_channel_configs`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道名称',
  `channel_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道代码',
  `promotion_link` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '推广链接',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认渠道：0=否，1=是',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_channel_code`(`channel_code`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_is_default`(`is_default`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '渠道配置表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for epay_cost_log
-- ----------------------------
DROP TABLE IF EXISTS `epay_cost_log`;
CREATE TABLE `epay_cost_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) NOT NULL COMMENT '成本类型：1=批次成本 2=人工发货 3=人工录入成本 4=批次成本修改 5=手动发货成本修改',
  `relate_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关联ID：批次ID或订单号',
  `amount` decimal(10, 2) NOT NULL COMMENT '成本金额',
  `amount_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '金额类型：1=增加 2=减少',
  `operator` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '详情',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `batch_card_count` int(11) NULL DEFAULT NULL COMMENT '批次卡密数量，仅批次相关类型用',
  `category_id` int(11) NULL DEFAULT NULL COMMENT '商品分类ID',
  `product_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商品名称',
  `quantity` int(11) NULL DEFAULT 0 COMMENT '数量',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type`(`type`) USING BTREE,
  INDEX `idx_relate_id`(`relate_id`) USING BTREE,
  INDEX `idx_category_id`(`category_id`) USING BTREE,
  INDEX `idx_product_name`(`product_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '成本流水表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_customer_service
-- ----------------------------
DROP TABLE IF EXISTS `epay_customer_service`;
CREATE TABLE `epay_customer_service`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `tg_service_link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'TG客服链接',
  `online_service_link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '在线客服链接',
  `group_link` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '群链接',
  `tg_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'TG客服链接显示开关：0=隐藏，1=显示',
  `group_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '群链接显示开关：0=隐藏，1=显示',
  `online_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '在线客服链接显示开关：0=隐藏，1=显示',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '客服信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_document_category
-- ----------------------------
DROP TABLE IF EXISTS `epay_document_category`;
CREATE TABLE `epay_document_category`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `sort` int(11) NULL DEFAULT 0 COMMENT '排序（数字越大越靠前）',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1:启用 0:禁用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档分类表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_documents
-- ----------------------------
DROP TABLE IF EXISTS `epay_documents`;
CREATE TABLE `epay_documents`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档标题',
  `subtitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '副标题',
  `category` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档分类',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档内容',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0:未发布 1:已发布',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_category`(`category`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档管理表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_email_config
-- ----------------------------
DROP TABLE IF EXISTS `epay_email_config`;
CREATE TABLE `epay_email_config`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `smtp_server` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'SMTP服务器地址',
  `port` int(11) NOT NULL COMMENT '端口',
  `security_protocol` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '安全协议',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `sender_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发件人邮箱',
  `sender_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin' COMMENT '发件人称呼',
  `auth_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '授权码/密码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '邮件配置表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_manager
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager`;
CREATE TABLE `epay_manager`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `truename` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `status` int(1) NULL DEFAULT 1 COMMENT '状态 1：正常 0：禁用',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `online_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '管理员头像',
  `token` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录token',
  `token_expire_time` int(11) NULL DEFAULT NULL COMMENT 'token过期时间',
  `temp_token` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '临时token',
  `temp_token_expire_time` int(11) NULL DEFAULT NULL COMMENT '临时token过期时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_manager_log
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager_log`;
CREATE TABLE `epay_manager_log`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `add_time` datetime NULL DEFAULT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员',
  `ip` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'IP',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'act&op',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `related` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '关联ID',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '类型',
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详情',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_manager_permission
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager_permission`;
CREATE TABLE `epay_manager_permission`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权限名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'url',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  UNIQUE INDEX `url`(`url`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_manager_role
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager_role`;
CREATE TABLE `epay_manager_role`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `role_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色id',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色名称',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色描述',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `state` tinyint(2) NOT NULL COMMENT '1 正常 2 禁用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_manager_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager_role_permission`;
CREATE TABLE `epay_manager_role_permission`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) UNSIGNED NOT NULL COMMENT '角色id',
  `permission_id` int(10) UNSIGNED NOT NULL COMMENT '权限id',
  `is_google` tinyint(1) NOT NULL DEFAULT 2 COMMENT '是否需要谷歌验证  1 需要  2 不需要',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_manager_user_role
-- ----------------------------
DROP TABLE IF EXISTS `epay_manager_user_role`;
CREATE TABLE `epay_manager_user_role`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manager_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `role_id` int(10) UNSIGNED NOT NULL COMMENT '角色id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_member_level
-- ----------------------------
DROP TABLE IF EXISTS `epay_member_level`;
CREATE TABLE `epay_member_level`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '等级名称',
  `upgrade_amount` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '累计充值升级条件',
  `discount` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '100' COMMENT '会员折扣（百分比）',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '备注',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '会员介绍',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '会员等级表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_notifications
-- ----------------------------
DROP TABLE IF EXISTS `epay_notifications`;
CREATE TABLE `epay_notifications`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '通知类型：manual_shipment=手动发货，replenishment=补货提醒',
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '通知标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '通知内容',
  `related_id` int(11) NULL DEFAULT 0 COMMENT '关联ID（订单ID或补货通知ID）',
  `order_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '订单号（手动发货通知使用）',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已读：0=未读，1=已读',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type_is_read`(`type`, `is_read`) USING BTREE,
  INDEX `idx_created_at`(`created_at`) USING BTREE,
  INDEX `idx_related_id`(`related_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '通知中心表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for epay_payment_channel_rates
-- ----------------------------
DROP TABLE IF EXISTS `epay_payment_channel_rates`;
CREATE TABLE `epay_payment_channel_rates`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '费率ID',
  `channel_id` int(11) NOT NULL COMMENT '通道ID',
  `payment_method` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付方式：wechat=微信，alipay=支付宝，usdt=USDT',
  `weight` int(11) NOT NULL DEFAULT 0 COMMENT '送单权重',
  `fee_rate` decimal(5, 2) NOT NULL COMMENT '手续费率(%)',
  `min_amount` decimal(10, 2) NOT NULL COMMENT '单笔最小金额',
  `max_amount` decimal(10, 2) NOT NULL COMMENT '单笔最大金额',
  `product_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付产品编号',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_channel_method`(`channel_id`, `payment_method`) USING BTREE,
  INDEX `idx_channel_id`(`channel_id`) USING BTREE,
  INDEX `idx_payment_method`(`payment_method`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_weight`(`weight`) USING BTREE,
  INDEX `idx_product_code`(`product_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '支付通道费率表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_payment_channels
-- ----------------------------
DROP TABLE IF EXISTS `epay_payment_channels`;
CREATE TABLE `epay_payment_channels`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '通道ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '通道名称',
  `abbr` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '渠道标识：sdpay=SD支付，k4pay=K4支付',
  `merchant_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商户号',
  `merchant_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商户密钥',
  `create_order_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '下单请求地址',
  `query_order_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '查单地址',
  `balance_query_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '余额查询地址',
  `notify_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '回调通知地址',
  `return_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '返回地址',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_merchant_id`(`merchant_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '支付通道表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_payment_config
-- ----------------------------
DROP TABLE IF EXISTS `epay_payment_config`;
CREATE TABLE `epay_payment_config`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `usdt_fee` decimal(5, 2) NOT NULL COMMENT 'USDT手续费',
  `wechat_fee` decimal(5, 2) NOT NULL COMMENT '微信手续费',
  `alipay_fee` decimal(5, 2) NOT NULL COMMENT '支付宝手续费',
  `usdt_rate` decimal(10, 4) NOT NULL DEFAULT 7.0000 COMMENT 'USDT汇率',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '支付配置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_popup_config
-- ----------------------------
DROP TABLE IF EXISTS `epay_popup_config`;
CREATE TABLE `epay_popup_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '弹窗标题',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '弹窗内容（支持富文本）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=停用，1=启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '首页弹窗配置表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for epay_product_categories
-- ----------------------------
DROP TABLE IF EXISTS `epay_product_categories`;
CREATE TABLE `epay_product_categories`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名称',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '分类描述',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序（数值越小越靠前）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（2-禁用，1-启用）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品分类表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_product_description_templates
-- ----------------------------
DROP TABLE IF EXISTS `epay_product_description_templates`;
CREATE TABLE `epay_product_description_templates`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `template_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '模板名称',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '分类ID',
  `content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档内容',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_category`(`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品描述模板表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_product_orders
-- ----------------------------
DROP TABLE IF EXISTS `epay_product_orders`;
CREATE TABLE `epay_product_orders`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品订单ID',
  `order_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `trade_no` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付渠道交易号',
  `product_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `product_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名称',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '商品分类ID',
  `online_price` decimal(10, 2) NOT NULL COMMENT '线上价格',
  `purchase_price` decimal(10, 2) NOT NULL COMMENT '购买价格（折后）',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT '数量',
  `total_price` decimal(10, 2) NOT NULL COMMENT '总价',
  `fee` decimal(10, 2) NOT NULL COMMENT '手续费',
  `card_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '卡密ID',
  `card_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '卡密信息',
  `user_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `visitor_uuid` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '访客UUID',
  `channel_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道代码',
  `user_role` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '游客' COMMENT '用户身份（会员等级名称）',
  `user_role_id` int(11) NULL DEFAULT 0 COMMENT '用户身份ID（会员等级ID，0表示游客）',
  `user_discount` decimal(5, 2) NULL DEFAULT 100.00 COMMENT '用户折扣（百分比）',
  `user_nickname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户昵称',
  `query_password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '查询密码',
  `payment_method` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付方式',
  `channel_id` int(11) NULL DEFAULT NULL COMMENT '支付通道ID',
  `channel_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付通道名称',
  `fee_rate` decimal(5, 2) NULL DEFAULT NULL COMMENT '实际费率(%)',
  `delivery_method` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发货方式',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（    1. 待付款\n    2. 待发货\n    3. 已完成\n    4. 已取消）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手动发货的备注',
  `usdt_rate` decimal(10, 4) NOT NULL DEFAULT 7.0000 COMMENT 'USDT汇率',
  `usdt_fee` decimal(5, 2) NOT NULL COMMENT 'USDT手续费',
  `wechat_fee` decimal(5, 2) NOT NULL COMMENT '微信手续费',
  `alipay_fee` decimal(5, 2) NOT NULL COMMENT '支付宝手续费',
  `usdt_amount` decimal(10, 4) NOT NULL COMMENT 'USDT支付金额',
  `alipay_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付宝支付链接',
  `wechat_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信支付链接',
  `usdt_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'USDT支付链接',
  `finished_at` datetime NULL DEFAULT NULL COMMENT '订单完成时间',
  `cost_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '订单成本价',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_order_number`(`order_number`) USING BTREE,
  INDEX `idx_category_id`(`category_id`) USING BTREE,
  INDEX `idx_user_email`(`user_email`) USING BTREE,
  INDEX `idx_user_role`(`user_role`) USING BTREE,
  INDEX `idx_user_discount`(`user_discount`) USING BTREE,
  INDEX `idx_user_nickname`(`user_nickname`) USING BTREE,
  INDEX `idx_visitor_uuid`(`visitor_uuid`) USING BTREE,
  INDEX `idx_channel_code`(`channel_code`) USING BTREE,
  INDEX `idx_channel_created_at`(`channel_code`, `created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品订单表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_product_packages
-- ----------------------------
DROP TABLE IF EXISTS `epay_product_packages`;
CREATE TABLE `epay_product_packages`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '安装包名称',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '安装包图标',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型：1=文件，2=链接',
  `file_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文件路径',
  `download_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '下载链接',
  `is_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示：0=隐藏，1=显示',
  `enable_regional_restriction` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用地域限制：0=否，1=是',
  `disallowed_cities` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '不允许的城市列表，JSON格式存储',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_product_id`(`product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品安装包表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_product_stock
-- ----------------------------
DROP TABLE IF EXISTS `epay_product_stock`;
CREATE TABLE `epay_product_stock`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '批次ID',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `order_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单ID',
  `card_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '卡密信息',
  `cost_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '成本价',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0未售出 1已售出 2已售出',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_product_id`(`product_id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_batch_id`(`batch_id`) USING BTREE,
  INDEX `idx_cost_price`(`cost_price`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品库存表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_products
-- ----------------------------
DROP TABLE IF EXISTS `epay_products`;
CREATE TABLE `epay_products`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名称',
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商品图片',
  `tutorial_video` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '使用教程视频URL',
  `tutorial_video_size` int(11) NOT NULL DEFAULT 0 COMMENT '视频文件大小（字节）',
  `tutorial_video_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '视频文件名',
  `tutorial_video_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '视频状态：0=停用，1=启用',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '商品分类ID',
  `description_template_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '商品详情模板ID',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品详情',
  `use_template` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否使用模板（0-自定义，1-使用模板）',
  `price` decimal(10, 2) NOT NULL COMMENT '价格',
  `original_price` decimal(10, 2) NOT NULL COMMENT '商品原价',
  `cost_price` decimal(10, 2) NOT NULL COMMENT '成本价',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT '库存',
  `stock_warning` int(11) NOT NULL DEFAULT 0 COMMENT '库存预警值',
  `enable_purchase_limit` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用购买限制：0=否，1=是',
  `purchase_limit_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '限制类型：1=固定数量，2=百分比',
  `purchase_limit_value` int(11) NOT NULL DEFAULT 0 COMMENT '限制值：固定数量或百分比',
  `sales` int(11) NOT NULL DEFAULT 0 COMMENT '销量',
  `delivery_method` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发货方式',
  `wholesale_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '批发价',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（2-下架，1-上架）',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序(数值越小越靠前)',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `enable_discount` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否参与会员折扣 1=是 0=否',
  `discount_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用折扣（0=未启用，1=已启用）',
  `discount_percentage` decimal(5, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣百分比（如80表示8折）',
  `discount_start_time` datetime NULL DEFAULT NULL COMMENT '折扣开始时间',
  `discount_end_time` datetime NULL DEFAULT NULL COMMENT '折扣结束时间',
  `discount_set_time` datetime NULL DEFAULT NULL COMMENT '折扣设置时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_category_id`(`category_id`) USING BTREE,
  INDEX `fk_description_template_id`(`description_template_id`) USING BTREE,
  CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES `epay_product_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品列表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_recharge_order
-- ----------------------------
DROP TABLE IF EXISTS `epay_recharge_order`;
CREATE TABLE `epay_recharge_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `user_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `nickname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户昵称',
  `payment_method` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付方式',
  `channel_id` int(11) NULL DEFAULT 0 COMMENT '支付渠道ID',
  `channel_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付渠道名称',
  `fee_rate` decimal(5, 4) NULL DEFAULT NULL COMMENT '手续费率',
  `recharge_amount` decimal(10, 2) NOT NULL COMMENT '充值金额',
  `fee` decimal(10, 2) NOT NULL COMMENT '手续费',
  `arrive_amount` decimal(10, 2) NOT NULL COMMENT '到账金额',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态 0待支付 1已完成 2已退款',
  `refund_amount` decimal(10, 2) NULL DEFAULT NULL COMMENT '退款金额',
  `refund_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '退款说明',
  `refund_operator` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '退款操作人',
  `cancel_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '取消原因',
  `confirm_operator` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '确认操作人',
  `alipay_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付宝支付链接',
  `wechat_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '微信支付链接',
  `usdt_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'USDT支付链接',
  `usdt_rate` decimal(10, 4) NULL DEFAULT NULL COMMENT 'USDT汇率',
  `usdt_fee` decimal(10, 2) NULL DEFAULT NULL COMMENT 'USDT手续费',
  `usdt_amount` decimal(10, 4) NULL DEFAULT NULL COMMENT 'USDT金额',
  `cancel_operator` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '取消操作人',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` datetime NULL DEFAULT NULL,
  `trade_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '第三方交易号',
  `product_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '充值' COMMENT '商品名称',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id`) USING BTREE,
  INDEX `idx_order_no`(`order_no`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_created_at`(`created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '充值订单表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_system_content
-- ----------------------------
DROP TABLE IF EXISTS `epay_system_content`;
CREATE TABLE `epay_system_content`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `type` enum('使用协议','关于我们') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '信息类型',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 开启 2 关闭',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type`(`type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统内容表，用于存储使用协议和关于我们的内容' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_system_info
-- ----------------------------
DROP TABLE IF EXISTS `epay_system_info`;
CREATE TABLE `epay_system_info`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `system_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '系统名称',
  `system_logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '系统Logo',
  `copyright_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '版权信息',
  `order_timeout` int(11) UNSIGNED NOT NULL DEFAULT 15 COMMENT '订单过期时间(分钟)',
  `manual_shipment_sound` tinyint(1) NOT NULL DEFAULT 1 COMMENT '手动发货提示音：0=关闭，1=开启',
  `replenishment_sound` tinyint(1) NOT NULL DEFAULT 1 COMMENT '补货提醒提示音：0=关闭，1=开启',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统基本信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for epay_user_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `epay_user_balance_log`;
CREATE TABLE `epay_user_balance_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '变动类型',
  `amount` decimal(10, 2) NOT NULL COMMENT '变动金额',
  `before_balance` decimal(18, 2) NULL DEFAULT NULL COMMENT '变动前余额',
  `after_balance` decimal(18, 2) NULL DEFAULT NULL COMMENT '变动后余额',
  `direction` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '变动方向(in增加，out减少)',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '变动说明',
  `order_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '关联订单号',
  `operator` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '操作人',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户余额变动日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_user_requests
-- ----------------------------
DROP TABLE IF EXISTS `epay_user_requests`;
CREATE TABLE `epay_user_requests`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '需求状态 0:未解决 1:已解决',
  `user_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `product_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名称',
  `quantity` int(11) UNSIGNED NOT NULL COMMENT '需求数量',
  `remarks` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注说明',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送时间',
  `ip_address` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户IP地址',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_user_email`(`user_email`) USING BTREE,
  INDEX `idx_ip_sent_at`(`ip_address`, `sent_at`) USING BTREE COMMENT 'IP地址和发送时间联合索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户需求表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_user_sessions
-- ----------------------------
DROP TABLE IF EXISTS `epay_user_sessions`;
CREATE TABLE `epay_user_sessions`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '会话ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `token` varchar(191) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录token（限制191字符以支持utf8索引）',
  `device_id` varchar(191) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '设备ID（可选）',
  `device_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '设备名称',
  `device_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '设备类型（web, mobile, desktop等）',
  `ip_address` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录IP地址',
  `user_agent` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户代理信息',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `last_activity` datetime NOT NULL COMMENT '最后活动时间',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否活跃（1=活跃，0=非活跃）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `token`(`token`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `device_id`(`device_id`) USING BTREE,
  INDEX `expire_time`(`expire_time`) USING BTREE,
  INDEX `is_active`(`is_active`) USING BTREE,
  INDEX `last_activity`(`last_activity`) USING BTREE,
  INDEX `idx_user_sessions_user_active`(`user_id`, `is_active`) USING BTREE,
  INDEX `idx_user_sessions_expire`(`expire_time`, `is_active`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户会话表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for epay_users
-- ----------------------------
DROP TABLE IF EXISTS `epay_users`;
CREATE TABLE `epay_users`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
  `nickname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户昵称',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `membership_level` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '会员等级',
  `balance` decimal(10, 2) NOT NULL COMMENT '账户余额',
  `total_recharge` decimal(10, 2) NOT NULL COMMENT '累计充值',
  `total_spent` decimal(10, 2) NOT NULL COMMENT '累计消费',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（2-禁用，1-正常）',
  `token` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户token',
  `token_expire_time` timestamp NULL DEFAULT NULL COMMENT 'token过期时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `custom_discount` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '自定义会员折扣，百分比，允许两位小数，如97.55表示97.55%',
  `allow_multi_login` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否允许多端登录（1=允许，0=不允许）',
  `max_sessions` int(11) NOT NULL DEFAULT 5 COMMENT '最大会话数量',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE,
  UNIQUE INDEX `uk_email`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for epay_visitor_records
-- ----------------------------
DROP TABLE IF EXISTS `epay_visitor_records`;
CREATE TABLE `epay_visitor_records`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `ip_address` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '访客IP地址',
  `user_agent` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户代理信息',
  `referer` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '来源页面',
  `request_uri` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '请求URI',
  `page_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'unknown' COMMENT '页面类型：homepage,product_detail,user_center,order,payment,api,other',
  `request_method` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'GET' COMMENT '请求方法',
  `session_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '会话ID',
  `visitor_uuid` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '访客UUID',
  `channel_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道代码',
  `product_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品ID',
  `product_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `view_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'page' COMMENT '浏览类型：page=页面浏览，detail=详情浏览',
  `referer_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '来源页面URL',
  `is_mobile` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否移动设备：0=否，1=是',
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '国家',
  `province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '城市',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '访问时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_ip_created`(`ip_address`, `created_at`) USING BTREE,
  INDEX `idx_created_at`(`created_at`) USING BTREE,
  INDEX `idx_session_id`(`session_id`) USING BTREE,
  INDEX `idx_visitor_uuid`(`visitor_uuid`) USING BTREE,
  INDEX `idx_channel_code`(`channel_code`) USING BTREE,
  INDEX `idx_channel_created_at`(`channel_code`, `created_at`) USING BTREE,
  INDEX `idx_product_id`(`product_id`) USING BTREE,
  INDEX `idx_product_created_at`(`product_id`, `created_at`) USING BTREE,
  INDEX `idx_view_type`(`view_type`) USING BTREE,
  INDEX `idx_visitor_product`(`visitor_uuid`, `product_id`) USING BTREE,
  INDEX `idx_visitor_uuid_created_at`(`visitor_uuid`, `created_at`) USING BTREE,
  INDEX `idx_ip_address_created_at`(`ip_address`, `created_at`) USING BTREE,
  INDEX `idx_channel_code_created_at`(`channel_code`, `created_at`) USING BTREE,
  INDEX `idx_visitor_uuid_channel_code`(`visitor_uuid`, `channel_code`) USING BTREE,
  INDEX `idx_ip_address_channel_code`(`ip_address`, `channel_code`) USING BTREE,
  INDEX `idx_visitor_uuid_channel_created_at`(`visitor_uuid`, `channel_code`, `created_at`) USING BTREE,
  INDEX `idx_ip_address_channel_created_at`(`ip_address`, `channel_code`, `created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '访问记录表' ROW_FORMAT = COMPACT;

SET FOREIGN_KEY_CHECKS = 1;
