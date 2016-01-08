<?php defined('SYSPATH') or die('No direct script access.');
return array(
    'username' => array(
        'not_empty' => 'Поле :field не должно быть пустое',
        'min_length' => 'Имя пользователя должно быть не короче :param2 символов',
        'max_length' => 'Имя пользователя должно быть не длинее чем  :param2',
        'unique' => 'Пользователь с таким логином уже зарегистрирован',
    ),
    'password' => array(
        'not_empty' => 'Поле пароля не должно быть пустое',
    ),
    'password_confirm' => array(
        'not_empty' => 'Поле подтверждение пароля не должно быть пустое',
    ),
    'email' => array(
        'not_empty' => 'Поле почта не должно быть пустое',
        'unique' => 'Пользователь с такой почтой уже зарегистрирован',
    ),
);