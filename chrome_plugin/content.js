// content.js
if(window.location.href.indexOf("https://www.xiaohongshu.com") === 0){
    //获取小红书 web_session
    chrome.runtime.sendMessage({ action: "getCookies" }, (response) => {
        // 输出包含 HttpOnly 的完整 Cookie 信息
        response.cookies.forEach(cookie => {
            if(cookie.name=="web_session"){
                //写入到全局变量中
                var div = document.createElement("div");
                div.id = "xiaohongshu_web_session";
                div.innerHTML = cookie.value;
                div.style = `display:none;`;
                document.body.appendChild(div);
            }
        });
      });
    //导出js

    const script = document.createElement('script');
    script.src = chrome.runtime.getURL('content_body.js');
    (document.body || document.documentElement).appendChild(script);
}