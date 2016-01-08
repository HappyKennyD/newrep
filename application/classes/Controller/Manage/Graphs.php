<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Graphs extends Controller_Manage_Core {

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/graphs/search/'.$search);
        }
        $graphs = ORM::factory('Graph')->order_by('date', 'DESC');
        $paginate = Paginate::factory($graphs)->paginate(NULL, NULL, 10)->render();
        $graphs = $graphs->order_by('date','desc')->find_all();
        $this->set('graphs', $graphs);
        $this->set('paginate', $paginate);
    }

    public function action_search()
    {

    }

    public function action_view()
    {
        $type_graph = $this->request->param('type');
        $id = (int) $this->request->param('id',0);
        $graph = ORM::factory('Graph',$id);
        if (!$graph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        switch ($type_graph)
        {
            case 'line':
                $items = $graph->items_graph->find_all();
                $value = $items[0]->values_graph->find();
                $this->set('_x',$value->x)->set('_y',$value->y)->set('items',$items);
                break;
            case 'bar':
                $items = $graph->items_graph->find_all();
                $this->set('items',$items);
                break;
            case 'pie':
                $item = $graph->items_graph->find();
                $values = $item->values_graph->find_all();
                $this->set('item',$item)->set('values',$values);
                break;
            default:
                throw new HTTP_Exception_404;

        }

        $this->template->set_filename('manage/graphs/view_'.$type_graph);
        $this->set('graph',$graph)->set('cancel_url', Url::media('manage/graphs'));
    }

    public function action_edit()
    {
        $type_graph = $this->request->param('type');
        $id = (int) $this->request->param('id',0);
        $graph = ORM::factory('Graph',$id);
        if (!$graph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($type_graph == 'line' OR $type_graph == 'bar')
        {
            if( $post = $this->request->post() )
            {
                $graph->name = Security::xss_clean(Arr::get($post,'name',''));
                $graph->category = $post['category'];
                $graph->description = Security::xss_clean(Arr::get($post,'description',''));
                $graph->name_x = Security::xss_clean(Arr::get($post,'name_x',''));
                $graph->name_y = Security::xss_clean(Arr::get($post,'name_y',''));
                $graph->count_graph = $post['count_graph'];
                $graph->count_point = $post['count'];
                $graph->published = $post['published'];
                $graph->is_important = $post['is_important'];
                if ($graph->save())
                {
                    $items = $graph->items_graph->find_all();
                    for ($i = 0; $i < count($post['name_graph']); $i++)
                    {
                        $item = ORM::factory('Graph_Item');
                        if ($i < count($items))
                        {
                            $item = ORM::factory('Graph_Item',$items[$i]->id);
                        }
                        $item->graph_id = $graph->id;
                        $item->name = Security::xss_clean($post['name_graph'][$i]);
                        $item->type = $post['type'];
                        if ($item->save())
                        {
                            $values = $item->values_graph->find_all();
                            $arr_ok = array();
                            for ($j = 0; $j < count($post['x_'.($i+1)]); $j++)
                            {
                                if (!empty($post['x_'.($i+1)][$j]) AND !empty($post['y_'.($i+1)][$j]))
                                {
                                    $value = ORM::factory('Graph_Value');
                                    $flag_new_element = true;
                                    foreach ($post['x_'.($i+1)][$j] as $key2 => $val2)
                                    {
                                        if ($key2)
                                        {
                                            $flag_new_element = false;
                                            $value = ORM::factory('Graph_Value', $key2);
                                            $arr_ok[] = $key2;
                                        }
                                    }
                                    $value->graph_id = $item->id;
                                    if ($flag_new_element)
                                    {
                                        $value->x_ru = Security::xss_clean($post['x_'.($i+1)][$j]);
                                        $value->y_ru = Security::xss_clean($post['y_'.($i+1)][$j]);
                                        $value->x_kz = Security::xss_clean($post['x_'.($i+1)][$j]);
                                        $value->y_kz = Security::xss_clean($post['y_'.($i+1)][$j]);
                                        $value->x_en = Security::xss_clean($post['x_'.($i+1)][$j]);
                                        $value->y_en = Security::xss_clean($post['y_'.($i+1)][$j]);
                                    }
                                    else
                                    {
                                        $value->x = Security::xss_clean($post['x_'.($i+1)][$j]);
                                        $value->y = Security::xss_clean($post['y_'.($i+1)][$j]);
                                    }
                                    $value->sort = $j + 1;
                                    $value->save();
                                }
                            }
                            foreach ($values as $v)
                            {
                                if (!in_array($v->id, $arr_ok))
                                    $v->delete();
                            }
                        }
                    }
                }
                if ($type_graph == 'line')
                {
                    Message::success('Линейный график изменен');
                }
                else
                {
                    Message::success('Гистограмма изменена');
                }
                $this->redirect('manage/graphs');
            }
        }
        elseif ($type_graph == 'pie')
        {
            if( $post = $this->request->post() )
            {
                $graph->name = Security::xss_clean(Arr::get($post,'name',''));
                $graph->category = $post['category'];
                $graph->description = Security::xss_clean(Arr::get($post,'description',''));
                $graph->count_point = $post['count'];
                $graph->published = $post['published'];
                $graph->is_important = $post['is_important'];
                if ($graph->save())
                {
                    $item = $graph->items_graph->find();
                    $item->graph_id = $graph->id;
                    $item->name = Security::xss_clean(Arr::get($post,'name_graph',''));
                    $item->type = $post['type'];
                    if ($item->save())
                    {
                        $values = $item->values_graph->find_all();
                        $arr_ok = array();
                        for ($j = 0; $j < count($post['x']); $j++)
                        {
                            if (!empty($post['x'][$j]) AND !empty($post['y'][$j]))
                            {
                                $value = ORM::factory('Graph_Value');
                                $flag_new_element = true;
                                foreach ($post['x'][$j] as $key => $val)
                                {
                                    if ($key)
                                    {
                                        $flag_new_element = false;
                                        $value = ORM::factory('Graph_Value', $key);
                                        $arr_ok[] = $key;
                                    }
                                }
                                $value->graph_id = $item->id;
                                if ($flag_new_element)
                                {
                                    $value->x_ru = Security::xss_clean($post['x'][$j]);
                                    $value->y_ru = Security::xss_clean($post['y'][$j]);
                                    $value->x_kz = Security::xss_clean($post['x'][$j]);
                                    $value->y_kz = Security::xss_clean($post['y'][$j]);
                                    $value->x_en = Security::xss_clean($post['x'][$j]);
                                    $value->y_en = Security::xss_clean($post['y'][$j]);
                                }
                                else
                                {
                                    $value->x = Security::xss_clean($post['x'][$j]);
                                    $value->y = Security::xss_clean($post['y'][$j]);
                                }
                                $value->sort = $j + 1;
                                $value->save();
                            }
                        }
                        foreach ($values as $v)
                        {
                            if (!in_array($v->id, $arr_ok))
                                $v->delete();
                        }
                    }
                }
                Message::success('Круговой график изменен');
                $this->redirect('manage/graphs');
            }
        }
        else
        {
            throw new HTTP_Exception_404;
        }
        $this->template->set_filename('manage/graphs/edit_'.$type_graph);
        $this->set('graph',$graph)->set('cancel_url', Url::media('manage/graphs'));
    }

    public function action_new()
    {

        $type_graph = $this->request->param('type');
        if ($type_graph == 'line' OR $type_graph == 'bar')
        {
            if( $post = $this->request->post() )
            {
                $graph = ORM::factory('Graph');
                $graph->type = $type_graph;
                $graph->category = $post['category'];
                $graph->name_ru = Security::xss_clean(Arr::get($post,'name',''));
                $graph->name_kz = Security::xss_clean(Arr::get($post,'name',''));
                $graph->name_en = Security::xss_clean(Arr::get($post,'name',''));
                $graph->description_ru = Security::xss_clean(Arr::get($post,'description',''));
                $graph->description_kz = Security::xss_clean(Arr::get($post,'description',''));
                $graph->description_en = Security::xss_clean(Arr::get($post,'description',''));
                $graph->name_x_ru = Security::xss_clean(Arr::get($post,'name_x',''));
                $graph->name_x_kz = Security::xss_clean(Arr::get($post,'name_x',''));
                $graph->name_x_en = Security::xss_clean(Arr::get($post,'name_x',''));
                $graph->name_y_ru = Security::xss_clean(Arr::get($post,'name_y',''));
                $graph->name_y_kz = Security::xss_clean(Arr::get($post,'name_y',''));
                $graph->name_y_en = Security::xss_clean(Arr::get($post,'name_y',''));
                $graph->count_graph = $post['count_graph'];
                $graph->count_point = $post['count'];
                $graph->published = $post['published'];
                $graph->is_important = $post['is_important'];
                $graph->date = date("y-m-d");
                if ($graph->save())
                {
                    for ($i = 0; $i < count(Arr::get($post,'name_graph',0)); $i++)
                    {
                        $item = ORM::factory('Graph_Item');
                        $item->graph_id = $graph->id;
                        $item->name_ru = Security::xss_clean($post['name_graph'][$i]);
                        $item->name_kz = Security::xss_clean($post['name_graph'][$i]);
                        $item->name_en = Security::xss_clean($post['name_graph'][$i]);
                        $item->type = $post['type'];
                        if ($item->save())
                        {
                            for ($j = 0; $j < count($post['x_'.($i+1)]); $j++)
                            {
                                if (!empty($post['x_'.($i+1)][$j]) AND !empty($post['y_'.($i+1)][$j]))
                                {
                                    $value = ORM::factory('Graph_Value');
                                    $value->graph_id = $item->id;
                                    $value->x_ru = Security::xss_clean($post['x_'.($i+1)][$j]);
                                    $value->x_kz = Security::xss_clean($post['x_'.($i+1)][$j]);
                                    $value->x_en = Security::xss_clean($post['x_'.($i+1)][$j]);
                                    $value->y_ru = Security::xss_clean($post['y_'.($i+1)][$j]);
                                    $value->y_kz = Security::xss_clean($post['y_'.($i+1)][$j]);
                                    $value->y_en = Security::xss_clean($post['y_'.($i+1)][$j]);
                                    $value->sort = $j + 1;
                                    $value->save();
                                }
                            }
                        }
                    }
                }
                if ($type_graph == 'line')
                {
                    Message::success('Линейный график добавлен');
                }
                else
                {
                    Message::success('Гистограмма добавлена');
                }
                $this->redirect('manage/graphs');
            }
        }
        elseif ($type_graph == 'pie')
        {
            if( $post = $this->request->post() )
            {
                $graph = ORM::factory('Graph');
                $graph->type = 'pie';
                $graph->category = $post['category'];
                $graph->name_ru = Security::xss_clean(Arr::get($post,'name',''));
                $graph->name_kz = Security::xss_clean(Arr::get($post,'name',''));
                $graph->name_en = Security::xss_clean(Arr::get($post,'name',''));
                $graph->description_ru = Security::xss_clean(Arr::get($post,'description',''));
                $graph->description_kz = Security::xss_clean(Arr::get($post,'description',''));
                $graph->description_en = Security::xss_clean(Arr::get($post,'description',''));
                $graph->count_point = $post['count'];
                $graph->published = $post['published'];
                $graph->is_important = $post['is_important'];
                $graph->date = date("y-m-d");
                if ($graph->save())
                {
                        $item = ORM::factory('Graph_Item');
                        $item->graph_id = $graph->id;
                        $item->name_ru = Security::xss_clean(Arr::get($post,'name_graph',''));
                        $item->name_kz = Security::xss_clean(Arr::get($post,'name_graph',''));
                        $item->name_en = Security::xss_clean(Arr::get($post,'name_graph',''));
                        $item->type = $post['type'];
                        if ($item->save())
                        {
                            for ($j = 0; $j < count($post['x']); $j++)
                            {
                                if (!empty($post['x'][$j]) AND !empty($post['y'][$j]))
                                {
                                    $value = ORM::factory('Graph_Value');
                                    $value->graph_id = $item->id;
                                    $value->x_ru = Security::xss_clean($post['x'][$j]);
                                    $value->x_kz = Security::xss_clean($post['x'][$j]);
                                    $value->x_en = Security::xss_clean($post['x'][$j]);
                                    $value->y_ru = Security::xss_clean($post['y'][$j]);
                                    $value->y_kz = Security::xss_clean($post['y'][$j]);
                                    $value->y_en = Security::xss_clean($post['y'][$j]);
                                    $value->sort = $j + 1;
                                    $value->save();
                                }
                            }
                        }
                }
                Message::success('Круговой график добавлен');
                $this->redirect('manage/graphs');
            }
        }
        else
        {
            throw new HTTP_Exception_404;
        }

        $this->template->set_filename('manage/graphs/new_'.$type_graph);
        $this->set('cancel_url', Url::media('manage/graphs'));
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $graph = ORM::factory('Graph',$id);
        if (!$graph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ( $graph->published )
        {
            $graph->published = 0;
            $graph->save();
            Message::success('График скрыт');
        }
        else
        {
            $graph->published = 1;
            $graph->save();
            Message::success('График опубликован');
        }
        $this->redirect('manage/graphs/');
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $graph = ORM::factory('Graph',$id);
        if (!$graph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ( $graph->is_important )
        {
            $graph->is_important = 0;
            $graph->save();
            Message::success('График убран с главной');
        }
        else
        {
            $graph->is_important = 1;
            $graph->save();
            Message::success('График добавлен на главную');
        }
        $this->redirect('manage/graphs/');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $graph = ORM::factory('Graph',$id);
        if (!$graph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $items = $graph->items_graph->find_all();
            foreach ($items as $item)
            {
                $values = $item->values_graph->find_all();
                foreach ($values as $value)
                {
                    $value->delete();
                }
                $item->delete();
            }
            $graph->delete();
            Message::success('График удален');
            $this->redirect('manage/graphs');
        }
        else
        {
            $this->set('record', $graph)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/graphs'));
        }
    }
}