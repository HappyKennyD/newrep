<?php echo "Dear, $user! During the period from $period1 to $period2, the following sections
of 'History of Kazakhstan' e-history.kz portal were updated:" ?>
    <br/>
    <br/>
<?php foreach ($links as $link){?>
    <a href="<?php echo Subtool::media($link['link'])?>"><?php echo $link['title']?></a>
    <br/>
<?php }?>
    <br/>
    <br/>
<?php echo "In case you don’t want to receive notifications, please click on this <a href='$unsublink'>link<a>".'.'?>