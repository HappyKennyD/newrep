<?php echo "Құрметті, $user! e-history.kz «Қазақстан тарихы» порталында мына аралықта  $period1 - $period2 төмендегі тараулар жаңартылды:" ?>
    <br/>
    <br/>
<?php foreach ($links as $link){?>
    <a href="<?php echo Subtool::media($link['link'])?>"><?php echo $link['title']?></a>
    <br/>
<?php }?>
    <br/>
    <br/>
<?php echo "Егер  сіз хабарламадан бас тартқыңыз келсе, мына <a href='$unsublink'>сілтемеге өтіңіз<a>"."."?>