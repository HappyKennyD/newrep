<div class="paginate">
    <div class="item-count">
        <?=Form::open(null, array('method'=> 'get','style'=>'margin: 0 0 0 10px;'));?>
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
        <?=Form::select('item_count', $options, $item_count, array('onchange'=> 'submit()','style'=>'margin: 0;'))?>
        <?=Form::label('item_count', 'на странице',array('style'=>'display: inline;'));?>
        <?=Form::close()?>
    </div>
    <div class="lister" style="margin: 5px 0 5px 10px;">
        Страница:
        <?php if ($page > 1)
    {
        ?>
        <a class="page prev" href="<?=Url::site($link) . URL::query(array($key  => 1));?>">&lt;</a>
        <a class="page" href="<?=Url::site($link) . URL::query(array($key  => $page - 1));?>"><?=($page - 1)?></a>
        <?php } ?>
        <span class="page selected"><?=$page?></span>
        <?php if ($page < $max_page)
    {
        ?>
        <a class="page" href="<?=Url::site($link) . URL::query(array($key  => $page + 1));?>"><?=($page + 1)?></a>
        <a class="page next" href="<?=Url::site($link) . URL::query(array($key  => $max_page));?>">&gt;</a>
        <?php } ?>
    </div>
    <div class="clear"></div>
</div>

<!--<div class="pages">
    <div class="arrows left"></div>
    <a href="#" class="part before no-active">предыдущая</a>
    <a href="#" class="number active">1</a>
    <a href="#" class="number">2</a>
    <a href="#" class="number">3</a>
    <a href="#" class="number">4</a>
    <a href="#" class="part after">следующая</a>
    <div class="arrows right"></div>
</div>-->