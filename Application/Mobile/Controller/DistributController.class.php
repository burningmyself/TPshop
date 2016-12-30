<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/8/18
 * Time: 10:26
 */
namespace Mobile\Controller;


class DistributController extends MobileBaseController
{
    public $user_id = 0;
    public $user = array();

    /*
    * 初始化操作
    */
    public function _initialize()
    {
        parent::_initialize();
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
        }
        $nologin = array(
            'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
            'verifyHandle', 'reg', 'send_sms_reg_code', 'find_pwd', 'check_validate_code',
            'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code', 'express',
        );
        if (!$this->user_id && !in_array(ACTION_NAME, $nologin)) {
            header("location:" . U('Mobile/User/login'));
            exit;
        }

        $order_status_coment = array(
            'WAITPAY' => '待付款 ', //订单查询状态 待支付
            'WAITSEND' => '待发货', //订单查询状态 待发货
            'WAITRECEIVE' => '待收货', //订单查询状态 待收货
            'WAITCCOMMENT' => '待评价', //订单查询状态 待评价
        );
        $this->assign('order_status_coment', $order_status_coment);
    }

    public function index()
    {

        $order_count = M('order')->where("user_id = {$this->user_id}")->count(); // 我的订单数
        $goods_collect_count = M('goods_collect')->where("user_id = {$this->user_id}")->count(); // 我的商品收藏
        $comment_count = M('comment')->where("user_id = {$this->user_id}")->count();//  我的评论数
        $coupon_count = M('coupon_list')->where("uid = {$this->user_id}")->count(); // 我的优惠券数量
        $level_name = M('user_level')->where("level_id = {$this->user['level']}")->getField('level_name'); // 等级名称
        $sale_sum = M('rebate_log')->where(array("user_id" => $this->user_id, "status" => 3))->sum('goods_price');//销售额
        $sale_sum = $sale_sum ? $sale_sum : 0;
        $brokerage = M('rebate_log')->where(array("user_id" => $this->user_id, "status" => 3))->sum('money');//佣金
        $brokerage = $brokerage ? $brokerage : 0;
        $one = M('users')->where("first_leader = {$this->user_id}")->count();
        $two = M('users')->where("second_leader = {$this->user_id}")->count();
        $three = M('users')->where("third_leader = {$this->user_id}")->count();
        $this->assign('level_name', $level_name);
        $this->assign('order_count', $order_count);
        $this->assign('goods_collect_count', $goods_collect_count);
        $this->assign('comment_count', $comment_count);
        $this->assign('coupon_count', $coupon_count);
        $this->assign('sale', $sale_sum);
        $this->assign('brokerage', $brokerage);
        $this->assign('one', $one);
        $this->assign('two', $two);
        $this->assign('three', $three);
        $this->display();
    }

    public function withdrawals()
    {
        if (IS_POST) {
            $money = I('post.money');
            $bank_name = I('post.bank_name');
            $account_bank = I('post.account_bank');
            $account_name = I('post.account_name');
            $data['user_id'] = $this->user_id;
            $data['create_time'] = time();
            $data['money'] = $money;
            $data['bank_name'] = $bank_name;
            $data['account_bank'] = $account_bank;
            $data['account_name'] = $account_name;
            $data['status'] = 0;
            $wd = M('withdrawals');
            $user = M('users');
            $model = $user->where("user_id={$this->user_id}")->find();
            //首先判断提现金额是否大于等于100，然后可以提现的余额是否大于等于提现额度
            if ($model['user_money'] < $data['$money']) {
                $this->display('可提现额度不足,请修改提现额度再试', U('/Mobile/Distribut/withdrawals'), 3);
                exit();
            }
            if ($data['money'] < 100) {
                $this->display('提现金额必须大于等于100元', U('/Mobile/Distribut/withdrawals'), 3);
                exit();
            }
            $wd->startTrans();
            $r1 = $wd->data($data)->add();
            $data1['user_money'] = $model['user_money'] - $money;
            $data1['frozen_money'] = $model['frozen_money'] + $money;
            $r2 = $user->where("user_id={$this->user_id}")->save($data1); // 根据条件更新记录
//            dump($r1);
//            exit;
            if ($r1 && $r2) {
                $wd->commit();
                $this->success('提现成功', U('/Mobile/User/index'), 3);
                exit();
            } else {
                $wd->rollback();
                $this->display('提现失败', U('/Mobile/User/index'), 3);
                exit();
            }
        } else {
            $order_count = M('order')->where("user_id = {$this->user_id}")->count(); // 我的订单数
            $goods_collect_count = M('goods_collect')->where("user_id = {$this->user_id}")->count(); // 我的商品收藏
            $comment_count = M('comment')->where("user_id = {$this->user_id}")->count();//  我的评论数
            $coupon_count = M('coupon_list')->where("uid = {$this->user_id}")->count(); // 我的优惠券数量
            $level_name = M('user_level')->where("level_id = {$this->user['level']}")->getField('level_name'); // 等级名称
            $this->assign('level_name', $level_name);
            $this->assign('order_count', $order_count);
            $this->assign('goods_collect_count', $goods_collect_count);
            $this->assign('comment_count', $comment_count);
            $this->assign('coupon_count', $coupon_count);
            //提现记录
            $data = D('remittance')->where("user_id={$this->user_id}")->select();
//            dump($data);
//            exit;
            $this->assign('data', $data);
            $this->display();
        }
    }

    public function qr_code()
    {
        $order_count = M('order')->where("user_id = {$this->user_id}")->count(); // 我的订单数
        $goods_collect_count = M('goods_collect')->where("user_id = {$this->user_id}")->count(); // 我的商品收藏
        $comment_count = M('comment')->where("user_id = {$this->user_id}")->count();//  我的评论数
        $coupon_count = M('coupon_list')->where("uid = {$this->user_id}")->count(); // 我的优惠券数量
        $level_name = M('user_level')->where("level_id = {$this->user['level']}")->getField('level_name'); // 等级名称
        $this->assign('level_name', $level_name);
        $this->assign('order_count', $order_count);
        $this->assign('goods_collect_count', $goods_collect_count);
        $this->assign('comment_count', $comment_count);
        $this->assign('coupon_count', $coupon_count);
        $this->display();
    }

    public function qrcode()
    {
        $url = SITE_URL . "/index.php?m=Mobile&c=Index&a=index&first_leader=" . $this->user_id;
        $url = urldecode($url);
        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel = intval(3);//容错级别
        $matrixPointSize = intval(4);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
    }
}