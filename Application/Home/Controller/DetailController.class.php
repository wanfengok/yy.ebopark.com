<?php
/**
 *  用户反馈详情页面
 * User: wangdong
 * Date: 16/6/2
 * Time: 下午3:50
 */

namespace Home\Controller;


use Think\Controller;

class DetailController extends BaseController{


    /**
     *   停车缴费 页面1
     */
    function payParkFee1(){

        $this->display();

    }

    /**
     *   停车缴费 页面2
     */
    function payParkFee2(){

        $this->display();

    }

    /**
     *   停车缴费 页面3
     */
    function payParkFee3(){

        $this->display();

    }


    /**
     *   停车缴费 页面4
     */
    function payParkFee4(){

        $this->display();

    }

    /**
     *   停车缴费 页面5
     */
    function payParkFee5(){

        $this->display();

    }

    /**
     *   停车缴费 页面6
     */
    function payParkFee6(){

        $this->display();

    }

    /**
     *   停车缴费 页面7
     */
    function payParkFee7(){
        $content='通过【我的e泊】-【我的余额】，进行充值；余额可用于停车缴费、月租购买和月租续费。';
        if(is_weixin()){
            $content='通过点击【充值-租车位】-【充值】，或者点击【充值-租车位】-【我的e泊】进行充值；余额可用于停车缴费、月租购买和月租续费。';
        }
        $this->assign("content",$content);
        $this->display();

    }


    /**
     *   停车缴费 页面8
     */
    function payParkFee8(){

        $this->display();

    }

    /**
     *   停车缴费 页面9
     */
    function payParkFee9(){

        $this->display();

    }










    /**
     *    月租服务1
     */
    function monthlyService1(){
        $content='1、来e泊抢购时段月租车位，点击【车位租赁】，选择心仪的月租车位，一键购买； 或者通过【我的e泊】，点击【月租服务】，了解更详细的月租服务内容；';
        if(is_weixin()){
            $content='1、来e泊抢购时段月租车位，点击【e生活】->【车位租赁】，选择心仪的月租车位，一键购买；';
        }
        $this->assign("content",$content);

        $this->display();
    }

    /**
     *    月租服务2
     */
    function monthlyService2(){
        $this->display();
    }


    /**
     *    月租服务3
     */
    function monthlyService3(){
        $this->display();
    }


    /**
     *    月租服务4
     */
    function monthlyService4(){
        $this->display();
    }


    /**
     *    月租服务5
     */
    function monthlyService5(){
        $this->display();
    }








    /**
     *    消息推送1
     */
    function messagePush1(){
        $content='通过【我的e泊】->【消息中心】，查看用户消息，您的车辆入场消息和车辆出场消息都能查看。';
        if(is_weixin()){
            $content='您的车辆入场消息和车辆出场消息可在微信公众号上查看。';
        }
        $this->assign("content",$content);
        $this->display();
    }

    /**
     *    消息推送2
     */
    function messagePush2(){
        $this->display();
    }

    /**
     *    消息推送3
     */
    function messagePush3(){
        $this->display();
    }

    /**
     *    消息推送4
     */
    function messagePush4(){
        $this->display();
    }

    /**
     *    消息推送5
     */
    function messagePush5(){
        $this->display();
    }
    /**
     *    消息推送5
     */
    function messagePush6(){
        $this->display();
    }


}