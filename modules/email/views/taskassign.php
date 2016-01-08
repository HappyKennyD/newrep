Здравствуйте, <?= $username ?> <br />
Изменение задачи «<?= $task_title ?>»<br />
Статус задачи: <?=$status;?><br />
Ответственный: <?=$target_username;?><br />
<a href="http://<?=$_SERVER['SERVER_NAME'];?><?=URL::site('/task/show/'.$id);?>">Просмотреть</a><br />
