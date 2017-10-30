<?php

namespace Home\Controller;


use Think\Controller;

class ParkApiController extends BaseController{
    /**
     * 获取可临停缴费停车场列表（首页）
     */
    public function getCanProStopParksHome(){

        $res=D("Park","Api")->getCanProStopParksHome();
        $this->jsonReturn($res);
    }
    /**
     * 获取可临停缴费停车场列表（分页）
     */
    public function getCanProStopParks(){

        $res=D("Park","Api")->getCanProStopParks();
        $this->jsonReturn($res);
    }
} 