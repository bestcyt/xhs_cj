//是否是笔记页面
var current_url = window.location.href;
var pre_in_xiaohongshu = current_url.indexOf("https://www.xiaohongshu.com") === 0;
var pre_in_verify_risk=current_url.indexOf("https://www.xiaohongshu.com/web-login/captcha")===0;//验证过于频繁，请稍后重试
var pre_in_verify_qrcode_risk=current_url.indexOf("https://www.xiaohongshu.com/website-login/captcha")===0;//为了保护账号安全，提示app扫一扫
//是否开启监控
async function preIsMonitor(){
    if(!pre_in_xiaohongshu){
        return false;
    }
    var isEnabled=window.localStorage.getItem("isEnabled");
    if(isEnabled===true || isEnabled=="true" || isEnabled==1 || isEnabled=="1"){
        return true;
    }
    return false;
}

//如果是非验证页面就不加载图片和媒体信息
if(!pre_in_verify_risk && !pre_in_verify_qrcode_risk){
    preIsMonitor().then((check)=>{
        if(check){
            //在html header里面添加 <meta http-equiv="Content-Security-Policy" content="img-src 'none'; media-src 'none';"
            var meta = document.createElement('meta');
            meta.httpEquiv = 'Content-Security-Policy';
            meta.content = "img-src 'none'; media-src 'none';";
            document.head.appendChild(meta);
        }
    });
}