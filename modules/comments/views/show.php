<?PHP foreach($commentaries as $comment):?>
    <div class="block-comment">
    <?php if (isset($user->profile->photo->file_path)):?><div class="ava"><img src="<?=HTML::chars(URL::media($comment->user->profile->photo->file_path))?>" alt=""></div><?php endif;?>
        <div class="content">
            <div class="part"></div>
            <div class="info">
                <a href="<?= URL::site('profile/index/'.$comment->user->id) ?>" class="text-nick mar-right-20 user"><?=HTML::chars($comment->user->profile->first_name)?></a>
                <span class="mar-right-20"><?=HTML::chars($comment->date)?></span>
                <span class="red-font mar-right-10"><!--|--></span>
            </div>
            <div class="comment">
                <?=HTML::chars($comment->text)?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
<?php endforeach;?>


