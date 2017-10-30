/**
 * Created by Administrator on 2016/1/25.
 *
 * 1、alert
 */
var dialog_toast_maxTime;
function hideTips() {

    dialog_toast_maxTime -= 500;
    if (dialog_toast_maxTime < 0) {
        $("body").find(".tips_text").remove();
    } else {
        setTimeout(hideTips(), 500);
    }

}
$.alert = function (title, callback) {
    var tplObj = $('<div class="dialog_container">' +
        '<div class="tips_dialog_content">' +
        '<p class="tips_dialog_title">' + title +
        '</p>' +
        '<div class="tips_dialog_btn">' +
        '确定' +
        ' </div>' +
        ' </div>' +
        '</div>');
    $(tplObj).find('.tips_dialog_btn').on('click', function () {
        $("body").find(".dialog_container").remove();
        callback();
    });
    $("body").append(tplObj);


}
$.tipsAlert = function (title, callback) {
    var tplObj = $('<div class="dialog_container">' +
    '<div class="tips_dialog_content">' +
    '<p class="tips_dialog_title">' + title +
    '</p>' +
    '<div class="tips_dialog_btn" style="color:#189fe9;">' +
    '知道了' +
    ' </div>' +
    ' </div>' +
    '</div>');
    $(tplObj).find('.tips_dialog_btn').on('click', function () {
        $("body").find(".dialog_container").remove();
        callback();
    });
    $("body").append(tplObj);


}
$.confirm = function (title, callback) {
    var tplObj = $('<div class="dialog_container" ng-controller="ctr_dialog_confirm" ng-show="showConfirmDialog">' +

        '<div class="confirm_dialog_content">' +
        '<p class="confirm_dialog_title">' + title +
        '</p>' +
        ' <div class="confirm_dialog_btn_c">' +
        '<div style="float:left;width:50%;" class="confirm_dialog_btn_cancle" >取消 </div>' +
        '<div style="float:left;width:50%;" class="confirm_dialog_btn_sure">确定</div>' +
        '<div style="width:1px;height:40px;background:#f3f3f3;left:50%;position: absolute;margin-top:5px;"></div>' +
        '</div>' +
        '</div>' +
        '</div>');
    $(tplObj).find(".confirm_dialog_btn_cancle").on("click", function () {
        $("body").find(".dialog_container").remove();
    });
    $(tplObj).find(".confirm_dialog_btn_sure").on('click', function () {
        $("body").find(".dialog_container").remove();
        callback();
    });
    $("body").append(tplObj);

}

$.toast = function (content, time) {
    $("body").find(".tips_text").remove();
    if (!content) {
        return;
    }
    var length = content.length * 14 + 20;
    var tplObj = $('<div class="tips_text" style="z-index:999;width:' + length + 'px;margin-left:-' + Math.round(length / 2) + 'px">' + content + '</div>');
    $("body").append(tplObj);
    if (isNaN(time)) {
        dialog_toast_maxTime = 100000;
    } else {
        dialog_toast_maxTime = time;
    }
    setTimeout("hideTips();", 500);
}
$.showIndicator = function () {
    if ($('.preloader-indicator-modal')[0]) return;
    $("body").append('<div class="preloader-indicator-overlay"></div><div class="preloader-indicator-modal"><span class="preloader preloader-white"></span></div>');
};
$.hideIndicator = function () {
    $('.preloader-indicator-overlay, .preloader-indicator-modal').remove();
};
$.showPreloader = function (title) {
    if ($(".modal-overlay")[0]) {
        return;
    }
    var titleStr = title ? title : "加载中...";
    $("body").append(' <div class="modal-overlay modal-overlay-visible">' +
        ' <div class="modal  modal-no-buttons modal-in" style="display: block; margin-top: -78px;">' +
        '<div class="modal-inner">' +
        '<div class="modal-title">' + titleStr + '</div>' +
        '<div class="modal-text">' +
        '<div class="preloader"></div></div></div' +
        '<div class="modal-buttons "></div></div>' +
        '</div>');

}
$.hidePreloader = function () {
    $(".modal-overlay").remove();
};
$.showActionSheet=function(arr,callback){
        if( !isArray(arr)||arr.length==0){
              return;
        }
    var tplObjStr='<div class="dialog_container">' +
        '<div class="list_dialog_content">' +
        '<ul>';

        if(arr.length==1){
            tplObjStr+='<li class="list_dialog_firstli list_dialog_bottomli" index="0">'+arr[0]+'</li>';
        }else{
            for(var i=0;i<arr.length;i++){
                if(i==0){
                    tplObjStr+='<li class="list_dialog_firstli" index="'+i+'">'+arr[i]+'</li>';
                }else if(i==arr.length-1){
                    tplObjStr+='<li class="list_dialog_bottomli" index="'+i+'">'+arr[i]+'</li>';
                }else{
                    tplObjStr+='<li index="'+i+'">'+arr[i]+'</li>';
                }
            }
        }
    tplObjStr+= '</ul>' +
        '<p class="list_dialog_cancle">取消</p>' +
        '</div>' +
        '</div>';
    var telObj=$(tplObjStr);
    $(telObj).find("li").on('click',function(){
       $(".dialog_container").remove();
        var index=$(this).attr("index");
        callback(index);
    });
    $(telObj).find(".list_dialog_cancle").on("click",function(){
        $(".dialog_container").remove();
    });
   $("body").append(telObj);



}


