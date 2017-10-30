/**
 * Created by pengjixiang on 17/3/10.
 */
var op=1;
var sid="0";
$(document).ready(function(){

    $("#name").keyup(function(){
        var val=$(this).val();
        $("#id").val(ConvertPinyin(val));
    });
    query();
    initQiniu();
    addGoodType();

});

function initQiniu(){
    $.getJSON('./getQiNiuKey',function(data){
        if(data.state==0){
            var key=data.result;
            initUploader(key,'lb');
            initUploader(key,'xj');
            initUploader(key,'spxq');
            initUploader(key,'cpts');

        }
    });
}

function initUploader(key,id){
    var cpts = Qiniu.uploader({
        runtimes: 'html5,flash,html4',      // 上传模式,依次退化
        browse_button: id+'btn',         // 上传选择的点选按钮，**必需**
        uptoken : key, // uptoken 是上传凭证，由其他程序生成
        get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
        unique_names: true,              // 默认 false，key 为文件名。若开启该选项，JS-SDK 会为每个文件自动生成key（文件名）
        domain:'7xpcl7.com2.z0.glb.qiniucdn.com',
        //domain:'7xodpu.com1.z0.glb.clouddn.com',//test
        max_file_size: '100mb',             // 最大文件体积限制
        flash_swf_url: '__PUBLIC__/js/qubuy/Moxie.swf',  //引入 flash,相对路径
        max_retries: 3,                     // 上传失败最大重试次数
        chunk_size: '4mb',                  // 分块上传时，每块的体积
        auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传,
        init: {
            'FilesAdded': function(up, files) {
                plupload.each(files, function(file) {
                    // 文件添加进队列后,处理相关的事情
                });
            },
            'BeforeUpload': function(up, file) {
                // 每个文件上传前,处理相关的事情
                //$.showPreloader('上传中……');
            },
            'UploadProgress': function(up, file) {
                // 每个文件上传时,处理相关的事情
            },
            'FileUploaded': function(up, file, info) {
                //$.hidePreloader();
                var res = JSON.parse(info);
                var imgkey=res.key;
                var src="http://7xpcl7.com2.z0.glb.qiniucdn.com/"+res.key;
                $("#"+id).attr("src",src);
            },
            'Error': function(up, err, errTip) {
                //上传出错时,处理相关的事情
            },
            'UploadComplete': function() {
                //队列文件处理完毕后,处理相关的事情
            },
            'Key': function(up, file) {
                var key = "";
                // do something with key here
                return key
            }
        }
    });
}


function savegood(){
    var name=$.trim($("#name").val());
    if(name.length==0){
        alert("商品名称不能为空！");
        $("#name").focus();
        return false;
    }
    var title= $.trim($("#title").val());
    if(title.length==0){
        alert("商品标题不能为空！");
        $("#title").focus();
        return false;
    }
    if($("#id").val().trim==""){
        alert("商品ID不能为空");
        return false;
    }
    var goodType=getSubGoodTypeJson();
    if((JSON.parse(goodType)).length==0){
        alert("您还未添加商品型号!");
        return false;
    }
    var originalprice=$("#originalprice").val();
    var initamount=$("#initamount").val();
    if($.trim(initamount)==""||isNaN(initamount)){
        initamount=0;
    }
    var saleprice =$("#saleprice").val();
    //if($.trim(saleprice)==""){
    //    alert("商品售价不能为空");
    //    return false;
    //}
    var lb=  $("#lb").attr("src");
    var xj=$("#xj").attr("src");
    if($.trim(lb)==""){
        alert("列表图不能为空");
        return false;
    }
    if($.trim(xj)==""){
        alert("细节图不能为空");
        return false;
    }
   var memberdiscounttype= $("#memberdiscounttype").val();
    var memberdiscountval =$("#memberdiscountval").val();
    if(memberdiscounttype==1||memberdiscounttype==2){
        if(isNaN(memberdiscountval)||memberdiscountval==""){
            alert("请填写格式正确的会员优惠金额或折扣");
            return false;
        }
    }
    if(memberdiscounttype==1&&(memberdiscountval<0||memberdiscountval>1)){
        alert("请填写格式正确的会员优惠折扣信息,折扣范围:0 — 1");
        return false;
    }
    if(memberdiscounttype==2&&(memberdiscountval%1 !== 0)){
        alert("请填写格式正确的会员优惠折扣信息,直减只能输入整数");
        return false;
    }
    var tel=$("#tel").val();
    if($.trim(tel)==""){
        alert("咨询电话不能为空");
        return false;
    }
    var data="name="+ encodeURIComponent(name);
    data+="&id="+encodeURIComponent($("#id").val());
//            data+="&title="+encodeURIComponent($("#title").val());
    data+="&merchantid="+encodeURIComponent($("#merchantid").val());
    data+="&categoryid="+encodeURIComponent($("#categoryid").val());
    data+="&logictype="+encodeURIComponent($("#logictype").val());
    data+="&title="+encodeURIComponent(title);
    data+="&expresstype="+$("input[name='f1']").filter(':checked').attr("value");
    data+="&initamount="+encodeURIComponent(initamount);
    data+="&originalprice="+encodeURIComponent(originalprice);
    data+="&saleprice="+saleprice;
    data+="&shophours="+encodeURIComponent($("#shophours").val());
    data+="&tel="+tel;
    data+="&lat="+encodeURIComponent($("#lat").val());
    data+="&lng="+encodeURIComponent($("#lng").val());
    data+="&address="+encodeURIComponent($("#address").val());
    data+="&tips="+encodeURIComponent($("#tips").val());
    data+="&orderdesctemp=";
    data+="&memberdiscounttype="+memberdiscounttype;
    data+="&memberdiscountval="+memberdiscountval;
    data+="&existsdetail="+existDetail();
    data+="&lb="+encodeURIComponent(lb);
    data+="&xj="+encodeURIComponent(xj);
    data+="&spxq="+encodeURIComponent($("#spxq").attr("src"));
    data+="&cpts="+encodeURIComponent($("#cpts").attr("src"));
    data+="&subgoodtypes="+encodeURIComponent(getSubGoodTypeJson());
    var ii=layer.msg("操作中……");
    $.ajax({
        type: "POST",
        url: "./savegood?ajax=1",
        dataType:"json",
        data:data,
        success: function (data) {
            if(data.state!=0){
                layer.alert(data.msg);
                layer.close(ii);
                return;
            }
            else{

             window.location.replace("./update?id="+$("#id").val());

            }
        }
    });
}
function existDetail(){
    var tips=$("#tips").val();
    var spxq=$("#spxq").attr("src");
    var xj=$("#xj").attr("src");
    if($.trim(tips)==""&& $.trim(spxq)==""&& $.trim(xj)==""){
        return 0;
    }
    return 1;
}
var imgsrc="";
function showimg(title,obj){
    var title="预览"+title;
    imgsrc=$("#"+obj).attr("src");
    if(imgsrc==""||imgsrc==null){
        alert("请先上传图片！");
        return;
    }
    $.get("img","",function(msg){
        (new $.zui.ModalTrigger({custom: msg,title:title})).show();
    });
    $("#"+obj+"span").css("display","none");
}

function delimg(obj){
    $("#"+obj).removeAttr("src");
    $("#"+obj+"span").css("display","");
}

function bindcategory(){
    $.ajax({
        type: "POST",
        url: "./bindcategory?ajax=1",
        dataType:"json",
        data:"",
        success: function (data) {
            var array=data.data;
            $("#categoryid").empty();
            $("<option value='0'>请选择</option>").appendTo($("#categoryid"));
            for(var i=0; i<array.length;i++) {
                var item = array[i];
                $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#categoryid"));
            }

            layer.close(ii);
            $(".close").click();
        }
    });

}

function openUrl(){
    var title="频道管理";
    $.post("category","",function(msg){
        (new $.zui.ModalTrigger({custom: msg,title:title})).show();
    })
}

function addAttr(){
    var title="添加属性值";
    $.get("control","",function(msg){
        (new $.zui.ModalTrigger({custom: msg,title:title})).show();
    })
}

function addAttrControl(type,title){
    //$("div[ctype='"+type+"']").css("display","block");
    $("div[ctype='"+type+"']").show();
    switch(Number(type)){
        case 1:addOutDate();break;
        case 3:addGoodsDesc();break;
        case 11:addPickingUpMethods();break;
    }
    $("div[ctype='"+type+"']").find(".title").html(title+"：");
}

function openMark(){
    var title="地图标记";
    $("#triggerModal").remove();
    $.get("mark?name="+encodeURIComponent($("#address").val()),"",function(msg){
        (new $.zui.ModalTrigger({custom: msg,title:title})).show();
    })
}

function mark(point){
    $("#lat").val(point.lat);
    $("#lng").val(point.lng);
}


function query(){
    $("#categoryid").empty();
    $("#merchantid").empty();
    $("<option value='0'>请选择</option>").appendTo($("#categoryid"));
    $("<option value='0'>请选择</option>").appendTo($("#merchantid"));

    for(var i=0; i<category.length;i++) {
        var item = category[i];
        $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#categoryid"));
    }
    for(var i=0; i<merchant.length;i++) {
        var item = merchant[i];
        $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#merchantid"));
    }

    $(".content_top .f2 div select").change(function(){
        var c=$("#categoryid").find("option:selected").text();
        var m=$("#merchantid").find("option:selected").text();
        var l=$("#logictype").find("option:selected").text();
        $(".f3 span").html("您当前选择的是："+c+" > "+m+" >"+l);
        if(c=="请选择"||m=="请选择"||l=="请选择") {
            $("#nextstep").attr("disabled","disabled");
            return;
        }
        $("#nextstep").attr("disabled",false);
    });

    var id=GetQueryString("id");
    if(!id) {
        op=1;
        $("#mytitle").html("添加商品&nbsp;&nbsp;&nbsp;");
    }
    else
    {
        op=2;
        $("#mytitle").html("修改商品&nbsp;&nbsp;&nbsp;");
        var ii=layer.msg("加载中……");
        $.ajax({
            type: "POST",
            url: "./listgood?ajax=1",
            dataType:"json",
            data:"id="+id,
            success: function (data) {
                layer.close(ii);
                if(data.state!=0){
                    //layer.alert(data.msg);
                    return;
                }
                var item=data.data[0];
                $("#name").val(item["name"]);
                $("#id").val(item["id"]);
                $("#title").val(item["title"]);
                $("#merchantid").val(item["merchantid"]);
                $("#categoryid").val(item["categoryid"]);
                $("#logictype").val(item["logictype"]);

                $("input[name='f1']").each(function(obj){
                    if($(obj).attr("value")==item["expresstype"]){
                        $(obj).attr("checked","checked");
                    }
                });
                $("#initamount").val(item["initamount"]);
                $("#originalprice").val(item["originalprice"]);
                $("#saleprice").val(item["saleprice"]);
                $("#shophours").val(item["shophours"]);
                $("#tel").val(item["tel"]);
                $("#address").val(item["address"]);//lng lat
                $("#tips").val(item["tips"]);
                $("#memberdiscounttype").val(item["memberdiscounttype"]);
                $("#memberdiscountval").val(item["memberdiscountval"]);
                $("#endtime").val(item["endtime"]);


                if(data.msg[0]){
                    var msg=data.msg[0];
                    $("#MsgTailOfMerchant").val(msg["msgtailofmerchant"]);
                    $("#MsgTailOfUser").val(msg["msgtailofuser"]);
                }
                sid=item["id"];
                next();
            }
        });
    }
}

function next(){
    $(".content_top").css("display","none");
    $(".content_middle").css("display","");
}

//添加商品型号
function  addGoodType(){
    var itemAmount=$("#good_sub_type_c").find(".item").length;
    if(isNaN(itemAmount)){
        return;
    }
    var currentIndex=itemAmount+1;
    var itemStr=' <div class="item" id="item'+currentIndex+'" itemid="100">'+
        '<div class="good_sub_type">'+
        '<input placeholder="型号名称" type="text" class="baseinput fl"  id="typename'+currentIndex+'"/>'+
        '<label for="realPrice'+currentIndex+'" style="margin-left:10px;" class="fl">商品结算价</label><input id="realPrice'+currentIndex+'" type="text" class="realPrice baseinput fl" style="width:50px;"/>'+
        '<a href="javascript:void(0);"  class="delGoodType" style="margin-left:5px;float:left;height:25px;line-height: 25px;">删除</a>'+
        '</div>'+
        '<div style="clear:both;"></div>'+
        '</div>';
    $("#good_sub_type_c").append($(itemStr));
    $(".delGoodType").unbind();
    $(".delGoodType").on("click",function(){
        $(this).parents(".item").eq(0).remove();
    });


}
function getSubGoodTypeJson(){
    var itemAmount=$("#good_sub_type_c").find(".item").length;
    if(isNaN(itemAmount)){
        return "";
    }
    if(itemAmount==0){
        return "";
    }
    var objArr=$("#good_sub_type_c").find(".item");
    var typeList=[];
    for(var index=1;index<=objArr.length;index++){
        var typeObj={};
        typeObj.typename=encodeURIComponent($("#typename"+index).val());
        typeObj.realPrice=$("#realPrice"+index).val();
        typeObj.itemId=$(objArr[index-1]).attr("itemid");

        if(typeObj.typename==""||isNaN(typeObj.realPrice)){
            continue;
        }
        typeList.push(typeObj);
    }
    return JSON.stringify(typeList);
}




