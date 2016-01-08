<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Ent extends Controller_Manage_Core {

	public function action_index()
	{
        $ent = ORM::factory('Ent')->order_by('id')->find_all();
        $this->set('ent', $ent);
	}

    public function action_quests()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);

        $quests = ORM::factory('Ent_Quest')->where('ent_id', '=', $id)->order_by('number')->find_all();
        $this->set('quests', $quests);
    }

    public function action_questup()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Ent_Quest', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $quest->number = $quest->number - 1;
        $quest->save();
        $this->redirect(URL::media('manage/ent/quests/'.$quest->ent_id));
    }

    public function action_questdown()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Ent_Quest', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $quest->number = $quest->number + 1;
        $quest->save();
        $this->redirect(URL::media('manage/ent/quests/'.$quest->ent_id));
    }

    public function action_variantup()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Quest_Variant', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $variant->number = $variant->number - 1;
        $variant->save();
        $this->redirect('manage/ent/variants/'.$variant->quest_id);
    }

    public function action_variantdown()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Quest_Variant', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $variant->number = $variant->number + 1;
        $variant->save();
        $this->redirect('manage/ent/variants/'.$variant->quest_id);
    }

    public function action_variants()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Ent_Quest', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('quest', $quest);

        $ent = ORM::factory('Ent', $quest->ent_id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);

        $variants = ORM::factory('Quest_Variant')->where('quest_id', '=', $id)->order_by('number')->find_all();
        $this->set('variants', $variants);
    }

    public function action_questedit()
    {
        $id = $this->request->param('id', 0);
        $params = explode('-', $id);
        array_walk($params, 'intval');
        $ent_id = $params[0];
        if (isset($params[1]))
        {
            $quest_id = $params[1];
            $quest = ORM::factory('Ent_Quest', $quest_id);
        }
        else
        {
            $quest = ORM::factory('Ent_Quest');
        }
        $ent = ORM::factory('Ent', $ent_id);

        $this->set('quest', $quest);

        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);
        $this->set('cancel_url', URL::media('manage/ent/quests/'.$ent_id));

        if ($this->request->method() == 'POST')
        {
            try
            {
                $numbers = ORM::factory('Ent_Quest')->where('ent_id', '=', $ent_id)->order_by('number', 'DESC')->limit(1)->find();
                $number = $numbers->number + 1;
                $quest->text = Security::xss_clean(Arr::get($_POST, 'text', ''));
                $quest->ent_id = $ent_id;
                if (!isset($params[1])) $quest->number = $number;
                $quest->published = 1;
                $quest->save();
                $this->redirect('manage/ent/quests/'.$ent_id);
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
            $variant = ORM::factory('Quest_Variant', $variant_id);
        }
        else
        {
            $variant = ORM::factory('Quest_Variant');
        }
        $quest = ORM::factory('Ent_Quest', $quest_id);

        $this->set('variant', $variant);

        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('quest', $quest);
        $this->set('cancel_url', URL::media('manage/ent/variants/'.$quest_id));

        if ($this->request->method() == 'POST')
        {
            try
            {
                $numbers = ORM::factory('Quest_Variant')->where('quest_id', '=', $quest_id)->order_by('number', 'DESC')->limit(1)->find();
                $number = $numbers->number + 1;
                $variant->text = Security::xss_clean(Arr::get($_POST, 'text', ''));
                $variant->quest_id = $quest_id;
                $variant->number = $number;
                $variant->right = (int) Arr::get($_POST, 'right', 0);
                $variant->published = 1;
                $variant->save();
                $this->redirect('manage/ent/variants/'.$quest_id);
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
            $ent = ORM::factory('Ent', $id);
            $this->set('ent', $ent);
        }
        else{
            $language = Security::xss_clean($this->request->param('id', ''));
            $ent = array('language' => $language);
            $this->set('ent', $ent);
        }
        if ($this->request->method() == 'POST')
        {
            $language = Security::xss_clean(Arr::get($_POST, 'language', ''));
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            try
            {
                $ent=ORM::factory('Ent', $id);
                $ent->language = $language;
                $ent->title = $title;
                $ent->date=date("Y-m-d H:i:s");
                $ent->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$ent->title);
                $loger->log($ent);

                $this->redirect('manage/ent/');
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
        $ent = ORM::factory('Ent', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $ent->language = $ent->language == 'ru' ? 'kz' : 'ru';
        $ent->save();
        $this->redirect('manage/ent/');
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
        if ( !$ent->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $ent->language = $ent->language == 'kz' ? 'ru' : 'kz';
        $ent->save();
        $this->redirect('manage/ent/');
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
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
        $this->redirect('manage/ent/');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
        if (!$ent->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $ent->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/ent');
        }
        else
        {
            $this->set('record', $ent)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/ent'));
        }
    }

    public function action_questdelete()
    {
        $id = (int) $this->request->param('id', 0);
        $quest = ORM::factory('Ent_Quest', $id);
        if ( !$quest->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $ent_id = $quest->ent_id;
            $quest->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/ent/quests/'.$ent_id);
        }
        else
        {
            $this->set('record', $quest)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/ent/quests/'.$quest->ent_id));
        }
    }

    public function action_variantdelete()
    {
        $id = (int) $this->request->param('id', 0);
        $variant = ORM::factory('Quest_Variant', $id);
        if ( !$variant->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $quest_id = $variant->quest_id;
            $variant->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/ent/variants/'.$quest_id);
        }
        else
        {
            $this->set('record', $variant)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/ent/variants/'.$variant->quest_id));
        }
    }

    public function action_questpublish()
    {
        $id = $this->request->param('id', 0);
        $quest = ORM::factory('Ent_Quest', $id);
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
        $this->redirect('manage/ent/quests/'.$quest->ent_id);
    }

    public function action_variantpublish()
    {
        $id = $this->request->param('id', 0);
        $variant = ORM::factory('Quest_Variant', $id);
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
        $this->redirect('manage/ent/variants/'.$variant->quest_id);
    }


}
