<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/10
 * Time: 下午6:11
 */

namespace Admin\Controller;


use Think\Exception;

class CategoryHomeController extends BaseController
{
    /**
     * 频道运营
     */
    public function index(){
        $data=D("CategoryHome")->getData();
        $this->assign("data",json_encode($data));
        $this->display();
    }
    public function updateTab(){
        $model=D("GoodsSubCategory");
       if(!$model->formcheck()){
           $data["state"]=-1;
           $data["msg"]="参数错误!";
           $this->ajaxReturn($data);
           return;
       }
        try{
            $model->updateTab();
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]="创建失败,请稍后再试!";
            $this->ajaxReturn($data);
        }

    }
    public function delTab(){
        $id=getParam("id");
        if(empty($id)){
            $data["state"]=-1;
            $data["msg"]="参数错误!";
            $this->ajaxReturn($data);
        }
        try{
            D("GoodsSubCategory")->delTab($id);
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]="操作失败,请稍后再试!";
            $this->ajaxReturn($data);
        }


    }

    /**
     * 移动标签
     */
    public function move(){
        $ids=getParam("ids");
        $targetId=getParam("subid");
        $orid=getParam("orid");
        try{
            D("GoodsSubCategoryItems")->move($ids,$targetId,$orid);
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]="操作失败,请稍后再试!";
            $this->ajaxReturn($data);
        }
    }
    public function changeOrder(){
        $origoodid=getParam("origoodid");
        $goodid=getParam("goodid");
        $cateid=getParam("cateid");
        $order=getParam("order");
        $oriorder=getParam("oriorder");
        try{
            D("GoodsSubCategoryItems")->changeOrder($origoodid,$oriorder,$goodid,$cateid,$order);
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]="操作失败,请稍后再试!";
            $this->ajaxReturn($data);
        }
    }
    public function topOrder(){
        $origoodid=getParam("origoodid");
        $cateid=getParam("cateid");
        $oriorder=getParam("oriorder");
        try{
            D("GoodsSubCategoryItems")->updateOrder($origoodid,$oriorder,$cateid);
            $data["state"]=0;
            $this->ajaxReturn($data);
        }catch(Exception $e){
            $data["state"]=-1;
            $data["msg"]="操作失败,请稍后再试!";
            $this->ajaxReturn($data);
        }

    }
}