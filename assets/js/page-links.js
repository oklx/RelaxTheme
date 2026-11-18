/**
 * 友情链接页面脚本
 * - Favicon 懒加载 + localStorage 缓存（90 天）
 * - 搜索过滤功能
 */
(function () {
    // ========== Favicon 缓存系统 ==========
    var FAV_CACHE_KEY = 'daydream_favicons';
    var FAV_TTL = 90 * 24 * 3600 * 1000; // 90 天

    function getFavCache() {
        try { return JSON.parse(localStorage.getItem(FAV_CACHE_KEY)) || {}; }
        catch (e) { return {}; }
    }

    function setFavCache(domain, url) {
        var cache = getFavCache();
        cache[domain] = { u: url, t: Date.now() };
        try { localStorage.setItem(FAV_CACHE_KEY, JSON.stringify(cache)); } catch (e) {}
    }

    function letterColor(str) {
        var h = 0;
        for (var i = 0; i < str.length; i++) h = str.charCodeAt(i) + ((h << 5) - h);
        return 'hsl(' + (Math.abs(h) % 360) + ',45%,55%)';
    }

    function letterAvatar(img) {
        var name = img.getAttribute('data-name') || img.getAttribute('data-domain') || '?';
        var ch = name.charAt(0).toUpperCase();
        var size = img.getAttribute('width') || 24;
        var fs = Math.round(size * 0.45);
        img.style.display = 'none';
        var span = document.createElement('span');
        span.textContent = ch;
        span.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;width:' + size + 'px;height:' + size + 'px;border-radius:50%;background:' + letterColor(name) + ';color:#fff;font-size:' + fs + 'px;font-weight:700;line-height:1;flex-shrink:0;';
        img.parentElement.insertBefore(span, img);
    }

    function loadFavicons() {
        var imgs = document.querySelectorAll('.lazy-favicon');
        if (!imgs.length) return;
        var cache = getFavCache();

        for (var i = 0; i < imgs.length; i++) {
            (function (img) {
                if (img.dataset.loaded === '1') return;
                var domain = img.getAttribute('data-domain');
                if (!domain) return;

                var c = cache[domain];
                if (c && (Date.now() - c.t < FAV_TTL)) {
                    img.src = c.u;
                    img.dataset.loaded = '1';
                    return;
                }

                img.dataset.loaded = '1';
                var proxyBase = (typeof __faviconProxy !== 'undefined') ? __faviconProxy : '';
                if (proxyBase) {
                    img.src = proxyBase + '?d=' + domain;
                    img.onload = function () { setFavCache(domain, img.src); };
                    img.onerror = function () { letterAvatar(img); };
                } else {
                    var src1 = 'https://favicon.im/' + domain + '?larger=true';
                    var src2 = 'https://www.google.com/s2/favicons?sz=64&domain=' + domain;
                    img.src = src1;
                    img.onload = function () { setFavCache(domain, img.src); };
                    img.onerror = function () {
                        img.onerror = function () { letterAvatar(img); };
                        img.onload = function () { setFavCache(domain, img.src); };
                        img.src = src2;
                    };
                }
            })(imgs[i]);
        }
    }

    // ========== 搜索过滤 ==========
    function initLinksFilter() {
        var input = document.getElementById('links-filter');
        var grid = document.getElementById('links-grid');
        if (!input || !grid) return;
        if (input.dataset.bound === '1') return;
        input.dataset.bound = '1';

        input.addEventListener('input', function () {
            var keyword = this.value.trim().toLowerCase();
            var cards = grid.querySelectorAll('.link-card');
            for (var i = 0; i < cards.length; i++) {
                var card = cards[i];
                var name = (card.getAttribute('data-name') || '').toLowerCase();
                var desc = (card.getAttribute('data-desc') || '').toLowerCase();
                if (!keyword || name.indexOf(keyword) !== -1 || desc.indexOf(keyword) !== -1) {
                    card.classList.remove('is-hidden');
                } else {
                    card.classList.add('is-hidden');
                }
            }
        });
    }

    // ========== 初始化 ==========
    function init() {
        loadFavicons();
        initLinksFilter();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    if (window.jQuery) {
        jQuery(document).on('pjax:complete pjax:end', function () { init(); });
    }
})();
