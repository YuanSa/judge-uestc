//////////////////////  公评 通用 JS 动作  ////////////////////// 
/////////////////  Universal JS for Gong Ping  /////////////////
//
// 【更新时间 Lase Renew Time】
//      2019/02/02  (YYYY/MM/DD)
// 【目录 Contents】
//      [i11] 激活单选组件 - Active Radio Component
//      [i10] 阻止默认刷新 - Prevent default renew
//      [i09] 对象转换为字符串 - Turn object to string
//      [i08] POST请求 - Send POST AJAX
//      [i07] 登陆跳转 - Trun to only if login
//      [i06] 地址参数化 - Saftify URL
//      [i05] 首位有效 - The first available variable
//      [i04] 规范域名 - Normalize URL
//      [i03] 获取 url 参数 - Get parameters in URL
//      [i02] cookie 操作 - Manipulate cookies
//      [i01] 移动端识别 - Via mobile or PC?
//
"use strict";

(function main() {
    //activeRadio();
})();

// [i11] 激活单选组件 - Active Radio Component
function activeRadio() {
    let radioSets = document.getElementsByClassName("radioSet");
    for(radioSet of radioSets) {
        let radios = radioSet.children;
        for(radio of radios) {
            radio.defaultClassName = radio.className;
            radio.onclick = function() {
                for(let j = 0; j < radios.length; j++) {
                    radios[j].className = radios[j].defaultClassName;
                    radios[j].value = false;
                }
                this.className = "selected";
                this.value = true;
            }
        }
    }
}

// [i10] 阻止默认刷新 - Prevent default renew
document.onkeydown = function (event) {
    var e = event || window.event || arguments.callee.caller.arguments[0];
    if (e && e.keyCode == 116) {
        e.preventDefault();
        location.reload();
    }
};

// [i09] 对象转换为字符串 - Turn object to string
function obj2str(obj) {
    let key, str = "", notFirst = false;
    for(key in obj) {
        if(notFirst) str += "&";
        str += key + "=" + obj[key];
        notFirst = true;
    }
    return str;
}

// [i08] POST请求 - Send POST AJAX
function ajaxTo(method = "POST", url, para, callback, jsonFormat = true) {
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            if(jsonFormat) {
                //console.log(xmlhttp.responseText);
                callback(JSON.parse(xmlhttp.responseText));
            } else {
                callback({
                    state: "success",
                    text: xmlhttp.responseText
                });
            }
        } else if(xmlhttp.status >= 400) {
            callback({
                state: "fail",
                code: xmlhttp.status,
                text: xmlhttp.responseText
            });
        }
    }
    xmlhttp.open(method, method=="GET" ? url+"?"+obj2str(para) : url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(method=="POST" ? obj2str(para) : "");
}

// [i07] 登陆跳转 - Trun to only if login
function loginThenGoto(url, needed=true, autoJump=true) {
    let target, login = getCookie("login");
    if(needed) {
        if(login == "true") {
            target = url;
        } else {
            target = "user_login.html?to=" + urlParse(url);
        }
    } else {
        if(login == "true") {
            location.reload();
            return
        } else {
            target = url;
        }
    }
    if(autoJump) {
        location.href = target;
        return
    } else {
        return target;
    }
}

// [i06] 地址参数化 - Saftify URL
function urlParse(url) {
    url = url.replace(/&/g, "%26");
    url = url.replace(/\//g, "%2F");
    url = url.replace(/\?/g, "%3F");
    return url;
}
function urlUnparse(url) {
    url = url.replace(/%26/g, "&");
    url = url.replace(/%2F/g, "/");
    url = url.replace(/%3F/g, "?");
    return url;
}

// [i05] 首位有效 - The first available variable
function sequence() {
    let argument;
    for(argument of arguments) {
        if(argument) {
            return argument;
        }
    }
    return null;
}

// [i04] 规范域名 - Normalize URL
function normalizeUrl(){
    var needed = false;
    var PROTOCOL = window.location.protocol;
    var HOST = window.location.host;
    var PATHNAME = window.location.pathname;
    var SEARCH = window.location.search;
    if(PROTOCOL != "https:") {
        needed = true;
        PROTOCOL = "https:";
    }
    if(needed){
        location.href = PROTOCOL + "//" + HOST + "//" + PATHNAME + SEARCH;
        needed = false;
    }
    if(navigator.userAgent.indexOf("Edge")>-1) {
        AGENT = "Edge";
        needed = true;
    }
    if(navigator.userAgent.indexOf("compatible")>-1 && navigator.userAgent.indexOf("MSIE")>-1 && !(navigator.userAgent.indexOf("Opera")>-1)) {
        AGENT = "IE";
        needed = true;
    }
    if(needed) {
        alert("您的浏览器兼容性差，请更换浏览器。推荐使用Chrome或Firefox。");
        needed = false;
    }
}

// [i03] 获取URL参数 - Get parameters in URL
function getUrlPara(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null) {
        return decodeURIComponent(r[2]);
    } else {
        return null;
    }
}

// [i02] Cookie 操作 - Manipulate cookies
function setCookie(c_name, value, expiredays){
    var exdate = new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie = c_name+"="+encodeURIComponent(value)+((expiredays==null)?"":";expires="+exdate.toGMTString()+";path=/");
}
function getCookie(c_name){
    if (document.cookie.length>0) {
        var c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) { 
            c_start = c_start+c_name.length+1;
            var c_end = document.cookie.indexOf(";",c_start);
            if (c_end == -1) {c_end = document.cookie.length;}
            return decodeURIComponent(document.cookie.substring(c_start,c_end));
        } 
    }
    return null;
}
function clearCookie(c_name){
    setCookie(c_name, "", 0);
}

// [i01] 移动端识别 - Via mobile or PC?
function deviceKind() {
    if(/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return "mobile";
    } else {
        return "pc";
    }
}