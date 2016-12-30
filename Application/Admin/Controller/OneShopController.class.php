<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/9/21
 * Time: 12:50
 */

namespace Admin\Controller;

use Think\Page;
use Think\AjaxPage;

class OneShopController extends BaseController
{

    public function index()
    {
        $this->display();
    }

    /**
     * 类型列表
     */
    public function dbtypelist()
    {
        $supplier_model = M('dbtype');
        $dbtyper_count = $supplier_model->count();
        $page = new Page($dbtyper_count, 10);
        $show = $page->show();
        $dbtype_list = $supplier_model
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign('list', $dbtype_list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 类型资料
     */
    public function dbtype_info()
    {
        $dbtype_id = I('get.dbtype_id', 0);
        if ($dbtype_id) {
            $model = M('dbtype');
            $info = $model->where("dbtype_id={$dbtype_id}")->find();

            $this->assign('info', $info);
        }
        $act = empty($dbtype_id) ? 'add' : 'edit';
        $this->assign('act', $act);
        $this->display();
    }

    /**
     * 类型增删改
     */
    public function dbtypeHandle()
    {
        $data = I('post.');
        $dbtype_model = M('dbtype');
        //增
        if ($data['act'] == 'add') {
            unset($data['dbtype_id']);
            $count = $dbtype_model->where("name='" . $data['name'] . "'")->count();
            if ($count) {
                $this->error("此名称已被使用，请更换", U('Admin/OneShop/dbtype_info'));
            } else {
                $r = $dbtype_model->add($data);
            }
        }
        //改
        if ($data['act'] == 'edit' && $data['dbtype_id'] > 0) {
            $r = $dbtype_model->where('dbtype_id=' . $data['dbtype_id'])->save($data);
        }
        //删
        if ($data['act'] == 'del' && $data['dbtype_id'] > 0) {
            $r = $dbtype_model->where('dbtype_id=' . $data['dbtype_id'])->delete();
        }

        if ($r !== false) {
            $this->success("操作成功", U('Admin/OneShop/dbtypelist'));
        } else {
            $this->error("操作失败", U('Admin/OneShop/dbtypelist'));
        }
    }

    public function shoplist()
    {
        $condition = array();
        $model = M('dbperiods');
        $count = $model->where($condition)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $prom_list = $model->where($condition)->order("dbshop_id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('prom_list', $prom_list);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function oneshop_info()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            if (empty($data['id'])) {
                $uint = M('dbtype')->where("dbtype_id={$data['dbtype_id']}")->field('uint')->find();
                $data['db_count'] = ceil($data['db_total_price'] / $uint['uint']);
                $data['db_price'] = $uint['uint'];
                $data['db_nums'] = $this->cale_num($data);
                $r = M('dbperiods')->add($data);
                M('goods')->where("goods_id=" . $data['goods_id'])->save(array('prom_id' => $r, 'prom_type' => 10));
                adminLog("管理员添加夺宝活动 " . $data['name']);
            } else {
                //判断此商品是否下架，下架了才能修改
                $goods_id = M('goods')->where("goods_id=" . $data['goods_id'] . " and is_on_sale=0")->field('goods_id')->select();
                if (empty($goods_id)) {
                    $this->error('禁止修改', U('OneShop/shoplist'));
                    exit;
                }
                $data['db_nums'] = $this->cale_num($data);
                $r = M('dbperiods')->where("dbshop_id=" . $data['id'])->save($data);
                //M('goods')->where("prom_type=1 and prom_id=" . $data['id'])->save(array('prom_id' => 0, 'prom_type' => 0));
                M('goods')->where("goods_id=" . $data['goods_id'])->save(array('prom_id' => $data['id'], 'prom_type' => 10));
            }
            if ($r) {
                $this->success('编辑夺宝活动成功', U('OneShop/shoplist'));
                exit;
            } else {
                $this->error('编辑夺宝活动失败', U('OneShop/shoplist'));
            }
        }
        $id = I('id');
        $info['start_time'] = date('Y-m-d');
        $info['end_time'] = date('Y-m-d', time() + 3600 * 24 * 60);
        if ($id > 0) {
            $info = M('dbperiods')->where("dbshop_id=$id")->find();
            $info['start_time'] = date('Y-m-d', $info['start_time']);
            $info['end_time'] = date('Y-m-d', $info['end_time']);
        }
        //获得夺宝类型
        $dbtype = M('dbtype')->field('dbtype_id,name')->select();
        //dump($dbtype);exit;
        $this->assign('dbtype', $dbtype);
        $this->assign('info', $info);
        $this->assign('min_date', date('Y-m-d'));
        $this->display();
    }

    //清理夺宝商品
    public function clear_goods()
    {
        $result = M('goods')->where("prom_type=10")->save(array('prom_id' => 0, 'prom_type' => 0));
        if ($result) {
            $this->success('清理成功');
        } else {
            $this->error('清理失败');
        }
    }

    //计算要生成好多夺宝号
    public function cale_num($data)
    {
        //$uint = M('dbtype')->field('uint')->where("dbtype_id={$data['dbtype_id']}")->find();
        $count = $data['db_count'];//夺宝号个数
        $numbers = range(10000001, $count + 10000000);
        shuffle($numbers);
        return implode(',', $numbers);
    }

    public function oneshop_del()
    {
        $id = I('del_id');
        //dump($id);exit;
        if ($id) {
            //判断此商品是否下架，下架了才能修改
//            $goods_id= M('dbperiods')->where("dbshop_id=$id")->field('goods_id')->find();
//            $goods_id= M('goods')->where("goods_id=" . $goods_id." and is_on_sale=0")->field('goods_id')->select();
//            if(empty($goods_id)) {
//            $this->error('禁止删除', U('OneShop/shoplist'));
//            exit;
//            }
            M('dbperiods')->where("dbshop_id=$id")->delete();
            M('goods')->where("prom_type=10 and prom_id=$id")->save(array('prom_id' => 0, 'prom_type' => 0));
            exit(json_encode(1));
        } else {
            exit(json_encode(0));
        }
    }

//夺宝订单
    public function db_order()
    {
//        C('TOKEN_ON',false); // 关闭表单令牌验证
//        $this->order_status = C('ORDER_STATUS');
//        $this->pay_status = C('PAY_STATUS');
//        $this->shipping_status = C('SHIPPING_STATUS');
//        // 订单 支付 发货状态
//        $this->assign('order_status',$this->order_status);
//        $this->assign('pay_status',$this->pay_status);
//        $this->assign('shipping_status',$this->shipping_status);
        $begin = date('Y/m/d', (time() - 30 * 60 * 60 * 24));//30天前
        $end = date('Y/m/d', strtotime('+1 days'));
        $this->assign('timegap', $begin . '-' . $end);
        $this->display();
    }

//夺宝订单分页
    public function ajax_order()
    {
        //定义状态数组
        $order_status = array('-5' => '退款失败', '-4' => '退款成功', '-3' => '退款中', '-2' => '退款', '-1' => '支付失败', '0' => '未支付', '1' => '已支付', '2' => '已揭晓', '3' => '待发货', '4' => '待签收', '5' => '已签收', '6' => '已晒单');//'-5退款失败-4退款成功-3退款中-2退款,-1支付失败0未支付1已支付(等待揭晓),2已经揭晓,3待发货4待签收,5已签收6已经晒单',
        $win_status = array('0' => '未中奖', '1' => '已中奖', '2' => '已领奖');
        $timegap = I('timegap');
        if ($timegap) {
            $gap = explode('-', $timegap);
            $begin = strtotime($gap[0]);
            $end = strtotime($gap[1]);
        }
        // 搜索条件
        $condition = array();

        //I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        if ($begin && $end) {
            $condition['add_time'] = array('between', "$begin,$end");
        }
        //dump($GLOBALS['HTTP_RAW_POST_DATA']);
        //exit;
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;
        I('status') != '' ? $condition['status'] = I('status') : false;
        I('is_win') != '' ? $condition['is_win'] = I('is_win') : false;
        I('goods_id') != '' ? $condition['goods_id'] = I('goods_id') : false;
        //I('shipping_status') != '' ? $condition['shipping_status'] = I('shipping_status') : false;
        I('user_id') ? $condition['user_id'] = trim(I('user_id')) : false;
        $sort_order = I('order_by', 'DESC') . ' ' . I('sort');
        $count = M('dborder')->where($condition)->count();
        $Page = new AjaxPage($count, 20);
        //  搜索条件下 分页赋值
        foreach ($condition as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $show = $Page->show();
        //获取订单列表
        $orderList = M('dborder')->where($condition)->limit($Page->firstRow, $Page->listRows)->order($sort_order)->select();
        $this->assign('order_status', $order_status);
        $this->assign('win_status', $win_status);
        $this->assign('orderList', $orderList);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function delete_order($order_id)
    {
        $result = M('dborder')->where(array('order_id' => $order_id))->delete();
        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function db_detail($order_id)
    {
        $order = M('dborder')->where(array('order_id' => $order_id))->find();
        //$order['pay_time']=$order['pay_time']/1000;
        $this->assign('order', $order);
        //定义状态数组
        $order_status = array('-1' => '支付失败', '0' => '未支付', '1' => '已支付', '2' => '已揭晓', '3' => '待发货', '4' => '待签收', '5' => '已签收', '6' => '已经晒');//'-5退款失败-4退款成功-3退款中-2退款,-1支付失败0未支付1已支付(等待揭晓),2已经揭晓,3待发货4待签收,5已签收6已经晒单'
        $win_status = array('0' => '未中奖', '1' => '已中奖', '2' => '已领奖');
        $this->assign('order_status', $order_status);
        $this->assign('win_status', $win_status);
        //dump($order);exit;
        $this->display();
    }

    public function comment_list()
    {
        $this->display();
    }

    public function ajax_comment()
    {
        $model = M('dbcomment');
        $nickname = I('nickname', '', 'trim');
        $content = I('content', '', 'trim');
        $where = '1=1 ';
        if ($nickname) {
            $where .= "AND  nickname  like '%{$nickname}%'";
        }
        if ($content) {
            $where .= " AND content like '%{$content}%'";
        }
        $count = $model->where($where)->count();
        $Page = new AjaxPage($count, 20);
        $show = $Page->show();
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('comment_list', $comment_list);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function comment_del($id)
    {
        $result = M('dbcomment')->where("comment_id=$id")->delete();
        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}