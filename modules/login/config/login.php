<?php defined('SYSPATH') OR die('No direct access allowed.');
return array(
    'driver'       => 'ORM',
    'hash_method'  => 'sha256',
    'hash_key'     => '2343vsl; j ;jtw;56k j56p9 azf;gjasd;j',
    'lifetime'     => 1209600,
    'session_type' => Session::$default,
    'session_key'  => 'auth_user',
    //social - регистрация через соц сети и обычная, usually - обычная регистрация
    'type' => 'social',
    //подтверждение регистрации по почте
    'confirmation' => '1',
    //использовать защиту от перебора(вывод капчи при 3 неправильных вводах)
    'bruteforce' => '1',
);
