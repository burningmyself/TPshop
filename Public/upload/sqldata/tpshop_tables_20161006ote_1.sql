# -----------------------------------------------------------
# Description:备份的数据表[结构] tp_dbperiods,tp_dbtype,tp_user_level,tp_wx_menu,tp_wx_user
# Description:备份的数据表[数据] tp_dbperiods,tp_dbtype,tp_user_level,tp_wx_menu,tp_wx_user
# Time: 2016-10-06 12:14:06
# -----------------------------------------------------------
# SQLFile Label：#1
# -----------------------------------------------------------


# 表的结构 tp_dbperiods 
DROP TABLE IF EXISTS `tp_dbperiods`;
CREATE TABLE `tp_dbperiods` (
  `dbshop_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'dbshop_id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '夺宝名称',
  `dbtype_id` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝类型',
  `periods` int(11) NOT NULL DEFAULT '10000000' COMMENT '期数',
  `db_nums` text NOT NULL COMMENT '夺宝号码',
  `db_total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '夺宝总价格',
  `db_price` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝价格',
  `db_count` int(11) NOT NULL DEFAULT '1' COMMENT '夺宝数量',
  `db_limit` int(11) NOT NULL DEFAULT '1' COMMENT '最大夺宝数量',
  `ydb_count` int(11) NOT NULL DEFAULT '0' COMMENT '已经夺宝数量',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝结束时间',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否中奖1进行中，2揭晓,3已发货',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '中奖人id',
  `nickname` varchar(13) NOT NULL DEFAULT '' COMMENT '中奖人昵称',
  `buy_count` int(11) NOT NULL DEFAULT '0' COMMENT '参与次数',
  `win_time` int(11) NOT NULL DEFAULT '0' COMMENT '中奖时间',
  `ip` varchar(20) NOT NULL DEFAULT ':::' COMMENT 'ip地址',
  `time_sum` int(20) NOT NULL DEFAULT '0' COMMENT '中奖时间和',
  `win_code` char(20) NOT NULL DEFAULT '' COMMENT '中奖号码',
  `win_content` varchar(225) NOT NULL COMMENT '中奖内容',
  PRIMARY KEY (`dbshop_id`),
  KEY `goods_id` (`goods_id`),
  KEY `dbtype_id` (`dbtype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='夺宝期数' ;

# 表的结构 tp_dbtype 
DROP TABLE IF EXISTS `tp_dbtype`;
CREATE TABLE `tp_dbtype` (
  `dbtype_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'dbtype_id',
  `title` varchar(20) NOT NULL COMMENT '区域名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `imgurl` varchar(255) NOT NULL COMMENT '图片地址',
  `display` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '数据状态',
  `uint` int(11) NOT NULL DEFAULT '1' COMMENT '几元夺宝',
  PRIMARY KEY (`dbtype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='夺宝类型' ;

# 表的结构 tp_user_level 
DROP TABLE IF EXISTS `tp_user_level`;
CREATE TABLE `tp_user_level` (
  `level_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `level_name` varchar(30) DEFAULT NULL COMMENT '头衔名称',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '等级必要金额',
  `discount` smallint(4) DEFAULT NULL COMMENT '折扣',
  `describe` varchar(200) DEFAULT NULL COMMENT '头街 描述',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '人数',
  `rate` smallint(4) NOT NULL DEFAULT '0' COMMENT '会员等级分成比例',
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ;

# 表的结构 tp_wx_menu 
DROP TABLE IF EXISTS `tp_wx_menu`;
CREATE TABLE `tp_wx_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `level` tinyint(1) DEFAULT '1' COMMENT '菜单级别',
  `name` varchar(50) NOT NULL COMMENT 'name',
  `sort` int(5) DEFAULT '0' COMMENT '排序',
  `type` varchar(20) DEFAULT '' COMMENT '0 view 1 click',
  `value` varchar(255) DEFAULT NULL COMMENT 'value',
  `token` varchar(50) NOT NULL COMMENT 'token',
  `pid` int(11) DEFAULT '0' COMMENT '上级菜单',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ;

# 表的结构 tp_wx_user 
DROP TABLE IF EXISTS `tp_wx_user`;
CREATE TABLE `tp_wx_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `uid` int(11) NOT NULL COMMENT 'uid',
  `wxname` varchar(60) NOT NULL COMMENT '公众号名称',
  `aeskey` varchar(256) NOT NULL DEFAULT '' COMMENT 'aeskey',
  `encode` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'encode',
  `appid` varchar(50) NOT NULL DEFAULT '' COMMENT 'appid',
  `appsecret` varchar(50) NOT NULL DEFAULT '' COMMENT 'appsecret',
  `wxid` varchar(64) NOT NULL COMMENT '公众号原始ID',
  `weixin` char(64) NOT NULL COMMENT '微信号',
  `headerpic` char(255) NOT NULL COMMENT '头像地址',
  `token` char(255) NOT NULL COMMENT 'token',
  `w_token` varchar(150) NOT NULL DEFAULT '' COMMENT '微信对接token',
  `create_time` int(11) NOT NULL COMMENT 'create_time',
  `updatetime` int(11) NOT NULL COMMENT 'updatetime',
  `tplcontentid` varchar(2) NOT NULL COMMENT '内容模版ID',
  `share_ticket` varchar(150) NOT NULL COMMENT '分享ticket',
  `share_dated` char(15) NOT NULL COMMENT 'share_dated',
  `authorizer_access_token` varchar(200) NOT NULL COMMENT 'authorizer_access_token',
  `authorizer_refresh_token` varchar(200) NOT NULL COMMENT 'authorizer_refresh_token',
  `authorizer_expires` char(10) NOT NULL COMMENT 'authorizer_expires',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `web_access_token` varchar(200) NOT NULL COMMENT ' 网页授权token',
  `web_refresh_token` varchar(200) NOT NULL COMMENT 'web_refresh_token',
  `web_expires` int(11) NOT NULL COMMENT '过期时间',
  `qr` varchar(200) NOT NULL COMMENT 'qr',
  `menu_config` text COMMENT '菜单',
  `wait_access` tinyint(1) DEFAULT '0' COMMENT '微信接入状态,0待接入1已接入',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `uid_2` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='微信公共帐号' ;



# 转存表中的数据：tp_dbperiods 


# 转存表中的数据：tp_dbtype 
INSERT INTO `tp_dbtype` VALUES ('12','一元夺宝','0','/Public/upload/category/2016/09-29/57ec6fb270c1b.png','1','0','0','0','1');
INSERT INTO `tp_dbtype` VALUES ('13','五元专区','0','/Public/upload/category/2016/09-29/57ec76e1d7e0c.png','1','0','0','0','5');


# 转存表中的数据：tp_user_level 
INSERT INTO `tp_user_level` VALUES ('1','注册会员','0.00','100','注册会员','0','0');
INSERT INTO `tp_user_level` VALUES ('2','铜牌会员','500.00','99','铜牌会员','1','1');
INSERT INTO `tp_user_level` VALUES ('3','白银会员','3000.00','98','白银会员','10','2');
INSERT INTO `tp_user_level` VALUES ('4','黄金会员','10000.00','97','黄金会员','100','3');
INSERT INTO `tp_user_level` VALUES ('5','钻石会员','50000.00','95','钻石会员','1000','5');
INSERT INTO `tp_user_level` VALUES ('6','超级VIP','100000.00','90','超级VIP','10000','10');


# 转存表中的数据：tp_wx_menu 
INSERT INTO `tp_wx_menu` VALUES ('28','1','搜豹官网','0','view','http://www.tp-shop.cn/index.php/Mobile/','eesops1462769263','0');
INSERT INTO `tp_wx_menu` VALUES ('29','1','TPshop商城','0','view','http://www.tp-shop.cn/index.php/Mobile/','eesops1462769263','0');
INSERT INTO `tp_wx_menu` VALUES ('30','1','你好TP','0','click','你好','eesops1462769263','28');
INSERT INTO `tp_wx_menu` VALUES ('33','1','开发中','0','view','http://1519ey4059.iok.la/index.php/mobile','riklqc1474257597','0');


# 转存表中的数据：tp_wx_user 
