<?php
class Api
{
    /*
     * Срок действия токена для api
     * @param string $token - токен
     * @param int $interval=интервал жизни токена
     * @return boolean
     */
    public function token_expires($token, $interval=172800)
    {
        $token_expires = ORM::factory('Api_Token')->where('token', '=', $token)->find();
        $now = time();
        if( $token_expires->loaded() )
        {
            if($token_expires->expires + $interval > $now)
            {
                return true;
            }
        }

        return false;
    }

    /*
     * Записать новый токен
     * @param int $user_id - id пользователя
     * @param string $api_token - токен
     */
    public function create_token($user_id, $api_token)
    {
        $token_expires = ORM::factory('Api_Token');
        $token_expires->user_id = $user_id;
        $token_expires->token = $api_token;
        $token_expires->expires = time();
        $token_expires->save();
    }

    /*
     * Обновить время последнего обращения к токену
     * @param string $api_token - токен
     */
    public function update_token($api_token='')
    {
         $token = ORM::factory('Api_Token')->where('token', '=', $api_token)->find();
         if($token->loaded()){
             $token->expires = time();
             $token->update();
         }
    }

    /*
     * Вернет дату и время последнего обращения к сервису уведомлений
     * @param int $user_id - id пользователя
     * @return string  $date - дата и время последнего обращения к сервису уведомлений
     */
    public function get_last_query_notice($user_id)
    {
        $date = '';

        //последнее время обращения к сервису по id - пользователя в таблице Api_Queries
        $last_query = ORM::factory('Api_Querie')->where('user_id', '=', $user_id)->find();

        //если есть запись последнего обращения в таблице Api_Queries , то берем время из таблицы
        if( $last_query->loaded() )
        {
            $date = $last_query->date;

            $last_query->date       = date('Y-m-d H:i:s');
            $last_query->update();
        }
        // если нет записи последнего обращения, то берем время последей аутентификации пользователя, и записываем время обращения в Api_Queries
        else
        {
            $user = ORM::factory('User', $user_id);

            if( !empty($user->last_login) )
            {
                $date = date('Y-m-d H:i:s', $user->last_login);
            }
            else
            {
                $date = date('Y-m-d H:i:s');
            }

            $last_query             = ORM::factory('Api_Querie');
            $last_query->user_id    = $user_id;
            $last_query->date       = date('Y-m-d H:i:s');
            $last_query->save();
        }

        return $date;
    }

    public function check_notice($user_id)
    {
        //узнаем время последнего обращения к сервису
        $date = $this->get_last_query_notice($user_id);


        //вытаскиваем дебаты пользователя
        $debates = ORM::factory('Debate')->where_open()->where('author_id', '=', $user_id)->or_where('opponent_id', '=', $user_id)->where_close()->and_where('is_public', '=', 1)->and_where('is_closed', '=', 0)->find_all();

        if($debates->count() > 0)
        {
            foreach($debates as $debate)
            {
                $user_debates[]      = $debate->id;
            }

            $debate_opinions = ORM::factory('Debate_Opinion')->where('debate_id', 'in', $user_debates)->and_where('author_id', '<>', $user_id)->and_where('date', '>', $date)->find_all();
            if($debate_opinions->count() > 0)
            {
                $i=0;

                foreach($debate_opinions as $debate_opinion)
                {
                    $notice = ORM::factory('Api_Notice')->where('opinion_id', '=', $debate_opinion->id)->and_where('type', '=', 'OPINION')->find();
                    if( !$notice->loaded() )
                    {
                        // сохраняем уведомления по пользователю
                        $notice->user_id           = $user_id;
                        $notice->object_id         = $debate_opinion->debate_id;
                        $notice->opinion_id        = $debate_opinion->id;
                        $notice->type              = 'OPINION';
                        $notice->entry_type        = 'DEBATE';
                        $notice->date              = $debate_opinion->date;
                        $notice->flag              = 0;
                        $notice->save();

                        $i++;
                    }
                }
            }

            $debate_comments = ORM::factory('Debate_Comment')->where('debate_id', 'in', $user_debates)->and_where('user_id', '<>', $user_id)->and_where('date', '>', $date)->find_all();
            if($debate_comments->count() > 0)
            {
                $i=0;

                foreach($debate_comments as $debate_comment)
                {
                    $notice = ORM::factory('Api_Notice')->where('opinion_id', '=', $debate_comment->id)->and_where('type', '=', 'COMMENT')->find();
                    if( !$notice->loaded() )
                    {
                        // сохраняем уведомления по пользователю
                        $notice->user_id           = $user_id;
                        $notice->object_id         = $debate_comment->debate_id;
                        $notice->opinion_id        = $debate_comment->id;
                        $notice->type              = 'COMMENT';
                        $notice->entry_type        = 'DEBATE';
                        $notice->date              = $debate_comment->date;
                        $notice->flag              = 0;
                        $notice->save();

                        $i++;
                    }
                }
            }
        }
    }

    public function get_path($user_id)
    {
        $config    = Kohana::$config->load('storage');
        $directory = $config['dir'].$user_id.'/'.date("Y").'/'.date("m").'/'.date("d");
        $p         = explode('/', $directory);
        $directory = '';
        for($i = 1; $i < count($p); $i++)
        {
            $directory .= $p[$i].'/';
            if(!is_dir($directory))
            {
                mkdir($directory, 02777);

                chmod($directory, 02777);
            }
        }
        return $directory;
    }

}