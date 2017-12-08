<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/2
 * Time: 上午9:12
 */
namespace Admin\Model;
use Think\Model;
class ParkinfoModel extends Model
{


    protected $connection = 'DB_CONFIG2';
    protected $trueTableName = 'parkinfo';

    /**
     * 获取所有停车场坐在城市列表
     */
    public function getCities()
    {

        try {
            $res = $this->distinct(true)->field('City')->select();
            if (empty($res)) {
                $data["state"] = -1;
                $data["msg"] = '数据库操作失败';
            } else {
                $data["state"] = 0;
                $data["data"] = $res;
            }
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
        }
        return $data;

    }

    /**
     * 根据城市名称获取停车场列表
     */
    public function getParkLotListByCity($city)
    {
        try {
            $res = $this->field("ParkCode,ParkName")->where("City='$city'")->select();
            if (empty($res)) {
                $data["state"] = -1;
                $data["data"] = "数据库";
            } else {
                $data["state"] = 0;
                $data["data"] = $res;
            }
        } catch (Exception $e) {
            $data["state"] = -1;
            $data["msg"] = $e->getMessage();
        }
        return $data;
    }

    /**
     * 获取所有的城市列表及其所有的停车场
     * @return mixed
     */
    public function getCityAndParkLots()
    {

        $data = $this->getCities();
        if ($data["state"] != 0) {
            return $data;
        }
        $citys = $data["data"];
        //停车场数据,如array("成都"=>array("parkcode"=>456,"parkname"=>"宜泊"));
        $parkLotArr = array();
        //城市数据
        $citysArr = array();
        foreach ($citys as $key => $value) {

            $city = $value["city"];
            if(empty($city)){
                continue;
            }
            array_push($citysArr, $city);
            $res = $this->getParkLotListByCity($city);
            if ($res["state"] != 0) {
                return $res;
            }
            $parkLotArr[$city]=$res;
        }
        $finalData["state"]=0;
        $finalData["citys"]=$citysArr;
        $finalData["parklot"]=$parkLotArr;
        return $finalData;


    }
    public function getParkCodeAndParkName(){
        $res=$this->field("ParkName,ParkCode")->select();
        foreach($res as $key=>$value){
            $data[$value["parkcode"]]=$value["parkname"];
        }
        return $data;
    }
    public function searchByParkName($name){
         return   $this->field("ParkCode")->where("ParkName like '%$name%'")->select();
    }

}