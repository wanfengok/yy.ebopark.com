<?php
namespace Admin\Controller;
use Admin\Model\ParkAccountInfoModel;
use Admin\Model\ParkinfoModel;
use Admin\Model\ParkSettleMentRateModel;
use Admin\Model\VirtualAccountConfigNoticeModel;
use Admin\Model\VirtualAccountConfigPayRateModel;
use Admin\Model\VirtualAccountParkinfoModel;
use Think\Exception;

/**
 * 后台配置控制器
 */
class ParkMsController extends BaseController
{
    /**
     * 异常订单
     */
    public function exceptionwithdrawLogs(){
        $noticeInfo=(new VirtualAccountConfigNoticeModel())->limit(1)->find();
        $this->assign("notice_email",empty($noticeInfo)||empty($noticeInfo["email"])?"":$noticeInfo["email"]);
        $this->display();
    }
    public function withdrawLogs(){
        $noticeInfo=(new VirtualAccountConfigNoticeModel())->limit(1)->find();
        $this->assign("notice_mobile",empty($noticeInfo)||empty($noticeInfo["telephone"])?"":$noticeInfo["telephone"]);
        $this->display();
    }

    /**
     * 标记异常状态
     */
    public function markOrderWithStatus(){
        $this->ajaxReturn(D("VirtualAccount","Service")->markOrderWithStatus());
    }
    /**
     * 更新邮件通知
     */
    public function updateBindEmail(){
        $this->ajaxReturn(D("VirtualAccount","Service")->updateBindEmail());
    }
    /**
     * 更新手机通知
     */
    public function updateBindMobile(){
        $this->ajaxReturn(D("VirtualAccount","Service")->updateBindMobile());

    }
    /**
     * 获取提现记录
     */
    public function getWithDrawLogs(){
        $this->ajaxReturn(D("VirtualAccount","Service")->getWithdrawLogs());
    }

    /**
     * 获取异常订单
     */
    public function getExceptionwithdrawLogs(){
        $this->ajaxReturn(D("VirtualAccount","Service")->getExceptionwithdrawLogs());
    }
    /**
     * 更新车场账户信息
     */
    public function updateVirtualAccountInfo(){
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->updateVirtualAccountInfo());
    }
    /**
     *停车场基本信息更新页面
     */
    public function parkingUpdate(){
        $id=getParam("id");
        if(empty($id)||!is_numeric($id)){
            $this->error("参数错误");
        }

        $parkinfo=(new ParkinfoModel())->query("select * from parkinfo where Id=$id");
        if(empty($parkinfo)){
            $this->error("该停车场不存在");
        }
        //费率设置
        $rateinfo=(new VirtualAccountConfigPayRateModel())->where("ParkCode=".$parkinfo[0]["parkcode"])->select();
        if(empty($rateinfo)){
            $rateinfo=false;
        }
        $accountinfo=(new ParkAccountInfoModel())->where("ParkID=".$parkinfo[0]["parkcode"])->find();
        $virtualAccountInfo=(new VirtualAccountParkinfoModel())->where(["ParkCode"=>$parkinfo[0]["parkcode"]])->find();
        $freefee="";
        $busyfee="";
        $overfreefee="";
        $overbusyfee="";
        $memberfee="";
        foreach($rateinfo as $key=>$val){
            $settlementrate=$val["percentage"];
            $settlementsort=$val["allowedpurpose"];
            switch($settlementsort){
                case 21:$freefee=$settlementrate;break;
                case 22:$busyfee=$settlementrate;break;
                case 2:$overfreefee=$settlementrate;break;
                case 3:$overbusyfee=$settlementrate;break;
                case 40:$memberfee=$settlementrate;break;
            }

        }
        $this->assign("id",$id);
        $this->assign("freefee",round($freefee,2));
        $this->assign("busyfee",round($busyfee,2));
        $this->assign("overfreefee",round($overfreefee,2));
        $this->assign("overbusyfee",round($overbusyfee,2));
        $this->assign("memberfee",round($memberfee,2));
        $this->assign("accountinfo",$accountinfo);
        $this->assign("rateinfo",json_encode(empty($rateinfo)?[]:$rateinfo));
        $this->assign("virtualAccountInfo",json_encode(empty($virtualAccountInfo)?"":$virtualAccountInfo));
        $this->assign("parkinfo",$parkinfo[0]);

        $this->display();
    }
    /**
     * 更新停车场基本信息
     */
    public function updateParkinglot()
    {
        $parkMsService = D("ParkMs", "Service");
        $res=$parkMsService->updatePark();
        $this->ajaxReturn($res);
    }

    /**
     * 更新停车场账户信息
     */
    public function updateParkAccountInfo(){
        $parkMsService = D("ParkMs", "Service");
        $res=$parkMsService->updateParkAccountInfo();
        $this->ajaxReturn($res);
    }

    /**
     * 获取车场列表
     */
    public function pagequery()
    {
        $keyword=getParam("keyword");
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->pageQuery($keyword));
    }
    /**
     * 添加停车场基本信息
     */
    public function addParkinglot()
    {
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->addPark());
    }
    /**
     *停车场登记
     * @return none
     */
    public function addPark()
    {
        $this->display();
    }
    /**
     * 停车场权限管理
     */
    public function parkAuthMs()
    {
        $this->display();
    }
    /*
     * 率费信息
     * */
    public function parkingLot()
    {
        $parkinfo=D("Map","Service")->getAllParkinfo();
        $this->assign("parkinfo",json_encode($parkinfo));
        $this->display();
    }
    /*编辑停车场*/
    public function parkingAdd()
    {
        $this->display();
    }
    /**
     * 权限管理
     */
    public function auMs()
    {

        $uid = getParam("uid");
        $nickname=getParam("nickname");
        if (empty($uid)||empty($nickname)) {
            $this->error("参数错误");
        }
        $parkMsService = D("ParkMs", "Service");
        $treeData = $parkMsService->getParksTreeData();
        $parks = $parkMsService->getParksByUid($uid);
        if ($treeData["state"] != 0 || $parks["state"] != 0) {
            $this->error($treeData["desc"]);
        }
        $this->assign("data", $treeData["data"]);
        $this->assign("parklist",$parks["data"]);
        $this->assign("uid",$uid);
        $this->assign("nickname",$nickname);
        $this->display();
    }
    public function addParklot(){
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->addParklot());
    }
    public function delParklot(){
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->delParklot());
    }
    /*
     * 删除数据
     * */
    public function pagedetele(){
        $parkMsService = D("ParkMs", "Service");
        $this->ajaxReturn($parkMsService->pageDetele());
    }
    /**
     * 获取七牛key
     */
    public function getQiNiuKey()
    {
        $token = "";
        $privatekey = '0xOWPxOtXev3#$sCC4AxSoSJpr4LCY4b';//session("privatekey");
        $url = getUrl('/resource/token', [], $token, $privatekey);
        $res = curl($url, []);
        $this->ajaxReturn(json_decode($res));
    }
}

