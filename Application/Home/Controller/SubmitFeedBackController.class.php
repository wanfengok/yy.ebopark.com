<?php
/**
 * Created by PhpStorm.
 * User: wangdong
 * Date: 16/6/2
 * Time: 上午10:50
 */


namespace Home\Controller;


use Think\Controller;

class SubmitFeedBackController extends BaseController{


    /**
     * 意见反馈界面
     */
    function report(){
        $this->display();
    }

    /**
     * 提交反馈
     */
    function submitForm(){

        $type=getParam("type");
        $content=getParam("content");

        $data["type"]=$type;
        $data["source"]= session("source");
        $data["content"]=$content;
        $data["feedback_time"]=date('Y-m-d H:i:s');
        $data["version_name"]=session("version_name");
        $data["mobile"]=session("mobile");
        $data["device_type"]=$this->getDeviceType();
        $network_type=session("network_type");
        $data["network_type"]=empty($network_type)?'':$network_type;
        if(!$this->checkForm()){
            $res["state"]=-1;
            $res["msg"]="参数错误";
            $this->ajaxReturn($res);
            return;
        }
        try{
            M("feedback")->add($data);
            $res["state"]=0;
            $this->ajaxReturn($res);
        }catch(Exception $e){
            $res["state"]=-1;
            $res["msg"]=$e->getMessage();
            $this->ajaxReturn($res);
        }

    }
    private function checkForm(){

        $version_name=session("version_name");
        $mobile=session("mobile");
        $source=session("source");
        $type=getParam("type");
        $content=getParam("content");

        if(empty($version_name)||empty($mobile)||empty($source)||empty($type)||empty($content)){
//            return false;
        }
        return true;




    }
    /**
     * 获取设备类型
     */
    private function getDeviceType(){

        $http_user_agent=$_SERVER['HTTP_USER_AGENT'];
        $http_user_agent_arr=explode("(",$http_user_agent);
        $device_type='';
        if(count($http_user_agent_arr)!=0){
            $device_type=explode(")",$http_user_agent_arr[1])[0];
        }
        return $device_type;

    }





}