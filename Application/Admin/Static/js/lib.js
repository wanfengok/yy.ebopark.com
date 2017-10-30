
 // 对Date的扩展，将 Date 转化为指定格式的String
 // 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
 // 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
 // 例子：
 // (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
 // (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
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
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = decodeURIComponent(window.location.search).substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}
 Array.prototype.del = function (index) {
     if (isNaN(index) || index >= this.length) {
         return false;
     }
     for (var i = 0, n = 0; i < this.length; i++) {
         if (this[i] != this[index]) {
             this[n++] = this[i];
         }
     }
     this.length -= 1;
 }
 Array.prototype.addAll=function(arr){
     if(!isArray(arr)){
         return;
     }
     for(var index=0;index<arr.length;index++){
         this.push(arr[index])
     }
     return this;

 }
 Array.prototype.clone=function(){

     var temparr=[];
     for(var index=0;index<this.length;index++){
         temparr.push(this[index])
     }
     return temparr;
 }
 function isArray(arr){
     return arr instanceof Array;
 }
 //是否存在指定函数
 function isExitsFunction(funcName) {
     try {
         if (typeof(eval(funcName)) == "function") {
             return true;
         }
     } catch(e) {}
     return false;
 }
 function setCookie(name, value) {
     var Days = 365;
     var exp = new Date();
     exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
     document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString()+";path=/";
 }
 //读取cookies
 function getCookie(name) {
     var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
     if (arr = document.cookie.match(reg))
         return unescape(arr[2]);
     else
         return null;
 }
