<?php
/**
 * Created by PhpStorm.
 * User: wangdong
 * Date: 16/6/2
 * Time: 上午11:13
 */

namespace Home\Controller;


use Think\Controller;

class MoreIssuesController extends BaseController{



    /**
     *   支付停车费 更多
     */
    public function payParkFee(){

        $this->display();

    }


    /**
     *   月租服务 更多
     */
    function monthlyService(){

        $this->display();
    }


    /**
     *   消息推送 更多
     */
    function messagePush(){

        if(is_weixin()){
            $this->redirect("Home/MoreIssues/messagePushweixin");
        }
        $this->display();
    }
    public function messagePushweixin(){
        $this->display();
    }



}


