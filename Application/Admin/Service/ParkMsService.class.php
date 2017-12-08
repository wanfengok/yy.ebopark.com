<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/27
 * Time: 下午3:12
 */

namespace Admin\Service;

use Admin\Model\ParkAccountInfoModel;
use Admin\Model\ParkinfoModel;
use Admin\Model\ParkSettleMentRateModel;
use Admin\Model\VirtualAccountConfigPayRateModel;
use Admin\Model\VirtualAccountParkinfoModel;
use Think\Exception;
use Think\Model;

class ParkMsService
{


    /**
     * 表单验证
     */
    public function checkForm()
    {

        //地域编码
        $data["AreaCode"] = getParam("bm");
        $data["ParkCode"] = getParam("parkbm");
        $data["ParkAddr"] = getParam("dz");
        $data["ParkName"] = getParam("name");
        $data["City"] = getParam("city");
        $data["ParkCompany"] = getParam("companyname");


        $data["FixLimtTime"] = getParam("monthlyEndDate");
        $data["ImgUrl"] = getParam("parkpic");
        $data["CADUrl"] = getParam("parkcad");
        $data["ParkStatus"] = getParam("parkStatus");
        $data["ParkOperator"] = getParam("manager");
        $data["ParkTel"] = getParam("contractInfo");
        $data["fixaccred"] = getParam("yzybrz");
        $data["tmppayconfirm"] = getParam("lytc");
        $data["ismember"] = getParam("yzhy");

        if (empty($data["AreaCode"]) || empty($data["ParkCode"]) || empty($data["ParkAddr"]) || empty($data["ParkName"]) || empty($data["City"]) || empty($data["ParkCompany"])) {
            return $this->getWrapData(-1, "请填写完整的车场信息");
        }
        $res = ["state" => 0, "data" => $data];
        return $res;

    }

    public function parkAccountFormCheck()
    {
        $name=getParam("Name");
        if (empty($name)) {
            return $this->getWrapData(-1, "收款人不能为空!");
        }
        $BankAccount=getParam("BankAccount");
        if (empty($BankAccount)) {
            return $this->getWrapData(-1, "银行卡号不能为空!");
        }
        $BankName=getParam("BankName");
        if (empty($BankName)) {
            return $this->getWrapData(-1, "开户行不能为空!");
        }
        $BankNo=getParam("BankNo");
        if (empty($BankNo)) {
            return $this->getWrapData(-1, "开户行行号不能为空!");
        }
        $BankAddress=getParam("BankAddress");
        if (empty($BankAddress)) {
            return $this->getWrapData(-1, "开户行地址不能为空!");
        }
        return ["state" => 0, "data" => $_POST];
    }

    /**
     * 添加停车场
     */
    public function addPark()
    {
        if (!UID) {
            return $this->getWrapData(-1, "授权失败,请稍后重试");
        }
        $checkRes = $this->checkForm();
        if ($checkRes["state"] != 0) {
            return $checkRes;
        }
        return $this->insert($checkRes["data"]);

    }

    /**
     * 修改停车场
     * @return array|mixed
     */
    public function updatePark()
    {
        if (!UID) {
            return $this->getWrapData(-1, "授权失败,请稍后重试");
        }
        $checkRes = $this->checkForm();
        if ($checkRes["state"] != 0) {
            return $checkRes;
        }
        $id = getParam("id");
        if (empty($id)) {
            return $this->getWrapData(-1, "参数错误");
        }
        return $this->update($id, $checkRes["data"]);

    }

    public function updateParkAccountInfo()
    {
        if (!UID) {
            return $this->getWrapData(-1, "授权失败,请稍后重试");
        }
        $checkRes = $this->parkAccountFormCheck();
        if ($checkRes["state"] != 0) {
            return $checkRes;
        }
        $data = $checkRes["data"];
        $parkAccountInfo = new ParkAccountInfoModel();
        $parkAccountInfo->startTrans();
        try {
            $this->updateAccountInfo($parkAccountInfo, $data);
            $this->updateAccountRate();
            $parkAccountInfo->commit();
            return $this->getWrapData(0);
        } catch (Exception $e) {
            $parkAccountInfo->rollback();
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    /**
     * 分成比例
     */
    private function updateAccountRate()
    {

        $freefee = $_GET["freefee"];
        $busyfee = $_GET["busyfee"];
        $overfreefee = $_GET["overfreefee"];
        $overbusyfee = $_GET["overbusyfee"];
        $memberfee = $_GET["memberfee"];
        $parkcode = getParam("ParkID");
        $data = [];
        //临停缴费
        $parkfeeRate = $_GET["parkfeeRate"];//1:临停
        $parkItem = ["ParkCode" => $parkcode, "AllowedPurpose" => 1, "WeiXinRate" => 0, "AlipayRate" => 0, "", "UnionpayRate" => 0, "Percentage" => 0, "OtherRate" => 0];
        $parkfeeRatechannelIndex = strpos($parkfeeRate, '_');
        if ($parkfeeRatechannelIndex !== false) {
            $rateArr = explode("_", $parkfeeRate);
            $parkItem["AlipayRate"] = $rateArr[0];
            $parkItem["WeiXinRate"] = $rateArr[1];
            $parkItem["OtherRate"] = $rateArr[2];
            $parkItem["UnionpayRate"] = $rateArr[3];
        } else {
            $parkItem["AlipayRate"] = $parkfeeRate;
            $parkItem["WeiXinRate"] = $parkfeeRate;
            $parkItem["OtherRate"] = $parkfeeRate;
            $parkItem["UnionpayRate"] = $parkfeeRate;
        }
        array_push($data, $parkItem);
        //月租
        $monthItem = ["ParkCode" => $parkcode, "AllowedPurpose" => 20, "WeiXinRate" => 0, "AlipayRate" => 0, "", "UnionpayRate" => 0, "Percentage" => 0, "OtherRate" => 0];
        $monthlyrate = $_GET["monthlyrate"];
        $monthlyratechannelIndex = strpos($monthlyrate, '_');
        if ($monthlyratechannelIndex !== false) {
            $rateArr = explode("_", $monthlyrate);
            $monthItem["AlipayRate"] = $rateArr[0];
            $monthItem["WeiXinRate"] = $rateArr[1];
            $monthItem["OtherRate"] = $rateArr[2];
            $monthItem["UnionpayRate"] = $rateArr[3];
        } else {
            $monthItem["AlipayRate"] = $monthlyrate;
            $monthItem["WeiXinRate"] = $monthlyrate;
            $monthItem["OtherRate"] = $monthlyrate;
            $monthItem["UnionpayRate"] = $monthlyrate;
        }
        array_push($data, $monthItem);
        //时段月租购买
        $intervalmonthlyItem = ["ParkCode" => $parkcode, "AllowedPurpose" => 50, "WeiXinRate" => 0, "AlipayRate" => 0, "", "UnionpayRate" => 0, "Percentage" => 0, "OtherRate" => 0];
        $intervalmonthlyrate = $_GET["freemonthlyrate"];
        $intervalmonthlyratechannelIndex = strpos($intervalmonthlyrate, '_');
        if ($intervalmonthlyratechannelIndex !== false) {
            $rateArr = explode("_", $intervalmonthlyrate);
            $intervalmonthlyItem["AlipayRate"] = $rateArr[0];
            $intervalmonthlyItem["WeiXinRate"] = $rateArr[1];
            $intervalmonthlyItem["OtherRate"] = $rateArr[2];
            $intervalmonthlyItem["UnionpayRate"] = $rateArr[3];
        } else {
            $intervalmonthlyItem["AlipayRate"] = $intervalmonthlyrate;
            $intervalmonthlyItem["WeiXinRate"] = $intervalmonthlyrate;
            $intervalmonthlyItem["OtherRate"] = $intervalmonthlyrate;
            $intervalmonthlyItem["UnionpayRate"] = $intervalmonthlyrate;
        }
        array_push($data, $intervalmonthlyItem);
        //时段月租续费
        $intervalmonthlyItemRenew = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $intervalmonthlyItemRenew["AllowedPurpose"] = 51;
        array_push($data, $intervalmonthlyItemRenew);
        //闲时月租分成比例
        $freeIntervalMonth = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $freeIntervalMonth["AllowedPurpose"] = 21;
        $freeIntervalMonth["Percentage"] = $freefee;
        array_push($data, $freeIntervalMonth);
        //忙时月租分成比例
        $busyIntervalMonth = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $busyIntervalMonth["AllowedPurpose"] = 22;
        $busyIntervalMonth["Percentage"] = $busyfee;
        array_push($data, $busyIntervalMonth);
        //闲时月租超时分成比例
        $overfreeIntervalMonth = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $overfreeIntervalMonth["AllowedPurpose"] = 2;
        $overfreeIntervalMonth["Percentage"] = $overfreefee;
        array_push($data, $overfreeIntervalMonth);
        //忙时月租超时分成比例
        $overbusyIntervalMonth = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $overbusyIntervalMonth["AllowedPurpose"] = 3;
        $overbusyIntervalMonth["Percentage"] = $overbusyfee;
        array_push($data, $overbusyIntervalMonth);
        //月租会员分成比例
        $vipMonth = array_combine(array_keys($intervalmonthlyItem), $intervalmonthlyItem);
        $vipMonth["AllowedPurpose"] = 40;
        $vipMonth["Percentage"] = $memberfee;
        array_push($data, $vipMonth);

        $virtualAccountConfigPayRate = new VirtualAccountConfigPayRateModel();
        $virtualAccountConfigPayRate->where("ParkCode=$parkcode")->delete();
        $virtualAccountConfigPayRate->addAll($data);

    }

    //更新账户基本信息
    private function updateAccountInfo($parkAccountInfo, $data)
    {
        $parkcode = getParam("ParkID");
        $res = $parkAccountInfo->where("ParkID=$parkcode")->find();
        if (empty($res)) {
            //新增
            $insertRes = $parkAccountInfo->data($data)->add();
            if (empty($insertRes)) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            return $this->getWrapData(0);
        }

        //更新
        $updateres = $parkAccountInfo->where("ParkID=$parkcode")->save($data);
        if ($updateres === false) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
        return $this->getWrapData(0);
    }

    /**
     * 更新数据
     * @return mixed
     */
    private function update($id, $item)
    {
        try {
            $parkinfoModel = new ParkinfoModel();
            $res = $parkinfoModel->where("id=$id")->save($item);

            if ($res === false) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            return $this->getWrapData(0);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    /**
     * 新增数据
     * @return mixed
     */
    private function insert($item)
    {
        try {
            //检验parkcode一致性
            $parkinfoModel = new ParkinfoModel();
            $res = $parkinfoModel->query("select * from parkinfo where ParkCode='" . $item["ParkCode"] . "'");
            if (!empty($res)) {
                return $this->getWrapData(-1, "停车场编码已存在,请修改后再试");
            }
            //插入
            $res = $parkinfoModel->data($item)->add();

            if (empty($res)) {
//                $sql=$parkinfoModel->_sql();
                return $this->getWrapData(-1, "数据库操作失败");
            }
            return $this->getWrapData(0);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    public function getWrapData($state = -1, $desc = "", $obj = [])
    {
        $data["state"] = $state;
        $data["desc"] = $desc;
        if (!empty($obj)) {
            $data["data"] = $obj;
        }
        return $data;
    }

    /**
     * 车场分页查询
     * @return mixed
     */
    public function pageQuery($keyword)
    {

        $pageindex = getParam("pageIndex");
        $pagesize = getParam("pageSize");
        if (!is_numeric($pageindex) || !is_numeric($pagesize)) {
            return $this->getWrapData(-1, "参数错误");
        }
        $offset = $pageindex * $pagesize;
        $where = empty($keyword) ? "" : "where ParkName like '%$keyword%'";
        $parkInfoModel = new ParkinfoModel();
        $res = $parkInfoModel->query("select * from parkinfo $where order by ID desc limit  $offset ,$pagesize");
        if ($res === false) {
            return $this->getWrapData(-1, '数据读取失败,请稍后再试');
        }
        $total = $parkInfoModel->query("select count(*) as amount from parkinfo $where");
        $data = $this->getWrapData(0, '', $res);
        $data["total"] = $total[0]["amount"];
        return $data;
    }

    public function getParksTreeData()
    {
        try {
            $res = M("")->query("select * from oms_parkinfo order by ParkCompany desc");
            if ($res === false) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            $data = [];
            foreach ($res as $val) {
                $companyName = $val["parkcompany"];
                if (empty($companyName)) {
                    continue;
                }
                $data[$companyName][] = $val;
            }
            return $this->getWrapData(0, "", $data);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    public function getParksByUid($uid)
    {
        try {
            $res = M("")->query("select * from oms_authpark where UserId = $uid");
            if ($res === false) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            return $this->getWrapData(0, "", $res);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    public function addParklot()
    {
        $parkcode = getParam("parkcode");
        $parkname = getParam("parkname");


        $uid = getParam("uid");
        if (empty($parkcode) || empty($parkname) || empty($uid)) {
            return $this->getWrapData(-1, "参数错误");
        }
        try {
            $res = M("")->execute("insert into oms_authpark(UserId,ParkCode,ParkName,RowTime) values($uid,'$parkcode','$parkname',now())");
            if ($res === false) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            return $this->getWrapData(0);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }


    }

    public function delParklot()
    {

        $parkcode = getParam("parkcode");
        $uid = getParam("uid");
        if (empty($parkcode) || empty($uid)) {
            return $this->getWrapData(-1, "参数错误");
        }
        try {
            $res = M("")->execute("delete from  oms_authpark where UserId=$uid and ParkCode='$parkcode'");
            if ($res === false) {
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            return $this->getWrapData(0);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    /*
     * 删除
     * */
    public function pageDetele()
    {
        $id = getParam("id");
        $parkcode = getParam("parkcode");
        $db = new ParkinfoModel();
        try {
            $db->startTrans();
            $res = $db->execute("delete from parkinfo where ID in($id)");
            if ($res === false) {
                $db->rollback();
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            //删除
            $res = $db->execute("delete from yun_authpark where ParkCode in($parkcode)");
            if ($res === false) {
                $db->rollback();
                return $this->getWrapData(-1, "数据操作失败,请稍后重试");
            }
            $db->commit();
            return $this->getWrapData(0);
        } catch (Exception $e) {
            $db->rollback();
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }
    }

    public function updateVirtualAccountInfo()
    {
        $isOpen = getParam("isOpen");
        $ChoosedBank = getParam("ChoosedBank");
        $IsAuto = getParam("IsAuto");
        $NewScheduledWay = getParam("NewScheduledWay");
        $daySelect = getParam("daySelect");
        $weekSelect = getParam("weekSelect");
        $AllowManual = getParam("AllowManual");
        $LeftCouponMoney = getParam("LeftCouponMoney");
        $FinanceName = getParam("FinanceName");
        $Telephone = getParam("Telephone");
        $ParkID = getParam("ParkID");
        if (empty($FinanceName)) {
            return $this->getWrapData(-1, "财务联系人不能为空");;
        }
        if (empty($Telephone)) {
            return $this->getWrapData(-1, "财务联系电话不能为空");
        }
        if (!empty($LeftCouponMoney) && !is_numeric($LeftCouponMoney)) {
            return $this->getWrapData(-1, "优惠券存量填写错误");;
        }
        $virtualAccountParkInfo = new VirtualAccountParkinfoModel();
        $parkInfo = $this->getVirtualAccountByParkcode($virtualAccountParkInfo, $ParkID);
        try {
            $t_n = $NewScheduledWay == 2 ? $daySelect : ($NewScheduledWay == 1 ? $weekSelect : 1);
            $virtualAccountParkInfoData=["ParkCode" => $ParkID,
                "OpenTime" => date("Y-m-d", strtotime("+1 day")),
                "IsAuto" => $IsAuto,
                "AllowManual" => $AllowManual,
                "NewAutoTransferInterval" => $t_n,
                "NewManualInterval" => $t_n,
                "NewScheduledWay" => $NewScheduledWay,
                "LeftCouponMoney" => $LeftCouponMoney,
                "FinanceName" => $FinanceName,
                "Telephone" => $Telephone,
                "ChoosedBank" => $ChoosedBank
            ];
            if ($isOpen == 0) {
                //关闭停车场
                $virtualAccountParkInfoData["OpenTime"]="2099-01-01 00:00:00";
                $virtualAccountParkInfoData["Status"]=1;
                if (empty($parkInfo)) {
                    $virtualAccountParkInfo->add($virtualAccountParkInfoData);
                }else{
                    $virtualAccountParkInfo->where(["ParkCode" => $ParkID])->save($virtualAccountParkInfoData);
                }
                return $this->getWrapData(0);
            }
            //开启停车场
            //T+N
            if (empty($parkInfo)) {
                $virtualAccountParkInfoData["ManualInterval"]=$t_n;
                $virtualAccountParkInfoData["AutoTransferInterval"]=$t_n;
                $virtualAccountParkInfoData["ScheduledWay"]=$NewScheduledWay;
                $virtualAccountParkInfoData["Status"]=0;
                $virtualAccountParkInfo->add($virtualAccountParkInfoData);
            } else {
                $virtualAccountParkInfoData["Status"]=1;
                $virtualAccountParkInfo->where(["ParkCode" => $ParkID])->save($virtualAccountParkInfo);
            }
            return $this->getWrapData(0);
        } catch (Exception $e) {
            return $this->getWrapData(-1, "数据操作失败,请稍后重试");
        }

    }

    public function getVirtualAccountByParkcode($virtualAccountParkInfo, $ParkID)
    {

        return $virtualAccountParkInfo->where(["ParkCode" => $ParkID])->find();
    }


}