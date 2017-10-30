<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/4/8
 * Time: 下午2:11
 */

namespace Home\Api;

/**
 * 优惠券相关
 * Class PaymentApi
 * @package Home\Api
 */
class ParkApi
{


    /**
     * 获取可临停缴费停车场列表（首页）
     */
    public function getCanProStopParksHome(){

        $postData["skip"]=getParam("skip");
        $postData["take"]=getParam("take");
        $token=session("token");
        $url=getUrl('/Park/GetCanProStopParksHome',$postData,'',C("fixed_key"));
        $res=curl($url,$postData);
        return json_decode($res);

    }
    /**
     * 获取可临停缴费停车场列表（分页）
     */
    public function getCanProStopParks(){
        $skip=getParam('skip');
        $take=getParam('take');
        $city=getParam('city');
        $postData["skip"]=$skip;
        $postData["take"]=$take;
        $postData["city"]=$city;

        $url=getUrl('/Park/GetCanProStopParks',$postData,'',C("fixed_key"));
        $res=curl($url,$postData);
        return json_decode($res);

    }





} 