<?php
/**
 * 功能：
 * 1）登录态管理；
 * User: pengjixiang
 * Date: 16/3/23
 * Time: 下午4:14
 */
namespace Home\Controller;
use Think\Controller;
class BaseController extends  Controller{
    protected function _initialize(){
    }


    protected  function jsonReturn($res){

        if($res->state!=0){
            if($res->state==-1102001){
                session("account",null);
                session("token",null);
                session("privatekey",null);
            }
            $data['state']=$res->state;
            $data['msg'] =C("errorcode")[$res->state];
            $this->ajaxReturn($data);
            return;
        }
        $this->ajaxReturn($res);
    }


} 