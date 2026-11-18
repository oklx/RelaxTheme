<?php
/**
 * Daydream 是一个简洁轻盈的 Typecho 主题。
 * 
 * @package Daydream
 * @author SkyWT
 * @version 1.0
 * @link https://blog.skywt.cn/
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
    $this->need('header.php');
?>

<?php if ($this->options->notification !=''): ?>
    <div class="alert">
        <?php $this->options->notification(); ?>
    </div>
<?php endif; ?>

<?php while ($this->next()): ?>
    <section itemscope itemtype="http://schema.org/BlogPosting">
        <?php if ($this->fields->headPic !=''): ?>
            <a data-fancybox="gallery" href="<?php $this->fields->headPic(); ?>" data-caption="<?php $this->title(); ?>">
                <img src=<?php $this->fields->headPic();?> class="shadow rounded" alt="<?php $this->title(); ?>" title="<?php $this->title(); ?>">
            </a>
        <?php endif; ?>
        <a itemprop="url" href="<?php $this->permalink();?>">
            <h2 itemprop="name headline"><?php $this->title();?></h2>
        </a>

        <!-- ADDED: 标题下方显示发表时间（与文章页一致的格式） -->
        <div class="meta-time" style="font-size:.9em;opacity:.85;margin:.25rem 0 .75rem;display:flex;align-items:center;gap:.4em;">
            <i class="czs-calendar"></i>
            <time itemprop="datePublished" datetime="<?php $this->date('c'); ?>">
                <?php $this->date('Y-m-d D h:iA'); ?>
            </time>
        </div>
        <!-- /ADDED -->

        <div class="summary" itemprop="articleBody">
            <?php
            ob_start();
            $this->content('阅读全文');
            $summary = ob_get_clean();
            echo daydream_demote_headings($summary);
            ?>
        </div>
    </section>
    <hr>
<?php endwhile; ?>

<nav>
    <?php $this->pageNav('&laquo;', '&raquo;', 3, '...', array(
        'wrapTag' => 'ul',
        'wrapClass' => '',
        'itemTag' => 'li',
        'currentClass' => 'active',
    )); ?>
</nav>

<?php $this->need('footer.php'); ?>
