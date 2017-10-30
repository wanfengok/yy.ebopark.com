<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/11
 * Time: 上午11:18
 */

namespace Admin\Model;


use Think\Model;

class GoodsSubCategoryItemsModel extends  Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='goods_sub_category_items';
    protected $tablePrefix=null;

    /**
     * 子类商品数量
     */
    public function getGoodsOfAllSubCate(){

        $sql="select * from goods_sub_category_items order by `Order` desc";
       return $this->query($sql);
    }

    /**
     * 移动商品至指定标签
     * @param $ids
     * @param $targetId
     * @param $orid
     */
    public function move($ids,$targetId,$orid){
            //商品id
            $idArr=explode(",",$ids);

            foreach($idArr as $id){

                if(!empty($orid)){
                    //删除原有关系
                    $this->execute("delete from goods_sub_category_items where GoodsId='$id' and SubCategoryId='$orid'");
                }
                if($targetId==-1){
                    continue;
                }
                //新增关系
                $order=$this->getGoodOrder();
                $this->execute("insert into goods_sub_category_items(GoodsId,SubCategoryId,`Order`) VALUES ('$id','$targetId',$order)");
            }
    }
    public function getGoodOrder(){
        $data=$this->query("select goods_sub_category_items.`Order` from goods_sub_category_items order by goods_sub_category_items.`Order` desc limit 0, 1");
        if(empty($data)){
            return 1;
        }
        $oriOrder=$data[0]["order"];
        return intval($oriOrder)+1;
    }
    public function changeOrder($origoodid,$oriorder,$goodid,$cateid,$order){
        $sql1="update goods_sub_category_items set `Order`=$order where GoodsId='$origoodid' and SubCategoryId='$cateid' ";
        $sql2="update goods_sub_category_items set `Order`=$oriorder where GoodsId='$goodid' and SubCategoryId='$cateid' ";
        $this->execute($sql1);
        $this->execute($sql2);

    }
    public function updateOrder($origoodid,$oriorder,$cateid){
        $sql1="update goods_sub_category_items set `Order`=$oriorder where GoodsId='$origoodid' and SubCategoryId='$cateid' ";
        $this->execute($sql1);

    }

}