<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 关于
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
$aboutAvatar = $options->aboutAvatar ? $options->aboutAvatar : $options->logoUrl;
$aboutIntro = $options->aboutIntro;
$githubUsername = $options->githubUsername;
$aboutSkills = $options->aboutSkills;
$siteCreatedDate = $options->siteCreatedDate;
$education = daydream_parse_lines($options->aboutEducation, array('time', 'school', 'major', 'desc'));
$honors = daydream_parse_lines($options->aboutHonors, array('name', 'time', 'desc', 'color'));
$contacts = daydream_parse_lines($options->aboutContacts, array('type', 'value', 'qr'));
$websites = daydream_parse_lines($options->aboutWebsites, array('name', 'url', 'desc', 'icon'));
$socials = daydream_parse_lines($options->aboutSocials, array('platform', 'url', 'icon', 'iconUrl'));

// 获取文章数和总字数
Typecho_Widget::widget('Widget_Stat')->to($stat);
?>

<link rel="stylesheet" href="<?php $options->themeUrl('assets/css/page-about.css'); ?>">

<div class="about-page">

    <!-- 个人介绍 -->
    <?php if ($aboutIntro): ?>
        <div class="about-intro"><?php echo $aboutIntro; ?></div>
    <?php endif; ?>

    <!-- 编辑器中填写的自由内容 -->
    <?php $processedContent = exContent($this->content); ?>
    <?php if (trim(strip_tags($processedContent))): ?>
        <div class="about-content post-content">
            <?php echo $processedContent; ?>
        </div>
    <?php endif; ?>

    <!-- 教育经历 -->
    <?php if (!empty($education)): ?>
    <section id="about-education">
        <h2><i class="czs-blackboard-l"></i> 教育经历</h2>
        <div class="edu-timeline">
            <?php foreach ($education as $edu): ?>
            <div class="edu-item">
                <div class="edu-body">
                    <div class="edu-head">
                        <span class="edu-school"><?php echo htmlspecialchars($edu['school']); ?></span>
                        <?php if ($edu['time']): ?>
                        <span class="edu-time"><i class="czs-calendar-l"></i> <?php echo htmlspecialchars($edu['time']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($edu['major']): ?>
                    <div class="edu-major"><?php echo htmlspecialchars($edu['major']); ?></div>
                    <?php endif; ?>
                    <?php if ($edu['desc']): ?>
                    <div class="edu-desc"><?php echo htmlspecialchars($edu['desc']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 技能标签 -->
    <?php if ($aboutSkills): ?>
    <section id="about-skills">
        <h2><i class="czs-code-l"></i> 技能</h2>
        <div class="skill-tags">
            <?php foreach (array_map('trim', explode(',', $aboutSkills)) as $skill): ?>
                <?php if ($skill !== ''): ?>
                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 所获荣誉 -->
    <?php if (!empty($honors)): ?>
    <section id="about-honors">
        <h2><i class="czs-medal"></i> 所获荣誉</h2>
        <div class="honor-list">
            <?php foreach ($honors as $honor): ?>
            <?php $honorColorClass = daydream_honor_color_class($honor['color']); ?>
            <div class="honor-item<?php echo $honorColorClass ? ' ' . $honorColorClass : ''; ?>">
                <span class="honor-icon"><i class="czs-medal-l"></i></span>
                <div class="honor-body">
                    <div class="honor-head">
                        <span class="honor-name"><?php echo htmlspecialchars($honor['name']); ?></span>
                        <?php if ($honor['time']): ?>
                        <span class="honor-time"><?php echo htmlspecialchars($honor['time']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($honor['desc']): ?>
                    <div class="honor-desc"><?php echo htmlspecialchars($honor['desc']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 项目与网站 -->
    <?php if (!empty($websites) || $githubUsername): ?>
    <section id="about-projects">
        <h2><i class="czs-link-l"></i> 项目与网站</h2>

        <?php if (!empty($websites)): ?>
        <div class="about-cards-grid" id="website-cards">
            <?php foreach ($websites as $site): ?>
            <a class="about-card" href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank" rel="noopener">
                <div class="about-card-icon">
                    <?php if (!empty($site['icon'])): ?>
                        <img src="<?php echo htmlspecialchars($site['icon']); ?>" alt="" width="24" height="24" loading="lazy">
                    <?php else: ?>
                        <img class="lazy-favicon" data-domain="<?php echo htmlspecialchars(daydream_get_domain($site['url'])); ?>" data-name="<?php echo htmlspecialchars($site['name']); ?>" alt="" width="24" height="24">
                    <?php endif; ?>
                </div>
                <div class="about-card-info">
                    <div class="about-card-name"><?php echo htmlspecialchars($site['name']); ?></div>
                    <?php if ($site['desc']): ?>
                        <div class="about-card-desc"><?php echo htmlspecialchars($site['desc']); ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($githubUsername): ?>
        <?php
            $ghCount = intval($options->githubRepoCount) ?: 6;
            $ghInclude = array_filter(array_map('trim', preg_split('/[\r\n]+/', $options->githubInclude ?: '')));
            $ghExclude = array_filter(array_map('trim', preg_split('/[\r\n]+/', $options->githubExclude ?: '')));
        ?>
        <div class="about-cards-grid" id="github-repos"
             data-username="<?php echo htmlspecialchars($githubUsername); ?>"
             data-count="<?php echo $ghCount; ?>"
             <?php if (!empty($ghInclude)): ?>data-include="<?php echo htmlspecialchars(implode(',', $ghInclude)); ?>"<?php endif; ?>
             <?php if (!empty($ghExclude)): ?>data-exclude="<?php echo htmlspecialchars(implode(',', $ghExclude)); ?>"<?php endif; ?>>
            <div class="about-loading">
                <span>正在加载 GitHub 仓库...</span>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- 社交账号 -->
    <?php if (!empty($socials)): ?>
    <section id="about-social">
        <h2><i class="czs-heart"></i> 社交账号</h2>
        <div class="social-grid">
            <?php foreach ($socials as $social): ?>
            <?php
                $iconClass = daydream_social_icon_class($social['icon'] ?: $social['platform']);
                $domain = daydream_get_domain($social['url']);
                $iconUrl = isset($social['iconUrl']) ? $social['iconUrl'] : '';
            ?>
            <a class="social-card" href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" rel="noopener">
                <span class="social-icon">
                    <?php if ($iconUrl): ?>
                        <img src="<?php echo htmlspecialchars($iconUrl); ?>" alt="" width="20" height="20" loading="lazy">
                    <?php elseif ($iconClass): ?>
                        <i class="<?php echo $iconClass; ?>"></i>
                    <?php else: ?>
                        <img class="lazy-favicon" data-domain="<?php echo htmlspecialchars($domain); ?>" data-name="<?php echo htmlspecialchars($social['platform']); ?>" alt="" width="20" height="20">
                    <?php endif; ?>
                </span>
                <span class="social-name"><?php echo htmlspecialchars($social['platform']); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 联系方式 -->
    <?php if (!empty($contacts)): ?>
    <section id="about-contact">
        <h2><i class="czs-paper-plane-l"></i> 联系方式</h2>
        <div class="contact-grid">
            <?php foreach ($contacts as $contact):
                $ctype = trim($contact['type']);
                $cvalue = trim($contact['value']);
                $cqr = trim($contact['qr']);
                $cIconClass = daydream_social_icon_class($ctype);
                // 生成链接：邮箱 → mailto:，QQ 纯数字 → wpa.qq.com，其余原样
                $cHref = '';
                if ($cqr) {
                    // 二维码类型：不作为链接，悬停弹出二维码
                    $cTag = 'div';
                } else {
                    $cTag = 'a';
                    if (preg_match('/^(邮箱|mail|email|e-?mail|gmail)$/i', $ctype) && !preg_match('/^mailto:/i', $cvalue)) {
                        $cHref = 'mailto:' . $cvalue;
                    } elseif (preg_match('/^(qq|扣扣)$/i', $ctype) && preg_match('/^\d{5,11}$/', $cvalue)) {
                        $cHref = 'https://wpa.qq.com/msgrd?v=3&uin=' . rawurlencode($cvalue) . '&site=qq&menu=yes';
                    } else {
                        $cHref = $cvalue;
                    }
                }
            ?>
            <?php if ($cTag === 'a'): ?>
            <a class="contact-card" href="<?php echo htmlspecialchars($cHref); ?>" <?php if (preg_match('/^https?:/i', $cHref)): ?>target="_blank" rel="noopener"<?php endif; ?>>
            <?php else: ?>
            <div class="contact-card contact-qr" tabindex="0">
            <?php endif; ?>
                <span class="contact-icon">
                    <?php if ($cIconClass): ?>
                        <i class="<?php echo $cIconClass; ?>"></i>
                    <?php else: ?>
                        <i class="czs-link-l"></i>
                    <?php endif; ?>
                </span>
                <span class="contact-info">
                    <span class="contact-name"><?php echo htmlspecialchars($ctype); ?></span>
                    <?php if ($cvalue): ?>
                    <span class="contact-value"><?php echo htmlspecialchars($cvalue); ?></span>
                    <?php endif; ?>
                </span>
                <?php if ($cqr): ?>
                <span class="contact-qr-badge"><i class="czs-qrcode-l"></i></span>
                <?php endif; ?>
                <?php if ($cqr): ?>
                <span class="qr-pop">
                    <img src="<?php echo htmlspecialchars($cqr); ?>" alt="<?php echo htmlspecialchars($ctype); ?>二维码" loading="lazy">
                    <span class="qr-pop-tip"><?php echo htmlspecialchars($ctype); ?> 扫一扫</span>
                </span>
                <?php endif; ?>
            <?php if ($cTag === 'a'): ?></a><?php else: ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 站点统计 -->
    <section id="about-site-info">
        <h2><i class="czs-bar-chart"></i> 站点统计</h2>
        <div class="site-info-grid">
            <div class="info-item">
                <div class="info-value"><?php $stat->publishedPostsNum(); ?></div>
                <div class="info-label">篇文章</div>
            </div>
            <div class="info-item">
                <div class="info-value"><?php allOfCharacters(); ?></div>
                <div class="info-label">总字数</div>
            </div>
            <?php if ($siteCreatedDate): ?>
            <div class="info-item">
                <div class="info-value" id="site-days" data-created="<?php echo htmlspecialchars($siteCreatedDate); ?>">-</div>
                <div class="info-label">天运行</div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 本站主题 -->
    <section id="about-theme">
        <h2><i class="czs-pen-write"></i> 本站主题</h2>
        <div class="theme-info">
            <p>本站使用 <a href="https://typecho.org" target="_blank" rel="noopener">Typecho</a> 程序，主题为基于 <a href="https://github.com/Skywt2003/Daydream" target="_blank" rel="noopener">DayDream</a> 修改的 <a href="https://github.com/oklx/RelaxTheme" target="_blank" rel="noopener">RelaxTheme</a>。</p>
        </div>
    </section>

</div>

<script>var __faviconProxy = '<?php $options->themeUrl('favicon.php'); ?>';</script>
<script src="<?php $options->themeUrl('assets/js/page-about.js'); ?>"></script>

<?php $this->need('comments.php'); ?>
<?php $this->need('footer.php'); ?>
