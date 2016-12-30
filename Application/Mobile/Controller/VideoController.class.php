<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/12/19
 * Time: 11:43
 */
namespace Mobile\Controller;


class VideoController extends MobileBaseController
{
    public function __construct()
    {
        parent::__construct();
        //视频类型
        $types = M('video_type')->where('is_show=1')->order("sort desc")->select();
        $this->assign('types', $types);
    }

    public function index()
    {

        //获取广告 600的广告数量
        $ad_count = M('ad')->where("pid=600")->count();

        //推荐的视频类型
        $types_hot = M('video_type')->where('is_show=1 and is_hot=1')->order("sort desc")->select();
        //最热的四个视频
        $hot_videos = M('video')->where("is_show=1 and is_hot=1 ")->order('sort desc,play_count desc')->limit(0, 4)->select();
        $this->assign("hot_videos", $hot_videos);

        $this->assign('types_hot', $types_hot);
        $this->assign('ad_count', $ad_count);

        $this->display();
    }

    public function get_type_hot_video_list($type_id)
    {
        $video_list = M("video")->where("type_id=$type_id and is_show=1 and is_hot=1 ")->order('sort desc')->limit(0, 4)->select();
        $this->assign("video_list", $video_list);
        $this->display();
    }

    public function video_type_list($type_id, $name)
    {
        $this->assign('name', $name);
        $this->assign('type_id', $type_id);
        $this->display();
    }

    public function ajax_video_type_list($type_id, $page)
    {
        $start = $page * 8;
        $video_list = M("video")->where("type_id=$type_id and is_show=1")->order('sort desc,play_count desc')->limit($start, 8)->select();
        $this->assign('video_list', $video_list);
        $this->display();
    }

    public function play_video($video_id)
    {
        $video = M("video")->where("video_id=$video_id")->find();
        $video['play_count'] = $video['play_count'] + 1;
        M("video")->save($video);
        $this->assign('video', $video);
        $video_hot = M("video")->where("type_id={$video['type_id']} and is_show=1 and is_hot=1 ")->order('sort desc')->limit(0, 4)->select();
        $this->assign("video_hot", $video_hot);
        $this->display();
    }

    public function video_hot_list($name)
    {
        $this->assign('name', $name);
        $this->display();
    }

    public function ajax_video_hot_list($page)
    {
        $start = $page * 8;
        $video_list = M("video")->where("is_show=1 and is_hot=1")->order('sort desc,play_count desc')->limit($start, 8)->select();
        $this->assign('video_list', $video_list);
        $this->display();
    }
}