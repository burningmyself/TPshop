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
use Think\Page;
use Think\Verify;
class IndexController extends BaseController {
    
    /* 
    public function test(){
        $goods_id_arr = M('goods')->where("goods_id >= 140")->getField('goods_id',true);
        foreach($goods_id_arr as $key => $val)
        {
            $goods_image_url = M('goods_images')->where("goods_id = $val")->getField('image_url',3);
            
            $data = array(                    
                    'goods_id'=>$val,
                    'email'=>'www.99soubao.com',
                    'username'=>'淑女',
                    'content'=>'买回来用了一段时间, 真心感觉bucuo  ....',
                    'deliver_rank'=>rand(0, 5),
                    'add_time'=>time(),
                    'ip_address'=>'127.0.0.1',
                    'is_show'=>1,
                    'parent_id'=>0,
                    'user_id'=>1,
                    //'img'=>serialize($goods_image_url),
                    'order_id'=>1,
                    'goods_rank'=>rand(0, 5),
                    'service_rank'=>rand(0, 5),
            );
            M('comment')->add($data);
            
           // M('goods_consult')->execute("insert into tp_goods_consult (goods_id,username,add_time,consult_type,content,parent_id) (
//select $val,username,add_time,consult_type,content,parent_id from `tp_goods_consult`  where goods_id = 95)");
            //M('comment')->where("goods_id = $val")->save(array('img'=>serialize($goods_image_url)));
        }
    }
    */ 
    
    public function index(){         
                       
        // 如果是手机跳转到 手机模块
        if(true == isMobile()){
            header("Location: ".U('Mobile/Index/index'));
        }
        
        $hot_goods = $hot_cate = $cateList = array();
        $sql = "select a.goods_name,a.goods_id,a.shop_price,a.market_price,a.cat_id,b.parent_id_path,b.name from __PREFIX__goods as a left join ";
        $sql .= " __PREFIX__goods_category as b on a.cat_id=b.id where a.is_hot=1 and a.is_on_sale=1 order by a.sort";//二级分类下热卖商品       
        $index_hot_goods = M()->query($sql);//首页热卖商品
		if($index_hot_goods){
			foreach($index_hot_goods as $val){
				$cat_path = explode('_', $val['parent_id_path']);
				$hot_goods[$cat_path[1]][] = $val;
			}
		}

        $hot_category = M('goods_category')->where("is_hot=1 and level=3 and is_show=1")->select();//热门三级分类
        foreach ($hot_category as $v){
        	$cat_path = explode('_', $v['parent_id_path']);
        	$hot_cate[$cat_path[1]][] = $v;
        }
        
        foreach ($this->cateTrre as $k=>$v){
            if($v['is_hot']==1){
        		$v['hot_goods'] = empty($hot_goods[$k]) ? '' : $hot_goods[$k];
        		$v['hot_cate'] = empty($hot_cate[$k]) ? '' : $hot_cate[$k];
        		$cateList[] = $v;
        	}
        }
        
        $this->assign('cateList',$cateList); 
        $this->display();
    }
 
    /**
     *  公告详情页
     */
    public function notice(){
        $this->display();
    }
    
    // 二维码
    public function qr_code(){        
        // 导入Vendor类库包 Library/Vendor/Zend/Server.class.php
        //http://www.tp-shop.cn/Home/Index/erweima/data/www.99soubao.com
         require_once 'ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php';
          //import('Vendor.phpqrcode.phpqrcode');
            error_reporting(E_ERROR);            
            $url = urldecode($_GET["data"]);
            \QRcode::png($url);          
    }
    
    // 验证码
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : '';
        $fontSize = I('get.fontSize') ? I('get.fontSize') : '40';
        $length = I('get.length') ? I('get.length') : '4';
        
        $config = array(
            'fontSize' => $fontSize,
            'length' => $length,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);        
    }
    
    // 促销活动页面
    public function promoteList()
    {                          
        $Model = new \Think\Model();
        $goodsList = $Model->query("select * from __PREFIX__goods as g inner join __PREFIX__flash_sale as f on g.goods_id = f.goods_id   where ".time()." > start_time  and ".time()." < end_time");                        
        $brandList = M('brand')->getField("id,name,logo");
        $this->assign('brandList',$brandList);
        $this->assign('goodsList',$goodsList);
        $this->display();
    }
    
    function truncate_tables (){
        $model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $tables = $model->query("show tables");
        $table = array('tp_admin','tp_config','tp_region','tp_system_module','tp_admin_role','tp_system_menu');
        foreach($tables as $key => $val)
        {                                    
            if(!in_array($val['tables_in_tpshop'], $table))                             
                echo "truncate table ".$val['tables_in_tpshop'].' ; ';
                echo "<br/>";         
        }                
    }

}