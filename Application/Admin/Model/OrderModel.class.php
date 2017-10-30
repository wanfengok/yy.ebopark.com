<?php
/**
 * Created by PhpStorm.
 * User: gt
 * Date: 2017/8/22
 * Time: 上午9:56
 */

namespace Admin\Model;

use Think\Model;
class OrderModel extends Model
{
    protected $connection = 'DB_CONFIG2'; //连接数据库192.160.0.250 eboparkplatformtest
    protected $trueTableName='msg_stat_send';//连接表
    protected $tablePrefix=null;



}