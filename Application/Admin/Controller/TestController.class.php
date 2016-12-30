<?php
/**
 * Created by PhpStorm.
 * User: yang_
 * Date: 2016/9/11
 * Time: 9:43
 */

namespace Admin\Controller;


class TestController
{
    //http://localhost:8088/index.php/Admin/Test/index
    public function index()
    {
//        $order = M('order')->where(" order_id=746")->find();
//        $distributLogic = new \Common\Logic\DistributLogic();
//        $distributLogic->rebate_log($order);
//        dump($distributLogic);
//        exit;
        $distributLogic = new \Common\Logic\DistributLogic();
        $distributLogic->auto_confirm();
    }
}