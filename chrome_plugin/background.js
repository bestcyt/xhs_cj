// 在background.js中维护弹窗状态
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    console.log('[Background] 收到消息:', request);

    // 模拟异步处理
    setTimeout(async () => {
        if (request.action !== "GET_VERSION" && request.action !== "setStorage" && request.action !== "getStorage" && request.action!== "getCookies") {
            sendResponse({
                status: "Received",
                echo: request.url,
                timestamp: Date.now()
            });
        }
        if (request.action === "getCookies") {
            chrome.cookies.getAll({
                domain: ".xiaohongshu.com",
            }, (cookies) => {
              sendResponse({ cookies });
            });
          }

        if (request.action === "setStorage") {
            chrome.storage.local.set({[request.key]: request.value});
            sendResponse({
                status: "success"
            });
        }
        if (request.action === "getStorage") {
            var tempValue = await chrome.storage.local.get(request.key);
            console.log(tempValue);
            if (!tempValue || tempValue[request.key] === undefined || tempValue[request.key] === null) {
                tempValue = {
                    [request.key]: false,
                }
            }
            sendResponse({
                value: tempValue[request.key]
            });
        }
        if (request.action === 'GET_VERSION') {
            sendResponse({
                version: chrome.runtime.getManifest().version
            });
        }
    }, 100);

    return true;
});


// 断网后重定向到离线页面，并在离线页面中检测网络状态，恢复后重定向到原页面
const OFFLINE_PAGE_PATH = chrome.runtime.getURL('offline.html');

// 监听网络错误事件
chrome.webNavigation.onErrorOccurred.addListener(details => {
    const networkErrors = [
        // 标准 Chrome 错误
        'net::ERR_INTERNET_DISCONNECTED',
        'net::ERR_PROXY_CONNECTION_FAILED',
        'net::ERR_CONNECTION_RESET',
        'net::ERR_NAME_NOT_RESOLVED',
        'net::ERR_ADDRESS_UNREACHABLE',

        // DNS 相关错误
        'DNS_PROBE_FINISHED_NO_INTERNET',
        'DNS_PROBE_FINISHED_BAD_CONFIG',

        // 跨浏览器兼容
        'NS_ERROR_UNKNOWN_HOST',          // Firefox
        'kCFErrorDomainCFNetwork error 2' // Safari
    ];

    if (networkErrors.includes(details.error)) {
        // 重定向到离线页
        chrome.tabs.update(details.tabId, {
            url: `${OFFLINE_PAGE_PATH}?url=` + encodeURIComponent(details.url)
        });
    }
});