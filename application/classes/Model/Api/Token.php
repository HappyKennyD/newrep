<?php
class Model_Api_Token extends ORM
{
    /*
     * Обновить время последнего обращения к токену
     */
    public function update_token($api_token='')
    {
        if(!empty($api_token))
        {
            $this->token = $api_token;
            $this->expires = time();
            $this->update();
        }
        else
        {
            $this->expires = time();
            $this->update();
        }
    }

}