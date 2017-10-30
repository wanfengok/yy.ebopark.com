<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:34
 */

namespace Admin\Model;


use Think\Model;

class MsgDefineSmsModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='msg_define_sms';
    protected $tablePrefix=null;


}