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
namespace Mobile\Controller;
use Home\Logic\CartLogic;
use Think\Controller;
use Think\Page;
use Think\Verify;
class ArticleController extends MobileBaseController {
    public function index(){       
        $article_id = I('article_id',38);
    	$article = D('article')->where("article_id=$article_id")->find();
    	$this->assign('article',$article);
        $this->display();
    }
 

    /**
     * 文章内列表页
     */
    public function articleList(){        
        $list = M('Article')->where("cat_id IN(1,2,3,4,5,6,7)")->select();
        $this->assign('list',$list);
        $this->display();
    }    
    /**
     * 文章内容页
     */
    public function article(){
    	$article_id = I('article_id',1);
    	$article = D('article')->where("article_id=$article_id")->find();
    	$this->assign('article',$article);
        $this->display();
    }     
}