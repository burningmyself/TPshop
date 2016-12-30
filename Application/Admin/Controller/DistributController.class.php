<?php
namespace Admin\Controller;

class DistributController extends BaseController
{
    public function set()
    {
        $this->redirect("/Admin/System/index/inc_type/distribut");
    }

    public function tree()
    {
        $user_id = I('get.userid');
        $users = null;
        if ($user_id != null && $user_id != "") {
            $users = D('users')->where(array('user_id' => $user_id))->find();
        } else {
            $users = D('users')->where("is_distribut=1 and first_leader = 0")->select();
        }
//         dump($users);
//         exit;
        $this->assign("users", $users);
        $this->display();
    }

    public function gets()
    {
        $id = I('get.id');
        $users = D('users')->where("first_leader = {$id}")->select();
        $str = "<ul>";
        foreach ($users as $key => $value) {
            $str = $str . "<li><span class='tree_span' data-id=" . $value['user_id'] . "><i class='icon-folder-open'></i>" . $value['user_id'] . ": " . $value['nickname'] . "</span></li>";
        }
        $str = $str . "</ul>";
        $this->ajaxReturn($str);
    }

    public function withdrawals()
    {

        $condition = array();
        if (empty($_POST)) {
            $begin = date('Y/m/d', (time() - 30 * 60 * 60 * 24));//30天前
            $end = date('Y/m/d', strtotime('+1 days'));
            $this->assign('timegap', $begin . '-' . $end);

        }

        I('status') ? $condition['status'] = trim(I('status')) : false;
        I('user_id') ? $condition['user_id'] = trim(I('user_id')) : false;
        I('account_bank') ? $condition['account_bank'] = trim(I('account_bank')) : false;
        I('account_name') ? $condition['account_name'] = trim(I('account_name')) : false;
        I('create_time') ? $condition['create_time'] = trim(I('create_time')) : false;
        $count = M('withdrawals')->where($condition)->count();
        $Page = new \Think\Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        $list = M('withdrawals')->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->order('create_time DESC')->select();
        $this->assign('list', $list);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();

    }

    public function delWithdrawals($id)
    {
        $model = D('withdrawals')->where("id={$id}")->find();
        $user = D('users')->where("user_id={$model['user_id']}")->find();
        $data['user_money'] = $user['user_money'] + $model['money'];
        $data['frozen_money'] = $user['frozen_money'] - $model['money'];
        $wd = D('withdrawals');
        $us = D('users');
        $wd->startTrans();
        $data1['status']=2;
        $del = $wd->where("id={$id}")->save($data1);
        $up = $us->where("user_id={$user['user_id']}")->save($data);
        if ($del && $up) {
            $wd->commit();
            $this->success('删除成功');
        } else {
            $wd->rollback();
            $this->error('删除失败');
        }

    }

    public function editWithdrawals($id)
    {
        if (IS_POST) {
            //获取操作类型
            $remark = I('post.remark');
            $status = I('post.status');
            $user_id = I('post.user_id');
            $m1 = D('withdrawals');

            $wd = $m1->where(array('id' => $id))->find();
            $wd['remark'] = $remark;
            $wd['status'] = $status;
            //$m2 = D("users");
            $m3 = D('remittance');
            if ($status == 1) {
                $m1->startTrans();
                $r1 = $m1->save($wd);
//                $m2->user_id = $user_id;
//                $user = $m2->where(array('user_id' => $user_id))->find();
//                if ($user['user_money'] < $wd['money']) {
//                    $m1->rollback();
//                    //$this->success("操作成功!",U('Admin/Admin/role_info',array('role_id'=>$data['role_id'])));
//                    exit($this->error('用户余额不足，禁止生成还款记录', U('Admin/Distribut/editWithdrawals', array('id' => $id)), 3));
//                }
//                $m2->user_money = $m2->user_money - $wd['money'];
//                $m2->frozen_money = $m2 > frozen_money - $wd['money'];
//                $r2 = $m2->save();

                $m3->user_id = $wd['user_id'];
                $m3->money = $wd['money'];
                $m3->bank_name = $wd['bank_name'];
                $m3->account_bank = $wd['account_bank'];
                $m3->account_name = $wd['account_name'];
                $m3->remark = $wd['remark'];
                $m3->status = $status;
                $m3->create_time = time();
                $m3->admin_id = session('admin_id');
                $m3->withdrawals_id = $wd['id'];
                $r2 = $m3->add();
                if ($r1 && $r2) {
                    $m1->commit();//成功则提交
                } else {
                    $m1->rollback();//不成功，则回滚
                }
            } else if ($status == 2) {
//                $m1->where(array('id' => $id))->delete();
                $this->delWithdrawals($id);
            } else if ($status == 3) {
                $wd['status'] = 1;
                $r1 = $m1->save($wd);
            }
            exit($this->success('操作成功', U('/Admin/Distribut/withdrawals'), 1));
        }
        $sql = "SELECT * FROM __PREFIX__users as u JOIN __PREFIX__withdrawals wd ON u.user_id=wd.user_id WHERE wd.id={$id}";
        $data = M()->query($sql)[0];
//        dump($data);
//        exit;
        $this->assign("data", $data);
        $this->display();
    }

    public function remittance()
    {
        $condition = array();
        if (empty($_POST)) {
            $begin = date('Y/m/d', (time() - 30 * 60 * 60 * 24));//30天前
            $end = date('Y/m/d', strtotime('+1 days'));
            $this->assign('timegap', $begin . '-' . $end);

        }

        //I('status') ? $condition['status'] = trim(I('status')) : false;
        I('user_id') ? $condition['user_id'] = trim(I('user_id')) : false;
        I('account_bank') ? $condition['account_bank'] = trim(I('account_bank')) : false;
        I('account_name') ? $condition['account_name'] = trim(I('account_name')) : false;
        I('start_time') ? $condition['start_time'] = trim(I('start_time')) : false;
        $count = M('remittance')->where($condition)->count();
        $Page = new \Think\Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        $list = M('remittance')->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->order('create_time DESC')->select();
        $this->assign('list', $list);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function rebate_log()
    {
        $condition = array();
        if (empty($_POST)) {
            $begin = date('Y/m/d', (time() - 30 * 60 * 60 * 24));//30天前
            $end = date('Y/m/d', strtotime('+1 days'));
            $this->assign('timegap', $begin . '-' . $end);

        }

        I('status') ? $condition['status'] = trim(I('status')) : false;
        I('user_id') ? $condition['user_id'] = trim(I('user_id')) : false;
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;
        I('create_time') ? $condition['create_time'] = trim(I('create_time')) : false;

        $count = M('rebate_log ')->where($condition)->count();

        $Page = new \Think\Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
//        dump($Page);
//        exit;
        $show = $Page->show();
        $list = M('rebate_log as log')->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->order('create_time DESC')->select();
        $this->assign('list', $list);
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function editRebate($id)
    {
        $sql = "SELECT *  FROM __PREFIX__rebate_log rl  JOIN __PREFIX__users as u ON u.user_id=rl.user_id WHERE rl.id={$id}";
        $data = M()->query($sql)[0];
//        dump($data);
//        exit;
        $this->assign("data", $data);
        $this->display();
    }
}

