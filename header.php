<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<!-- Anti-flash (place at the very top of <head>) -->
<!-- Anti-flash + theme backplate sync (put this at very top of <head>) -->
<script>
(function () {
  var root = document.documentElement;

  function currentMode() {
    var saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
    if (saved === 'light' || saved === 'dark') return saved;
    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  }

  // 创建 / 获取 meta 与 style
  var meta = document.querySelector('meta[name="theme-color"]');
  if (!meta) { meta = document.createElement('meta'); meta.name = 'theme-color'; document.head.appendChild(meta); }
  var backplate = document.getElementById('anti-flash');
  if (!backplate) { backplate = document.createElement('style'); backplate.id = 'anti-flash'; document.head.appendChild(backplate); }

  function colorFor(mode){ return mode === 'dark' ? '#0b1220' : '#ffffff'; }

  // 同步底色（html/body 背景 + meta theme-color）
  function applyBackplate(mode){
    var c = colorFor(mode);
    meta.content = c;
    backplate.textContent =
      'html, body { background:'+ c +' !important; }' +
      'html.no-theme-transition *, html.no-theme-transition *::before, html.no-theme-transition *::after { transition:none !important; }';
  }

  // 初始：锁定主题 & 禁用过渡
  var initMode = currentMode();
  root.setAttribute('data-theme', initMode);
  root.classList.add('no-theme-transition');
  applyBackplate(initMode);

  // 首屏渲染后恢复过渡
  window.addEventListener('load', function () {
    requestAnimationFrame(function(){ root.classList.remove('no-theme-transition'); });
  });

  // 任何时刻只要 data-theme 改了，就更新底色
  var obs = new MutationObserver(function(muts){
    for (var m of muts) {
      if (m.type === 'attributes' && m.attributeName === 'data-theme') {
        applyBackplate(root.getAttribute('data-theme') || currentMode());
      }
    }
  });
  obs.observe(root, { attributes:true });

  // 系统深浅色切换时（当你未强制保存 theme 时生效）
  if (window.matchMedia) {
    var mq = window.matchMedia('(prefers-color-scheme: dark)');
    if (typeof mq.addEventListener === 'function') {
      mq.addEventListener('change', function () {
        if (!localStorage.getItem('theme')) {
          var mode = currentMode();
          root.setAttribute('data-theme', mode);
          applyBackplate(mode);
        }
      });
    }
  }

  // PJAX 期间暂时禁过渡，结束后 1 帧恢复
  window.addEventListener('pjax:send', function(){ root.classList.add('no-theme-transition'); });
  window.addEventListener('pjax:end', function(){
    requestAnimationFrame(function(){ root.classList.remove('no-theme-transition'); });
    // PJAX 后根据当前 data-theme 再同步一次底色
    applyBackplate(root.getAttribute('data-theme') || currentMode());
  });

  // 可选：如果你在别处只改了 localStorage('theme') 没改 data-theme，这里兜底监听 storage
  window.addEventListener('storage', function(e){
    if (e.key === 'theme') {
      var mode = currentMode();
      root.setAttribute('data-theme', mode); // 触发上面的 observer
    }
  });
})();
</script>





<?php
$headerCodeRaw = (string) $this->options->headerCode;
ob_start();
$this->header();
$headerOutput = ob_get_clean();
$hasMetaDescription = (
    stripos($headerCodeRaw, 'name="description"') !== false
    || stripos($headerCodeRaw, "name='description'") !== false
    || stripos($headerOutput, 'name="description"') !== false
    || stripos($headerOutput, "name='description'") !== false
);
$metaDescription = daydream_meta_description($this);
?>

    <meta charset="<?php $this->options->charset(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (!$hasMetaDescription): ?>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES); ?>">
    <?php endif; ?>
    <title><?php $this->archiveTitle(array(
            'category'  =>  '分类 %s 下的文章',
            'search'    =>  '包含关键字 %s 的文章',
            'tag'       =>  '标签 %s 下的文章',
            'author'    =>  '%s 发布的文章'
        ), '', ' - '); ?><?php $this->options->title(); ?></title>

    <!-- CHANGED: 网站图标写死为指定 .ico -->
    <link rel="apple-touch-icon" href="https://2bpic.oss-cn-beijing.aliyuncs.com/2025/10/13/68ecc5aa51eb4.ico">
    <link rel="shortcut icon" href="https://2bpic.oss-cn-beijing.aliyuncs.com/2025/10/13/68ecc5aa51eb4.ico" />
    <link rel="bookmark" href="https://2bpic.oss-cn-beijing.aliyuncs.com/2025/10/13/68ecc5aa51eb4.ico" type="image/x-icon"/>

	<!-- Pico.css -->
	<link rel="stylesheet" href="<?php $this->options->themeUrl('/assets/css/pico.min.css');?>">
    <!-- Daydream CSS -->
    <link type="text/css" href="<?php $this->options->themeUrl('/assets/css/style.css')?>" rel="stylesheet">
    <!-- Animate.css -->
    <link type="text/css" href="<?php $this->options->themeUrl('/assets/css/animate.min.css')?>" rel="stylesheet">
    <!-- Fancybox.css -->
    <link type="text/css" href="<?php $this->options->themeUrl('/assets/css/jquery.fancybox.min.css')?>" rel="stylesheet">
    <!-- KaTeX.css -->
    <link type="text/css" href="<?php $this->options->themeUrl('/assets/css/katex.min.css')?>" rel="stylesheet">
    <!-- Highlight.js CSS -->
    <link type="text/css" href="<?php $this->options->themeUrl('/assets/css/atom-one-dark.min.css')?>" rel="stylesheet">
    <!-- Caomei Icons CSS -->
    <link rel="stylesheet" href="<?php $this->options->themeUrl('/assets/css/caomei.css')?>">
    <!-- Google Fonts -->
    <link href="https://fonts.loli.net/css2?family=Noto+Serif+SC:wght@200;300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style><?php $this->options->cutsomCSS(); ?></style>

<style>
/* 亮色 / 默认 */
.post-content code:not(pre code),
.summary code:not(pre code){
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
  font-size: .95em;
  padding: .2em .45em;
  border-radius: .35em;
  background: rgba(2,6,23,.06);
  border: 1px solid rgba(2,6,23,.10);
  color: #2F81F7;           /* 更亮的蓝 */
  white-space: nowrap;
}

/* 暗色 */
[data-theme="dark"] .post-content code:not(pre code),
[data-theme="dark"] .summary code:not(pre code){
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.14);
  color: #9FC3FF;           /* 暗色下更亮一点 */
}

/* 链接里的行内代码，避免过度蓝 */
.post-content a code:not(pre code),
.summary a code:not(pre code){
  color: inherit;
  border-color: currentColor;
}
</style>

<style id="katex-scroll-global">
/* —— 独立公式容器：卡片化 & 不拉伸页面 —— */
.post-content .katex-scroll,
.page-content .katex-scroll,
main .katex-scroll {
  display:block !important;
  width:100% !important;
  max-width:100% !important;
  overflow-x:auto !important;
  overflow-y:hidden !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
  padding:10px 14px !important;
  margin:.9em 0 !important;
  border-radius:10px !important;
  background: linear-gradient(180deg, rgba(0,0,0,.03), rgba(0,0,0,.02)) !important;
  border: 1px solid rgba(2,6,23,.10) !important;
  box-shadow: 0 1px 2px rgba(0,0,0,.04) !important;
  text-align:center !important;            /* 短公式：居中 */
  touch-action: pan-x;                      /* 手势锁在容器内 */
  overscroll-behavior-x: contain;           /* 阻止页面级侧滑/回弹 */
}
[data-theme="dark"] .katex-scroll {
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.04)) !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  box-shadow: 0 1px 2px rgba(0,0,0,.25) !important;
}

/* —— 内层承载：短内容时居中；长内容从最左开始滚动 —— */
.katex-scroll-inner{
  display:inline-block !important;
  white-space:nowrap !important;
  min-width:max-content !important;
  text-align:initial !important;
}

/* —— 兜底：KaTeX 结构微调（不破坏其布局） —— */
.katex-scroll .katex-display{ 
  margin:0 !important; 
  display:inline-block !important;
}
.katex-scroll .katex-display .katex,
.katex-scroll .katex{
  display:inline-block !important;
  white-space:nowrap !important;
  min-width:max-content !important;
}

/* —— 精致滚动条（可留可去） —— */
.katex-scroll::-webkit-scrollbar{ height:6px }
.katex-scroll::-webkit-scrollbar-thumb{ background:rgba(2,6,23,.28); border-radius:3px }
[data-theme="dark"] .katex-scroll::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.28) }

/* —— 兜底：彻底禁掉页面层面的横向滚动 —— */
html,body{ overflow-x:hidden }
</style>
<style id="dark-strong-fix">
/* 黑夜模式：让加粗在正文/摘要里显著一些（不影响代码/公式） */
[data-theme="dark"] :where(.post-content,.summary,article,main) strong,
[data-theme="dark"] :where(.post-content,.summary,article,main) b{
  color: rgba(255,255,255,0.98) !important; /* 比正文更亮一点 */
  font-weight: 800 !important;              /* 更粗，Noto Serif SC 已加载 800/900 字重 */
  text-shadow: 0 0 0.4px rgba(255,255,255,0.25); /* 细微提亮轮廓，别太刺眼 */
}

/* 避免影响行内代码、代码块、KaTeX 公式内部 */
[data-theme="dark"] :where(pre,code,.katex,.katex-display, .katex-scroll) strong,
[data-theme="dark"] :where(pre,code,.katex,.katex-display, .katex-scroll) b{
  color: inherit !important;
  font-weight: inherit !important;
  text-shadow: none !important;
}

/* 链接里的 strong：跟随链接颜色，不要过度提亮 */
[data-theme="dark"] a strong,
[data-theme="dark"] a b{
  color: inherit !important;
  text-shadow: none !important;
}
</style>
<style id="katex-scroll-minimal">
/* —— 独立公式容器：极简款（无边框/无背景/无阴影） —— */
.post-content .katex-scroll,
.page-content  .katex-scroll,
main           .katex-scroll{
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  padding: 6px 0 !important;        /* 更轻的内边距 */
  margin: .9em 0 !important;
  text-align: center !important;    /* 短公式居中 */
  overflow-x: auto !important;
  overflow-y: hidden !important;
  -webkit-overflow-scrolling: touch;
  touch-action: pan-x;
  overscroll-behavior-x: contain;
}

/* 保持结构与滚动行为（不变） */
.katex-scroll .katex-display{ 
  margin: 0 !important;
  display: inline-block !important;
}
.katex-scroll .katex-display .katex,
.katex-scroll .katex{
  display: inline-block !important;
  white-space: nowrap !important;
  min-width: max-content !important;
}

/* 可选：更素的细滚动条 */
.katex-scroll::-webkit-scrollbar{ height: 4px }
.katex-scroll::-webkit-scrollbar-thumb{
  background: rgba(0,0,0,.22);
  border-radius: 2px;
}
[data-theme="dark"] .katex-scroll::-webkit-scrollbar-thumb{
  background: rgba(255,255,255,.22);
}

/* 兜底：页面不产生横向滚动 */
html, body { overflow-x: hidden; }
</style>


<style>
.post-content table{ width: 100%; border-collapse: collapse; }
.post-content th, .post-content td{ border:1px solid rgba(2,6,23,.12); padding:.5em .7em; }
.post-content table{ display:block; overflow-x:auto; border-radius: 8px; }
[data-theme="dark"] .post-content th, [data-theme="dark"] .post-content td{
  border-color: rgba(255,255,255,.15);
}
</style>
<style>
.post-content img{ border-radius: 10px; }
.post-content figure{ margin: 1em 0; text-align: center; }
.post-content figcaption{
  margin-top: .4em; font-size: .9em; opacity: .8;
}
</style>
<style>
.post-content blockquote,
.summary blockquote{
  margin: 1.2em 0; padding: .8em 1em;
  border-left: 4px solid #6aa6ff;
  background: rgba(2,6,23,.04);
  border-radius: 8px;
}
[data-theme="dark"] .post-content blockquote,
[data-theme="dark"] .summary blockquote{
  background: rgba(255,255,255,.06);
  border-left-color: #9fc3ff;
}
</style>
<style>
.post-content a { text-decoration: none; border-bottom: 1px solid transparent; }
.post-content a:hover { border-bottom-color: currentColor; }
</style>
<style>
#tableOfContents {
  border: 1px solid rgba(2,6,23,.08);
  border-radius: 10px;
  padding: 10px 14px;
  background: rgba(2,6,23,.03);
}
#tableOfContents ol { margin: .4em 0 0; padding-left: 1.1em; }
#tableOfContents li { margin: .2em 0; }
[data-theme="dark"] #tableOfContents {
  background: rgba(255,255,255,.05);
  border-color: rgba(255,255,255,.12);
}
</style>

<style id="read-progress-style">
/* 顶部阅读进度条容器（不挡点击） */
#read-progress{
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  height: 2px;                 /* 你要的 2px 细线 */
  pointer-events: none;
  z-index: 9999;
}

/* 实际进度条：用 scaleX 提高性能 */
#read-progress .bar{
  height: 100%;
  width: 100%;
  transform-origin: left center;
  transform: scaleX(0);
  transition: transform .1s linear, opacity .2s ease;
  opacity: .95;
  background: #2F81F7;         /* 亮色 */
}

/* 暗色主题下颜色稍亮一点 */
[data-theme="dark"] #read-progress .bar{
  background: #9FC3FF;
}
</style>
<style id="center-media">
/* ========== 图片默认居中 ========== */
/* 文章正文/摘要中的独立图片（含 fancybox 包裹的） */
.post-content img,
.summary img{
  display:block;
  max-width:100%;
  height:auto;
  margin:.75em auto;             /* 水平居中 */
  border-radius:10px;            /* 轻微圆角，可按需删 */
}

/* 如果图片被 fancybox 的 <a> 包裹，让 <a> 也居中承载 */
.post-content a[data-fancybox],
.summary a[data-fancybox]{
  display:block;
  width:fit-content;             /* 宽度跟随内容 */
  max-width:100%;
  margin:.75em auto;             /* 居中 */
}

/* 使用 <figure> 的图也一起美化与居中 */
.post-content figure,
.summary figure{
  margin:1em auto;
  text-align:center;
}
.post-content figcaption,
.summary figcaption{
  margin-top:.4em;
  font-size:.9em;
  opacity:.8;
}

/* ========== 表格默认居中，且不撑破 ========== */
/* 让表格以“内容宽度”为主，窄时居中，宽时自身可横向滚动 */
.post-content table,
.summary table{
  display:block;                 /* 允许自身滚动与 margin:auto 生效 */
  width:max-content;             /* 宽度按内容 */
  max-width:100%;                /* 不超过容器 */
  overflow-x:auto;               /* 宽了就横向滚动 */
  margin:.9em auto;              /* 居中 */
  border-collapse:collapse;
  border-radius:8px;             /* 轻微圆角（可选） */
}

/* 表格边线与内边距（保持简洁） */
.post-content th, .post-content td,
.summary th, .summary td{
  border:1px solid rgba(2,6,23,.12);
  padding:.5em .7em;
}
[data-theme="dark"] .post-content th,
[data-theme="dark"] .post-content td,
[data-theme="dark"] .summary th,
[data-theme="dark"] .summary td{
  border-color:rgba(255,255,255,.15);
}

/* 更细的横向滚动条（可选） */
.post-content table::-webkit-scrollbar,
.summary table::-webkit-scrollbar{ height:6px }
.post-content table::-webkit-scrollbar-thumb,
.summary table::-webkit-scrollbar-thumb{
  background:rgba(2,6,23,.28); border-radius:3px
}
[data-theme="dark"] .post-content table::-webkit-scrollbar-thumb,
[data-theme="dark"] .summary table::-webkit-scrollbar-thumb{
  background:rgba(255,255,255,.28)
}
</style>

<style id="more-pill">
/* 命中两种情况：已有 .more，或摘要里只有这一个链接 */
.summary a.more,
.summary > a:only-child{
  display:inline-flex;
  align-items:center;
  gap:.4em;
  padding:.46em .9em;
  border-radius:999px;
  font-size:.95em;
  line-height:1;
  text-decoration:none;
  border:1px solid rgba(2,6,23,.12);
  background: linear-gradient(180deg, rgba(2,6,23,.03), rgba(2,6,23,.02));
  color:#1f2c40;
  transition: background .2s ease, border-color .2s ease, box-shadow .2s ease, transform .15s ease;
}
[data-theme="dark"] .summary a.more,
[data-theme="dark"] .summary > a:only-child{
  border-color: rgba(255,255,255,.14);
  background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.06));
  color:#e6edf6;
}
/* 箭头装饰 */
.summary a.more::after,
.summary > a:only-child::after{
  content:"↗";
  font-size:.9em;
  opacity:.7;
  transform: translateY(-.5px);
  transition: transform .2s ease, opacity .2s ease;
}
/* hover/focus */
.summary a.more:hover,
.summary a.more:focus,
.summary > a:only-child:hover,
.summary > a:only-child:focus{
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(2,6,23,.08);
  border-color: rgba(2,6,23,.18);
}
[data-theme="dark"] .summary a.more:hover,
[data-theme="dark"] .summary a.more:focus,
[data-theme="dark"] .summary > a:only-child:hover,
[data-theme="dark"] .summary > a:only-child:focus{
  box-shadow: 0 6px 20px rgba(0,0,0,.35);
  border-color: rgba(255,255,255,.22);
}
/* 箭头轻微前移 */
.summary a.more:hover::after,
.summary > a:only-child:hover::after{ transform: translate(2px,-1px); opacity:.9; }

/* 与摘要段落的间距更匀称 */
.summary{ margin-bottom:.4rem; }
</style>

<style id="post-meta-pills">
/* 容器：轻边框、微渐变、可换行 */
.post-meta.sleek{
  display:flex; flex-wrap:wrap; align-items:center;
  gap:.6rem 1rem;
  padding:.9rem 1rem;
  margin:1.2rem 0 0;
  border:1px solid rgba(2,6,23,.08);
  border-radius:12px;
  background: linear-gradient(180deg, rgba(2,6,23,.02), rgba(2,6,23,.01));
}
[data-theme="dark"] .post-meta.sleek{
  border-color: rgba(255,255,255,.14);
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.04));
}

/* 小标题：低饱和度，不喧宾夺主 */
.post-meta .meta-group{ display:flex; flex-wrap:wrap; align-items:center; gap:.5rem .5rem; }
.post-meta .meta-title{
  font-size:.9em;
  opacity:.72;
  letter-spacing:.02em;
}

/* 分隔竖线 */
.post-meta .meta-sep{
  width:1px; height:1.25em;
  background: rgba(2,6,23,.12);
}
[data-theme="dark"] .post-meta .meta-sep{ background: rgba(255,255,255,.18); }

/* 胶囊标签 */
.meta-badge{
  display:inline-flex; align-items:center;
  padding:.28em .6em;
  border-radius:999px;
  font-size:.9em; line-height:1;
  text-decoration:none; color:inherit;
  border:1px solid rgba(2,6,23,.12);
  background: rgba(2,6,23,.04);
  transition: border-color .2s ease, background .2s ease, transform .15s ease, box-shadow .2s ease;
}
[data-theme="dark"] .meta-badge{
  border-color: rgba(255,255,255,.16);
  background: rgba(255,255,255,.08);
}

/* 悬停：微抬起 + 柔和阴影 */
.meta-badge:hover{
  transform: translateY(-1px);
  border-color: rgba(2,6,23,.20);
  box-shadow: 0 6px 14px rgba(2,6,23,.08);
}
[data-theme="dark"] .meta-badge:hover{
  border-color: rgba(255,255,255,.22);
  box-shadow: 0 8px 18px rgba(0,0,0,.45);
}
</style>

<style id="comments-overhaul">
/* —— 统一变量（亮/暗自动切换） —— */
:root{
  --fab-fg: #111827;
  --fab-bg: #ffffff;
  --fab-bd: rgba(2,6,23,.14);
  --fab-shadow: 0 10px 28px rgba(0,0,0,.12);
  --panel-bg: #ffffff;
  --panel-fg: #111827;
  --panel-bd: rgba(2,6,23,.12);
  --panel-hover: rgba(2,6,23,.06);
}

/* 暗色变量：作用于 <html data-theme="dark"> */
:root[data-theme="dark"], [data-theme="dark"]{
  --fab-fg: #e5e7eb;
  --fab-bg: #0f172a;
  --fab-bd: rgba(255,255,255,.20);
  --fab-shadow: 0 12px 32px rgba(0,0,0,.55);
  --panel-bg: #0b1220;          /* 深色纯底，去眩光 */
  --panel-fg: #e5e7eb;
  --panel-bd: rgba(255,255,255,.18);
  --panel-hover: rgba(255,255,255,.08);
}


/* —— 外层 —— */
#comments{ margin-top: 1.8rem; }
#comments .comments-title{
  margin: 0 0 .9rem;
  font-weight: 800;
  font-size: 1.1rem;
  letter-spacing:.3px;
}

/* —— 单条评论卡片 —— */
.comment-list{ list-style:none; margin:0; padding:0; }
.comment-list > li{
  padding: 1rem 1.05rem;
  margin-bottom: .9rem;
  border:1px solid var(--card-bd);
  border-radius: 14px;
  background: var(--card-bg);
  box-shadow: 0 1px 2px rgba(0,0,0,.04);
}

/* 子级缩进 + 左导引线（层级更清晰） */
.comment-list .comment-children{
  list-style:none;
  margin: .7rem 0 0 1rem;
  padding-left: 1rem;
  border-left: 2px solid var(--card-bd);
}

/* —— 头部（头像/昵称/时间） —— */
.comment-list .comment-author{
  display:flex; align-items:center; gap:.7rem;
  margin-bottom:.45rem;
}
.comment-list .comment-author .avatar{
  width:40px; height:40px; border-radius:50%;
  box-shadow: 0 1px 6px rgba(2,6,23,.15);
}
.comment-list .comment-author cite{
  font-weight: 800; font-style:normal;
}
.comment-list .comment-meta{
  margin-left:auto; font-size:.88em; color: var(--muted);
}

/* 身份/标识小胶囊（如果主题会输出） */
.comment-list .comment-author .badge,
.comment-list .comment-author .comment-by-author{
  display:inline-flex; align-items:center;
  padding:.18em .5em; margin-left:.25rem;
  border-radius:999px; font-size:.75em;
  border:1px solid var(--chip-bd); background: var(--chip-bg);
}

/* —— 正文 —— */
.comment-list .comment-content{ line-height:1.75; margin:.2rem 0 0; }
.comment-list .comment-content p{ margin:.45em 0; }

/* —— 操作区（回复/编辑） —— */
.comment-list .comment-reply, .comment-list .comment-edit{
  margin-top: .6rem; display:flex; gap:.45rem;
}

/* —— 通用按钮 —— */
.btn{
  display:inline-flex; align-items:center; justify-content:center;
  gap:.4em; height:2.2rem; padding:0 .95rem;
  font-size:.92em; font-weight:700; letter-spacing:.2px;
  border-radius:999px; border:1px solid transparent; text-decoration:none; cursor:pointer;
  transition: transform .15s ease, box-shadow .25s ease, filter .2s ease, border-color .25s ease;
  user-select:none;
}
.btn:focus{ outline:none; box-shadow: 0 0 0 3px var(--ring); }
.btn:hover{ transform: translateY(-1px); }

/* 主按钮（亮蓝渐变） */
.btn-primary{
  color:#fff;
  background: linear-gradient(180deg, var(--primary-1), var(--primary-2));
  border-color: transparent;
  box-shadow: 0 10px 20px var(--primary-shadow);
}
.btn-primary:hover{ filter: brightness(1.04); }
.btn-primary:active{ transform: translateY(0); }

/* 线框按钮（用于“回复”） */
.btn-outline{
  color: inherit;
  background: var(--chip-bg);
  border-color: var(--chip-bd);
}
.btn-outline:hover{
  border-color: rgba(2,6,23,.25);
  box-shadow: 0 8px 16px rgba(2,6,23,.10);
}
[data-theme="dark"] .btn-outline:hover{
  border-color: rgba(255,255,255,.24);
  box-shadow: 0 10px 20px rgba(0,0,0,.45);
}

/* —— 表单 —— */
#respond{ margin-top: 1.3rem; }
#respond .cancel-comment-reply{ float:right; font-size:.9em; color: var(--muted); }
#comment-form{ margin-top:.7rem; }
#comment-form .row{ display:flex; flex-wrap:wrap; gap:.7rem; }

/* 输入控件 */
#comment-form input[type="text"],
#comment-form input[type="email"],
#comment-form input[type="url"],
#comment-form textarea{
  width:100%;
  border:1px solid var(--card-bd);
  background: var(--card-bg);
  border-radius: 12px;
  padding:.7rem .85rem;
  font: inherit;
  transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
#comment-form textarea{ min-height: 160px; resize: vertical; line-height:1.6; }
#comment-form input:focus, #comment-form textarea:focus{
  border-color: var(--primary-1);
  box-shadow: 0 0 0 3px var(--ring);
}

/* 提交按钮（用 .btn .btn-primary 命中） */
#comment-form .submit{
  /* 如果你暂时不改 PHP，这里兜底也给 submit 美化 */
  display:inline-flex; align-items:center; justify-content:center;
  gap:.4em; height:2.4rem; padding:0 1.1rem;
  font-size:.95em; font-weight:800; letter-spacing:.2px;
  border-radius:999px; border:1px solid transparent; text-decoration:none; cursor:pointer;
  color:#fff; background: linear-gradient(180deg, var(--primary-1), var(--primary-2));
  box-shadow: 0 12px 22px var(--primary-shadow);
  transition: transform .15s ease, box-shadow .25s ease, filter .2s ease;
}
#comment-form .submit:hover{ transform: translateY(-1px); filter:brightness(1.04); }
#comment-form .submit:active{ transform: translateY(0); }

/* 分页 */
#comments .page-navigator{
  list-style:none; display:flex; gap:.45rem; padding:0; margin:1rem 0 0;
}
#comments .page-navigator li a{
  display:inline-flex; align-items:center; justify-content:center;
  min-width:2.1rem; height:2.1rem; padding:0 .7rem;
  border-radius:11px; text-decoration:none; color:inherit;
  border:1px solid var(--card-bd); background: var(--chip-bg);
}
#comments .page-navigator li.current a{ font-weight:800; }
</style>
<style id="comment-button-tweak">
/* 基础按钮（统一圆角胶囊 + 轻微动效） */
#comment-form .btn,
.comment-reply .btn{
  appearance: none;
  border: 1px solid transparent;
  padding: .55em 1.1em;
  border-radius: 999px;
  font-weight: 600;
  font-size: .95rem;
  line-height: 1.2;
  transition: background .15s ease, border-color .15s ease,
              box-shadow .2s ease, transform .05s ease;
  box-shadow: 0 1px 2px rgba(0,0,0,.06);
}

/* 主按钮：纯色、简洁 */
#comment-form .btn-primary{
  background: #2F81F7;       /* 亮一点的蓝 */
  color: #fff;
  border-color: rgba(47,129,247,.9);
}
#comment-form .btn-primary:hover{
  background: #2374EE;       /* 轻微加深 */
}
#comment-form .btn-primary:active{
  transform: translateY(1px);
}
#comment-form .btn-primary:focus-visible{
  outline: none;
  box-shadow: 0 0 0 3px rgba(47,129,247,.25);
}

/* 暗色模式下的主按钮：更柔和的蓝，仍然纯色 */
[data-theme="dark"] #comment-form .btn-primary{
  background: #6AA6FF;
  border-color: rgba(159,195,255,.7);
  box-shadow: 0 1px 2px rgba(0,0,0,.4);
}
[data-theme="dark"] #comment-form .btn-primary:hover{
  background: #5A9AFF;
}

/* 回复按钮：低调线框款 */
.comment-reply .btn-outline{
  background: transparent;
  color: inherit;
  border-color: rgba(2,6,23,.18);
}
.comment-reply .btn-outline:hover{
  border-color: rgba(2,6,23,.35);
  background: rgba(2,6,23,.04);
}
[data-theme="dark"] .comment-reply .btn-outline{
  border-color: rgba(255,255,255,.22);
}
[data-theme="dark"] .comment-reply .btn-outline:hover{
  border-color: rgba(255,255,255,.36);
  background: rgba(255,255,255,.06);
}

/* 细节：与输入框距离更自然一点 */
#comment-form .submit{ margin-top: .6em; }
</style>


<style id="link-scope-v3">
/* 主题色（亮/暗） */
:root{ --link: #2F81F7; --link-hover: #2374EE; }
[data-theme="dark"]{ --link: #8FB8FF; --link-hover: #A9C8FF; }

/* ========== 正文与首页摘要里的“文字容器”内的链接（仅此处生效） ========== */
/* 仅命中：段落、列表、引用、表格单元、图注中的 a[href]，并排除按钮/更多 */
.post-content :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn),
.summary      :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn){
  color: var(--link) !important;
  text-decoration: none;
  border-bottom: 1px dotted color-mix(in srgb, var(--link) 60%, transparent) !important;
  transition: color .15s ease, border-color .15s ease;
}
.post-content :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn):hover,
.post-content :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn):focus-visible,
.summary      :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn):hover,
.summary      :where(p,li,blockquote,td,th,figcaption) a[href]:not(.more):not(.btn):focus-visible{
  color: var(--link-hover) !important;
  border-bottom: 1px solid currentColor !important;
  outline: none;
}

/* —— 只在上述容器内的链接后面加 ↗ —— */
/* 排除：目录、图片灯箱、显式 no-link-mark、自身含图片、按钮/更多 */
.post-content :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn)::after,
.summary :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn)::after{
  content: "↗";
  font-size: .9em;
  margin-left: .28em;
  opacity: .65;
  vertical-align: .02em;
  transition: opacity .15s ease, transform .15s ease;
}
.post-content :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn):hover::after,
.post-content :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn):focus-visible::after,
.summary :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn):hover::after,
.summary :where(p,li,blockquote,td,th,figcaption)
  a[href]:not([data-fancybox]):not(.no-link-mark):not(#tableOfContents a):not(:has(img)):not(.more):not(.btn):focus-visible::after{
  opacity: .95;
  transform: translateY(-1px);
}

/* 图片链接和 fancybox：不加底线/符号 */
.post-content a[data-fancybox], .summary a[data-fancybox]{ border-bottom: none !important; }
.post-content :where(p,li,blockquote,td,th,figcaption) a[href]:has(img),
.summary      :where(p,li,blockquote,td,th,figcaption) a[href]:has(img){
  border-bottom: none !important;
}
/* 兼容不支持 :has 的浏览器，给这类链接加 .no-link-mark 即可 */
@supports not selector(:has(*)){
  .post-content a.no-link-mark, .summary a.no-link-mark{ border-bottom: none !important; }
  .post-content a.no-link-mark::after, .summary a.no-link-mark::after{ content: none !important; }
}

/* ========== 目录（TOC）里的链接：低调且无符号 ========== */
#tableOfContents a{
  color: inherit !important;
  text-decoration: none;
  border-bottom: 1px dotted rgba(0,0,0,.25) !important;
}
[data-theme="dark"] #tableOfContents a{ border-bottom-color: rgba(255,255,255,.35) !important; }
#tableOfContents a:hover, #tableOfContents a:focus-visible{
  color: var(--link) !important;
  border-bottom: 1px solid currentColor !important;
}
#tableOfContents a::after{ content: none !important; }

/* ========== 明确把“阅读全文”胶囊、标题/导航链接排除在外 ========== */
/* 还原“阅读全文”胶囊的小箭头（不受正文链接规则影响） */
/* 让“阅读全文”胶囊的箭头固定在右上角，不占文字间距 */
.summary a.more{
  position: relative;
  padding-right: 1.8em;              /* 给右侧留出箭头空间 */
  border-bottom: none !important;     /* 按钮不需要底线 */
}

.summary a.more::after{
  content: "↗";
  position: absolute;
  right: .65em;                       /* 箭头靠右一点 */
  top: .70em;                         /* 稍微靠上，像右上角标 */
  font-size: .9em;
  opacity: .82;
  transform: translateX(1px);         /* 轻微右移更灵动 */
  transition: transform .18s ease, opacity .18s ease;
}

.summary a.more:hover::after{
  opacity: .95;
  transform: translateX(2px);         /* 悬停时轻微前移 */
}



header .site-title, header .site-title a{ color: inherit; border: 0 !important; }
header .site-title-heading{
  font-size: 2rem;
  font-weight: 700;
  line-height: var(--line-height);
  color: var(--h1-color);
}
header .site-title a::after{ content: none !important; }
.navbar a::after, header a::after, footer a::after{ content: none !important; }

/* 代码区域里的链接：保持代码配色，不加符号 */
:where(pre,code,.hljs) a{ color: inherit !important; border: 0 !important; }
:where(pre,code,.hljs) a::after{ content: none !important; }
</style>

<style id="comments-neutral-overrides">
/* —— 中性配色变量（亮/暗） —— */
:root{
  --cmt-card-bg: linear-gradient(180deg, rgba(2,6,23,.02), rgba(2,6,23,.01));
  --cmt-card-bd: rgba(2,6,23,.12);
  --cmt-muted: rgba(2,6,23,.62);
  --cmt-surface: rgba(2,6,23,.05);
  --cmt-surface-hover: rgba(2,6,23,.08);
  --cmt-ring: rgba(0,0,0,.15);
  --cmt-btn-fg: #111827;
  --cmt-btn-bg: #ffffff;
  --cmt-btn-bd: rgba(2,6,23,.22);
  --cmt-btn-bg-hover: #f5f6f8;
  --cmt-btn-bd-hover: rgba(2,6,23,.36);
  --cmt-btn-shadow: 0 8px 20px rgba(0,0,0,.08);
}
:root[data-theme="dark"], [data-theme="dark"]{
  --cmt-card-bg: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.04));
  --cmt-card-bd: rgba(255,255,255,.16);
  --cmt-muted: rgba(229,231,235,.78);
  --cmt-surface: rgba(255,255,255,.06);
  --cmt-surface-hover: rgba(255,255,255,.10);
  --cmt-ring: rgba(255,255,255,.20);
  --cmt-btn-fg: #e5e7eb;
  --cmt-btn-bg: #0f172a;
  --cmt-btn-bd: rgba(255,255,255,.22);
  --cmt-btn-bg-hover: #111827;
  --cmt-btn-bd-hover: rgba(255,255,255,.38);
  --cmt-btn-shadow: 0 10px 22px rgba(0,0,0,.45);
}

/* —— 卡片与文字的中性化 —— */
.comment-list > li{
  background: var(--cmt-card-bg) !important;
  border-color: var(--cmt-card-bd) !important;
  box-shadow: 0 1px 2px rgba(0,0,0,.04) !important;
}
.comment-list .comment-meta{ color: var(--cmt-muted) !important; }

/* —— 输入控件：黑白灰 —— */
#comment-form input[type="text"],
#comment-form input[type="email"],
#comment-form input[type="url"],
#comment-form textarea{
  background: var(--cmt-card-bg) !important;
  border: 1px solid var(--cmt-card-bd) !important;
}
#comment-form input:focus, #comment-form textarea:focus{
  border-color: var(--cmt-btn-bd-hover) !important;
  box-shadow: 0 0 0 3px var(--cmt-ring) !important;
}

/* —— 主按钮（提交）：黑白灰纯色，圆角胶囊 —— */
/* 兼容：button[type=submit]、#submit、.btn-primary 三种写法 */
#comment-form button[type="submit"],
#comment-form #submit,
#comment-form .btn-primary{
  appearance: none;
  display: inline-flex; align-items: center; justify-content: center;
  gap: .45em; height: 2.5rem; padding: 0 1.15rem;
  border-radius: 999px; font-weight: 700; letter-spacing: .2px;
  color: var(--cmt-btn-fg) !important;
  background: var(--cmt-btn-bg) !important;
  border: 1px solid var(--cmt-btn-bd) !important;
  box-shadow: var(--cmt-btn-shadow);
  transition: transform .08s ease, box-shadow .22s ease, background .15s ease, border-color .15s ease, filter .15s ease;
  text-decoration: none;
}
#comment-form button[type="submit"]:hover,
#comment-form #submit:hover,
#comment-form .btn-primary:hover{
  background: var(--cmt-btn-bg-hover) !important;
  border-color: var(--cmt-btn-bd-hover) !important;
  transform: translateY(-1px);
}
#comment-form button[type="submit"]:active,
#comment-form #submit:active,
#comment-form .btn-primary:active{
  transform: translateY(0);
}
#comment-form button[type="submit"]:focus-visible,
#comment-form #submit:focus-visible,
#comment-form .btn-primary:focus-visible{
  outline: none;
  box-shadow: 0 0 0 3px var(--cmt-ring) !important;
}

/* —— 次按钮（回复/编辑）：线框中性 —— */
.comment-list .comment-reply a,
.comment-list .comment-edit a,
.btn-outline{
  display:inline-flex; align-items:center; justify-content:center;
  height: 2.2rem; padding: 0 .9rem;
  font-size: .92em; border-radius: 999px;
  color: inherit; text-decoration: none;
  background: var(--cmt-surface);
  border: 1px solid var(--cmt-card-bd);
  transition: transform .08s ease, background .15s ease, border-color .15s ease, box-shadow .22s ease;
}
.comment-list .comment-reply a:hover,
.comment-list .comment-edit a:hover,
.btn-outline:hover{
  background: var(--cmt-surface-hover);
  border-color: var(--cmt-btn-bd-hover);
  transform: translateY(-1px);
}

/* —— 细节统一：分页、徽标、导引线 —— */
#comments .page-navigator li a{
  background: var(--cmt-surface);
  border-color: var(--cmt-card-bd);
}
#comments .page-navigator li a:hover{
  background: var(--cmt-surface-hover);
  border-color: var(--cmt-btn-bd-hover);
}
.comment-list .comment-author .badge,
.comment-list .comment-author .comment-by-author{
  background: var(--cmt-surface);
  border-color: var(--cmt-card-bd);
}

/* —— 与正文的留白更大气（可选） —— */
#comments{ scroll-margin-top: 64px; } /* 锚点更舒服 */
#respond{ margin-top: 1.4rem !important; }
#comment-form .submit{ margin-top: .7rem !important; } /* 就算仍然使用 .submit 类也有间距 */
</style>
<style id="katex-scroll-vertical-fix">
/* 允许在公式区域内进行“上下滚动页面 + 左右滑动公式” */
.post-content .katex-scroll,
.page-content  .katex-scroll,
main           .katex-scroll{
  /* 兼容顺序写两遍：不支持 pan-y 的浏览器用 auto，支持的用 pan-y pan-x */
  touch-action: auto !important;
  touch-action: pan-y pan-x !important;
}

/* 可选：如果你之前为了“横向回弹隔离”写了 overscroll-behavior-x: contain; 保留即可；
   但请确保不要写 overscroll-behavior: none；否则也会让纵向滚动感觉“粘住”。 */
</style>


    <!-- jQuery.js -->
    <script src="<?php $this->options->themeUrl('/assets/js/jquery.min.js');?>"></script>
    <!-- jQuery.pjax.js -->
    <script src="<?php $this->options->themeUrl('/assets/js/jquery.pjax.js');?>"></script>
    <!-- Highlight.js -->
    <script src="<?php $this->options->themeUrl('/assets/js/highlight.min.js');?>"></script>
    <!-- Fancybox.js -->
    <script src="<?php $this->options->themeUrl('/assets/js/jquery.fancybox.min.js');?>"></script>
    <!-- KaTeX.js -->
    <script src="<?php $this->options->themeUrl('/assets/js/katex/katex.min.js');?>"></script>
    <script src="<?php $this->options->themeUrl('/assets/js/katex/auto-render.min.js');?>"></script>

    <?php echo $headerCodeRaw; ?>

    <?php echo $headerOutput; ?>


<style id="fab-toc-style">
:root{
  --fab-fg: #111827;
  --fab-bg: #ffffff;
  --fab-bd: rgba(2,6,23,.14);
  --fab-shadow: 0 10px 28px rgba(0,0,0,.12);

  --panel-bg: #ffffff;                /* 亮色：纯白 */
  --panel-fg: #111827;
  --panel-bd: rgba(2,6,23,.12);
  --panel-hover: rgba(2,6,23,.06);
}
[data-theme="dark"] :root{
  --fab-fg: #e5e7eb;
  --fab-bg: #0f172a;                  /* 暗色：更深 */
  --fab-bd: rgba(255,255,255,.20);
  --fab-shadow: 0 12px 32px rgba(0,0,0,.55);

  --panel-bg: #0b1220;                /* 暗色面板：纯深色，不透白 */
  --panel-fg: #e5e7eb;
  --panel-bd: rgba(255,255,255,.18);
  --panel-hover: rgba(255,255,255,.08);
}

/* 浮动按钮（对齐、对比、简约） */
#fab{
  position: fixed; right: 16px; bottom: 16px;
  width: 48px; height: 48px; border-radius: 50%;
  background: var(--fab-bg); color: var(--fab-fg);
  border: 1px solid var(--fab-bd);
  display: inline-flex; align-items: center; justify-content: center;
  box-shadow: var(--fab-shadow);
  cursor: pointer; user-select: none; z-index: 10000;
  transition: transform .15s ease, background .2s ease, border-color .2s ease, filter .2s ease;
  font-size: 20px; line-height: 1;
}
#fab:hover{ transform: translateY(-2px); background: var(--panel-hover); }

/* 面板：暗色更暗；无玻璃特效；边距为 TOC 留足底部空间 */
#fab-panel{
  position: fixed; right: 16px; bottom: 72px;
  width: min(92vw, 340px); max-height: min(72vh, 560px);
  background: var(--panel-bg); color: var(--panel-fg);
  border: 1px solid var(--panel-bd); border-radius: 12px;
  box-shadow: var(--fab-shadow);
  overflow: hidden; z-index: 10000;
  opacity: 0; transform: translateY(8px) scale(.98);
  pointer-events: none;
  transition: transform .18s cubic-bezier(.2,.6,.2,1), opacity .18s ease;
}
#fab-panel.open{ opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

/* 头部：图标绝对对齐不跑位 */
#fab-panel .hd{
  display: flex; align-items: center; justify-content: space-between;
  padding: .6rem .7rem .45rem;
  border-bottom: 1px dashed var(--panel-bd);
}
#fab-panel .title{ font-weight: 800; font-size: .95rem; letter-spacing: .2px; }
#fab-panel .actions{ display: inline-flex; align-items: center; gap: .4rem; }
#fab-panel .iconbtn{
  width: 34px; height: 34px; border-radius: 8px;
  display: inline-flex; align-items: center; justify-content: center;
  border: 1px solid var(--panel-bd);
  background: transparent; color: inherit; cursor: pointer;
  transition: background .15s ease, border-color .15s ease, transform .1s ease, opacity .15s ease;
  font-size: 16px; line-height: 1;
}
#fab-panel .iconbtn:hover{ background: var(--panel-hover); }
#fab-panel .iconbtn:active{ transform: translateY(1px); }

/* 内容区：给滚动区域加底部内边距 & scroll-padding，最后一项不再被裁 */
#fab-panel .bd{
  padding: .45rem .6rem .9rem;
  max-height: calc(min(72vh,560px) - 50px);
  overflow: auto;
  scroll-padding-bottom: 24px;
}

/* 快捷区已去除“回到首页”，不再显示 */

/* 目录：只文字，无边框；hover 仅微底色 */
#fab-toc{ font-size: .95rem; line-height: 1.65; }
#fab-toc ol{ padding-left: 1.1em; margin: 0; }
#fab-toc li{ margin: .12rem 0; }
#fab-toc a{
  color: inherit; text-decoration: none;
  padding: .08rem .2rem; border-radius: 6px;
}
#fab-toc a:hover{ background: var(--panel-hover); }

@media (max-width: 480px){
  #fab{ right: 12px; bottom: 12px; }
  #fab-panel{ right: 12px; bottom: 68px; width: min(94vw, 360px); }
}
</style>
<script>
(function(){
  /* —— 路由：主页和 /page/... 不显示 —— */
  function shouldShow(){
    var p = location.pathname.replace(/\/+$/,'/') || '/';
    if (p === '/') return false;
    if (p.indexOf('/page/') === 0) return false;
    return true;
  }

  /* —— 主题：读取/切换/同步两个位置的图标 —— */
  function getMode(){
    var saved = localStorage.getItem('theme');
    if (saved==='light' || saved==='dark') return saved;
    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  }
  function setMode(next){
    if (next==='light' || next==='dark'){
      localStorage.setItem('theme', next);
      document.documentElement.setAttribute('data-theme', next);
    }
    syncThemeIcons();
  }
  function syncThemeIcons(){
    // 面板图标
    var t1 = document.getElementById('rt-theme');
    if (t1) { var m = getMode(); t1.textContent = (m==='dark') ? '🌞' : '🌙'; t1.title = (m==='dark') ? '切换为亮色' : '切换为暗色'; }
    // 标题栏图标（你现有的）
    var t2 = document.getElementById('theme-toggle-icon');
    if (t2) { var m2 = getMode(); t2.textContent = (m2==='dark') ? '🌞' : '🌙'; t2.title = (m2==='dark') ? '切换到白天模式' : '切换到黑夜模式'; }
  }

  /* —— 生成/获取 FAB 与面板（只建一次） —— */
  function ensureFab(){
    var fab = document.getElementById('fab');
    var panel = document.getElementById('fab-panel');
    if (!fab){
      fab = document.createElement('div');
      fab.id = 'fab';
      fab.title = '阅读工具';
      fab.textContent = '☰';
      document.body.appendChild(fab);
    }
    if (!panel){
      panel = document.createElement('div');
      panel.id = 'fab-panel';
      panel.innerHTML = `
        <div class="hd">
          <div class="title">阅读工具</div>
          <div class="actions">
            <button type="button" class="iconbtn" id="rt-home"  title="首页">⌂</button>
            <button type="button" class="iconbtn" id="rt-theme" title="切换主题">🌙</button>
            <button type="button" class="iconbtn" id="rt-close" title="关闭">✕</button>
          </div>
        </div>
        <div class="bd">
          <div id="fab-toc">本页暂无目录</div>
        </div>`;
      document.body.appendChild(panel);
    }

    /* 事件只绑定一次 */
    if (!fab.dataset.bound){
      fab.dataset.bound = '1';
      fab.addEventListener('click', function(){ panel.classList.toggle('open'); });
      document.addEventListener('click', function(e){
        if (!panel.contains(e.target) && e.target !== fab){ panel.classList.remove('open'); }
      });
    }
    var btnClose = document.getElementById('rt-close');
    var btnHome  = document.getElementById('rt-home');
    var btnTheme = document.getElementById('rt-theme');

    if (btnClose && !btnClose.dataset.bound){
      btnClose.dataset.bound = '1';
      btnClose.addEventListener('click', function(){ panel.classList.remove('open'); });
    }
    if (btnHome && !btnHome.dataset.bound){
      btnHome.dataset.bound = '1';
      btnHome.addEventListener('click', function(){ location.href = "<?php $this->options->siteUrl(); ?>"; });
    }
    if (btnTheme && !btnTheme.dataset.bound){
      btnTheme.dataset.bound = '1';
      btnTheme.addEventListener('click', function(){
        var cur = getMode();
        setMode(cur === 'dark' ? 'light' : 'dark');
      });
    }

    syncThemeIcons();
    return {fab: fab, panel: panel};
  }

  /* —— 构建 TOC：优先复制 #tableOfContents，缺省从 H2–H4 生成 —— */
  function buildTOC(ctx){
    var container = ctx || (document.getElementById('pjax-container') || document);
    var dst = document.getElementById('fab-toc');
    if (!dst) return;

    var toc = container.querySelector('#tableOfContents ol');
    if (toc){
      dst.innerHTML = '<ol>'+ toc.innerHTML +'</ol>';
      bindTOCAnchors(dst);
      return;
    }

    var root = container.querySelector('.post-content, article, .entry, main') || container;
    var hs = root.querySelectorAll('h2, h3, h4');
    if (!hs.length){ dst.textContent = '本页暂无目录'; return; }

    var ol = document.createElement('ol');
    hs.forEach(function(h, i){
      if (!h.id) h.id = 'h-' + Date.now().toString(36) + '-' + i;
      var li = document.createElement('li');
      if (h.tagName === 'H3') li.style.marginLeft = '.6em';
      if (h.tagName === 'H4') li.style.marginLeft = '1.2em';
      var a = document.createElement('a');
      a.href = '#'+h.id; a.textContent = (h.textContent||'').trim();
      li.appendChild(a); ol.appendChild(li);
    });
    dst.innerHTML = ''; dst.appendChild(ol);
    bindTOCAnchors(dst);
  }

  function bindTOCAnchors(scope){
    scope.querySelectorAll('a[href^="#"]').forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var id = a.getAttribute('href').slice(1);
        var t = document.getElementById(id);
        if (t){ window.scrollTo({ top: t.getBoundingClientRect().top + scrollY - 8, behavior: 'smooth' }); }
      }, {passive:false});
    });
  }

  /* —— 显示/隐藏控制 —— */
  function applyVisibility(){
    var show = shouldShow();
    var fab = document.getElementById('fab');
    var panel = document.getElementById('fab-panel');
    if (!fab || !panel) return;
    fab.style.display = show ? 'inline-flex' : 'none';
    panel.classList.remove('open');
  }

  /* —— 初始化（首屏 & PJAX 后） —— */
  function init(){
    var nodes = ensureFab();
    applyVisibility();
    if (nodes && nodes.fab.style.display !== 'none'){
      buildTOC(document.getElementById('pjax-container') || document);
    }
    syncThemeIcons();
  }

  document.addEventListener('DOMContentLoaded', init);

  if (window.jQuery){
    jQuery(document).on('pjax:send', function(){
      var panel = document.getElementById('fab-panel');
      if (panel) panel.classList.remove('open');
    });
    jQuery(document).on('pjax:end pjax:complete pjax:success', function(){
      init();   // 新页面：重建 TOC + 控制显隐 + 同步图标
    });
  }

  // 若正文异步变化（例如 KaTeX 公式渲染），也刷新一次 TOC
  var mo;
  document.addEventListener('DOMContentLoaded', function(){
    var container = document.getElementById('pjax-container') || document.body;
    if (!container) return;
    mo = new MutationObserver(function(muts){
      for (var m of muts){
        if (m.addedNodes && m.addedNodes.length){
          if (document.getElementById('fab')?.style.display !== 'none'){
            buildTOC(container);
          }
          break;
        }
      }
    });
    mo.observe(container, { childList: true, subtree: true });
  });

  // 如果别处切了主题（例如点了标题栏的按钮），我们这边也联动图标
  window.addEventListener('storage', function(e){
    if (e.key === 'theme') syncThemeIcons();
  });
  // 监听 <html data-theme> 变化（你已有 MutationObserver 同步背景），这里只更新图标
  new MutationObserver(function(muts){
    muts.forEach(function(m){
      if (m.type==='attributes' && m.attributeName==='data-theme'){ syncThemeIcons(); }
    });
  }).observe(document.documentElement, {attributes:true});
})();
</script>



</head>
<!--[if lt IE 8]>
    当前网页不支持你正在使用的浏览器。为了正常访问, 请升级你的浏览器！
<![endif]-->
<body>

<header class="container">
  <a class="home-link" href="<?php $this->options->siteUrl(); ?>" aria-label="返回首页" style="display:inline-block;">
    <img id="site-logo" class="headpic shadow" src="<?php $this->options->logoUrl()?>" alt="<?php $this->options->title() ?>" width="128" height="128" style="cursor:pointer;will-change:transform;">
  </a>
    <hgroup>
      <?php if ($this->is('index')): ?>
      <h1 style="margin:8px 0 0;">
        <a href="<?php $this->options->siteUrl(); ?>" class="site-title" rel="home"
           style="color:inherit !important; text-decoration:none;">
          <?php $this->options->title()?>
        </a>
      </h1>
      <?php else: ?>
      <div class="site-title-heading" style="margin:8px 0 0;">
        <a href="<?php $this->options->siteUrl(); ?>" class="site-title" rel="home"
           style="color:inherit !important; text-decoration:none;">
          <?php $this->options->title()?>
        </a>
      </div>
      <?php endif; ?>

      <h4>
    <div id="quote">
        <a id="quote-link" href="#" style="color: inherit; text-decoration: none;">加载中...</a>
    </div>

    <script>
        fetch('https://quoteapi.2b.gs/endpoints/run/%E8%AF%97%E8%AF%8D&&%E6%AD%8C%E8%AF%8D')
            .then(res => res.json())
            .then(data => {
                const quoteLink = document.getElementById('quote-link');
                quoteLink.textContent = data.content;
                quoteLink.href = 'https://quoteapi.2b.gs' + data.link;
                quoteLink.target = '_blank'; // 新标签页打开
            });
    </script>
</h4>

    </hgroup>
</header>


<!-- 导航栏固定链接 -->
<nav class="navbar">
    <ul>
        <li>
            <a href="https://yourlink"><i class="czs-home"></i> 首页</a>
        </li>
        <li>
            <a href="https://yourlink" target="_blank" rel="noopener"><i class="czs-paper"></i> 博客</a>
        </li>
        <li>
  <a href="https://github.com/lixu10" target="_blank" rel="noopener">
    <i class="czs-github"></i> GitHub
  </a>
</li>
<li>
  <button id="theme-toggle" type="button" style="border:none;background:transparent;cursor:pointer;">
    <span id="theme-toggle-icon" aria-hidden="true">🌙/🌞</span>
    <span class="sr-only"></span>
  </button>
</li>

    </ul>
</nav>

<!-- Add shadow for navbar when fixed. -->
<!-- Ref: https://codepen.io/hey-nick/pen/mLpmMV -->
<script>
    const headerEl = document.querySelector('.navbar')
    const sentinalEl = document.querySelector('header')
    const handler = (entries) => {
      console.log(entries)
      if (!entries[0].isIntersecting) {
        headerEl.classList.add('shadow')
      } else {
        headerEl.classList.remove('shadow')
      }
    }
    const observer = new window.IntersectionObserver(handler)
    observer.observe(sentinalEl)
</script>

<main class="container" id="pjax-container">


<style>
/* Logo 旋转动画（内联，不改外部 CSS） */
#site-logo { transition: transform 0.6s ease; }

/* 单次顺时针旋转 */
@keyframes spinOnce { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
/* 单次逆时针旋转 */
@keyframes spinBackOnce { from { transform: rotate(0deg); } to { transform: rotate(-360deg); } }

#site-logo.spin { animation: spinOnce 0.6s ease both; }
#site-logo.spin-back { animation: spinBackOnce 0.6s ease both; }
</style>

<script>
/**
 * PJAX 安全的统一初始化
 * - 主题切换：直绑 + 委托双保险；同时设置 <html data-theme> 与 <body class>
 * - Logo 旋转：事件委托（header 不被 PJAX 替换）
 */
(function () {
  var root = document.documentElement;

  function getMode() {
    var saved = localStorage.getItem('theme');
    if (saved === 'light' || saved === 'dark') return saved;
    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  }
  function applyMode(mode) {
    // 同步 <html> 和 <body>
    if (mode === 'light' || mode === 'dark') {
      root.setAttribute('data-theme', mode);
      document.body && (document.body.classList.remove('light','dark'), document.body.classList.add(mode));
    } else {
      root.removeAttribute('data-theme');
      document.body && document.body.classList.remove('light','dark');
    }
  }
  function setMode(mode) {
    if (mode === 'light' || mode === 'dark') {
      localStorage.setItem('theme', mode);
    } else {
      localStorage.removeItem('theme');
    }
    applyMode(mode);
  }
  function updateThemeIcon() {
    var icon = document.getElementById('theme-toggle-icon');
    if (!icon) return;
    var mode = getMode();
    icon.textContent = (mode === 'dark') ? '🌞' : '🌙';
    icon.title = (mode === 'dark') ? '切换到白天模式' : '切换到黑夜模式';
  }

  // —— 主题切换：直绑（按钮存在时） —— //
  function bindThemeButton() {
    var btn = document.getElementById('theme-toggle');
    if (!btn || btn.dataset.bound) return;
    btn.dataset.bound = '1';
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      var next = (getMode() === 'dark') ? 'light' : 'dark';
      setMode(next);
      updateThemeIcon();
    });
  }

  // —— 主题切换：事件委托（即使按钮被替换也能命中） —— //
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('#theme-toggle');
    if (!btn) return;
    e.preventDefault(); e.stopPropagation();
    var next = (getMode() === 'dark') ? 'light' : 'dark';
    setMode(next);
    updateThemeIcon();
  });

  // —— Logo 悬停旋转（委托） —— //
  document.addEventListener('mouseenter', function (e) {
    var img = e.target.closest && e.target.closest('#site-logo');
    if (!img) return;
    img.classList.remove('spin-back');
    void img.offsetWidth;
    img.classList.add('spin');
  }, true);

  document.addEventListener('mouseleave', function (e) {
    var img = e.target.closest && e.target.closest('#site-logo');
    if (!img) return;
    img.classList.remove('spin');
    void img.offsetWidth;
    img.classList.add('spin-back');
  }, true);

  document.addEventListener('animationend', function (e) {
    if (e.target && e.target.id === 'site-logo') {
      e.target.classList.remove('spin', 'spin-back');
    }
  });

  // —— 初始化（首屏 + PJAX 完成后） —— //
  function initOnce() {
    applyMode(getMode());
    updateThemeIcon();
    bindThemeButton();
  }

  document.addEventListener('DOMContentLoaded', initOnce);

  if (window.jQuery) {
    // 某些主题会在 pjax:complete 触发，这里用 pjax:end 覆盖大多数场景
    jQuery(document).on('pjax:end', function () {
      initOnce();
    });
  }
})();
</script>


<script>
/**
 * 阅读进度条：PJAX 安全、性能友好（rAF + scaleX）
 */
(function(){
  var ticking = false;

  // 创建或获取进度条的内部元素
  function ensureBar(){
    var holder = document.getElementById('read-progress');
    if(!holder){
      holder = document.createElement('div');
      holder.id = 'read-progress';
      var inner = document.createElement('div');
      inner.className = 'bar';
      holder.appendChild(inner);
      // 尽量早地插到 body 顶部
      (document.body || document.documentElement).appendChild(holder);
    }
    return holder.querySelector('.bar');
  }

  function calcRatio(){
    var doc = document.documentElement;
    var scrollTop = window.pageYOffset || doc.scrollTop || 0;
    var max = (doc.scrollHeight - doc.clientHeight);
    if (max <= 2) return -1; // 页面太短：隐藏
    var r = scrollTop / max;
    if (r < 0) r = 0; if (r > 1) r = 1;
    return r;
  }

  function updateBar(){
    var bar = ensureBar();
    var ratio = calcRatio();
    if (ratio < 0){
      bar.style.opacity = '0';
      bar.style.transform = 'scaleX(0)';
    } else {
      bar.style.opacity = '';
      bar.style.transform = 'scaleX(' + ratio + ')';
    }
  }

  function onScrollOrResize(){
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function(){
      updateBar();
      ticking = false;
    });
  }

  // 首屏
  document.addEventListener('DOMContentLoaded', updateBar);
  // 滚动/尺寸
  window.addEventListener('scroll', onScrollOrResize, {passive:true});
  window.addEventListener('resize', onScrollOrResize);

  // PJAX：进入新页时重置为 0，加载完再计算
  if (window.jQuery){
    jQuery(document).on('pjax:send', function(){
      var bar = ensureBar();
      bar.style.transform = 'scaleX(0)';
      bar.style.opacity = '0.6';
    });
    jQuery(document).on('pjax:end', function(){
      updateBar();
    });
  }
})();
</script>

<script>
(function(){
  var mo = null;

  // 给摘要里的“阅读全文/More…”加上 .more 类，方便样式命中
  function decorateReadMore(container){
    var root = container || document;
    root.querySelectorAll('.summary').forEach(function(box){
      // 已有 .more 就跳过
      if (box.querySelector('a.more')) return;

      // 优先找中文/英文“阅读全文/More”关键字
      var target = null;
      box.querySelectorAll('a[href]').forEach(function(a){
        var t = (a.textContent || '').trim().toLowerCase();
        if (t === '阅读全文' || t === 'read more' || t === 'more' || /^more\.*$/.test(t)){
          target = a;
        }
      });

      // 如果摘要里只有一个链接，也视作 “阅读全文”
      if (!target){
        var links = box.querySelectorAll(':scope > a[href], a[href].more');
        if (links.length === 1) target = links[0];
      }

      if (target) target.classList.add('more');
    });
  }

  function startObserver(){
    stopObserver();
    var container = document.getElementById('pjax-container') || document.body;
    if (!container) return;
    mo = new MutationObserver(function(muts){
      for (var m of muts){
        if (m.addedNodes && m.addedNodes.length){
          // 一有新内容就补一次
          decorateReadMore(container);
          break;
        }
      }
    });
    mo.observe(container, {childList:true, subtree:true});
  }

  function stopObserver(){ if (mo){ mo.disconnect(); mo = null; } }

  // 首屏
  document.addEventListener('DOMContentLoaded', function(){
    decorateReadMore(document);
    startObserver();
  });

  // 兼容各种 PJAX 事件名
  if (window.jQuery){
    jQuery(document).on('pjax:send', function(){ stopObserver(); });
    jQuery(document).on('pjax:end pjax:complete pjax:success', function(){
      var container = document.getElementById('pjax-container') || document;
      decorateReadMore(container);
      startObserver();
    });
  }
})();
</script>

<script>
(function(){
  function enhanceComments(root){
    root = root || document;
    var ta = root.querySelector('#comment-form textarea');
    if (ta && !ta.dataset.autosize){
      ta.dataset.autosize = '1';
      var handler = function(){
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight + 2) + 'px';
      };
      ta.addEventListener('input', handler);
      handler();
    }
    // 给常用字段补全
    var name = root.querySelector('#comment-form input[name="author"]');
    var mail = root.querySelector('#comment-form input[name="mail"]');
    var url  = root.querySelector('#comment-form input[name="url"]');
    if (name) name.setAttribute('autocomplete','name');
    if (mail) mail.setAttribute('autocomplete','email');
    if (url)  url.setAttribute('autocomplete','url');
  }
  document.addEventListener('DOMContentLoaded', function(){ enhanceComments(document); });
  if (window.jQuery){
    jQuery(document).on('pjax:end', function(){ enhanceComments(document.getElementById('pjax-container')||document); });
  }
})();
</script>
<script>
(function(){
  function enhance(root){
    root = root || document;
    var ta = root.querySelector('#comment-form textarea');
    if (ta && !ta.dataset.autosize){
      ta.dataset.autosize = '1';
      var h = function(){ ta.style.height='auto'; ta.style.height=(ta.scrollHeight+2)+'px'; };
      ta.addEventListener('input', h); h();
    }
  }
  document.addEventListener('DOMContentLoaded', function(){ enhance(document); });
  if (window.jQuery){
    jQuery(document).on('pjax:end', function(){
      enhance(document.getElementById('pjax-container')||document);
    });
  }
})();
</script>
