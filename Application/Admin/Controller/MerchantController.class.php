<?php
namespace Admin\Controller;
use Think\Exception;

/**
 * 商户管理
 */
class MerchantController extends BaseController
{

    /**
     * 商户列表页面
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 商户添加页面
     */
    public function addMerchant(){
        $id=getParam("id");
        $json="";
        if($id!="0"){
            $array=M("merchant")->where('id=%d',$id)->select()[0];
            $json=json_encode($array);
        }
        //加载活动
        $activity=json_encode(M("merchant_activity")->select());

        $this->assign("id",$id);
        $this->assign("json",$json);
        $this->assign("activity",$activity);
        $this->display();
    }

    /**
     * 添加商户
     */
    public function add(){
        $id=getParam("id");
        $username=getParam("username");
        $realname=getParam("realname");
        $mobile=getParam("mobile");
        $email=getParam("email");
        $pwd=getParam("pwd");
        $project=getParam("project");
        $merchantname=getParam("merchantname");
        $merchantaddr=getParam("merchantaddr");
        $merchanttel=getParam("merchanttel");
        $storename=getParam("storename");

        $data["username"]=$username;
        $data["realname"]=$realname;
        $data["mobile"]=$mobile;
        $data["email"]=$email;
        $data["pwd"]=$pwd;
        $data["project"]=$project;
        $data["merchantname"]=$merchantname;
        $data["merchantaddr"]=$merchantaddr;
        $data["merchanttel"]=$merchanttel;
        $data["storename"]=$storename;
        $res="";
        if($id==0){
            $res= M("merchant")->add($data);
            if(empty($res)){
                $this->error("添加失败,请稍后重试");
            }else{
                $this->success("添加成功","index");
            }
        }else{
            $data["id"]=$id;
            $res= M("merchant")->save($data);
            if(empty($res)&&$res<0){
                $this->error("修改失败,请稍后重试");
            }else {
                $this->success("修改成功", "index");
            }
        }
    }

    /**
     * 获取商户列表
     */
    public function getMerchantList()
    {
        $activities=M("merchant_activity")->select();
        foreach ($activities as $key => $value) {
            $activitiydic[$value["activitycode"]] = $value;
        }
        $res = $this->merchantPageQuery('merchant');
        $data = array();
        foreach ($res["data"] as $key => $value) {
            $item = array();
            $item["username"]=$value["username"];
            $item["realname"]=$value["realname"];
            $item["mobile"]=$value["mobile"];
            $item["email"]=$value["email"];
            $item["pwd"]=$value["pwd"];

            //加载活动名称
            $arr=explode(',',$value["project"]);
            $pstr="";
            foreach($arr as $key=>$v){
                if(!is_null($activitiydic[$v])){
                    $pstr.=$activitiydic[$v]["activityname"]." ";
                }
            }
            $item["project"]=$pstr;
            if(!is_null($activitiydic[$value["project"]])){
                $item["project"]=$activitiydic[$value["project"]]["activityname"];
            }
            $item["merchantname"]=$value["merchantname"];
            $item["merchantaddr"]=$value["merchantaddr"];
            $item["merchanttel"]=$value["merchanttel"];
            $item["storename"]=$value["storename"];
            $item["id"]=$value["id"];
            array_push($data, $item);
        }
        $res["data"]=$data;
        $this->ajaxReturn($res);
    }


    /**
     * 商户信息分页查询
     */
    private function merchantPageQuery()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        try {
            //total
            $total = M('merchant')->count();
            $res = M('merchant')->page($pageIndex, $pageSize)->order("id desc")->select();
            $data["total"] = $total;
            $data["data"] = $res;
            $data["state"] = 0;
            return $data;
        } catch (\Think\Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $data["total"] = 0;
            return $data;
        }
    }

    /**
     * 删除商户
     */
    public function deleteMerchant()
    {
        $id = getParam("id");
        if (!is_numeric($id)) {
            $resp["state"] = -1;
            $resp["msg"] = "参数错误";
            $this->ajaxReturn($resp);
            return;
        }
        try {
            //删除广告
            M("merchant")->where("id=$id")->delete();
            $resp["state"] = 0;
        } catch (Exception $e) {
            $resp["state"] = -1;
            $resp["msg"] = $e->getMessage();
        }
        $this->ajaxReturn($resp);
    }

    /**
     * 活动列表页面
     */
    public function project()
    {
        $this->display();
    }

    /**
     * 获取活动列表
     */
    public function getActivityList()
    {
        $res = $this->activityPageQuery('merchant_activity');
        $this->ajaxReturn($res);
    }

    /**
     * 商户信息分页查询
     */
    private function activityPageQuery()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        try {
            //total
            $total = M('merchant_activity')->count();
            $res = M('merchant_activity')->page($pageIndex, $pageSize)->order("id desc")->select();
            $data["total"] = $total;
            $data["data"] = $res;
            $data["state"] = 0;
            return $data;
        } catch (\Think\Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $data["total"] = 0;
            return $data;
        }
    }

    /**
     * 删除活动
     */
    public function deleteActivity()
    {
        $id = getParam("id");
        if (!is_numeric($id)) {
            $resp["state"] = -1;
            $resp["msg"] = "参数错误";
            $this->ajaxReturn($resp);
            return;
        }
        try {
            //删除广告
            M("merchant_activity")->where("id=$id")->delete();
            $resp["state"] = 0;
        } catch (Exception $e) {
            $resp["state"] = -1;
            $resp["msg"] = $e->getMessage();
        }
        $this->ajaxReturn($resp);
    }

    /**
     * 活动添加页面
     */
    public function addActivity(){
        $id=getParam("id");
        $json="";
        if($id!="0"){
            $array=M("merchant_activity")->where('id=%d',$id)->select()[0];
            $json=json_encode($array);
        }
        $this->assign("id",$id);
        $this->assign("json",$json);
        $this->display();
    }

    /**
     * 添加活动
     */
    public function activity_add(){
        $id=getParam("id");
        $activityname=getParam("activityname");
        $activitycode=getParam("activitycode");

        if (empty($activityname)) {
            $this->error("活动名称不能为空");
            return;
        }
        if (empty($activitycode)) {
            $this->error("活动编码不能为空");
            return;
        }

        $res= M("merchant_activity")->where("activityname='%s' and id<>%d",$activityname,$id)->count();
        if($res>1){
            $this->error("活动名称已存在");
            return;
        }

        $res= M("merchant_activity")->where("activitycode='%s' and id<>%d",$activitycode,$id)->count();
        if($res>1){
            $this->error("活动编码已存在");
            return;
        }

        $data["activityname"]=$activityname;
        $data["activitycode"]=$activitycode;
        $res="";
        if($id==0){
            $res= M("merchant_activity")->add($data);
            if(empty($res)){
                $this->error("添加失败,请稍后重试");
            }else{
                $this->success("添加成功","project");
            }
        }else{
            $data["id"]=$id;
            $res= M("merchant_activity")->save($data);
            if(empty($res)){
                $this->error("修改失败,请稍后重试");
            }else{
                $this->success("修改成功","project");
            }
        }
    }
}
