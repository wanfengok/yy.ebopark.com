<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/11
 * Time: 上午11:47
 */

namespace Admin\Model;


use Think\Model;

class CategoryHomeModel
{

    /**
     * 获取频道页面初始数据
     */
    public function getData()
    {
        $cates = D("GoodsCategory")->getCates();
        if (!empty($cates)) {
            foreach ($cates as $key => $val) {
                //商品分类id
                $parentid = $cates[$key]["id"];
                //商品分类下的所有子分类
                $tabs = D("GoodsSubCategory")->query("select * from goods_sub_category where ParentId='$parentid'");
                $cates[$key]["tabs"]=$tabs;
                //商品分类下所有的在售商品
                $now=date('Y-m-d H:i:s');
                $totalGoods=D("GoodsInfoBase")->query("select * from goods_info_base where CategoryId='$parentid' and '$now'>StartTime and '$now'<EndTime");
                $cates[$key]["goods"]=$totalGoods;
                //各商品分类下所有的商品
            }

        }
        $goodsOfSubCate=D("GoodsSubCategoryItems")->getGoodsOfAllSubCate();
        $data["cates"]=$cates;
        $data["subcate_goods"]=$goodsOfSubCate;
        return $data;

    }
}