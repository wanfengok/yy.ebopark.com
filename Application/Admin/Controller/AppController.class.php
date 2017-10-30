<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 16/11/15
 * Time: 下午1:52
 */

namespace Admin\Controller;
require_once  VENDOR_PATH.'php-sdk-7.1.2/src/Qiniu/functions.php';
use Admin\Utils\ApkParser;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
class AppController extends  BaseController
{
    private $versioname='';
    private $versioncode='';
    private $filename='';
    private $package='';
    private $content='';

//android sdk版本管理
    public function versionMs(){

        $data=getSdkInfo();
        if(!empty($data)){
            $this->assign("url",$data["url"]);
            $this->assign("versionname",$data["versionname"]);
            $this->assign("versioncode",$data["versioncode"]);
            $this->assign("content",$data["content"]);
            $this->assign("package",$data["package"]);

        }
        $this->display();
    }
    public function updateSdk(){

        $this->content=getParam("content");
        if(empty($this->content)){
            $this->error("更新说明不能为空！");

        }
        $this->upload();
    }

    public function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     33145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','apk');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $filePath='./Uploads/'.$info["apkfile"]["savepath"]."".$info["apkfile"]["savename"];
            $this->hanlderApk($filePath);
        }
    }
    /**
     * 解析apk文件,并保存最新消息
     */
    private function hanlderApk($filePath){
        $appObj= new ApkParser();
        $res   =$appObj->open($filePath);
        if(!$res){
            //解析失败
            $this->error("文件上传错误,请稍后重试!");
        }
        $this->package=$appObj->getPackage();    // 应用包名
        $this->versioname=$appObj->getVersionName();  // 版本名称
        $this->versioncode=$appObj->getVersionCode();  // 版本代码
        $this->uploadToQiNiu($filePath);
    }
    private function uploadToQiNiu($filePath){
        $accessKey = 'giJQSarbtz7xk09mzNj1T8MLhCeXTOrHPj9-Sq_2';
        $secretKey = '7nGQ3C_0xOWPxOtXev381psCC4AxSoSJpr4LCY4b';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'test';
        // 上传文件到七牛后， 七牛将文件名和文件大小回调给业务服务器
//        $policy = array(
//            'callbackUrl' => 'http://your.domain.com/callback.php',
//            'callbackBody' => 'filename=$(fname)&filesize=$(fsize)'
//        );
        $filePathArr=explode("/",$filePath);
        $this->filename=$filePathArr[count($filePathArr)-1];
        $uptoken = $auth->uploadToken($bucket, null, 3600, null);

        //上传文件的本地路径
        $uploadMgr = new UploadManager();

        list($ret, $err) = $uploadMgr->putFile($uptoken, $this->filename, $filePath);
        if ($err !== null) {
            $this->error("文件上传错误");
        } else {
            $url=C("QINIU_ADDR").$ret["key"];
            //保存版本信息
            $this->saveSdkInfo($url);
            $this->redirect("versionMs");
        }



    }
    private function saveSdkInfo($url=''){
        // 生成一个PHP数组
        $data = array();
        $data["versionname"] = empty($this->versionname)?"":$this->versionname;
        $data["versioncode"] = $this->versioncode;
        $data["url"]=$url;
        $data["package"]=$this->package;
        $data["content"]=$this->content;
        // 把PHP数组转成JSON字符串
        $json_string = json_encode($data);
        // 写入文件
        file_put_contents('./apk.json', $json_string);
    }

}