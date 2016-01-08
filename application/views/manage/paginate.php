<div class="paginate">
    <div class="item-count pull-right">
        <?=Form::open(null, array('method'=> 'get'));?>
        <? if (!empty($_GET['item_count']))
        {
            $item_count = (int)$_GET['item_count'];
        }
        else
        {
            $item_count = 10;
        }?>
        <? $options = array(
            '5'  => '5', '10'=> '10', '15'=> '15', '20'=> '20', '25'=> '25', '50'=> '50', '100'=> '100'
        );?>
        <?=Form::label('item_count', 'на странице',array('style'=>'display: inline;'));?>
        <?=Form::select('item_count', $options, $item_count, array('onchange'=> 'submit()','style'=>'margin: 0;', 'class' => 'input-small'))?>
        <?=Form::close()?>
    </div>
    <div class="lister pull-left">
        <div class="btn-group">
        <?php if ($page > 1)
        {
            ?>
            <a class="page prev btn btn-small" href="<?=Url::site($link) . URL::query(array($key  => 1));?>"><i class="icon-chevron-left"></i></a>
            <a class="page btn btn-small" href="<?=Url::site($link) . URL::query(array($key  => $page - 1));?>"><?=($page - 1)?></a>
        <?php } ?>
        <span class="page selected btn btn-small btn-info"><?=$page?></span>
        <?php if ($page < $max_page)
        {
            ?>
            <a class="page btn btn-small" href="<?=Url::site($link) . URL::query(array($key  => $page + 1));?>"><?=($page + 1)?></a>
            <a class="page next btn btn-small" href="<?=Url::site($link) . URL::query(array($key  => $max_page));?>"><i class="icon-chevron-right"></i></a>
        <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
</div>