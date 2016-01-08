<?php if ($max_page > 1): ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#in-pagination').css('left', Math.round(($('#pagination-block').width() - $('#in-pagination').width()) / 2));
        $('#in-pagination').css('visibility', 'visible');
    });
</script>
<div class="pre-pagination-line"></div>
<div id="pagination-block">
    <div id="in-pagination">
    <?php if ($page == 2) { ?>
        <a href="<?=strip_tags(Security::xss_clean(Url::media($link))) ;?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=__('Previous')?></span></a>
    <?php } else { ?>
        <?php if ($page > 1) { ?>
        <a href="<?=strip_tags(Security::xss_clean(Url::media($link) .'/'. $key .'-'.  ($page - 1)));?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=__('Previous')?></span></a>
    <?php } else { ?>
    <span class="arrows"></span>
<?php } }?>
        <?php if ($page > 1) { ?>
        <?php if ($page > 2) { ?>
            <a href="<?=strip_tags(Security::xss_clean(Url::media($link))) ;?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span>1</span></a>
            <?php if ($page > 3) { ?>
                <span class="space-span">...</span>
            <?php } ?>
            <?php } ?>

            <?php if ($page == 2) { ?>
            <a href="<?=strip_tags(Security::xss_clean(Url::media($link))) ;?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=($page - 1)?></span></a>
            <?php } else { ?>
            <a href="<?=strip_tags(Security::xss_clean(Url::media($link) .'/'. $key .'-'. ($page - 1)));?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=($page - 1)?></span></a>
            <?php } }?>
        <a href="javascript:void(0)" class="selected-pagelink"><span><?=$page?></span></a>
        <?php if ($page < $max_page) { ?>
        <a href="<?=strip_tags(Security::xss_clean(Url::media($link) .'/'. $key .'-'. ($page + 1)));?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=($page + 1)?></span></a>
        <?php if ($page < $max_page-1) { ?>
            <?php if ($page < $max_page-2) { ?>
            <span class="space-span">...</span>
            <?php } ?>
            <a href="<?=strip_tags(Security::xss_clean(Url::media($link) .'/'. $key .'-'. $max_page));?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=($max_page)?></span></a>
        <?php } ?>
        <?php } ?>
    <?php if ($page < $max_page) { ?>
        <a href="<?=strip_tags(Security::xss_clean(Url::media($link) .'/'. $key .'-'. ($page + 1)));?><?=strip_tags(Security::xss_clean(Url::query()))?>"><span><?=__('Next')?></span></a>
    <?php } else{ ?>
        <span class="arrows"></span>
    <?php } ?>
    </div>
</div>
<?php endif; ?>