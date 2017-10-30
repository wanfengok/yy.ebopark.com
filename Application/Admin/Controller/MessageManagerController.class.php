<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:13
 */

namespace Admin\Controller;
use Think\Controller;
use Think\Exception;
use Admin\Model\AdvConstantModel;
import("Vendor.PHPExcel176.PHPExcel");

/**
 * 消息管理
 * @package Admin\Controller
 */
class MessageManagerController extends BaseController
{
    /**
     * 用户消息
     */
    public function userMessage(){
        $this->display();
    }
    /**
     * 系统消息
     */
    public function sysMessageIndex(){
        $this->display();
    }
    public function smsList(){

        $this->display();

    }
    public function smsQuery(){
        $result=customizedPageQuery("MsgDefineSms",array());
        $this->ajaxReturn($result);
    }
    public function smsMessageDetail(){
        $id =getParam("id");
        $res=D("MsgDefineSms")->where("id=$id")->select();
        $this->assign("msgObj",json_encode($res[0]));
        $this->display();
    }

    /**
     * 修改短信消息
     */
    public function smsModify(){

        $content = getParam("content");
        $id = getParam("id");
        $data=array();
        //参数校验
        if(empty($content)){
            $data["state"]=-1;
            $data["msg"]="标题或内容不能为空";
        }
        if(count($content)>200){
            $data["state"]=-1;
            $data["msg"]="内容过长,不能超过100个字符";
        }
        if(!is_numeric($id)){
            $data["state"]=-1;
            $data["msg"]="参数错误";
        }
        if(!empty($data)){
            $this->ajaxReturn($data);
            return;
        }
        //表单提交
        // 要修改的数据对象属性赋值
        $data['Content'] = $content;
        $data['Operator'] = session('user_auth')["username"];//$operator
        $data['OperateTime'] =date('Y-m-d H:i:s');;
        $data['Id'] = $id;
        try{
            $res=D("MsgDefineSms")->save($data);
            if(empty($res)){
                $data["state"]=-1;
                $data["msg"]="数据更新错误,请稍后再试!";
            }else{
                $data["state"]=0;
            }
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }
    }
    /*
     *  文字消息列表
     * */
    public function sysMessageWzMsg(){
        $this->display();
    }
    public function sysMessageWzMsgSql(){
        //从表内获取相应数据
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        try {
            $res = M()->query("select * from msg_define_text");
//            $total = 1;
            $total = M()->query("select count(*) as count from msg_define_text");
            //m方法查询这个表里面二级数组，as后面为别名；
            $data = array();
            $i=1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["id"]=is_null($value["id"])?"":$value["id"];
                $item["Content"]=is_null($value["Content"])?"":$value["Content"];
                $item["JumpUrl"]=is_null($value["JumpUrl"])?"":$value["JumpUrl"];
                $item["LimitCity"]=is_null($value["LimitCity"])?"":$value["LimitCity"];
                $item["LimitPark"]=is_null($value["LimitPark"])?"":$value["LimitPark"];
                $item["LimitTime"]=is_null($value["LimitTime"])?"":$value["LimitTime"];
                $item["StartTime"]=is_null($value["StartTime"])?"":$value["StartTime"];
                $item["EndTime"]=is_null($value["EndTime"])?"":$value["EndTime"];
                $item["SendType"]=is_null($value["SendType"])?"":$value["SendType"];
                $item["SendDesc"]=is_null($value["SendDesc"])?"":$value["SendDesc"];
                $item["RowTime"]=is_null($value["RowTime"])?"":$value["RowTime"];
                $item["Remark"]=is_null($value["Remark"])?"":$value["Remark"];
                $item["Operator"]=is_null($value["Operator"])?"":$value["Operator"];
                $item["OperateTime"]=is_null($value["OperateTime"])?"":$value["OperateTime"];
                array_push($data, $value);
                $i++;
            }
            $result["total"] = $total[0]["count"];
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
    public function sysMessageWzDetele(){
        $Id=getParam("id");
        try{
            $User = M('',"msg_define_text"); //
            $User->where('id='.$Id)->delete(); //
            $this->ajaxReturn($User);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }
    }
    public function sysMessageWzAdd(){
        $Content = getParam("msg");
        $starTime = getParam("startTime");
        $endTime = getParam("endTime");
        $JumpUrl = getParam("tzly");
        $Remark = getParam("bz");
        $OperateTime = getParam("nowTime");
        $SendDesc = getParam("mobile");
        $LimitCity = getParam("city");
        $User = M('',"msg_define_text");
        $data['Content'] = $Content;
        $data['StartTime'] = $starTime;
        $data['EndTime'] = $endTime;
        $data['JumpUrl'] = $JumpUrl;
        $data['Remark'] = $Remark;
        $data['RowTime'] = $OperateTime;
        $data['OperateTime'] = $OperateTime;
        $data['Operator'] = 'admin';
        $data['SendDesc'] = $SendDesc;
        $data['LimitCity'] = $LimitCity;
        $data['state'] = 0;
        $User->add($data);
    }
    public function sysMessagePreviewAdd(){
        $Content = getParam("msg");
        $JumpUrl = getParam("tzly");
        $Remark = getParam("bz");
        $OperateTime = getParam("nowTime");
        $SendDesc = getParam("mobile");
        $LimitCity = getParam("city");
        $User = M('',"msg_define_text");
        $User->where('state=1')->delete();
        $data['Content'] = $Content;
        $data['StartTime'] = "2099-12-31 00:00:00";
        $data['EndTime'] = "2099-12-31 00:00:00";
        $data['JumpUrl'] = $JumpUrl;
        $data['Remark'] = $Remark;
        $data['RowTime'] = $OperateTime;
        $data['OperateTime'] = $OperateTime;
        $data['Operator'] = 'admin';
        $data['SendDesc'] = $SendDesc;
        $data['LimitCity'] = $LimitCity;
        $data['state'] = 1;
        $User->add($data);
    }
    public function sysMessageWzSave(){
        $Content = getParam("msg");
        $starTime = getParam("startTime");
        $endTime = getParam("endTime");
        $JumpUrl = getParam("tzly");
        $Remark = getParam("bz");
        $OperateTime = getParam("nowTime");
        $SendDesc = getParam("mobile");
        $LimitCity = getParam("city");
        $id = getParam("id");
        $User = M('',"msg_define_text");
        $data['Content'] = $Content;
        $data['StartTime'] = $starTime;
        $data['EndTime'] = $endTime;
        $data['JumpUrl'] = $JumpUrl;
        $data['Remark'] = $Remark;
        $data['RowTime'] = $OperateTime;
        $data['OperateTime'] = $OperateTime;
        $data['Operator'] = 'admin';
        $data['SendDesc'] = $SendDesc;
        $data['LimitCity'] = $LimitCity;
        $data['state'] = 0;
        $User->where('id='.$id)->save($data);
    }
    public function sysMessagePreview(){
        $Content = getParam("msg");
        $JumpUrl = getParam("tzly");
        $Remark = getParam("bz");
        $OperateTime = getParam("nowTime");
        $SendDesc = getParam("mobile");
        $LimitCity = getParam("city");
        $id = getParam("id");
        $User = M('',"msg_define_text");
        $User->where('state=1')->delete();
        $data['Content'] = $Content;
        $data['StartTime'] = "2099-12-31 00:00:00";
        $data['EndTime'] = "2099-12-31 00:00:00";
        $data['JumpUrl'] = $JumpUrl;
        $data['Remark'] = $Remark;
        $data['RowTime'] = $OperateTime;
        $data['OperateTime'] = $OperateTime;
        $data['Operator'] = 'admin';
        $data['SendDesc'] = $SendDesc;
        $data['LimitCity'] = $LimitCity;
        $data['state'] = 1;
        $User->where('id='.$id)->save($data);
    }

    public function sysMessageWzxg(){
        $cityAndParkLot=$this->getCityAndParkingLot();
        $res = M()->query("select * from msg_define_text");
        $this->assign("data",json_encode($res));
        $this->assign("city_parklot",json_encode($cityAndParkLot));
        $this->display();
    }
    /**
     * 微信消息
     */
    public function wxMessage(){
        $this->display();
    }
    /**
     * 修改微信消息
     */
    public function wxMessageDetail(){
        $id=getParam("id");
        if(!is_numeric($id)){
            $this->error("参数错误");
        }
        $res=D("MsgDefineWx")->where("id=$id")->select();
        $cityAndParkLot=$this->getCityAndParkingLot();
        $this->assign("msgObj",json_encode($res[0]));
        $this->assign("city_parklot",json_encode($cityAndParkLot));
        $this->assign("id",$id);
        $this->display();
    }
    /**
     * 添加微信消息
     */
    public function wxMessageAdd(){
        $cityAndParkLot=$this->getCityAndParkingLot();
        $this->assign("city_parklot",json_encode($cityAndParkLot));
        $this->display();
    }
    public function sysMessageAdd(){
        $cityAndParkLot=$this->getCityAndParkingLot();
        $this->assign("city_parklot",json_encode($cityAndParkLot));
        $this->display();
    }
    /**
     * 删除微信消息
     */
    public function deleteWxMessage(){
        $id=getParam("id");
        try{
            D("MsgDefineWx")->where("id=$id")->delete();
            $res["state"]=0;

        }catch(Exception $e){

            $res["state"]=-1;
            $res["msg"]=$e->getMessage();
        }
        $this->ajaxReturn($res);

    }
    /**
     * 升序
     */
    public function exchangeOrder(){
        $order=getParam("order");
        $anotherOrder=getParam("anotherOrder");
        $id=getParam("id");
        $anotherid=getParam("anotherid");
        try{
            $data["Order"]=$anotherOrder;
            $data["Id"]=$id;
            D("MsgDefineWx")->save($data);
            $data1["Order"]=$order;
            $data1["Id"]=$anotherid;
            D("MsgDefineWx")->save($data1);

            $res["state"]=0;
        }catch(Exception $e){
            $res["state"]=-1;
            $res["msg"]=$e->getMessage();
        }
        $this->ajaxReturn($res);
    }
    /**
     * 提交微信消息模板
     */
    public function submitWeixinMsg()
    {
        $tplCode=getParam("wxMsgTpl");
        $title=getParam("title");
        $remark=getParam("remark");
        $headerContent=getParam("headerContent");
        $footerContent=getParam("footerContent");
        $customization=getParam("customization");
        $redirectType=getParam("redirectType");
        $custom_url=getParam("custom_url");
        $fixedURL=getParam("fixedURL");
        $startTime=getParam("startTime");
        $endTime=getParam("endTime");
        $intervalTime=getParam("intervalTime");

        if($tplCode=="-1"){
            $this->error("请选择消息模板");
        }
        if(empty($headerContent)){
            $this->error("消息头部不能为空");
        }
        if(empty($footerContent)){
            $this->error("消息尾部不能为空");
        }

        //指定链接
        $redirectURL=$redirectType==1?$custom_url:$this->getFixedURL($fixedURL);
        $data["TriggerType"]=$tplCode;
        $data["Title"]=$title;
        $data["Remark"]=$remark;
        $data["Head"]=$headerContent;
        $data["Tail"]=$footerContent;
        $data["StartTime"]=$startTime;
        $data["EndTime"]=$endTime;
        $data["IntervalPreTime"]=is_numeric($intervalTime)?$intervalTime:"";
        $data["JumpType"]=$redirectType;
        $data["JumpConfig"]=$redirectURL;
        $data["RowTime"]=date('Y-m-d H:i:s');
        $data["Operator"]=session('user_auth')["username"];
        $data["OperateTime"]=date('Y-m-d H:i:s');
        if($customization==1){
            $data["LimitCity"]=getParam("areaListStr");
            $data["LimitPark"]=getParam("parkLotListStr");
            $data["LimitMobile"]=$this->getPhoneList();
        }
        try{
            $count= D("MsgDefineWx")->count();
            $data["Order"]=$count+1;
            $res=D("MsgDefineWx")->add($data);
            //跳转至列表页面
            $this->redirect("/Admin/MessageManager/wxMessage");
        }catch(Exception $e){
            $this->error($e->getMessage());
        }

    }

    /**
     * 修改微信消息
     */
    public function updateMsg(){
        $id=getParam("id");
        $tplCode=getParam("tplCode");
        $title=getParam("title");
        $remark=getParam("remark");
        $headerContent=getParam("headerContent");
        $footerContent=getParam("footerContent");
        $customization=getParam("customization");
        $redirectType=getParam("redirectType");
        $custom_url=trim(getParam("custom_url"));
        $fixedURL=getParam("fixedURL");
        $startTime=getParam("startTime");
        $endTime=getParam("endTime");
        $intervalTime=getParam("intervalTime");

        if($tplCode=="-1"){
            $this->error("请选择消息模板");
        }
        if(empty($headerContent)){
            $this->error("消息头部不能为空");
        }
        if(empty($footerContent)){
            $this->error("消息尾部不能为空");
        }

        //指定链接
        $redirectURL=$redirectType==1?$custom_url:$this->getFixedURL($fixedURL);
        $data["Id"]=$id;
        $data["TriggerType"]=$tplCode;
        $data["Title"]=$title;
        $data["Remark"]=$remark;
        $data["Head"]=$headerContent;
        $data["Tail"]=$footerContent;
        $data["StartTime"]=$startTime;
        $data["EndTime"]=$endTime;
        $data["IntervalPreTime"]=is_numeric($intervalTime)?$intervalTime:"";
        $data["JumpType"]=$redirectType;
        $data["JumpConfig"]=$redirectURL;

        $data["Operator"]=session('user_auth')["username"];
        $data["OperateTime"]=date('Y-m-d H:i:s');
        if($customization==1){
            $areaListStr=getParam("areaListStr");
            $parkLotListStr=getParam("parkLotListStr");
            $data["LimitCity"]=empty($areaListStr)?null:$areaListStr;
            $data["LimitPark"]=empty($parkLotListStr)?null:$parkLotListStr;
            $data["LimitMobile"]=$this->getPhoneListWhenModify();
        }else{
            $data["LimitCity"]=null;
            $data["LimitPark"]=null;
            $data["LimitMobile"]=null;
        }
        try{
            $res=D("MsgDefineWx")->save($data);
            //跳转至列表页面
            $this->success("修改成功",U("Admin/MessageManager/wxMessage"));
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
    }
    /**
     * 根据自定type，获取对应的微信跳转链接
     * @param $type
     * @return mixed|string
     */
    public function getFixedURL($type){
        $url="";
        switch($type){
            case 1:$url=C("weixin_autopay");break;
            case 2:$url=C("weixin_balance");break;
            case 3:$url=C("weixin_recorder");break;
            case 4:$url=C("weixin_car_manage");break;
            case 5:$url=C("weixin_monthly_car");break;
            case 6:$url=C("weixin_coupon_list");break;
            case 7:$url=C("weixin_recharge");break;
            case 8:$url=C("weixin_parkfee");break;
        }
        return $url;

    }
    /**
     * 获取所有的微信消息列表
     */
    public function getWxMessageList(){
            try{
                $code=getParam("code");
                $res=D("MsgDefineWx")->field("msg_define_wx.*,msg_stat_send.`Cnt`")->join("left join msg_stat_send ON msg_stat_send.`MsgId` = msg_define_wx.`Id` and msg_stat_send.`MsgType`=3")->where("TriggerType IN($code) ")->order("`order` desc")->select();
                foreach($res as $key=>&$value){
                    $value["show"]=$this->showAdvOperatorBtn($value,AdvConstantModel::ADV_SOURCE_WX);
                }
                $data["state"]=0;
                $data["data"]=$res;
            }catch(Exception $e){
                $data["state"]=-1;
                $data["msg"]=$e->getMessage();
            }
            $this->ajaxReturn($data);
    }

    /**
     * 获取城市及对应的停车场
     */
    public function getCityAndParkingLot(){
            $res=D("Parkinfo")->getCityAndParkLots();
            if($res["state"]!=0){
                $this->error($res["msg"]);
            }
            return $res;
    }
    /**
     * 修改系统消息
     */
    public function sysMessageDetail(){
        $id=getParam("id");
        if(!is_numeric($id)){
            $this->error("参数错误");
        }
        $res=D("MsgDefineSystem")->where("id=$id")->select();
        $cityAndParkLot=$this->getCityAndParkingLot();
        $this->assign("msgObj",json_encode($res[0]));
        $this->assign("city_parklot",json_encode($cityAndParkLot));
        $this->assign("id",$id);
        $this->display();
    }
    /**
     * 修改系统消息
     */
    public function modifySysMessageDetail(){
            $title = getParam("title");
            $content = getParam("content");
            $customization = getParam("customization");
            $redirectType = getParam("redirectType");
            $custom_url = getParam("custom_url");
            $fixedURL = getParam("fixedURL");
            $timingSendTime = getParam("timingSendTime");
            $startTime = getParam("startTime");
            $endTime = getParam("endTime");
            $intervalTime = getParam("intervalTime");
            if (empty($content)) {
                $this->error("消息内容不能为空");
            }
            if (empty($title)) {
                $this->error("消息标题不能为空");
            }

            //指定链接
            $redirectURL = $redirectType == 1 ? $custom_url : $fixedURL;
//            $msg_define_system = D("MsgDefineSystem");

            $data["TimingSendTime"] = "2016-10-21 ".$timingSendTime;
            $data["Title"] = $title;
            $data["Content"] = $content;
            $data["StartTime"] = $startTime;
            $data["EndTime"] = $endTime;
            $data["IntervalPreTime"] = is_numeric($intervalTime) ? $intervalTime : "";
            $data["JumpType"] = $redirectType;
            $data["JumpConfig"]= $redirectURL;
            $data["Operator"] = session('user_auth')["username"];
            $data["OperateTime"] = date('Y-m-d H:i:s');
            if ($customization == 1) {
                $data["LimitCity"] = getParam("areaListStr");
                $data["LimitPark"] = getParam("parkLotListStr");
                $data["LimitMobile"]= $this->getPhoneListWhenModify();
            }else{
                $data["LimitCity"] = null;
                $data["LimitPark"] = null;
                $data["LimitMobile"]=null;
            }
            $data['Id']=getParam('id');
            try {
                $res=D("MsgDefineSystem")->save($data);
                //跳转至列表页面
                $this->success("修改成功",U("Admin/MessageManager/sysMessage"));
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

        }
    /**
     * 修改系统消息
     */
    public function addSysMessage(){
        $title = getParam("title");
        $content = getParam("content");
        $customization = getParam("customization");
        $redirectType = getParam("redirectType");
        $custom_url = getParam("custom_url");
        $fixedURL = getParam("fixedURL");
        $timingSendTime = getParam("timingSendTime");
        $startTime = getParam("startTime");
        $endTime = getParam("endTime");
        $intervalTime = getParam("intervalTime");
        if (empty($content)) {
            $this->error("消息内容不能为空");
        }
        if (empty($title)) {
            $this->error("消息标题不能为空");
        }

        //指定链接
        $redirectURL = $redirectType == 1 ? $custom_url : $fixedURL;
//            $msg_define_system = D("MsgDefineSystem");
        if (!empty($timingSendTime)) {
            $timingSendTime = date('Y-m-d') . " " . $timingSendTime;
            $data["TimingSendTime"] = $timingSendTime;
        }
        $data["Title"] = $title;
        $data["Content"] = $content;
        $data["StartTime"] = $startTime;
        $data["EndTime"] = $endTime;
        $data["IntervalPreTime"] = is_numeric($intervalTime) ? $intervalTime : "";
        $data["JumpType"] = $redirectType;
        $data["JumpConfig"]= $redirectURL;
        $data["Operator"] = session('user_auth')["username"];
        $data["OperateTime"] = date('Y-m-d H:i:s');
        $data["TriggerType"]=getParam("triggerType");
        $data["RowTime"]=date('Y-m-d H:i:s');
        if ($customization == 1) {
            $data["LimitCity"] = getParam("areaListStr");
            $data["LimitPark"] = getParam("parkLotListStr");
            $data["LimitMobile"]= $this->getPhoneList();
        }
        try {
            $res=D("MsgDefineSystem")->add($data);
            //跳转至列表页面
            $this->redirect("/Admin/MessageManager/sysMessage");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }
    public function getPhoneList(){

        if (! empty ( $_FILES ['file_stu'] ['name'] ))
        {
            $phoneListStr=$this->getPhoneListFromExcel();
        }
        $phoneListStr=empty($phoneListStr)?'':$phoneListStr;
        return $phoneListStr;
    }
    public function getPhoneListWhenModify(){

        if (! empty ( $_FILES ['file_stu'] ['name'] ))
        {
            $phoneListStr=$this->getPhoneListFromExcel();
        }else{
            $phoneListStr=getParam("limitmobile");
        }
        return $phoneListStr;
    }
    public function deleteSysMsg(){
            $id=getParam("id");
            if(!is_numeric($id)){
                $data["state"]=-1;
                $data["msg"]="参数错误";
                $this->ajaxReturn($data);
                return;
            }
            try {
                $res=D("MsgDefineSystem")->where('id=%d',$id)->delete();
                if(empty($res)){
                    $data["state"]=-1;
                    $data["msg"]="数据库操作失败";
                }else{
                    $data["state"] = 0;
                }
                $this->ajaxReturn($data);

            } catch (Exception $e) {
                $data["state"] = -1;
                $data["msg"] = $e->getMessage();
                $this->ajaxReturn($res);
            }

    }
    /**
     * 系统消息分页查询
     */
    public function sysMessageQuery(){
        $result=$this->messagePageQuery("MsgDefineSystem",array());
        $this->ajaxReturn($result);
    }
    function messagePageQuery($tableName, $where)
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        try {
            //total
            $total = D($tableName)->where($where)->count();
            $res = D($tableName)->where($where)->page($pageIndex, $pageSize)->order("id desc")->select();
            foreach($res as $key=>&$value){
                $value["show"]=$this->showAdvOperatorBtn($value);
            }
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
     * 系统消息添加至广告列表
     */
    public function addToAdvList(){
        $id=getParam("id");
        if(!is_numeric($id)){
            $data["state"]=-1;
            $data["msg"]='参数错误';
            $this->ajaxReturn($data);
        }
        $data["create_time"]=date('Y-m-d H:i:s');
        $data["token"]=uuid("adv_");
        $data["source"]="".AdvConstantModel::ADV_SOURCE_SYSTEM_INFO;
        $data["msg_id"]=$id;
       $res= M("advertisement")->add($data);

        if(empty($res)){
            $resp["state"]=-1;
            $resp["msg"]="添加失败,请稍后重试";
        }else{

            //更新url
            $res=D("MsgDefineSystem")->where("Id=$id")->select();
            $sysdata["JumpConfig"]=$this->getUrlWithTokenParam($res[0]["jumpconfig"],$data["token"]);
            $sysdata["Id"]=$id;
            D("MsgDefineSystem")->save($sysdata);
            $resp["state"]=0;
        }
        $this->ajaxReturn($resp);

    }
    /**
     * 微信消息添加至广告列表
     */
    public function wxAddToAdvList(){
        $id=getParam("id");
        if(!is_numeric($id)){
            $data["state"]=-1;
            $data["msg"]='参数错误';
            $this->ajaxReturn($data);
        }
        $data["create_time"]=date('Y-m-d H:i:s');
        $data["token"]=uuid("adv_");
        $data["source"]="".AdvConstantModel::ADV_SOURCE_WX;
        $data["msg_id"]=$id;
        $res= M("advertisement")->add($data);

        if($res===false){
            $resp["state"]=-1;
            $resp["msg"]="添加失败,请稍后重试";
        }else{
            //更新url
            $res=D("MsgDefineWx")->where("Id=$id")->select();
            $sysdata["JumpConfig"]=$this->getUrlWithTokenParam($res[0]["jumpconfig"],$data["token"]);
            $sysdata["Id"]=$id;
            D("MsgDefineWx")->save($sysdata);
            $resp["state"]=0;
        }
        $this->ajaxReturn($resp);

    }
    private function getUrlWithTokenParam($url,$token){
        $oriUrl=explode("?",$url)[0];
        return $oriUrl."?adv_token=".$token;
    }
    /**
     * 判断是否显示广告添加按钮
     */
    private function showAdvOperatorBtn($value,$source=AdvConstantModel::ADV_SOURCE_SYSTEM_INFO){
        //已设置为广告或系统页面不能显示
        $pos=strpos($value["jumpconfig"],'http');
        if($pos===false){
            return -1;
        }
        $data=M('advertisement')->field('*')->where("msg_id=".$value["id"]." and source=".$source)->select();
        if(empty($data)){
            return 1;
        }
        //广告
        return 0;
    }
    /**
     * 用户消息分页查询
     */
    public function userMessageQuery(){
        $result=customizedPageQuery("MsgDefinePersonal",array());
        $this->ajaxReturn($result);
    }
    public function userMessageDetail(){
        $id =getParam("id");
        $res=D("MsgDefinePersonal")->where("id=$id")->select();
        $this->assign("msgObj",json_encode($res[0]));
        $this->display();
    }
    /**
     *    修改用户消息
     */
    function modifyUserMsg(){

        $triggertype = getParam("triggertype");
        $title = getParam("title");
        $content = getParam("content");
        $id = getParam("id");
        $jumptype = getParam("jumptype");
        $jumpconfig = getParam("jumpConfig");
        $data=array();
        //参数校验
        if(empty($title)||empty($content)){
            $data["state"]=-1;
            $data["msg"]="标题或内容不能为空";
        }
        if(count($content)>100){
            $data["state"]=-1;
            $data["msg"]="内容过长,不能超过100个字符";
        }
        if(!is_numeric($id)||!is_numeric($jumptype)){
            $data["state"]=-1;
            $data["msg"]="参数错误";
        }
        if(!empty($data)){
            $this->ajaxReturn($data);
            return;
        }
        //表单提交
        // 要修改的数据对象属性赋值
        $data['TriggerType'] = $triggertype;
        $data['Title'] = $title;
        $data['Content'] = $content;
        $data['JumpType'] = $jumptype;
        $data['JumpConfig'] = $jumpconfig;
        $data['Operator'] = session('user_auth')["username"];//$operator
        $data['OperateTime'] =date('Y-m-d H:i:s');;
        $data['Id'] = $id;
        try{
            $res=D("MsgDefinePersonal")->save($data);
            if(empty($res)){
                $data["state"]=-1;
                $data["msg"]="数据更新错误,请稍后再试!";
            }else{
                $data["state"]=0;
            }
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]=$e->getMessage();
            $this->ajaxReturn($data);
        }


    }
    /**
     * 如有上传文件，获取手机号码列表
     */
    public function getPhoneListFromExcel(){
        $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
        $file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
        $file_type = $file_types [count ( $file_types ) - 1];
        /*判别是不是.xls文件，判别是不是excel文件*/
        if (strtolower ( $file_type ) != "xls" &&strtolower ( $file_type ) != "xlsx")
        {
            $this->error ( '不是Excel文件，重新上传' );
        }
        /*设置上传路径*/
        $savePath =  explode('Application',dirname(__FILE__))[0].'public/upfile/';
        /*以时间来命名上传的文件*/
        $str = date ( 'Ymdhis' );
        $file_name = $str . "." . $file_type;
        /*是否上传成功*/
        if (! copy ( $tmp_file, $savePath . $file_name ))
        {
            $this->error ( '上传失败' );
        }
        /*
           *对上传的Excel数据进行处理生成编程数据,这个函数会在下面第三步的ExcelToArray类中
          注意：这里调用执行了第三步类里面的read函数，把Excel转化为数组并返回给$res,再进行数据库写入
        */
        $res = $this->read ( $savePath . $file_name );
        /*
             重要代码 解决Thinkphp M、D方法不能调用的问题
             如果在thinkphp中遇到M 、D方法失效时就加入下面一句代码
         */
        //spl_autoload_register ( array ('Think', 'autoload' ) );
        /*对生成的数组进行数据库的写入*/
        $phoneListStr='';
        foreach ( $res as $k => $v )
        {
            $phoneListStr.=$v[0].',';
        }
        return $phoneListStr;

    }
    public function read($filename,$encode='utf-8'){

        $file_types = explode ( ".", $filename );
        $file_type = $file_types [count ( $file_types ) - 1];

        if( $file_type =='xlsx' )
        {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        else
        {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }

//        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

}