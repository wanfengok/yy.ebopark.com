<?php
/**
 * Created by PhpStorm.
 * User: gt
 * Date: 2017/8/21
 * Time: 下午3:15
 */
namespace Admin\Controller;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
class OrderController extends BaseController
{
    /**
     * 订单找零页面
     */
    public function changeRecord()
    {
         $this->display();
    }
    /**
     * 订单找零页面分页
     */
    public function changeRecordPageQuery()
    {
        $pageIndex = getParam("pageIndex");
        $pageSize = getParam("pageSize");
        $sql = "SELECT * FROM orders_give_change INNER JOIN parkinfo on orders_give_change.ParkCode = parkinfo.ID";
        $sql1 = "select count(*) as count from orders_give_change INNER JOIN parkinfo ON orders_give_change.ParkCode=parkinfo.ID";
        try {
            $res = D('Order')->query($sql);
            $total = D('Order')->query($sql1);
            $data = array();
            $i=1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["ParkCode"]=is_null($value["ParkCode"])?"":$value["ParkCode"];
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
    /**
     * 订单找零页面查询
     */
    public function changerecordMsg(){
        $haveChange = $_POST['haveChange'];
        $changeMode = $_POST['changeMode'];
        $parkname = $_POST['carName'];
        $carCp = $_POST['carCp'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        if($haveChange =='全部'){
            $haveChange = '0,1,2,3';
        }
        if($haveChange == '是'){
            $haveChange = '2';
        }
        if($haveChange == '否'){
            $haveChange = '0,1,3';
        }
        $sql = "SELECT * FROM orders_give_change INNER JOIN parkinfo on orders_give_change.ParkCode = parkinfo.ID
        where ParkName like '%$parkname%' AND orders_give_change.CarNo LIKE '%$carCp%' and RowTime >= '$startTime' and RowTime <= '$endTime'
        and  State in($haveChange)";
        $data = D("Order")->query($sql);
        $this->ajaxReturn($data);
    }
    /**
     * 订单找零页面状态改变
     */
    public function changeState(){
        $orderno = $_POST['id'];
        $username = session('user_auth.username');
        $sql="update orders_give_change set State = 2 , RefundPlatType = 0 , RefundUserToken = '$username' where OrderNo = '$orderno'";
        $res = D('Order')->query($sql);
    }
    /**
     *欠费记录页面
     */
    public function arrearsRecord(){
        $this->display();
    }
    /**
     * 欠费记录页面分页
     */
    public function arrearsRecordPage(){
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $sql = "SELECT * FROM orders_overdue_bill INNER JOIN parkinfo on orders_overdue_bill.ParkCode = parkinfo.ID";
        $sql1 = "select count(*) as count from orders_overdue_bill INNER JOIN parkinfo ON orders_overdue_bill.ParkCode=parkinfo.ID";

        try {
            $res = D('Order')->query($sql);
            $total = D('Order')->query($sql1);
            $data = array();
            $i=1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["ParkCode"]=is_null($value["ParkCode"])?"":$value["ParkCode"];
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
    /**
     * 欠费找零页面查询
     */
    public function arrearsRecordMsg(){
        $haveChange = $_POST['haveChange'];
        $parkname = $_POST['carName'];
        $carCp = $_POST['carCp'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        if($haveChange =='全部'){
            $haveChange = '0,2';
        }
        if($haveChange == '是'){
            $haveChange = '2';
        }
        if($haveChange == '否'){
            $haveChange = '0';
        }
        $sql = "SELECT * FROM orders_overdue_bill INNER JOIN parkinfo on orders_overdue_bill.ParkCode = parkinfo.ID
        where ParkName like '%$parkname%' AND CarNo LIKE '%$carCp%' and RowTime >= '$startTime' and RowTime <= '$endTime'
        and  State in($haveChange)";
        $data = D("Order")->query($sql);
        $this->ajaxReturn($data);
    }
    /**
     * 欠费找零页面状态改变
     */
    public function changeStateQF(){
        $orderno = $_POST['id'];
        $username = session('user_auth.username');
        $sql="update orders_overdue_bill set State = 2  , Operator = '$username' where OrderNo = '$orderno'";
        $res = D('Order')->query($sql);
    }
    /**
     *订单历史记录页面
     */
    public function orderHistory(){
        $this->display();
    }
    /**
     *找零打印
     */
    function export(){
        $haveChange = getParam('haveChange');
        $changeMode = getParam('changeMode');
        $parkname = getParam('carName');
        $carCp = getParam('carCp');
        $startTime = getParam('startTime');
        $endTime = getParam('endTime');
        if($haveChange =='全部'){
            $haveChange = '0,1,2,3';
        }
        if($haveChange == '是'){
            $haveChange = '2';
        }
        if($haveChange == '否'){
            $haveChange = '0,1,3';
        }
        $colum=array("停车场名称","服务类型","车牌/车位","订单号","订单时间","应收金额","抵扣金额","优惠券金额","实收金额","投币金额","找零金额","是否已兑换","兑换方式","操作人","备注");
        $sql = "SELECT * FROM orders_give_change INNER JOIN parkinfo on orders_give_change.ParkCode = parkinfo.ID
        where ParkName like '%$parkname%' AND orders_give_change.CarNo LIKE '%$carCp%' and RowTime >= '$startTime' and RowTime <= '$endTime'
        and  State in($haveChange)";;
        $res=D("Order")->query($sql);
        $data = array();
        $i=1;
        foreach ($res as $key => $value){
            $item = array();
            $item['parkname'] =is_null($value['parkname'])?"":$value['parkname'];
            $item['lt']='临停';
            $item['carno'] =is_null($value['carno'])?"":$value['carno'];
            $item['orderno'] =is_null($value['orderno'])?"":$value['orderno'];
            $item['rowtime'] =is_null($value['rowtime'])?"":$value['rowtime'];
            $item['totalmoney'] =is_null($value['totalmoney'])?"":$value['totalmoney'];
            $item['dedumoney'] =is_null($value['dedumoney'])?"":$value['dedumoney'];
            $item['couponmoney'] =is_null($value['couponmoney'])?"":$value['couponmoney'];
            $item['paymoney'] =is_null($value['paymoney'])?"":$value['paymoney'];
            $item['tbmoney'] =is_null($value['paymoney'])?"":$value['paymoney'];
            $item['refundmoney'] =is_null($value['refundmoney'])?"":$value['refundmoney'];
            if($haveChange = 2) {
                $item['state'] = '是';
            }
            if($haveChange = 0 ||$haveChange = 1 ||  $haveChange = 3){
                $item['state'] = '否';
            }
            else{
                $item['state'] = '全部';
            }
            $item['refundusertoken'] =is_null($value['refundusertoken'])?"":$value['refundusertoken'];
            if($item['refundusertoken'] == null){
                $item['dh'] = '扫码找零';
            }
            else{
                $item['dh'] = '人工找零';
            }
            $item['RefundUserToken'] =is_null($value['RefundUserToken'])?"":$value['RefundUserToken'];
            $item['description'] =is_null($value['description'])?"":$value['description'];
            array_push($data,$item);
        }
        $this->exportExcel("找零记录",$colum,$data);
    }
    /**
     * 欠费打印
     */
    function exportQf(){
        $haveChange = getParam('haveChange');
        $parkname = getParam('carName');
        $carCp = getParam('carCp');
        $startTime = getParam('startTime');
        $endTime = getParam('endTime');
        if($haveChange =='全部'){
            $haveChange = '0,2';
        }
        if($haveChange == '是'){
            $haveChange = '2';
        }
        if($haveChange == '否'){
            $haveChange = '0';
        }
        $colum=array("停车场名称","服务类型","车牌/车位","订单号","订单时间","应收金额","抵扣金额","优惠券金额","实收金额","投币金额","应还款金额","是否已还款","操作人","备注");
        $sql = "SELECT * FROM orders_overdue_bill INNER JOIN parkinfo on orders_overdue_bill.ParkCode = parkinfo.ID
        where ParkName like '%$parkname%' AND CarNo LIKE '%$carCp%' and RowTime >= '$startTime' and RowTime <= '$endTime'
        and  State in($haveChange)";;
        $res=D("Order")->query($sql);
        $data = array();
        $i=1;
        foreach ($res as $key => $value){
            $item = array();
            $item['parkname'] =is_null($value['parkname'])?"":$value['parkname'];
            $item['lt']='临停';
            $item['carno'] =is_null($value['carno'])?"":$value['carno'];
            $item['orderno'] =is_null($value['orderno'])?"":$value['orderno'];
            $item['rowtime'] =is_null($value['rowtime'])?"":$value['rowtime'];
            $item['totalmoney'] =is_null($value['totalmoney'])?"":$value['totalmoney'];
            $item['dedumoney'] =is_null($value['dedumoney'])?"":$value['dedumoney'];
            $item['couponmoney'] =is_null($value['couponmoney'])?"":$value['couponmoney'];
            $item['paymoney'] =is_null($value['paymoney'])?"":$value['paymoney'];
            $item['tbmoney'] =is_null($value['paymoney'])?"":$value['paymoney'];
            $item['overdueMoney'] =is_null($value['overdueMoney'])?"":$value['overdueMoney'];
            if($haveChange = 2) {
                $item['state'] = '是';
            }
            if($haveChange = 0){
                $item['state'] = '否';
            }
            else{
                $item['state'] = '全部';
            }
            $item['operator'] =is_null($value['operator'])?"":$value['operator'];
            $item['description'] =is_null($value['description'])?"":$value['description'];
            array_push($data,$item);
        }
        $this->exportExcel("欠费记录",$colum,$data);
    }
    /**
     * 打印
     */
    function exportExcel($title,$titleArr,$data){
        header("Content-Type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=".$title.".xls");
        header("Pragma:no-cache");
        header("Expires:0");
        $str='';
        echo iconv("UTF-8","GBK",implode("\t",$titleArr)),"\n";
        $max=count($titleArr);
        foreach($data as $key =>$value){
            echo iconv("UTF-8","GBK",implode("\t",array_slice($value,0,$max))),"\n";
        }
    }


    /**
     * 订单详情页
     */
    public function changeRecordDetails(){
        $this->display();
    }
    /**
     * 详情页数据
     */
    public function detailsMsg(){
        $name=$_POST['name'];
        $id = $_POST['id'];
        if($name == 'qf'){
            $sql="select * from (orders_overdue_bill INNER  JOIN orders_extre_carexit ON orders_overdue_bill.OrderNo = orders_extre_carexit.OrderNo) INNER JOIN parkinfo ON orders_overdue_bill.Parkcode = parkinfo.ID
            WHERE orders_overdue_bill.OrderNo = '$id'";
        }else{
            $sql="select * from (orders_give_change INNER  JOIN orders_extre_carexit ON orders_give_change.OrderNo = orders_extre_carexit.OrderNo) INNER JOIN parkinfo ON orders_give_change.Parkcode = parkinfo.ID
            WHERE orders_give_change.OrderNo = '$id'";
        }
        $res=D("Order")->query($sql);
        $this->ajaxReturn($res);
    }
    /**
     * 详情页状态改变
     */
    public function detailsState(){
        $orderno = $_POST['id'];
        $name = $_POST['name'];
        $state = $_POST['state'];
        $username = session('user_auth.username');
        if($name == 'qf'){
            $sql="update orders_overdue_bill set State = '$state'  , Operator = '$username' where OrderNo = '$orderno'";
        }else{
            $sql="update orders_give_change set State = '$state'  , RefundUserToken = '$username' where OrderNo = '$orderno'";
        }
        $res = D('Order')->query($sql);
    }
}