<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/10/31
 * Time: 14:22
 */

namespace Admin\Controller;

use Think\AjaxPage;
use Think\Model;
use Think\Page;


class YellowController extends BaseController
{
    public function yellow_type()
    {
        $list = M('yellow_type')->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function addEditType()
    {
        if (IS_GET) {
            $type_id = I('get.type_id', -1);
            $where = " type_id=$type_id";
            $is_ajax = 0;
            if ($type_id > 0) {
                $is_ajax = 1;
            }
            $type = M('yellow_type')->where($where)->find();
//
//            if($type==null){
//                $type['parent_id']=0;
//            }
            if ($type['parent_id'] != 0) {
                //查出上级分类
                $temp = M('yellow_type')->where("type_id={$type['parent_id']}")->find();
                $children = M('yellow_type')->where("parent_id={$temp['parent_id']}")->select();
                $child = M('yellow_type')->where("type_id={$type['parent_id']}")->find();
                if ($child['parent_id'] != 0) {
                    //$children_next=M('yellow_type')->where("parent_id={$type['parent_id']}")->select();
                    $child_next = M('yellow_type')->where("type_id={$child['parent_id']}")->find();
                    $this->assign('children', $children);
                    $this->assign('first', $child_next['type_id']);
                    $this->assign('two', $child['type_id']);
                } else {
                    $this->assign('two', 0);
                    $this->assign('first', $child['type_id']);
                }
            } else {
                $this->assign('two', 0);
                $this->assign('first', 0);
            }
            $this->assign('type', $type);
            $list = M('yellow_type')->where("type_id<>$type_id and parent_id =0 ")->select();
            $this->assign('list', $list);
            $this->assign('is_ajax', $is_ajax);
            $this->display();
            exit;
        }
        if (IS_POST) {
            $data = I('post.');
            if ($data['parent_id_1']) {
                $data['parent_id'] = $data['parent_id_1'];
            }
            $is_ajax = I('get.is_ajax', 0);
            if ($is_ajax == 0) {
                $data['add_time'] = time();
                $id = M('yellow_type')->data($data)->add();
                if ($id > 0) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/yellow_type')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            if ($is_ajax == 1) {
                $result = M('yellow_type')->save($data);
                if ($result > 0) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/yellow_type')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }
    }

    public function delYellowType($type_id)
    {
        $children = M('yellow_type')->where("parent_id=$type_id")->select();
        if ($children != null) {
            $this->error("该分类下还有分类不得删除!!!", U('Admin/Yellow/yellow_type'));
        }
        $result = M('yellow_type')->where("type_id=$type_id")->delete();
        if ($result) {
            $this->success("操作成功!!!", U('Admin/Yellow/yellow_type'));
        }
    }

    public function get_type($parent_id)
    {
        $list = M('yellow_type')->where("parent_id=$parent_id")->select();
        $html = null;
        foreach ($list as $item) {
            $html = $html . "<option value='{$item['type_id']}'>{$item['name']}</option>";
        }
        echo $html;
        exit;
    }

    public function yellow_info_list()
    {
        $yellow_types = M('yellow_type')->select();
        $this->assign('yellow_types', $yellow_types);
        $this->display();
    }

    public function addEditYellowInfo()
    {
        $list = M('yellow_type')->select();
        $is_ajax = I('get.is_ajax', 0);
        if (IS_GET) {
            $info_id = I('get.info_id', 0);
            $yelow_info = M('yellow_info')->where("info_id=$info_id")->find();
            if ($yelow_info == null) {
                $yelow_info['type_id'] = 0;
            } else {
                $is_ajax = 1;
                if ($yelow_info['img_list']) {
                    $img_list = explode(',', $yelow_info['img_list']);
                    $this->assign('img_list', $img_list);
                }
            }
            $this->assign('yelow_info', $yelow_info);
            $this->assign('list', $list);
            $this->assign('is_ajax', $is_ajax);
            $this->display();
        }
        if (IS_POST) {
            $data = I('post.');
            $img_str = "";
            foreach ($data['img_list'] as $item) {
                $data['img_url'] = $item;
                $img_str = $img_str . $item . ',';
            }

            $data['img_list'] = trim($img_str, ',');
            if ($is_ajax == 0) {
                $data['add_time'] = time();
                $info_id = M('yellow_info')->add($data);
                if ($info_id > 0) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/yellow_info_list')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            if ($is_ajax == 1) {
                $result = M('yellow_info')->save($data);
                if ($result) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/yellow_info_list')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }
    }

    public function ajax_yellow_list($p)
    {
        $where = ' 1 = 1 '; // 搜索条件
        I('type_id') && $where = "$where and info.type_id = " . I('type_id');
        (I('is_show') !== '') && $where = "$where and info.is_show = " . I('is_show');

        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';

        if ($key_word) {
            $where = "$where and (info.name like '%$key_word%')";
        }

        $sql = "SELECT count(*) as count FROM __PREFIX__yellow_info info  JOIN __PREFIX__yellow_type as ty ON info.type_id=ty.type_id WHERE " . $where;

        $count = M()->query($sql);
        $Page = new AjaxPage($count[0]['count'], 10);
        $sql = "SELECT info.*,ty.name as type_name FROM __PREFIX__yellow_info info  JOIN __PREFIX__yellow_type as ty ON info.type_id=ty.type_id WHERE " . $where;
        $order_by = $_POST['orderby1'];
        $sort = $_POST['orderby2'];
        $sql = $sql . " order by info.{$order_by} " . $sort . ' limit ' . $Page->firstRow . ',' . $Page->listRows;
        $show = $Page->show();

        $lists = M()->query($sql);

        $this->assign('page', $show);
        $this->assign('lists', $lists);
        $this->display();
    }

    public function del_Yellow_Info($info_id)
    {
        $result = M('yellow_info')->where("info_id=$info_id")->delete();
        if ($result) {
            $msg = '操作成功';
        } else {
            $msg = '操作失败';
        }
        $return_arr = array(
            'status' => 1,
            'msg' => $msg,
            'data' => array('url' => U('Admin/Yellow/yellow_info_list')),
        );
        $this->ajaxReturn(json_encode($return_arr));
    }

    public function ad_yellow_list()
    {
        $this->display();
    }

    public function addEditAd()
    {
        $list = M('yellow_type')->select();
        $is_ajax = I('get.is_ajax', 0);
        if (IS_GET) {
            $ad_id = I('get.ad_id', 0);
            $ad_type = M('ad_type')->where("ad_id=$ad_id")->find();
            if ($ad_type != null) {
                $is_ajax = 1;
            }
            $this->assign('list', $list);
            $this->assign('ad_type', $ad_type);
            $this->assign('is_ajax', $is_ajax);
            $this->display();
        }
        if (IS_POST) {
            $data = I('post.');
            $data['name'] = M('yellow_type')->where("type_id={$data['type_id']}")->field('name')->find()['name'];
            if ($data['name'] == null) {
                $data['name'] = "热搜索推荐";
            }
            if ($is_ajax == 0) {
                $data['add_time'] = time();
                $info_id = M('ad_type')->add($data);
                if ($info_id > 0) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/ad_yellow_list')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            if ($is_ajax == 1) {
                $result = M('ad_type')->save($data);
                if ($result) {
                    $msg = '操作成功';
                } else {
                    $msg = '操作失败';
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => $msg,
                    'data' => array('url' => U('Admin/Yellow/ad_yellow_list')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }
    }

    public function ajax_ad_list($p)
    {
        $where = ' 1 = 1 '; // 搜索条件
        (I('is_show') !== '') && $where = "$where and info.is_show = " . I('is_show');

        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if ($key_word) {
            $where = "$where and (name like '%$key_word%')";
        }

        $sql = "SELECT count(*) as count FROM __PREFIX__ad_type  WHERE " . $where;

        $count = M()->query($sql);
        $Page = new AjaxPage($count[0]['count'], 10);
        $sql = "SELECT * FROM __PREFIX__ad_type WHERE " . $where;
        $order_by = $_POST['orderby1'];
        $sort = $_POST['orderby2'];
        $sql = $sql . "order by {$order_by} " . $sort . ' limit ' . $Page->firstRow . ',' . $Page->listRows;
        $show = $Page->show();
        $lists = M()->query($sql);
        $this->assign('page', $show);
        $this->assign('lists', $lists);
        $this->display();
    }

    public function del_Yellow_ad($ad_id)
    {
        $result = M('ad_type')->where("ad_id=$ad_id")->delete();
        if ($result) {
            $msg = '操作成功';
        } else {
            $msg = '操作失败';
        }
        $return_arr = array(
            'status' => 1,
            'msg' => $msg,
            'data' => array('url' => U('Admin/Yellow/ad_yellow_list')),
        );
        $this->ajaxReturn(json_encode($return_arr));
    }
}