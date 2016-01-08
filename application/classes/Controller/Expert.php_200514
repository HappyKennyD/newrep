<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Expert extends Controller_Core
{

    public function action_index()
    {
        $list = ORM::factory('Expert_Opinion')->where('title_' . $this->language, '<>', '')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
        $this->add_cumb('Expert opinions', '/');

        /* метатэг description */
        $this->metadata->description( i18n::get('Экспертный взгляд на историю Казахстана, аналитика и экспертиза истории Казахстана') );
    }

    public function action_view()
    {

        $id = (int)$this->request->param('id', 0);
        $opinion = ORM::factory('Expert_Opinion', $id);
        if (!$opinion->loaded()) {
            throw new HTTP_Exception_404;
        }
        if (!$opinion->translation(I18n::$lang))
        {
            throw new HTTP_Exception_404('no_translation');
        }
        $this->set('opinion', $opinion);
        $other_opinions = ORM::factory('Expert_Opinion')->where('expert_id', '=', $opinion->expert_id)->and_where('id', '<>', $id)->order_by('date', 'DESC')->find_all();
        $this->set('other_opinions', $other_opinions);
        $comment = ORM::factory('Expert_Comment')->where('opinion_id', '=', $id);
        if (Auth::instance()->logged_in()) {
            $has_access = Auth::instance()->get_user()->has_access('ma');
        } else {
            $has_access = 0;
        }
        if (!$has_access) {
            $comment = $comment->and_where('moderator_decision', '=', 1);
        }
        $comment = $comment->order_by('date', 'ASC')->find_all();
        $this->set('comments', $comment);
        if ($this->request->method() == Request::POST) {
            $has_ma_access = Auth::instance()->get_user()->has_access('ma');
            if (Auth::instance()->logged_in()) {
                try {
                    $user_id = Auth::instance()->get_user()->id;
                    $comment = ORM::factory('Expert_Comment');
                    $comment->text = Arr::get($_POST, 'text', '');
                    $comment->user_id = $user_id;
                    $comment->opinion_id = $id;
                    if ($has_ma_access) {
                        $comment->moderator_decision = 1;
                    }
                    $comment->date = date('Y-m-d H:i:s');
                    $comment->save();
                    if ($has_ma_access) {
                        Message::success(i18n::get('Thank you for your comment'));
                    } else {
                        Message::success(i18n::get('Your comment has been sent for moderation'));
                    }

                } catch (ORM_Validation_Exception $e) {
                }
            } else {
                Message::success(i18n::get('You have to login'));
            }
            $this->redirect('expert/view/' . $id,301);
        }
        $metadesc=$opinion->description;
        if(!empty($metadesc)) {
            $this->metadata->description($metadesc);
        }
        else {
            $this->metadata->snippet($opinion->text);
        }
        $this->add_cumb('Expert opinions', 'expert');
        $this->add_cumb($opinion->title, '/');
    }

    public function action_questions()
    {
        $list = ORM::factory('Expert_Question')->where('is_answered', '=', 1);
            $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search)) {
            $list->and_where('question', 'LIKE', '%' . $search . '%');
        }
        $list = $list->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('search', $search);
        $this->set('list', $list);
        $this->set('paginate', $paginate);

        if ($this->request->method() == Request::POST) {
            if (Auth::instance()->logged_in()) {
                try {
                    $user_id = Auth::instance()->get_user()->id;
                    $question = ORM::factory('Expert_Question');
                    $question->user_id = $user_id;
                    $question->question = Arr::get($_POST, 'question', '');
                    $question->date = date('Y-m-d H:i:s');
                    $question->save();
                } catch (ORM_Validation_Exception $e) {
                }
            } else {
                Message::success(i18n::get('You have to login'));
            }
        }
        $this->add_cumb('Question-answer', '/');
    }
}
