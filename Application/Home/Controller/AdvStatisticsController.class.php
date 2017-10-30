<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/3
 * Time: 下午2:09
 */

namespace Home\Controller;

use Think\Controller;
/**
 * 广告统计
 * @package Admin\Controller
 */
class AdvStatisticsController extends Controller
{
    //统计入口
    public function statistics(){
         $advToken=getParam("adv_token");
         $userToken=getParam("usertoken");
         if(empty($advToken)||empty($userToken)){
             $this->ajaxReturn('invalid request:adv_token or usertoken is empty');
         }

        $this->handlerRequest();
    }
    /**
     * 解析请求数据
     */
    public function handlerRequest(){

            $advToken=getParam("adv_token");
            $userToken=getParam("usertoken");
            $mark=getParam("mark");
            $ip=get_client_ip();
            $accesstime=date('Y-m-d H:i:s');
            try{
                $data["access_time"]=$accesstime;
                $data["adv_token"]=$advToken;
                $data["user_token"]=$userToken;
                $data["access_ip"]=$ip;
                $data["mark"]=$mark;
                M('adv_access_log')->add($data);
            }catch(Exception $e){

            }
    }

    /**
     * 更新系统消息发送数量
     */
    public function updateSystemInfoSendNum(){
        $advToken=getParam("token");
        if(empty($advToken)){
            return;
        }
        $res=M("advertisement")->where("token='$advToken'")->select();
        if(empty($res)){
            //没找到相应广告
            return;
        }
        $data["id"]=$res[0]["id"];
        $data["send"]=$res[0]["send"]+1;
       M("advertisement")->where("token='$advToken'")->save($data);

    }
    /**
     * 统计测试页面
     */
    public function test(){
        $this->display();
    }
}