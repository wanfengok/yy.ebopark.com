<?php
namespace Admin\Controller;
require_once VENDOR_PATH . 'php-sdk-7.1.2/src/Qiniu/functions.php';
use Think\Exception;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Admin\Model\AuthGroupModel;

/**
 * 商品商城
 */
class GoodController extends BaseController
{
    /**
     * merchant
     */
    public function merchant()
    {
        $this->display();
    }

    /**
     * merchantdetail
     */
    public function merchantdetail()
    {
        $this->display();
    }

    /**
     * merchantdetail
     */
    public function distributordetail()
    {
        try {
            $res = D("GoodsInfoBase")->query("select `id`,`name` from goods_category");
            $this->assign("preview", json_encode(C("DIST")));
            $this->assign("category", json_encode($res));

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->display();
    }

    /**
     * validate
     */
    public function validate()
    {
        $this->display();
    }

    /**
     * validatedetail
     */
    public function validatedetail()
    {
        $this->display();
    }

    /**
     * 获取商户列表
     */
    public function getMerchants()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $name = $_GET["name"];
        try {
            $where = "";
            if (!empty($name)) {
                $where .= "name like '%" . $name . "%'";
            }

            //total
            $total = D("GoodsMerchant")->where($where)->count();
            $res = D("GoodsMerchant")->page($pageIndex, $pageSize)->where($where)->order("id desc")->select();
            $data = array();
            $i = 1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["address"] = is_null($value["address"]) ? "" : $value["address"];
                $item["tel"] = is_null($value["tel"]) ? "" : $value["tel"];
                $item["shophours"] = is_null($value["shophours"]) ? "" : $value["shophours"];
                $item["rowtime"] = is_null($value["rowtime"]) ? "" : $value["rowtime"];
                $item["lastopuser"] = is_null($value["lastopuser"]) ? "" : $value["lastopuser"];
                array_push($data, $item);
                $i++;
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
     * 获取商户列表
     */
    public function getDistributors()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $name = urldecode($_GET["name"]);
        $state = $_GET["state"];
        $goodsid = getParam("goodsid");
        $categoryid = getParam("categoryid");
        try {
            $where = "";
            if (!empty($name)) {
                $where .= "name like '%" . $name . "%'";
            }

            //total
            $total = D("GoodsDistributor")->where($where)->count();
            $res = D("GoodsDistributor")->page($pageIndex, $pageSize)->where($where)->order("id desc")->select();
            //过滤掉没有授权的分销商
            $statearray = array();
            $statelist = D("GoodsDistributor")->query("select * from goods_distributor_items " . (empty($goodsid) ? "" : "where GoodsId='" . $goodsid . "'"));
            if ($state) {
                foreach ($statelist as $key => $value) {
                    $statearray[$value["disid"]] = $value["state"];
                }

            }

            $data = array();
            $i = 1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["address"] = is_null($value["address"]) ? "" : $value["address"];
                $item["tel"] = is_null($value["tel"]) ? "" : $value["tel"];
                $item["authcategories"] = is_null($value["authcategories"]) ? "" : $value["authcategories"];
                $item["rowtime"] = is_null($value["rowtime"]) ? "" : $value["rowtime"];
                $item["lastopuser"] = is_null($value["lastopuser"]) ? "" : $value["lastopuser"];
                if (array_key_exists($item["id"], $statearray)) {
                    $item["state"] = $statearray[$item["id"]];
                } else {
                    $item["state"] = "0";
                }
                array_push($data, $item);
                $i++;
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

    public function getDistributtorsFromDialog()
    {
        $pageIndex = getParam("pageIndex") + 1;
//        $pageSize = getParam("pageSize");
        $pageSize =100;
        $state = $_GET["state"];
        $goodsid = getParam("goodsid");
        $categoryid = getParam("categoryid");
        try {
            //total
            $total = D("GoodsDistributor")->count();
            $res = D("GoodsDistributor")->page($pageIndex, $pageSize)->order("id desc")->select();
            //过滤掉没有授权的分销商
            $resTemp = [];
            if (!empty($categoryid)) {
                foreach ($res as $key => $value) {
                    $authcategories = $value["authcategories"];
                    $cateidArr = explode(",", $authcategories);
                    if (in_array($categoryid, $cateidArr)) {
                        $resTemp[] = $value;
                    }
                }
            } else {
                $resTemp = $res;
            }

            $statearray = array();
            $statelist = D("GoodsDistributor")->query("select * from goods_distributor_items " . (empty($goodsid) ? "" : "where GoodsId='" . $goodsid . "'"));
            if ($state) {
                foreach ($statelist as $key => $value) {
                    $statearray[$value["disid"]] = $value["state"];
                }

            }

            $data = array();
            $i = 1;
            foreach ($resTemp as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["address"] = is_null($value["address"]) ? "" : $value["address"];
                $item["tel"] = is_null($value["tel"]) ? "" : $value["tel"];
                $item["authcategories"] = is_null($value["authcategories"]) ? "" : $value["authcategories"];
                $item["rowtime"] = is_null($value["rowtime"]) ? "" : $value["rowtime"];
                $item["lastopuser"] = is_null($value["lastopuser"]) ? "" : $value["lastopuser"];
                if (array_key_exists($item["id"], $statearray)) {
                    $item["state"] = $statearray[$item["id"]];
                } else {
                    $item["state"] = "0";
                }
                array_push($data, $item);
                $i++;
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
     * 删除分销商
     */
    public function deleteDistributors()
    {
        $id = getParam("id");
        try {
            $res = D("GoodsInfoBase")->query("select * from goods_distributor_items where disid='%s'", $id);
            if (count($res) > 0) {
                $data["state"] = -1;
                $data["msg"] = "此分销商下存在商品，不可删除!";
                $this->ajaxReturn($data);
                return;
            }

            $result = D("GoodsDistributor")->delete($id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 查询分销商
     */
    public function listDistributors()
    {
        $id = getParam("id");
        try {
            $map["id"] = $id;
            $result = D("GoodsDistributor")->where("id='%s'", $id)->select();
            $data["state"] = (count($result) >= 0 ? 0 : -1);
            $data["data"] = $result;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 保存分销商
     */
    public function savedistributor()
    {
        $sid = getParam("sid");
        $id = getParam("id");
        $name = getParam("name");
        $tel = getParam("tel");
        $address = getParam("address");
        $op = getParam("op");
        $user = session("user_auth")["username"];
        $authcategories = getParam("authcategories");
        try {
            $item["id"] = $id;
            $item["name"] = $name;
            $item["tel"] = $tel;
            $item["address"] = $address;
            $item["authcategories"] = $authcategories;
            $merchant = D("GoodsDistributor");

            $count = -1;
            if ($op == 1) {
                if (count($merchant->where("id='%s'", $id)->select()) > 0) {
                    $data["state"] = -1;
                    $data["msg"] = "商户ID号已登记";
                    $this->ajaxReturn($data);
                    return;
                }
                $count = $merchant->execute("insert into goods_distributor(id,`name`,address,tel,authcategories,rowtime,lastopuser,lastoptime) value('%s','%s','%s','%s','%s',now(),'%s',now())", $id, $name, $address, $tel, $authcategories, $user);
            } else {
                if (count($merchant->where("id='%s' and id!='%s'", $id, $sid)->select()) > 0) {
                    $data["state"] = -1;
                    $data["msg"] = "商户ID号已登记";
                    $this->ajaxReturn($data);
                    return;
                }
                $count = $merchant->execute("update goods_distributor set id='%s',name='%s',tel='%s',address='%s',authcategories='%s' where id='%s'", $id, $name, $tel, $address, $authcategories, $sid);
            }
            $data["state"] = ($count >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 删除商户信息
     */
    public function deleteMerchant()
    {
        $id = getParam("id");
        try {

            if (count(D("GoodsInfoBase")->where("MerchantId='%s'", $id)->select()) > 0) {
                $data["state"] = -1;
                $data["msg"] = "此商户下存在商品，不可删除!";
                $this->ajaxReturn($data);
                return;
            }

            $result = D("GoodsMerchant")->delete($id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 查询商户信息
     */
    public function listMerchant()
    {
        $id = getParam("id");
        try {
            $map["id"] = $id;
            $result = D("GoodsMerchant")->where("id='%s'", $id)->select();
            $data["state"] = (count($result) >= 0 ? 0 : -1);
            $data["data"] = $result;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 保存商户信息
     */
    public function savemerchant()
    {
        $sid = getParam("sid");
        $id = getParam("id");
        $name = getParam("name");
        $tel = getParam("tel");
        $address = getParam("address");
        $op = getParam("op");
        $user = session("user_auth")["username"];
        try {
            $item["id"] = $id;
            $item["name"] = $name;
            $item["tel"] = $tel;
            $item["address"] = $address;
            $merchant = D("GoodsMerchant");

            $count = -1;
            if ($op == 1) {
                if (count($merchant->where("id='%s'", $id)->select()) > 0) {
                    $data["state"] = -1;
                    $data["msg"] = "商户ID号已登记";
                    $this->ajaxReturn($data);
                    return;
                }
                $count = $merchant->execute("insert into goods_merchant(id,`name`,address,tel,rowtime,lastopuser,lastoptime) value('%s','%s','%s','%s',now(),'%s',now())", $id, $name, $address, $tel, $user);
            } else {
                if (count($merchant->where("id='%s' and id!='%s'", $id, $sid)->select()) > 0) {
                    $data["state"] = -1;
                    $data["msg"] = "商户ID号已登记";
                    $this->ajaxReturn($data);
                    return;
                }
                $count = $merchant->execute("update goods_merchant set id='%s',name='%s',tel='%s',address='%s' where id='%s'", $id, $name, $tel, $address, $sid);
            }
            $data["state"] = ($count >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 获取商户名称列表
     */
    public function getMerchantNames()
    {
        try {
            $res = D("GoodsMerchantUser")->query("select id,`name` from goods_merchant");
            $result["data"] = $res;
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
     * 获取商户列表
     */
    public function getMerchantUsers()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $merchantid = $_GET["merchantid"];
        try {
            $where = "";
            if (!empty($merchantid)) {
                $where = " where merchantid = '" . $merchantid . "'";
            }

            //total
            $total = 0;
            $totalarray = D("GoodsMerchantUser")->query("select count(1) as count from goods_merchant_user inner join goods_merchant on goods_merchant_user.merchantid=goods_merchant.id " . $where);
            $res = D("GoodsMerchantUser")->query("select u.rcvmsgtels,u.id,username,`name`,realname,u.tel,enablercvmsg,u.rowtime from goods_merchant_user u inner join goods_merchant g on u.merchantid=g.id " . $where . " order by u.id desc limit %s,%s", ($pageIndex - 1) * $pageSize, $pageSize);
            $data = array();
            $i = 1;
            foreach ($totalarray as $key => $value) {
                $total = $value["count"];
            }
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["username"] = is_null($value["username"]) ? "" : $value["username"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["realname"] = is_null($value["realname"]) ? "" : $value["realname"];
                $item["tel"] = is_null($value["tel"]) ? "" : $value["tel"];
                $item["enablercvmsg"] = is_null($value["enablercvmsg"]) ? "" : $value["enablercvmsg"];
                $item["rcvmsgtels"] = is_null($value["rcvmsgtels"]) ? "" : $value["rcvmsgtels"];
                $item["rowtime"] = is_null($value["rowtime"]) ? "" : $value["rowtime"];
                array_push($data, $item);
                $i++;
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
     * 删除商户账户信息
     */
    public function deleteMerchantUser()
    {
        $id = getParam("id");
        try {
            $result = D("GoodsMerchantUser")->delete($id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 查询商户账户信息
     */
    public function listMerchantUser()
    {
        $id = getParam("id");
        try {
            $map["id"] = $id;
            $result = D("GoodsMerchantUser")->where("id='%s'", $id)->select();
            $data["state"] = (count($result) >= 0 ? 0 : -1);
            $data["data"] = $result;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 保存商户账户信息
     */
    public function savemerchantuser()
    {
        $sid = getParam("sid");
        $id = getParam("id");
        $merchantid = getParam("merchantid");
        $username = getParam("username");
        $realname = getParam("realname");
        $tel = getParam("tel");
        $enablercvmsg = getParam("enablercvmsg");
        $rcvmsgtels = getParam("rcvmsgtels");
        $op = getParam("op");
        $user = session("user_auth")["username"];
        $pwd = getParam("pwd");
        try {
            $merchantuser = D("GoodsMerchantUser");

            if (count($merchantuser->where("id<>'%s' and username='%s'", $id, $username)->select()) > 0) {
                $data["state"] = -1;
                $data["msg"] = "用户名不能重复";
                $this->ajaxReturn($data);
                return;
            }

            $count = -1;
            if ($op == 1) {
                $count = $merchantuser->execute("insert into goods_merchant_user(merchantid,`username`,`pwd`,realname,tel,enablercvmsg,rcvmsgtels,rowtime,lastopuser,lastoptime) value('%s','%s','%s','%s','%s','%s','%s',now(),'%s',now())", $merchantid, $username, $pwd, $realname, $tel, $enablercvmsg, $rcvmsgtels, $user);
            } else {
                $count = $merchantuser->execute("update goods_merchant_user set merchantid='%s',username='%s',pwd='%s',realname='%s',tel='%s',enablercvmsg='%s',rcvmsgtels='%s',lastopuser='%s',lastoptime=now() where id='%s'", $merchantid, $username, $pwd, $realname, $tel, $enablercvmsg, $rcvmsgtels, $user, $sid);
            }
            $data["state"] = ($count >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * sale
     */
    public function sale()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_merchant");
        $category = array();
        $merchant = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($merchantarray as $key => $value) {
            $merchant[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->assign("merchant", json_encode($merchant));
        $this->assign("preview", json_encode(C("GOODPREVIEW")));
        $this->display();
    }

    /**
     * sale
     */
    public function forsale()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_merchant");
        $category = array();
        $merchant = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($merchantarray as $key => $value) {
            $merchant[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->assign("merchant", json_encode($merchant));
        $this->assign("preview", json_encode(C("GOODPREVIEW")));
        $this->display();
    }

    public function nouse()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_merchant");
        $category = array();
        $merchant = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($merchantarray as $key => $value) {
            $merchant[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->assign("merchant", json_encode($merchant));
        $this->display();
    }

    public function bindcategory()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $category = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $result["data"] = $category;
        $result["state"] = 0;
        $this->ajaxReturn($result);
    }

    /**
     * addgood
     */
    public function addgood()
    {
        $this->initCateGoryAndMerchant();
        $this->display();
    }

    private function initCateGoryAndMerchant()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_merchant");
        $sms = D("GoodsMerchantUser")->query("select `name`,`id` from msg_smstemp");
        $category = array();
        $merchant = array();
        $array = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($merchantarray as $key => $value) {
            $merchant[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($sms as $key => $value) {
            $array[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->assign("merchant", json_encode($merchant));
        $this->assign("sms", json_encode($array));

    }

    /**
     * 获取商品列表
     */
    public function getSales()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $goodid = getParam("goodid");
        $name = urldecode($_GET["name"]);
        $categoryid = urldecode($_GET["categoryid"]);
        $merchantid = urldecode($_GET["merchantid"]);
        $starttime = urldecode($_GET["starttime"]);
        $endtime = urldecode($_GET["endtime"]);
        $saling = urldecode($_GET["saling"]);
        $shelfstarttime = urldecode($_GET["shelfstarttime"]);
        $shelfendtime = urldecode($_GET["shelfendtime"]);
        $name = urldecode($_GET["name"]); //名字
        $categoryid = urldecode($_GET["categoryid"]); //商品频道
        $merchantid = urldecode($_GET["merchantid"]); //供应商名称
        $starttime = urldecode($_GET["starttime"]); //开始时间
        $endtime = urldecode($_GET["endtime"]); //结束时间
        $saling = urldecode($_GET["saling"]);  //销售状态
        $shelfstarttime = urldecode($_GET["shelfstarttime"]); //开始销售时间
        $shelfendtime = urldecode($_GET["shelfendtime"]);  //销售结束时间
        try {
            $where = "";
            if ($saling == "1") {
                $where = " where StartTime>now() and EndTime>now()";//待售
            } else if ($saling == "0") {
                $where = " where EndTime>now() and startTime<=now() ";//上架
            } else {
                $where = " where EndTime<now() and startTime<now()";//失效
            }

            if (!empty($merchantid)) {
                $where .= " and merchantid = '" . $merchantid . "'";
            }
            if (!empty($categoryid)) {
                $where .= " and categoryid = '" . $categoryid . "'";
            }
            if (!empty($name)) {
                $where .= " and `name` like '%" . $name . "%'";
            }
            if (!empty($starttime) && !empty($endtime)) {
                $where .= " and starttime between '" . $starttime . "' and '" . $endtime . "'";
            }
            if (!empty($shelfstarttime) && !empty($shelfendtime)) {
                $where .= " and endtime between '" . $shelfstarttime . "' and '" . $shelfendtime . "'";
            }
            if (!empty($goodid)) {
                $where .= " and Id='$goodid'";
            }
            $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
            $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_merchant");
            $category = array();
            $merchant = array();
            foreach ($categoryarray as $key => $value) {
                $category[$value["id"]] = $value["name"];
            }
            foreach ($merchantarray as $key => $value) {
                $merchant[$value["id"]] = $value["name"];
            }

            //2017/04/06 添加商品类型
            $typesarray = D("GoodsMerchantUser")->query("select i.id as itemid, i.GoodsId,i.costprice,i.name,sum(s.amount) as amount ,goods_info_control.Data from goods_info_item i
                                                      left join goods_stat_sale s on i.id=s.itemid
                                                      left join goods_info_control on goods_info_control.GoodsId=i.GoodsId and goods_info_control.ControlType='10102'
                                                      group by i.costprice,i.name,i.GoodsId order by i.GoodsId desc");
            $types = array();
            foreach ($typesarray as $key => $value) {
                $goodsid = $value["goodsid"];
                $temp = array();
                if (array_key_exists($goodsid, $types)) {
                    $temp = $types[$goodsid];
                }
                $temp[] = ["itemid" => $value["itemid"], "name" => $value["name"], "sale" => empty($value["amount"]) ? 0 : $value["amount"], "costprice" => $value["costprice"], "data" => $value["data"]];
                $types[$goodsid] = $temp;
            }

            //total
            $total = 0;
            $totalarray = D("GoodsMerchantUser")->query("select count(1) as count from goods_info_base  " . $where);
            $res = D("GoodsMerchantUser")->query("select *, (select ifnull(sum(amount),0) from goods_stat_sale where GoodsId=goods_info_base.id) as amount from goods_info_base " . $where . " order by id desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize);
            //获取商品id
            $ids = [];
            foreach ($res as $val) {
//                $ids.="'".$val["id"]."',";
                array_push($ids, "'" . $val["id"] . "'");
            }

            $sql = "select goods_stat_sale.* from goods_stat_sale  left join goods_info_base on `goods_stat_sale`.`GoodsId`=goods_info_base.`Id` where goods_stat_sale.`GoodsId`  in  (" . implode(",", $ids) . ")";
            $sellData = D("GoodsMerchantUser")->query($sql);
            $data = array();
            $i = 1;
            foreach ($totalarray as $key => $value) {
                $total = $value["count"];
            }
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["merchantname"] = "";
                if (array_key_exists($value["merchantid"], $merchant)) {
                    $item["merchantname"] = $merchant[$value["merchantid"]];
                }
                $item["categoryname"] = "";
                if (array_key_exists($value["categoryid"], $category)) {
                    $item["categoryname"] = $category[$value["categoryid"]];
                }
                $item["total"] = is_null($value["total"]) ? "" : $value["total"];
                $item["amount"] = is_null($value["amount"]) ? "" : $value["amount"];
                $item["lastopuser"] = is_null($value["lastopuser"]) ? "" : $value["lastopuser"];
                $item["starttime"] = is_null($value["starttime"]) ? "" : $value["starttime"];
                $item["endtime"] = is_null($value["endtime"]) ? "" : $value["endtime"];


                if (array_key_exists($item["id"], $types)) {
                    $temp = $types[$item["id"]];
                    $item["data"] = $temp;
                } else {
                    $item["data"] = array();
                }
                array_push($data, $item);
                $i++;
            }
            $result["total"] = $total;
            $result["data"] = $data;
            $result["goods_sell_data"] = empty($sellData) ? [] : $sellData;
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
     * 商品下架
     */
    public function shelf()
    {
        $id = urldecode(getParam("id"));
        try {
            $result = D("GoodsMerchantUser")->execute("update goods_info_base set EndTime=date_add(now(), interval -1 second) where id='%s'", $id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    public function waittingsale()
    {
        $id = urldecode(getParam("id"));
        try {
            $result = D("GoodsMerchantUser")->execute("update goods_info_base set starttime='2099-01-01',EndTime='2099-01-01' where id='%s'", $id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 商品上架
     */
    public function unshelf()
    {
        $id = urldecode(getParam("id"));
        try {

            $res = D("GoodsMerchantUser")->query("select 1 as count from goods_info_base where id='%' endtime<now() or endtime<starttime", $id);
            $total = 0;
            foreach ($res as $key => $value) {
                $total = $value["count"];
            }
            if ($total == 1) {
                $data["state"] = -2;
                $data["msg"] = "商品下架时间小于上架时间！";
                $this->ajaxReturn($data);
                return;
            }

            $result = D("GoodsMerchantUser")->execute("update goods_info_base set starttime=now() where id='%s'", $id);
            $data["state"] = ($result >= 0 ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }


    /**
     * 商品复制到待售
     */
    public function copytounshelf()
    {
        $id = urldecode(getParam("id"));
        try {
            $executeresult = true;
            $obj = D("GoodsMerchantUser");
            $suffix = time();
            $goodid = "copy" . $suffix;
            //销售状态 分享链接 消息定义 商品型号 商品图片 商品控件属性值 基本信息
            $array = array(
                "insert into goods_info_item(GoodsId,Name,Total,CostPrice) select '" . $goodid . "',Name,Total,CostPrice from goods_info_item where GoodsId='" . $id . "'",
                "insert into goods_stat_sale(ItemId,Date,GoodsId,Amount) select LAST_INSERT_ID(),Date,'" . $goodid . "',0 from goods_stat_sale where GoodsId='" . $id . "'",
                "insert into goods_info_share(GoodsId,`Title`,`Content`,Img) select '" . $goodid . "',`Title`,`Content`,Img from goods_info_share where GoodsId='" . $id . "'",
                "insert into goods_info_msgdef(GoodsId,MsgTailOfMerchant,MsgTailOfUser) select '" . $goodid . "',MsgTailOfMerchant,MsgTailOfUser from goods_info_msgdef where GoodsId='" . $id . "'",
                "insert into goods_info_img(GoodsId,`Type`,`Order`,`Url`) select '" . $goodid . "',`Type`,`Order`,`Url` from goods_info_img where GoodsId='" . $id . "'",
                "insert into goods_info_control(GoodsId,`ControlType`,`Data`,`Order`) select '" . $goodid . "',`ControlType`,`Data`,`Order` from goods_info_control where GoodsId='" . $id . "'",
                "insert into goods_info_base(`Id`,`MerchantId`,`CategoryId`,`LogicType`,`Name`,`Title`,`ExpressType`,`InitAmount`,`OriginalPrice`,`SalePrice`,`ShopHours`,`Tel`,`Lat`,`lng`,`Address`,`Tips`,`RowTime`,`LastOpUser`,`LastOpTime`,`OrderDescTemp`,`StartTime`,`EndTime`,`MemberDiscountType`,`MemberDiscountVal`,`ExistsDetail`,`total`) select '" . $goodid . "',`MerchantId`,`CategoryId`,`LogicType`,CONCAT_WS('','copy',`Name`),`Title`,`ExpressType`,`InitAmount`,`OriginalPrice`,`SalePrice`,`ShopHours`,`Tel`,`Lat`,`lng`,`Address`,`Tips`,now(),`LastOpUser`,now(),`OrderDescTemp`,'2099-1-1','2099-1-1',`MemberDiscountType`,`MemberDiscountVal`,`ExistsDetail`,`total` from goods_info_base where id='" . $id . "'"
            );

            $obj->startTrans();
            foreach ($array as $key => $value) {
                $r = $obj->execute($value);
                if ($r != 0 && !$r) {
                    $obj->rollback();
                    $executeresult = false;
                    break;
                }
            }
            if ($executeresult) {
                $obj->commit();
            }
            //更新e泊特价itemid
            $updateSuccess = $this->updatePricesControlAfterCopy($goodid);
            $data["state"] = ($updateSuccess && $executeresult ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    public function updatePricesControlAfterCopy($goodid = "")
    {
        $db = D("GoodsMerchantUser");
        $sql = "select Id from goods_info_item where GoodsId='$goodid'";
        $res = $db->query($sql);
        if (!$res) {
            return false;
        }
        $control = $db->query("select Data from goods_info_control where GoodsId='$goodid' and ControlType=10102");
        if (!$control) {
            return false;
        }
        $controlObj = json_decode($control[0]["data"]);
        if (count($controlObj->Items) != count($res)) {
            return false;
        }
        foreach ($res as $key => $val) {
            $controlObj->Items[$key]->ItemId = $val["id"];
        }
        $json = json_encode($controlObj);
        $res = $db->execute("update goods_info_control set Data='%s' where GoodsId='%s' and ControlType=10102", $json, $goodid);
        return !!$res;


    }

    /**
     * 商品删除
     */
    public function del()
    {
        $id = urldecode(getParam("id"));
        try {
            $executeresult = true;
            $obj = D("GoodsMerchantUser");
            $obj->startTrans();

            //销售状态 分享链接 消息定义 商品型号 商品图片 商品控件属性值 基本信息
            $array = array("goods_stat_sale", "goods_info_share", "goods_info_msgdef", "goods_info_item", "goods_info_img", "goods_info_control", "goods_info_base");
            foreach ($array as $key => $value) {
                $r = false;
                if ($value == "goods_info_base") {
                    $r = $obj->execute("delete from %s where id='%s'", $value, $id);
                } else {
                    $r = $obj->execute("delete from %s where GoodsId='%s'", $value, $id);
                }

                if (!$r && $r !== 0) {
                    $obj->rollback();
                    $executeresult = false;
                    break;
                }
            }
            if ($executeresult) {
                $obj->commit();
            }

            $data["state"] = ($executeresult ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /**
     * 类目管理
     */
    public function savecategory()
    {
        $id = urldecode(getParam("id"));
        $name = urldecode(getParam("name"));
        try {
            $res = D("GoodsMerchantUser")->query("select * from goods_category where `id`='%s' or `name`='%s'", $id, $name);
            if ($res) {
                $_id = "";
                $_name = "";
                foreach ($res as $key => $value) {
                    $_id = $value["id"];
                    $_name = $value["name"];
                }
                if ($_id == $id) {
                    $data["state"] = -1;
                    $data["msg"] = "频道ID重复！";
                    $this->ajaxReturn($data);
                    return;
                }
                if ($_name == $name) {
                    $data["state"] = -1;
                    $data["msg"] = "频道名称重复！";
                    $this->ajaxReturn($data);
                    return;
                }
            }

            $user = session("user_auth")["username"];
            $r = D("GoodsMerchantUser")->execute("insert into goods_category(`id`,`name`,RowTime,LastOpUser,LastOpTime) values('%s','%s',now(),'%s',now())", $id, $name, $user);
            $data["state"] = ($r ? 0 : -1);
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }


    /**
     * 添加商品
     */
    public function savegood()
    {
        $id = urldecode(getParam("id"));
        $merchantid = urldecode(getParam("merchantid"));
        $categoryid = urldecode(getParam("categoryid"));
        $logictype = urldecode(getParam("logictype"));
        $name = urldecode(getParam("name"));
        $title = urldecode(getParam("title"));
        $expresstype = urldecode(getParam("expresstype"));
        $initamount = urldecode(getParam("initamount"));
        $originalprice = urldecode(getParam("originalprice"));
        $saleprice = urldecode(getParam("saleprice"));
        $shophours = urldecode(getParam("shophours"));
        $tel = urldecode(getParam("tel"));
        $lat = urldecode(getParam("lat"));
        $lng = urldecode(getParam("lng"));
        $address = urldecode(getParam("address"));
        $tips = urldecode(getParam("tips"));
        $orderdesctemp = urldecode(getParam("orderdesctemp"));
        $starttime = urldecode(getParam("starttime"));
        if (!$starttime) {
            $starttime = "2099-1-1";
        }
        $endtime = urldecode(getParam("endtime"));
        if (!$endtime) {
            $endtime = "2099-1-1";
        }
        $memberdiscounttype = urldecode(getParam("memberdiscounttype"));
        $memberdiscountval = $memberdiscounttype == "0" ? 0 : urldecode(getParam("memberdiscountval"));
        $existsdetail = urldecode(getParam("existsdetail"));
        $lb = urldecode(getParam("lb"));
        $xj = urldecode(getParam("xj"));
        $spxq = urldecode(getParam("spxq"));
        $cpts = urldecode(getParam("cpts"));
        $subgoodtypes = urldecode(getParam("subgoodtypes"));
        try {
            $res = D("GoodsMerchantUser")->query("select * from goods_info_base where `id`='%s' or `name`='%s'", $id, $name);
            if ($res) {
                foreach ($res as $key => $value) {
                    $_id = $value["id"];
                    $_name = $value["name"];
                    if ($_id == $id) {
                        $data["state"] = -1;
                        $data["msg"] = "商品ID号重复！";
                        $this->ajaxReturn($data);
                        return;
                    }
                    if ($_name == $name) {
                        $data["state"] = -1;
                        $data["msg"] = "商品名称重复！";
                        $this->ajaxReturn($data);
                        return;
                    }
                }

            }
            $user = session("user_auth")["username"];
            $r = D("GoodsMerchantUser")->execute("insert into goods_info_base(`id`,MerchantId,CategoryId,LogicType,`name`,`title`,ExpressType,InitAmount,OriginalPrice,SalePrice,ShopHours,Tel,Lat,lng,Address,Tips,RowTime,LastOpUser,LastOpTime,OrderDescTemp,StartTime,EndTime,MemberDiscountType,MemberDiscountVal,ExistsDetail,Total) values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',now(),'%s',now(),'%s','%s','%s','%s','%s','%s',0)",
                $id, $merchantid, $categoryid, $logictype, $name, $title, $expresstype, $initamount, $originalprice, $saleprice, $shophours, $tel, $lat, $lng, $address, $tips, $user, $orderdesctemp, $starttime, $endtime, $memberdiscounttype, $memberdiscountval, $existsdetail);
            if ($r) {
                //保存图片
                D("GoodsMerchantUser")->execute("insert into goods_info_img(`GoodsId`,`Type`,`Order`,`Url`) values('%s','%s','%s','%s')", $id, 1, 1, $lb);
                D("GoodsMerchantUser")->execute("insert into goods_info_img(`GoodsId`,`Type`,`Order`,`Url`) values('%s','%s','%s','%s')", $id, 2, 2, $xj);
                D("GoodsMerchantUser")->execute("insert into goods_info_img(`GoodsId`,`Type`,`Order`,`Url`) values('%s','%s','%s','%s')", $id, 3, 3, $spxq);
                D("GoodsMerchantUser")->execute("insert into goods_info_img(`GoodsId`,`Type`,`Order`,`Url`) values('%s','%s','%s','%s')", $id, 4, 4, $cpts);
                //保存商品型号
                $GoodsTypeArr = json_decode($subgoodtypes);
                for ($index = 0; $index < count($GoodsTypeArr); $index++) {
                    $item = $GoodsTypeArr[$index];
                    D("GoodsMerchantUser")->execute("insert into goods_info_item(`GoodsId`,`Name`,`Total`,`CostPrice`) values('%s','%s','%s','%s')", $id, $item->typename, 0, $item->realPrice);
                }


            }
            $data["state"] = ($r ? 0 : -1);
            $data["id"] = $r;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    public function updateBasenfo()
    {


        $id = urldecode(getParam("id"));
        $merchantid = urldecode(getParam("merchantid"));
        $categoryid = urldecode(getParam("categoryid"));
        $logictype = urldecode(getParam("logictype"));
        $name = urldecode(getParam("name"));
        $title = urldecode(getParam("title"));
        $expresstype = urldecode(getParam("expresstype"));
        $initamount = urldecode(getParam("initamount"));
        $originalprice = urldecode(getParam("originalprice"));
        $saleprice = urldecode(getParam("saleprice"));
        $shophours = urldecode(getParam("shophours"));
        $tel = urldecode(getParam("tel"));
        $lat = urldecode(getParam("lat"));
        $lng = urldecode(getParam("lng"));
        $address = urldecode(getParam("address"));
        $tips = urldecode(getParam("tips"));
        $orderdesctemp = urldecode(getParam("orderdesctemp"));
        //$starttime = urldecode(getParam("starttime"));
        //$endtime = urldecode(getParam("endtime"));
        $memberdiscounttype = urldecode(getParam("memberdiscounttype"));
        $memberdiscountval = $memberdiscounttype == "0" ? 0 : urldecode(getParam("memberdiscountval"));
        $existsdetail = urldecode(getParam("existsdetail"));
        $lb = urldecode(getParam("lb"));
        $xj = urldecode(getParam("xj"));
        $spxq = urldecode(getParam("spxq"));
        $cpts = urldecode(getParam("cpts"));
        $subgoodtypes = urldecode(getParam("subgoodtypes"));
        try {
            $res = D("GoodsMerchantUser")->query("select * from goods_info_base where `id`='%s'", $id);
            if (!$res) {
                $data["state"] = -1;
                $data["msg"] = "商品不存在！";
                $this->ajaxReturn($data);
                return;

            }
            $user = session("user_auth")["username"];

            $r = D("GoodsMerchantUser")->execute("update goods_info_base set MerchantId='%s',CategoryId='%s',LogicType='%s',`name`='%s',title='%s',ExpressType='%s',InitAmount='%s',OriginalPrice='%s',SalePrice='%s',ShopHours='%s',Tel='%s',Lat='%s',lng='%s',Address='%s',Tips='%s',RowTime=now(),LastOpUser='%s',LastOpTime=now(),OrderDescTemp='%s',MemberDiscountType='%s',MemberDiscountVal='%s',ExistsDetail='%s' where id='%s'", $merchantid, $categoryid, $logictype, $name, $title, $expresstype, $initamount, $originalprice, $saleprice, $shophours, $tel, $lat, $lng, $address, $tips, $user, $orderdesctemp, $memberdiscounttype, $memberdiscountval, $existsdetail, $id);

            //保存图片
            D("GoodsMerchantUser")->execute("update goods_info_img set Url='%s' where GoodsId='%s' and Type='%s'", $lb, $id, 1);
            D("GoodsMerchantUser")->execute("update goods_info_img set Url='%s' where GoodsId='%s' and Type='%s'", $xj, $id, 2);
            D("GoodsMerchantUser")->execute("update goods_info_img set Url='%s' where GoodsId='%s' and Type='%s'", $spxq, $id, 3);
            D("GoodsMerchantUser")->execute("update goods_info_img set Url='%s' where GoodsId='%s' and Type='%s'", $cpts, $id, 4);
            //保存商品型号
            //清空商品型号
            D("GoodsMerchantUser")->execute("delete from goods_info_item where GoodsId='%s'", $id);
            $GoodsTypeArr = json_decode($subgoodtypes);
            for ($index = 0; $index < count($GoodsTypeArr); $index++) {
                $item = $GoodsTypeArr[$index];
                if (is_numeric($item->itemId)) {
                    D("GoodsMerchantUser")->execute("insert into goods_info_item(`Id`,`GoodsId`,`Name`,`Total`,`CostPrice`) values('%s','%s','%s','%s','%s')", $item->itemId, $id, $item->typename, 0, $item->realPrice);
                } else {
                    D("GoodsMerchantUser")->execute("insert into goods_info_item(`GoodsId`,`Name`,`Total`,`CostPrice`) values('%s','%s','%s','%s')", $id, $item->typename, 0, $item->realPrice);
                }
            }
            $data["state"] = 0;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }


    }

    /**
     * 修改商品页面
     */
    public function update()
    {

        $id = getParam("id");
        try {
            $res = D("GoodsInfoBase")->where("id='$id'")->find();
            if (empty($res)) {
                $this->error("该商品,不存在");
            }


            $this->assign("data", json_encode($res));
            $this->initCateGoryAndMerchant();
            $this->display("updategood");

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


    }


    /**
     * ajax,修改商品
     */
    public function updategood()
    {
        try {
            $this->updateOrderDescTemp();
            $this->updateMsgAndPublishTime();
            $this->updateShareContent();
            $this->updateControls();
            //分享内容
            $data["state"] = 0;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    private function updateOrderDescTemp()
    {
        $id = urldecode(getParam("id"));
        $descTemp = getParam("orderdesctemp");
        $r = D("GoodsMerchantUser")->execute("update goods_info_base set OrderDescTemp='%s' where id='%s'", $descTemp, $id);

    }

    private function updateControls()
    {
        $id = urldecode(getParam("id"));
        $controls = json_decode(getParam("control"));
        if (!is_array($controls)) {
            return;
        }
        //清空控件
        D("GoodsMerchantUser")->execute("delete from goods_info_control where GoodsId='%s'", $id);
        for ($index = 0; $index < count($controls); $index++) {
            $item = $controls[$index];
            $ControlType = $item->ControlType;
            $Data = json_encode($item);
            $Order = $item->Order;
            D("GoodsMerchantUser")->execute("insert into goods_info_control(`GoodsId`,`ControlType`,`Data`,`Order`) values('%s','%s','%s','%s')", $id, $ControlType, $Data, $Order);
            $this->handlerControl($id, $item);
        }


    }

    private function handlerControl($id, $item)
    {
        if ($item->ControlType == "10102") {
            //计算总库存
            $total = 0;
            foreach ($item->Items as $val) {
                if (count($val->Prices) == 0) {
                    $total += $val->DefaultTotal;
                    continue;
                }
                $tempTotal = $this->getTotal($val);
                $total += $tempTotal;
            }
            D("GoodsMerchantUser")->execute("update goods_info_base set Total=$total where Id='$id'");
        }

    }

    private function getTotal($val)
    {
        $total = 0;
        foreach ($val->Prices as $price) {
            $total += $price->Total;
        }
        return $total;
    }

    private function updateShareContent()
    {
        $id = urldecode(getParam("id"));
        $shareimg = getParam("shareimg");
        $sharetitle = getParam("sharetitle");
        $sharecontent = getParam("sharecontent");
        //清空分享内容
        D("GoodsMerchantUser")->execute("delete from goods_info_share where GoodsId='%s'", $id);
        D("GoodsMerchantUser")->execute("insert into goods_info_share(`GoodsId`,`Title`,`Content`,`Img`) values('%s','%s','%s','%s')", $id, $sharetitle, $sharecontent, $shareimg);

    }

    private function updateMsgAndPublishTime()
    {
        $id = urldecode(getParam("id"));
        $starttime = urldecode(getParam("starttime"));
        $endtime = urldecode(getParam("endtime"));
        $msgtailofmerchant = urldecode(getParam("msgtailofmerchant"));
        $msgtailofuser = urldecode(getParam("msgtailofuser"));
        $smstempid = urldecode(getParam("smstempid"));
        $user = session("user_auth")["username"];
        $r = D("GoodsMerchantUser")->execute("update goods_info_base set starttime='%s',endtime='%s',LastOpUser='%s',LastOpTime=now() where id='%s'", $starttime, $endtime, $user, $id);

        $msgarray = D("GoodsMerchantUser")->query("select count(1) as `count` from goods_info_msgdef where goodsid='%s'", $id);
        foreach ($msgarray as $key => $value) {
            if ($value["count"] == 0) {
                D("GoodsMerchantUser")->execute("insert into goods_info_msgdef(GoodsId,MsgTailOfMerchant,MsgTailOfUser) values('%s','%s','%s') ", $id, $msgtailofmerchant, $msgtailofuser);
            } else {
                D("GoodsMerchantUser")->execute("update goods_info_msgdef set MsgTailOfMerchant='%s',MsgTailOfUser='%s',SmsTempId='%s' where goodsid='%s'", $msgtailofmerchant, $msgtailofuser, $smstempid, $id);
            }
            break;
        }
    }

    public function listgood()
    {
        $id = urldecode(getParam("id"));
        try {
            $r = D("GoodsMerchantUser")->query("select * from goods_info_base where id='%s'", $id);
            $msg = D("GoodsMerchantUser")->query("select * from goods_info_msgdef where GoodsId='%s'", $id);
            $itemList = D("GoodsInfoItem")->where("GoodsId='$id'")->select();
            $imgList = D("GoodsInfoImg")->where("GoodsId='$id'")->select();
            $share = D("GoodsInfoShare")->where("GoodsId='$id'")->find();
            $controls = D("GoodsMerchantUser")->query("select * from goods_info_control where GoodsId='%s'", $id);
            $data["state"] = !$r ? -1 : 0;
            $data["data"] = $r;
            $data["msg"] = $msg;
            $data["share"] = empty($share) ? "" : $share;
            $data["item_list"] = empty($itemList) ? [] : $itemList;
            $data["img_list"] = empty($imgList) ? [] : $imgList;
            $data["controls"] = empty($controls) ? [] : $controls;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }


    public function mark()
    {
        $this->display();
    }

    public function uploadimg()
    {
        $goodid = urldecode(getParam("goodid"));
        $type = urldecode(getParam("type"));
        $img = urldecode(getParam("img"));
        if ($type == "spxq") {

        }

        $data["state"] = -1;
        $this->ajaxReturn($data);
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


    public function getsms()
    {
        $sms = D("GoodsMerchantUser")->query("select `name``,`id` from msg_smstemp");
        $array = array();
        foreach ($sms as $key => $value) {
            $array[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->ajaxReturn(json_decode($array));
    }

    /**
     * categorybanner
     */
    public function categorybanner()
    {
        try {
            $res = D("GoodsInfoBase")->query("select `id`,`name` from goods_category");
            $this->assign("category", json_encode($res));

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->display();
    }

    /**
     * categorybanner
     */
    public function getBanners()
    {
        $id = urldecode(getParam("id"));
        $res = D("GoodsInfoBase")->query("select * from goods_category_banner where categoryid='%s' and state=1", $id);
        $data["state"] = count($res) >= 0 ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }

    public function getShelfBanners()
    {
        $id = urldecode(getParam("id"));
        $res = D("GoodsInfoBase")->query("select * from goods_category_banner where categoryid='%s' and state=0", $id);
        $data["state"] = count($res) >= 0 ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }

    /**
     * 复制banner
     */
    public function copyBanners()
    {
        $id = urldecode(getParam("id"));
        $user = session("user_auth")["uid"];
        $res = D("GoodsInfoBase")->execute("insert into goods_category_banner(CategoryId,Img,`Desc`,TimeOutType,TimeOutDesc,SendType,SendDesc,TransferType,Transfer,OperateTime,Operater,state,`order`,`ClientType`) select CategoryId,Img,`Desc`,TimeOutType,TimeOutDesc,SendType,SendDesc,TransferType,Transfer,now(),'%s',0,`order`,`ClientType` from goods_category_banner where id='%s'", $user, $id);
        $data["state"] = $res ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }

    /**
     * 发布
     */
    public function publish()
    {
        $id = urldecode(getParam("id"));
        $res = D("GoodsInfoBase")->execute("update goods_category_banner set state=3 where CategoryId='%s' and state=0", $id);
        $res = D("GoodsInfoBase")->execute("update goods_category_banner set state=0 where CategoryId='%s' and state=1", $id);
        $res = D("GoodsInfoBase")->execute("update goods_category_banner set state=1 where CategoryId='%s' and state=3", $id);
        $data["state"] = $res >= 0 ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }

    /**
     * 预览发布
     */
    public function preview()
    {
        $id = urldecode(getParam("id"));
        $mobiles = urldecode(getParam("mobiles"));
        $res = -1;
        if (empty($mobiles)) {
            $res = D("GoodsInfoBase")->execute("update goods_category_banner set SendType=0,SendDesc='' where CategoryId='%s' and state=0", $id);
        } else {
            $res = D("GoodsInfoBase")->execute("update goods_category_banner set SendType=1,SendDesc='%s' where CategoryId='%s' and state=0", $mobiles, $id);
        }
        $data["state"] = $res >= 0 ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }

    /**
     * 保存banner
     */
    public function savecategorybanner()
    {
        $id = urldecode(getParam("id"));
        $ar = json_decode(getParam("content"));
        $user = session("user_auth")["uid"];
        foreach ($ar as $key => $value) {
            if (empty($value->id)) {
                D("GoodsInfoBase")->execute("insert into goods_category_banner(CategoryId,Img,`Desc`,TimeOutType,TimeOutDesc,SendType,SendDesc,TransferType,Transfer,OperateTime,Operater,state,`order`,`ClientType`) values('%s','%s','%s',0,'2099-1-1',0,'',0,'%s',now(),'%s','0','0','0')  ", $id, $value->img, $value->desc, $value->transfer, $user);
            } else {
                D("GoodsInfoBase")->execute("update goods_category_banner set Img='%s',`Desc`='%s',Transfer='%s',OperateTime=now(),Operater='%s' where id='%s'", $value->img, $value->desc, $value->transfer, $user, $value->id);
            }
        }
        $data["state"] = 0;
        $this->ajaxReturn($data);
    }

    /**
     * 删除banner
     */
    public function delcategorybanner()
    {
        $id = urldecode(getParam("id"));
        $res = D("GoodsInfoBase")->execute("delete from goods_category_banner where id='%s' and state=0", $id);
        $data["state"] = $res > 0 ? 0 : -1;
        $data["data"] = $res;
        $this->ajaxReturn($data);
    }


    /**
     * sale
     */
    public function resource()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $category = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->display();
    }

    /**
     * 获取商品列表
     */
    public function getResource()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $name = urldecode($_GET["name"]);
        $categoryid = urldecode($_GET["categoryid"]);
        try {
            $where = " where starttime<=now() and endtime>now()";
            if (!empty($categoryid)) {
                $where .= " and categoryid = '" . $categoryid . "'";
            }
            if (!empty($name)) {
                $where .= " and `name` like '%" . $name . "%'";
            }

            $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
            $category = array();
            foreach ($categoryarray as $key => $value) {
                $category[$value["id"]] = $value["name"];
            }

            //total
            $total = 0;
            $totalarray = D("GoodsMerchantUser")->query("select count(*) as count from goods_info_base  " . $where);
            $res = D("GoodsMerchantUser")->query("select *  from goods_info_base " . $where . " order by id desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize);
            $data = array();
            $i = 1;
            foreach ($totalarray as $key => $value) {
                $total = $value["count"];
            }
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["categoryname"] = "";
                if (array_key_exists($value["categoryid"], $category)) {
                    $item["categoryname"] = $category[$value["categoryid"]];
                }
                $item["categoryid"] = $value["categoryid"];
                $item["starttime"] = is_null($value["starttime"]) ? "" : $value["starttime"];
                $item["endtime"] = is_null($value["endtime"]) ? "" : $value["endtime"];
                array_push($data, $item);
                $i++;
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
     * 查询商户信息
     */
    public function getTypes()
    {
        $goodsid = getParam("goodid");
        $disid = getParam("disid");
        $categoryid = getParam("categoryid");
        try {
            $result = D("GoodsMerchant")->query("select id,`Name`,CostPrice from goods_info_item where GoodsId='%s'", $goodsid);
            $pricearray = D("GoodsMerchant")->query("select itemid,Discount from goods_distributor_items_price  where GoodsId='%s' and Disid='%s'", $goodsid, $disid);
            $price = array();
            $final = array();
            foreach ($pricearray as $key => $value) {
                $price[$value["itemid"]] = $value["discount"];
            }
            foreach ($result as $key => $value) {
                $item = array();
                $item["id"] = $value["id"];
                $item["name"] = $value["name"];
                $item["costprice"] = $value["costprice"];
                $item["discount"] = "";
                if (array_key_exists($value["id"], $price)) {
                    $item["discount"] = $price[$value["id"]];
                }
                $final[] = $item;
            }
            $data["state"] = 0;
            $data["data"] = $final;
            $this->ajaxReturn($data);
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    public function granttype()
    {
        $content = json_decode(getParam("content"));
        foreach ($content as $key => $value) {
            $r = D("GoodsMerchant")->execute("update goods_distributor_items_price set discount='%s',RowTime=now() where ItemId='%s' and Disid='%s'", $value->discount, $value->itemid, $value->disid);
            if (!$r) {
                $r = D("GoodsMerchant")->execute("insert into goods_distributor_items_price(Disid,GoodsId,ItemId,Discount,RowTime) values('%s','%s','%s','%s',now())", $value->disid, $value->goodsid, $value->itemid, $value->discount);
            }

            $r = D("GoodsMerchant")->execute("update goods_distributor_items set State='1'  where GoodsId='%s' and Disid='%s'", $value->goodsid, $value->disid);
            if (!$r) {
                $r = D("GoodsMerchant")->execute("insert into goods_distributor_items(GoodsId,Disid,State,RowTime) values('%s','%s','1',now())", $value->goodsid, $value->disid);
            }
        }
        $data["state"] = 0;
        $this->ajaxReturn($data);
    }

    public function  cancelgrant()
    {
        $goodsid = getParam("goodsid");
        $disid = getParam("disid");
        $r = D("GoodsMerchant")->execute("update goods_distributor_items set State='0'  where GoodsId='%s' and Disid='%s'", $goodsid, $disid);
        $data["state"] = 0;
        $this->ajaxReturn($data);
    }


    /**
     * sale
     */
    public function disgood()
    {
        $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
        $merchantarray = D("GoodsMerchantUser")->query("select id,name from goods_distributor");
        $category = array();
        $merchant = array();
        foreach ($categoryarray as $key => $value) {
            $category[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        foreach ($merchantarray as $key => $value) {
            $merchant[] = array("name" => $value["name"], "id" => $value["id"]);
        }
        $this->assign("category", json_encode($category));
        $this->assign("merchant", json_encode($merchant));
        $this->assign("preview", json_encode(C("GOODDIS")));
        $this->display();
    }

    public function getDisgood()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $name = urldecode($_GET["name"]);
        $categoryid = urldecode($_GET["categoryid"]);
        $merchantid = urldecode($_GET["merchantid"]);
        $state = urldecode($_GET["state"]);
        try {
            $where = " where 1=1";
            if ($state == "2") {
                $where = " where (startTime>now() or EndTime<now()) and i.state=1";//待售
            } else if ($state == "1") {
                $where = " where EndTime>now() and startTime<=now() and i.state=1";//上架
            } else if ($state == "0") {
                $where = " where i.state=0";
            }

            if (!empty($merchantid)) {
                $where .= " and d.id = '" . $merchantid . "'";
            }
            if (!empty($categoryid)) {
                $where .= " and categoryid = '" . $categoryid . "'";
            }
            if (!empty($name)) {
                $where .= " and g.`name` like '%" . $name . "%'";
            }
            $categoryarray = D("GoodsMerchantUser")->query("select id,name from goods_category");
            $disgoodarray = D("GoodsMerchantUser")->query("select d.name,i.state,goodsid from goods_distributor_items i inner join goods_distributor d on i.disid=d.id");
            $category = array();
            $disgood = array();
            foreach ($categoryarray as $key => $value) {
                $category[$value["id"]] = $value["name"];
            }
            foreach ($disgoodarray as $key => $value) {
                $disgood[$value["goodsid"]] = [$value["name"], $value["state"]];
            }

            //2017/04/06 添加商品类型
            $typesarray = D("GoodsMerchantUser")->query("select DISTINCT i.name,s.discount,i.goodsid from goods_info_item i inner join goods_distributor_items_price s on i.id=s.itemid ");
            $types = array();
            foreach ($typesarray as $key => $value) {
                $goodsid = $value["goodsid"];
                $temp = array();
                if (array_key_exists($goodsid, $types)) {
                    $temp = $types[$goodsid];
                }
                $temp[] = [$value["name"], $value["discount"]];
                $types[$goodsid] = $temp;
            }

            //total
            $total = 0;
            $totalarray = D("GoodsMerchantUser")->query("select count(i.goodsid) as count  from goods_info_base g inner join goods_distributor_items i on g.id=i.goodsid inner join goods_distributor d on i.disid=d.id " . $where . " ");
            $res = D("GoodsMerchantUser")->query("select i.goodsid,g.id,i.disid,g.`name` as gname,d.`name` as dname,g.CategoryId,(case when (g.starttime<now() and g.endtime>now()) then '1' else '0' end) as state1,i.state from goods_info_base g inner join goods_distributor_items i on g.id=i.goodsid inner join goods_distributor d on i.disid=d.id " . $where . "   order by g.id desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize);
            $data = array();
            $i = 1;
            foreach ($totalarray as $key => $value) {
                $total = $value["count"];
            }
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["id"] = is_null($value["id"]) ? "" : $value["id"];
                $item["goodsid"] = is_null($value["goodsid"]) ? "" : $value["goodsid"];
                $item["disid"] = is_null($value["disid"]) ? "" : $value["disid"];
                $item["gname"] = is_null($value["gname"]) ? "" : $value["gname"];
                $item["dname"] = is_null($value["dname"]) ? "" : $value["dname"];
                $item["categoryname"] = "";
                if (array_key_exists($value["categoryid"], $category)) {
                    $item["categoryname"] = $category[$value["categoryid"]];
                }
                $item["state"] = $value["state"] == "0" ? "0" : "";
                if ($item["state"] != "0" && empty($item["state"])) {
                    if ($value["state1"] == "1") {
                        $item["state"] = "1";
                    } else {
                        $item["state"] = "2";
                    }
                }

                if (array_key_exists($item["id"], $types)) {
                    $temp = $types[$item["id"]];
                    $item["data"] = $temp;
                } else {
                    $item["data"] = array();
                }
                array_push($data, $item);
                $i++;
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

    public function disorder()
    {
        $user_groups = AuthGroupModel::getUserGroup(UID);
        $isDisorder = 0;
        foreach ($user_groups as $val) {
            if ($val["group_id"] == 23) {
                $isDisorder = 1;
            }
        }
        $this->assign("isdisorder", $isDisorder);
        $this->assign("username", session("user_auth")["username"]);
        $this->display();
    }

    public function queryDisorder()
    {
        $pageIndex = getParam("pageIndex") + 1;
        $pageSize = getParam("pageSize");
        $name = urldecode($_GET["name"]);
        $disname = urldecode($_GET["disname"]);
        $mobile = urldecode($_GET["mobile"]);
        $orderstate = urldecode($_GET["orderstate"]);
        $starttime = urldecode($_GET["starttime"]);
        $endtime = urldecode($_GET["endtime"]);
        try {

            $where = " where 1=1";
            if (!empty($disname)) {
                $where .= " and d.`name` like '%" . $disname . "%'";
            }
            if (!empty($mobile)) {
                $where .= " and a.`contract` like '%" . $mobile . "%'";
            }
            if (!empty($name)) {
                $where .= " and b.`name` like '%" . $name . "%'";
            }
            if (!empty($starttime) && !empty($endtime)) {
                $where .= " and o.`ordertime` between '" . $starttime . "' and '" . $endtime . "'";
            }
            if ($orderstate == 0) {
                $table = "";
            } else {
                $table = $orderstate == "3" ? "orders_canceled" : "orders_success";
            }
            if ($orderstate == "1") {
                $where .= " and a.`isused` = '1'";
            } else if ($orderstate == "2") {
                $where .= " and a.`isused` = '0'";
            }
            //total
            $total = 0;
            if ($table == "orders_canceled") {
                $totalarray = D("GoodsMerchantUser")->query("select count(*) as count from activity_dn97 a INNER JOIN $table o on (a.OrderNo=o.orderno or a.OrderNo=o.OrgOrderNo) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc");
                $sql = "select a.amount,a.orderno,d.`Name` as disname,a.`name`,a.contract,b.`Name` as gname,i.name as iname, i.costprice,a.takencnt,o.ordertime,o.totalmoney,a.isused from activity_dn97 a INNER JOIN $table o on (a.OrderNo=o.orderno or a.OrderNo=o.OrgOrderNo) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize;
                $res = D("GoodsMerchantUser")->query($sql);
                $total = $totalarray[0]["count"];
            } else if ($table == "orders_success") {
                $totalarray = D("GoodsMerchantUser")->query("select count(*) as count from activity_dn97 a INNER JOIN $table o on (a.OrderNo=o.orderno ) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc");
                $sql = "select a.TrdStatus,  a.amount,a.orderno,d.`Name` as disname,a.`name`,a.contract,b.`Name` as gname,i.name as iname, i.costprice,a.takencnt,o.ordertime,o.totalmoney,a.isused from activity_dn97 a INNER JOIN $table o on (a.OrderNo=o.orderno) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize;
                $res = D("GoodsMerchantUser")->query($sql);
                $total = $totalarray[0]["count"];
            } else {
                $offset = ($pageIndex - 1) * $pageSize;
                //已取消
                $totalarray1 = D("GoodsMerchantUser")->query("select count(*) as count from activity_dn97 a INNER JOIN orders_canceled o on (a.OrderNo=o.orderno or a.OrderNo=o.OrgOrderNo) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc");
                $totalarray = D("GoodsMerchantUser")->query("select count(*) as count from activity_dn97 a INNER JOIN orders_success o on (a.OrderNo=o.orderno ) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc");
                $total = $totalarray[0]["count"] + $totalarray1[0]["count"];
                if ($offset < $totalarray[0]["count"]) {
                    $sql1 = "select a.TrdStatus, a.amount,a.orderno,d.`Name` as disname,a.`name`,a.contract,b.`Name` as gname,i.name as iname, i.costprice,a.takencnt,o.ordertime,o.totalmoney,a.isused from activity_dn97 a INNER JOIN orders_success o on (a.OrderNo=o.orderno) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc  limit " . ($pageIndex - 1) * $pageSize . "," . $pageSize;
                    $res = D("GoodsMerchantUser")->query($sql1);
                } else {
                    $offset = $offset - $totalarray[0]["count"];
                    //交易成功
                    $sql = "select a.amount,a.orderno,d.`Name` as disname,a.`name`,a.contract,b.`Name` as gname,i.name as iname, i.costprice,a.takencnt,o.ordertime,o.totalmoney,a.isused from activity_dn97 a INNER JOIN orders_canceled o on (a.OrderNo=o.orderno or a.OrderNo=o.OrgOrderNo) left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc  limit " . $offset . "," . $pageSize;
                    $res = D("GoodsMerchantUser")->query($sql);
                }
            }
            $data = array();
            $i = 1;
            foreach ($res as $key => $value) {
                $item = array();
                $item["num"] = $i;
                $item["orderno"] = is_null($value["orderno"]) ? "" : $value["orderno"];
                $item["disname"] = is_null($value["disname"]) ? "" : $value["disname"];
                $item["name"] = is_null($value["name"]) ? "" : $value["name"];
                $item["contract"] = is_null($value["contract"]) ? "" : $value["contract"];
                $item["gname"] = is_null($value["gname"]) ? "" : $value["gname"];
                $item["iname"] = is_null($value["iname"]) ? "" : $value["iname"];
                $item["costprice"] = is_null($value["costprice"]) ? "" : $value["costprice"];
                $item["takencnt"] = is_null($value["amount"]) ? "" : $value["amount"];
                $item["ordertime"] = is_null($value["ordertime"]) ? "" : $value["ordertime"];
                $item["totalmoney"] = is_null($value["totalmoney"]) ? "" : $value["totalmoney"];
                $item["TrdStatus"] = is_null($value["trdstatus"]) ? "99" : $value["trdstatus"];
                $item["isused"] = $value["isused"];
                if($table == "orders_canceled"||is_null($value["trdstatus"])){
                    $item["isused"]="已退款";
                }else{
                    if($item["isused"]=="1"){
                        $item["isused"] = "已取票";
                    }else{
                        $item["isused"] = "已付款";
                    }
                }
                array_push($data, $item);
                $i++;
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

    public function disorderdetail()
    {
        try {
            $orderno = urldecode($_GET["orderno"]);
            $res = D("GoodsInfoBase")->query("select * from activity_dn97 where orderno='%s'", $orderno);

            $content = array();
            foreach ($res as $key => $value) {
                $content["orderno"] = $value["orderno"];
                $content["name"] = $value["name"];
                $content["contract"] = $value["contract"];
                $content["idcard"] = $value["idcard"];
                $content["takencnt"] = $value["amount"];
                $content["carno"] = $value["carno"];
                $stime = $value["rangestartdate"];
                $etime = $value["rangeenddate"];
                if ($stime == $etime) {
                    $content["rangedate"] = $value["rangedate"];
                } else {
                    $content["rangedate"] = $stime . "至" . $etime;
                }
                if ($content["rangedate"] == "0001-01-01" || empty($content["rangedate"])) {
                    $content["rangedate"] = $value["activitydate"];
                }

            }

            $this->assign("content", json_encode($content));

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->display();
    }


    function export()
    {
        $name = urldecode($_GET["name"]);
        $disname = urldecode($_GET["disname"]);
        $mobile = urldecode($_GET["mobile"]);
        $orderstate = urldecode($_GET["orderstate"]);
        $starttime = urldecode($_GET["starttime"]);
        $endtime = urldecode($_GET["endtime"]);

        $where = " where 1=1";
        if (!empty($disname)) {
            $where .= " and d.`name` like '%" . $disname . "%'";
        }
        if (!empty($mobile)) {
            $where .= " and a.`contract` like '%" . $mobile . "%'";
        }
        if (!empty($name)) {
            $where .= " and b.`name` like '%" . $name . "%'";
        }
        if (!empty($starttime) && !empty($endtime)) {
            $where .= " and o.`ordertime` between '" . $starttime . "' and '" . $endtime . "'";
        }

        $table = $orderstate == "3" ? "orders_canceled" : "orders_success";
        if ($orderstate == "1") {
            $where .= " and a.`isused` = '1'";
        } else if ($orderstate == "2") {
            $where .= " and a.`isused` = '0'";
        }

        $column = array("分销商名称", "姓名", "手机号", "商品名称", "商品型号", "商品结算价", "票数", "下单时间", "商品价格", "订单状态");
        $res = D("GoodsMerchantUser")->query("select a.amount,a.orderno,d.`Name` as disname,a.`name`,a.contract,b.`Name` as gname,i.name as iname, i.costprice,a.takencnt,o.ordertime,o.totalmoney,a.isused from activity_dn97 a INNER JOIN $table o on a.OrderNo=o.orderno left join goods_info_base b on a.activityid=b.id LEFT JOIN goods_info_item i on a.itemid=i.id left join goods_distributor d on a.DistId=d.id $where order by o.ordertime desc  ");
        $data = array();
        $i = 1;
        foreach ($res as $key => $value) {
            $item = array();
            //$item["num"]=$i;
            // $item["orderno"]=is_null($value["orderno"])?"":$value["orderno"];
            $item["disname"] = is_null($value["disname"]) ? "" : $value["disname"];
            $item["name"] = is_null($value["name"]) ? "" : $value["name"];
            $item["contract"] = is_null($value["contract"]) ? "" : $value["contract"];
            $item["gname"] = is_null($value["gname"]) ? "" : $value["gname"];
            $item["iname"] = is_null($value["iname"]) ? "" : $value["iname"];
            $item["costprice"] = is_null($value["costprice"]) ? "" : $value["costprice"];
            $item["takencnt"] = is_null($value["amount"]) ? "" : $value["amount"];
            $item["ordertime"] = is_null($value["ordertime"]) ? "" : $value["ordertime"];
            $item["totalmoney"] = is_null($value["totalmoney"]) ? "" : $value["totalmoney"];

            if ($orderstate == "1") {
                $item["isused"] = "已取票";
            } else if ($orderstate == "2") {
                $item["isused"] = "已付款";
            } else {
                $item["isused"] = "已退款";
            }
            //$item["isused"]=is_null($value["isused"])?"":$value["isused"];
            array_push($data, $item);
            $i++;
        }
        $this->exportExcel("分销电子订单", $column, $data);
    }

    function exportExcel($title, $titleArr, $data)
    {
        header("Content-Type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $title . ".xls");
        header("Pragma:no-cache");
        header("Expires:0");
        $str = "";
        echo iconv("utf-8", "gbk", implode("\t", $titleArr)), "\n";
        $max = count($titleArr);
        foreach ($data as $key => $value) {
            echo iconv("utf-8", "gbk", implode("\t", array_slice($value, 0, $max))), "\n";
        }
    }
}
