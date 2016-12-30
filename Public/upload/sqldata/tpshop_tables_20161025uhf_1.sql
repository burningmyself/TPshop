# -----------------------------------------------------------
# Description:备份的数据表[结构] tp_payment,tp_plugin,tp_wx_img,tp_wx_keyword,tp_wx_menu,tp_wx_msg,tp_wx_news,tp_wx_text,tp_wx_user
# Description:备份的数据表[数据] tp_payment,tp_plugin,tp_wx_img,tp_wx_keyword,tp_wx_menu,tp_wx_msg,tp_wx_news,tp_wx_text,tp_wx_user
# Time: 2016-10-25 11:11:52
# -----------------------------------------------------------
# SQLFile Label：#1
# -----------------------------------------------------------


# 表的结构 tp_payment 
DROP TABLE IF EXISTS `tp_payment`;
CREATE TABLE `tp_payment` (
  `pay_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `pay_code` varchar(20) NOT NULL DEFAULT '' COMMENT '支付code',
  `pay_name` varchar(120) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `pay_fee` varchar(10) NOT NULL DEFAULT '0' COMMENT '手续费',
  `pay_desc` text NOT NULL COMMENT '描述',
  `pay_order` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'pay_coder',
  `pay_config` text NOT NULL COMMENT '配置',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开启',
  `is_cod` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否货到付款',
  `is_online` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否在线支付',
  PRIMARY KEY (`pay_id`),
  UNIQUE KEY `pay_code` (`pay_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

# 表的结构 tp_plugin 
DROP TABLE IF EXISTS `tp_plugin`;
CREATE TABLE `tp_plugin` (
  `code` varchar(13) DEFAULT NULL COMMENT '插件编码',
  `name` varchar(55) DEFAULT NULL COMMENT '中文名字',
  `version` varchar(255) DEFAULT NULL COMMENT '插件的版本',
  `author` varchar(30) DEFAULT NULL COMMENT '插件作者',
  `config` text COMMENT '配置信息',
  `config_value` text COMMENT '配置值信息',
  `desc` varchar(255) DEFAULT NULL COMMENT '插件描述',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否启用',
  `type` varchar(50) DEFAULT NULL COMMENT '插件类型 payment支付 login 登陆 shipping物流',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `bank_code` text COMMENT '网银配置信息',
  `scene` tinyint(1) DEFAULT '0' COMMENT '使用场景 0 PC+手机 1 手机 2 PC'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

# 表的结构 tp_wx_img 
DROP TABLE IF EXISTS `tp_wx_img`;
CREATE TABLE `tp_wx_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `desc` text NOT NULL COMMENT '简介',
  `pic` char(255) NOT NULL COMMENT '封面图片',
  `url` char(255) NOT NULL COMMENT '图文外链地址',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `uptatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `token` char(30) NOT NULL COMMENT 'token',
  `title` varchar(60) NOT NULL COMMENT '标题',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_name` varchar(50) DEFAULT NULL COMMENT '商品名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='微信图文' ;

# 表的结构 tp_wx_keyword 
DROP TABLE IF EXISTS `tp_wx_keyword`;
CREATE TABLE `tp_wx_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `pid` int(11) NOT NULL COMMENT '对应表ID',
  `token` varchar(60) NOT NULL COMMENT 'token',
  `type` varchar(30) DEFAULT 'TEXT' COMMENT '关键词操作类型',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=utf8 ;

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

# 表的结构 tp_wx_msg 
DROP TABLE IF EXISTS `tp_wx_msg`;
CREATE TABLE `tp_wx_msg` (
  `msgid` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '系统用户ID',
  `titile` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sendtime` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `issend` tinyint(1) DEFAULT '0' COMMENT '0未发送1成功2失败',
  `sendtype` tinyint(1) DEFAULT '1' COMMENT '0单人1所有',
  PRIMARY KEY (`msgid`),
  KEY `uid` (`admin_id`),
  KEY `createymd` (`sendtime`),
  KEY `fake_id` (`titile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

# 表的结构 tp_wx_news 
DROP TABLE IF EXISTS `tp_wx_news`;
CREATE TABLE `tp_wx_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT 'keyword',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `uptatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `token` char(30) NOT NULL COMMENT 'token',
  `img_id` varchar(50) DEFAULT NULL COMMENT '图文组合id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信图文' ;

# 表的结构 tp_wx_text 
DROP TABLE IF EXISTS `tp_wx_text`;
CREATE TABLE `tp_wx_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `uname` varchar(90) NOT NULL COMMENT '用户名',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `precisions` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'precisions',
  `text` text NOT NULL COMMENT 'text',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `updatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `click` int(11) NOT NULL COMMENT '点击',
  `token` char(30) NOT NULL COMMENT 'token',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='文本回复表' ;

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='微信公共帐号' ;



# 转存表中的数据：tp_payment 


# 转存表中的数据：tp_plugin 
INSERT INTO `tp_plugin` VALUES ('alipay','支付宝支付','1.0','jy_pwn','a:6:{i:0;a:4:{s:4:"name";s:14:"alipay_account";s:5:"label";s:15:"支付宝帐户";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:10:"alipay_key";s:5:"label";s:21:"交易安全校验码";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:2;a:4:{s:4:"name";s:14:"alipay_partner";s:5:"label";s:17:"合作者身份ID";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:3;a:4:{s:4:"name";s:18:"alipay_private_key";s:5:"label";s:6:"秘钥";s:4:"type";s:8:"textarea";s:5:"value";s:0:"";}i:4;a:4:{s:4:"name";s:17:"alipay_pay_method";s:5:"label";s:19:" 选择接口类型";s:4:"type";s:6:"select";s:6:"option";a:2:{i:0;s:24:"使用担保交易接口";i:1;s:30:"使用即时到帐交易接口";}}i:5;a:4:{s:4:"name";s:7:"is_bank";s:5:"label";s:18:"是否开通网银";s:4:"type";s:6:"select";s:6:"option";a:2:{i:0;s:3:"否";i:1;s:3:"是";}}}','a:6:{s:14:"alipay_account";s:0:"";s:10:"alipay_key";s:0:"";s:14:"alipay_partner";s:0:"";s:18:"alipay_private_key";s:0:"";s:17:"alipay_pay_method";s:1:"2";s:7:"is_bank";s:1:"2";}','支付宝插件 ','1','payment','logo.jpg','a:8:{s:12:"招商银行";s:9:"CMB-DEBIT";s:18:"中国工商银行";s:10:"ICBC-DEBIT";s:12:"交通银行";s:10:"COMM-DEBIT";s:18:"中国建设银行";s:9:"CCB-DEBIT";s:18:"中国民生银行";s:4:"CMBC";s:12:"中国银行";s:9:"BOC-DEBIT";s:18:"中国农业银行";s:3:"ABC";s:12:"上海银行";s:6:"SHBANK";}','2');
INSERT INTO `tp_plugin` VALUES ('cod','到货付款','1.0','IT宇宙人','a:1:{i:0;a:4:{s:4:"name";s:9:"code_desc";s:5:"label";s:12:"配送描述";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:1:{s:9:"code_desc";s:9:"545234234";}','货到付款插件 ','1','payment','logo.jpg','N;','0');
INSERT INTO `tp_plugin` VALUES ('alipay','支付宝快捷登陆','1.0','彭老师','a:2:{i:0;a:4:{s:4:"name";s:14:"alipay_partner";s:5:"label";s:17:"合作者身份ID";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:10:"alipay_key";s:5:"label";s:15:"安全检验码";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:2:{s:14:"alipay_partner";s:0:"";s:10:"alipay_key";s:0:"";}','支付宝快捷登陆插件 ','1','login','logo.jpg','N;','0');
INSERT INTO `tp_plugin` VALUES ('weixin','微信登陆','1.0','彭老师','a:2:{i:0;a:4:{s:4:"name";s:5:"appid";s:5:"label";s:17:"开放平台appid";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:6:"secret";s:5:"label";s:18:"开放平台secret";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:2:{s:5:"appid";s:18:"wx354a2d2ab2d5fca6";s:6:"secret";s:32:"71165595bd1b774282fbd134c12f51ef";}','微信登陆插件 ','1','login','logo.jpg','N;','0');
INSERT INTO `tp_plugin` VALUES ('shentong','申通物流','1.0','宇宙人','N;','','申通物流插件 ','1','shipping','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('shunfeng','顺丰物流','1.0','shunfeng','N;','','顺丰物流插件 ','1','shipping','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('qq','QQ登陆','1.0','彭老师','a:2:{i:0;a:4:{s:4:"name";s:6:"app_id";s:5:"label";s:6:"app_id";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:10:"app_secret";s:5:"label";s:10:"app_secret";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:2:{s:6:"app_id";s:0:"";s:10:"app_secret";s:0:"";}','QQ登陆插件 ','1','login','logo.jpg','N;','0');
INSERT INTO `tp_plugin` VALUES ('alipayMobile','手机网站支付宝','1.0','宇宙人','a:6:{i:0;a:4:{s:4:"name";s:14:"alipay_account";s:5:"label";s:15:"支付宝帐户";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:10:"alipay_key";s:5:"label";s:21:"交易安全校验码";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:2;a:4:{s:4:"name";s:14:"alipay_partner";s:5:"label";s:17:"合作者身份ID";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:3;a:4:{s:4:"name";s:18:"alipay_private_key";s:5:"label";s:6:"秘钥";s:4:"type";s:8:"textarea";s:5:"value";s:0:"";}i:4;a:4:{s:4:"name";s:17:"alipay_pay_method";s:5:"label";s:19:" 选择接口类型";s:4:"type";s:6:"select";s:6:"option";a:2:{i:0;s:24:"使用担保交易接口";i:1;s:30:"使用即时到帐交易接口";}}i:5;a:4:{s:4:"name";s:7:"is_bank";s:5:"label";s:18:"是否开通网银";s:4:"type";s:6:"select";s:6:"option";a:2:{i:0;s:3:"否";i:1;s:3:"是";}}}','a:6:{s:14:"alipay_account";s:0:"";s:10:"alipay_key";s:0:"";s:14:"alipay_partner";s:0:"";s:18:"alipay_private_key";s:0:"";s:17:"alipay_pay_method";s:1:"2";s:7:"is_bank";s:1:"2";}','手机端网站支付宝 ','1','payment','logo.jpg','N;','1');
INSERT INTO `tp_plugin` VALUES ('unionpay','银联在线支付','1.0','奇闻科技','a:4:{i:0;a:4:{s:4:"name";s:12:"unionpay_mid";s:5:"label";s:9:"商户号";s:4:"type";s:4:"text";s:5:"value";s:15:"777290058130619";}i:1;a:4:{s:4:"name";s:21:"unionpay_cer_password";s:5:"label";s:25:" 商户私钥证书密码";s:4:"type";s:4:"text";s:5:"value";s:6:"000000";}i:2;a:4:{s:4:"name";s:13:"unionpay_user";s:5:"label";s:19:" 企业网银账号";s:4:"type";s:4:"text";s:5:"value";s:12:"123456789001";}i:3;a:4:{s:4:"name";s:17:"unionpay_password";s:5:"label";s:19:" 企业网银密码";s:4:"type";s:4:"text";s:5:"value";s:6:"789001";}}','a:4:{s:12:"unionpay_mid";s:0:"";s:21:"unionpay_cer_password";s:0:"";s:13:"unionpay_user";s:0:"";s:17:"unionpay_password";s:0:"";}','银联在线支付插件 ','1','payment','logo.jpg','N;','0');
INSERT INTO `tp_plugin` VALUES ('helloworld','HelloWorld插件','v1.2.0,v1.2.1','IT宇宙人','N;','','适合v1.2.0 , v1.2.1','0','function','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('tenpay','PC端财付通','1.0','IT宇宙人','a:2:{i:0;a:4:{s:4:"name";s:7:"partner";s:5:"label";s:7:"partner";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:3:"key";s:5:"label";s:3:"key";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:2:{s:7:"partner";s:0:"";s:3:"key";s:0:"";}','PC端财付通插件 ','1','payment','logo.jpg','N;','2');
INSERT INTO `tp_plugin` VALUES ('bestexpress','百世汇通','1.0','bestexpress','N;','','百世汇通插件 ','0','shipping','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('tiantian','天天物流','1.0','tiantian','N;','','天天快递插件 ','0','shipping','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('ztoexpress','中通快递','1.0','ztoexpress','N;','','中通快递插件 ','0','shipping','logo.jpg','N;','');
INSERT INTO `tp_plugin` VALUES ('weixin','微信支付','1.0','IT宇宙人','a:4:{i:0;a:4:{s:4:"name";s:5:"appid";s:5:"label";s:20:"绑定支付的APPID";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:1;a:4:{s:4:"name";s:5:"mchid";s:5:"label";s:9:"商户号";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:2;a:4:{s:4:"name";s:3:"key";s:5:"label";s:18:"商户支付密钥";s:4:"type";s:4:"text";s:5:"value";s:0:"";}i:3;a:4:{s:4:"name";s:9:"appsecret";s:5:"label";s:57:"公众帐号secert（仅JSAPI支付的时候需要配置)";s:4:"type";s:4:"text";s:5:"value";s:0:"";}}','a:4:{s:5:"appid";s:18:"wx354a2d2ab2d5fca6";s:5:"mchid";s:10:"1349938201";s:3:"key";s:32:"Xph123456789Xph12345678912345679";s:9:"appsecret";s:32:"71165595bd1b774282fbd134c12f51ef";}','PC端+微信公众号支付','1','payment','logo.jpg','N;','0');


# 转存表中的数据：tp_wx_img 
INSERT INTO `tp_wx_img` VALUES ('18','改变关键字','这里是描述秒速改变改变','/Public/upload/banner/2015/11-10/5641dff44e322.jpg','http://www.tpshop.com/index.php/Admin/Wechat/add_img/id/326/edit/1','1447159325','1447162878','vjotae1439949952','标题改变','0','');
INSERT INTO `tp_wx_img` VALUES ('21','iphone','漂亮的手机1千块钱抢购了.','http://www.tp-shop.cn/Public/upload/weixin/2016/05-28/574943d30ded2.jpg','http://demo.tp-shop.cn/index.php/Home/Goods/goodsInfo/id/1.html','1464419295','','eesops1462769263','iphone 1千块限时抢购','0','');


# 转存表中的数据：tp_wx_keyword 
INSERT INTO `tp_wx_keyword` VALUES ('280','孤鸿寡鹄','5','vjotae1439949952','TEXT');
INSERT INTO `tp_wx_keyword` VALUES ('281','包包','6','vjotae1439949952','TEXT');
INSERT INTO `tp_wx_keyword` VALUES ('324','车型的谁','9','vjotae1439949952','TEXT');
INSERT INTO `tp_wx_keyword` VALUES ('325','你好','10','eesops1462769263','TEXT');
INSERT INTO `tp_wx_keyword` VALUES ('326','你坏','11','eesops1462769263','TEXT');
INSERT INTO `tp_wx_keyword` VALUES ('328','iphone','21','eesops1462769263','IMG');


# 转存表中的数据：tp_wx_menu 
INSERT INTO `tp_wx_menu` VALUES ('28','1','搜豹官网','0','view','http://www.tp-shop.cn/index.php/Mobile/','eesops1462769263','0');
INSERT INTO `tp_wx_menu` VALUES ('29','1','TPshop商城','0','view','http://www.tp-shop.cn/index.php/Mobile/','eesops1462769263','0');
INSERT INTO `tp_wx_menu` VALUES ('30','1','你好TP','0','click','你好','eesops1462769263','28');
INSERT INTO `tp_wx_menu` VALUES ('33','1','开发中','0','view','http://1519ey4059.iok.la/index.php/mobile','riklqc1474257597','0');


# 转存表中的数据：tp_wx_msg 


# 转存表中的数据：tp_wx_news 


# 转存表中的数据：tp_wx_text 
INSERT INTO `tp_wx_text` VALUES ('5','27','','孤鸿寡鹄','0','啊飒飒','1439958307','1439958307','0','vjotae1439949952');
INSERT INTO `tp_wx_text` VALUES ('6','13','','sas','0','sasqa','1447072140','1447072140','0','tiyykb1446817155');
INSERT INTO `tp_wx_text` VALUES ('9','0','','车型的谁','0','好久好久双方都发生sfdgdfd','1447078190','','0','vjotae1439949952');
INSERT INTO `tp_wx_text` VALUES ('10','0','','你好','0','你也很好,','1462772293','','0','eesops1462769263');
INSERT INTO `tp_wx_text` VALUES ('11','0','','你坏','0','你也很坏','1462777159','','0','eesops1462769263');


# 转存表中的数据：tp_wx_user 
