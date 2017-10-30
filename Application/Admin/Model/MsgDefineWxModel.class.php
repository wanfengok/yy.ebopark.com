<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:34
 */

namespace Admin\Model;


use Think\Model;

class MsgDefineWxModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $trueTableName='msg_define_wx';
    protected $tablePrefix=null;

    public function getMessageById($id){
        $res=$this->where("Id=$id")->select();
        if(empty($res)){
            return $res;
        }
        $res[0]["cnt"]=$this->getSendCount($res[0]);
        return $res[0];
    }
    private function getSendCount($message){
        $res=D("MsgStatSend")->field("cnt")->where("msgid=".$message["id"]." and MsgType=3")->select();
        $count=$res!==false?$res[0]["cnt"]:0;
        return $count;
    }

}