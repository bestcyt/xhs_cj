// 进度条动画
const progressBar = document.getElementById('progressBar');
let progress = 0;

const animateProgress = () => {
    progress = (progress + 2) % 100;
    progressBar.style.width = `${progress}%`;
    requestAnimationFrame(animateProgress);
};
animateProgress();

// 获取原始URL
const urlParams = new URLSearchParams(window.location.search);
const originalUrl = urlParams.get('url');
document.getElementById('originalUrl').textContent = `原页面: ${decodeURIComponent(originalUrl)}`;

// 重试按钮
document.getElementById('retryBtn').addEventListener('click', () => {
    window.location = originalUrl;
});

setInterval(async function () {
    window.location = originalUrl;
}, 10000);