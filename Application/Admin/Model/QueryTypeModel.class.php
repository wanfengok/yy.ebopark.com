<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/8/1
 * Time: 上午11:38
 */

namespace Admin\Model;


use Think\Model;

class QueryTypeModel extends Model
{
    //使用M方法查询
    const QUERY_WITH_METHOD_M=0;
    //使用D方法查询
    const QUERY_WIDTH_METHOD_D=1;
}