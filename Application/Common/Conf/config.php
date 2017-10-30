<?php
//if (is_file('./Conf/common.php'))
//    return array_merge(require_once('./Conf/common.php'),array(
//        'LANG_SWITCH_ON' => true,
//        'LANG_AUTO_DETECT' => false, // 自动侦测语言 开启多语言功能后有效
//        'LANG_LIST'        => 'zh-cn,en-us,ja-jp,ko-kr', // 允许切换的语言列表 用逗号分隔
//        'VAR_LANGUAGE'     => 'l', // 默认语言切换变量
//    ));

return array(
////lpn数据库连接
    'DB_CONFIG2' => 'mysql://sa:123123@192.168.0.250:3306/eboparkplatformtest',
     /* 数据库设置 */
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '192.168.0.250', // 服务器地址
    'DB_NAME' => 'oms', // 数据库名
    'DB_USER' => 'sa', // 用户名
    'DB_PWD' => '123123', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 'oms_', // 数据库表前缀
    'DB_FIELDTYPE_CHECK' => true, // 是否进行字段类型检查
    'DB_FIELDS_CACHE' => true, // 启用字段缓存
    'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE' => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE' => false, // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM' => 1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO' => '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE' => false, // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE' => 'file', // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH' => 20, // SQL缓存的队列长度
    'DB_SQL_LOG' => true, // SQL执行日志记录
    'DB_BIND_PARAM' => false,// 数据库写入数据自动参数绑定
    'lpn_server_url'   => 'lpn.ebopark.com',
    'lpn_server_port'  =>'80',

//    //正式环境
//    'DB_CONFIG2' => 'mysql://lpnservice:lpn123_aBcedsAA@rdsy0x5e424zsa8rs006.mysql.rds.aliyuncs.com:3306/lpnservice',
//    /* 数据库设置 */
//    'DB_TYPE' => 'mysql', // 数据库类型
//    'DB_HOST' => 'rdsy0x5e424zsa8rs006.mysql.rds.aliyuncs.com', // 服务器地址
//    'DB_NAME' => 'oms', // 数据库名
//    'DB_USER' => 'oms', // 用户名
//    'DB_PWD' => 'oms53@#A12', // 密码
//    'DB_PORT' => '3306', // 端口
//    'DB_PREFIX' => 'oms_', // 数据库表前缀
//    'DB_FIELDTYPE_CHECK' => true, // 是否进行字段类型检查
//    'DB_FIELDS_CACHE' => true, // 启用字段缓存
//    'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8
//    'DB_DEPLOY_TYPE' => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
//    'DB_RW_SEPARATE' => false, // 数据库读写是否分离 主从式有效
//    'DB_MASTER_NUM' => 1, // 读写分离后 主服务器数量
//    'DB_SLAVE_NO' => '', // 指定从服务器序号
//    'DB_SQL_BUILD_CACHE' => false, // 数据库查询的SQL创建缓存
//    'DB_SQL_BUILD_QUEUE' => 'file', // SQL缓存队列的缓存方式 支持 file xcache和apc
//    'DB_SQL_BUILD_LENGTH' => 20, // SQL缓存的队列长度
//    'DB_SQL_LOG' => true, // SQL执行日志记录
//    'DB_BIND_PARAM' => false,// 数据库写入数据自动参数绑定
"AUTOLOAD_NAMESPACE"=>array(
        "Qiniu"=>VENDOR_PATH.'php-sdk-7.1.2/src/Qiniu/'
),
"QINIU_ADDR"=>"http://7xpcl7.com2.z0.glb.qiniucdn.com/",
    'GOODPREVIEW'=>"http://wx.ebopark.com/index.php/Home/Eshop/gooddetail_",//在售商品复制链接
    'GOODDIS'=>"http://wx.ebopark.com/index.php/Eshop/Goods/gooddetail?id=",
    'DIST'=>"http://wx.ebopark.com/index.php/Eshop/Category/index"//分销
);