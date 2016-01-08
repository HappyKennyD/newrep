<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Feedback extends Controller_Manage_Core
{

    public function action_index()
    {
        $list = ORM::factory('Feedback_Question')->order_by('date', 'DESC');
        $sort = Security::xss_clean(Arr::get($_GET, 'sort', 'noreply'));
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
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('questions', $list)->set('sort', $sort);
        $this->set('paginate', $paginate);
    }

    public function action_reply()
    {
        $id = $this->request->param('id', 0);
        $question = ORM::factory('Feedback_Question', $id);
        if (!$question->loaded()) {
            $this->redirect('manage/feedback');
        }
        $cancel_url = Security::xss_clean(Arr::get($_GET, 'r', 'manage/feedback'));
        $this->set('cancel_url', Url::media($cancel_url));
        $this->set('question', $question);
        if ($this->request->method() == 'POST') {
            $answer = Arr::get($_POST, 'answer', '');
            try {
                $answers = ORM::factory('Feedback_Answer');
                $answers->answer = $answer;
                $answers->date = date('Y-m-d G:i:s');
                $answers->question_id = $id;
                $answers->moderator_id = $this->user->id;
                $answers->save();
                $question->is_answered = 1;
                $question->save();
                Message::success(i18n::get('The answer to the question is saved'));
                $this->redirect($cancel_url);
            } catch (ORM_Validation_Exception $e) {
                $this->set('answer', $answer);
                $this->set('errors', true);
            }
        }
    }

    public function action_spam()
    {
        $id = (int)$this->request->param('id', 0);
        $question = ORM::factory('Feedback_Question', $id);
        $user_id = $this->user->id;
        if (!$question->loaded()) {
            $this->redirect('manage/feedback');
        }
        $token = Arr::get($_POST, 'token', false);
        $return = Security::xss_clean(Arr::get($_GET, 'r', 'manage/expert'));
        $this->set('return', Url::media($return));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $question->is_spam = ($question->is_spam + 1) % 2;
            $question->spam_mod_id = $user_id;
            $question->save();
            if ($question->is_spam == 1) {
                Message::success(i18n::get('The question is marked as spam'));
            } else {
                Message::success(i18n::get('Marked "Spam" is removed from the question'));
            }
            $this->redirect($return);
        } else {
            if ($question->loaded()) {
                $this->set('question', $question)->set('token', Security::token(true));

            } else {
                $this->redirect('manage/expert');
            }
        }
    }
}