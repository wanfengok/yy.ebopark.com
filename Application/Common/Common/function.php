<?php
require_once(APP_PATH . '/Common/Common/query_user.php');
require_once(APP_PATH . '/Common/Common/limit.php');
/**
 * 获取当前页面完整URL地址
 */
function get_url()
{
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}

/**
 * @param string $url
 * @param string $posts post请求参数
 * @return mixed
 */
function curl($url = "", $posts = "")
{
    \Think\Log::write('request url:' . $url . ',post param:' . implode(',', $posts), 'INFO');
    $ch = curl_init();
    $jsonData = "";
    if ($posts) {
        $jsonData = json_encode($posts);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData))
    );
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    $res = curl_exec($ch);
    \Think\Log::write(' response:' . $res, 'INFO');
    curl_close($ch);
    return $res;
}


/**
 * @param string $path
 * @param $postData
 * @param $token
 * @param $privatekey
 * @return string
 */
function getUrl($path = '', $postData, $token, $privatekey)
{
    $serverUrl = C("lpn_server_url");
    $serverPort = c("lpn_server_port");
    $url = $serverUrl . ':' . $serverPort . $path;
    return getMd5Url($url, $postData, $token, $privatekey);

}

function getMessageUrl($path = '', $postData, $token, $privatekey)
{
    $serverUrl = C("msg_server_url");
    $serverPort = c("msg_server_port");
    $url = $serverUrl . ':' . $serverPort . $path;
    return getMd5Url($url, $postData, $token, $privatekey);

}

/**
 * @param $url
 * @param $postData
 * @param string $token
 * @param string $privatekey
 * @return string
 */
function getMd5Url($url, $postData, $token = '', $privatekey = '')
{

    $jsonStr = json_encode($postData);
    $md5Str = $jsonStr . $privatekey;
    $sign = md5($md5Str);
    $finalUrl = $url . '?' . 'token=' . $token . '&sign=' . $sign . '&client=3';

    $lng = getParam('lng');
    $lat = getParam('lat');
    if (!empty($lng) && !empty($lat)) {
        $finalUrl = $finalUrl . '&lng=' . $lng . '&lat=' . $lat;
    }
    return $finalUrl;
}

/**
 * @param $name 参数名称
 * @return string
 */
function getParam($name)
{

    $value = IS_GET ? $_GET[$name] : $_POST[$name];
    if (empty($value)) {
        return $value;
    }
    $result = iconv("gb2312", "utf-8", $value);
    return trim(!$result ? $value : $result);

}

function http_get($url)
{

    $res = file_get_contents($url);
    return $res;
}

/**
 * 判断终端是否为手机端
 * @return bool
 */
function is_mobile()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi",
        "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio",
        "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu",
        "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ",
        "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi",
        "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "iphone", "ipod", "jbrowser", "kddi",
        "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo",
        "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-",
        "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia",
        "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-",
        "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo",
        "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank",
        "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit",
        "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin",
        "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce",
        "wireless", "xda", "xde", "zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}

/**
 * 判断是否为微信浏览器
 * @return bool
 */
function is_weixin()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $isWeixin = false;
    if (stripos($user_agent, "micromessenger")) {
        $isWeixin = true;
    }
    return $isWeixin;

}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login()
{

    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null)
{
    $uid = is_null($uid) ? is_login() : $uid;
    $admin_uids = explode(',', C('USER_ADMINISTRATOR'));//调整验证机制，支持多管理员，用,分隔
    //dump($admin_uids);exit;
    return $uid && (in_array(intval($uid), $admin_uids));//调整验证机制，支持多管理员，用,分隔
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null)
{

    //参数检查
    if (empty($action) || empty($model) || empty($record_id)) {
        return L('_PARAMETERS_CANT_BE_EMPTY_');
    }
    if (empty($user_id)) {
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if ($action_info['status'] != 1) {
        return L('_THE_ACT_IS_DISABLED_OR_DELETED_');
    }

    //插入行为日志
    $data['action_id'] = $action_info['id'];
    $data['user_id'] = $user_id;
    $data['action_ip'] = ip2long(get_client_ip());
    $data['model'] = $model;
    $data['record_id'] = $record_id;
    $data['create_time'] = NOW_TIME;

    //解析日志规则,生成日志备注
    if (!empty($action_info['log'])) {
        if (preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)) {
            $log['user'] = $user_id;
            $log['record'] = $record_id;
            $log['model'] = $model;
            $log['time'] = NOW_TIME;
            $log['data'] = array('user' => $user_id, 'model' => $model, 'record' => $record_id, 'time' => NOW_TIME);
            foreach ($match[1] as $value) {
                $param = explode('|', $value);
                if (isset($param[1])) {
                    $replace[] = call_user_func($param[1], $log[$param[0]]);
                } else {
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
        } else {
            $data['remark'] = $action_info['log'];
        }
    } else {
        //未定义日志规则，记录操作url
        $data['remark'] = '操作url：' . $_SERVER['REQUEST_URI'];
    }


    $log_id = M('ActionLog')->add($data);

//    if (!empty($action_info['rule'])) {
//        //解析行为
//        $rules = parse_action($action, $user_id);
//        //执行行为
//        $res = execute_action($rules, $action_info['id'], $user_id, $log_id);
//    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 */
function parse_action($action = null, $self)
{
    if (empty($action)) {
        return false;
    }

    //参数支持id或者name
    if (is_numeric($action)) {
        $map = array('id' => $action);
    } else {
        $map = array('name' => $action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();

    if (!$info || $info['status'] != 1) {
        return false;
    }
    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = unserialize($info['rule']);
    foreach ($rules as $key => &$rule) {
        foreach ($rule as $k => &$v) {
            if (empty($v)) {
                unset($rule[$k]);
            }
        }
        unset($k, $v);
    }
    unset($key, $rule);

    /*    $rules = str_replace('{$self}', $self, $rules);
        $rules = explode(';', $rules);
        $return = array();
        foreach ($rules as $key => &$rule) {
            $rule = explode('|', $rule);
            foreach ($rule as $k => $fields) {
                $field = empty($fields) ? array() : explode(':', $fields);
                if (!empty($field)) {
                    $return[$key][$field[0]] = $field[1];
                }
            }
            //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
            if (!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])) {
                unset($return[$key]['cycle'], $return[$key]['max']);
            }
        }*/


    return $rules;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 */
function execute_action($rules = false, $action_id = null, $user_id = null, $log_id = null)
{
    $log_score = '';

    hook('handleAction', array('action_id' => $action_id, 'user_id' => $user_id, 'log_id' => $log_id, 'log_score' => &$log_score));

    if (!$rules || empty($action_id) || empty($user_id)) {
        return false;
    }
    $return = true;

    $action_log = M('ActionLog')->where(array('id' => $log_id))->find();
    foreach ($rules as $rule) {
        //检查执行周期
        $map = array('action_id' => $action_id, 'user_id' => $user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if ($exec_count > $rule['max']) {
            continue;
        }
        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        $field = 'score' . $rule['field'];


        $rule['rule'] = (is_bool(strpos($rule['rule'], '+')) ? '+' : '') . $rule['rule'];
        $rule['rule'] = is_bool(strpos($rule['rule'], '-')) ? $rule['rule'] : substr($rule['rule'], 1);
        $res = $Model->where(array('uid' => is_login(), 'status' => 1))->setField($field, array('exp', $field . $rule['rule']));

        $scoreModel = D('Ucenter/Score');

        $scoreModel->cleanUserCache(is_login(), $rule['field']);


        $sType = D('ucenter_score_type')->where(array('id' => $rule['field']))->find();
        $log_score .= '【' . $sType['title'] . '：' . $rule['rule'] . $sType['unit'] . '】';

        $action = strpos($rule['rule'], '-') ? 'dec' : 'inc';
        $scoreModel->addScoreLog(is_login(), $rule['field'], $action, substr($rule['rule'], 1, strlen($rule['rule']) - 1), $action_log['model'], $action_log['record_id'], $action_log['remark'] . '【' . $sType['title'] . '：' . $rule['rule'] . $sType['unit'] . '】');

        if (!$res) {
            $return = false;
        }
    }
    if ($log_score) {
        cookie('score_tip', $log_score, 30);
        M('ActionLog')->where(array('id' => $log_id))->setField('remark', array('exp', "CONCAT(remark,'" . $log_score . "')"));
    }
    return $return;
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * t函数用于过滤标签，输出没有html的干净的文本
 * @param string text 文本内容
 * @return string 处理后内容
 */
function op_t($text, $addslanshes = false)
{
    $text = nl2br($text);
    $text = real_strip_tags($text);
    if ($addslanshes)
        $text = addslashes($text);
    $text = trim($text);
    return $text;
}

/**过滤函数，别名函数，op_t的别名
 * @param $text
 */
function text($text, $addslanshes = false)
{
    return op_t($text, $addslanshes);
}

function real_strip_tags($str, $allowable_tags = "")
{
    // $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    return strip_tags($str, $allowable_tags);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL, $format = 'Y-m-d H:i')
{
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0)
{
    static $list;
    if (!($uid && is_numeric($uid))) { //获取当前登录用户名
        return $_SESSION['ocenter']['user_auth']['username'];
    }

    /* 获取缓存数据 */
    if (empty($list)) {
        $list = S('sys_active_user_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if (isset($list[$key])) { //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $User = new User\Api\UserApi();
        $info = $User->info($uid);
        if ($info && isset($info[1])) {
            $name = $list[$key] = $info[1];
            /* 缓存用户 */
            $count = count($list);
            $max = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_active_user_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = null)
{
    $user = query_user('nickname', $uid);
    return $user['nickname'];
}

function get_uid()
{
    return is_login();
}

function UCenterMember()
{
    return D('User/UcenterMember');
}

/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "")
{
    $result = array();
    if (is_array($pArray)) {
        foreach ($pArray as $temp_array) {
            if (is_object($temp_array)) {
                $temp_array = (array)$temp_array;
            }
            if (("" != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || "" == $pCondition) {
                $result[] = ("" == $pKey) ? $temp_array : isset($temp_array[$pKey]) ? $temp_array[$pKey] : "";
            }
        }
        return $result;
    } else {
        return false;
    }
}

/**
 * @param $tableName
 * @param $where
 * @param $type :0:M方法,1:D方法
 * @return mixed
 */
function pageQuery($tableName, $where = array())
{
    $pageIndex = getParam("pageIndex") + 1;
    $pageSize = getParam("pageSize");
    try {
        //total
        $total = M($tableName)->where($where)->count();
        $res = M($tableName)->where($where)->page($pageIndex, $pageSize)->order("id desc")->select();
        $data["total"] = $total;
        $data["data"] = $res;
        $data["state"] = 0;
        return $data;
    } catch (\Think\Exception $e) {
        $data["state"] = -1;
        $data["msg"] = $e->getMessage();
        $data["total"] = 0;
        return $data;
    }

}

function customizedPageQuery($tableName, $where)
{
    $pageIndex = getParam("pageIndex") + 1;
    $pageSize = getParam("pageSize");
    try {
        //total
        $total = D($tableName)->where($where)->count();
        $res = D($tableName)->where($where)->page($pageIndex, $pageSize)->order("id desc")->select();
        $data["total"] = $total;
        $data["data"] = $res;
        $data["state"] = 0;
        return $data;
    } catch (\Think\Exception $e) {
        $data["state"] = -1;
        $data["msg"] = $e->getMessage();
        $data["total"] = 0;
        return $data;
    }
}

function uuid($prefix = '')
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . '-';
    $uuid .= substr($chars, 8, 4) . '-';
    $uuid .= substr($chars, 12, 4) . '-';
    $uuid .= substr($chars, 16, 4) . '-';
    $uuid .= substr($chars, 20, 12);
    return md5($prefix . $uuid);
}

 function getSdkInfo(){
    // 从文件中读取数据到PHP变量
    $json_string = file_get_contents('./apk.json');
    // 把JSON字符串转成PHP数组
    $data = json_decode($json_string, true);
    return $data;


}

/**
 * @param $data
 */
function sign($data)
{
    $define=getDefine();
    return md5($data.$define["CommSignKey"]);//x2
}


function send_msg($carNo,$isPassed,$remark)
{
    $define=getDefine();
    $data=["CarNo"=>$carNo,"Ispassed"=>$isPassed,"Remark"=>$remark];
    $data_string = json_encode($data);
    $url=$define["Url"].("/CarVerify/NotifyState?sign=").sign($data_string);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip"); // 关键在这里
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    $result = curl_exec($ch);
    $returnObj=json_decode($result);
    curl_close($ch);
    return $returnObj->state==0;
}


function getDefine(){
    return array(
        "Token"=>"token",
        "Sign"=>"sign",
        "Did"=>"did",
        "CommSignKey"=>'0xOWPxOtXev3#$sCC4AxSoSJpr4LCY4b',
        //"Url"=>"192.168.0.250:8080"
        "Url"=>"http://lpn.ebopark.com"
    );
}

