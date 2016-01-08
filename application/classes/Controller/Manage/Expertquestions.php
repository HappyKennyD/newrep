<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Expertquestions extends Controller_Manage_Core
{

    protected $page;

    public function before()
    {
        parent::before();

        $this->page = Security::xss_clean( (int) $this->request->param('page',0) );
        if( empty($this->page) )
        {
            $this->page=1;
        }

        $this->sort = Security::xss_clean(Arr::get($_GET, 'sort', 'noreply'));
    }

    public function action_index()
    {
        $sort = Security::xss_clean(Arr::get($_GET, 'sort', 'noreply'));
        $list = ORM::factory('Expert_Question');
        switch ($sort) {
            case "spam":
                $list->and_where('is_spam', '=', 1);
                break;
            case "reply":
                $list->and_where('is_answered', '=', 1);
                break;
            case "noreply":
                $list->and_where('is_answered', '=', 0);
                break;
        }
        $list = $list->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('questions', $list)->set('sort', $sort)->set('page', $this->page);
        $this->set('paginate', $paginate);
    }

    public function action_reply()
    {
        $id = $this->request->param('id', 0);
        $questions = ORM::factory('Expert_Question', $id);
        $cancel_url = Security::xss_clean(Arr::get($_GET, 'r', 'manage/expertquestions'));
            if (!$questions->loaded()) {
            $this->redirect($cancel_url);
        }
        $user_id = $this->user->id;
        $this->set('cancel_url', Url::media('manage/expertquestions/page-'.$this->page.'?sort='.$this->sort))->set('page', $this->page)->set('sort', $this->sort);
        $this->set('questions', $questions);
        if ($this->request->method() == 'POST') {
            $answer = Arr::get($_POST, 'answer', '');
            try {
                $answers = ORM::factory('Expert_Answer');
                $answers->answer = $answer;
                $answers->date = date('Y-m-d G:i:s');
                $answers->question_id = $id;
                $answers->respondent_id = $user_id;
                $answers->save();
                $questions->is_answered = 1;
                $questions->save();
                Message::success(i18n::get('The answer to the question is saved'));
                $this->redirect( Url::media('manage/expertquestions/page-'.$this->page.'?sort='.$this->sort));
                exit();
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('answer', $answer);
                $this->set('errors', $errors);
            }
        }
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $questions = ORM::factory('Expert_Question', $id);
        $user_id = $this->user->id;
        $cancel_url = Url::media('manage/expertquestions/page-'.$this->page.'?sort='.$this->sort);
        if (!$questions->loaded()) {
            $this->redirect($cancel_url);
        }
        $this->set('cancel_url', Url::media($cancel_url));
        $this->set('questions', $questions);
        if ($this->request->method() == 'POST') {
            $question = Arr::get($_POST, 'question', '');
            try {
                $questions->question = $question;
                $questions->moderator_id = $user_id;
                $questions->save();
                Message::success(i18n::get('User question edited'));
                $this->redirect($cancel_url);
            } catch (ORM_Validation_Exception $e) {
                $this->set('question', $question);
                $this->set('errors', true);
            }
        }
    }

    public function action_spam()
    {
        $id = (int)$this->request->param('id', 0);
        $question = ORM::factory('Expert_Question', $id);
        $user_id = $this->user->id;
        $cancel_url = Url::media('manage/expertquestions/page-'.$this->page.'?sort='.$this->sort);
        $token = Arr::get($_POST, 'token', false);
        $this->set('return', Url::media('manage/expertquestions/reply/'.$id.'page-'.$this->page.'?sort='.$this->sort));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $question->is_spam = 1;
            $question->moderator_id = $user_id;
            $question->save();
            Message::success(i18n::get('The question is marked as spam'));
            $this->redirect($cancel_url);
        } else {
            if ($question->loaded()) {
                $this->set('question', $question)->set('token', Security::token(true));
            } else {
                $this->redirect($cancel_url);
            }
        }
    }
}