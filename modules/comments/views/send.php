<a name="comments"></a>
<form method="POST">
<div class="mar-top-30">
    <div class="border-gray mar-top-10"></div>
    <div class="block-comment">
        <?php if (isset($user->profile->photo->file_path)):?><div class="ava"><img src="<?=HTML::chars(URL::media($user->profile->photo->file_path))?>" alt=""></div><?php endif;?>
        <div class="content">
            <div class="part"></div>
            <div class="info">
            </div>
            <textarea id="message" name="message" class="comment" placeholder="Оставить комментарий"></textarea>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="marg-bot-30 mar-top-10">
    <input id="send_message" name="send_message" class="locate-search-button" type="submit" value="<?=__('Отправить')?>">
</div>
</form>
<script type="text/javascript">
    $(document).ready(function()
    {
        $(".send_message").click(function(){
        });
    });
</script>
<div class="clear"></div>
