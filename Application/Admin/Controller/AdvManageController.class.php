<?php
namespace Admin\Controller;

use Admin\Model\AdvConstantModel;
use Think\Exception;
use Admin\Model\AuthGroupModel;

/**
 * 广告管理
 */
class AdvManageController extends BaseController
{

    /**
     * 广告列表页面
     */
    public function index()
    {
        $users=$this->getUsersByGroupId();
        $this->assign("users",json_encode($users));
        $this->display();
    }

    /**
     * 广告添加页面
     */
    public function addAdv(){
        $this->display();
    }

    /**
     * 添加广告
     */
    public function add(){
        $source=getParam("source");
        $advName=getParam("advName");
        $advUrl=getParam("advUrl");
//        $startTime=getParam("startTime");
//        $endTime=getParam("endTime");

        if(empty($advName)||empty($advUrl)||!is_numeric($source)){
            $this->error("参数错误");
        }
        $data["source"]=$source;
        $data["msg_id"]=-1;
        $data["token"]=uuid("adv_");
        $data["name"]=$advName;
        $data["create_time"]=date('Y-m-d H:i:s');
//        $data["starttime"]=$startTime;
//        $data["endtime"]=$endTime;
        $pos=strpos($advUrl,"?");
        if($pos===false){
            $data["url"]=$advUrl."?adv_token=".$data["token"];
        }else{
            $data["url"]=$advUrl."&adv_token=".$data["token"];
        }
        $res= M("advertisement")->add($data);

        if(empty($res)){

            $this->error("添加失败,请稍后重试");
        }else{
            $this->success("添加成功","index");
        }


    }
    /**
     * 获取广告列表
     */
    public function getAdvList()
    {
        $res = $this->advPageQuery('advertisement');
        $this->ajaxReturn($res);
    }

    /**
     * 我的广告页面
     */
    public function myAdvs(){
        $this->display();
    }

    /**
     *
     */
    public function getMyAdvs(){
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $uid=session("user_auth")["uid"];

        try {
            //total
            $total = M('advertisement')->where(IS_ROOT?'':"uids like '%,".$uid.",%'")->count();
            $res = M('advertisement')->page($pageIndex, $pageSize)->order("id desc")->where(IS_ROOT?"":"uids like '%,".$uid.",%'")->select();
            $res = $this->wrapData($res);
            $data["total"] = $total;
            $data["data"] = $res;
            $data["state"] = 0;
            $this->ajaxReturn($data);
        } catch (\Think\Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $data["total"] = 0;
            $this->ajaxReturn($data);
        }
    }
    /**
     * 广告分页查询
     */
    private function advPageQuery()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        try {
            //total
            $total = M('advertisement')->count();
            $res = M('advertisement')->page($pageIndex, $pageSize)->order("id desc")->select();
            $res = $this->wrapData($res);
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
     * 封装列表信息
     * @param $res
     * @return array
     */
    public function wrapData($res)
    {
        if (empty($res)) {
            return $res;
        }
        $data = array();
        foreach ($res as $key => $value) {
            $item = $this->getMessageById($value);
            if (empty($item)) {
                continue;
            }
            array_push($data, $item);
        }
        return $data;
    }

    /**
     * 获取广告详细信息
     * @param $adv
     * @return mixed
     */
    private function getMessageById($adv)
    {

        $item = array();
        switch ($adv["source"]) {
            case AdvConstantModel::ADV_SOURCE_SYSTEM_INFO:
                $item = $this->getWrapSysinfo($adv);
                break;
            case AdvConstantModel::ADV_SOURCE_BANNER:
                $item=$this->getBannerInfo($adv);
                break;
            case AdvConstantModel::ADV_SOURCE_WX:
                $item=$this->getWrapWxinfo($adv);
                break;
        }
        return $item;
    }
    private function getBannerInfo($adv){
        $item["uids"]=$adv["uids"];
        $item["id"]=$adv["id"];
        $item["source"] ="banner";
        $item["name"] = $adv["name"];
        $item["url"] = $adv["url"];
        $item["endtime"] ='-';
        $item["starttime"] ='-';
        $item["send"] ="-";
        $item["pv"] = $this->getMessagePvByToken($adv["token"]);
        $item["uv"] =  $this->getMessageUvByToken($adv["token"]);
        return $item;
    }
    /**
     * 获取微信消息
     * @param $adv
     */
    private function getWrapWxinfo($adv){

        $message = D("MsgDefineWx")->getMessageById($adv["msg_id"]);
        if(empty($message)){
           // return $message;
            $item["uids"]=$adv["uids"];
            $item["id"]=$adv["id"];
            $item["source"] ="消息已删除";
            $item["name"] = "-";
            $item["url"] = "-";
            $item["endtime"] ="-";
            $item["starttime"] = "-";
            $item["send"] =0;
            $item["pv"] = $this->getMessagePvByToken($adv["token"]);
            $item["uv"] =  $this->getMessageUvByToken($adv["token"]);
            return null;
        }
        $item["uids"]=$adv["uids"];
        $item["id"]=$adv["id"];
        $item["source"] = $this->getWxSourceName($message);
        $item["name"] = $message["remark"];
        $item["url"] = $message["jumpconfig"];
        $item["endtime"] = $message["endtime"];
        $item["starttime"] = $message["starttime"];
        $item["send"] =empty( $message["cnt"])?0:$message["cnt"];;
        $item["pv"] = $this->getMessagePvByToken($adv["token"]);
        $item["uv"] =  $this->getMessageUvByToken($adv["token"]);
        return $item;
    }

    /**
     * 获取微信来源描述
     * @param $message
     */
    private function getWxSourceName($message){
        $triggertype=$message["triggertype"];
        $triggerDesc='';
        switch($triggertype){
            case '103':$triggerDesc='入场通知';break;
            case '106':$triggerDesc='出场通知';break;
            case '901':$triggerDesc='登录成功';break;
            case '801':$triggerDesc='优惠返还';break;
            case '1001':
            case '1002':$triggerDesc='洗车活动';break;
        }
        return $triggerDesc;
    }
    private function getWrapSysinfo($adv)
    {
        $message = D("MsgDefineSystem")->getMessageById($adv["msg_id"]);
        if(empty($message)){
            // return $message;
            $item["uids"]=$adv["uids"];
            $item["id"]=$adv["id"];
            $item["source"] ="消息已删除";
            $item["name"] = "-";
            $item["url"] = "-";
            $item["endtime"] ="-";
            $item["starttime"] = "-";
            $item["send"] =0;
            $item["pv"] = $this->getMessagePvByToken($adv["token"]);
            $item["uv"] =  $this->getMessageUvByToken($adv["token"]);
            return null;
        }
        $item["uids"]=$adv["uids"];
        $item["id"]=$adv["id"];
        $item["source"] = "系统消息";
        $item["name"] = $message["title"];
        $item["url"] = $message["jumpconfig"];
        $item["endtime"] = $message["endtime"];
        $item["starttime"] = $message["starttime"];
        $item["send"] = empty($adv["send"])?0:$adv["send"];
        $item["pv"] = $this->getMessagePvByToken($adv["token"]);
        $item["uv"] =  $this->getMessageUvByToken($adv["token"]);
        return $item;
    }
    /**
     * 根据token获取广告pv
     * @param $token
     */
    private function getMessagePvByToken($token)
    {
        $count=M("adv_access_log")->where("adv_token='$token'")->count();
        return $count;
    }
    /**
     * 根据token获取广告UV
     * @param $token
     */
    private function getMessageUvByToken($token)
    {
        $count= M("adv_access_log")->where("adv_token='$token'")->count("DISTINCT user_token");
        return $count;
    }

    public function deleteAdv()
    {
        $id = getParam("id");
        if (!is_numeric($id)) {
            $resp["state"] = -1;
            $resp["msg"] = "参数错误";
            $this->ajaxReturn($resp);
            return;
        }
        try {
            //删除访问日志
            $res=M("advertisement")->where("id=$id")->select();
            $token=$res[0]["token"];
            M("adv_access_log")->where("adv_token='$token'")->delete();
            //删除广告
            M("advertisement")->where("id=$id")->delete();
            $resp["state"] = 0;
        } catch (Exception $e) {
            $resp["state"] = -1;
            $resp["msg"] = $e->getMessage();

        }
        $this->ajaxReturn($resp);

    }

    public function getUsersByGroupId(){

        $prefix = C('DB_PREFIX');
        $l_table = $prefix . (AuthGroupModel::UCENTER_MEMBER);
        $r_table = $prefix . (AuthGroupModel::AUTH_GROUP_ACCESS);
        $data = M()->table($l_table . ' m')
                    ->join($r_table . ' a ON m.id=a.uid')
                    ->where(array('a.group_id'=> AdvConstantModel::ADV_GROUP_ID, 'm.status'=> array('egt', 0)))->select();
        return $data;
    }
    public function updateAdvAuth(){
        $id=getParam("id");
        $uids=getParam("uids");
        if(!is_numeric($id)){
            $data["state"]=-1;
            $data["msg"]='参数错误';
            $this->ajaxReturn($data);
        }
        $data["id"]=$id;
        $data["uids"]=$uids;
        $res=  M("advertisement")->save($data);
        if($res===fasle){
            $data["state"]=-1;
            $data["msg"]='数据库操作失败,请稍后重试!';
        }
        $data["state"]=0;
        $this->ajaxReturn($data);

    }
}
