<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Expertcomments extends Controller_Manage_Core
{
    public function action_index()
    {
        $comments = ORM::factory('Expert_Comment')->order_by('date', 'desc');
        $paginate = Paginate::factory($comments)->set_view('manage/paginate')->paginate()->render();
        $comments = $comments->find_all();
        $this->set('paginate', $paginate)->set('comments', $comments);
    }

    public function action_show()
    {
        $id = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        $return = Security::xss_clean(Arr::get($_GET, 'r', 'manage/expertcomments'));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $comments = ORM::factory('Expert_Comment', $id);
            $comments->moderator_decision = ($comments->moderator_decision + 1) % 2;
            $comments->moderator_id = $this->user->id;
            $comments->save();
            $expert = ORM::factory('Expert_Opinion', $comments->opinion_id);
            $comments_count = $expert->comments_count;
            $comments_count += ($comments->moderator_decision) ? 1 : (-1);
            DB::query(Database::UPDATE, "UPDATE `history`.`expert_opinions` SET `comments_count` = '" . $comments_count . "' WHERE `id` = '" . $expert->id . "'")->execute();
            $this->redirect($return);
        } else {
            $comment = ORM::factory('Expert_Comment', $id);
            if ($comment->loaded()) {
                $this->set('item', $comment)->set('token', Security::token(true));
            } else {
                $this->redirect($return);
            }
        }

    }
}