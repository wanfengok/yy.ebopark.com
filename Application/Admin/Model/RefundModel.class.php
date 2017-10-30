<?php
/**
 * Created by PhpStorm.
 * User: gt
 * Date: 2017/9/6
 * Time: 上午10:07
 */
namespace Admin\Model;


use Think\Model;

class RefundModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='orders_cancel_apply';
    protected $tablePrefix=null;


}