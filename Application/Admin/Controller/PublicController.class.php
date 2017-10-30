<?php
namespace Admin\Controller;

use Think\Controller;
use  User\Api\UserApi;
class PublicController extends Controller
{
    /**
     * 后台登录页
     */
    public function login()
    {
        if (IS_POST) {
            $this->loginAction();
            return;
        }
        $this->display();
    }
    /**
     * 执行登录
     */
    public function loginAction()
    {
        $username = getParam("username");
        $password = getParam("password");
        if (empty($username) || empty($password)) {
            $this->error('用户名或密码不能为空');
        }
        $userApi=new UserApi();
        $uid= $userApi->login($username,$password);
        if(0 < $uid){ //1UC登录成功
            /* 登录用户 */
            $Member = D('Member');
            if($Member->login($uid)){ //登录用户
                //TODO:跳转到登录前页面
                $this->success(L('_LOGIN_SUCCESS_'), U('Index/index'));
            } else {
                $this->error($Member->getError());
            }

        } else { //登录失败
            switch($uid) {
                case -1: $error = L('_USERS_DO_NOT_EXIST_OR_ARE_DISABLED_'); break; //系统级别禁用
                case -2: $error = L('_PASSWORD_ERROR_'); break;
                default: $error = L('_UNKNOWN_ERROR_'); break; // 0-接口参数错误（调试阶段使用）
            }
            $this->error($error);
        }
    }
    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success(L('_EXIT_SUCCESS_'), U('login'));
        } else {
            $this->redirect('login');
        }
    }

} 