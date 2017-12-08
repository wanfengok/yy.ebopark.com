<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 2017/11/24
 * Time: 下午4:49
 */

namespace Admin\Model;


use Think\Model;

class VirtualAccountParkChangeDetailModel extends  Model
{
    protected $connection = 'DB_CONFIG3';
    protected $trueTableName = 'transferqueue';
}