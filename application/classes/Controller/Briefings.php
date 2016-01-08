<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Briefings extends Controller_Core {

	public function action_index()
	{
        $briefings = ORM::factory('Briefing')->where('published','=', 1)->order_by('date', 'DESC');
        $paginate = Paginate::factory($briefings)->paginate(NULL, NULL, 10)->render();
        $briefings = $briefings->find_all();
        $this->add_cumb('Briefings','/');
        $this->set('briefings', $briefings);
        $this->set('paginate', $paginate);

        /* метатэг description */
        $this->metadata->description( i18n::get('Брифинги по вопросам изучения истории Казахстана') );
	}

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $briefing = ORM::factory('Briefing', $id);
        if (!$briefing->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $comments = $briefing->comments->where('published', '=', 1)->order_by('date')->find_all();
        $this->add_cumb('Briefings','briefings');
        $this->add_cumb($briefing->title,'/');
        $this->set('briefing', $briefing)->set('comments', $comments);
        /*if (Auth::instance()->logged_in())
        {
            $this->set('auth', 1);
            if ( $post = $this->request->post() )
            {
                $comment = ORM::factory('Briefings_Comment');
                $comment->briefing_id = $id;
                $comment->user_id = Auth::instance()->get_user()->id;
                $comment->text = Security::xss_clean(Arr::get($post,'text',''));
                $comment->date = date('Y-m-d H:i:s');
                $comment->save();
                Message::success(I18n::get("Your comment has been sent for approval to the administrator"));
                $this->redirect('briefings/view/'.$id);
            }
        }*/
        $metadesc=$briefing->desc;
        if(!empty($metadesc)) {
            $this->metadata->description($metadesc);
        }
        else {
            $this->metadata->snippet($briefing->text);
        }
    }

}
