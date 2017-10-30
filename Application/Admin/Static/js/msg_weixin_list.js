/**
 * Created by pengjixiang on 16/6/12.
 */
function query() {
    var ii=layer.msg('加载中', {icon: 16});
    var code=$(".select_c").find(".active").eq(0).attr("code");
    $.getJSON("/index.php/Admin/MessageManager/getWxMessageList?code="+code,function(data){

        if(data.state!=0){
            layer.close(ii);
            layer.alert(data.msg);
            return;
        }
        $("#tbody").empty();
        $.each(data.data,function(index,item){
            var  htmlStr='<tr index="'+(index+1)+'" id="list'+(index+1)+'" dataid="'+item.id+'"  order="'+item.order+'">' +
                '<td style="text-align:center;">'+(index+1)+'</td>' +
                '<td style="text-align:center;">'+(item.title==null?"":item.title)+'</td>' +
            '<td style="text-align:center;">'+(item.remark==null?"":item.remark)+'</td>' +
                '<td style="text-align:center;">'+getTriggerDesc(item.triggertype)+'</td>' +
            '<td style="text-align:center;">'+getLimitStr(item)+'</td>' +
            '<td style="text-align:center;max-width:240px;overflow:hidden;">'+getRedirectAction(item)+'</td>' +
            '<td style="text-align:center;">'+item.operator+'</td>' +
            '<td style="text-align:center;">'+item.operatetime+'</td>' +
            '<td style="text-align:center;">'+item.starttime+'</td>' +
            '<td style="text-align:center;">'+item.endtime+'</td>' +
            '<td style="text-align:center;">'+(item.cnt==null?0:item.cnt)+'</td>' +
            '<td style="text-align:center;min-width: 120px;"  >'+getOrderOperateListHtml(data.data.length,index,item.remark,item)+'</td>' +
            '</tr>';
            $("#tbody").append(htmlStr);
        });
        initOrderOrDelListener();
        $(".layer_tips").click(function(){
            var tips=$(this).attr("tips");
            //layer.tips(tips);
            var textStr=$(this).text();
            if(textStr=="空"){
                return;
            }
            if(tips==""){
                tips="暂无数据";
            }
            layer.alert(tips);

        });
        layer.close(ii);
    });
}
$(function(){

    initListener();
    query();
});
function getTriggerDesc(type){
        var triggerDesc="无";
        switch(type){
            case "901":triggerDesc="登录成功提醒";break;
            case "103":triggerDesc="入场通知";break;
            case "106":triggerDesc="车辆出场提醒";break;
            case "801":triggerDesc="优惠返还通知";break;
            case "601":triggerDesc="车牌找回提醒";break;
            case "602":triggerDesc="车牌找回成功";break;
            case "603":triggerDesc="车牌找回失败";break;
            case "1001":triggerDesc="上门洗车";break;
            case "1002":triggerDesc="门店洗车";break;
        }
    return triggerDesc;

}
/**
 * 获取跳转动作
 * @param item
 */
function getRedirectAction(item){

    if(item.jumpconfig==null||item.jumpconfig==""){

        return "无";
    }
    if(item.jumpconfig.indexOf("PersonalCenter/autopay")!=-1){

        return "自动支付";
    }
    if(item.jumpconfig.indexOf("PersonalCenter/balance")!=-1){

        return "我的余额";
    }
    if(item.jumpconfig.indexOf("Recorder/index")!=-1){

        return "交易记录";
    }
    if(item.jumpconfig.indexOf("CarnoMs/index")!=-1){

        return "车辆管理";
    }
    if(item.jumpconfig.indexOf("MonthlyCars/index")!=-1){

        return "月租服务";
    }
    if(item.jumpconfig.indexOf("Coupon/couponList")!=-1){

        return "我的优惠券";
    }
    if(item.jumpconfig.indexOf("Recharge/index")!=-1){

        return "充值";
    }
    if(item.jumpconfig.indexOf("ParkFee/parkfee")!=-1){

        return "临停缴费";
    }
    return item.jumpconfig;

}
/**
 * 获取指定范围
 */
function getLimitStr(item){

    if((item.limitcity==null||item.limitcity=="")&&(item.limitpark==null||item.limitpark=="")&&(item.limitmobile==null||item.limitmobile=="")){

        return "全部";
    }
    return "指定";

}
function getOrderOperateListHtml(total,index,remark,item){
    var deleteStr=remark=="默认消息"?'':'<span  class="operateBtn deleteBtn">删除</span>';
    var  htmlStr='';
    if(total==1){
        htmlStr='<span  class="operateBtn modifyBtn">修改</span>'+deleteStr;
    }
    else if(index==0){
        htmlStr='<span  class="operateBtn descending">降序</span><span  class="operateBtn modifyBtn">修改</span>'+deleteStr;
    }
    else if(index==total-1){
        htmlStr='<span class="operateBtn ascending">升序</span><span  class="operateBtn modifyBtn">修改</span>'+deleteStr;
    }
    else{
        htmlStr='<span class="operateBtn ascending">升序</span><span  class="operateBtn descending">降序</span><span  class="operateBtn modifyBtn">修改</span>'+deleteStr;
    }
    //是否显示添加为广告
    var jumpconfig=getRedirectAction(item);
    if(jumpconfig.indexOf("http")!=-1){
        htmlStr+=getAdvHtmlStr(item.show);
    }
    return htmlStr;
}
//返回添加广告的html
function getAdvHtmlStr(show){
    if(show=='1'){
        return ' <span class="operateBtn addToAdvList">设为广告</span>';
    }
    if(show=='0'){
        return ' <span class="operateBtn" style="color:gray;">已设为广告</span>';
    }
    return '';

}
function initListener(){

    $(".select_c li").click(function(){

        $(".select_c li").removeClass("active");
        $(this).addClass("active");
        query();
    });

}
function initOrderOrDelListener(){

    $(".ascending").click(function(){

        var order=$(this).parents("tr").eq(0).attr("order");
        var id=$(this).parents("tr").eq(0).attr("dataid");

        var index=$(this).parents("tr").eq(0).attr("index");
        var anotherOrder=$("#list"+(Number(index)-1)).attr("order");
        var anotherid=$("#list"+(Number(index)-1)).attr("dataid");
        exchageOrder(order,id,anotherOrder,anotherid);


    });
    $(".descending").click(function(){

        var order=$(this).parents("tr").eq(0).attr("order");
        var id=$(this).parents("tr").eq(0).attr("dataid");

        var index=$(this).parents("tr").eq(0).attr("index")
        var anotherOrder=$("#list"+(Number(index)+1)).attr("order");
        var anotherid=$("#list"+(Number(index)+1)).attr("dataid");

        exchageOrder(order,id,anotherOrder,anotherid);
    });
    $(".deleteBtn").click(function(){
        var id=$(this).parents("tr").eq(0).attr("dataid");
        layer.confirm("确认删除该条消息？",function(){

            deleteMsg(id);
        });


    });
    $(".modifyBtn").click(function(){

        var id=$(this).parents("tr").eq(0).attr("dataid");

        window.location.href="/index.php/Admin/MessageManager/wxMessageDetail?id="+id;

    });
    //设为广告
    $(".addToAdvList").click(function(){
        var id=$(this).parents("tr").eq(0).attr("dataid");
        var url='./wxAddToAdvList?id='+id;
        var ii=layer.msg("操作中……");
        $.getJSON(url,function(data){
            layer.close(ii);
            if(data.state!=0){
                layer.alert(data.msg);
                return
            }
            query();

        });
    });


}
function deleteMsg(id){

    var ii=layer.msg('操作中……', {icon: 16});
    $.getJSON('/index.php/Admin/MessageManager/deleteWxMessage?id='+id,function(data){
        layer.close(ii);
        if(data.state!=0){
            layer.alert(data.msg);
            return;
        }
        query();
    });

}
function exchageOrder(order,id,anotherOrder,anotherid){
    var ii=layer.msg('操作中……', {icon: 16});
    $.getJSON('/index.php/Admin/MessageManager/exchangeOrder?order='+order+"&anotherOrder="+anotherOrder+"&id="+id+"&anotherid="+anotherid,function(data){
        layer.close(ii);
        if(data.state!=0){
            layer.alert(data.msg);
            return;
        }
        query();
    });

}