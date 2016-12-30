<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/9/20
 * Time: 18:25
 */

namespace Mobile\Controller;

use Think\AjaxPage;
use Think\Log;
use Think\Page;
use Home\Logic\UsersLogic;

class OneShopController extends MobileBaseController
{
    /**
     * 析构流函数
     */
    public function __construct()
    {
        parent::__construct();
        parent::_initialize();
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
        }
        // 导入具体的支付类文件
        include_once "plugins/payment/weixin/weixin.class.php"; // D:\wamp\www\svn_tpshop\www\plugins\payment\alipay\alipayPayment.class.php
        $code = '\\' . "weixin"; // \alipay
        $this->payment = new $code();
    }

    public function index_old()
    {
        $dbtype = M('dbtype')->field('dbtype_id,name,img_url1')->where('display=1')->order('sort DESC')->select();

        $this->assign('dbtype', $dbtype);
        $this->display();
    }

    public function index()
    {
        //获取广告 500的广告数量
        $ad_count = M('ad')->where("pid=500")->count();
        $menus = M('dbtype')->where('display=1')->field('dbtype_id,name,img_url1,img_url2')->select();
        //获取最近中奖名单前五名
        $win_infos = M('dborder')->where(" is_win>0 ")->field("nickname,name")->order("order_id desc")->limit(5)->select();
        $this->assign('win_infos', $win_infos);
        $this->assign('ad_count', $ad_count);
        $this->assign('menus', $menus);
        $this->display();
    }

    public function get_dbshoplist()
    {
        $type = I('get.type', 0);
        $by_or_id = I('get.by_or_id', '');
        $page = I('get.page', 1);
        $where = "p.is_show=1 and p.db_count>p.ydb_count ";
        $order = " dbshop_id desc ";
        switch ($type) {
            case 1:
                $where = $where . " and p.is_hot=1 ";
                break;
            case 2:
                $order = "width desc ";
                break;
            case 3:
                $order = " db_count " . $by_or_id;
                break;
            case 4:
                if ($by_or_id != 1) $where = $where . " and p.dbtype_id=$by_or_id ";
                break;
        }

        $db_prefix = C('DB_PREFIX');
        $dbperiods_model = M('');
        $lists = $dbperiods_model->table($db_prefix . 'dbperiods p')->join(' LEFT JOIN ' . $db_prefix . 'goods AS g ON p.goods_id = g.goods_id')->order($order)->where($where)->page($page, 10)->field("g.goods_id,p.name,db_count-ydb_count as shengyu,ydb_count/db_count*100 as width")->select();

        $this->assign('lists', $lists);
        $this->display();
    }

    public function ajaxGetMore()
    {
        $p = I('p', 1);
        $dbtype_id = I('dbtype_id', 0);
        $where = "db_count>ydb_count";
        if ($dbtype_id > 0) {
            $where = $where . " and dbtype_id={$dbtype_id}";
        }
        $db_prefix = C('DB_PREFIX');
        $dbperiods_model = M('');
        $periodsList = $dbperiods_model->table($db_prefix . 'dbperiods p')->join(' LEFT JOIN ' . $db_prefix . 'goods AS g ON p.goods_id = g.goods_id')->where($where)->page($p, 10)->field("*,db_count-ydb_count as shengyu,ydb_count/db_count*100 as width")->select();
        $this->assign('periods_goods', $periodsList);
        $this->display();
    }

    //不同区域切换
    public function getdbtype()
    {
        $dbtype_id = I('dbtype_id', 0);
        $this->assign('dbtype_id', $dbtype_id);
        $this->display();
    }

//老的详情页面
    public function goodsInfo()
    {
        C('TOKEN_ON', true);
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $goods_id = I("get.goods_id");//商品id
        $dbshop_id = I("get.dbshop_id");//夺宝商品id
        $periods = I("get.periods");//夺宝的期数
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->select(); // 商品 图册
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        if (!empty($dbshop_id) && !empty($periods)) {
            $where = "p.goods_id=$goods_id and dbshop_id={$dbshop_id}  and periods={$periods}";
        } else {
            $where = "p.goods_id=$goods_id";
        }
        //      dump($where);exit;
        $db_prefix = C('DB_PREFIX');
        $dbperiods_model = M('');
        $goods = $dbperiods_model->table($db_prefix . 'dbperiods p')->join(' LEFT JOIN ' . $db_prefix . 'goods AS g ON p.goods_id = g.goods_id')->where($where)->order('dbshop_id desc')->field("*,db_count-ydb_count as shengyu,ydb_count/db_count*100 as width")->find();

//        dump($periods);
//
        $icon_color = array(1 => 'c_red', 2 => 'c_blue', 3 => 'c_orange', 4 => 'c_grey');

        $msg_status = array(1 => '进行中', 2 => '已揭晓', 3 => '已发货');
        //dump($color);exit;
        if (empty($goods)) {
            $this->tp404('此商品已经夺宝完成');
        }
        //参与人信息
        $users = M('dborder')->where(" dbshop_id={$goods['dbshop_id']} and status>0")->field('user_id,nickname,head_url,db_count,pay_time')->limit(50)->select();
        foreach ($users as &$userinfo) {
            $userinfo['pay_time'] = microtime_format('Y-m-d H:i:s:x', $userinfo['pay_time'] / 1000);
            //dump($userinfo['pay_time']);
        }

        //自己是否参与
        $order = M('dborder');
        //dump($dbshop_id);exit;
        $where = "user_id={$this->user_id} and dbshop_id={$goods['dbshop_id']} and status>0";
        $count = $order->where($where)->sum('db_count');//参与人次，
        $db_nums = $order->where($where)->field('db_nums')->select();//夺宝号码
        $str_num = "";

        foreach ($db_nums as $nums) {
            $str_num = $str_num . $nums['db_nums'] . ",";
        }
        $str_num = trim($str_num, ',');
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
        $filter_spec = $goodsLogic->get_spec($goods_id);
        $this->assign('goods_attr_list', $goods_attr_list);//属性列表
        $this->assign('goods_attribute', $goods_attribute);//属性值
        $this->assign('filter_spec', $filter_spec);//规格参数
        $this->assign('count', $count);
        $this->assign('nums', $str_num);
        $this->assign('users', $users);
        $this->assign('msg_status', $msg_status[$goods['status']]);//状态说明
        $this->assign('icon_color', $icon_color[$goods['status']]);//夺宝状态
        $this->assign('commentStatistics', $commentStatistics);//评论概览
        $this->assign('goods_images_list', $goods_images_list);//商品缩略图
        $this->assign('img_count', count($goods_images_list));//商品缩略图总数
        $this->assign('goods', $goods);
        $this->display();
    }

    /**
     * 商品详情页
     */
    public function goods_info()
    {
        C('TOKEN_ON', true);
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $goods_id = I("get.goods_id");//商品id
        $dbshop_id = I("get.dbshop_id");//夺宝商品id
        $periods = I("get.periods");//夺宝的期数
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->select(); // 商品 图册

        if (!empty($dbshop_id) && !empty($periods)) {
            $where = "p.goods_id=$goods_id and dbshop_id={$dbshop_id}  and periods={$periods}";
        } else {
            $where = "p.goods_id=$goods_id";
        }
        //      dump($where);exit;
        $db_prefix = C('DB_PREFIX');
        $dbperiods_model = M('');
        $goods = $dbperiods_model->table($db_prefix . 'dbperiods p')->join(' LEFT JOIN ' . $db_prefix . 'goods AS g ON p.goods_id = g.goods_id')->where($where)->order('dbshop_id desc')->field("*,db_count-ydb_count as shengyu,ydb_count/db_count*100 as width")->find();
        $icon_color = array(1 => 'c_red', 2 => 'c_blue', 3 => 'c_orange', 4 => 'c_grey');
        $msg_status = array(1 => '进行中', 2 => '已揭晓', 3 => '已发货');

        if (empty($goods)) {
            $this->assign('msg', '此商品已经夺宝完成');
            $this->display('error');
            exit;
        }


        //自己是否中奖
        //$win_order=M('dborder')->where("user_id={$this->user_id} and dbshop_id={$goods['dbshop_id']} and is_win =2")->find();
        //自己参与次数和夺宝号
        $order = M('dborder');
        //dump($dbshop_id);exit;
        $where = "user_id={$this->user_id} and dbshop_id={$goods['dbshop_id']} and status>0";
        $count = $order->where($where)->sum('db_count');//参与人次，
        $db_nums = $order->where($where)->field('db_nums')->select();//夺宝号码
        $str_num = "";
        foreach ($db_nums as $nums) {
            $str_num = $str_num . $nums['db_nums'] . ",";
        }
        $str_num = trim($str_num, ',');
        $str_num = explode(',', $str_num);
        $this->assign('count', $count);
        $this->assign('nums', $str_num);

        $this->assign('msg_status', $msg_status[$goods['status']]);//状态说明
        $this->assign('icon_color', $icon_color[$goods['status']]);//夺宝状态
        //$this->assign('win_order',$win_order);//中奖订单
        $this->assign('user_id', $this->user_id);
        $this->assign('goods_images_list', $goods_images_list);//商品缩略图
        $this->assign('img_count', count($goods_images_list));//商品缩略图总数
        $this->assign('goods', $goods);
        $this->display();
    }

    //加载参与者的信息
    public function ajax_treasure_users($page, $dbshop_id)
    {
        //参与人信息
        $users = M('dborder')->where(" dbshop_id=$dbshop_id and status>0")->field('user_id,nickname,head_url,db_count,pay_time,millisecond')->page($page, 50)->select();
        $this->assign('users', $users);
        $this->display();
    }

    //图文详情
    public function shopinfo($goods_id)
    {
        $goods = M('goods')->where(array('goods_id' => $goods_id))->find();
        $this->assign('goods', $goods);
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $filter_spec = $goodsLogic->get_spec($goods_id);
        $this->assign('goods_attr_list', $goods_attr_list);//属性列表
        $this->assign('goods_attribute', $goods_attribute);//属性值
        $this->assign('filter_spec', $filter_spec);//规格参数
        $this->display();
    }


    //确认订单
    public function confirm_order()
    {
        $goods_id = I("get.goods_id");//商品id
        $dbshop_id = I("get.dbshop_id");//夺宝商品id
        $periods = I("get.periods");//夺宝的期数
        $count = I("get.count");//购买数量
        $where = "db_count>ydb_count and goods_id={$goods_id} and dbshop_id={$dbshop_id}  and periods={$periods}";
        $dbperiods = M('dbperiods')->where($where)->find();
        if (empty($dbperiods)) {
            $this->assign('msg', '此期商品已经开奖,请抢宝下一期商品');
            $url = U('OneShop/goods_info', array('goods_id' => $goods_id));
            $this->assign('url', $url);
            $this->display('error');
            exit;
        }
        $where = "goods_id={$goods_id} and dbshop_id={$dbshop_id}  and periods={$periods}";
        $y_count = M('dborder')->field("sum(db_count) as count")->where($where)->select();
        if (empty($y_count['count'])) $y_count = 0;//已购买数量
        if ($dbperiods['db_limit'] <> 0 && $dbperiods['db_limit'] < ($y_count + count)) {
            $arr = array('status' => 0, 'msg' => "您已购买数已经超过该期已最大限购数，不能在购买，请下期在购买!");
            exit(json_encode($arr));
        }
        $img_url = M('GoodsImages')->where("goods_id = $goods_id")->field('image_url')->find();
        $pay_money = $count * $dbperiods[db_price];
        //插入订单
        $order = M('dborder');
        $data['goods_id'] = $goods_id;
        $data['dbshop_id'] = $dbshop_id;
        $data['periods'] = $periods;
        $data['user_id'] = $this->user_id;
        $data['db_count'] = $count;
        $data['order_amount'] = $pay_money;
        $data['order_sn'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $data['attach'] = 'db';
        $data['name'] = $dbperiods['name'];
        $data['create_time'] = time();
        $data['nickname'] = $this->user['nickname'];
        $data['head_url'] = $this->user['head_pic'];
        $data['ip'] = getIP();
        $data['img_url'] = $img_url[image_url];
        $order_id = $order->add($data);
        $data['order_id'] = $order_id;
        $order = M('dborder')->where("user_id=$this->user_id and status>0")->order('order_id DESC')->find();
        if (!empty($order)) {
            $data['phone'] = $order['phone'];
        }
        $this->assign('order', $data);
        $this->display();
    }

    public function back_order($order_id)
    {
        $order = M('dborder')->where("order_id=$order_id")->find();
        $this->assign('order', $order);
        $this->display('confirm_order');
    }

    //微信支付
    public function payWeixin($order_id, $phone)
    {
        C('TOKEN_ON',false); // 关闭 TOKEN_ON
        header("Content-type:text/html;charset=utf-8");
        $order = M('dborder');
        $order->phone = $phone;
        $order->order_sn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $order->where("order_id = $order_id")->save();
        $order = M('dborder')->where("order_id = $order_id")->find();
        //检查是否可以夺宝，夺宝商品销售完是否开奖
        $where = "db_count>ydb_count and goods_id={$order['goods_id']} and dbshop_id={$order['dbshop_id']}  and periods={$order['periods']}";
        $count = M('dbperiods')->where($where)->field('db_count-ydb_count as count')->find();
        if (empty($count)) {
            $arr = array('status' => 0, 'msg' => "您手慢了,该期已经准备开奖停止购买了!");
            exit(json_encode($arr));
        } else if ($count['count'] < $order['db_count']) {
            $arr = array('status' => 0, 'msg' => "您购买的数量大于剩余数量了!请修改购买数量");
            exit(json_encode($arr));
        }
        //dump($count);dump($order['db_count']);exit;
        //微信支付
        if ($_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $code_str = $this->payment->getJSAPI($order);
            exit($code_str);
        }
    }

    //支付成功
    public function pay_success($order_id)
    {
        $this->assign('order_id', $order_id);
        $this->display();
    }

    public function order_detail($order_id)
    {
        $order = M('dborder')->where("order_id = $order_id")->find();
        $order['pay_time'] = microtime_format('Y-m-d H:i:s:x', $order['pay_time'] / 1000);
//        $order['time']=udate('u',time());
//       dump(getMillisecond());
//        dump(time());
//        dump(microtime_format('Y-m-d H:i:s:x',getMillisecond()/1000));exit;
        $img_url = M('GoodsImages')->where("goods_id = " . $order['goods_id'])->field('image_url')->find();
        //中奖信息
        $info = M('dbperiods')->where("dbshop_id = {$order['dbshop_id']}")->field('status,user_id,nickname,head_url,buy_count,win_time,ip,time_sum,win_code')->find();
        //夺宝号码
        $nums = explode(',', $order['db_nums']);
        //dump($info);exit();
        //领奖订单信息
        $win_order = M('order')->where("user_id=$this->user_id and order_id={$order['orderid']} and order_status=1")->field('shipping_status')->find();
        $this->assign('win_order', $win_order);
        $this->assign('info', $info);
        $this->assign('img_url', $img_url['image_url']);
        $this->assign('nums', $nums);
        $this->assign('order', $order);
        $this->display();
    }

    //支付通知
    public function notifyUrl($order_id)
    {
        $arr = explode("/", $_SERVER['PHP_SELF']);
        //$order_id = $arr[6];
        \Think\Log::record("支付成功，准备写入信息");
        \Think\Log::record($order_id);
        $result = $this->payment->db_handle();
//        $msg = var_export($result, true);
        \Think\Log::record($result);
        //支付成功，更新支付状态，分配夺宝
        if ($result) {
            //检查此通知是否是第一次通知
            $model = M('dborder')->where("order_id={$order_id} and status=0")->field('status')->find();
            if (empty($model)) exit();//重复通知，直接结束
            $order = M('dborder');
            $data['status'] = 1;
            $data['pay_time'] = getMillisecond();
            $data['millisecond'] = get_microtime($data['pay_time'] / 1000);
            $data['time_num'] = microtime_format('Hsi', $data['pay_time'] / 1000) . round_bai($data['millisecond']);
            $order->where("order_id={$order_id}")->save($data);
            $data = M('dborder')->where("order_id={$order_id}")->find();
            db_record_insert($data);
        } else {
            $order = M('dborder');
            $data['status'] = -1;
            $order->where("order_id={$order_id}")->save($data);
        }
        \Think\Log::record($result);
        exit();
    }

//抢宝记录
    public function db_record()
    {
        $this->display();
    }

//热门推荐
    public function treasure_sugguest_list()
    {
        $db_prefix = C('DB_PREFIX');
        $dbperiods_model = M('');
        $where = " status=1";
        $periodsList = $dbperiods_model->table($db_prefix . 'dbperiods p')->join(' LEFT JOIN ' . $db_prefix . 'goods AS g ON p.goods_id = g.goods_id')->where($where)->order('dbshop_id desc')->limit(9)->field("*,db_count-ydb_count as shengyu,ydb_count/db_count*100 as width")->select();
        $this->assign('periods_goods', $periodsList);
        $this->display();
    }

    //全部纪录
    public function ajax_all_record($p)
    {
        $orders = M('dborder')->where("user_id={$this->user_id}")->order("order_id DESC")->page($p, 5)->select();
        $this->assign('orders', $orders);
        $this->display();
    }

    //加载中奖纪录
    public function ajax_win_record($p)
    {
        $orders = M('dborder')->where(" status>0 and user_id={$this->user_id} and is_win>0")->order("order_id DESC")->page($p, 5)->select();
        $this->assign('orders', $orders);
        $this->display();
    }

//最新揭晓
    public function publish()
    {
        $this->display();
    }

    public function publish_ajax($page)
    {
        $orders = M('dborder')->where("status>1 and is_win>0")->order('order_id desc')->page($page, 5)->select();
        $this->assign('orders', $orders);
        $this->display();
    }

    //往期回顾
    public function history($goods_id)
    {
        $this->assign('goods_id', $goods_id);
        $this->display();
    }

//往期异步加载
    public function ajax_history($page, $goods_id)
    {
        $where = "goods_id={$goods_id} and status>1";
        $periods = M('dbperiods')->where($where)->page($page, 20)->field('dbshop_id,goods_id,periods,periods,win_code,user_id,buy_count,win_time,ip,nickname,head_url')->order('dbshop_id desc')->select();
        $this->assign('periods', $periods);
        $this->display();
    }

//计算公式
    public function formula($dbshop_id)
    {
        $periods = M('dbperiods')->where(array('dbshop_id' => $dbshop_id))->field('time_sum,win_code')->find();
        $where = "dbshop_id=$dbshop_id and status>0";
        $orders = M('dborder')->where($where)->field('pay_time,millisecond,time_num,nickname')->select();
        $this->assign('orders', $orders);
        $this->assign('periods', $periods);
        $this->display();
    }

//规则
    public function rule()
    {
        $this->display();
    }

    //确认收货
    public function confirm_address($order_id)
    {
        $order = M('dborder')->where("order_id = $order_id")->find();
        $as_list = M('user_address')->where("user_id=$this->user_id")->order('address_id desc,is_default')->select();
        $d_address = M('user_address')->where("user_id=$this->user_id and is_default=1")->find();
        $this->assign('order', $order);
        $this->assign('as_list', $as_list);
        $this->assign('d_address', $d_address);
        //dump($as_list);exit;
        $this->display();
    }

    /*
     * 地址编辑
     */
    public function edit_address()
    {
        $id = I('address_id', 0);
        $address = M('user_address')->where(array('address_id' => $id))->find();

        if (IS_POST) {
            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id, $id, I('post.'));
            echo json_encode($data);
            exit();
        }
        //获取省份
        $p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        $c = M('region')->where(array('parent_id' => $address['province'], 'level' => 2))->select();
        $d = M('region')->where(array('parent_id' => $address['city'], 'level' => 3))->select();
        if ($address['twon']) {
            $e = M('region')->where(array('parent_id' => $address['district'], 'level' => 4))->select();
            $this->assign('twon', $e);
        }

        $this->assign('province', $p);
        $this->assign('city', $c);
        $this->assign('district', $d);

        $this->assign('address', $address);
        $this->display();
    }

    public function add_address()
    {
        $p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        $this->assign('province', $p);
        $this->display();
    }

    /*
         * 设置默认收货地址
         */
    public function set_default()
    {
        $id = I('get.address_id');
        M('user_address')->where(array('user_id' => $this->user_id))->save(array('is_default' => 0));
        $row = M('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->save(array('is_default' => 1));
        $result = null;
        if ($row) {
            $result = array('status' => 1, 'msg' => '成功', 'result' => 'succ');
        } else {
            $result = array('status' => 0, 'msg' => '失败', 'result' => 'fail');
        }
        echo json_encode($result);
        exit();
    }

    /*
     * 地址删除
     */
    public function del_address()
    {
        $id = I('get.address_id');

        $address = M('user_address')->where("address_id = $id")->find();
        $row = M('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = M('user_address')->where("user_id = {$this->user_id}")->find();
            $address2 && M('user_address')->where("address_id = {$address2['address_id']}")->save(array('is_default' => 1));
        }
        $result = null;
        if ($row) {
            $result = array('status' => 1, 'msg' => '成功', 'result' => 'succ');
        } else {
            $result = array('status' => 0, 'msg' => '失败', 'result' => 'fail');
        }
        echo json_encode($result);
        exit();
    }

//    确认领奖
    public function confirm_win($order_id)
    {
        $order = M('dborder')->where("order_id=$order_id and status=2 and is_win=1 and user_id=$this->user_id")->find();
        $result = null;
        if ($order) {
            if (win_order($order)) {
                $result = array('status' => 1, 'msg' => '领奖成功', 'result' => 'succ');
            } else {
                $result = array('status' => 0, 'msg' => '领奖失败', 'result' => 'fail');
            }
        } else {
            $result = array('status' => 0, 'msg' => '信息不符合领奖要求', 'result' => 'fail');
        }
        echo json_encode($result);
        exit();
    }

//确认收货
    function confirm_receipt($order_id)
    {
        $order = M('dborder')->where("order_id=$order_id and user_id=$this->user_id")->find();
        $res = M('dborder')->where("order_id=$order_id and user_id=$this->user_id")->save(array('status' => 5));
        if ($res) {
            $result = array('status' => 1, 'msg' => '成功', 'result' => 'succ');
        } else {
            $result = array('status' => 0, 'msg' => '失败', 'result' => 'fail');
        }
        echo json_encode($result);
        confirm_order($order[orderid], $this->user_id);//确认商城订单收货

    }

//评论订单
    function share_order()
    {

        if (IS_POST) {
            $order_id = I('post.order_id', 0);
            $order = M('dborder')->where("order_id=$order_id and status=5 and user_id=$this->user_id")->find();
            if ($order['status'] != 5) {
                $result = array('status' => 0, 'msg' => '订单状态不对', 'result' => 'fail');
                echo json_encode($result);
                exit;
            }
            $imageJson = I('post.imageJson', '');
            $content = I('post.content', '');
            $imgType = I('post.imgType', 0);
            $images = explode(',', $imageJson);
            $access_token = $this->get_access_token();
            $upload_path = UPLOAD_PATH . 'comment/' . date('YmdH') . '/';
//            \Think\Log::record($upload_path);
            // 是否存在
            if (!is_dir($upload_path)) {
                // 不存在，创建
                mkdir($upload_path);
            }
            $paths = "";
            foreach ($images as $media_id) {
                $path = $upload_path . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $path = wx_download($media_id, $path, $access_token);
                $paths = $paths . '/' . $path . ',';
            }
            $paths = trim($paths, ',');
            //$order = M('dborder')->where(array('order_id' => $order_id, 'user_id' => $this->user_id,'status'=>5))->find();
            $data = array();
            $data ['goods_id'] = $order['goods_id'];
            $data['periods'] = $order['periods'];
            $data['ip_address'] = getIP();
            $data['content'] = $content;
            $data['nickname'] = $this->user['nickname'];
            $data['user_id'] = $this->user_id;
            $data['head_url'] = $this->user['head_pic'];
            $data['nickname'] = $this->user['nickname'];
            $data['add_time'] = time();
            $data['img'] = $paths;
            $arg_img = explode(',', $paths);

            for ($i = 0; $i < 3; $i++) {
                $data['img_url' . $i] = $arg_img[$i];
            }
//            dump($data);
            $data['img_sum'] = (int)count($arg_img);
            $data['imgType'] = $imgType;
            $data['order_id'] = $order['order_id'];
            $data['name'] = $order['name'];
            $id = M('dbcomment')->add($data);
            if ($id > 0) {
                M('dborder')->where(array('order_id' => $order_id, 'user_id' => $this->user_id, 'status' => 5))->save(array('status' => 6));
                $result = array('status' => 1, 'msg' => '成功', 'result' => 'succ');
            } else {
                $result = array('status' => 0, 'msg' => '失败', 'result' => 'fail');
            }
            echo json_encode($result);
            exit;
        }

        $order_id = I('get.order_id', 0);
        $order = M('dborder')->where("order_id=$order_id")->find();
        $this->assign('order', $order);
        $this->display();
    }

// 评论列表
    function ajax_comment($p)
    {
        $list = M('dbcomment')->where("user_id=$this->user_id")->order('comment_id DESC')->page($p, 10)->select();
        $this->assign('list', $list);
        $this->display();
    }

//晒单详情
    function share_order_detail($order_id)
    {
        $order = M('dborder')->where("order_id=$order_id")->find();
        $com = M('dbcomment')->where("order_id=$order_id")->find();
        $sum_count = M('dbperiods')->where("dbshop_id = " . $order['dbshop_id'])->field('buy_count')->find();
        $imgs = explode(',', $com['img']);
        $this->assign('order', $order);
        $this->assign('com', $com);
        $this->assign('imgs', $imgs);
        $this->assign('sum_count', $sum_count['buy_count']);
        $this->display();
    }

    //晒单广场
    function share_all_order()
    {
        $goods_id = I('get.goods_id', 0);
        $this->assign('goods_id', $goods_id);
        $this->display();
    }

    function ajax_share_all_order($goods_id, $p)
    {
        $where = "is_show=1";
        if ($goods_id > 0) {
            $where = $where . " and goods_id=$goods_id";
        }
        $comm = M('dbcomment')->where($where)->order("comment_id DESC")->page($p, 10)->select();
        $this->assign('comm', $comm);
        $this->display();
    }

//http://localhost:8088/index.php/Mobile/OneShop/test/order_id/9.html
    public function test($order_id)
    {
        $order = M("dborder")->where("order_id={$order_id}")->find();

        win_order($order);
        exit;
        $this->display();
    }

}
