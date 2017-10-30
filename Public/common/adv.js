var advObj = {};
advObj.setCookie = function (name, value) {
    var Days = 365;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString()+";path=/";

}
advObj.getCookie=function (name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}
advObj.uuidFast = function () {
    var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
    var chars = CHARS, uuid = new Array(36), rnd = 0, r;
    for (var i = 0; i < 36; i++) {
        if (i == 8 || i == 13 || i == 18 || i == 23) {
            uuid[i] = '-';
        } else if (i == 14) {
            uuid[i] = '4';
        } else {
            if (rnd <= 0x02) rnd = 0x2000000 + (Math.random() * 0x1000000) | 0;
            r = rnd & 0xf;
            rnd = rnd >> 4;
            uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
        }
    }
    return uuid.join('');
}
advObj.getQueryString = function (name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return r[2];
    return null;
}
advObj.createElementAndRequest = function (mark,_adv_token) {
    if (mark == null || mark == undefined) {
        mark = '';
    }
    //广告token
    var adv_token = _adv_token;
    var usertoken = advObj.getCookie("ebo_token");
    if (usertoken == null) {
        usertoken = advObj.uuidFast();
        advObj.setCookie("ebo_token", usertoken);
    }
    var adv_s = document.createElement("script");
    adv_s.src = 'http://yy.ebopark.com/index.php/Home/AdvStatistics/statistics?adv_token=' + adv_token + '&usertoken=' + usertoken + "&mark=" + mark;
    //adv_s.src = 'http://192.168.0.250:8030/index.php/Home/AdvStatistics/statistics?adv_token=' + adv_token + '&usertoken=' + usertoken + "&mark=" + mark;
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(adv_s, s);
}
/**
 * @param mark 备注
 * @param defaultStr adv_token 广告token
 */
function as(mark,adv_token) {
    try{
        if(adv_token==""){
            return;
        }
        advObj.createElementAndRequest(mark,adv_token);
    }catch (err){
    }
}