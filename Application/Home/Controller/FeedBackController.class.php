<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/5/27
 * Time: 下午6:02
 */

namespace Home\Controller;


class FeedBackController extends BaseController{
    /**
     * 反馈首页
     */
    public function index(){
        $network_type=getParam("networkType");
        $version_name=getParam("versionName");
        $mobile=getParam("mobile");
        $source=getParam("source");

        session("source",$source);
        session("mobile",$mobile);
        session("version_name",$version_name);
        session("network_type",$network_type);

        $this->display();
    }

    public function carManage(){
        if(is_weixin()){
            $this->redirect("/Home/FeedBack/car_manage_wx");
        }
        $this->redirect("/Home/FeedBack/car_manage");
    }
    public function monthRenew(){
        if(is_weixin()){
            $this->redirect("/Home/FeedBack/month_renew_wx");
        }
        $this->redirect("/Home/FeedBack/month_renew");
    }
    public function parkfee(){
        if(is_weixin()){
            $this->redirect("/Home/FeedBack/parking_wx");
        }
        $this->redirect("/Home/FeedBack/parking");

    }
    public function monthlypayableParks(){
        $this->display();
    }
    /**
     * 车辆管理
     */
    public function car_manage(){
        $this->display();
    }
    /**
     * 月租续费
     */
    public function month_renew(){
        $this->display();
    }
    /**
     * 临停缴费
     */
    public function parking(){
        $this->display();
    }
    /**
     * 车辆管理-微信
     */
    public function car_manage_wx(){
        $this->display();
    }
    /**
     * 月租续费-微信
     */
    public function month_renew_wx(){
        $this->display();
    }
    /**
     * 临停缴费-微信
     */
    public function parking_wx(){
        $this->display();
    }
    /**
     * 可支付的停车场
     */
    public function payableParks(){
        $this->display();
    }

} 