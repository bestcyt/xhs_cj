// content_pre.js
if(window.location.href.indexOf("https://www.xiaohongshu.com") === 0){
    const script = document.createElement('script');
    script.src = chrome.runtime.getURL('content_no_media.js');
    (document.body || document.documentElement).appendChild(script);
}