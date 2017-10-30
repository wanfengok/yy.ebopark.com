<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/4/11
 * Time: 上午10:15
 */

namespace Admin\Model;


use Think\Model;

class GoodsCategoryModel extends  Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='goods_category';
    protected $tablePrefix=null;

    public function getCates(){
        return $this->query('select goods_category.Id,goods_category.Name from goods_category');
    }
}