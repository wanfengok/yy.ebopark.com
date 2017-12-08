<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 2017/11/24
 * Time: 下午4:44
 */

namespace Admin\Service;
use Admin\Model\ParkinfoModel;
use Admin\Model\VirtualAccountOrderMergeModel;
use Admin\Model\VirtualAccountParkChangeDetailModel;
use Admin\Model\VirtualAccountConfigNoticeModel;
use Think\Exception;

/**
 * 虚拟机账户
 * Class VirtualAccountService
 * @package Admin\Service
 */
class VirtualAccountService
{

    private $virtualAccountParkChangeDetail;
    private $virtualAccountOrdersMerge;
    function __construct() {
        $this->virtualAccountParkChangeDetail=new VirtualAccountParkChangeDetailModel();
        $this->virtualAccountOrdersMerge=new VirtualAccountOrderMergeModel();
    }
    public function markOrderWithStatus(){

        $mark=getParam("mark");
        $status=getParam("status");
        $id=getParam("id");
        if(!is_numeric($status)){
            return $this->getWrapData(-1,"参数错误");
        }
        try{
            $map["ID"]=array("eq",$id);
        $this->virtualAccountOrdersMerge->where($map)->save(["Status"=>$status,"HandleRemark"=>$mark]);
            return $this->getWrapData(0);
        }catch(Exception $e){
            return $this->getWrapData(-1,"数据库操作错误!");
        }
    }
    public function updateBindEmail(){
        $email=getParam("email");
        if(empty($email)){
            return $this->getWrapData(-1,"邮件不能为空");
        }
        $virtualAccountConfigNotice=new VirtualAccountConfigNoticeModel();
        $res=$virtualAccountConfigNotice->limit(1)->find();
        try{
            if(empty($res)){
                $virtualAccountConfigNotice->data(["Email"=>$email])->add();
            }else{
                $virtualAccountConfigNotice->where(["ID"=>$res["id"]])->save(["Email"=>$email]);
            }
            return $this->getWrapData(0);
        }catch(Exception $e){
            return $this->getWrapData(-1,"数据库操作错误!");
        }
    }

    public function updateBindMobile(){
        $mobile=getParam("mobile");
        if(!is_numeric($mobile)){
            return $this->getWrapData(-1,"请填写格式正确的手机号码");
        }
        $virtualAccountConfigNotice=new VirtualAccountConfigNoticeModel();
        $res=$virtualAccountConfigNotice->limit(1)->find();
        try{
            if(empty($res)){
                $virtualAccountConfigNotice->data(["Telephone"=>$mobile])->add();
            }else{
                $virtualAccountConfigNotice->where(["ID"=>$res["id"]])->save(["Telephone"=>$mobile]);
            }
            return $this->getWrapData(0);
        }catch(Exception $e){
            return $this->getWrapData(-1,"数据库操作错误!");
        }
    }
    public function getExceptionwithdrawLogs(){

        $pageIndex = getParam("pageIndex");
        $pageSize = getParam("pageSize");

        $parkname=getParam("parkname");
        $starttime=getParam("starttime");
        $endtime=getParam("endtime");
        $carno=getParam("carno");
        $markType=getParam("markType");
        $isNoCarno=getParam("isNoCarno");

        empty($starttime)&&$starttime="0001-01-01";
        empty($endtime)&&$endtime="2099-00-00";

        $map["LocalOrderTime"]=array("between",array($starttime,$endtime));

        $parkinfoModel=new ParkinfoModel();
        if(!empty($parkname)){
            $res= $parkinfoModel->searchByParkName($parkname);
            $parkcodesTemp=[];
            foreach($res as  $key=>$value){
                $parkcodesTemp[$value["parkcode"]]=$value["parkname"];
            }
            $parkcodes=array_keys($parkcodesTemp);
            if(!empty($parkcodes)){
                $map["LocalParkCode"]=array("in",$parkcodes);
            }else{
                $data=$this->getWrapData(0,'',[]);
                $data["total"]=0;
                return $data;
            }
        }
        if(!empty($carno)){
            $map["CarNo"]=array("like","%$carno%");
        }
        if($markType!=""){
            $status=$markType==0?11:10;
            $map["Status"]=array("in",array($status));
        }else{
            $map["Status"]=array("in",array(5,10,11,12));
        }
        if($isNoCarno==1){
            //无牌车
            $map['CarNo']=array('EXP','IS NULL');
        }
        $total=count($this->virtualAccountOrdersMerge->where($map)->select());
        $res=$this->virtualAccountOrdersMerge->where($map)->limit($pageIndex*$pageSize,$pageSize)->select();
//        $parks=$parkinfoModel->getParkCodeAndParkName();
//        foreach($res as &$value){
//            $value["parkname"]=empty($parks[$value["localparkcode"]])?"--":$parks[$value["localparkcode"]];
//        }
        $data=$this->getWrapData(0,'',empty($res)?[]:$res);
        $data["total"]=$total;
        return $data;








    }
    public function getWithdrawLogs(){
        $pageIndex = getParam("pageIndex");
        $pageSize = getParam("pageSize");
        $parkname=getParam("parkname");
        $withDrawType=getParam("withDrawType");
        $withDrawStatus=getParam("withDrawStatus");
        $starttime=getParam("starttime");
        $endtime=getParam("endtime");
        //总数
        empty($starttime)&&$starttime="0001-01-01";
        empty($endtime)&&$endtime="2099-00-00";
        $map["HandleTime"]=array("between",array($starttime,$endtime));

        if($withDrawStatus!=-2){
            $map["Status"]=array("eq",$withDrawStatus);
        }
        if($withDrawType!=-2){
            $map["IsAuto"]=array("eq",$withDrawType);
        }
        $parkinfoModel=new ParkinfoModel();
        if(!empty($parkname)){
            $res= $parkinfoModel->searchByParkName($parkname);
            $parkcodesTemp=[];
           foreach($res as  $key=>$value){
               $parkcodesTemp[$value["parkcode"]]=$value["parkname"];
            }
            $parkcodes=array_keys($parkcodesTemp);
            if(!empty($parkcodes)){
                $map["ParkCode"]=array("in",$parkcodes);
            }
        }
        $total=count($this->virtualAccountParkChangeDetail->where($map)->select());
        $res=$this->virtualAccountParkChangeDetail->where($map)->limit($pageIndex*$pageSize,$pageSize)->select();
        $parks=$parkinfoModel->getParkCodeAndParkName();
        foreach($res as &$value){
            $value["parkname"]=empty($parks[$value["parkcode"]])?"--":$parks[$value["parkcode"]];
        }
        $data=$this->getWrapData(0,'',empty($res)?[]:$res);
        $data["total"]=$total;
        return $data;
    }
    public function getWrapData($state = -1, $desc = "", $obj = [])
    {
        $data["state"] = $state;
        $data["desc"] = $desc;
        $data["data"] = $obj;
        return $data;
    }
}