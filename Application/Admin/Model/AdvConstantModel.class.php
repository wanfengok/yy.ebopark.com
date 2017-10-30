<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/4
 * Time: 下午6:39
 */

namespace Admin\Model;


use Think\Model;

class AdvConstantModel extends Model
{
    //微信消息
    const  ADV_SOURCE_WX=1;
    //系统消息
    const  ADV_SOURCE_SYSTEM_INFO=2;
    //banner(APP)
    const ADV_SOURCE_BANNER=3;
    //广告主ID
    const ADV_GROUP_ID=16;

    /**
     * 获取来源描述
     * @param $type
     * @return string
     */
    public function  getSourceName($type){
        $sourceName='';
        switch($type){
            case ADV_SOURCE_SYSTEM_INFO:$sourceName='系统消息';break;
            case ADV_SOURCE_WX:$sourceName='微信消息';break;
            case ADV_SOURCE_BANNER:$sourceName='Banner';break;
        }
        return $sourceName;
    }


}