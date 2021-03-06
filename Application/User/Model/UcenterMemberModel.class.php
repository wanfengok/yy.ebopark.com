<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace User\Model;
use Think\Model;
require_once(APP_PATH . '/User/Conf/config.php');
require_once(APP_PATH. '/User/Common/common.php');//thinkphp 加密\解密

/**
 * 会员模型
 */
class UcenterMemberModel extends Model
{

    /**
     * 检测用户名是不是被禁止注册(保留用户名)
     * @param  string $username 用户名
     * @return boolean          ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMember($username)
    {
        $denyName=M("Config")->where(array('name' => 'USER_NAME_BAOLIU'))->getField('value');
        if($denyName!=''){
            $denyName=explode(',',$denyName);
            foreach($denyName as $val){
                if(!is_bool(strpos($username,$val))){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 检测邮箱是不是被禁止注册
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyEmail($email)
    {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    protected function checkUsername($username)
    {

        //如果用户名中有空格，不允许注册
        if (strpos($username, ' ') !== false) {
            return false;
        }
        preg_match("/^[a-zA-Z0-9_]{0,64}$/", $username, $result);

        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 验证用户名长度
     * @param $username
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    protected function checkUsernameLength($username)
    {
        $length = mb_strlen($username, 'utf-8'); // 当前数据长度
        if ($length < modC('USERNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')) {
            return false;
        }
        return true;
    }
    /**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMobile($mobile)
    {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 根据配置指定用户状态
     * @return integer 用户状态
     */
    protected function getStatus()
    {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $nickname 昵称
     * @param  string $password 用户密码
     * @param  string $email 用户邮箱
     * @param  string $mobile 用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $nickname, $password, $email='', $mobile='', $type=1)
    {
        $data = array(
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'mobile' => $mobile,
            'type' => $type,
        );

        //验证手机
        if (empty($data['mobile'])) unset($data['mobile']);
        if (empty($data['username'])) unset($data['username']);
        if (empty($data['email'])) unset($data['email']);
        //验证用户是否已注册
        $res=$this->where("username='$username'")->select();
        if(count($res)!=0){
           return "用户已存在,不能重复添加!";
        }
        /* 添加用户 */
        $usercenter_member = $this->create($data);
        if ($usercenter_member) {
            $result = D('Common/Member')->registerMember($nickname);
            if ($result > 0) {
                $usercenter_member['id'] = $result;
                $uid = $this->add($usercenter_member);
                if ($uid === false) {
                    //如果注册失败，则回去Memeber表删除掉错误的记录
                    D('Common/Member')->where(array('uid' => $result))->delete();
                }
                action_log('reg','ucenter_member',1,1);
                return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
            } else {
                return $result;
            }
        } else {
            return $this->getError(); //错误详情见自动验证注释
        }
    }
    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password)
    {
        $map = array();
        $map['username'] = $username;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if (is_array($user)) {
            /* 验证用户密码 */
                $key=think_ucenter_md5($password, UC_AUTH_KEY);
            if ( $key=== $user['password']) {
                $this->updateLogin($user['id']); //更新用户登录信息
                return $user['id']; //登录成功，返回用户ID
            } else {
                action_log('input_password','ucenter_member',$user['id'],$user['id']);
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }
    /**
     * 初始化角色用户信息
     * @param $role_id
     * @param $uid
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    public  function initRoleUser($role_id = 0, $uid)
    {
        $memberModel = D('Member');
        $role = D('Role')->where(array('id' => $role_id))->find();
        $user_role = array('uid' => $uid, 'role_id' => $role_id, 'step' => "start");
        if ($role['audit']) { //该角色需要审核
            $user_role['status'] = 2; //未审核
        } else {
            $user_role['status'] = 1;
        }
        $result = D('UserRole')->add($user_role);
        if (!$role['audit']) {
            //该角色不需要审核
            $memberModel->initUserRoleInfo($role_id, $uid);
        }
        $memberModel->initDefaultShowRole($role_id, $uid);

        return $result;
    }


    public function getLocal($username, $password)
    {
        $aUsername = $username;
        check_username($aUsername, $email, $mobile, $type);

        $map = array();
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        /* 获取用户数据 */
        $user = $this->where($map)->find();

        if (is_array($user) && $user['status']) {
            /* 验证用户密码 */
            if (think_ucenter_md5($password, UC_AUTH_KEY) === $user['password']) {
                return $user; //登录成功，返回用户ID
            } else {
                return false; //密码错误
            }
        } else {
            return false; //用户不存在或被禁用
        }
    }

    /**
     * 用户密码找回认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function lomi($username, $email)
    {
        $map = array();
        $map['username'] = $username;
        $map['email'] = $email;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if (is_array($user)) {
            /* 验证用户 */
            //if($user['last_login_time']){
            //return $user['last_login_time']; //成功，返回用户最后登录时间
            return $user; //成功，返回用户最后登录时间
            //}else{
            //return $user['reg_time']; //返回用户注册时间
            //return -1; //成功，返回用户最后登录时间
            //}
        } else {
            return -2; //用户和邮箱不符
        }
    }

    /**
     * 用户密码找回认证2
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function reset($uid)
    {
        $map = array();
        $map['id'] = $uid;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if (is_array($user)) {
            return $user; //成功，返回用户数据

        } else {
            return -2; //用户和邮箱不符
        }
    }

    /**
     * 根据IP获取用户最后注册时间
     * @param  string  $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function infos($regip)
    {
        $map['reg_ip'] = $regip;
        $user = $this->where($map)->max('reg_time');
        if ($user) {
            return $user;
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 获取用户信息
     * @param  string  $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false)
    {
        $map = array();
        if ($is_username) { //通过用户名获取
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->field('id,username,email,mobile,status')->find();
        if (is_array($user) && $user['status'] = 1) {
            return array($user['id'], $user['username'], $user['email'], $user['mobile']);
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 检测用户信息
     * @param  string  $field 用户名
     * @param  integer $type 用户名类型 1-用户名，2-用户邮箱，3-用户电话
     * @return integer         错误编号
     */
    public function checkField($field, $type = 1)
    {
        $data = array();
        switch ($type) {
            case 1:
                $data['username'] = $field;
                break;
            case 2:
                $data['email'] = $field;
                break;
            case 3:
                $data['mobile'] = $field;
                break;
            default:
                return 0; //参数错误
        }

        return $this->create($data) ? 1 : $this->getError();
    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateLogin($uid)
    {
        $data = array(
            'id' => $uid,
            'last_login_time' => NOW_TIME,
            'last_login_ip' => get_client_ip(1),
        );
        $this->save($data);
    }

    /**
     * 更新用户信息
     * @param int    $uid 用户id
     * @param string $password 密码，用来验证
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFields($uid, $password, $data)
    {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = L('_PARAM_ERROR_25_');
            return false;
        }

        //更新前检查用户密码
        if (!$this->verifyUser($uid, $password)) {
            $this->error = "原始密码错误";
            return false;
        }

        //更新用户信息
        $data = $this->create($data, 2); //指定此处为更新数据
        if ($data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }

    /**
     * 重置用户密码
     * @param int    $uid 用户id
     * @param string $password 密码，用来验证
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFieldss($uid, $data)
    {
        if (empty($uid) || empty($data)) {
            $this->error = L('_PARAM_ERROR_25_');
            return false;
        }
        //更新用户信息
        $data = $this->create($data, 2);
        if ($data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }

    /**
     * 验证用户密码
     * @param int    $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
    public function verifyUser($uid, $password_in)
    {
        $password = $this->getFieldById($uid, 'password');
        if (think_ucenter_md5($password_in, UC_AUTH_KEY) === $password) {
            return true;
        }
        return false;
    }




    /**修改密码
     * @param $old_password
     * @param $new_password
     * @return bool
     * @auth 陈一枭
     */
    public function changePassword($old_password, $new_password)
    {
        //检查旧密码是否正确
        if (!$this->verifyUser(get_uid(), $old_password)) {
            $this->error = -41;
            return false;
        }
        //更新用户信息
        $model = $this;
        $data = array('password' => think_ucenter_md5('$new_password',UC_AUTH_KEY));
        $data = $model->create($data);
        if (!$data) {
            $this->error = $model->getError();
            return false;
        }
        $model->where(array('id' => get_uid()))->save($data);
        //返回成功信息
        clean_query_user_cache(get_uid(), 'password');//删除缓存
        D('user_token')->where('uid=' . get_uid())->delete();
        return true;
    }

    public function getErrorMessage($error_code = null)
    {

        $error = $error_code == null ? $this->error : $error_code;
        switch ($error) {
            case -1:
                $error = L('_USER_NAME_MUST_BE_IN_LENGTH_').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').L('_BETWEEN_CHARACTERS_WITH_EXCLAMATION_');
                break;
            case -2:
                $error = L('_USER_NAME_IS_FORBIDDEN_TO_REGISTER_WITH_EXCLAMATION_');
                break;
            case -3:
                $error = L('_USER_NAME_IS_OCCUPIED_WITH_EXCLAMATION_');
                break;
            case -4:
                $error = L('_PW_LENGTH_6_30_');
                break;
            case -41:
                $error = L('_USERS_OLD_PASSWORD_IS_INCORRECT_');
                break;
            case -5:
                $error = L('_MAILBOX_FORMAT_IS_NOT_CORRECT_WITH_EXCLAMATION_');
                break;
            case -6:
                $error = L('_EMAIL_LENGTH_4_32_');
                break;
            case -7:
                $error = L('_MAILBOX_IS_PROHIBITED_TO_REGISTER_WITH_EXCLAMATION_');
                break;
            case -8:
                $error = L('_MAILBOX_IS_OCCUPIED_WITH_EXCLAMATION_');
                break;
            case -9:
                $error = L('_MOBILE_PHONE_FORMAT_IS_NOT_CORRECT_WITH_EXCLAMATION_');
                break;
            case -10:
                $error = L('_MOBILE_PHONES_ARE_PROHIBITED_FROM_REGISTERING_WITH_EXCLAMATION_');
                break;
            case -11:
                $error = L('_PHONE_NUMBER_IS_OCCUPIED_WITH_EXCLAMATION_');
                break;
            case -12:
                $error = L('_UN_LIMIT_SOME_');
                break;
            case -31:
                $error = L('_THE_NICKNAME_IS_PROHIBITED_');
                break;
            case -33:
                $error = L('_NICKNAME_LENGTH_MUST_BE_IN_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').L('_BETWEEN_CHARACTERS_WITH_EXCLAMATION_');
                break;
            case -32:
                $error = L('_THE_NICKNAME_IS_NOT_LEGAL_');
                break;
            case -30:
                $error = L('_THE_NICKNAME_HAS_BEEN_OCCUPIED_');
                break;

            default:
                $error = L('_UNKNOWN_ERROR_');
        }
        return $error;
    }


    /**
     * addSyncData
     * @return mixed
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function addSyncData()
    {
        $data['email'] = $this->rand_email();
        $data['password'] = create_rand(10);
        $data['type'] = 2;  // 视作用邮箱注册
        $data = $this->create($data);
        $uid = $this->add($data);
        return $uid;
    }

    protected  function rand_email()
    {
        $email = create_rand(10) . '@ocenter.com';
        if ($this->where(array('email' => $email))->select()) {
            $this->rand_email();
        } else {
            return $email;
        }
    }
    public function getUserName($uid){
        return $this->where(array('id'=>(int)$uid))->getField('username');
    }



}
