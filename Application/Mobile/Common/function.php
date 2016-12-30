<?php
//夺宝纪录插
function db_record_insert($order)
{
    $where = "db_count>ydb_count and goods_id={$order['goods_id']} and dbshop_id={$order['dbshop_id']}  and periods={$order['periods']}";
    $count_nums = M('dbperiods')->where($where)->field('db_count-ydb_count as count,db_nums,ydb_count,db_count')->find();
    if ($count_nums['count'] >= $order['db_count']) {
        //夺宝号足够，夺宝成功，
        $dborder = M('dborder');
        $dbperiods = M('dbperiods');
        $arr_nums = explode(',', $count_nums['db_nums']);//取出全部夺宝号装入数组
        $db_nums = array_slice($arr_nums, 0, $order['db_count']);//取出夺宝号
        $data['db_nums'] = implode(",", $db_nums);
        $dbperiods->db_nums = implode(",", array_slice($arr_nums, $order['db_count']));//取出后剩余的夺宝号；
        $dbperiods->ydb_count = (int)$count_nums['ydb_count'] + (int)$order['db_count'];
        $dborder->startTrans();
        $id = $dborder->where("order_id={$order['order_id']}")->save($data);
        \Think\Log::record(var_export($count_nums['db_nums'], true));
        \Think\Log::record(var_export($data['db_nums'], true));
        //\Think\Log::record(var_export($db_pr, true));
        $result = $dbperiods->where($where)->save();
        if ($id > 0 && $result) {
            $dborder->commit();
            unset($dbperiods);
        } else {
            $dborder->rollback();
        }
    }
    if ($count_nums['count'] == $order['db_count']) {//1这一期夺宝已销售完准备开奖 2准备生成下一期夺宝商品，夺宝商品已下架后者库存为零不生成
        //计算开奖
        \Think\Log::record("kaijiang");
        cale_win($order, $count_nums['db_count']);
        $count_sale = M('goods')->where("goods_id={$order['goods_id']}")->field('store_count,is_on_sale')->find();
        \Think\Log::record($count_sale['store_count']);
        \Think\Log::record($count_sale['is_on_sale']);
        \Think\Log::record($count_sale['store_count'] > 0 && $count_sale['is_on_sale'] == 1);
        if ($count_sale['store_count'] > 0 && $count_sale['is_on_sale'] == 1) {
            \Think\Log::record("生成下一期夺宝商品");
            $dbperiods = M('dbperiods')->where("dbshop_id=" . $order['dbshop_id'])->field('goods_id,name,dbtype_id,periods,db_total_price,db_price,db_count,db_limit,is_show,is_hot')->find();
            $dbperiods['periods'] = $dbperiods['periods'] + 1;
            $dbperiods['db_nums'] = cale_num($dbperiods['db_count']);
            $dbperiods['start_time'] = time();
            $dbperiods['end_time'] = time() + (3600 * 24 * 30);
            \Think\Log::record(var_export($dbperiods, true));
            M('dbperiods')->add($dbperiods);
        }
    }
}

//计算中奖号码，如果要指定中奖号码，需要客户端购买一个中奖号码，且这个中奖号码不能被外面看到购买时间(购买时间与中奖号码有关)
function cale_win($order, $db_count)
{
    //先查看是否有指定人中奖
    $user_id = M('dbwinnum')->where("dbshop_id={$order['dbshop_id']} and periods={$order['periods']} and goods_id={$order['goods_id']}")->field('user_id')->find();
    $orders = M('dborder')->where("dbshop_id={$order['dbshop_id']} and status=1 ")->order('pay_time desc')->limit(50)->select();
    $win_num = cale_win_num($orders, $db_count);
    \Think\Log::record($win_num);
    if (!empty($user_id)) {
        //得到这个用户这一期所买的夺宝号
        $nums = M('dborder')->where("dbshop_id={$order['dbshop_id']} and user_id={$user_id}")->field('db_nums')->select();
        $arr_nums = array();
        foreach ($nums as $num) {
            $str = str + "," + $num;
        }
        $arr_nums = explode(",", substr($str, 1));
        $zd_num = array_rand($arr_nums, 1);//随机得到制定的num
        cale_zd_win_num($orders, $win_num, $zd_num);
    } else {
        update_all_order($order, $win_num);
    }
}

//更新订单状态/和中奖号码
function update_all_order($order, $win_num)
{
    //更新所有订单
    \Think\Log::record('更新所有订单');
//    $model = M('dborder');
//    $model->status = 2;
//    $model->win_code = $win_num;
//    $sql = $model->where("dbshop_id={$order['dbshop_id']}")->fetchSql(false)->save();
    $Model = new \Think\Model();
    $sql = "UPDATE tp_dborder SET status=2 ,win_code={$win_num} WHERE dbshop_id={$order['dbshop_id']} AND status=1";
    $Model->execute($sql);
    //\Think\Log::record($sql);
    //更新中奖者的订单
    \Think\Log::record('更新中奖者的订单');
    $order_win = M('dborder')->where("dbshop_id={$order['dbshop_id']} and db_nums like '%{$win_num}%'")->find();
    //dump($order_id);
    \Think\Log::record($order_win['order_id']);
    $up_order = M('dborder');
    $up_order->is_win = 1;
    $up_order->where("order_id={$order_win['order_id']}")->save();
    \Think\Log::record('更新商品中奖这的信息');
    $dbps = M('dbperiods');
    $dbps->user_id = $order_win['user_id'];
    $dbps->nickname = $order_win['nickname'];
    $dbps->buy_count = $order_win['db_count'];
    $dbps->ip = $order_win['ip'];
    $dbps->head_url = $order_win['head_url'];
    $dbps->where("dbshop_id={$order['dbshop_id']}")->save();
    //微信通知
    $msg = $wx_content = "你刚刚夺宝已中奖,订单号为:{$order['order_sn']} ，请尽快确认领奖，若七日内未领取，将视为自动放弃奖品，不会补发奖品的!";
    weixin_notify($order_win['user_id'], $msg);
}

//1、商品的所有号码分配完毕后，将公示该认购时间点并向前截取，一元抢宝平台全部商品的近50个下单时间；
//2、将这50个时间的数值进行求和，得到数值A（每个时间按时、分、秒、毫秒的顺序组合，如20:13:14.362则为201314362，若时间为20:13:14.002则数值为20131402），为了防止用户批量进行毫秒级下单，毫秒百位为0，自动舍去，数字将从9位，修改成8位进行求和计算；
//3、数值A除以该商品总需人次得到的余数【余数=mod（数值A/商品总需人次）】 + 原始数 10000001，得到幸运号码，拥有该幸运号码者，直接获得该商品。
function cale_win_num($orders, $db_count)
{
    $sum = 0;
    foreach ($orders as $order) {
        $sum = $sum + (int)$order['time_num'];
        \Think\Log::record($order['time_num']);
    }
    $win_num = $sum % $db_count + 10000001;
    //更新夺宝商品状态
    $model = M("dbperiods");
    $model->status = 2;
    $model->win_code = $win_num;
    $model->time_sum = $sum;
    $model->win_time = time();
    $model->where("dbshop_id={$order['dbshop_id']}")->save();
    //\Think\Log::record(var_export($model,true));
    return $win_num;
}

//计算制定中奖好嘛，更新下单和支付时间，开奖指定中奖号码
function cale_zd_win_num($orders, $win_num, $zd_num)
{
    $cha = $win_num - $zd_num;
    if ($cha != 0) {
        if (update_time($orders, $cha, $zd_num)) {//指定成功
            update_all_order($orders[0], $zd_num);
        } else {
            update_all_order($orders[0], $win_num);
        }
    } else {
        update_all_order($orders[0], $win_num);
    }
}

//计算后五十笔订单是否有合格指定夺宝号修改的订单
function update_time($orders, $cha, $zd_num)
{
    foreach ($orders as $order) {
        $time_num = $order['time_num'] - $cha;
        $ary = explode('.', (float)$time_num / 1000);
        $bsg = $ary[1];
        if ($bsg > 99) {
            return false;
        }
    }
    $order['pay_time'] = $order['pay_time'] + $cha;
    $order['time_num'] = $order['time_num'] + $cha;
    $order['millisecond'] = $bsg;
    $model = M('dborder');
    $model->save($order);
    //更新夺宝商品状态
    $dbperiods = M('dbperiods')->where("dbshop_id={$order['dbshop_id']}")->find();
    $dbperiods['status'] = 2;
    $dbperiods['win_code'] = $zd_num;
    $dbperiods['time_sum'] = $dbperiods['time_sum'] + $cha;
    M('dbperiods')->save($dbperiods);
    return true;
}

//计算要生成好多夺宝号
function cale_num($count)
{
    //$uint = M('dbtype')->field('uint')->where("dbtype_id={$data['dbtype_id']}")->find();
    // $count = $data['db_count'];//夺宝号个数
    $numbers = range(10000001, $count + 10000000);
    shuffle($numbers);
    return implode(',', $numbers);
}

/** 获取当前时间戳，精确到毫秒 */

function getMillisecond()
{
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}


/** 格式化时间戳，精确到毫秒，x代表毫秒 */

function microtime_format($tag, $time)
{
    list($usec, $sec) = explode(".", $time);
    $date = date($tag, $usec);
    return str_replace('x', $sec, $date);
}

//返回毫秒
function get_microtime($time)
{
    list($usec, $sec) = explode(".", $time);
    return $sec;
}

//为了防止用户批量进行毫秒级下单，毫秒百位为0，自动舍去，数字将从9位，修改成8位进行求和计算
function round_bai($microtime)
{
    if ((int)$microtime < 99) {
        return (int)$microtime;
    }
    return $microtime;
}

//领奖
function win_order($dborder)
{
    $address = M('UserAddress')->where("is_default = 1 and user_id={$dborder['user_id']}")->find();
    $shipping = M('Plugin')->where("code = 'shunfeng'")->find();//默认发顺丰
    $data = array(
        'order_sn' => date('YmdHis') . rand(1000, 9999), // 订单编号
        'user_id' => $dborder['user_id'], // 用户id
        'consignee' => $address['consignee'], // 收货人
        'province' => $address['province'],//'省份id',
        'city' => $address['city'],//'城市id',
        'district' => $address['district'],//'县',
        'twon' => $address['twon'],// '街道',
        'address' => $address['address'],//'详细地址',
        'mobile' => $address['mobile'],//'手机',
        'zipcode' => $address['zipcode'],//'邮编',
        'email' => $address['email'],//'邮箱',
        'shipping_code' => $shipping['code'],//'物流编号',
        'shipping_name' => $shipping['name'], //'物流名称',
        'invoice_title' => $address['consignee'], //'发票抬头',
//        'goods_price'      =>$car_price['goodsFee'],//'商品价格',
//        'shipping_price'   =>$car_price['postFee'],//'物流价格',
//        'user_money'       =>$car_price['balance'],//'使用余额',
//        'coupon_price'     =>$car_price['couponFee'],//'使用优惠券',
//        'integral'         =>($car_price['pointsFee'] * tpCache('shopping.point_rate')), //'使用积分',
//        'integral_money'   =>$car_price['pointsFee'],//'使用积分抵多少钱',
//        'total_amount'     =>($car_price['goodsFee'] + $car_price['postFee']),// 订单总额
//        'order_amount'     =>$car_price['payables'],//'应付款金额',
        'add_time' => time(), // 下单时间
        'order_prom_id' => 10,//'订单优惠活动id',
        'order_prom_amount' => 0,//'订单优惠活动优惠了多少钱',
        pay_status => 1,//夺宝中奖已支付
        pay_time => time(),
    );
    $order_id = M("Order")->data($data)->add();

    if (!$order_id) {
        return false;
    }
    // 记录订单操作日志
    logOrder($order_id, '您提交了订单，请等待系统确认', '提交订单', $dborder['user_id']);

    $order = M('Order')->where("order_id = $order_id")->find();

    // 1插入order_goods 表
    $goods = M('goods')->where("goods_id={$dborder['goods_id']}")->find();

    $data2['order_id'] = $order_id; // 订单id
    $data2['goods_id'] = $goods['goods_id']; // 商品id
    $data2['goods_name'] = $goods['goods_name']; // 商品名称
    $data2['goods_sn'] = $goods['goods_sn']; // 商品货号
    $data2['goods_num'] = 1; // 购买数量
    $data2['market_price'] = $goods['market_price']; // 市场价
    $data2['goods_price'] = $goods['shop_price']; // 商品价
    $data2['spec_key'] = $goods['spec_type']; // 商品规格
    $data2['spec_key_name'] = $goods['spec_type']; // 商品规格名称
    $data2['sku'] = $goods['sku']; // 商品sku
    $data2['member_goods_price'] = 0; // 会员折扣价
    $data2['cost_price'] = 0; // 成本价
    $data2['give_integral'] = 0; // 购买商品赠送积分
    $data2['prom_type'] = 10; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
    $data2['prom_id'] = 10; // 活动id

    $order_goods_id = M("OrderGoods")->data($data2)->add();
    if (!$order_goods_id) {
        return false;
    }
    //更新夺宝订单的状态
    M('dborder')->where("order_id={$dborder['order_id']}")->save(array('is_win' => 2, 'status' => 3, 'orderid' => $order_id,'address'=>$address['address']));

    // 扣除商品库存
    M('Goods')->where("goods_id = " . $goods['goods_id'])->setDec('store_count', 1); // 商品减少库存
// 如果有微信公众号 则推送一条消息到微信
    $user = M('users')->where("user_id = {$dborder['user_id']}")->find();
    if ($user['oauth'] == 'weixin') {
        $wx_user = M('wx_user')->find();
        $jssdk = new \Mobile\Logic\Jssdk($wx_user['appid'], $wx_user['appsecret']);
        $wx_content = "你刚刚领取奖品生成的订单号为:{$order['order_sn']} ,我们会尽快安排发货!";
        $jssdk->push_msg($user['openid'], $wx_content);
    }
    return true;
}

//微信通知
function weixin_notify($user_id, $msg)
{
    // 如果有微信公众号 则推送一条消息到微信
    $user = M('users')->where("user_id = $user_id")->find();
    if ($user['oauth'] == 'weixin') {
        $wx_user = M('wx_user')->find();
        $jssdk = new \Mobile\Logic\Jssdk($wx_user['appid'], $wx_user['appsecret']);
        $jssdk->push_msg($user['openid'], $msg);
    }
}


//更新夺宝发货状态(备份admin)
function update_send_status($action, $order_id, $express = '')
{
    switch ($action) {
        case 'pay': //付款
            return false;
        case 'pay_cancel': //取消付款
            return false;
        case 'confirm': //确认订单
            return false;
        case 'cancel': //取消确认
            return false;
        case 'invalid': //作废订单
            return false;
        case 'remove': //移除订单
            return false;
        case 'delivery_confirm'://确认收货
            $temp = M('dborder')->where("orderid=$order_id")->field('order_id')->find();
            if (!empty($temp)) {
                $order_id = $temp['order_id'];
                M('dborder')->where("order_id=$order_id")->save(array('status' => 5));//已收货
            }
            return true;
        case 'delivery'://发货货
            $temp = M('dborder')->where("orderid=$order_id")->field('order_id')->find();
            $order_id = $temp['order_id'];
            if (!empty($temp)) {
                M('dborder')->where("order_id=$order_id")->save(array('status' => 4, 'express' => $express));//已收货
            }
            return true;
        default:
            return true;
    }
}

//从微信下载图片
function wx_download($media_id, $path, $access_token)
{
    $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";

    $fileInfo = downloadWeixinFile($url);;
    $ext = strrchr($url, '.');
    $imageType=$fileInfo["header"]['content_type'];
    $ext=explode('/',$imageType)[1];
    $filename = $path.'.'.$ext;
    saveWeixinFile($filename, $fileInfo["body"]);
    return $filename;
}

function downloadWeixinFile($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    $imageAll = array_merge(array('header' => $httpinfo), array('body' => $package));
    return $imageAll;
}

function saveWeixinFile($filename, $filecontent)
{
    $local_file = fopen($filename, 'w');
    if (false !== $local_file) {
        if (false !== fwrite($local_file, $filecontent)) {
            fclose($local_file);
        }
    }
}



