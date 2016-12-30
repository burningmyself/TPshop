<?php

/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/9/10
 * Time: 9:40
 */
namespace Common\Logic;
class DistributLogic
{
    public function rebate_log($order)
    {
        //$disConfig = M('config')->where("inc_type='distribut' ")->field('name,value')->select();
        $total = 0;//佣金分成总金额
        $pattern = tpCache('distribut.pattern');
        $condition = tpCache('distribut.condition');

        if ($pattern == 0) {//按照商品佣金额分成
            $sql = "SELECT SUM(commission) AS total FROM __PREFIX__goods WHERE goods_id IN(SELECT goods_id FROM __PREFIX__order_goods WHERE order_id={$order['order_id']})";
            $total = M()->query($sql)[0]['total'];
        } else if ($pattern == 1) {//按照订单金额分成（应付款额减去运费(余额支付不享受分成)）
            $total = $order['order_amount'] - $order['shipping_price'];
        } else if ($pattern == 2)//按照会员等级分成
        {
            $this->level_fc($order, $condition);
            goto end;
        }
        $user = M('users')->where("user_id={$order['user_id']}")->find();
        $first_leader = M('users')->where("user_id={$user['first_leader']}")->find();
        $second_leader = M('users')->where("user_id={$user['second_leader']}")->find();
        $third_leader = M('users')->where("user_id={$user['third_leader']}")->find();
        $first = 0;
        $second = 0;
        $third = 0;
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $first = $total * (tpCache('distribut.first_rate') / 100);
            $this->add_rebate($order, $user, $first_leader, $first);
        }
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $second = $total * (tpCache('distribut.second_rate') / 100);
            $this->add_rebate($order, $user, $second_leader, $second);
        }
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $third = $total * (tpCache('distribut.third_rate') / 100);
            $this->add_rebate($order, $user, $third_leader, $third);
        }
        end:
//        dump($first_leader);
//        exit;

//        $first_leader->distribut_money = $first_leader->distribut_money + $first;
//        $first_leader->save();
//        $second_leader->distribut_money = $second_leader->distribut_money + $second;
//        $second_leader->save();
//        $third_leader->distribut_money = $third_leader->distribut_money + $third;
//        $third_leader->save();
//        $data['is_distribut'] = 1;
//        M('order')->where("order_id={$order['order_id']}")->save($data);
    }

    public function add_rebate($order, $user, $other, $total)
    {
        //        id int(11)  分成记录表
//        user_id int(11) 0 获佣用户
//        buy_user_id int(11) 0 购买人id
//        nickname varchar(32)  购买人名称
//        order_sn varchar(32)  订单编号
//        order_id int(11) 0 订单id
//        goods_price decimal(10,2) 0.00 订单商品总额
//        money decimal(10,2) 0.00 获佣金额
//        level tinyint(1) 1 获佣用户级别
//        create_time int(11) 0 分成记录生成时间
//        confirm int(11) 0 确定收货时间
//        status tinyint(1) 0 0未付款,1已付款, 2等待分成(已收货) 3已分成, 4已取消
//        confirm_time int(11) 0 确定分成或者取消时间
//        remark varchar(1024)  如果是取消, 有取消备注
        $rebate = M("rebate_log");
        $data['user_id'] = $other['user_id'];
        $data['buy_user_id'] = $user['user_id'];
        $data['nickname'] = $user['nickname'];
        $data['order_sn'] = $order['order_sn'];
        $data['order_id'] = $order['order_id'];
        $data['goods_price'] = $order['total_amount'];
        $data['money'] = $total;
        $data['level'] = $other['level'];
        $data['create_time'] = time();
        $data['status'] = $order['pay_status'];
//        dump($rebate);
//        exit;
        if ($data['user_id']) {//存在佣金者添加纪录
            $rebate->data($data)->add();
        }
    }

    //会员等级分成
    public function level_fc($order, $condition)
    {

        $total = $order['order_amount'] - $order['shipping_price'];//应付款额减去运费(余额支付不享受分成)
        $table = M("user_level")->field('level_id,rate')->select();
        $levels = array_column($table, 'rate', 'level_id');
        $user = M('users')->where("user_id={$order['user_id']}")->find();
        $first_leader = M('users')->where("user_id={$user['first_leader']}")->find();
        $second_leader = M('users')->where("user_id={$user['second_leader']}")->find();
        $third_leader = M('users')->where("user_id={$user['third_leader']}")->find();
        $first = 0;
        $second = 0;
        $third = 0;
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $first = $total * $levels["{$first_leader['level']}"] / 100;
            $this->add_rebate($order, $user, $first_leader, $first);
        }
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $second = $total * $levels["{$second_leader['level']}"] / 100;
            $this->add_rebate($order, $user, $second_leader, $second);
        }
        if (first_leader['is_distribut'] == 1 || $condition == 0) {
            $third = $total * $levels["{$third_leader['level']}"] / 100;
            $this->add_rebate($order, $user, $third_leader, $third);
        }
    }

    public function auto_confirm()
    {
        $date = tpCache('distribut.date');
        $orders = M("order")->where("pay_status=1 and is_distribut=0 and order_status=4")->select();//分成条件，订单已经付款，货物已签收，并且要晒单

        foreach ($orders as $k => $v) {
            $rebates = M("rebate_log")->where("order_id={$v['order_id']}")->select();
//            dump($rebates);exit;
            $days = round((time() - $v['confirm_time']) / 3600 / 24);
            $days = 8;//测试自动分成到钱包
            $data_state = $days >= (float)$date;//分成时间状态

            foreach ($rebates as $k1 => $v1) {
                $order = M('order');
                $order->where("order_id={$v1['order_id']}")->find();
                if ($data_state) {
                    $user = M('users')->where("user_id={$v1['user_id']}")->find();
                    $user['distribut_money'] = $user['distribut_money'] + $v1['money'];
                    $user['user_money'] = $user['user_money'] + $v1['money'];
                    $_user = M('users');
                    $_user->save($user);
                    $rebate = M("rebate_log");
                    $v1['status'] = 3;
                    $rebate->save($v1);
                }
            }
            if ($data_state) {
                $order = M('order');
                $order->is_distribut = 1;
                $order->where("order_id={$v['order_id']}")->save();
            }

        }
    }
}