<?php echo "Уважаемый(ая), $user! На портале 'История Казахстана' e-history.kz, в период с $period1 по $period2 были обновлены следующие разделы:" ?>
<br/>
<br/>
<?php foreach ($links as $link) { ?>
    <a href="<?php echo Subtool::media($link['link'])?>"><?php echo $link['title'] ?></a>
    <br/>
<?php }?>
<br/>
<br/>
<?php echo "В случае, если вы хотите отписаться от рассылки уведомлений, то вам необходимо перейти по этой <a href='$unsublink'>ссылке<a>".'.'?>