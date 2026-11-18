<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 友情链接
 *
 * @package custom
 */
?>
<?php $this->need('header.php'); ?>

<?php if ($this->fields->linkTo): ?>
    <script type='text/javascript'>window.location.href = '<?php echo $this->fields->linkTo ?>';</script>
<?php endif; ?>

<?php
$options = $this->options;
$parsed = daydream_parse_links_content($this->content);
$intro = $parsed['intro'];
$links = $parsed['links'];
$linkCount = count($links);
?>

<link rel="stylesheet" href="<?php $options->themeUrl('assets/css/page-links.css'); daydream_asset_ver('assets/css/page-links.css'); ?>">

<div class="links-page">

    <h1><i class="czs-link"></i> <?php $this->title(); ?></h1>

    <!-- 介绍文字 -->
    <?php if (trim(strip_tags($intro))): ?>
    <div class="links-intro post-content">
        <?php echo exContent($intro); ?>
    </div>
    <?php endif; ?>

    <hr>

    <!-- 搜索框 -->
    <?php if ($linkCount > 0): ?>
    <div class="links-search">
        <input type="text" id="links-filter" placeholder="搜索友链..." autocomplete="off">
    </div>
    <?php endif; ?>

    <!-- 友链卡片网格 -->
    <div class="links-grid" id="links-grid">
        <?php foreach ($links as $link): ?>
        <?php
            $domain = daydream_get_domain($link['url']);
            $avatar = $link['avatar'];
            $hasCustomAvatar = !empty($avatar);
        ?>
        <a class="link-card"
           href="<?php echo htmlspecialchars($link['url']); ?>"
           target="_blank" rel="noopener"
           data-name="<?php echo htmlspecialchars($link['name']); ?>"
           data-desc="<?php echo htmlspecialchars($link['desc']); ?>">
            <div class="link-card-avatar">
                <?php if ($hasCustomAvatar): ?>
                    <img src="<?php echo htmlspecialchars($avatar); ?>"
                         alt="<?php echo htmlspecialchars($link['name']); ?>"
                         width="48" height="48" loading="lazy">
                <?php else: ?>
                    <img class="lazy-favicon"
                         data-domain="<?php echo htmlspecialchars($domain); ?>"
                         data-name="<?php echo htmlspecialchars($link['name']); ?>"
                         alt="<?php echo htmlspecialchars($link['name']); ?>"
                         width="48" height="48">
                <?php endif; ?>
            </div>
            <div class="link-card-info">
                <div class="link-card-name"><?php echo htmlspecialchars($link['name']); ?></div>
                <?php if ($link['desc']): ?>
                <div class="link-card-desc"><?php echo htmlspecialchars($link['desc']); ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- 底部统计 -->
    <?php if ($linkCount > 0): ?>
    <div class="links-footer">
        共 <strong><?php echo $linkCount; ?></strong> 个友链
    </div>
    <?php endif; ?>

</div>

<script>var __faviconProxy = '<?php $options->themeUrl('favicon.php'); ?>';</script>
<script src="<?php $options->themeUrl('assets/js/page-links.js'); daydream_asset_ver('assets/js/page-links.js'); ?>"></script>

<?php $this->need('comments.php'); ?>
<?php $this->need('footer.php'); ?>
