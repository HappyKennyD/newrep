<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Forecasts extends Controller_Manage_Core {

    public function action_index()
    {
        $forecasts = ORM::factory('Forecast')->find_all();
        $this->set('forecasts', $forecasts);
    }

    public function action_new()
    {
        $id = $this->request->param('id', 0);
        $forecast = ORM::factory('Forecast', $id);
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            try
            {
                $forecast->title = Security::xss_clean(Arr::get($post,'title',''));
                $forecast->key = Security::xss_clean(Arr::get($post,'key',''));
                $forecast->save();
                Message::success('Тип прогноза сохранен');
                $this->redirect('manage/forecasts');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $forecast);
    }

    public function action_list()
    {
        $id = $this->request->param('id', 0);
        $forecast_type = ORM::factory('Forecast', $id);
        if (!$forecast_type->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/forecasts/search/'.$search.'?type='.$id);
        }
        $forecasts = $forecast_type->items->order_by('date', 'DESC');
        $paginate = Paginate::factory($forecasts)->paginate(NULL, NULL, 10)->render();
        $forecasts = $forecasts->find_all();
        $this->set('forecasts', $forecasts);
        $this->set('paginate', $paginate);
        $this->set('type',$id);
    }


    public function action_search()
    {
        $type = Arr::get($_GET,'type');
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $forecasts = ORM::factory('Forecasts_Item')->where($query_m, '', $query_a)->and_where('forecast_id','=',$type)->find_all();
        $this->template->set_filename('manage/forecasts/list');
        $this->set('forecasts', $forecasts);

        $totalcount = sizeof($forecasts);
        if ($totalcount==0)
        {
            Message::notice('Извините, по вашему запросу ничего не найдено.');
            $this->redirect('manage/forecasts/list/'.$type);
        }
    }

    public function action_view()
    {
        $type = Arr::get($_GET,'type');
        $id = $this->request->param('id', 0);
        $forecast = ORM::factory('Forecasts_Item', $id);
        if ( !$forecast->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('forecast', $forecast)->set('type',$type);
    }

    public function action_edit()
    {
        $type = Arr::get($_GET,'type');
        $id = $this->request->param('id', 0);
        $forecast = ORM::factory('Forecasts_Item', $id);
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $post['date'] = date('Y-m-d',strtotime($post['date']));
                $forecast->title = Security::xss_clean(Arr::get($post,'title',''));
                $forecast->description = Security::xss_clean(Arr::get($post,'description',''));
                $forecast->text = Security::xss_clean(Arr::get($post,'text',''));
                $forecast->forecast_id = $type;
                $forecast->values($post, array('image', 'date', 'published'))->save();
                Message::success('Прогноз сохранен');
                $this->redirect('manage/forecasts/view/'.$forecast->id.'?type='.$type);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $forecast)->set('type',$type);
    }

    public function action_delete()
    {
        $type = Arr::get($_GET,'type');
        $id = (int) $this->request->param('id', 0);
        $forecast = ORM::factory('Forecasts_Item', $id);
        if (!$forecast->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $forecast->delete();
            Message::success('Прогноз удален');
            $this->redirect('manage/forecasts/list/'.$type);
        }
        else
        {
            $this->set('record', $forecast)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/forecasts/list/'.$type));
        }
    }


    public function action_published()
    {
        $type = Arr::get($_GET,'type');
        $id = $this->request->param('id', 0);
        $forecast = ORM::factory('Forecasts_Item', $id);
        if ( !$forecast->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $forecast->published )
        {
            $forecast->published = 0;
            $forecast->save();
            Message::success('Прогноз скрыт');
        }
        else
        {
            $forecast->published = 1;
            $forecast->save();
            Message::success('Прогноз опубликован');
        }
        $this->redirect('manage/forecasts/list/'.$type);
    }

    /*
     * Удалить изображение в прогнозах
     * TODO реализовать через ajax
     */
    public function action_clearImage()
    {
        $type = Arr::get($_GET,'type');
        $id = $this->request->param('id', 0);
        $forecast = ORM::factory('Forecasts_Item',$id);
        if ($forecast->loaded())
        {
            $forecast->image = 0;
            $forecast->save();
        }
        $this->redirect('manage/forecasts/edit/'.$id.'?type='.$type);
    }

    public function action_mainedit()
    {
        $type = Arr::get($_GET,'type');
        $id = $this->request->param('id', 0);
        $forecast_main_header = ORM::factory('Forecasts_Main')->where('type','=','header')->find();
        $forecast_main_rows = ORM::factory('Forecasts_Main')->where('type','=','rows')->order_by('id','ASC')->find_all();
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            //var_dump($post);
            try
            {
                $forecast = ORM::factory('Forecasts_Main', $forecast_main_header->id);
                $forecast->title = Security::xss_clean(Arr::get($post,'title',''));
                $forecast->column_1 = Security::xss_clean(Arr::get($post,'column_head_1',''));
                $forecast->column_2 = Security::xss_clean(Arr::get($post,'column_head_2',''));
                $forecast->column_3 = Security::xss_clean(Arr::get($post,'column_head_3',''));
                $forecast->type = 'header';
                $forecast->save();

                //column_row_1
                //column_row_2
                //column_row_3
                //column_row_radio
                foreach ($forecast_main_rows as $item)
                {
                    //ORM::factory('Forecasts_Main',$item->id)->delete();
                }
                $arr_ok = array();
                if (isset($post['column_row_1']) AND isset($post['column_row_2']) AND isset($post['column_row_3']) AND isset($post['column_row_radio']))
                {
                    for ($i = 0; $i < count($post['column_row_1']); $i++)
                    {
                        $forecast = ORM::factory('Forecasts_Main');
                        foreach ($post['column_row_1'][$i] as $key => $value)
                        {
                            if ($key)
                            {
                                $forecast = ORM::factory('Forecasts_Main', $key);
                                $arr_ok[] = $key;
                            }
                        }
                        $forecast->column_1 = Security::xss_clean($post['column_row_1'][$i]);
                        $forecast->column_2 = Security::xss_clean($post['column_row_2'][$i]);
                        $forecast->column_3 = Security::xss_clean($post['column_row_3'][$i]);
                        $forecast->type = 'rows';
                        $j = 0;
                        foreach ($post['column_row_radio'] as $item)
                        {
                            if ($i==$j)
                            {
                                $forecast->title_ru = $item;
                                $forecast->title_kz = $item;
                                $forecast->title_en = $item;
                            }
                            $j++;
                        }
                        $forecast->save();
                    }
                    foreach ($forecast_main_rows as $item)
                    {
                        if (!in_array($item->id,$arr_ok))
                            ORM::factory('Forecasts_Main',$item->id)->delete();
                    }
                }
                Message::success('Прогноз сохранен');
                $this->redirect('manage/forecasts/main');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('forecast_main_header', $forecast_main_header)
             ->set('forecast_main_rows',$forecast_main_rows);
    }

    public function action_main()
    {
        $forecast_main_header = ORM::factory('Forecasts_Main')->where('type','=','header')->find();
        $forecast_main_rows = ORM::factory('Forecasts_Main')->where('type','=','rows')->order_by('id','ASC')->find_all();
        $this->set('forecast_main_header', $forecast_main_header)
            ->set('forecast_main_rows',$forecast_main_rows);
    }

}