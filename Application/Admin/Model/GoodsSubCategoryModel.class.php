<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/11
 * Time: 上午10:15
 */

namespace Admin\Model;


use Think\Model;

class GoodsSubCategoryModel extends  Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='goods_sub_category';
    protected $tablePrefix=null;
    public $name;
    public $parentid;
    public $id;


    public function getId(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $id = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $id;
    }
    public function formcheck(){
        $this->name=getParam("Name");
        $this->parentid=getParam("ParentId");
        $this->id=getParam("id");
        if(empty($this->name)||empty($this->parentid)){

            return false;
        }
        return true;
    }
    public function updateTab(){
        if(!empty($this->id)){
            $sql=" update  goods_sub_category set Name='$this->name'  where  Id='$this->id' and ParentId='$this->parentid'";
        }else{
            $tabid=$this->getId();
            $sql="insert into goods_sub_category(ParentId,Name,Id) VALUES ('$this->parentid','$this->name','$tabid')";

        }
        $res=  $this->execute($sql);
        return $res;

    }
    public function delTab($id){

     return   $this->execute("delete from goods_sub_category where Id='$id'");
    }

}
