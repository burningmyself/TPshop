<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;

class TopicController extends BaseController {
	/*
	 * 专题列表
	 */
	public function topicList(){
		$topicList = M('topic')->where("topic_state=2")->select();
		$this->assign('topicList',$topicList);
		$this->display();
	}
	
	/*
	 * 专题详情
	 */
	public function detail(){
		$topic_id = I('topic_id',1);
		$topic = D('topic')->where("topic_id=$topic_id")->find();
		$this->assign('topic',$topic);
		$this->display();
	}
	
	public function info(){
		$topic_id = I('topic_id',1);
		$topic = D('topic')->where("topic_id=$topic_id")->find();                
        echo htmlspecialchars_decode($topic['topic_content']);                
        exit;
	}
}