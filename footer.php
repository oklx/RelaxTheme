<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<script>
(function(){
    function extractLanguage(codeEl){
        var cls = codeEl.getAttribute('class') || '';
        var match = cls.match(/language-([\w#+-]+)/i) || cls.match(/lang-([\w#+-]+)/i);
        return match ? match[1] : '';
    }

    function normalizeCode(codeEl){
        var text = codeEl.textContent;
        var trimmed = text.replace(/^\n/, '').replace(/\s+$/, '');
        if (trimmed !== text){
            codeEl.textContent = trimmed;
        }
        return trimmed;
    }

    function buildLines(count){
        var frag = document.createDocumentFragment();
        var total = count > 0 ? count : 1;
        for (var i = 1; i <= total; i++){
            var span = document.createElement('span');
            span.textContent = i;
            frag.appendChild(span);
        }
        return frag;
    }

    function setCopied(btn){
        var copyIcon = btn.querySelector('.icon-copy');
        var checkIcon = btn.querySelector('.icon-check');
        var text = btn.querySelector('.copy-text');
        if (copyIcon) copyIcon.style.display = 'none';
        if (checkIcon) checkIcon.style.display = 'block';
        if (text) text.textContent = '已复制';
        btn.classList.add('is-success');
        setTimeout(function(){
            if (copyIcon) copyIcon.style.display = 'block';
            if (checkIcon) checkIcon.style.display = 'none';
            if (text) text.textContent = '复制';
            btn.classList.remove('is-success');
        }, 2000);
    }

    function createCopyButton(raw){
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'code-card__copy';
        btn.innerHTML = '<svg class="icon-copy" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"></path></svg><svg class="icon-check" viewBox="0 0 24 24" aria-hidden="true" style="display:none;"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19l12-12-1.41-1.41z"></path></svg><span class="copy-text">复制</span>';
        btn.addEventListener('click', function(){
            var copyPromise = (navigator.clipboard && navigator.clipboard.writeText)
                ? navigator.clipboard.writeText(raw)
                : Promise.reject();
            copyPromise.catch(function(){
                var ta = document.createElement("textarea");
                ta.value = raw;
                document.body.appendChild(ta);
                ta.select();
                try{ document.execCommand("copy"); }catch(e){}
                document.body.removeChild(ta);
            }).finally(function(){ setCopied(btn); });
        });
        return btn;
    }

    function enhanceCodeBlock(pre){
        var code = pre.querySelector('code');
        if (!code || code.dataset.uiverseReady === '1') return;

        var lang = extractLanguage(code);
        var normalized = normalizeCode(code);
        if (typeof hljs !== 'undefined' && hljs.highlightElement){
            try{ hljs.highlightElement(code); }catch(e){}
        }
        code.dataset.uiverseReady = '1';

        var card = document.createElement('div');
        card.className = 'code-card';

        var header = document.createElement('div');
        header.className = 'code-card__header';

        var badge = document.createElement('div');
        badge.className = 'code-card__lang';
        if (lang){
            badge.textContent = lang;
        } else {
            badge.classList.add('is-empty');
            badge.setAttribute('aria-hidden', 'true');
        }

        var body = document.createElement('div');
        body.className = 'code-card__body';

        var lines = document.createElement('div');
        lines.className = 'code-card__lines';
        lines.appendChild(buildLines(normalized.split('\n').length));

        var newPre = document.createElement('pre');
        if (pre.getAttribute('class')) newPre.setAttribute('class', pre.getAttribute('class'));

        var newCode = document.createElement('code');
        if (code.getAttribute('class')) newCode.setAttribute('class', code.getAttribute('class'));
        newCode.innerHTML = code.innerHTML;
        newPre.appendChild(newCode);

        header.appendChild(badge);
        header.appendChild(createCopyButton(normalized));
        body.appendChild(lines);
        body.appendChild(newPre);
        card.appendChild(header);
        card.appendChild(body);

        pre.replaceWith(card);
    }

    function enhanceCodeBlocks(root){
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('pre > code').forEach(function(code){
            enhanceCodeBlock(code.parentElement);
        });
    }

    function decorateContent(root){
        if (!window.jQuery) return;
        var $root = root === document ? $(document) : $(root);
        $root.find("main .post-content img").addClass("img-fluid shadow rounded");
        $root.find("main blockquote").addClass("shadow rounded");
        $root.find(".aplayer").addClass("shadow rounded");
    }

    function initFancybox(root){
        if (!window.jQuery || !$.fancybox) return;
        var $root = root === document ? $(document.body) : $(root);
        $root.find('[data-fancybox=\"gallery\"]').fancybox({
            buttons: ["zoom", "slideShow", "fullScreen", "download", "thumbs", "close"],
            clickContent: function() {
                return "close";
            }
        });
    }

    function renderMath(root){
        if (typeof renderMathInElement !== 'function') return;
        var target = (root && root.nodeType === 1) ? root : document.body;
        renderMathInElement(
            target,
            {
                delimiters: [
                    {left: "$$", right: "$$", display: true},
                    {left: "$", right: "$", display: false},
                ]
            }
        );
    }

    function hydrate(root){
        decorateContent(root);
        enhanceCodeBlocks(root);
        initFancybox(root);
        renderMath(root);
    }

    document.addEventListener('DOMContentLoaded', function(){
        hydrate(document);
    });
    if (window.jQuery){
        $(document).on('pjax:complete', function() {
            var container = document.getElementById('pjax-container') || document;
            hydrate(container);
        });
    }
})();
</script>

</main>

<script>
    $(document).pjax('a[href^="<?php $this->options->siteUrl()?>"]:not(a[target="_blank"])', {
        container: '#pjax-container',
        fragment: '#pjax-container'
    });
    $(document).on('pjax:send',function() {
        // alert('开始加载');
    });
    $(document).on('pjax:complete', function() {   
        // alert('加载完成');
        // Aplayer 插件代码
        var len = APlayerOptions.length;
        for(var i=0;i<len;i++){
            APlayers[i] = new APlayer({
                element: document.getElementById('player' + APlayerOptions[i]['id']),
                narrow: false,
                preload: APlayerOptions[i]['preload'],
                mutex: APlayerOptions[i]['mutex'],
                autoplay: APlayerOptions[i]['autoplay'],
                showlrc: APlayerOptions[i]['showlrc'],
                music: APlayerOptions[i]['music'],
                theme: APlayerOptions[i]['theme']
            });
            APlayers[i].init();
        }
    });
</script>

<footer>
    <div class="container">
        <hr>
        <p>
            <?php if ($this->options->nisInfo != ""): ?>
            <a id="nis" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=<?php echo mb_substr($this->options->nisInfo, 5, 14) ?>" target="_blank">
                <?php echo $this->options->nisInfo ?>
            </a> | <?php endif; ?>
            <?php if ($this->options->icpInfo != ""): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank"><?php echo $this->options->icpInfo ?></a>
            <?php endif; ?>
        </p>
        <p>&copy; <?php echo date('Y');?> <?php $this->options->title();?> <i class="czs-heart"></i> <?php Typecho_Widget::widget('Widget_Stat')->to($stat); ?><?php $stat->publishedPostsNum() ?> Posts <?php allOfCharacters();?> Words crafted</p>
        <?php $this->options->footerCode(); ?>
    </div>
</footer>

<?php $this->footer(); ?>

</body>
</html>
