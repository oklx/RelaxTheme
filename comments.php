<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
/* 自定义楼层渲染 */
function threadedComments($comments, $options) {
    $commentClass = '';
    if ($comments->levels > 0) $commentClass .= ' comment-ml';
    ?>

    <?php if ($comments->type == 'pingback' || $comments->type == 'traceback'): ?>
        <blockquote id="<?php $comments->theId(); ?>">
            被 <?php $comments->author(); ?> 引用。<br>
            <small><?php $comments->date('F jS, Y'); ?> at <?php $comments->date('h:i a'); ?></small>
        </blockquote>

    <?php else: ?>
        <div id="comment-<?php $comments->theId(); ?>" class="comment-card <?php echo $commentClass; ?>">
            <?php
                // 如果是 QQ 邮箱则使用 QQ 头像，否则使用 Gravatar
                $qq = str_replace('@qq.com', '', $comments->mail);
                if (strstr($comments->mail, "qq.com") && is_numeric($qq) && strlen($qq) < 11 && strlen($qq) > 4){
                    $avatarUrl = 'https://q3.qlogo.cn/g?b=qq&nk='.$qq.'&s=100';
                } else {
                    $avatarUrl = __TYPECHO_GRAVATAR_PREFIX__;
                    if (!empty($comments->mail)) $avatarUrl .= md5(strtolower(trim($comments->mail)));
                    $avatarUrl .= '?s=64&amp;r=' . Helper::options()->commentsAvatarRating . '&amp;d=' . Helper::options()->themeUrl.'/assets/img/visitor.png';
                }
            ?>

            <!-- 头部：头像 / 昵称 / 时间 -->
            <div class="comment-author">
                <img class="avatar circle" src="<?php echo $avatarUrl; ?>" alt="<?php echo $comments->author; ?>"/>
                <div class="author-line">
                    <cite class="fn"><b><?php $comments->author(); ?></b></cite>
                    <?php if ($comments->authorId == $comments->ownerId): ?>
                        <span class="badge comment-by-author"><i class="czs-forum-l"></i>&nbsp;博主</span>
                    <?php endif; ?>
                    <?php showUserAgent($comments->agent); ?>
                    <small class="from"><?php showLocation($comments->ip); ?></small>
                    <?php if ($comments->status == 'waiting'): ?>
                        <small class="badge"><i class="czs-talk-l"></i>&nbsp;等待审核</small>
                    <?php endif; ?>
                </div>
                <div class="comment-meta">
                    <?php $comments->date('F jS, Y'); ?> · <?php $comments->date('h:i a'); ?>
                </div>
            </div>

            <!-- 正文 -->
            <div class="comment-content">
                <?php $comments->content(); ?>
            </div>

            <!-- 操作 -->
            <div class="comment-reply">
                <?php
                // 给“回复”加上漂亮的胶囊按钮外观
                $comments->reply('<span class="btn btn-outline"><i class="czs-pen-write"></i>&nbsp;回复</span>');
                ?>
            </div>

            <!-- 子评论 -->
            <?php if ($comments->children): ?>
                <div class="comment-children">
                    <?php $comments->threadedComments($options); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif;
}
?>

<div id="comments">
    <?php $this->comments()->to($comments); ?>

    <?php if ($comments->have()): ?>
        <h3 class="comments-title"><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></h3>
        <?php $comments->listComments(array('before'=>'','after'=>'')); ?>
        <?php $comments->pageNav('«', '»'); ?>
    <?php endif; ?>

    <!-- 评论提交区域 -->
    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="respond">
            <div class="cancel-comment-reply">
                <?php $comments->cancelReply('<i class="czs-close"></i> 取消回复'); ?>
            </div>

            <?php if ($this->options->commentsNotice !=''): ?>
                <div class="alert" role="alert"><?php $this->options->commentsNotice(); ?></div>
            <?php endif; ?>

            <h2 id="response">添加新评论</h2>

            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" role="form">
                <?php if ($this->user->hasLogin()): ?>
                    <p>登录身份：
                        <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                        &nbsp;|&nbsp; <a href="<?php $this->options->logoutUrl(); ?>" title="Logout">退出 &raquo;</a>
                    </p>
                <?php else: ?>
                    <div class="row">
                        <input type="text" name="author" id="author" placeholder="称呼（必填）" value="<?php $this->remember('author'); ?>" required />
                        <input type="email" name="mail" id="mail" placeholder="邮箱（仅用于通知）" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?> />
                        <input type="url" name="url" id="url" placeholder="站点（可选）" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireURL): ?> required<?php endif; ?> />
                    </div>
                <?php endif; ?>

                <textarea rows="8" cols="50" name="text" id="textarea" placeholder="写点什么…" required><?php $this->remember('text'); ?></textarea>

                <!-- 提交按钮：主色渐变胶囊 -->
                <button id="submit" class="submit btn btn-primary" type="submit">
                    <i class="czs-send"></i>&nbsp;发表评论
                </button>
            </form>
        </div>

        <!-- Typecho 自带的回复脚本 -->
        <script>
        (function () {
            window.TypechoComment = {
                dom : function (id) { return document.getElementById(id); },
                create : function (tag, attr) {
                    var el = document.createElement(tag);
                    for (var key in attr) { el.setAttribute(key, attr[key]); }
                    return el;
                },
                reply : function (cid, coid) {
                    var comment = this.dom(cid), parent = comment.parentNode,
                        response = this.dom('<?php echo $this->respondId; ?>'), input = this.dom('comment-parent'),
                        form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
                        textarea = response.getElementsByTagName('textarea')[0];
                    if (null == input) {
                        input = this.create('input', { 'type' : 'hidden', 'name' : 'parent', 'id' : 'comment-parent' });
                        form.appendChild(input);
                    }
                    input.setAttribute('value', coid);
                    if (null == this.dom('comment-form-place-holder')) {
                        var holder = this.create('div', { 'id' : 'comment-form-place-holder' });
                        response.parentNode.insertBefore(holder, response);
                    }
                    comment.appendChild(response);
                    this.dom('cancel-comment-reply-link').style.display = '';
                    if (null != textarea && 'text' == textarea.name) { textarea.focus(); }
                    return false;
                },
                cancelReply : function () {
                    var response = this.dom('<?php echo $this->respondId; ?>'),
                        holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');
                    if (null != input) { input.parentNode.removeChild(input); }
                    if (null == holder) { return true; }
                    this.dom('cancel-comment-reply-link').style.display = 'none';
                    holder.parentNode.insertBefore(response, holder);
                    return false;
                }
            };
        })();
        </script>
    <?php endif; ?>
</div>
