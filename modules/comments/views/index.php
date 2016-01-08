<script type="text/javascript">
    $(document).ready(function () {
        $('#block').append( $('#comments-block') );

        var view_count_comment = 15;
        var all_count_comment = <?= $comments_count ?>;
        $("#enter").click(function () {
            $.ajax({
                url: "<?=Url::media('ajax_comments/send_comment') ?>",
                type: "POST",
                dataType: "json",
                data: { text: $('#message').val(), id: '<?=$id?>'},
                success: function (data) {
                    if (data == -1) {
                        $('#err-length').remove();
                        $('#error').append('<div id="err-length">Ошибка! Слишком короткий комментарий.</div>');
                    } else if (data) {
                        var photo = '';
                        var url = '<?php echo URL::site('profile/view/');?>' + data['id'];
                        if (data['photo'] != 0) {
                            photo = '<img src="<?php echo URL::media('/') ?>images/w65-h65-cct-si/' + data['photo'] + '" alt="">';
                        }
                        $('#err-length').remove();
                        $('#message').val('');
                        /*$('#comments-block').append(
                            '<div class="comment-block">' +
                                '<div class="comment-avatar">' +
                                '<div>' + photo + '</div></div>' +
                                '<div class="comment-text">' +
                                '<div class="comment-date">' + data['date'] + '  Комментарий будет добавлен после модерации</div>' +
                                '<div class="comment-author"><a href="#">' + data['name'] + '</a></div>' +
                                '<div class="comment-txt">' + data['text'] + '</div>' +
                                '</div><div class="comment-line"></div>');*/
                        $('#comments-block').append(
                            '<div class="one-comment">' +
                                '<div class="c-user-avatar">' +
                                    '<div>' + photo + '</div>' +
                                '</div>' +
                                '<div class="c-user-comment">' +
                                    '<p class="comment-information">' +
                                        '<strong>' +data['name']+'</strong><span>' + data['date'] + ' <?=__('Ваш комментарий находится на модерации')?>' + '</span>' +
                                    '</p>' +
                                    '<p class="comment-text">' + data['text'] + '</p>' +
                                '</div>' +
                            '</div>'
                        );
                        view_count_comment++;
                        all_count_comment++;
                    } else {
                        $('#err-length').remove();
                        $('#error').append('<div id="err-length">Ошибка Добавления комментария! Приносим свои извенения.</div>');
                    }

                },
                error: function () {
                    alert('Ошибка добавления коментария.')
                }
            });
        });
        $("#deploy_button").click(function (e) {
            e.preventDefault();
            $.ajax({
                url: "<?=Url::media('ajax_comments/deploy_comment') ?>",
                type: "POST",
                dataType: "json",
                data: { count_comment: view_count_comment, id: '<?=$id?>' },
                success: function (data) {
                    if (data.length > 0) {
                        $.each(data, function (i, value) {
                            var photo = '';
                            var url = '<?php echo URL::site('profile/view/');?>' + value['id'];
                            if (value['photo'] != 0) {
                                photo = '<img src="<?php echo URL::media('/') ?>images/w65-h65-ccc-si/' + value['photo'] + '" alt="">';
                            }
                            /*$('#comments-block').append(
                                '<div class="comment-block">' +
                                    '<div class="comment-avatar">' +
                                    '<div>' + photo + '</div></div>' +
                                    '<div class="comment-text">' +
                                    '<div class="comment-date">' + value['date'] + ' Комментарий будет добавлен после модерации</div>' +
                                    '<div class="comment-author"><a href="#">' + value['name'] + '</a></div>' +
                                    '<div class="comment-txt">' + value['text'] + '</div>' +
                                    '</div><div class="comment-line"></div>');*/
                            $('#comments-block').append(
                                '<div class="one-comment">' +
                                    '<div class="c-user-avatar">' +
                                    '<div>' + photo + '</div>' +
                                    '</div>' +
                                    '<div class="c-user-comment">' +
                                    '<p class="comment-information">' +
                                    '<strong><a href="' + url + '">' + value['name'] + '</a></strong><span>' + value['date'] + '</span>' +
                                    '</p>' +
                                    '<p class="comment-text">' + value['text'] + '</p>' +
                                    '</div>' +
                                    '</div>'
                            );
                        });
                        view_count_comment = view_count_comment + data.length;
                        if (view_count_comment >= all_count_comment) {
                            $('#deploy_button').remove();
                        }
                    }
                },
                error: function () {
                    //alert('Ошибка добавления коментария.')
                }
            });
        });
    });
</script>
<link rel="stylesheet" href="<?= URL::media('media/css/comments.css') ?>" />
<?php /*
<div id="comments-block">
        <?php foreach ($commentaries as $comment): ?>
        <div class="comment-block">
        <div class="comment-avatar">
            <?php if (isset($comment->user->profile->img->file_path)): ?><div><img src="<?php echo URL::media('images/w75-h75-ccc-si/' . $comment->user->profile->img->file_path) ?>" alt="" /></div><?php endif; ?>
        </div>
        <div class="comment-text">
            <div class="comment-date"><?= HTML::chars(Date::translate($comment->date)) ?><?php if (!$comment->status):?>Ваш комментарий находится на модерации<?php endif?></div>
            <div class="comment-author"><a href="#"><?= HTML::chars($comment->user->show_name()) ?></a></div>
            <div class="comment-txt"><?= HTML::chars($comment->text) ?></div>
        </div>
        <div class="comment-line"></div>
    </div>
    <?php endforeach; ?>
    </div>
    <div class="clear"></div>
    <div id="deploy">
        <?php if ($comments_count > 15): ?>
            <input type="button" value="<?= __('Развернуть') ?>" id="deploy_button" class="comments-deploy-button" />
        <?php endif; ?>
    </div>
<?php if ($user): ?>
    <div class="error" id="error"></div>
    <div class="comment-block">
        <div class="comment-avatar">
            <?php if (isset($user->profile->img->file_path)): ?><div><img src="<?php echo URL::media('images/w75-h75-ccc-si/' . $user->profile->img->file_path) ?>" alt="" /></div><?php endif; ?>
        </div>
        <textarea id="message" name="message" class="comment-form"></textarea>
        <a id="enter" class="comment-button">Добавить</a>
        <div class="comment-line"></div>
        </div>
<?php endif; ?>
 */ ?>
<div id="comments-block">
    <?php foreach ($commentaries as $comment): ?>
        <div class="one-comment">
            <div class="c-user-avatar">
                <?php if (isset($comment->user->profile->img->file_path)): ?>
                    <div>
                        <img src="<?php echo URL::media('images/w65-h65-cct-si/' . $comment->user->profile->img->file_path) ?>" alt="" />
                    </div>
                <?php endif; ?>
            </div>
            <div class="c-user-comment">
                <p class="comment-information">
                    <strong>
                        <?php if (!$comment->status):?>
                            <?= HTML::chars($comment->user->show_name()) ?>
                        <?php endif?>
                    </strong>
                    <span>
                        <?= HTML::chars(Date::translate($comment->date, 'd.m.Y')) ?>, <?= HTML::chars(Date::translate($comment->date, 'H:i')) ?>
                        <?php if (!$comment->status):?>
                            <?=__('Ваш комментарий находится на модерации')?>
                        <?php endif?>
                    </span>
                </p>

                <p class="comment-text"><?= HTML::chars($comment->text) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="clear"></div>
    <div id="deploy" style="padding-top: 10px;">
<!--        --><?php //if ($comments_count > 15): ?>
<!--            <a id="deploy_button" href="#" class="comment-button">--><?//=__('Загрузить еще')?><!--</a>-->
<!--        --><?php //endif; ?>
    </div>
</div>
<?php if ($user): ?>
    <div class="error" id="error"></div>
    <div class="add-comment-block">
        <textarea name="message" id="message" placeholder="<?=__('Оставить комментарий')?>"></textarea>
        <span>
            <a id="enter" class="comment-button"><?=__('Добавить')?></a>
        </span>
    </div>
<?php endif; ?>
