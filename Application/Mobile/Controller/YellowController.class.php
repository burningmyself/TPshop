<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/11/2
 * Time: 9:50
 */

namespace Mobile\Controller;


class YellowController extends MobileBaseController
{
    public function index()
    {

        //热点搜索关键词
        $keyword_hot = M('yellow_keyword')->where('is_hot=1')->page(1, 15);
        //左侧导航类别
        $left_nav = M('yellow_type')->where("is_show =1 and parent_id=1")->order('sort desc')->select();
        $this->assign('left_nav', $left_nav);
        $this->assign('keyword_hot', $keyword_hot);
        $this->display();
    }

//热点搜索
    public function hot_search()
    {
        //广告
        $pid = M('ad_type')->where("is_show=1 and is_hot=1 and type_id=0")->field('pid')->find();
        if($pid['pid'] == null)$pid['pid']=0;
        $this->assign('pid', $pid['pid']);
        //商家推荐
        $bus_hot = M('yellow_info')->where('is_hot=1 and is_show=1')->order('sort desc')->limit(0, 4)->select();
        $this->assign('bus_hot', $bus_hot);
        //热点商家
        $hot_business = M('yellow_info')->where('is_hot=1 and is_show=1')->page(1, 10)->select();
        $this->assign('hot_business', $hot_business);
        $this->display();
    }

//商家列表
    public function get_yellow_info($type_id)
    {
        //广告
        $pid = M('ad_type')->where("is_show=1 and is_hot=1 and type_id={$type_id}")->field('pid')->find();
        if($pid['pid'] == null)$pid['pid']=0;
        $this->assign('pid', $pid['pid']);
        //商家推荐
        $bus_hot = M('yellow_info')->where("is_hot=1 and is_show=1 and type_id={$type_id}")->order('sort desc')->limit(0, 4)->select();
        $this->assign('bus_hot', $bus_hot);
        //热点商家
        $hot_business = M('yellow_info')->where("is_hot=1 and is_show=1 and type_id={$type_id}")->select();
        $this->assign('hot_business', $hot_business);
        //其他商家
        $other_bus = M('yellow_info')->where("is_hot=0 and is_show=1 and type_id={$type_id}")->select();
        $this->assign('other_bus', $other_bus);
        $this->display();
    }

//商家详情
    public function get_detail($info_id)
    {
        $info = M('yellow_info')->where("info_id=$info_id and is_show=1")->find();
        if ($info != null) {
            $images = explode(',', $info['img_list']);
            $this->assign('images', $images);
        }
//        dump($images);exit;
        $this->assign('info', $info);
        $this->display();
    }

    //收索结果
    public function search_result()
    {
        $keyWord = I('keyWord', '');
        $this->assign('keyWord', $keyWord);
        $this->display();
    }

    public function get_search_result($keyWord)
    {
        $infos = M('yellow_info')->where(" name like '%{$keyWord}%'")->select();
        $this->assign('infos', $infos);
        $this->display();
    }
}