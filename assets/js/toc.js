(function() {
    'use strict';

    var tocObserver = null;
    var headerObserver = null;
    var currentNav = null;

    function cleanup() {
        if (currentNav) {
            currentNav.remove();
            currentNav = null;
        }
        if (tocObserver) {
            tocObserver.disconnect();
            tocObserver = null;
        }
        if (headerObserver) {
            headerObserver.disconnect();
            headerObserver = null;
        }
    }

    /**
     * 查找标题对应的 PHP 生成的锚点 ID
     * PHP exContent() 在每个标题前插入 <a id="toc_title{n}">
     */
    function resolveAnchorId(heading, index) {
        // 1. 查找标题前的 <a id="toc_title{n}"> 兄弟元素
        var prev = heading.previousElementSibling;
        while (prev) {
            if (prev.tagName === 'A' && prev.id && /^toc_title\d+$/.test(prev.id)) {
                return prev.id;
            }
            // 只向前找紧邻的兄弟，避免过远
            break;
        }

        // 2. 使用标题自身的 id
        if (heading.id) return heading.id;

        // 3. 兜底：生成 id
        heading.id = 'toc-h-' + index;
        return heading.id;
    }

    /**
     * TOC 自动滚动到当前激活项
     */
    function scrollTocToActive(nav, activeLi) {
        if (!activeLi) return;
        var navRect = nav.getBoundingClientRect();
        var liRect = activeLi.getBoundingClientRect();
        // 检查激活项是否在 sidebar 可视区外
        if (liRect.top < navRect.top || liRect.bottom > navRect.bottom) {
            activeLi.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    /* ========== 文章页 TOC ========== */
    function initPostTOC(postContent) {
        var headings = postContent.querySelectorAll('h2, h3, h4, h5');
        if (headings.length < 3) return;

        var nav = document.createElement('nav');
        nav.className = 'toc-sidebar';
        var ul = document.createElement('ul');

        headings.forEach(function(h, i) {
            var anchorId = resolveAnchorId(h, i);

            var li = document.createElement('li');
            li.className = 'toc-' + h.tagName.toLowerCase();
            li.setAttribute('data-toc-target', anchorId);

            var a = document.createElement('a');
            a.href = '#' + anchorId;
            a.textContent = h.textContent.trim();
            a.title = h.textContent.trim(); // tooltip 显示完整标题
            a.addEventListener('click', function(e) {
                e.preventDefault();
                // 优先滚动到 PHP 锚点元素（它有 top:-50px 偏移补偿）
                var target = document.getElementById(anchorId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });

            li.appendChild(a);
            ul.appendChild(li);
        });

        nav.appendChild(ul);
        postContent.parentElement.insertBefore(nav, postContent);
        currentNav = nav;

        // IntersectionObserver — 高亮当前可见章节
        var items = ul.querySelectorAll('li');
        var lastActiveId = null;
        tocObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var targetId = entry.target.id;
                    // 标题可能用自身 id，也可能用前面的锚点 id
                    // 查找对应的 li
                    var active = null;
                    items.forEach(function(item) {
                        item.classList.remove('active');
                    });

                    // 先尝试直接匹配
                    active = ul.querySelector('li[data-toc-target="' + targetId + '"]');

                    // 如果没找到，可能是因为 observer 监听的是标题本身，
                    // 但 data-toc-target 存的是 PHP 锚点 id
                    if (!active) {
                        // 通过标题查找其对应的锚点 id
                        var prev = entry.target.previousElementSibling;
                        if (prev && prev.tagName === 'A' && prev.id) {
                            active = ul.querySelector('li[data-toc-target="' + prev.id + '"]');
                        }
                    }

                    if (active) {
                        active.classList.add('active');
                        if (active.getAttribute('data-toc-target') !== lastActiveId) {
                            lastActiveId = active.getAttribute('data-toc-target');
                            scrollTocToActive(nav, active);
                        }
                    }
                }
            });
        }, {
            rootMargin: '-80px 0px -66% 0px'
        });

        headings.forEach(function(h) { tocObserver.observe(h); });

        // header 可见性检测：滚过 header 后才显示 TOC
        var siteHeader = document.querySelector('body > header');
        if (siteHeader) {
            headerObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        nav.classList.remove('is-visible');
                    } else {
                        nav.classList.add('is-visible');
                    }
                });
            });
            headerObserver.observe(siteHeader);
        }
    }

    /* ========== 自动检测页面类型并初始化 ========== */
    function init() {
        cleanup();

        var postContent = document.querySelector('.post-content');
        if (postContent) {
            initPostTOC(postContent);
        }
    }

    // 首次加载
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // PJAX 兼容
    function bindPjax() {
        if (window.jQuery) {
            jQuery(document).on('pjax:complete', init);
        }
    }
    if (window.jQuery) {
        bindPjax();
    } else {
        document.addEventListener('DOMContentLoaded', bindPjax);
    }
})();
