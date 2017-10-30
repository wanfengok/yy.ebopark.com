<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class IndexController extends BaseController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index()
    {
        $mainMenuArr=$this->menus["main"];
        if(empty($mainMenuArr)){
            $this->error(L('_VISIT_NOT_AUTH_'));
        }
        $hasIndexUrl=false;
        foreach($mainMenuArr as $key=>$value){
            if($value["url"]=='Index/index'){
                $hasIndexUrl=true;
                break;
            }
        }
        if($hasIndexUrl){
            $this->display();
        }else{
            $url=current($mainMenuArr)["url"];
            $this->redirect($url);
        }

    }

}
