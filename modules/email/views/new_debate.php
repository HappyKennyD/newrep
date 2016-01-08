<?php echo __("Здравствуйте! Настоящим письмом :author и Портал Истории Казахстана :link  приглашают вас принять участие в дебатах на тему :theme.", array(':author' => $author,':link' => $link,':theme' => $theme));
if ($ending!==true){
    echo __(" Для участия вам необходимо зарегистрироваться на портале. Регистрация доступна по следующему адресу: :ending.", array(':ending' => $ending));
}
if ($ending_tr!==true){
    echo __(" :ending_tr.", array(':ending_tr' => $ending_tr));
}
/* echo __('Hello, :user', array(':user' => 'asdda'));
$author
$link
$debate_link
$theme*/