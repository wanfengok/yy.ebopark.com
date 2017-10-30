<?php
/**
 * Created by PhpStorm.
 * User: gt
 * Date: 2017/9/5
 * Time: 下午5:29
 */
namespace Admin\Controller;
use Think\Exception;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Admin\Model\AuthGroupModel;

class RefundController extends BaseController
{
    public function refund()
    {
        $this->display();
    }
    /**
     * 退费页面分页加载
     */
    public function refundMsg(){
        $pageIndex = getParam("pageIndex");
        $pageSize = getParam("pageSize");
        $sql = "select orders_cancel_apply.OrderNo,orders_cancel_apply.UserID,orders_success.OrderTime,
orders_cancel_apply.RowTime,orders_cancel_apply.OperateTime,orders_cancel_apply.State,orders_success.OrderMoney,sysuser.Mobile,activity_dn97.ActivityName
from orders_cancel_apply 
left join activity_dn97 on orders_cancel_apply.OrderNo = activity_dn97.OrderNo
left join sysuser on orders_cancel_apply.UserId = sysuser.ID
left join orders_success on orders_cancel_apply.OrderNo = orders_success.OrderNo order by orders_cancel_apply.RowTime desc";
        $sql1 = "select count(*) from (
(orders_cancel_apply INNER JOIN activity_dn97 on orders_cancel_apply.OrderNo = activity_dn97.OrderNo)
inner join  orders_success on orders_cancel_apply.OrderNo = orders_success.OrderNo
)
inner join sysuser on orders_cancel_apply.UserId = sysuser.ID ";
        try {
            $res = D('Refund')->query($sql);
            $total = D('Refund')->query($sql1);
            $data = array();;
            $i=1;
            foreach($res as $key => $value){
                $item['orderno'] = is_null($value["orderno"])?"":$value["orderno"];
                $item['activityname'] = is_null($value['activityname'])?'':$value['activityname'];
                $item['ordertime'] = is_null($value['ordertime'])?'':$value['ordertime'];
                $item['rowtime'] = is_null($value['rowtime'])?'':$value['rowtime'];
                $item['state'] = is_null($value['state'])?'':$value['state'];
                $item['ordermoney'] = is_null($value['ordermoney'])?'':$value['ordermoney'];
                $item['mobile'] = is_null($value['mobile'])?'':$value['mobile'];
                $item['operatetime'] = is_null($value['operatetime'])?'':$value['operatetime'];
                array_push($data, $item);
                $i++;
            }
            $result["total"] = $total[0]["count(*)"];
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
     *退费页面状态修改
     * */
    public function changeState(){
        $id = $_POST['orderno'];
        $state = $_POST['state'];
        $time = $_POST['time'];
        if($state == '退费'){
            $state = 1;
        }
        if($state == '拒绝'){
            $state = 2;
        }
        $username = session('user_auth.username');
        $sql = "update orders_cancel_apply set State = '$state' ,Operator = '$username',OperateTime = '$time' where OrderNo = '$id'";
        $res = D("Refund")->query($sql);
    }
    /**
     *页面查询
     * */
    public function RefundQuery(){
        $pageIndex = getParam("pageIndex");
        $pageSize = getParam("pageSize");
        $carName = getParam('carname');
        $state = getParam('state');
        if($state == '全部'){
            $state = '0,1,2';
        }
        if($state == '已退费'){
            $state = '1';
        }
        if($state == '已拒绝'){
            $state = '2';
        }
        if($state == '未处理'){
            $state = '0';
        }
        if($state == '已处理'){
            $state = '1,2';
        }
        $sql = "select orders_cancel_apply.OrderNo,orders_cancel_apply.UserID,orders_success.OrderTime,
orders_cancel_apply.RowTime,orders_cancel_apply.OperateTime,orders_cancel_apply.State,orders_success.OrderMoney,sysuser.Mobile,activity_dn97.ActivityName
from orders_cancel_apply 
left join activity_dn97 on orders_cancel_apply.OrderNo = activity_dn97.OrderNo
left join sysuser on orders_cancel_apply.UserId = sysuser.ID
left join orders_success on orders_cancel_apply.OrderNo = orders_success.OrderNo";
        if($carName == ''){
            $sql.=" where orders_cancel_apply.state in ($state)";
        }else{
            $sql.=" where sysuser.Mobile ='$carName' and orders_cancel_apply.State in($state)";
        }
        $sql1 = "select count(*) from (
(orders_cancel_apply INNER JOIN activity_dn97 on orders_cancel_apply.OrderNo = activity_dn97.OrderNo) 
inner join  orders_success on orders_cancel_apply.OrderNo = orders_success.OrderNo
) 
inner join sysuser on orders_cancel_apply.UserId = sysuser.ID";
        try {
            $res = D('Refund')->query($sql);
            $total = D('Refund')->query($sql1);
            $data = array();;
            $i=1;
            foreach($res as $key => $value){
                $item['orderno'] = is_null($value["orderno"])?"":$value["orderno"];
                $item['activityname'] = is_null($value['activityname'])?'':$value['activityname'];
                $item['ordertime'] = is_null($value['ordertime'])?'':$value['ordertime'];
                $item['rowtime'] = is_null($value['rowtime'])?'':$value['rowtime'];
                $item['state'] = is_null($value['state'])?'':$value['state'];
                $item['ordermoney'] = is_null($value['ordermoney'])?'':$value['ordermoney'];
                $item['mobile'] = is_null($value['mobile'])?'':$value['mobile'];
                $item['operatetime'] = is_null($value['operatetime'])?'':$value['operatetime'];
                array_push($data, $item);
                $i++;
            }
            $result["total"] = $total[0]["count(*)"];
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
}
