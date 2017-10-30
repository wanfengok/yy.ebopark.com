<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/3
 * Time: 上午12:12
 */

namespace Admin\Model;


use Think\Model;

class AdvertisementModel extends Model
{

    public function getAdvertisementById($id){
        $res=$this->where("id=$id")->select();
        if(empty($res)){
            return $res;
        }
        return $res[0];
    }
}