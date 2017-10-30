<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:34
 */

namespace Admin\Model;


use Think\Model;

class GoodsMerchantUserModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='goods_merchant_user';
    protected $tablePrefix=null;

}