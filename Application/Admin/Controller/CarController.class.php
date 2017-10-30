<?php
namespace Admin\Controller;
use Think\Exception;

/**
 * 商户管理
 */
class CarController extends BaseController
{
    /**
     * 用户列表
     */
    public function user()
    {
        $this->display();
    }

    /**
     * 获取用户列表
     */
    public function getCarList()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $mobile=getParam("mobile");
        $carno=getParam("carno");
        $starttime=getParam("starttime");
        $endtime=getParam("endtime");
        try {
            $where="roleid=1";
            if(!empty($mobile)){
                $where.=" and mobile='".$mobile."'";
            }
            if(!empty($starttime)&&!empty($endtime)){
                $where.=" and createtime between '".$starttime."' and '".$endtime."'";
            }
            if(!empty($carno)){
                $where.=" and id in(select userid from carowner where carno='".$carno."')";
            }
            //total
            $total = D("sysuser")->where($where)->count();
            $res = D("sysuser")->page($pageIndex, $pageSize)->where($where)->order("id desc")->select();
            $ids="";
            foreach ($res as $key => $value) {
                if(empty($ids)){
                    $ids = $value["id"];
                }
                else {
                    $ids = $ids.",".$value["id"];
                }
            }

            //构建车牌信息表
            $cars = array();
            $accounts=array();

            $cardb=D("carowner")->where("userid in (".$ids.")")->select();
            $accountdb=D("sysuseraccount")->where("userid in (".$ids.")")->select();
            foreach ($cardb as $key => $value) {
                $userid=$value["userid"];
                $carno=$value["carno"];
                if(is_null($cars[$userid])){
                    $cars[$userid]=$carno;
                }
                else{
                    $cars[$userid]= $cars[$userid].",".$carno;
                }
            }
            foreach ($accountdb as $key => $value) {
                $userid=$value["userid"];
                $balancemoney=$value["balancemoney"];
                $accounts[$userid]=$balancemoney;
            }

            $data = array();
            foreach ($res as $key => $value) {
                $item = array();
                $item["id"]=is_null($value["id"])?"":$value["id"];
                $item["username"]=is_null($value["username"])?"":$value["username"];
                $item["userpwd"]=is_null($value["userpwd"])?"":$value["userpwd"];
                $item["realname"]=is_null($value["realname"])?"":$value["realname"];
                $item["tel"]=is_null($value["tel"])?"":$value["tel"];
                $item["mobile"]=is_null($value["mobile"])?"":$value["mobile"];
                $item["nickname"]=is_null($value["nickname"])?"":$value["nickname"];
                $item["email"]=is_null($value["email"])?"":$value["mobile"];
                $item["createtime"]=$value["createtime"];
                $item["bindcartime"]=$value["bindcartime"];
                $item["city"]=is_null($value["city"])?"":$value["city"];
                $item["sex"]=is_null($value["sex"])?"":$value["sex"];
                $item["cars"]="";
                $item["accounts"]="";
                //绑定车牌
                if(!is_null($cars[$value["id"]])){
                    $item["cars"]=$cars[$value["id"]];
                }
                //绑定帐户
                if(!is_null($accounts[$value["id"]])){
                    $item["accounts"]=$accounts[$value["id"]];
                }
                array_push($data, $item);
            }
            $result["total"] = $total;
            $result["data"] = $data;
            $result["state"] = 0;
            $this->ajaxReturn($result);
        } catch (\Think\Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $data["total"] = 0;
            $this->ajaxReturn($data);
        }

    }

    /**
     * 导出用户列表
     */
    public function exportCarList(){
        $mobile=getParam("mobile");
        $carno=getParam("carno");
        $starttime=getParam("starttime");
        $endtime=getParam("endtime");

        $where="roleid=1";
        if(!empty($mobile)){
            $where.=" and mobile='".$mobile."'";
        }
        if(!empty($starttime)&&!empty($endtime)){
            $where.=" and createtime between '".$starttime."' and '".$endtime."'";
        }
        if(!empty($carno)){
            $where.=" and id in(select userid from carowner where carno='".$carno."')";
        }
        $res = D("sysuser")->where($where)->select();
        $ids="";
        foreach ($res as $key => $value) {
            if(empty($ids)){
                $ids = $value["id"];
            }
            else {
                $ids = $ids.",".$value["id"];
            }
        }

        //构建车牌信息表
        $cars = array();
        $accounts=array();

        $cardb=D("carowner")->where("userid in (".$ids.")")->select();
        $accountdb=D("sysuseraccount")->where("userid in (".$ids.")")->select();
        foreach ($cardb as $key => $value) {
            $userid=$value["userid"];
            $carno=$value["carno"];
            if(is_null($cars[$userid])){
                $cars[$userid]=$carno;
            }
            else{
                $cars[$userid]= $cars[$userid].",".$carno;
            }
        }
        foreach ($accountdb as $key => $value) {
            $userid=$value["userid"];
            $balancemoney=$value["balancemoney"];
            $accounts[$userid]=$balancemoney;
        }

        $data = array();
        foreach ($res as $key => $value) {
            $item = array();
            $item["username"]=is_null($value["username"])?"":$value["username"];
            $item["city"]=is_null($value["city"])?"":$value["city"];
            $item["createtime"]=$value["createtime"];
            $item["bindcartime"]=$value["bindcartime"];
            $item["nickname"]=is_null($value["nickname"])?"":$value["nickname"];
            $item["sex"]=is_null($value["sex"])?"":$value["sex"];

            $item["accounts"]="";
            //绑定帐户
            if(!is_null($accounts[$value["id"]])){
                $item["accounts"]=$accounts[$value["id"]];
            }

            $item["cars"]="";
            //绑定车牌
            if(!is_null($cars[$value["id"]])){
                $item["cars"]=$cars[$value["id"]];
            }

            array_push($data, $item);
        }

        exportExcelSimple("用户信息",["手机号","城市","注册时间","绑定时间","昵称","性别","e泊帐户余额(元)","车牌号"],$data,[18,14,16,16,16,16,20,30],"","");
    }

    /**
     * 用户详情页面
     */
    public function userdetail(){
        $carno = getParam("carno");
        $json = "";
        $gstr="";
        if ($carno != "") {
            $array = D("CarVerify")->where("carno='%s'", $carno)->select();
            //if (!is_array($array)||count($array) == 0) {
                //return;
            //}
            $shareids="";
            $shareArray = D("sysuser")->query("select mobile from sysuser where id in (select userid from carment where carno='%s' and ownerid<>userid)",$carno);
            foreach ($shareArray as $key => $value) {
                if(empty($shareids)){
                    $shareids = $value["mobile"];
                }
                else {
                    $shareids = $shareids.",".$value["mobile"];
                }
            }

            //  17/02/13 montylycarinfoaddbyapp   修改为  monthlycarinfo
            $grantArray = D("sysuser")->query("select (select parkname from parkinfo where ParkCode=t.ParkCode) as parkname,t.carno from monthlycarinfo t where UserID in(select UserID from monthlycarinfo where carno='%s') and carno<>'%s' order by ParkCode",$carno,$carno);
            $garray= array();

            foreach ($grantArray as $key => $value) {
                if(empty($value["parkname"])){
                    continue;
                }
                if(empty($garray[$value["parkname"]])){
                    $garray[$value["parkname"]] = $value["carno"];
                }else {
                    $garray[$value["parkname"]] = $garray[$value["parkname"]] . "," . $value["carno"];
                }
            }
            foreach ($garray as $key => $value) {
                $gstr.=$key.":".$value."\t\r";
            }

            $array[0]["share"]=$shareids;
            $array[0]["grant"]=$gstr;
            $json = json_encode($array[0]);
            $this->assign("json", $json);
            $this->display();
        }
    }

    /**
     * 用户认证
     */
    public function validate()
    {
        $this->display();
    }

    /**
     * 获取用户认证列表
     */
    public function getCarVerifyList(){
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $carno=getParam("carno");
        $state=getParam("state");
        try {
            $where="1=1";
            if(!empty($state)||$state=="0"){
                $where.=" and state='".$state."'";
            }
            if(!empty($carno)){
                $where.=" and carno='".$carno."'";
            }
            //total
            $total = D("CarVerify")->where($where)->count();
            $res = D("CarVerify")->page($pageIndex, $pageSize)->where($where)->order("rowtime desc")->select();
            $ids="";
            foreach ($res as $key => $value) {
                if(empty($ids)){
                    $ids = $value["userid"];
                }
                else {
                    $ids = $ids.",".$value["userid"];
                }
            }
            //构建用户编号，手机号字典
            $users = array();
            $userdb=D("sysuser")->where("id in (".$ids.")")->select();
            foreach ($userdb as $key => $value) {
                $id=$value["id"];
                $mobile=$value["mobile"];
                if(is_null($users[$id])){
                    $users[$id]=$mobile;
                }
            }

            $data = array();
            foreach ($res as $key => $value) {
                $item = array();
                $item["rowtime"]=$value["rowtime"];
                if(empty($users[$value["userid"]])){
                    $item["mobile"]="";
                }
                else{
                    $item["mobile"]=$users[$value["userid"]];
                }

                $item["username"]=is_null($value["username"])?"":$value["username"];
                $item["carno"]=is_null($value["carno"])?"":$value["carno"];
                $item["brand"]=is_null($value["brand"])?"":$value["brand"];
                $rangeofvalue="";
                switch($value["rangeofvalue"]){
                    case 0:
                        $rangeofvalue="10万以下";
                        break;
                    case 1:
                        $rangeofvalue="10万-30万";
                        break;
                    case 2:
                        $rangeofvalue="30万-80万";
                        break;
                    case 3:
                        $rangeofvalue="80万以上";
                        break;
                    default:
                        $rangeofvalue="10万以下";
                        break;
                }

                $state="";
                switch($value["state"]){
                    case 0:
                        $state="请求审核";
                        break;
                    case 1:
                        $state="已通过";
                        break;
                    case 2:
                        $state="未通过";
                        break;
                }

                $item["rangeofvalue"]=$rangeofvalue;
                $item["frameno"]=is_null($value["frameno"])?"":$value["frameno"];
                $item["regtime"]=is_null($value["regtime"])?"":$value["regtime"];
                $item["idcard"]=is_null($value["idcard"])?"":$value["idcard"];
                $item["enginenumber"]=is_null($value["enginenumber"])?"":$value["enginenumber"];
                $item["state"]=$state;
                array_push($data, $item);
            }
            $result["total"] = $total;
            $result["data"] = $data;
            $result["state"] = 0;
            $result["pageindex"]=getParam("pageIndex");
            $this->ajaxReturn($result);
        } catch (\Think\Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $data["total"] = 0;
            $this->ajaxReturn($data);
        }
    }

    /**
     * 导出用户认证列表
     */
    public function exportCarVerifyList(){
        $carno=getParam("carno");
        $state=getParam("state");

        $where="1=1";
        if(!empty($state)){
            $where.=" and state='".$state."'";
        }
        if(!empty($carno)){
            $where.=" and carno='".$carno."'";
        }
        $res = D("CarVerify")->where($where)->select();
        $ids="";
        foreach ($res as $key => $value) {
            if(empty($ids)){
                $ids = $value["userid"];
            }
            else {
                $ids = $ids.",".$value["userid"];
            }
        }
        //构建用户编号，手机号字典
        $users = array();
        $userdb=D("sysuser")->where("id in (".$ids.")")->select();
        foreach ($userdb as $key => $value) {
            $id=$value["id"];
            $mobile=$value["mobile"];
            if(is_null($users[$id])){
                $users[$id]=$mobile;
            }
        }

        $data = array();
        foreach ($res as $key => $value) {
            $item = array();
            $item["rowtime"]=$value["rowtime"];
            if(empty($users[$value["userid"]])){
                $item["mobile"]="";
            }
            else{
                $item["mobile"]=$users[$value["userid"]];
            }

            $item["username"]=is_null($value["username"])?"":$value["username"];
            $item["carno"]=is_null($value["carno"])?"":$value["carno"];
            $item["brand"]=is_null($value["brand"])?"":$value["brand"];
            $rangeofvalue="";
            switch($value["rangeofvalue"]){
                case 0:
                    $rangeofvalue="10万以下";
                    break;
                case 1:
                    $rangeofvalue="10万-30万";
                    break;
                case 2:
                    $rangeofvalue="30万-80万";
                    break;
                case 3:
                    $rangeofvalue="80万以上";
                    break;
                default:
                    $rangeofvalue="10万以下";
                    break;
            }

            $state="";
            switch($value["state"]){
                case 0:
                    $state="请求审核";
                    break;
                case 1:
                    $state="已通过";
                    break;
                case 2:
                    $state="未通过";
                    break;
            }

            $item["rangeofvalue"]=$rangeofvalue;
            $item["frameno"]=is_null($value["frameno"])?"":$value["frameno"];
            $item["regtime"]=is_null($value["regtime"])?"":$value["regtime"];
            $item["idcard"]=is_null($value["idcard"])?"":$value["idcard"];
            $item["enginenumber"]=is_null($value["enginenumber"])?"":$value["enginenumber"];
            $item["state"]=$state;
            array_push($data, $item);
        }
        exportExcelSimple("用户认证信息",["申请日期","手机号","车主姓名","车牌号","品牌车系","车价值","车架号码","注册日期","发动机号","身份证号","审核状态"],$data,[18,14,16,16,16,16,20,20,20,20,20],"","");
    }

    /**
     * 用户详情页面
     */
    public function queryvalidatedetail(){
        try {
            $carno=getParam("carno");
            if ($carno != "") {
                $array = D("CarVerify")->where("carno='%s'", $carno)->select();
                if (!is_array($array)||count($array) == 0) {
                    return;
                }
                $result["data"] = $array[0];
                $result["state"] = 0;
                $this->ajaxReturn($result);
            }
        }
        catch (Exception $e) {
            $result["state"] = -1;
            $this->ajaxReturn($result);
        }
    }

    /**
     * 用户认证审核
     */
    public function pass(){
        $state=getParam("state");
        $carno=getParam("carno");
        $reason=getParam("reason");
        try{
            $op=M("Menu")->query("select username from oms_ucenter_member where id=%d",UID)[0]["username"];
            $result= D("CarVerify")->execute("update car_verify set state='%d',remark='%s',operatetime=now(),operator='%s' where carno='%s'",$state,$reason,$op,$carno);
            $data["state"]=($result>= 0 ? 0 : -1);
            $isPassed=($state=="1");
            if($result>=0) {
                send_msg($carno,$isPassed,$reason);
            }

            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }
    }



    /**
     * 保存车牌认证信息
     */
    public function save(){
        $username=getParam("username");
        $carno=getParam("carno");
        $rangeofvalue=getParam("rangeofvalue");
        $brand=getParam("brand");
        $frameno=getParam("frameno");
        $regtime=getParam("regtime");
        $idcard=getParam("idcard");
        $enginenumber=getParam("enginenumber");
        try{

            $op=M("Menu")->query("select username from oms_ucenter_member where id=%d",UID)[0]["username"];
            $result= D("CarVerify")->execute("update car_verify set username='%s' ,rangeofvalue='%s', brand='%s',frameno='%s',regtime='%s',operatetime=now(),operator='%s',idcard='%s',enginenumber='%s' where carno='%s'",$username,$rangeofvalue,$brand,$frameno,$regtime,$op,$idcard,$enginenumber,$carno);
            $data["state"]=($result>= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }
    }

}
