<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class FeedBackController extends BaseController {

    public function index(){
        //获取版本号
        $versionList=$this->getAllVersionList();
        $this->assign("versionList",$versionList);
        $this->display();
    }
    /**
     * 获取用户反馈列表
     */
    public function getFeedBackList(){
        $feedbackSource=getParam("feedbackSource");
        $feedbackType=getParam("feedbackType");
        $versionName=getParam("versionName");
        $feedbackRange=getParam("feedbackRange");
        $where=array();
        if(!empty($feedbackSource)){
            $where["source"]=$feedbackSource;
        }
        if(!empty($feedbackType)){
            $where["type"]=$feedbackType;
        }
        if(!empty($versionName)){
            $where["version_name"]=$versionName;
        }
        if(!empty($feedbackRange)){
            $startTime=date('Y-m-d H:i:s',strtotime("$feedbackRange days"));
            $where["feedback_time"]=array('egt',$startTime);
        }
        $result=pageQuery("feedback",$where);
        $this->ajaxReturn($result);
    }
    /**
     * 获取所有的版本列表
     */
    public function getAllVersionList(){
        $arrTemp=M("feedback")->distinct(true)->field("version_name")->select();
        $versionList=array();
        foreach($arrTemp as $value){
            if(is_array($value)&&!empty($value["version_name"])){
                array_push($versionList,$value["version_name"]);
            }
        }

        return  $versionList;

    }
    /**
     * 更新用户反馈处理状态
     */
    public function updateStatus(){
        $status=getParam("status");
        $id=getParam("id");
        try{
            $feeback["id"]=$id;
            $feeback["status"]=$status;
            M("feedback")->save($feeback);
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }
    }


}
