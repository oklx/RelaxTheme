# Daydream 主题文档

> Daydream 是一个简洁轻盈的 Typecho 博客主题，基于 PicoCSS 构建，采用单栏布局，支持深色/浅色模式切换与 PJAX 无刷新导航。

- **作者**：SkyWT
- **版本**：1.0
- **官网**：[https://blog.skywt.cn/](https://blog.skywt.cn/)
- **设计理念**：极简、轻盈、专注内容

---

## 目录结构

```
Daydream/
├── index.php               # 主题入口 + 首页模板
├── header.php              # 公共头部（HTML head、导航栏、内联脚本）
├── footer.php              # 公共底部（内容增强脚本、PJAX 配置、页脚）
├── page.php                # 默认独立页面模板
├── page-about.php          # 「关于」页面模板
├── page-links.php          # 「友情链接」页面模板
├── post.php                # 文章详情模板
├── comments.php            # 评论渲染模板
├── archives.php            # 文章归档页模板
├── 404.php                 # 404 错误页
├── functions.php           # 主题配置项 + 辅助函数
├── screenshot.png          # 主题截图
├── LICENSE                 # 开源协议
├── README.md               # 项目说明
├── docs/
│   └── README.md           # 本文档
├── assets/
│   ├── css/
│   │   ├── pico.min.css          # PicoCSS UI 框架
│   │   ├── style.css             # 主题核心样式
│   │   ├── animate.min.css       # CSS 动画库
│   │   ├── caomei.css            # 草莓图标库（StrawberryIcon）
│   │   ├── jquery.fancybox.min.css  # Fancybox 灯箱样式
│   │   ├── katex.min.css         # KaTeX 数学公式样式
│   │   ├── atom-one-dark.min.css # Highlight.js 暗色主题
│   │   ├── page-about.css        # 「关于」页面专用样式
│   │   └── page-links.css        # 「友链」页面专用样式
│   ├── js/
│   │   ├── jquery.min.js         # jQuery
│   │   ├── jquery.pjax.js        # PJAX 插件
│   │   ├── highlight.min.js      # 代码语法高亮
│   │   ├── jquery.fancybox.min.js  # Fancybox 灯箱
│   │   ├── page-about.js         # 「关于」页面脚本
│   │   ├── page-links.js         # 「友链」页面脚本
│   │   └── katex/
│   │       ├── katex.min.js      # KaTeX 数学渲染
│   │       └── auto-render.min.js  # KaTeX 自动渲染
│   ├── fonts/                    # KaTeX 数学字体
│   └── img/                      # 主题图片资源
└── include/
    ├── IP/
    │   └── IP.php                # IP 归属地查询
    └── UserAgent/
        ├── get_browser_name.php  # 浏览器识别
        ├── get_os.php            # 操作系统识别
        └── img/                  # UA 图标
```

---

## 模板文件说明

### `header.php` — 公共头部

包含完整的 `<head>` 区域和导航栏，主要功能：

| 功能 | 说明 |
|------|------|
| 防闪烁脚本 | 页面加载前读取 `localStorage` 设置 `data-theme`，避免白屏闪烁 |
| Meta 标签 | 自动生成 `description`、`title`（含归档标题） |
| CSS 加载 | PicoCSS、主题样式、图标库、Fancybox、KaTeX、Highlight.js |
| 字体加载 | Google Fonts — Noto Serif SC |
| 内联样式 | 代码块、评论区、进度条、按钮等组件样式 |
| 导航栏 | 固定链接 + 主题切换按钮，`position: sticky` 吸顶 |
| 主题切换 | `localStorage` 存储，同步 `<html data-theme>` 属性 |
| 阅读进度条 | `scaleX()` 变换，`requestAnimationFrame` 优化 |
| Logo 旋转 | 悬停触发 CSS 动画 |
| PJAX 容器 | `<main class="container" id="pjax-container">` |

### `footer.php` — 公共底部

| 功能 | 说明 |
|------|------|
| 代码块增强 | 自动添加语言标签、复制按钮、行号、语法高亮 |
| 内容装饰 | 图片添加 `.shadow .rounded`，引用块添加样式 |
| Fancybox 初始化 | 图片灯箱功能 |
| KaTeX 渲染 | 数学公式渲染 |
| `hydrate()` 函数 | 统一执行所有内容增强，PJAX 切换后重新调用 |
| PJAX 配置 | 拦截站内链接，替换 `#pjax-container` 内容 |
| 页脚信息 | 备案号、版权、文章数、总字数 |

### `page.php` — 默认页面模板

简洁的独立页面渲染：标题 → 分隔线 → 内容（经 `exContent()` 处理） → 评论区。

### `page-about.php` — 「关于」页面模板

自定义模板，包含个人简介头图区、技能标签、项目与网站展示、GitHub 仓库动态加载、社交账号、站点统计等模块。

### `page-links.php` — 「友情链接」页面模板

自定义模板，解析 `[links]...[/links]` 短代码渲染友链卡片网格，支持搜索过滤和 favicon 自动获取。

### `post.php` — 文章详情模板

包含：头图（Fancybox）→ 标题 → 发布信息（日期、地点、编辑链接）→ 旧文提示 → 正文内容 → 文章元信息（协议、分类、标签）→ 评论区。

### `comments.php` — 评论模板

自定义评论渲染函数 `threadedComments()`：头像（QQ/Gravatar）、作者信息（徽章、UA 图标、IP 归属地）、嵌套回复、评论表单。

### `archives.php` — 归档页模板

按年份分组展示所有文章列表，显示文章总数和总字数统计。

### `404.php` — 错误页

显示 404 提示和随机一言（Hitokoto API），附带返回首页链接。

---

## 主题配置项

在 Typecho 后台「控制台 → 外观 → 设置外观」中配置：

### 基础设置

| 配置项 | 字段名 | 类型 | 说明 |
|--------|--------|------|------|
| 站点 LOGO 地址 | `logoUrl` | Text | 网站头部 LOGO 图片 URL |
| 全站首页 | `realHomepage` | Text | 导航栏首页链接，留空不显示 |
| ICP 备案号 | `icpInfo` | Text | 显示在页脚 |
| 网安备案号 | `nisInfo` | Text | 显示在页脚（带国徽图标） |
| 网站公告 | `notification` | Text | 首页顶部公告横幅 |
| 文章有效期 | `oldPosts` | Text | 超过此天数显示旧文提示，默认 365 |
| 评论区公告 | `commentsNotice` | Text | 评论区顶部公告 |
| 头部代码 | `headerCode` | Textarea | 自定义 `<head>` 内 HTML/JS |
| 页脚代码 | `footerCode` | Textarea | 自定义页脚 HTML/JS |
| 自定义 CSS | `cunstomCSS` | Textarea | 自定义 CSS 规则 |

### 关于页面设置

| 配置项 | 字段名 | 类型 | 说明 |
|--------|--------|------|------|
| 个人介绍 | `aboutIntro` | Textarea | 支持 HTML 的个人介绍文字 |
| 头像地址 | `aboutAvatar` | Text | 留空则使用站点 LOGO |
| 教育经历 | `aboutEducation` | Textarea | 每行一个：`时间\|学校\|专业\|说明(可选)`，简历式时间轴 |
| GitHub 用户名 | `githubUsername` | Text | 用于 API 获取仓库列表 |
| GitHub 显示数量/包含/排除 | `githubRepoCount` / `githubInclude` / `githubExclude` | Text/Textarea | 展示数量（默认 6）；必显示/不显示的仓库名列表 |
| 个人网站 | `aboutWebsites` | Textarea | 每行一个：`名称\|URL\|描述\|图标URL(可选)` |
| 社交账号 | `aboutSocials` | Textarea | 每行一个：`平台名\|URL\|图标名(可选)\|图标URL(可选)` |
| 联系方式 | `aboutContacts` | Textarea | 每行一个：`类型\|内容\|二维码图片URL(可选)`。链接类自动识别邮箱 `mailto:`、QQ 号（wpa.qq.com）；带二维码的项鼠标悬停弹出二维码 |
| 技能标签 | `aboutSkills` | Text | 逗号分隔的技能列表 |
| 所获荣誉 | `aboutHonors` | Textarea | 每行一个：`名称\|获奖时间\|小字说明(可选)\|图标颜色(可选)`。颜色可选：金（默认）/ 银 / 铜 / 红 / 橙 / 蓝 / 绿 / 紫 / 青 / 粉，也支持英文名 |
| 网站创建日期 | `siteCreatedDate` | Text | 格式 `YYYY-MM-DD`，用于计算运行天数 |

---

## 自定义字段

在文章/页面编辑界面的「自定义字段」中设置：

| 字段名 | 适用范围 | 说明 |
|--------|----------|------|
| `headPic` | 文章 | 头图 URL，显示在文章顶部（点击 Fancybox 放大） |
| `pubPlace` | 文章 | 发布地点，显示在文章元信息中 |
| `pageIcon` | 页面 | 草莓图标库的图标类名，显示在导航栏链接前 |
| `linkTo` | 文章/页面 | 重定向 URL，打开时自动跳转 |

---

## 内容处理功能（`exContent()`）

所有文章和页面内容都经过 `exContent()` 函数处理，提供以下功能：

### 标题降级

自动将 H1→H2、H2→H3……避免文章内标题与页面标题冲突。

### 短代码

| 短代码 | 渲染结果 |
|--------|----------|
| `[info]提示内容[/info]` | 蓝色提示框（`.alert`） |
| `[htmlbox width="100%" ratio="16:9"]HTML内容[/htmlbox]` | 自适应 iframe 展示框 |

`[htmlbox]` 支持属性：`width`、`height`、`ratio`（宽高比）、`scroll`（滚动方式）。

### 自动目录（TOC）

自动从 H1-H5 标题生成嵌套目录，折叠在 `<details>` 标签中。

### KaTeX 数学公式

`$$...$$` 和 `\[...\]` 包裹的展示型公式自动添加横向滚动容器。

### Fancybox 图片灯箱

文章中的 `<img>` 自动包裹 Fancybox 链接，支持点击放大、画廊浏览。

---

## CSS 架构

### 基础框架

基于 **PicoCSS**（`pico.min.css`），提供响应式排版、表单、按钮等基础样式。

### 主题变量

```css
:root {
    --font-family: 'Noto Serif SC', serif;
    --line-height: 1.65;
    --primary: #1e88e5;
    --card-box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), ...;
}
```

### 深浅色模式

通过 `<html data-theme="dark|light">` 属性切换。CSS 选择器使用 `[data-theme="dark"]` 覆盖默认（浅色）样式。同时支持 `prefers-color-scheme` 媒体查询作为降级。

### 布局约束

```css
.container { max-width: 768px; }
```

单栏居中布局，所有内容限制在 768px 宽度内。

### 图标系统

使用 **草莓图标库（StrawberryIcon / Caomei）** 2.0.0 Free 版本，类名前缀 `czs-`。

---

## JavaScript 功能

### PJAX 无刷新导航

- **库**：jquery.pjax.js
- **容器**：`#pjax-container`（`<main>` 标签）
- **行为**：拦截站内链接，仅替换 `#pjax-container` 内容
- **事件**：`pjax:send`（开始加载）、`pjax:complete`（加载完成后重新 hydrate）

### 代码高亮

使用 Highlight.js，在 `hydrate()` 中调用 `hljs.highlightElement()`。代码块自动增强为卡片样式（语言标签、复制按钮、行号）。

### 主题切换

`localStorage` 存储用户偏好，点击切换按钮在 `light`/`dark` 间切换，同步更新 `<html data-theme>` 和按钮图标。

### 阅读进度条

`#read-progress` 元素，使用 `scaleX()` 变换 + `requestAnimationFrame` 优化性能。

### 内容增强（hydrate）

`hydrate()` 函数在首屏加载和 PJAX 切换后执行：

1. `decorateContent()` — 为图片、引用块添加样式类
2. `enhanceCodeBlocks()` — 构建代码卡片
3. `initFancybox()` — 初始化图片灯箱
4. `renderMath()` — 渲染 KaTeX 数学公式

---

## 第三方依赖

| 库 | 版本 | 用途 |
|----|------|------|
| PicoCSS | - | CSS UI 框架 |
| jQuery | - | DOM 操作、事件处理 |
| jquery.pjax | - | 无刷新导航 |
| Highlight.js | - | 代码语法高亮 |
| Fancybox | - | 图片灯箱 |
| KaTeX | - | 数学公式渲染 |
| Animate.css | - | CSS 动画 |
| StrawberryIcon (Caomei) | 2.0.0 Free | 图标库 |
| Noto Serif SC | - | Google Fonts 中文衬线字体 |
| JetBrains Mono | - | 代码等宽字体 |

---

## 新增页面模板使用说明

### 使用「关于」页面

1. 在 Typecho 后台「撰写 → 新建页面」
2. 页面模板选择「关于」
3. 在编辑器中填写自由内容（Markdown/HTML 均可，经 `exContent()` 处理）
4. 在「控制台 → 外观 → 设置外观」中填写关于页面的配置项

### 使用「友情链接」页面

1. 在 Typecho 后台「撰写 → 新建页面」
2. 页面模板选择「友情链接」
3. 在编辑器中按以下格式填写：

```
这里写友链页面的介绍文字，支持 Markdown/HTML。

[links]
站名|URL|描述|头像URL（可选）
张三的博客|https://zhangsan.com|热爱技术的开发者|https://zhangsan.com/avatar.png
李四的小站|https://lisi.dev|设计与前端|
王五的空间|https://wangwu.me|日常记录
[/links]
```

- `[links]...[/links]` 外的内容作为介绍文字
- 每行用 `|` 分隔：`名称|URL|描述|头像URL`
- 头像 URL 留空时自动获取域名 favicon
