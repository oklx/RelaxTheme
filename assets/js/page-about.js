/**
 * 关于页面脚本
 * - Favicon 懒加载 + localStorage 缓存（90 天）
 * - GitHub 仓库 API + localStorage 缓存（24 小时）
 * - 站点运行天数计算
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

                // 有缓存且未过期 → 直接用
                var c = cache[domain];
                if (c && (Date.now() - c.t < FAV_TTL)) {
                    img.src = c.u;
                    img.dataset.loaded = '1';
                    return;
                }

                // 无缓存 → 加载 favicon
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

    // ========== GitHub 仓库 ==========
    var GH_TTL = 24 * 3600 * 1000; // 24 小时

    var LANG_COLORS = {
        'JavaScript': '#f1e05a', 'TypeScript': '#3178c6', 'Python': '#3572A5',
        'Java': '#b07219', 'Go': '#00ADD8', 'Rust': '#dea584',
        'C': '#555555', 'C++': '#f34b7d', 'C#': '#178600',
        'PHP': '#4F5D95', 'Ruby': '#701516', 'Swift': '#F05138',
        'Kotlin': '#A97BFF', 'Dart': '#00B4AB', 'Shell': '#89e051',
        'HTML': '#e34c26', 'CSS': '#563d7c', 'Vue': '#41b883',
        'Scss': '#c6538c', 'Lua': '#000080'
    };

    var STAR_SVG = '<svg viewBox="0 0 16 16"><path d="M8 .25a.75.75 0 01.673.418l1.882 3.815 4.21.612a.75.75 0 01.416 1.279l-3.046 2.97.719 4.192a.75.75 0 01-1.088.791L8 12.347l-3.766 1.98a.75.75 0 01-1.088-.79l.72-4.194L.818 6.374a.75.75 0 01.416-1.28l4.21-.611L7.327.668A.75.75 0 018 .25z"></path></svg>';
    var FORK_SVG = '<svg viewBox="0 0 16 16"><path d="M5 3.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm0 2.122a2.25 2.25 0 10-1.5 0v.878A2.25 2.25 0 005.75 8.5h1.5v2.128a2.251 2.251 0 101.5 0V8.5h1.5a2.25 2.25 0 002.25-2.25v-.878a2.25 2.25 0 10-1.5 0v.878a.75.75 0 01-.75.75h-4.5A.75.75 0 015 6.25v-.878zm3.75 7.378a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm3-8.75a.75.75 0 100-1.5.75.75 0 000 1.5z"></path></svg>';

    function filterRepos(all, includeList, excludeList, maxCount) {
        // 排除列表
        var pool = all;
        if (excludeList.length) {
            pool = pool.filter(function (r) {
                return excludeList.indexOf(r.name.toLowerCase()) === -1;
            });
        }

        // 默认隐藏 fork，但 include 列表中的 fork 仍然保留
        pool = pool.filter(function (r) {
            return !r.fork || includeList.indexOf(r.name.toLowerCase()) !== -1;
        });

        // 分离：必须包含 vs 其余
        var pinned = [];
        var rest = [];
        for (var i = 0; i < pool.length; i++) {
            if (includeList.indexOf(pool[i].name.toLowerCase()) !== -1) {
                pinned.push(pool[i]);
            } else {
                rest.push(pool[i]);
            }
        }

        // 其余按星数排序，填充剩余名额
        rest.sort(function (a, b) { return (b.stars || 0) - (a.stars || 0); });
        var remaining = Math.max(0, maxCount - pinned.length);
        var result = pinned.concat(rest.slice(0, remaining));

        // 最终按星数排序展示
        result.sort(function (a, b) { return (b.stars || 0) - (a.stars || 0); });
        return result;
    }

    function loadGitHubRepos() {
        var container = document.getElementById('github-repos');
        if (!container) return;

        var username = container.getAttribute('data-username');
        if (!username) return;

        var maxCount = parseInt(container.getAttribute('data-count'), 10) || 6;
        var includeRaw = (container.getAttribute('data-include') || '').toLowerCase();
        var excludeRaw = (container.getAttribute('data-exclude') || '').toLowerCase();
        var includeList = includeRaw ? includeRaw.split(',').map(function (s) { return s.trim(); }).filter(Boolean) : [];
        var excludeList = excludeRaw ? excludeRaw.split(',').map(function (s) { return s.trim(); }).filter(Boolean) : [];

        var cacheKey = 'daydream_github_' + username;

        // 检查 localStorage 缓存（24h TTL），缓存存全量数据，每次重新过滤
        try {
            var raw = localStorage.getItem(cacheKey);
            if (raw) {
                var obj = JSON.parse(raw);
                if (obj && obj.t && (Date.now() - obj.t < GH_TTL)) {
                    renderRepos(container, filterRepos(obj.d, includeList, excludeList, maxCount));
                    return;
                }
            }
        } catch (e) {}

        var url = 'https://api.github.com/users/' + encodeURIComponent(username) + '/repos?sort=stars&per_page=100&type=owner';

        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error(res.status);
                return res.json();
            })
            .then(function (repos) {
                // 缓存全量数据（含 fork），过滤交给 filterRepos
                var all = repos.map(function (r) {
                        return {
                            name: r.name, desc: r.description || '',
                            url: r.html_url, lang: r.language || '',
                            stars: r.stargazers_count || 0, forks: r.forks_count || 0,
                            fork: !!r.fork
                        };
                    });

                try { localStorage.setItem(cacheKey, JSON.stringify({ d: all, t: Date.now() })); } catch (e) {}
                renderRepos(container, filterRepos(all, includeList, excludeList, maxCount));
            })
            .catch(function () {
                container.innerHTML = '<div class="about-error">GitHub 仓库加载失败，请稍后刷新重试</div>';
            });
    }

    function renderRepos(container, repos) {
        if (!repos || repos.length === 0) {
            container.innerHTML = '<div class="about-error">暂无公开仓库</div>';
            return;
        }

        var html = '';
        for (var i = 0; i < repos.length; i++) {
            var r = repos[i];
            var langColor = LANG_COLORS[r.lang] || '#888';
            html += '<a class="repo-card" href="' + escHtml(r.url) + '" target="_blank" rel="noopener">';
            html += '<div class="repo-name">' + escHtml(r.name) + '</div>';
            html += '<div class="repo-desc">' + escHtml(r.desc || '暂无描述') + '</div>';
            html += '<div class="repo-meta">';
            if (r.lang) {
                html += '<span class="repo-lang"><span class="repo-lang-dot" style="background:' + langColor + '"></span>' + escHtml(r.lang) + '</span>';
            }
            html += '<span class="repo-stat">' + STAR_SVG + ' ' + r.stars + '</span>';
            html += '<span class="repo-stat">' + FORK_SVG + ' ' + r.forks + '</span>';
            html += '</div></a>';
        }
        container.innerHTML = html;
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // ========== 站点运行天数 ==========
    function calcSiteDays() {
        var el = document.getElementById('site-days');
        if (!el) return;
        var created = el.getAttribute('data-created');
        if (!created) return;
        var days = Math.floor((new Date() - new Date(created)) / (1000 * 60 * 60 * 24));
        el.textContent = days;
    }

    // ========== 初始化 ==========
    function init() {
        calcSiteDays();
        loadFavicons();
        loadGitHubRepos();
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
