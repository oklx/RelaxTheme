<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php if ($this->fields->linkTo): ?>
    <script type='text/javascript'>window.location.href = '<?php echo $this->fields->linkTo ?>';</script>
<?php endif; ?>

<?php if ($this->fields->headPic !=''): ?>
    <a data-fancybox="gallery" href="<?php $this->fields->headPic(); ?>" data-caption="<?php $this->title(); ?>">
        <img src=<?php $this->fields->headPic(); ?> class="shadow rounded" alt="<?php $this->title(); ?>" title="<?php $this->title(); ?>">
    </a>
<?php endif; ?> 

<h1><?php $this->title() ?></h1>
<div class="meta-info">
    <i class="czs-calendar"></i>
    <time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d D h:iA'); ?></time>
    <a href="<?php $this->options->siteUrl();?>lxzsnb/write-post.php?cid=<?php $this->cid();?>" title="Edit">
        <i class="czs-pen-write"></i> 编辑
    </a>
    <?php if ($this->fields->pubPlace != ''): ?>
        <br>
        <i class="czs-location"></i>
        <?php echo $this->fields->pubPlace; ?>
    <?php endif; ?>
</div>
<hr>

<?php $date1=date_create(date('c',$this->date->timeStamp)); $date2=date_create(date('c')); $days=date_diff($date1,$date2); ?>
<?php if ($this->options->oldPosts != '' && $days->format('%a') > $this->options->oldPosts): ?>
    <div class="alert" role="alert">
        <i class="czs-time"></i> 这是一篇发布于 <?php echo $days->format('%a'); ?> 天以前的旧文。其中的部分内容可能已经过时。
    </div>
<?php endif; ?>

<div class="post-content">
    <?php echo exContent($this->content); ?>
</div>
<hr>


<!-- 文章尾部信息：协议 / 分类 / 标签（简洁胶囊风） -->
<div class="post-meta sleek">
  <div class="meta-group">
    <span class="meta-title">协议</span>
    <a class="meta-badge" rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener">
      CC BY-SA 4.0
    </a>
  </div>

  <div class="meta-sep"></div>

  <div class="meta-group">
    <span class="meta-title">分类</span>
    <?php foreach ($this->categories as $c): ?>
      <a class="meta-badge" href="<?php echo $c['permalink']; ?>">
        <?php echo htmlspecialchars($c['name']); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (count($this->tags) > 0): ?>
    <div class="meta-sep"></div>
    <div class="meta-group">
      <span class="meta-title">标签</span>
      <?php foreach ($this->tags as $t): ?>
        <a class="meta-badge" href="<?php echo $t['permalink']; ?>">
          #<?php echo htmlspecialchars($t['name']); ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- 如果有没有标题的「说说」，这个上一篇/下一篇就无法跳转 -->
<!-- <div class="grid">
    <div><?php #$this->thePrev('上一篇：%s','没有了'); ?></div>
    <div style="text-align:right"><?php #$this->theNext('下一篇：%s','没有了'); ?></div>
</div> -->

<?php $this->need('comments.php'); ?>

<?php $this->need('footer.php'); ?>
