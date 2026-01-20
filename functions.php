<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

define('__TYPECHO_GRAVATAR_PREFIX__', 'https://gravatar.loli.net/avatar/');

function themeConfig($form) {
    echo '<h2>Sky 主题设置</h2>';

    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, Helper::options()->themeUrl.'/assets/avatar.png', '站点 LOGO 地址', '在这里填入一个图片 URL 地址, 该图片会作为网站的 LOGO 显示在网站头部。');
    $form->addInput($logoUrl);

    $realHomepage = new Typecho_Widget_Helper_Form_Element_Text('realHomepage', NULL, NULL, '全站首页', '填入的链接会在导航栏首位显示为「首页」，适合将博客作为自己网站的一个子站点的情况。留空则不显示。');
    $form->addInput($realHomepage);

    $icpInfo = new Typecho_Widget_Helper_Form_Element_Text('icpInfo', NULL, NULL, 'ICP 备案号', '显示在底部，留空则不显示。');
    $form->addInput($icpInfo->addRule('xssCheck', '请不要使用特殊字符'));

    $nisInfo = new Typecho_Widget_Helper_Form_Element_Text('nisInfo', NULL, NULL, '网安备案号', '显示在底部（带国徽），留空则不显示。');
    $form->addInput($nisInfo->addRule('xssCheck', '请不要使用特殊字符'));

    $notification = new Typecho_Widget_Helper_Form_Element_Text('notification', NULL, NULL, '网站公告', '显示在首页，留空则不显示。');
    $form->addInput($notification);

    $oldPosts = new Typecho_Widget_Helper_Form_Element_Text('oldPosts', NULL, '365', '文章有效期', '单位：天。在此天数之前发布的文章将会显示「这是一篇旧文」的提示。留空则不显示。');
    $form->addInput($oldPosts);

    $commentsNotice = new Typecho_Widget_Helper_Form_Element_Text('commentsNotice', NULL, NULL, '评论区公告', '显示在评论区，留空则不显示。');
    $form->addInput($commentsNotice);

    $headerCode = new Typecho_Widget_Helper_Form_Element_Textarea('headerCode', NULL, NULL, '头部代码', '在头部添加的 HTML 代码，可以插入 JavsScript。');
    $form->addInput($headerCode);

    $footerCode = new Typecho_Widget_Helper_Form_Element_Textarea('footerCode', NULL, NULL, '页脚代码', '在页脚添加的 HTML 代码，可以插入 JavsScript。');
    $form->addInput($footerCode);

    $cunstomCSS = new Typecho_Widget_Helper_Form_Element_Textarea('cunstomCSS', NULL, NULL, '自定义 CSS', '加入自定义的 CSS 代码。');
    $form->addInput($cunstomCSS);
}

function themeFields($layout) {
    $headPic = new Typecho_Widget_Helper_Form_Element_Text('headPic', NULL, NULL, '文章头图地址', '仅对文章有效。在这里填入一个图片 URL 地址, 就可以让文章加上头图。留空则不显示头图。');
    $layout->addItem($headPic);

    $pubPlace = new Typecho_Widget_Helper_Form_Element_Text('pubPlace', NULL, NULL, '文章发布地点', '仅对文章有效。在这里输入一个地点的名字，文章头部会显示。留空则不显示发布地点。');
    $layout->addItem($pubPlace);

    $pageIcon = new Typecho_Widget_Helper_Form_Element_Text('pageIcon', NULL, NULL, '页面图标', '仅对非隐藏的页面有效。在这里为页面填入一个草莓图标库的代码，在菜单栏链接前会显示图标。草莓图标库是 2.0.0 Free 版本，参见<a href="https://chuangzaoshi.com/icon/" target="_blank">草莓图标库</a>。留空则不显示图标。');
    $layout->addItem($pageIcon);

    $linkTo = new Typecho_Widget_Helper_Form_Element_Text('linkTo', NULL, NULL, '重定向至', '在这里输入一个 URL，打开该页面或文章时会自动重定向到这个 URL，可以用于定制菜单栏。留空则不重定向。');
    $layout->addItem($linkTo);
}

function daydream_demote_headings($content) {
    $content = preg_replace_callback('/<h([1-5])(\s[^>]*)?>/i', function ($matches) {
        $level = intval($matches[1]) + 1;
        $attrs = isset($matches[2]) ? $matches[2] : '';
        return '<h' . $level . $attrs . '>';
    }, $content);

    $content = preg_replace_callback('/<\/h([1-5])>/i', function ($matches) {
        $level = intval($matches[1]) + 1;
        return '</h' . $level . '>';
    }, $content);

    return $content;
}

function daydream_meta_description($widget, $maxLen = 160) {
    $description = '';

    if ($widget->is('post') || $widget->is('page')) {
        $description = strip_tags($widget->content);
    } else {
        ob_start();
        $widget->archiveTitle(array(
            'category'  =>  '分类 %s 下的文章',
            'search'    =>  '包含关键字 %s 的文章',
            'tag'       =>  '标签 %s 下的文章',
            'author'    =>  '%s 发布的文章'
        ), '', '');
        $archiveTitle = trim(strip_tags(ob_get_clean()));
        if ($archiveTitle !== '') {
            $description = $archiveTitle;
        }
    }

    if ($description === '') {
        $description = (string) $widget->options->description;
    }

    if ($description === '') {
        $description = (string) $widget->options->title;
    }

    $description = preg_replace('/\s+/u', ' ', $description);
    $description = trim($description);

    if ($description === '') {
        return '';
    }

    if (function_exists('mb_substr')) {
        $description = mb_substr($description, 0, $maxLen, 'UTF-8');
    } else {
        $description = substr($description, 0, $maxLen);
    }

    return $description;
}

function exContent($content){
    $content = daydream_demote_headings($content);

    // 文章内短代码
    $pattern = '/\[(info)\](.*?)\[\s*\/\1\s*\]/';
    $replacement = '
    <div class="alert" role="alert">$2</div>';
    $content = preg_replace($pattern, $replacement, $content);

    // 自定义 HTML 展示框：[htmlbox width="100%" ratio="16:9"]...[/htmlbox]
    $content = preg_replace_callback('/\[htmlbox(.*?)\](.*?)\[\/htmlbox\]/is', function($m){
        $attrStr = $m[1];
        $inner = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5);
        $attrs = [];
        if (preg_match_all('/(\w+)="([^"]*)"/', $attrStr, $matches, PREG_SET_ORDER)){
            foreach ($matches as $item){
                $attrs[strtolower($item[1])] = $item[2];
            }
        }
        $width = isset($attrs['width']) && $attrs['width'] !== '' ? $attrs['width'] : '100%';
        $height = isset($attrs['height']) && $attrs['height'] !== '' ? $attrs['height'] : '';
        $ratioStr = isset($attrs['ratio']) && $attrs['ratio'] !== '' ? $attrs['ratio'] : '';
        $scroll = isset($attrs['scroll']) && $attrs['scroll'] !== '' ? strtolower($attrs['scroll']) : 'auto';

        // 计算 padding-top 百分比
        $padding = '56.25%'; // 默认 16:9
        if ($ratioStr !== '' && strpos($ratioStr, ':') !== false){
            list($rw, $rh) = array_map('floatval', explode(':', $ratioStr));
            if ($rw > 0 && $rh > 0){
                $padding = ($rh / $rw * 100) . '%';
            }
        }
        // 如果用户没给 height/ratio，则默认固定高 600px
        if ($height === '' && $ratioStr === ''){
            $height = '600px';
        }

        $isFixed = ($height !== '');
        $class = 'htmlbox' . ($isFixed ? ' is-fixed' : ' is-ratio');

        $srcdoc = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>html,body{margin:0;padding:0;box-sizing:border-box;width:100%;height:100%;overflow:auto;}body{min-height:100vh;}</style></head><body>' . $inner . '</body></html>';
        $srcdoc = htmlspecialchars($srcdoc, ENT_QUOTES);

        $style = 'style="--htmlbox-width:' . htmlspecialchars($width, ENT_QUOTES) . ';';
        if ($isFixed){
            $style .= '--htmlbox-height:' . htmlspecialchars($height, ENT_QUOTES) . ';';
        } else {
            $style .= '--htmlbox-ratio:' . htmlspecialchars($padding, ENT_QUOTES) . ';';
        }
        $style .= '"';

        $scrollAttr = 'data-scroll="'. htmlspecialchars($scroll, ENT_QUOTES) .'"';

        return '<div class="'. $class .'" '. $style .' '. $scrollAttr .'><div class="htmlbox__inner"><div class="htmlbox__frame"><iframe class="htmlbox__iframe" loading="lazy" sandbox="allow-scripts allow-same-origin allow-forms allow-modals allow-popups allow-downloads" srcdoc="'. $srcdoc .'" frameborder="0" allowfullscreen></iframe></div></div></div>';
    }, $content);

    // 文章 TOC 功能
    if (preg_match_all('/<h(\d)>(.*?)<\/h\d>/is', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)){
        $toc_out = "";
        $minlevel = 6;
        foreach ($matches as $match) $minlevel = min($minlevel, intval($match[1][0]));

        $curlevel = $minlevel-1;
        $offsetAdjust = 0; // track length added by anchors so later offsets stay correct
        foreach ($matches as $key => $match) {
            $level = intval($match[1][0]);
            $title = $match[2][0];
            $anchorId = "toc_title{$key}";

            $insertPos = $match[0][1] + $offsetAdjust;
            $anchorTag = "<a id=\"{$anchorId}\" style=\"position:relative; top:-50px\"></a>";
            $content = substr($content, 0, $insertPos) . $anchorTag . substr($content, $insertPos);
            $offsetAdjust += strlen($anchorTag);

            // 用伪锚点实现链接偏移。Safari 居然不支持！
            if ($level > $curlevel) $toc_out.=str_repeat("<ol>\n", $level-$curlevel);
            elseif ($level < $curlevel) $toc_out.=str_repeat("</ol>\n", $curlevel-$level);
            $curlevel = $level;
            $toc_out .= "<li><a href=\"#{$anchorId}\">{$title}</a></li>\n";
        }
        
        $content = "<details id=\"tableOfContents\" class=\"toc\"><summary style=\"font-weight:600;cursor:pointer;\">文章目录：</summary>{$toc_out}</details>" . $content;
    }


    // —— 展示型公式（$$...$$、\[...\]）包裹为可横向滚动的容器，防止小屏撑宽 —— //
    $hasDisplayMath = false;

    // $$ ... $$
    $content = preg_replace_callback('/\$\$[\s\S]*?\$\$/u', function($m) use (&$hasDisplayMath){
        $hasDisplayMath = true;
        return '<div class="katex-scroll"><span class="katex-scroll-inner">'.$m[0].'</span></div>';
    }, $content);

    // \[ ... \]
    $content = preg_replace_callback('/\\\\\[[\s\S]*?\\\\\]/u', function($m) use (&$hasDisplayMath){
        $hasDisplayMath = true;
        return '<div class="katex-scroll"><span class="katex-scroll-inner">'.$m[0].'</span></div>';
    }, $content);

    // 若本篇有展示型公式，内联最小样式（像代码框，可横向滚动；暗色下略调）
    if ($hasDisplayMath) {
        $content =
            '' . $content;
    }


    // Fancybox 图片灯箱
    $content = preg_replace("/<img src=\"([^\"]*)\" alt=\"([^\"]*)\" title=\"([^\"]*)\">/i", "<a data-fancybox=\"gallery\" href=\"\\1\" data-caption=\"\\3\"><img src=\"\\1\" alt=\"\\2\" title=\"\\3\"></a>", $content);

    return $content;
}

// 来自插件 WordsCounter
// https://github.com/elatisy/Typecho_WordsCounter
function allOfCharacters() {
    $chars = 0;
    $db = Typecho_Db::get();
    $select = $db ->select('text')
                  ->from('table.contents')
                  ->where('table.contents.status = ?','publish');
    $rows = $db->fetchAll($select);
    foreach ($rows as $row){
        $chars += mb_strlen($row['text'], 'UTF-8');
    }
    $unit = '';
    if ($chars >= 10000) {
        $chars /= 10000;
        $unit = 'W';
    } else if($chars >= 1000) {
        $chars /= 1000;
        $unit = 'K';
    }
    $out = sprintf('%.2lf%s',$chars, $unit);
    echo $out;
}

// 来自插件 IPLocation
function showLocation($ip) {
    require_once 'include/IP/IP.php';
    $addresses = IP::find($ip);
    $address = '';
    if ($addresses==='N/A'){
        $address = '';
    } else if (!empty($addresses)) {
        $addresses = array_unique($addresses);
        $address = implode('', $addresses);
        $address = str_replace('中国', '', $address);
    }
    echo $address;
}

// 来自插件 UserAgent
function getUAImg($type, $name, $title) {
    global $url_img;
    $img = "<img nogallery class='icon-ua' src='" . $url_img . $type . $name . ".svg' title='" . $title . "' alt='" . $title . "' height=16px style='vertical-align:-2px;' />";
    return $img;
}

function showUserAgent($ua) {
    global $url_img;
    $url_img = Helper::options()->themeUrl . '/include/UserAgent/img/';

    /* OS */
    require_once 'include/UserAgent/get_os.php';
    $Os = get_os($ua);
    $OsImg = getUAImg("os/", $Os['code'], $Os['title']);

    /* Browser */
    require_once 'include/UserAgent/get_browser_name.php';
    $Browser = get_browser_name($ua);
    $BrowserImg = getUAImg("browser/", $Browser['code'], $Browser['title']);

    echo "&nbsp;" . $OsImg . "&nbsp;" . $BrowserImg;
}
