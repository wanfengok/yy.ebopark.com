/**
 * Created by pengjixiang on 17/3/10.
 */
var op=1;
var sid="0";
var eboPriceObj=null;
$(document).ready(function(){

    query();
    initQiniu();
});
/**
 * 修改商家时间,在售商品不允许修改上架时间
 */
function initOnlineTime(){
    $("input[name='f2']").click(function(){
        if($(this).filter(':checked').attr("value")=="0") {//立即销售
            $("#starttime").val(new Date().Format("yyyy-MM-dd hh:mm:ss"));
        }
        else{
            $("#starttime").val("2099-01-01 00:00:00");
            $("#endtime").val("2099-01-01 00:00:00");
        }
    });
    if(getGoodType()==0){
        $(".onlinetimec").hide();
        $("#starttime").attr("readonly","readonly");

    }
    if(getGoodType()!=0){
        jeDate({
            dateCell:"#starttime",
            isinitVal:true,
            isTime: true
        });
    }
    jeDate({
        dateCell:"#endtime",
        isinitVal:true,
        isTime: true
    });


}
/**
 * 修改操作权限管理
 * 1、选择供应商
 * 2、选择业务
 * 3、商品ID号
 * 4、商品型号：不能删除,可修改新增
 * 5、出行日期：不能修改删除，可新增,可修改限制数量,类型不能修改
 * 6、上下架管理：只能修改下架时间
 * 7、控件可以新增；
 */
function authManager(){
    var goodtype=getGoodType();
    if(goodtype==-1){
        alert("该商品已失效,不能修改");
        window.history.go(-1);
        return ;
    }
    if(goodtype==0){
        initOperate();
    }

}

function initOperate(){
    $("#merchantid").attr("disabled","disabled");
    $("#logictype").attr("disabled","disabled");

}
function getGoodType(){
    var starttime=$("#starttime").val();
    var endtime= $("#endtime").val();
    var now = (new Date()).Format("yyyy-MM-dd hh:mm:ss");
    if(starttime>now){
        //待售
        return 1;
    }
    if(endtime<now){
        //失效
        return -1;
    }
    if(starttime<now&&endtime>now){
        //在售
        return 0;
    }
}
function initQiniu(){
    $.getJSON('./getQiNiuKey',function(data){
        if(data.state==0){
            var key=data.result;
            initUploader(key,'lb');
            initUploader(key,'xj');
            initUploader(key,'spxq');
            initUploader(key,'cpts');
            initUploader(key,'fm');
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



Date.prototype.Format = function(fmt)
{ //author: meizz
    var o = {
        "M+" : this.getMonth()+1,                 //月份
        "d+" : this.getDate(),                    //日
        "h+" : this.getHours(),                   //小时
        "m+" : this.getMinutes(),                 //分
        "s+" : this.getSeconds(),                 //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S"  : this.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt))
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
        if(new RegExp("("+ k +")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
    return fmt;
}
function updategood(){
    var data="id="+$("#id").val();
    if($("#id").val().trim==""){
        alert("请先创建商品");
        return;
    }
    if($("#endtime").val()==""){
        $("#endtime").val("2099-1-1 00:00:00");
    }
    var starttime= $.trim($("#starttime").val());
    var endtime= $.trim($("#endtime").val());
    var now=(new Date()).Format("yyyy-MM-dd hh:mm:ss");
    if(getGoodType()==0&&endtime<now){
        //在售
        alert("下架时间必须大于或等于当前时间");
        return;
    }
    if(getGoodType()!=0){
        if(starttime<now||endtime<now){
            alert("上下架时间必须大于或等于当前时间");
            return;
        }
    }
    if(starttime!=""&&endtime!=""&&starttime>endtime){
        alert("下架时间必须大于或等于上架时间");
        return;
    }

    data+="&starttime="+encodeURIComponent(starttime);
    data+="&endtime="+encodeURIComponent($("#endtime").val());
    data+="&msgtailofmerchant="+encodeURIComponent($("#MsgTailOfMerchant").val());
    data+="&msgtailofuser="+encodeURIComponent($("#MsgTailOfUser").val());
    //分享信息
    var shareImg=$(".shareImgUrl").eq(0).attr("src");
    var shareTitle=$("#shareTitle").val();
    var shareContent=$("#shareContent").val();
    data+="&shareimg="+shareImg;
    data+="&sharetitle="+shareTitle;
    data+="&sharecontent="+shareContent;
    //订单备注信息
    data+="&orderdesctemp="+getSpecialFieldsOfOrderDetailPage();
    //订单控件
    var controls=getControls();
    if(getControlByControlType(controls,'10102')==null){
        layer.alert("还没有添加e泊特价控价");
        return
    }
    if(getControlByControlType(controls,'10201')==null){
        layer.alert("还没有添加购买数量控价");
        return
    }
    //if(getControlByControlType(controls,'10202')==null){
    //    layer.alert("还没有添加联系人控价");
    //    return
    //}
    //if(getControlByControlType(controls,'10203')==null){
    //    layer.alert("还没有添加手机号码控价");
    //    return
    //}
    data+="&control="+encodeURIComponent(JSON.stringify(controls));
    data+="&smstempid="+$("#SmsTempId").val();

    var ii=layer.msg("操作中……");
    $.ajax({
        type: "POST",
        url: "./updategood?ajax=1",
        dataType:"json",
        data:data,
        success: function (data) {
            layer.close(ii);
            if(data.state!=0){
                layer.alert(data.msg);
                return;
            }
           layer.alert("修改成功",function(){
               window.location.reload(true);
           });

        }
    });
}
function previewgood(){
    showQrcode();
}
function showQrcode(){
    $("#qrcode").empty();
    var id=GetQueryString("id");
    var qrcode_deduction = new QRCode(document.getElementById("qrcode"), {
        width: 300, //设置宽高
        height: 300
    });
    qrcode_deduction.makeCode("http://wx.ebopark.com/index.php/Home/Eshop/gooddetail?id="+id+"&distid=");
    layer.open({
        type: 1,
        title:false,
        closeBtn: 0,
        area: 'auto',
        shadeClose: true,
        area: '300px',
        content:$("#qrcode"),
    });
}
function getControlByControlType(controls,controltype){
    var control=null;
    for(var index=0;index<controls.length;index++){
        if(controls[index].ControlType==controltype){
            control= controls[index];
        }
    }
    return control;
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
    var initamount=$("#initamount").val();
    if($.trim(initamount)==""||isNaN(initamount)){
        initamount=0;
    }
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
    var memberdiscounttype=$("#memberdiscounttype").val();
    var memberdiscountval=$("#memberdiscountval").val();
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
    data+="&initamount="+initamount;
    data+="&originalprice="+encodeURIComponent($("#originalprice").val());
    data+="&saleprice="+encodeURIComponent($("#saleprice").val());
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
        url: "./updateBasenfo?ajax=1",
        dataType:"json",
        data:data,
        success: function (data) {
            if(data.state!=0){
                layer.alert(data.msg);
                layer.close(ii);
                return;
            }
            else{
                $("#detail2").css("display","");
                layer.alert("保存成功",function(){
                    window.location.reload(true);
                });
                layer.close(ii);
            }
        }
    });
}
function existDetail(){
    var tips=$("#tips").val();
    var spxq=$("#spxq").attr("src");
    var xj=$("#cpts").attr("src");
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
    $("#"+obj).attr("src","");
    $("#"+obj+"span").css("display","");
}
//
//function bindcategory(){
//    $.ajax({
//        type: "POST",
//        url: "./bindcategory?ajax=1",
//        dataType:"json",
//        data:"",
//        success: function (data) {
//            var array=data.data;
//            $("#categoryid").empty();
//            $("<option value='0'>请选择</option>").appendTo($("#categoryid"));
//            for(var i=0; i<array.length;i++) {
//                var item = array[i];
//                $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#categoryid"));
//            }
//
//            layer.close(ii);
//            $(".close").click();
//        }
//    });
//
//}

function openUrl(){
    var title="类目管理";
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
    if($("div[ctype='"+type+"']").is(":hidden")){
        appendToControlsContainer($("div[ctype='"+type+"']"));
    }
    $("div[ctype='"+type+"']").show();

    switch(Number(type)){
        case 1:addOutDate();break;
        case 3:addGoodsDesc();break;
        case 11:addPickingUpMethods();break;
    }
    $("div[ctype='"+type+"']").find(".title").html(title);
}

function openMark(){
    var title="地图标记";
    $.get("mark?name="+encodeURIComponent($("#address").val()),"",function(msg){
        (new $.zui.ModalTrigger({custom: msg,title:title})).show();
    })
}

function mark(point){
    $("#lat").val(point.lat);
    $("#lng").val(point.lng);
}

function addMark(obj){
    $(obj).parent().parent().insertBefore();
}

function deleteMark(obj){
    if($(obj).parent().parent().find("div").size()==1) {
        $(obj).parent().parent().parent().parent().remove();
        return;
    }
    $(obj).parent().remove();
}

function query(){
    $("#categoryid").empty();
    $("#merchantid").empty();
    $("#SmsTempId").empty();
    $("<option value='0'>请选择</option>").appendTo($("#categoryid"));
    $("<option value='0'>请选择</option>").appendTo($("#merchantid"));
    $("<option value='0'>请选择</option>").appendTo($("#SmsTempId"));

    for(var i=0; i<category.length;i++) {
        var item = category[i];
        $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#categoryid"));
    }
    for(var i=0; i<merchant.length;i++) {
        var item = merchant[i];
        $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#merchantid"));
    }
    for(var i=0; i<sms.length;i++) {
        var item = sms[i];
        $("<option value='" + item["id"] + "'>" + item["name"] + "</option>").appendTo($("#SmsTempId"));
    }



    var id=GetQueryString("id");
    op=2;
    var ii=layer.msg("加载中……");
    $.ajax({
        type: "POST",
        url: "./listgood?ajax=1",
        dataType:"json",
        data:"id="+id,
        success: function (data) {
            layer.close(ii);
            console.log(data);
            if(data.state!=0){
                //layer.alert(data.msg);
                return;
            }
            var item=data.data[0];
            $("#lng").val(item["lng"]);
            $("#lb").attr("src","");
            $("#xj").attr("src","");
            $("#lat").val(item["lat"]);
            $("#name").val(item["name"]);
            $("#id").val(item["id"]);
            $("#title").val(item["title"]);
            $("#merchantid").val(item["merchantid"]);
            $("#categoryid").val(item["categoryid"]);
            $("#logictype").val(item["logictype"]);
            $("#good_total").val(item["total"]>=99999999?"":item["total"]);
            $("input[name='f1']").each(function(obj){
                if($(this).attr("value")==item["expresstype"]){
                    $(this).attr("checked","checked");
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
            $("#starttime").val(item["starttime"]);
            $("#endtime").val(item["endtime"]);

            if(data.msg[0]){
                var msg=data.msg[0];
                $("#MsgTailOfMerchant").val(msg["msgtailofmerchant"]);
                $("#MsgTailOfUser").val(msg["msgtailofuser"]);
                $("#SmsTempId").val(msg["smstempid"]);
            }
            sid=item["id"];
            //更新商品规格
            initGoodType(data.item_list);
            //商品详情及产品特色图片
            initProductImg(data.img_list);
            //商品分享信息
            initShare(data.share);
            //订单备注详情
            initOrderDetaiDesc(item.orderdesctemp)
            //next();
            initControls(data.controls)
            authManager();
            initOnlineTime();

        }
    });
}
function initControls(controls){
    if(!$.isArray(controls)){
        return;
    }
    //var reverseArr=controls.reverse();
    var reverseArr=controls;
    for(var index=0;index<reverseArr.length;index++){
        var item=reverseArr[index];
        var jsonObj=JSON.parse(item.data);
        switch(item.controltype){
            //出行日期
            case "10101":initOutDate(jsonObj);break;
            case "10102":initEboPrices(jsonObj);break;
            case "10209":initGoodsDesc(jsonObj);break;
            case "10211":initPickMethodsWithData(jsonObj);break;
            case "10201":initGoodsLimit(jsonObj);break;
            case "10202":initNormalControl(5,jsonObj);break;
            case "10203":initNormalControl(6,jsonObj);break;
            case "10204":initNormalControl(7,jsonObj);break;
            case "10205":initNormalControl(8,jsonObj);break;
            case "10206":initNormalControl(9,jsonObj);break;
            case "10207":initNormalControl(10,jsonObj);break;
        }

    }



}
function appendToControlsContainer(control){
    $(control).remove();
    $(control).appendTo($("#controlsContainer"));
    $(control).show();

}
function initNormalControl(ctype,data){

    var obj= $("div[ctype="+ctype+"]");
    $("div[ctype="+ctype+"]").eq(0).find(".title").text(data.Name);
    appendToControlsContainer(obj);

}

function initGoodsLimit(data){
    if(data.EnableLimitUser){
        $("div[ctype=4]").find(".enableLimit").eq(0).attr("checked","checked");
    }
    $("div[ctype=4]").eq(0).find(".title").text(data.Name);
    $("div[ctype=4]").find(".limitNum").eq(0).val( data.LimitUserCnt==-1?"":data.LimitUserCnt);
    appendToControlsContainer( $("div[ctype=4]"));

}
function initPickMethodsWithData(data){


    //添加商品描述
    if(!$.isArray(data.FieldValues)){
        return;
    }
    $("div[ctype=11]").eq(0).find(".title").text(data.Name);
    for(var index=0;index<data.FieldValues.length;index++){

        var htmlStr=' <div class="fcontent pickUpContainer">'+
            '<input name="mark" type="text"  class="baseinput" value="'+data.FieldValues[index]+'"/>'+
            '<a title="删除" class="x" onclick="delPickUpMethod(this)">X</a>'+
            '</div>';
        $("div[ctype=11]").find(".fcontentRootContainer").eq(0).append($(htmlStr));
    }
    appendToControlsContainer( $("div[ctype=11]"));

}
function initGoodsDesc(data){

    //添加商品描述
    if(!$.isArray(data.FieldValues)){
        return;
    }
    $("div[ctype=3]").eq(0).find(".title").text(data.Name);
    for(var index=0;index<data.FieldValues.length;index++){

        var htmlStr=' <div class="fcontent goodsDescContainer">'+
            '<input name="mark" type="text"  class="baseinput" value="'+data.FieldValues[index]+'"/>'+
            '<a title="删除" class="x" onclick="delGoodsDesc(this)">X</a>'+
            '</div>';
        $("div[ctype=3]").find(".fcontentRootContainer").eq(0).append($(htmlStr));
    }

    appendToControlsContainer( $("div[ctype=3]"));

}
/**
 * 初始化出行日期
 * @param data
 */
function initOutDate(data){
    addOutDateWithData(data);
}
/**
 * 初始化e泊特价
 * @param data
 */
function initEboPrices(data){
    if($.isArray(data.Items)&&data.Items.length==0){
        return;
    }
    eboPriceObj=data;
    generateEboPricesWithData(data);

}
//生成e泊特价信息
function generateEboPricesWithData(data){
    $("div[ctype=2]").eq(0).find(".title").text(data.Name);
    var obj=$("div[ctype=2]").eq(0).find(".pricecontent").eq(0);
    $(obj).empty();
    var priceType=JSON.parse(getSubGoodTypeJson());
    var listStr='<div style="float: left">'+
        '<table class="special_table" cellpadding="0" cellspacing="0">'+
        '<tr>'+
        '<th>商品型号</th>';
    var outDateArr=data.Items[0].Prices;//getOutDate();
    if(outDateArr.length>0){
        for(var index=0;index<outDateArr.length;index++){
            var item=outDateArr[index];
            listStr+="<th>"+item.StartTime+" 至 "+item.EndTime+"</th>";
        }
    }
    else{
        listStr+="<th>商品售价</th>";
    }

    listStr+="<th>商家pid（用于与商户交互，自动)</th>";
    listStr+="<th>是否禁止当天下单</th></tr>";

    for(var index=0;index<priceType.length;index++){
        listStr+="<tr><td>"+decodeURIComponent(priceType[index].typename)+"</td>";
        if(outDateArr.length>0) {
            for (var i = 0; i < outDateArr.length; i++) {
                    var price=index<data.Items.length? data.Items[index].Prices[i].Price:"";
                    var unit=index<data.Items.length?data.Items[index].Prices[i].Unit:"";
                    var total=index<data.Items.length?(data.Items[index].Prices[i].Total>=99999999?"":data.Items[index].Prices[i].Total):"";
                listStr += '<td><label class="eboprice_label" for="price_' + index + '_' + i + '">价格</label><input id="price_' + index + '_' + i + '" type="text" point="price_' + index + '_' + i + '" class="priceItem eboprice_input" value="' +(price==undefined?"":price) + '"/>' +
                    '<label class="eboprice_label" for="unit_' + index + '_' + i + '">单位</label><input id="unit_' + index + '_' + i + '" type="text" point="unit_' + index + '_' + i + '" class="unitItem eboprice_input" value="' + (unit==undefined?"":unit) + '"/>' +
                    '<label class="eboprice_label" for="rp_' + index + '_' + i + '">库存</label><input id="rp_' + index + '_' + i + '" type="text" point="rp_' + index + '_' + i + '" class="rpItem eboprice_input" value="' +(total==undefined?"":total)+ '"/>' +
                    '</td>';
            }
        }
        else{
            var price=index<data.Items.length?data.Items[index].DefaultPrice:"";
            var unit=index<data.Items.length?data.Items[index].DefaultUnit:"";
            var total=index<data.Items.length?( data.Items[index].DefaultTotal >=99999999?"":data.Items[index].DefaultTotal):"";
            listStr += '<td><label class="eboprice_label" for="priceItem_-2">价格</label><input id="priceItem_-2" type="text" point="price_' + index + '_-2" class="priceItem eboprice_input" value="' +(price==undefined?"":price)+ '"/>' +
                '<label class="eboprice_label" for="unit_' + index + '_-2 ">单位</label><input id="unit_' + index + '_-2" type="text" point="unit_' + index + '_-2" class="unitItem eboprice_input" value="' +(unit==undefined?"":unit)+ '" />' +
                '<label class="eboprice_label" for="rp_' + index + '_-2">库存</label><input id="rp_' + index + '_-2" type="text" point="rp_' + index + '_-2" class="rpItem eboprice_input" value="' +(total==undefined?"":total)+ '"/>' +
                '</td>';

        }
        var pid=index<data.Items.length?(data.Items[index].ExtParams==undefined?"":data.Items[index].ExtParams):"";

        listStr+='<td><input id="'+index+'_-1" type="text" point="'+index+'_-1" value="'+pid+'" class="priceItem" style="width:100%;text-align:center;"></td>';
        listStr+='<td><input id="'+index+'_-2" type="checkbox" point="'+index+'_-2" value="'+pid+'" class="" '+((data.Items[index].ExcTd!=undefined&&data.Items[index].ExcTd)?"checked":"")+'><label for="'+index+'_-2">禁止</label></td></tr>';
    }
    listStr+="</table></div>";
    var pricecontent= $("div[ctype=2]").eq(0).find(".pricecontent").eq(0);
    $(pricecontent).append(listStr);
    appendToControlsContainer( $("div[ctype=2]"));





}
//生成e泊特价信息
function generateEboPrices(_self){

    var obj=$(_self).parents(".control").eq(0).find(".pricecontent").eq(0);
    $(obj).empty();
    var priceType=JSON.parse(getSubGoodTypeJson());
    if(priceType==""||priceType.length==0){
        alert("请先添加商品型号");
        return;
    }
    for(var index=0;index<priceType.length;index++){
        if(priceType[index].itemId==""){
            alert("商品型号有变化,请先保存");
            return;
        }
    }
    var listStr='<div style="float: left">'+
        '<table class="special_table" cellpadding="0" cellspacing="0">'+
        '<tr>'+
        '<th>商品型号</th>';
    var outDateArr=getOutDate();
    if(outDateArr.length>0) {
        for (var index = 0; index < outDateArr.length; index++) {
            var item = outDateArr[index];
            listStr += "<th>" + item.starttime + " 至 " + item.endtime + "</th>";
        }
    }
    else{
        listStr += "<th>商品售价</th>";
    }
    listStr+="<th>商家pid（用于与商户交互，自动)</th>";
    listStr+="<th>是否禁止当天下单</th></tr>";

    for(var index=0;index<priceType.length;index++){
        listStr+="<tr><td>"+decodeURIComponent(priceType[index].typename)+"</td>";
        if(outDateArr.length>0) {
            for (var i = 0; i < outDateArr.length; i++) {
                var price='';
                var  unit='';
                var rp=''
                if(eboPriceObj!=null&&eboPriceObj.Items[index]!=null&&eboPriceObj.Items[index].Prices[i]!=null){
                    price=eboPriceObj.Items[index].Prices[i].Price;
                    unit=eboPriceObj.Items[index].Prices[i].Unit;
                    rp=eboPriceObj.Items[index].Prices[i].Total;
                }
                if(price==undefined){
                    price='';
                }
                if(unit==undefined){
                    unit='';
                }
                if(rp==undefined||rp==-1){
                    rp='';
                }

                listStr += '<td><div class="single_item"><label class="eboprice_label" for="priceItem_'+i+'">价格</label><input id="priceItem_'+i+'" type="text" point="price_' + index + '_' + i + '" class="priceItem eboprice_input" value="'+price+'"></div>' +
                    '<div class="single_item"><label class="eboprice_label" for="unit_'+i+'">单位</label><input id="unit_'+i+'" type="text" point="unit_' + index + '_' + i + '" class="unitItem eboprice_input" value="'+unit+'"></div>' +
                    '<div class="single_item"><label class="eboprice_label" for="rp_'+i+'">库存</label><input id="rp_'+i+'" type="text" point="rp_' + index + '_' + i + '" class="rpItem eboprice_input" value="'+rp+'"></div>' +
                    '</td>';
            }
        }
        else{
            var defaultPrice=(eboPriceObj==null||index>=eboPriceObj.Items.length)?"":eboPriceObj.Items[index].DefaultPrice;
            var defaultUnit=(eboPriceObj==null||index>=eboPriceObj.Items.length)?"":eboPriceObj.Items[index].DefaultUnit;
            var defaultTotal=(eboPriceObj==null||index>=eboPriceObj.Items.length)?"":eboPriceObj.Items[index].DefaultTotal;
            var price=defaultPrice==undefined?"":defaultPrice;
            var unit=defaultUnit==undefined?"":defaultUnit;
            var total=defaultTotal==undefined?"":defaultTotal;

            listStr += '<td><div class="single_item"><label class="eboprice_label" for="priceItem_-2">价格</label><input id="priceItem_-2" type="text" point="price_' + index + '_-2" class="priceItem eboprice_input" value="'+price+'" /></div>' +
                '<div class="single_item"><label class="eboprice_label" for="unit_-2">单位</label><input id="unit_-2" type="text" point="unit_' + index + '_-2" class="unitItem eboprice_input" value="'+unit+'"/></div>' +
                '<div class="single_item"><label class="eboprice_label" for="rp_-2">库存</label><input id="rp_-2" type="text" point="rp_' + index + '_-2" class="rpItem eboprice_input" value="'+total+'" /></div>' +
                '</td>';
        }
        listStr+='<td><input type="text" point="'+index+'_-1" class="priceItem" style="width:100%;" value="'+(eboPriceObj==null||index>=eboPriceObj.Items.length?"":(eboPriceObj.Items[index].ExtParams==undefined?"":eboPriceObj.Items[index].ExtParams))+'"></td>';
        listStr+='<td><input id="'+index+'_-2" type="checkbox" point="'+index+'_-2"  class="" ><label for="'+index+'_-2">禁止</label></td></tr>';
    }
    listStr+="</table></div>";
    var pricecontent= $(_self).parents(".control").find(".pricecontent").eq(0);
    $(pricecontent).append(listStr);




}
function addOutDateWithData(data){

    if($.isArray(data.Items)&&data.Items.length==0){
        return;
    }
    $("div[ctype=1]").eq(0).find(".title").text(data.Name);
    var outdateArr=[];
    for(var index=0;index<data.Items.length;index++){
        var id1="c11"+index+1;
        var id2="c11"+(index+2);
        var item=data.Items[index];
        var itemStr=' <div class="outdate">'+
            '<input id="'+id1+'" type="text" class="baseinput input1 starttime" value="'+item.StartTime+'" readonly="readonly"/>'+
            '<span> 至 </span>'+
            '<input id="'+id2+'" type="text" class="baseinput input1 endtime" value="'+item.EndTime+'" readonly="readonly"/>';
        if(index==0){
            //if(allowTodelGoodType()){
            //    itemStr+= '<select id="dayLimitType">';
            //}else{
            //    itemStr+= '<select id="dayLimitType" disabled>';
            //}
            itemStr+= '<select id="dayLimitType" disabled>';
            if(item.EnableDayLimit){
                itemStr+= '<option value="0">单日数量限制</option>' +
                    '<option value="1">总数量限制</option>' +
                    '</select>';

            }else{
                itemStr+= '<option value="1">总数量限制</option>' +
                    '<option value="0">单日数量限制</option>' +
                    '</select>';
            }
        }
        if(allowTodelGoodType()){
            itemStr+= '<a title="删除" class="x" onclick="deleteOutDate(this)">X</a>';
        }
        itemStr+=  '</div>';
        $("div[ctype=1]").find(".fcontent").eq(0).append($(itemStr));
        var obj={};
        obj.id1=id1;
        obj.id2=id2;
        outdateArr.push(obj);
    }
    appendToControlsContainer( $("div[ctype=1]"));
    //for(var index=0;index<outdateArr.length;index++){
    //    var item=outdateArr[index];
    //    //initJedate(item.id1,item.id2);
    //}
}
function initOrderDetaiDesc(orderFields){
    if(orderFields==""){
        return;
    }
    var fields=orderFields.split(",");
    if(!$.isArray(fields)&&fields.length==0){
        return;
    }
    refreshSelectedTypes(fields)
}

function initShare(shareObj){
    if(shareObj==""){
        return;
    }
    $("#fm").attr("src",shareObj.img);
    $("#shareTitle").val(shareObj.title);
    $("#shareContent").val(shareObj.content);


}
function initProductImg(imgList){
    if(!$.isArray(imgList)){
        return;
    }
    for(var index=0;index<imgList.length;index++){
        var item=imgList[index];
        if(item.type=="1"){
            $("#lb").attr("src",item.url);
        }
        if(item.type=="2"){
            $("#xj").attr("src",item.url);
        }
        if(item.type=="3"){
            //商品详情
            $("#spxq").attr("src",item.url)
        }
        if(item.type=="4"){
            //产品特色
            $("#cpts").attr("src",item.url)
        }
    }
}
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = decodeURIComponent(window.location.search).substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}

function allowDrop(ev,divdom)
{
    ev.preventDefault();
    return false;
}

var srcdiv = null;
function drag(ev,divdom)
{
    srcdiv=divdom;
    ev.dataTransfer.setData("text/html",divdom.innerHTML);
}

function drop(ev,divdom)
{
    ev.preventDefault();
    if(srcdiv != divdom){
        srcdiv.innerHTML = divdom.innerHTML;
        divdom.innerHTML=ev.dataTransfer.getData("text/html");
        var ctype=$(srcdiv).attr("ctype");
        $(srcdiv).attr("ctype",$(divdom).attr("ctype"));
        $(divdom).attr("ctype",ctype)
    }
}
function initGoodType(list_item){
    if(!$.isArray(list_item)){
        return;
    }
    for(var index=0;index<list_item.length;index++){
        var item=list_item[index];
        addGoodTypeWithData(item);
    }

}
function addGoodTypeWithData(item){

    var itemAmount=$("#good_sub_type_c").find(".item").length;
    if(isNaN(itemAmount)){
        return;
    }
    var currentIndex=itemAmount+1;
    var itemStr=' <div class="item" id="item'+currentIndex+'" itemid="'+item.id+'">'+
        '<div class="good_sub_type">'+
        '<input placeholder="型号名称" type="text" class="baseinput fl"  id="typename'+currentIndex+'" value="'+decodeURIComponent(item.name)+'"/>'+
            //'<label for="repertory'+currentIndex+'" style="margin-left:10px;" class="fl">库存</label><input id="repertory'+currentIndex+'" type="text" class="repertory baseinput fl" style="width:50px;" value="'+item.total+'"/>'+
        '<label for="realPrice'+currentIndex+'" style="margin-left:10px;" class="fl">商品结算价</label><input id="realPrice'+currentIndex+'" type="text" class="realPrice baseinput fl" style="width:50px;" value="'+item.costprice+'"/>';
    if(allowTodelGoodType()){
        itemStr+= '<a href="javascript:void(0);"  class="delGoodType" style="margin-left:5px;float:left;height:25px;line-height: 25px;">删除</a>';
    }
    itemStr+= '</div>'+
        '<div style="clear:both;"></div>'+
        '</div>';
    $("#good_sub_type_c").append($(itemStr));
    $(".delGoodType").unbind();
    $(".delGoodType").on("click",function(){
        $(this).parents(".item").eq(0).remove();
    });

}
function allowTodelGoodType(){
    if(getGoodType()==1){
        //待售
        return true;
    }
    return false;
}
//添加商品型号
function  addGoodType(){
    var itemAmount=$("#good_sub_type_c").find(".item").length;
    if(isNaN(itemAmount)){
        return;
    }
    var currentIndex=itemAmount+1;
    var itemStr=' <div class="item" id="item'+currentIndex+'" itemid="">'+
        '<div class="good_sub_type">'+
        '<input placeholder="型号名称" type="text" class="baseinput fl"  id="typename'+currentIndex+'"/>'+
            //'<label for="repertory'+currentIndex+'" style="margin-left:10px;" class="fl">库存</label><input id="repertory'+currentIndex+'" type="text" class="repertory baseinput fl" style="width:50px;"/>'+
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
        return '[]';
    }
    if(itemAmount==0){
        return '[]';
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
function getOutDate(){
    if($("div[ctype=1]").is(":hidden")){
        return [];
    }
    var objArr=$("div[ctype=1]").find(".outdate");

    var outDateArr=[];
    for(var index=0;index<objArr.length;index++){
        var outDateObj=objArr[index];
        var obj={};
        obj.starttime=$(outDateObj).find(".starttime").eq(0).val();
        obj.endtime=$(outDateObj).find(".endtime").eq(0).val();
        obj.singleIDLimitCheckbox=$(outDateObj).find(".singleIDLimitCheckbox").eq(0).is(":checked")?1:0;
        obj.totalLimitCheckbox=$(outDateObj).find(".totalLimitCheckbox").eq(0).is(":checked")?1:0;
        obj.singleIDLimit=$(outDateObj).find(".singleIDLimit").eq(0).val();
        obj.totalLimit=$(outDateObj).find(".totalLimit").eq(0).val();
        if($.trim(obj.starttime)==""|| $.trim(obj.endtime)==""){
            continue;
        }
        outDateArr.push(obj);
    }
    return outDateArr;


}
//添加出行日期
function addOutDate(){
    var  outdateArr=$("div[ctype=1]").find(".fcontent").eq(0).find(".outdate");
    var id1="c11"+outdateArr.length+1;
    var id2="c11"+(outdateArr.length+2);
    var itemStr=' <div class="outdate">'+
        '<input id="'+id1+'" type="text" class="baseinput input1 starttime datepicker" />'+
        '<span> 至 </span>'+
        '<input id="'+id2+'" type="text" class="baseinput input1 endtime datepicker"/>';
    if(outdateArr.length==0){
        //if(!allowTodelGoodType()){
        //    itemStr+='<select id="dayLimitType" disabled>';
        //}else{
        //    itemStr+='<select id="dayLimitType">';
        //}
        itemStr+='<select id="dayLimitType">';
        itemStr+='<option value="0">单日数量限制</option>' +
            '<option value="1">总数量限制</option>' +
            '</select>';
    }
    itemStr+= '<a title="删除" class="x" onclick="deleteOutDate(this)">X</a>';
    itemStr+= '</div>';
    $("div[ctype=1]").show();
    $("div[ctype=1]").find(".fcontent").eq(0).append($(itemStr));
    initJedate(id1,id2);
}
function initJedate(id1,id2){

    $("#"+id1).on("click",function(){
        jeDate({
            dateCell:"#"+id1,
            isinitVal:false,
            isTime: false,
            format:"YYYY-MM-DD"
        });
    });
    $("#"+id2).on("click",function(){
        jeDate({
            dateCell:"#"+id2,
            isinitVal:false,
            isTime: false,
            format:"YYYY-MM-DD"
        });
    });
}

//删除出行日期
function deleteOutDate(_self){
    var listObj=$(_self).parents(".fcontent").find(".outdate");
    if(listObj.length==1){
        $(_self).parents(".control").eq(0).hide();
    }
    $(_self).parents(".outdate").eq(0).remove();

}

//添加商品描述
function addGoodsDesc(){
    var htmlStr=' <div class="fcontent goodsDescContainer">'+
        '<input name="mark" type="text"  class="baseinput"/>'+
        '<a title="删除" class="x" onclick="delGoodsDesc(this)">X</a>'+
        '</div>';
    $("div[ctype=3]").find(".fcontentRootContainer").eq(0).append($(htmlStr));


}
function addPickingUpMethods(){
    var htmlStr=' <div class="fcontent pickUpContainer">'+
        '<input name="mark" type="text"  class="baseinput"/>'+
        '<a title="删除" class="x" onclick="delPickUpMethod(this)">X</a>'+
        '</div>';
    $("div[ctype=11]").find(".fcontentRootContainer").eq(0).append($(htmlStr));

}
//删除商品描述
function delGoodsDesc(_self){
    if($(_self).parents(".fcontentRootContainer").eq(0).find(".goodsDescContainer").size()==1){
        $(_self).parents(".control").hide();
    }
    $(_self).parents(".goodsDescContainer").eq(0).remove();

}
function delPickUpMethod(_self){
    if($(_self).parents(".fcontentRootContainer").eq(0).find(".pickUpContainer").size()==1){
        $(_self).parents(".control").hide();
    }
    $(_self).parents(".pickUpContainer").eq(0).remove();
}
function selectTypes(){
    $(".order_detail_field li").hide();
    var controls=getControls();
    if(controls.length==0){
        alert("请先填写控价");
        return;
    }
    for(var index=0;index<controls.length;index++){
        var item=controls[index];
        var controlType=item.ControlType;
        $(".order_detail_field").find("li[ctype="+controlType+"]").eq(0).show();
    }
    var modal =new $.zui.ModalTrigger({title:"订单详情备注",custom: $("#selectParkContainer").find(".selectParkContainer").eq(0).html()});
    modal.show();
    $(".order_detail_btn").on("click",function(){
        var orderDetails = $(".modal-body .order_detail_field").find(".cx");
        var ctype=[];
        for(var index=0;index<$(orderDetails).length;index++){
            var item=orderDetails[index];
            if($(item).is(":checked")){
                ctype.push($(item).parent("li").attr("ctype"));
            }
        }
        refreshSelectedTypes(ctype);
        modal.close();
    });
}
function refreshSelectedTypes(ctype){
    $(".selected_types").empty();
    for(var index=0;index<ctype.length;index++){
        var type=ctype[index];
        var id=$(".order_detail_field").find("li[ctype="+type+"]").eq(0).find(".cx").eq(0).attr("desc");
        if(id==undefined||id==null){
            continue;
        }
        $(".selected_types").append("<li ctype='"+type+"'>"+id+"</li>");
    }
}

function getSpecialFieldsOfOrderDetailPage(){
    var arrObj=$(".selected_types li");
    if($(arrObj).length==0){
        return "";
    }
    var selectedDesc="";
    for(var index=0;index<$(arrObj).length;index++){
        if(index!=0){
            selectedDesc+=",";
        }
        var item=$(arrObj)[index];
        var desc=$(item).attr("ctype");
        selectedDesc+=desc;
    }
    return selectedDesc;
}
function getControls(){
    updateControlsOrder();
    var arrObj=$("div[name=control]");
    var controls=[];
    for(var index=0;index<arrObj.length;index++){
        var item=arrObj[index];
        if($(item).is(":hidden")){
            continue;
        }
        var ctype=$(item).attr("ctype");
        var controlObj=getControlByCtype(ctype);
        if(controlObj!=null){
            controls.push(controlObj);
        }

    }
    return controls;
}
function updateControlsOrder(){

    var controlArr=$("div[name=control]");
    for(var index=0;index<controlArr.length;index++){
        $(controlArr[index]).attr("order",index+1);
    }


}
function getControlByCtype(ctype){
    var obj=null;
    var order=$("div[ctype="+ctype+"]").attr("order");
    switch(ctype){
        //出行日期
        case "1":obj=getOutDateControlObj(10101,order);break;
        //e泊特价
        case "2":obj=getEboPrices(10102,order);break;
        //商品描述
        case "3":obj=getGoodDesc(10209,order);break;
        //购买数量
        case "4":obj=getGoodsNumLimit(10201,order);break;
        //联系人
        case "5":obj=getNormalInputControl(10202,order,5);break;
        //手机号
        case "6":obj=getNormalInputControl(10203,order,6);break;
        //身份证
        case "7":obj=getNormalInputControl(10204,order,7);break;
        //车牌号
        case "8":obj=getNormalInputControl(10205,order,8);break;
        //车型
        case "9":obj=getNormalInputControl(10206,order,9);break;break;
        //车位信息
        case "10":obj=getNormalInputControl(10207,order,10);break;break;break;
        //取票方式
        case "11":obj=getPickUpMethods(10211,order,11);break;
    }
    return obj;
}
//********************************出行日期**************************************//
function getOutDateControlObj(_controlType,_order){
    var outdates=$("div[ctype=1]").eq("0").find(".outdate");
    if(outdates.length==0){
        return null;
    }
    var obj={};
    obj.Name= $("div[ctype=1]").eq("0").find(".title").text();
    obj.Items=[];
    obj.Order=_order;
    obj.ControlType=_controlType;
    var limitType=$("#dayLimitType").val();
    for(var index=0;index<outdates.length;index++){
        var objItem={};
        var item=outdates[index];
        var startTime=$(item).find(".starttime").eq(0).val();
        var endTime=$(item).find(".endtime").eq(0).val();
        if($.trim(startTime)==""|| $.trim(endTime)==""){
            continue;
        }
        objItem.StartTime=startTime;
        objItem.EndTime=endTime;
        // 是否支持当日销售数量限制

        objItem.EnableDayLimit=(limitType==0);
        // 当前销售数量限制
        objItem.DayLimitCnt=0;
        // 是否支持总数量限制
        objItem.EnableTotalLimit= !objItem.EnableDayLimit;
        //总数量限制
        objItem.TotalLimitCnt=0;
        obj.Items.push(objItem);
    }
    if(obj.Items.length==0){
        return null;
    }
    return obj;

}
//********************************e泊特价**************************************//
function getEboPrices(_controlType,_order){
    var obj={};
    obj.Order=_order;
    obj.Name=$("div[ctype=2]").eq("0").find(".title").text();
    obj.ControlType=_controlType;
    var priceType=JSON.parse(getSubGoodTypeJson());
    if(priceType==""||priceType.length==0){
        //没有添加商品型号
        return null;
    }
    obj.Items=[];
    for(var index=0;index<priceType.length;index++){
        var itemObj={};
        itemObj.ItemId=priceType[index].itemId;
        //itemObj.DefaultPrice=priceType[index].realPrice;
        //$("input[point="+itemIndex+"_-2]").val();
        //pid
        itemObj.ExtParams=$("input[point="+index+"_-1]").val();
        itemObj.ExcTd=$("input[point="+index+"_-2]").is(":checked");
        //日期价格表
        itemObj.Prices=getListOfPrice(index);
        if(itemObj.Prices.length==0){
            var price=$("input[point=price_"+index+"_-2]").val()
            itemObj.DefaultPrice= $.trim(price)==""?0:price;
            itemObj.DefaultUnit=$("input[point=unit_"+index+"_-2]").val();
            var total=$.trim($("input[point=rp_"+index+"_-2]").val());
            itemObj.DefaultTotal=total==""?99999999:total;

        }
        obj.Items.push(itemObj);

    }
    return obj;

}
//获取具体商品对应的价格表
function getListOfPrice(itemIndex){
    var outDate=getOutDate();
    var prices=[];
    if(outDate.length==0){
        return prices;
    }
    for(var index=0;index<outDate.length;index++){
        var priceItem={};
        priceItem.StartTime=outDate[index].starttime;
        priceItem.EndTime=outDate[index].endtime;
        priceItem.Price=$("input[point=price_"+itemIndex+"_"+index+"]").val();
        priceItem.Unit=$("input[point=unit_"+itemIndex+"_"+index+"]").val();
        var total=$.trim($("input[point=rp_"+itemIndex+"_"+index+"]").val());
        priceItem.Total=total==""?99999999:total;

        prices.push(priceItem);
    }
    return prices;
}
//********************************商品描述**************************************//
//商品描述
function getGoodDesc(_controlType,_order){
    var obj={};
    obj.Order=_order;
    obj.Name=$("div[ctype=3]").eq("0").find(".title").text();
    obj.ControlType=_controlType;
    var goodsArr=$(".goodsDescContainer");
    if(goodsArr.length==0){
        return null;
    }
    var valueArr=[];
    for(var index=0;index<goodsArr.length;index++){
        var valueStr=$(goodsArr[index]).find(".baseinput").eq(0).val();
        if($.trim(valueStr)==""){
            continue;
        }
        valueArr.push($.trim(valueStr));
    }
    obj.FieldValues=valueArr;
    if(obj.FieldValues.length==0){
        return null;
    }
    return obj;
}
//********************************购买数量**************************************//
function getGoodsNumLimit(_controlType,_order){
    var obj={};
    obj.Order=_order;
    obj.Name=$("div[ctype=4]").eq("0").find(".title").text();
    obj.ControlType=_controlType;
    obj.EnableLimitUser=$("div[ctype=4]").find(".enableLimit").eq(0).is(":checked");
    obj.LimitUserCnt=$("div[ctype=4]").find(".limitNum").eq(0).val();
    if($.trim(obj.LimitUserCnt)==""){
        obj.LimitUserCnt=-1;
    }
    return obj;
}
//********************************取票方式**************************************//
function getPickUpMethods(_controlType,_order){
    var obj={};
    obj.Order=_order;
    obj.Name="取票方式";
    obj.ControlType=_controlType;
    var methodsContainerArr=$(".pickUpContainer");
    if(methodsContainerArr.length==0){
        return null;
    }
    var valueArr=[];
    for(var index=0;index<methodsContainerArr.length;index++){
        var valueStr=$(methodsContainerArr[index]).find(".baseinput").eq(0).val();
        if($.trim(valueStr)==""){
            continue;
        }
        valueArr.push($.trim(valueStr));
    }
    obj.FieldValues=valueArr;
    if(obj.FieldValues.length==0){
        return null;
    }
    return obj;
}
//********************************获取用户输入组件**************************************//
function getNormalInputControl(_controlType,_order,_ctype){
    var obj={};
    obj.Order=_order;
    obj.ControlType=_controlType;
    obj.Name=$("div[ctype="+_ctype+"]").eq("0").find(".title").text();
    return obj;
}

$.fn.onlyNum = function () {
    $(this).keypress(function (event) {
        var eventObj = event || e;
        var keyCode = eventObj.keyCode || eventObj.which;
        if ((keyCode >= 48 && keyCode <= 57))
            return true;
        else
            return false;
    }).focus(function () {
        //禁用输入法
        this.style.imeMode = 'disabled';
    }).change(function () {
        var val=$(this).val();
        if (!/^\d+$/.test(val)){
            $(this).val("0");
        }
    });
};



