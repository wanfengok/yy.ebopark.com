<?php
namespace Home\Controller;
use Think\Controller;

class AppController extends Controller
{
    //android端获取服务器端apk版本信息
    public function appUpdate(){
        $sdkInfoa=getSdkInfo();
        if(empty($sdkInfoa)){
            //不需更新
            $data["state"]=0;
            $res["versioncode"]=-1;
            $data["data"]=$res;
            $this->ajaxReturn($data);
            return;
        }
        $data["state"]=0;
        $data["data"]=$sdkInfoa;
        $this->ajaxReturn($data);
    }
}