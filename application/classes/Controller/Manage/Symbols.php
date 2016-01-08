<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Symbols extends Controller_Manage_Core {

    public function action_index()
    {
        $general_info = ORM::factory('Symbol')->where('key','=','general')->find();
        $this->set('general_info',$general_info);
    }

    public function action_edit()
    {
        $type = Arr::get($_GET,'type');
        $symbols = ORM::factory('Symbol')->where('key','=',$type)->find();
        $uploader = View::factory('storage/image')->set('user_id', $this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $symbols->title = Security::xss_clean(Arr::get($post,'title',''));
                $symbols->text = Security::xss_clean(Arr::get($post,'text',''));
                $symbols->image = Arr::get($post,'image',0);
                $symbols->key = $type;
                $symbols->save();
                Message::success('Информация о государственных символах сохранена');
                $this->redirect('manage/symbols');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $symbols)->set('type',$type)->set('uploader',$uploader);
    }

    public function action_view()
    {
        $type = Arr::get($_GET,'type');
        $symbols = ORM::factory('Symbol')->where('key','=',$type)->find();
        $this->set('item', $symbols)->set('type',$type);
    }

    /*
 * Удалить изображение в
 * TODO реализовать через ajax
 */
    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $symbol = ORM::factory('Symbol',$id);
        if ($symbol->loaded())
        {
            $symbol->image = 0;
            $symbol->save();
        }
        $this->redirect('manage/symbols/edit?type='.$symbol->key);
    }
}
