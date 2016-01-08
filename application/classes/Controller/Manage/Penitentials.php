<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Penitentials extends Controller_Manage_Core {

	public function action_index()
	{
        $penitentials = ORM::factory('Penitentials')->order_by('checked', 'DESC')->find_all();
        $this->set('penitentials', $penitentials);
	}

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $penitentials = ORM::factory('Penitentials', $id);
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            try
            {
                $penitentials->text = Security::xss_clean(Arr::get($post,'text',''));
                $penitentials->link = Security::xss_clean(Arr::get($post,'link',''));
                $penitentials->checked = (int) Arr::get($post,'checked', 0);
                $penitentials->save();

                $this->redirect('manage/penitentials');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $penitentials);
    }

    public function action_checked()
    {
        $id = $this->request->param('id', 0);
        $penitentials = ORM::factory('Penitentials', $id);
        if ( !$penitentials->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $penitentials->checked )
        {
            $penitentials->checked = 0;
            $penitentials->save();
            Message::success('Траурный режим отключен');
        }
        else
        {
            $penitentials->checked = 1;
            $penitentials->save();
            Message::success('Траурный режим включен');
        }
        $this->redirect('manage/penitentials/');
    }

}
