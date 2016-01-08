<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Contents extends Controller_Core {

    public function action_list()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page',$id);
        $key_arr = array('publications', 'debate','expert','organization');
        if (in_array($page->key, $key_arr))
        {
            $this->redirect($page->key, 301);
        }

        $e = explode('_', $page->key);
        if ($e[0] == 'books')
        {
            $this->redirect('books/'.$e[1].'?category='.$e[2], 301);
        }
        if ($e[0] == 'biography')
        {
            if (!isset($e[1])) { $e[1] = ''; }
            //$this->redirect($e[0].'/era-'.$e[2].'/'.$e[1]);
            $this->redirect($e[0].'/'.$e[1], 301);
        }
        if ( $e[0] == 'organization')
        {
            $this->redirect('organization/show/'.$e[1], 301);
        }
        if (isset($page->parent->parent->key) && ($page->parent->parent->key=='institute'))
        {
            $this->redirect('organization/show/'.$page->id, 301);
        }
        $paginate = '';
        if (!$page->loaded())
            throw new HTTP_Exception_404('Page not found');
        if ($page->static)
        {
            $content = $page->content->where('type','=','static')->find();
            $this->redirect('contents/view/'.$content, 301);
        }else
        {
            $contents = $page->content->where('published', '=', 1)->where('title_'.I18n::$lang, '<>', '')->order_by('date', 'DESC');
          
if ($id==378 || $id==384 || $id==385) 

 $paginate = Paginate::factory($contents)->paginate(NULL, NULL, 400)->render();

else
 $paginate = Paginate::factory($contents)->paginate(NULL, NULL, 10)->render();
            $contents = $contents->find_all();
            $path = $page->parents();
            foreach ($path as $p)
            {
                if ($p->key != 'menu')
                {
                    if ($p->has_children())
                    {
                        $this->add_cumb($p->name, '');
                    }
                    else
                    {
                        $this->add_cumb($p->name,'contents/list/'.$p->id);
                    }
                }
            }
            if ($page->key != 'menu')
            $this->add_cumb($page->name,'/');
            if ($page->key == 'message-list' OR $page->key == 'articles-list')
            {
                $last_message = $page->content->where('published', '=', 1)->order_by('date', 'DESC')->find();
                $this->set('last_message',$last_message);
                $this->template->set_filename('contents/message_president');
            }
            $this->set('contents',$contents)->set('page',$page)->set('path',$path)->set('paginate',$paginate);
        }
        $this->set('paginate', $paginate);
        $cont_p = ORM::factory('Page')->where('parent_id', '=', $id)->find_all();
        $this->set('cont_p',$cont_p);

        if(!empty($page->description)) {
            $this->metadata->description($page->description);
        }
    }

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content')->where('id','=',$id)->and_where('published','=',1)->find();
        if (!$content->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if (!$content->translation())
        {
            throw new HTTP_Exception_404('no_translation');
        }
        $page = ORM::factory('Page',$content->page_id);
        $path = $page->parents();
        foreach ($path as $p)
        {
            if ($p->key != 'menu')
            {
                if ( $p->has_children() )
                {
                    $this->add_cumb($p->name,'');
                }else
                {
                    $this->add_cumb($p->name,'contents/list/'.$p->id);
                }
            }
        }
        if ($page->lvl == 1)
        {
            if ($page->key =='contacts')
            {
                $this->add_cumb($page->name,'');
            }
            else
            {
                $this->add_cumb($page->name,'contents/list/'.$page->id);
                $this->add_cumb($content->title,'');
            }
        }else
        {
            $this->add_cumb($page->name,'contents/list/'.$page->id);
            $this->add_cumb($content->title,'');
        }
        $this->set('content', $content)->set('page',$page)->set('path',$path);
        $metadesc=$content->description;
        if(!empty($metadesc)) {
            $this->metadata->description($metadesc);
        }
        else {
            $this->metadata->snippet($content->text);
        }
    }

}
