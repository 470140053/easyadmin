SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ----------------------------
--  Table structure for `tp_user`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user`;
CREATE TABLE `__PREFIX__user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(8) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码延伸码',
  `pay_pwd` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '支付密码',
  `pay_salt` varchar(8) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '支付密码延伸码',
  `phone` bigint(15) NOT NULL DEFAULT '0' COMMENT '手机号码',
  `nickname` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '昵称',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-保密 1-男 2-女',
  `avatar` int(10) NOT NULL DEFAULT '0' COMMENT '会员图片',
  `birthday` int(10) NOT NULL DEFAULT '0' COMMENT '生日',
  `openid` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '微信OPENID',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `reg_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-账号注册 1-手机注册 2-邮箱注册 3-微信注册',
  `last_login_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `qr_code_thumb` int(10) NOT NULL DEFAULT '0' COMMENT '二维码图片地址',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-禁用 1-启用  2-冻结',
  `auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-未审核 1-审核 2-已反审 ',
  `is_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-提交审核 0-默认未提交 2-已审核',
  `auth_time` int(10) NOT NULL DEFAULT '0' COMMENT '审核通过时间',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '上一级ID',
  `ppid` int(10) NOT NULL DEFAULT '0' COMMENT '上二级ID',
  `pppid` int(10) NOT NULL DEFAULT '0' COMMENT '上三级ID',
  `pid_username` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '上一级用户名',
  `ppid_username` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '上二级用户名',
  `pppid_username` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '上三级用户名',
  `pid_ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '推广比例 下一级',
  `ppid_ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '推广比例 下二级',
  `pppid_ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '推广比例 下三级',
  `realname` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '真实姓名',
  `country` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '中国' COMMENT '国家',
  `province` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '市',
  `area` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '区',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `note` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '备注信息',
  PRIMARY KEY (`id`),
  KEY `username_inx` (`username`),
  KEY `pinx` (`pid`) USING BTREE,
  KEY `openid_inx` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
--  Records of `__PREFIX__user`
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user` VALUES ('1', 'admin', '', '', '', '', '0', 'admin', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '', '', '', '0.00', '0.00', '0.00', '', '中国', '', '', '', '1606071364', '');
COMMIT;

-- ----------------------------
--  Table structure for `tp_user_frozen`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_frozen`;
CREATE TABLE `__PREFIX__user_frozen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '被冻结用户',
  `note` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '备注',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '操作用户',
  `admin_username` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `uid_inx` (`uid`),
  KEY `aid_inx` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户冻结记录表';



-- ----------------------------
--  Table structure for `tp_user_relation`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_relation`;
CREATE TABLE `__PREFIX__user_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `relation` text CHARACTER SET utf8 COMMENT '关系',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='上级关系链';

-- ----------------------------
--  Records of `tp_user_relation`
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_relation` VALUES ('1', '1');
COMMIT;

-- ----------------------------
--  Table structure for `tp_user_relation_asc`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_relation_asc`;
CREATE TABLE `tp_user_relation_asc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `relation` text CHARACTER SET utf8 COMMENT '关系',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='我的团队';

-- ----------------------------
--  Records of `tp_user_relation_asc`
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_relation_asc` VALUES ('1', '1');
COMMIT;

-- ----------------------------
--  Table structure for `tp_user_wallet`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_wallet`;
CREATE TABLE `__PREFIX__user_wallet` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '等级',
  `level_title` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '等级名称',
  `level_time` int(10) NOT NULL DEFAULT '0' COMMENT '等级提升最后时间',
  `level_expire_time` int(10) NOT NULL DEFAULT '0' COMMENT '会员等级到期时间',
  `primary_level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '原等级',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-禁用 1-启用  2-冻结',
  `auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-审核',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '钱包余额',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '积分余额',
  `grow` int(10) NOT NULL DEFAULT '0' COMMENT '成长值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户钱包';

-- ----------------------------
--  Records of `tp_user_wallet`
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_wallet` VALUES ('1', '0', '', '0', '0', '0', '0', '0', '0.00', '0', '0');
COMMIT;

-- ----------------------------
--  Table structure for `tp_user_wallet_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_wallet_log`;
CREATE TABLE `__PREFIX__user_wallet_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '流水类型 1-积分流水 2-钱包约流水',
  `flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '结算类型 1-收入 2-扣除 3-未结算',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-正常',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `note` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '备注',
  `source_id` int(10) NOT NULL DEFAULT '0' COMMENT '来源ID',
  `source_realname` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '来源用户',
  `table_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '对应表',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid_inx` (`uid`),
  KEY `time_inx` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户收支流水';



BEGIN;
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '会员管理列表', 'HuiYuanGuanLiLieBiao', 'table', '/admin/member/lists', '', '{\"searchForm\":[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"input\",\"title\":\"\\u8f93\\u5165\\u6846\",\"col\":6,\"labelWidth\":\"80px\",\"props\":[],\"label\":\"ID\",\"prop\":\"id\",\"tab\":0,\"index\":0},{\"type\":\"input\",\"title\":\"\\u8f93\\u5165\\u6846\",\"col\":6,\"labelWidth\":\"80px\",\"props\":[],\"label\":\"\\u7528\\u6237\\u540d\",\"prop\":\"username\",\"tab\":0,\"index\":1},{\"type\":\"input\",\"title\":\"\\u8f93\\u5165\\u6846\",\"col\":6,\"labelWidth\":\"80px\",\"props\":[],\"label\":\"\\u624b\\u673a\\u53f7\",\"prop\":\"phone\",\"tab\":0,\"index\":2},{\"type\":\"select\",\"title\":\"\\u4e0b\\u62c9\\u6846\",\"col\":6,\"labelWidth\":\"80px\",\"props\":[],\"options\":[{\"label\":\"\\u5df2\\u7981\\u7528\",\"value\":\"0\"},{\"label\":\"\\u5df2\\u542f\\u7528\",\"value\":\"1\"},{\"label\":\"\\u5df2\\u51bb\\u7ed3\",\"value\":\"2\"}],\"label\":\"\\u72b6\\u6001\",\"prop\":\"status\",\"tab\":0,\"index\":3,\"datatype\":2,\"dictapi\":\"\",\"dictattr\":\"\",\"dictlabel\":\"\",\"dictvalue\":\"\"},{\"type\":\"input\",\"title\":\"\\u8f93\\u5165\\u6846\",\"col\":6,\"labelWidth\":\"100px\",\"props\":[],\"label\":\"\\u63a8\\u8350\\u4eba\\u8d26\\u53f7\",\"prop\":\"pid_username\",\"tab\":0,\"index\":4},{\"type\":\"date\",\"title\":\"\\u65e5\\u671f\",\"col\":8,\"labelWidth\":\"100px\",\"props\":{\"startPlaceholder\":\"\\u5f00\\u59cb\\u65e5\\u671f\",\"endPlaceholder\":\"\\u7ed3\\u675f\\u65e5\\u671f\",\"rangeSeparator\":\"\\u81f3\",\"type\":\"daterange\"},\"label\":\"\\u6700\\u540e\\u767b\\u5f55\\u65f6\\u95f4\",\"prop\":\"last_login_time\",\"tab\":0,\"index\":5},{\"type\":\"date\",\"title\":\"\\u65e5\\u671f\",\"col\":8,\"labelWidth\":\"80px\",\"props\":{\"startPlaceholder\":\"\\u5f00\\u59cb\\u65e5\\u671f\",\"endPlaceholder\":\"\\u7ed3\\u675f\\u65e5\\u671f\",\"rangeSeparator\":\"\\u81f3\",\"type\":\"daterange\"},\"label\":\"\\u6ce8\\u518c\\u65f6\\u95f4\",\"prop\":\"create_time\",\"tab\":0,\"index\":6}]}],\"table\":[{\"type\":\"label\",\"title\":\"\\u666e\\u901a\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"ID\",\"prop\":\"id\",\"index\":0,\"align\":\"left\",\"header-align\":\"left\",\"is-show\":true},{\"type\":\"image\",\"title\":\"\\u56fe\\u7247\",\"labelWidth\":\"0px\",\"props\":{\"styles\":\"width:80px;height:80px;\"},\"label\":\"\\u5934\\u50cf\",\"prop\":\"avatar_text\",\"index\":1,\"align\":\"center\",\"header-align\":\"center\",\"is-show\":true},{\"type\":\"label\",\"title\":\"\\u666e\\u901a\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"\\u7528\\u6237\\u540d\",\"prop\":\"username\",\"index\":1,\"align\":\"left\",\"header-align\":\"left\",\"is-show\":true},{\"type\":\"label\",\"title\":\"\\u666e\\u901a\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"\\u624b\\u673a\\u53f7\",\"prop\":\"phone\",\"index\":2,\"is-show\":false,\"align\":\"left\",\"header-align\":\"left\"},{\"type\":\"label\",\"title\":\"\\u666e\\u901a\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"\\u6635\\u79f0\",\"prop\":\"nickname\",\"index\":4,\"is-show\":true,\"align\":\"left\",\"header-align\":\"left\"},{\"type\":\"buttons\",\"title\":\"\\u6309\\u94ae\\u7ec4\",\"labelWidth\":\"0px\",\"children\":[{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"prop\":\"1\",\"props\":{\"value\":\"\\u5df2\\u542f\\u7528\",\"type\":\"success\",\"size\":\"mini\",\"changes\":\"ajax\",\"url\":\"\\/admin\\/member\\/status?id=###&status=0\",\"tips\":\"\\u786e\\u5b9a\\u8981\\u7981\\u7528\\u6b64\\u6570\\u636e\\u5417\\uff1f\",\"ifValue\":\"status=1\",\"powerUrl\":\"\\/admin\\/member\\/status\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5df2\\u7981\\u7528\",\"type\":\"warning\",\"changes\":\"ajax\",\"url\":\"\\/admin\\/member\\/status?id=###&status=1\",\"tips\":\"\\u786e\\u5b9a\\u8981\\u542f\\u7528\\u6b64\\u6570\\u636e\\u5417\\uff1f\",\"size\":\"mini\",\"ifValue\":\"status=0\",\"powerUrl\":\"\\/admin\\/member\\/status\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5df2\\u51bb\\u7ed3\",\"type\":\"danger\",\"tips\":\"\\u786e\\u5b9a\\u8981\\u5c06\\u6b64\\u6570\\u636e\\u79fb\\u51fa\\u56de\\u6536\\u7ad9\\u5e76\\u542f\\u7528\\u5417\\uff1f\",\"url\":\"\\/admin\\/member\\/status?id=###&status=1\",\"size\":\"mini\",\"ifValue\":\"status=2\",\"powerUrl\":\"\\/admin\\/member\\/status\"}}],\"props\":[],\"label\":\"\\u72b6\\u6001\",\"prop\":\"status\",\"index\":5,\"align\":\"center\",\"header-align\":\"center\",\"is-show\":true},{\"type\":\"label\",\"title\":\"\\u666e\\u901a\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"\\u63a8\\u8350\\u4eba\",\"prop\":\"pid_username\",\"index\":6,\"is-show\":true,\"align\":\"left\",\"header-align\":\"left\"},{\"type\":\"timeText\",\"title\":\"\\u65f6\\u95f4\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":{\"format\":\"\"},\"label\":\"\\u6ce8\\u518c\\u65f6\\u95f4\",\"prop\":\"create_time\",\"index\":8,\"is-show\":true,\"align\":\"left\",\"header-align\":\"left\"},{\"type\":\"timeText\",\"title\":\"\\u65f6\\u95f4\\u6587\\u672c\",\"labelWidth\":\"0px\",\"props\":[],\"label\":\"\\u6700\\u540e\\u767b\\u5f55\\u65f6\\u95f4\",\"prop\":\"last_login_time\",\"index\":8,\"is-show\":false,\"align\":\"left\",\"header-align\":\"left\"},{\"type\":\"buttons\",\"title\":\"\\u6309\\u94ae\\u7ec4\",\"labelWidth\":\"0px\",\"children\":[{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"prop\":\"1\",\"props\":{\"value\":\"\\u7f16\\u8f91\",\"type\":\"success\",\"size\":\"mini\",\"url\":\"\\/admin\\/member\\/edit?id=###\",\"changes\":\"jump\",\"powerUrl\":\"\\/admin\\/member\\/edit\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5220\\u9664\",\"type\":\"danger\",\"size\":\"mini\",\"tips\":\"\\u786e\\u5b9a\\u8981\\u5220\\u9664\\u6b64\\u6570\\u636e\\u5417\\uff1f\\u5220\\u9664\\u540e\\u5c06\\u4e0d\\u80fd\\u6062\\u590d\\u54e6\\uff01\",\"url\":\"\\/admin\\/member\\/del?id=###\",\"changes\":\"ajax\",\"powerUrl\":\"\\/admin\\/member\\/del\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u51bb\\u7ed3\",\"type\":\"warning\",\"size\":\"mini\",\"tips\":\"\\u786e\\u5b9a\\u8981\\u51bb\\u7ed3\\u6b64\\u7528\\u6237\\u5417\\uff1f\\u51bb\\u7ed3\\u540e\\u6b64\\u7528\\u6237\\u5c06\\u65e0\\u6cd5\\u767b\\u5f55\\u54e6\\uff01\",\"url\":\"DongJieHuiYuanZhangHao?id=###\",\"changes\":\"alert\",\"width\":\"500px\",\"height\":\"40px\",\"powerUrl\":\"\\/admin\\/member\\/setFrozen\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u8bbe\\u7f6e\\u4f59\\u989d\",\"type\":\"success\",\"size\":\"mini\",\"changes\":\"alert\",\"url\":\"SheZhiHuiYuanYuE?id=###\",\"powerUrl\":\"\\/admin\\/memberWallet\\/setBalance\",\"width\":\"500px\",\"tips\":\"\\u8bbe\\u7f6e\\u4f59\\u989d\"}},{\"type\":\"button\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u8bbe\\u7f6e\\u5bc6\\u7801\",\"type\":\"success\",\"size\":\"mini\",\"changes\":\"alert\",\"tips\":\"\\u8bbe\\u7f6e\\u5bc6\\u7801\",\"url\":\"HuiYuanMiMaBianJi?id=###\",\"powerUrl\":\"\\/admin\\/member\\/setPwd\",\"width\":\"500px\"}}],\"props\":[],\"label\":\"\\u64cd\\u4f5c\",\"prop\":\"operation\",\"index\":9,\"is-show\":true}],\"leftButton\":[{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u6dfb\\u52a0\",\"type\":\"success\",\"size\":\"small\",\"icon\":\"el-icon-plus\",\"changes\":\"jump\",\"url\":\"\\/admin\\/member\\/add\",\"powerUrl\":\"\\/admin\\/member\\/add\"},\"label\":\"\\u6dfb\\u52a0\",\"prop\":\"add\",\"index\":0,\"is-show\":true}],\"rightButton\":[{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5bfc\\u5165\",\"type\":\"success\",\"size\":\"mini\",\"icon\":\"el-icon-upload2\"},\"label\":\"\\u5bfc\\u5165\",\"prop\":\"import\",\"index\":0},{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5bfc\\u51fa\",\"type\":\"success\",\"size\":\"mini\",\"icon\":\"el-icon-download\"},\"label\":\"\\u5bfc\\u51fa\",\"prop\":\"export\",\"index\":1},{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u6253\\u5370\",\"type\":\"success\",\"size\":\"mini\",\"icon\":\"el-icon-printer\"},\"label\":\"\\u6253\\u5370\",\"prop\":\"printing\",\"index\":2},{\"type\":\"button\",\"title\":\"\\u6309\\u94ae\",\"labelWidth\":\"0px\",\"props\":{\"value\":\"\\u5b57\\u6bb5\",\"type\":\"success\",\"size\":\"mini\",\"icon\":\"el-icon-s-grid\"},\"label\":\"\\u5b57\\u6bb5\",\"prop\":\"field\",\"index\":3,\"is-show\":true}]}', '1', '1601882542', '1606077761', '0', '1', '');
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '会员添加', 'HuiYuanTianJia', 'form', '/admin/member/getInfo', '/admin/member/add', '[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"select\",\"title\":\"\\u4e0b\\u62c9\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"0\"},\"options\":[{\"label\":\"\\u8d26\\u53f7\\u6ce8\\u518c\",\"value\":\"0\"},{\"label\":\"\\u624b\\u673a\\u6ce8\\u518c\",\"value\":\"1\"},{\"label\":\"\\u90ae\\u7bb1\\u6ce8\\u518c\",\"value\":\"2\"},{\"label\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\",\"value\":\"3\"}],\"label\":\"\\u6ce8\\u518c\\u6e20\\u9053\",\"prop\":\"reg_type\",\"tab\":\"0\",\"index\":0,\"datatype\":2,\"dictattr\":\"\",\"dictlabel\":\"\",\"dictvalue\":\"\",\"dictapi\":\"\",\"rules\":[{\"message\":\"\\u8bf7\\u9009\\u62e9\\u6ce8\\u518c\\u6e20\\u9053\",\"trigger\":[\"change\",\"blur\"],\"required\":true,\"pattern\":\"^[0-9]*$\"}]},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u7528\\u6237\\u540d\",\"prop\":\"username\",\"tab\":\"0\",\"index\":1,\"rules\":[{\"message\":\"\\u8bf7\\u8f93\\u5165\\u7528\\u6237\\u540d\",\"trigger\":[\"change\",\"blur\"],\"type\":\"string\",\"required\":true,\"min\":6,\"max\":20}]},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u6635\\u79f0\",\"prop\":\"nickname\",\"tab\":\"0\",\"index\":2,\"rules\":[{\"message\":\"\\u8bf7\\u8f93\\u5165\\u6635\\u79f0\",\"min\":2,\"required\":true,\"trigger\":[\"change\",\"blur\"],\"type\":\"string\"}]},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":11},\"label\":\"\\u624b\\u673a\\u53f7\",\"prop\":\"phone\",\"tab\":\"0\",\"index\":3},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u5bc6\\u7801\",\"prop\":\"password\",\"tab\":\"0\",\"index\":4,\"rules\":[{\"message\":\"\\u5bc6\\u7801\\u4e0d\\u80fd\\u4e3a\\u7a7a\",\"min\":6,\"max\":20,\"required\":true,\"trigger\":[\"change\"],\"type\":\"string\"}]},{\"type\":\"upload\",\"title\":\"\\u4e0a\\u4f20\",\"col\":24,\"labelWidth\":\"100px\",\"value\":\"\",\"props\":{\"action\":\"\",\"autoUpload\":false,\"limit\":1,\"UploadType\":\"\\u56fe\\u7247\",\"isPrivate\":1},\"label\":\"\\u5934\\u50cf\",\"prop\":\"avatar\",\"tab\":\"0\",\"index\":5},{\"type\":\"date\",\"title\":\"\\u65e5\\u671f\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"type\":\"date\"},\"label\":\"\\u751f\\u65e5\",\"prop\":\"birthday\",\"tab\":\"0\",\"index\":6},{\"type\":\"radio\",\"title\":\"\\u5355\\u9009\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"0\"},\"options\":[{\"label\":\"\\u4fdd\\u5bc6\",\"value\":\"0\"},{\"label\":\"\\u7537\",\"value\":\"1\"},{\"label\":\"\\u5973\",\"value\":\"2\"}],\"label\":\"\\u6027\\u522b\",\"prop\":\"six\",\"tab\":\"0\",\"index\":6,\"datatype\":2,\"dictattr\":\"\",\"dictlabel\":\"\",\"dictvalue\":\"\",\"dictapi\":\"\"},{\"type\":\"select\",\"title\":\"\\u4e0b\\u62c9\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"1\"},\"options\":[{\"label\":\"\\u9009\\u9879\\u4e00\",\"value\":\"1\"},{\"label\":\"\\u9009\\u9879\\u4e8c\",\"value\":\"2\"}],\"label\":\"\\u5ba1\\u6838\\u72b6\\u6001\",\"prop\":\"auth\",\"tab\":\"0\",\"index\":9,\"datatype\":1,\"dictapi\":\"authAddText\",\"dictattr\":\"list\",\"dictlabel\":\"title\",\"dictvalue\":\"id\"},{\"type\":\"select\",\"title\":\"\\u4e0b\\u62c9\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"1\"},\"options\":[{\"label\":\"\\u9009\\u9879\\u4e00\",\"value\":\"1\"},{\"label\":\"\\u9009\\u9879\\u4e8c\",\"value\":\"2\"}],\"label\":\"\\u72b6\\u6001\",\"prop\":\"status\",\"tab\":\"0\",\"index\":7,\"datatype\":1,\"dictapi\":\"statusAddText\",\"dictattr\":\"list\"}]}]', '1', '1601883105', '1606070288', '0', '1', '');
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '会员编辑', 'HuiYuanBianJi', 'form', '/admin/member/getInfo', '/admin/member/edit', '[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":11},\"label\":\"\\u624b\\u673a\\u53f7\",\"prop\":\"phone\",\"tab\":\"0\",\"index\":0},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u6635\\u79f0\",\"prop\":\"nickname\",\"tab\":\"0\",\"index\":1,\"rules\":[{\"message\":\"\\u8bf7\\u8f93\\u5165\\u6635\\u79f0\",\"min\":2,\"required\":true,\"trigger\":[\"change\",\"blur\"],\"type\":\"string\"}]},{\"type\":\"upload\",\"title\":\"\\u4e0a\\u4f20\",\"col\":24,\"labelWidth\":\"100px\",\"value\":\"\",\"props\":{\"action\":\"\",\"autoUpload\":false,\"limit\":1,\"UploadType\":\"\\u56fe\\u7247\",\"isPrivate\":1},\"label\":\"\\u5934\\u50cf\",\"prop\":\"avatar\",\"tab\":\"0\",\"index\":5},{\"type\":\"date\",\"title\":\"\\u65e5\\u671f\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"type\":\"date\"},\"label\":\"\\u751f\\u65e5\",\"prop\":\"birthday\",\"tab\":\"0\",\"index\":6},{\"type\":\"radio\",\"title\":\"\\u5355\\u9009\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"0\"},\"options\":[{\"label\":\"\\u4fdd\\u5bc6\",\"value\":\"0\"},{\"label\":\"\\u7537\",\"value\":\"1\"},{\"label\":\"\\u5973\",\"value\":\"2\"}],\"label\":\"\\u6027\\u522b\",\"prop\":\"six\",\"tab\":\"0\",\"index\":6,\"datatype\":2,\"dictattr\":\"\",\"dictlabel\":\"\",\"dictvalue\":\"\",\"dictapi\":\"\"}]}]', '1', '1601893661', '1606072799', '0', '1', '');
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '冻结会员账号', 'DongJieHuiYuanZhangHao', 'form', '', '/admin/member/setFrozen', '[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"80px\",\"props\":{\"showWordLimit\":true,\"maxlength\":50},\"label\":\"\\u51bb\\u7ed3\\u8bf4\\u660e\",\"prop\":\"note\",\"tab\":\"0\",\"index\":0}]}]', '1', '1601882953', '1606077740', '0', '1', '');
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '会员密码编辑', 'HuiYuanMiMaBianJi', 'form', '', '/admin/member/setPwd', '[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"80px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u65b0\\u5bc6\\u7801\",\"prop\":\"password\",\"tab\":\"0\",\"index\":0,\"rules\":[{\"message\":\"\\u65b0\\u5bc6\\u7801\\u4e0d\\u80fd\\u4e3a\\u7a7a\",\"required\":true,\"trigger\":[\"change\",\"blur\"],\"type\":\"string\"}]},{\"type\":\"input\",\"title\":\"\\u6587\\u672c\\u6846\",\"col\":24,\"labelWidth\":\"80px\",\"props\":{\"showWordLimit\":true,\"maxlength\":20},\"label\":\"\\u786e\\u8ba4\\u5bc6\\u7801\",\"prop\":\"pwd\",\"tab\":\"0\",\"index\":1,\"rules\":[{\"message\":\"\\u786e\\u8ba4\\u5bc6\\u7801\\u4e0d\\u80fd\\u4e3a\\u7a7a\",\"trigger\":[\"change\",\"blur\"],\"type\":\"string\",\"required\":true}]}]}]', '1', '1601882972', '1606077495', '0', '1', '');
INSERT INTO `__PREFIX__templates` ( `title`, `alias`, `template_type`, `index_url`, `ajax_url`, `template_content`, `status`, `create_time`, `update_time`, `sort`, `is_footer`, `note`) VALUES ( '设置会员余额', 'SheZhiHuiYuanYuE', 'form', '/admin/memberWallet/getInfo?id=###', '/admin/memberWallet/setBalance', '[{\"title\":\"\\u57fa\\u672c\\u914d\\u7f6e\",\"id\":\"0\",\"content\":[{\"type\":\"label\",\"title\":\"\\u6587\\u672c\",\"col\":24,\"labelWidth\":\"100px\",\"props\":[],\"label\":\"\\u5f53\\u524d\\u4f59\\u989d\",\"prop\":\"balance\",\"tab\":\"0\",\"index\":0},{\"type\":\"radio\",\"title\":\"\\u5355\\u9009\\u6846\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"value\":\"1\"},\"options\":[{\"label\":\"\\u589e\\u52a0\",\"value\":\"1\"},{\"label\":\"\\u51cf\\u5c11\",\"value\":\"2\"}],\"label\":\"\\u8bbe\\u7f6e\\u7c7b\\u578b\",\"prop\":\"diff_type\",\"tab\":\"0\",\"index\":1,\"datatype\":2,\"dictattr\":\"\",\"dictlabel\":\"\",\"dictvalue\":\"\",\"dictapi\":\"\"},{\"type\":\"number\",\"title\":\"\\u6570\\u5b57\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"min\":0,\"value\":0},\"label\":\"\\u8bbe\\u7f6e\\u6570\\u91cf\",\"prop\":\"diff\",\"tab\":\"0\",\"index\":2,\"rules\":[{\"message\":\"\\u8bf7\\u8f93\\u5165\\u6570\\u91cf\",\"required\":true,\"trigger\":[\"change\",\"blur\"],\"type\":\"number\"}]},{\"type\":\"input\",\"title\":\"\\u591a\\u884c\\u6587\\u672c\",\"col\":24,\"labelWidth\":\"100px\",\"props\":{\"rows\":3,\"value\":\"\\u8865\\u5dee\\u4ef7\",\"type\":\"textarea\",\"showWordLimit\":true,\"maxlength\":100},\"label\":\"\\u5907\\u6ce8\\u8bf4\\u660e\",\"prop\":\"note\",\"tab\":\"0\",\"index\":3,\"rules\":[{\"message\":\"\\u8bf7\\u8f93\\u5165\\u5907\\u6ce8\\u8bf4\\u660e\",\"trigger\":[\"change\",\"blur\"],\"type\":\"string\",\"required\":true}]}]}]', '1', '1606073596', '1606076364', '0', '1', '');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;