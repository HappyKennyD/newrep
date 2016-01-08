Здравствуйте, <?php echo $username ?> <br/>
    Список комментариев на ваши публикации за период с <?php echo $date_start?> по <?php echo $date_finish?><br>
    <?php foreach ($list_comment as $item ):?>
        Публикация: <a href="http://<?php echo$_SERVER['SERVER_NAME'].$item['link']; ?>"><?php echo $item['text']; ?></a>.<br>
        <?php echo $item['date'].'. '. $item['message']?><br>
        <br>
    <?php endforeach;?>
<br/>Отписаться от получения уведомлений можете в настройках профиля.