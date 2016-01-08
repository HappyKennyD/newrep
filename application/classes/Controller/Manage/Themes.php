<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Themes extends Controller_Manage_Core {

	public function action_index()
	{
        $themes = ORM::factory('themes')->order_by('id')->find_all();
        $this->set('themes', $themes);
	}

    public function action_addvar()
	{
        $vk = ORM::factory('vk')->order_by('id')->find_all();
        $this->set('vk', $vk);
        $id = (int) $this->request->param('id', 0);
        $kt = ORM::factory('kt',$id);
        $this->set('kt', $kt);

        $sql = "SELECT DISTINCT vk.*
        FROM vk
        WHERE vk.id_kt=$id";
        $var = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $this->set('var', $var);
	}

    public function action_quests()
    {
        $id = $this->request->param('id', 0);
        $vk = ORM::factory('vk',$id);
        $this->set('vk', $vk);
        $vk = ORM::factory('vk',$id);
        if ( !$vk->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('vk', $vk);

        // $quests = ORM::factory('Test_Quests')->where('test_variant_id', '=', $id)->order_by('number')->find_all();
        $sql = "SELECT DISTINCT qv.*
        FROM qv
        WHERE qv.id_vk=$id";
	    $quests = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $this->set('quests', $quests);
    }


    public function action_questup()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Test_Quests', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $quest->number = $quest->number - 1;
        $quest->save();
        $this->redirect(URL::media('manage/tests/quests/'.$quest->test_variant_id));
    }

    public function action_questdown()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Test_Quests', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $quest->number = $quest->number + 1;
        $quest->save();
        $this->redirect(URL::media('manage/tests/quests/'.$quest->test_variant_id));
    }

    public function action_variantup()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Test_Questvar', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $variant->number = $variant->number - 1;
        $variant->save();
        $this->redirect('manage/tests/variants/'.$variant->quests_id);
    }

    public function action_variantdown()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Test_Questvar', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $variant->number = $variant->number + 1;
        $variant->save();
        $this->redirect('manage/tests/variants/'.$variant->quests_id);
    }

    public function action_variants()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('qv', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('quest', $quest);

        $ent = ORM::factory('vk', $quest->id_vk);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);

        $variants = ORM::factory('aq')->where('id_qv', '=', $id)->order_by('number')->find_all();
        $this->set('variants', $variants);
    }

    public function action_questedit()
    {
        $id = $this->request->param('id', 0);
        $params = explode('-', $id);
        array_walk($params, 'intval');
        $ent_id = $params[0];
//        die($ent_id);
        if (isset($params[1]))
        {
            $quest_id = $params[1];
            $quest = ORM::factory('Qv', $quest_id);
        }
        else
        {
            $quest = ORM::factory('Qv');
        }
        $vk = ORM::factory('vk', $ent_id);

        $this->set('quest', $quest);

        if ( !$vk->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('vk', $vk);
        $this->set('cancel_url', URL::media('manage/tests/quests/'.$ent_id));

        if ($this->request->method() == 'POST')
        {
            try
            {
                $numbers = ORM::factory('Qv')->where('id_vk', '=', $ent_id)->order_by('number', 'DESC')->limit(1)->find();
                $number = $numbers->number + 1;
                $quest->quest = Security::xss_clean(Arr::get($_POST, 'text', ''));
                $quest->id_vk = $ent_id;
                if (!isset($params[1])) $quest->number = $number;
                $quest->published = 1;
                $quest->save();
                $this->redirect('manage/tests/quests/'.$ent_id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_variantedit()
    {
        $id = $this->request->param('id', 0);
        $params = explode('-', $id);
        array_walk($params, 'intval');
        $quest_id = $params[0];
        if (isset($params[1]))
        {
            $variant_id = $params[1];
            $variant = ORM::factory('aq', $variant_id);
        }
        else
        {
            $variant = ORM::factory('aq');
        }
        $quest = ORM::factory('qv', $quest_id);

        $this->set('variant', $variant);

        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('quest', $quest);
        $this->set('cancel_url', URL::media('manage/tests/variants/'.$quest_id));

        if ($this->request->method() == 'POST')
        {
            try
            {
                $numbers = ORM::factory('aq')->where('id_qv', '=', $quest_id)->order_by('number', 'DESC')->limit(1)->find();
                $number = $numbers->number + 1;
                $variant->answer = Security::xss_clean(Arr::get($_POST, 'text', ''));
                $variant->id_qv = $quest_id;
                $variant->number = $number;
                $variant->right = (int) Arr::get($_POST, 'right', 0);
                $variant->published = 1;
                $variant->save();
                $this->redirect('manage/tests/variants/'.$quest_id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

  /*  public function action_new()
    {
        $language = Security::xss_clean($this->request->param('id', ''));
        if ($language == '' or !in_array($language, array('ru', 'kz')))
        {
            throw new HTTP_Exception_404;
        }

        $ent = ORM::factory('Ent');
        $ent->language = $language;
        $ent->save();
        Message::success(I18n::get('Variant added. Variant number:').' '.$ent->id);
        $this->redirect('manage/ent/');
    }
   */
    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);

        if($id){
            $kt = ORM::factory('kt', $id);
            $this->set('kt', $kt);
        }
        else{
        }
        if ($this->request->method() == 'POST')
        {
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            try
            {
                $kt=ORM::factory('kt', $id);
                $kt->title = $title;
                $kt->date=date("Y-m-d H:i:s");
                $kt->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$kt->title);
                $loger->log($kt);

                $this->redirect('manage/tests/');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_addedit()
    {
        $id = $this->request->param('id', 0);
        $params = explode('-', $id);
        array_walk($params, 'intval');
        $ent_id = $params[0];
        if (isset($params[1]))
        {
            $quest_id = $params[1];
            $quest = ORM::factory('Qv', $quest_id);
        }
        else
        {
            $quest = ORM::factory('Qv');
        }
        $kt = ORM::factory('kt', $ent_id);

        $this->set('quest', $quest);

        if ( !$kt->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('kt', $kt);
        if($id){
            $vk = ORM::factory('Vk', $id);
            $this->set('vk', $vk);
        }
        else{
        }
        if ($this->request->method() == 'POST')
        {
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            try
            {
                $vk=ORM::factory('vk', $id);
                $vk->id_kt = $ent_id;
                $vk->title = $title;
                $vk->published = 1;
                $vk->date=date("Y-m-d H:i:s");
                $vk->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$vk->title);
                $loger->log($vk);

                $this->redirect('manage/tests/addvar/'.$ent_id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_show_ru()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Test_variant', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $ent->language = $ent->language == 'ru' ? 'kz' : 'ru';
        $ent->save();
        $this->redirect('manage/tests/');
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Test_variant', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $ent->language = $ent->language == 'kz' ? 'ru' : 'kz';
        $ent->save();
        $this->redirect('manage/tests/');
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Test_variant', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $ent->published )
        {
            $ent->published = 0;
            $ent->save();
            Message::success(I18n::get('Variant hided'));
        }
        else
        {
            $ent->published = 1;
            $ent->save();
            Message::success(I18n::get('Variant unhided'));
        }
        $this->redirect('manage/tests/');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $ent = ORM::factory('Test_variant', $id);
        if (!$ent->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $ent->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/tests');
        }
        else
        {
            $this->set('record', $ent)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/tests'));
        }
    }

    public function action_questdelete()
    {
        $id = (int) $this->request->param('id', 0);
        $quest = ORM::factory('Test_Quests', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $ent_id = $quest->test_variant_id;
            $quest->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/tests/quests/'.$ent_id);
        }
        else
        {
            $this->set('record', $quest)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/tests/quests/'.$quest->test_variant_id));
        }
    }

    public function action_adddelete()
    {
        $id = (int) $this->request->param('id', 0);
        $quest = ORM::factory('vk', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $ent_id = $quest->id_kt;
            $quest->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/tests/addvar/'.$ent_id);
        }
        else
        {
            $this->set('record', $quest)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/tests/add/'.$quest->id_kt));
        }
    }

    public function action_variantdelete()
    {
        $id = (int) $this->request->param('id', 0);
        $variant = ORM::factory('Test_Questvar', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $quest_id = $variant->quests_id;
            $variant->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/tests/variants/'.$quest_id);
        }
        else
        {
            $this->set('record', $variant)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/tests/variants/'.$variant->quest_id));
        }
    }

    public function action_questpublish()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Test_Quests', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $quest->published )
        {
            $quest->published = 0;
            $quest->save();
            Message::success(I18n::get('Quest hided'));
        }
        else
        {
            $quest->published = 1;
            $quest->save();
            Message::success(I18n::get('Quest unhided'));
        }
        $this->redirect('manage/tests/quests/'.$quest->test_variant_id);
    }

    public function action_variantpublish()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Test_Questvar', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $variant->published )
        {
            $variant->published = 0;
            $variant->save();
            Message::success(I18n::get('Variant hided'));
        }
        else
        {
            $variant->published = 1;
            $variant->save();
            Message::success(I18n::get('Variant unhided'));
        }
        $this->redirect('manage/tests/variants/'.$variant->quests_id);
    }


}
