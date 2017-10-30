<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:34
 */

namespace Admin\Model;


use Think\Model;

class MsgDefineSystemModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='msg_define_system';
    protected $tablePrefix=null;

    public function getMessageById($id){
        $res=$this->where("Id=$id")->select();
        if(empty($res)){
            return $res;
        }
        return $res[0];
    }

}