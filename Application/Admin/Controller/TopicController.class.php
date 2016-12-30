<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: 当燃      
 * 专题管理
 * Date: 2015-09-09
 */

namespace Admin\Controller;

class TopicController extends BaseController {

    public function index(){
        $this->display();
    }
    
    public function topic(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$topic_id = I('GET.topic_id');
    	$topic_info = array();
    	if($topic_id){
    		$topic_info = D('topic')->where('topic_id='.$topic_id)->find();
    		$this->assign('info',$topic_info);
    	}
    	
    	$this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'topic')));
    	$this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'topic')));
    	$this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'topic')));
    	$this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'topic')));
    	$this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'topic')));
    	$this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'topic')));
    	$this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'topic')));
    	$this->assign("URL_Home", "");
    	$this->display();
    }
    
    public function topicList(){
    	$Ad =  M('topic');
    	$res = $Ad->where('1=1')->order('ctime')->page($_GET['p'].',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$val['topic_state'] = $val['topic_state']>1 ? '已发布' : '未发布';
    			$val['ctime'] = date('Y-m-d H:i',$val['ctime']);
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);// 赋值数据集
    	$count = $Ad->where('1=1')->count();// 查询满足要求的总记录数
    	$Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $Page->show();// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    
    public function topicHandle(){
    	$data = I('post.');       
        $data['topic_content'] = $_POST['topic_content']; // 这个内容不做转义        
    	if($data['act'] == 'add'){
    		$data['ctime'] = time();
    		$r = D('topic')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = D('topic')->where('topic_id='.$data['topic_id'])->save($data);
    	}
    	 
    	if($data['act'] == 'del'){
    		$r = D('topic')->where('topic_id='.$data['topic_id'])->delete();
    		if($r) exit(json_encode(1));
    	}
    	 
    	if($r){
    		$this->success("操作成功",U('Admin/Topic/topicList'));
    	}else{
    		$this->error("操作失败",U('Admin/Topic/topicList'));
    	}
    }
}