<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Changepassword extends Controller_Api_Private{

    public function action_index()
    {
        $current_password = Auth::instance()->hash( Arr::get($this->post, 'currentPassword', ''), '' );
        $new_password     = Arr::get($this->post, 'newPassword', '');

        if( !empty($current_password) AND !empty($new_password) )
        {
            $user = ORM::factory('User', $this->user_id);

            if($current_password === $user->password)
            {
                try
                {
                    $user->password = $new_password;
                    $user->save();
                    $this->data = true;
                }
                catch (ORM_Validation_Exception $e)
                {
                    $this->data['error'] = $e->errors($e->alias());
                }
            }
            else
            {
                $this->data['error'] = 'Incorrect password';
            }

        }

        $this->response->body(json_encode($this->data));
    }

}