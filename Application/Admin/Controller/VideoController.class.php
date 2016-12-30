<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/12/19
 * Time: 16:07
 */

namespace Admin\Controller;

namespace Admin\Controller;

use Think\AjaxPage;
use Think\Model;
use Think\Page;

class VideoController extends BaseController
{
    public function video_type_list()
    {
        $supplier_model = M('video_type');
        $typer_count = $supplier_model->count();
        $page = new Page($typer_count, 10);
        $show = $page->show();
        $type_list = $supplier_model
            ->order("sort desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign('list', $type_list);
        $this->assign('page', $show);
        $this->display();
    }

    public function typeHandle()
    {
        $data = I('post.');
        $type_model = M('video_type');

        //增
        if ($data['act'] == 'add') {
            unset($data['type_id']);
            $count = $type_model->where("name='" . $data['name'] . "'")->count();
            if ($count) {
                $this->error("此名称已被使用，请更换", U('Admin/Video/type_info'));
            } else {
                $r = $type_model->add($data);
            }
        }
        //改
        if ($data['act'] == 'edit' && $data['type_id'] > 0) {
            $r = $type_model->where('type_id=' . $data['type_id'])->save($data);
        }
        //删
        if ($data['act'] == 'del' && $data['type_id'] > 0) {
            $r = $type_model->where('type_id=' . $data['type_id'])->delete();
        }

        if ($r !== false) {
            $this->success("操作成功", U('Admin/Video/video_type_list'));
        } else {
            $this->error("操作失败", U('Admin/Video/video_type_list'));
        }
    }

    public function type_info()
    {
        $type_id = I('get.type_id', 0);
        if ($type_id) {
            $model = M('video_type');
            $info = $model->where("type_id={$type_id}")->find();
            $this->assign('info', $info);
        }
        $act = empty($type_id) ? 'add' : 'edit';
        $this->assign('act', $act);
        $this->display();
    }


    public function video_list()
    {
        $supplier_model = M('video');
        $typer_count = $supplier_model->count();
        $page = new Page($typer_count, 10);
        $show = $page->show();
        $type_list = $supplier_model
            ->order("sort desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign('list', $type_list);
        $this->assign('page', $show);
        $this->display();
    }

    public function videoHandle()
    {
        $data = I('post.');
        $type_model = M('video');
        //dump($data);exit;
        //增
        if ($data['act'] == 'add') {
            unset($data['video_id']);
            $count = $type_model->where("title='" . $data['title'] . "'")->count();
            if ($count) {
                $this->error("此名称已被使用，请更换", U('Admin/Video/video_info'));
            } else {
                $data['play_time'] = strtotime($data['play_time']);
                $r = $type_model->add($data);
            }
        }
        //改
        if ($data['act'] == 'edit' && $data['video_id'] > 0) {
            $data['play_time'] = strtotime($data['play_time']);
            $r = $type_model->where('video_id=' . $data['video_id'])->save($data);
        }
        //删
        if ($data['act'] == 'del' && $data['type_id'] > 0) {
            $r = $type_model->where('video_id=' . $data['video_id'])->delete();
        }

        if ($r !== false) {
            $this->success("操作成功", U('Admin/Video/video_list'));
        } else {
            $this->error("操作失败", U('Admin/Video/video_list'));
        }
    }

    public function video_info()
    {
        $video_id = I('get.video_id', 0);
        if ($video_id) {
            $model = M('video');
            $info = $model->where("video_id={$video_id}")->find();
            $this->assign('info', $info);
        }
        $act = empty($video_id) ? 'add' : 'edit';
        $video_type = M('video_type')->select();
        $this->assign('video_type', $video_type);
        $this->assign('act', $act);
        $this->display();
    }
}