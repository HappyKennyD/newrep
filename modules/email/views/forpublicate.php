Здравствуйте, <?= $username ?> <br />
Пользователь <?=$auth_username;?> отправил вам на рассмотрение статью «<?= $publication ?>».<br />
<a href="http://<?=$_SERVER['SERVER_NAME'];?>/publication/<?=$type;?>/view/<?=$id;?>">Просмотреть</a><br />