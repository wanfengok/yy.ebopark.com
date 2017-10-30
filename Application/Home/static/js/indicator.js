var cities=[];
var parks=[]
var isRefresh=true;
var loading=false;
var city='';
var isFirst=true;
var lng='';
var lat='';

$(function(){
    //var geolocation = new BMap.Geolocation();
    //
    //geolocation.getCurrentPosition(function(r){
    //    if(this.getStatus() == BMAP_STATUS_SUCCESS){
    //        lng=r.point.lng;
    //        lat=r.point.lat;
    //
    //    }
    //    initPage();
    //},{enableHighAccuracy: true});
    initPage();


});
function getDistance(item){
    return "";
    //
    //var myPoint = new BMap.Point(lng,lat);  // 创建点坐标A--大渡口区
    //var pointB = new BMap.Point(item.lng,item.lat);
    //var distance= (map.getDistance(myPoint,pointB)).toFixed(0);
    //if(isNaN(distance)){
    //    return '';
    //}
    //if(distance>1000){
    //    distance=(distance/1000).toFixed(1);
    //    return distance+'km';
    //}
    //
    //return distance+'m';

}
function initPage(){
    //map = new BMap.Map("allmap");
    FastClick.attach(document.body);

    setListener();

    getData();

}

function setListener(){

    $(".cityList").click(function(event){
        event=event||window.event;
        event.stopPropagation();
    });
    //点击层外，隐藏这个层。由于层内的事件停止了冒泡，所以不会触发这个事件
    $(".page_content").click(function(e){
        $("#ic_monthly_guide_drop").attr('src',IMG_URL+'/ic_monthly_guide_drop_off.png');
        hideCityList();
    });
    $(".cityList").on('touchmove',function(event){
        event=event||window.event;
        event.stopPropagation();
    });
    //点击层外，隐藏这个层。由于层内的事件停止了冒泡，所以不会触发这个事件
    $(".page_content").on('touchmove',function(){
        $("#ic_monthly_guide_drop").attr('src',IMG_URL+'/ic_monthly_guide_drop_off.png');
        hideCityList();

    });

    $("#showCityList").on("click",function(event){
        event=event||window.event;
        event.stopPropagation();
        $("#ic_monthly_guide_drop").attr('src',IMG_URL+'/ic_monthly_guide_drop_on.png');
        showCityList();

    });



    $(document).on('infinite', '.infinite-scroll-bottom',function() {
        // 如果正在加载，则退出
        if (loading) return;
        loadMore();
    });

    $.init();

}
function showCityList(){

    $("#parklistC").toggle();


}
function hideCityList(){

    $("#parklistC").hide();

}
function stopLoading(){

    // 加载完毕，则注销无限加载事件，以防不必要的加载
    //$.detachInfiniteScroll($('.infinite-scroll'));
    // 删除加载提示符
    $('.infinite-scroll-preloader').remove();
}
function refresh(){
    isRefresh=true;
    parks=[];
    getDataByAddress();

}
function loadMore(){
    // 设置flag
    loading = true;
    isRefresh=false;
    if(isFirst){
        isRefresh=true;
        isFirst=false;
    }
    getDataByAddress();

}
/**
 * 根据地理位置信息获取数据
 */
function getDataByAddress(){

    if(isRefresh){
        $.showIndicator();
    }

    var url='/index.php/Home/ParkApi/getCanProStopParks?take=10&skip='+parks.length+"&city="+city;
    $.getJSON(url,function(data){
        $.hideIndicator();
        loading = false;
        if(!isRefresh){
            stopLoading();
        }
        if(data.state!=0){
            $.toast(data.msg);
            return;
        }
        //停车场遍历
        var parkTemp=data.result;
        if(isRefresh){
            parks=[];
        }
        parks.addAll(parkTemp);
        updateView(parkTemp);

    });

}
function getData(){
    var url='/index.php/Home/ParkApi/getCanProStopParksHome?take=10&skip=0';
    $.getJSON(url,function(data){
        loading = false;
        stopLoading();
        if(data.state!=0){
            $.toast(data.msg);
            return;
        }
        var  citiesTemp=data.result.cities;
        cities.addAll(citiesTemp);
        //城市遍历
        for(var i=0;i< data.result.cities.length;i++){
            var citys=data.result.cities;
            $(".cityList").append(' <li name="'+citys[i]+'">'+citys[i]+'</li>');

        }
        $(".cityList li").on('click',function(){
            $("#ic_monthly_guide_drop").attr('src',IMG_URL+'/ic_monthly_guide_drop_off.png');
            hideCityList();
            city=$(this).attr('name');
            updateCity();
            refresh();
        });
        //停车场遍历
        var parkTemp=data.result.parks;
        city=data.result.defaultcity;
        updateCity();
        updateView(parkTemp);

    });

}
function updateCity(){
    $("#city").text(city==''?'全部':city);
}
function updateView(parkTemp){

    if(isRefresh){
        $(".normalLi").remove();
    }
    for(var i=0;i<parkTemp.length;i++){
        var park=parkTemp[i];
        $(".parklist_ul").append('<li class="normalLi" style="padding:0px 22px;"><img src="'+IMG_URL+'/parking_icon.png" style="margin-right:10px;"/><div class="li_parkname">' +
            '<p class="li_parkname" style=""><span style="float:left;">'+park.parkname+'</span><span style="float:right;">'+getDistance(park)+'</span></p>' +
             '<br/>'+
            '<p class="li_parknamesec" style="float:left;">'+park.address+'</p></div></li>');
    }
    //容器发生改变,如果是js滚动，需要刷新滚动
    if(parkTemp.length==0&&isRefresh){
        $('.empty').show();
    }else{
        $('.empty').hide();
    }
    $.refreshScroller();
}