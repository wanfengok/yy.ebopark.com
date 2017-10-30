<?php
return array(
/* 数据库配置*/
    'DEFAULT_MODULE'=> 'Admin',//默认访问模块
    'DEFAULT_CONTROLLER'=>'Index',//默认访问控制器
    'DEFAULT_DEFAULT_ACTION'=>'index',//默认访问方法
/* 模板文件配置*/
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/images',
        '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/css',
        '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/js',
        '__ZUI__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/zui',
        '__MODULE_STATIC__'=> __ROOT__ . '/Application/' . MODULE_NAME . '/Static',
        '__APPLICATION__'=>__ROOT__.'/Application/'
    ),
    'LANG_SWITCH_ON'    => true,        //开启多语言支持开关
    'DEFAULT_LANG'        => 'zh-cn',    // 默认语言
    'LANG_AUTO_DETECT'    => true,    //
    //管理员id设置
    'USER_ADMINISTRATOR' => 1, //管理员用户ID
    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR' => MODULE_PATH . 'View/default/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => MODULE_PATH . 'View/default/Public/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => MODULE_PATH . 'View/default/Public/exception.html',// 异常页面的模板文件
    'DEFAULT_THEME' => 'default', // 默认模板主题名称

    //系统跳转链接
    'weixin_autopay'=>'http://wx.ebopark.com/index.php/Home/PersonalCenter/autopay',//自动支付
    'weixin_balance'=>'http://wx.ebopark.com/index.php/Home/PersonalCenter/balance',//我的余额
    'weixin_recorder'=>'http://wx.ebopark.com/index.php/Home/Recorder/index',//交易记录
    'weixin_car_manage'=>'http://wx.ebopark.com/index.php/Home/CarnoMs/index',//车辆管理
    'weixin_monthly_car'=>'http://wx.ebopark.com/index.php/Home/MonthlyCars/index',//月租服务
    'weixin_coupon_list'=>'http://wx.ebopark.com/index.php/Home/Coupon/couponList',//我的优惠券
    'weixin_recharge'=>'http://wx.ebopark.com/index.php/Home/Recharge/index',//充值
    'weixin_parkfee'=>'http://wx.ebopark.com/index.php/Home/ParkFee/parkfee',//临停缴费



);