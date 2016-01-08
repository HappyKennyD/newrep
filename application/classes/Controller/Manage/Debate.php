<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Debate extends Controller_Manage_Core
{
    public static function log($debate_id, $action, $old = false, $new = false, $changed_id = false)
    {
        $log = ORM::factory('Debate_Log');
        $log->moderator_id = Auth::instance()->get_user()->id;
        $log->date = date('Y-m-d H:i:s');
        $log->debate_id = $debate_id;
        $log->action = $action;
        $log->changed_id = $changed_id;
        switch ($action) {
            case "time":
                $log->old_time = $old;
                $log->new_time = $new;
                break;
            case "member":
                $log->old_member = $old;
                $log->new_member = $new;
                break;
            case "edit":
            case "title":
            case "description":
                $log->old_text = $old;
                $log->new_text = $new;
                break;
        }
        $log->save();
    }

    public function action_index()
    {
        $list = ORM::factory('Debate')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }

    public function action_hide()
    {
        $id = (int)$this->request->param('id', 0);
        $item = ORM::factory('Debate', $id);
        $r = Security::xss_clean(Arr::get($_GET, 'r', 'manage/debate'));
        if (!$item->loaded()) {
            $this->redirect($r);
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            try {
                $item->is_closed = $item->is_closed == 1 ? 0 : 1;
                $item->save();
                $action = ($item->is_closed) ? 'close' : 'open';
                $this->log($id, $action);
            } catch (ORM_Validation_Exception $e) {
            }
            $this->redirect($r);
        } else {
            $this->set('item', $item)->set('token', Security::token(true))->set('cancel_url', Url::site($r));
        }
    }

    public function action_opinions()
    {
        $list = ORM::factory('Debate_Opinion')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }

    public function action_opinionhide()
    {
        $id = (int)$this->request->param('id', 0);
        $item = ORM::factory('Debate_Opinion', $id);
        $r = Security::xss_clean(Arr::get($_GET, 'r', 'manage/debate/opinions'));
        if (!$item->loaded()) {
            $this->redirect($r);
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            try {
                $old_text = (!empty($item->moderator_text)) ? $item->moderator_text : $item->opinion;
                $item->moderator_id = $this->user->id;
                $item->moderator_text = Arr::get($_POST, 'moderator_text', '');
                $item->save();
                $this->log($item->debate_id, 'edit', $old_text, $item->moderator_text, $id);
                $this->redirect($r);
            } catch (ORM_Validation_Exception $e) {
                $this->set('item', $item)->set('post', $_POST)->set('error', true);
            }

        } else {
            $this->set('item', $item)->set('token', Security::token(true))->set('cancel_url', Url::site($r));
            if ($this->request->method() == Request::POST) {
                $this->set('post', $_POST);
            }
        }
    }

    public function action_comments()
    {
        $list = ORM::factory('Debate_Comment')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }

    public function action_commenthide()
    {
        $id = (int)$this->request->param('id', 0);
        $item = ORM::factory('Debate_Comment', $id);
        $r = Security::xss_clean(Arr::get($_GET, 'r', 'manage/debate/comments'));
        if (!$item->loaded()) {
            $this->redirect($r);
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            try {
                $item->hide = $item->hide == 1 ? 0 : 1;
                $item->save();
                $debate = ORM::factory('Debate', $item->debate_id);
                $debate->comments_count += $item->hide == 1 ? (-1) : 1;
                $debate->save();
                $action = ($item->hide) ? 'hide' : 'show';
                $this->log($item->debate_id, $action, false, false, $id);
            } catch (ORM_Validation_Exception $e) {
            }
            $this->redirect($r);
        } else {
            $this->set('item', $item)->set('token', Security::token(true))->set('cancel_url', Url::site($r));
        }
    }

    public function action_new()
    {
        $id = (int)$this->request->param('id', 0);
        $debate = ORM::factory('Debate', $id);
        if ($debate->loaded() and !$debate->admin_create) {
            $this->redirect('manage/debate');
        }
        $this->set('debate', $debate);
        if ($this->request->method() == Request::POST) {
            $author_email = strtolower(Arr::get($_POST, 'author_email', ''));
            $opponent_email = strtolower(Arr::get($_POST, 'opponent_email', ''));
            $author = ORM::factory('User')->where('email', '=', $author_email)->find();
            $opponent = ORM::factory('User')->where('email', '=', $opponent_email)->find();
            if ($opponent->loaded() && $author->loaded() && $author_email != $opponent_email) {
                if ($debate->loaded()) {
                    $old_title = $debate->title;
                    $old_description = $debate->description;
                    $old_end_time = $debate->end_time;
                    $old_author = $debate->author_id;
                    $old_opponent = $debate->opponent_id;
                } else {
                    $debate_create = true;
                }
                try {
                    $debate->title = Arr::get($_POST, 'title', '');
                    $debate->description = Arr::get($_POST, 'description', '');
                    $debate->lifetime = (int)Arr::get($_POST, 'lifetime', '');
                    $debate->date = $debate->start_time = date('Y-m-d H:i:s');
                    $debate->end_time = date('Y-m-d H:i:s', strtotime("+" . $debate->lifetime . " hours", strtotime(date('Y-m-d H:i:s'))));
                    $debate->author_id = $debate->replier_id = $author->id;
                    $debate->author_email = $author_email;
                    $debate->opponent_id = $opponent->id;
                    $debate->opponent_email = $opponent_email;
                    $debate->language = $this->language;
                    $debate->is_public = 1;
                    $debate->admin_create = 1;
                    $debate->save();
                    if (isset($old_title) && $old_title != $debate->title) {
                        $this->log($debate->id, 'title', $old_title, $debate->title);
                    }
                    if (isset($old_description) && $old_description != $debate->description) {
                        $this->log($debate->id, 'description', $old_description, $debate->description);
                    }
                    if (isset($old_end_time) && $old_end_time != $debate->end_time) {
                        $this->log($debate->id, 'time', $old_end_time, $debate->end_time);
                    }
                    if (isset($old_author) && $old_author != $debate->author_id) {
                        $this->log($debate->id, 'member', $old_author, $debate->author_id);
                    }
                    if (isset($old_opponent) && $old_opponent != $debate->opponent_id) {
                        $this->log($debate->id, 'member', $old_opponent, $debate->opponent_id);
                    }
                    if (isset($debate_create)) {
                        $this->log($debate->id, 'create');
                    }
                    Message::success(i18n::get('Дебаты сохранены'));
                    $debate_link = Url::site("debate/view/" . $debate->id, true);
                    $ending_tr = "Your opponent was given the right to begin debates";
                    Email::connect();
                    Email::View('new_debate');
                    Email::set(array('author' => $author->username, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>", 'ending' => true, 'ending_tr' => $ending_tr));
                    Email::send($debate->opponent_email, array('no-reply@e-history.kz', 'e-history.kz'), __("Участие в дебатах на тему :theme", array(':theme' => "'" . $debate->title . "'")), '', true);
                    $ending_tr = "You are given the right to begin debates";
                    Email::connect();
                    Email::View('new_debate');
                    Email::set(array('author' => $opponent->username, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>", 'ending' => true, 'ending_tr' => $ending_tr));
                    Email::send($debate->author_email, array('no-reply@e-history.kz', 'e-history.kz'), __("Участие в дебатах на тему :theme", array(':theme' => "'" . $debate->title . "'")), '', true);

                    $this->redirect('manage/debate');
                } catch (ORM_Validation_Exception $e) {
                    $errors = $e->errors($e->alias());
                    $this->set('debate', $_POST);
                    $this->set('errors', $errors);
                }
            } else {
                if (!$author->loaded()) {
                    $errors['author_email'] = true;
                }
                if (!$opponent->loaded()) {
                    $errors['opponent_email'] = true;
                }
                if ($author_email == $opponent_email) {
                    $errors['members'] = true;
                }
                $this->set('debate', $_POST);
                $this->set('errors', $errors);
            }
        }
    }

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $debate = ORM::factory('Debate', $id);
        if (!$debate->loaded() or $debate->admin_create) {
            $this->redirect('manage/debate');
        }
        $this->set('debate', $debate);
        if ($this->request->method() == Request::POST) {
            $old_title = $debate->title;
            $old_description = $debate->description;
            try {
                $debate->title = Arr::get($_POST, 'title', '');
                $debate->description = Arr::get($_POST, 'description', '');
                $debate->save();
                Message::success(i18n::get('Дебаты сохранены'));
                if ($old_title != $debate->title) {
                    $this->log($debate->id, 'title', $old_title, $debate->title);
                }
                if ($old_description != $debate->description) {
                    $this->log($debate->id, 'description', $old_description, $debate->description);
                }
                $this->redirect('manage/debate');
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('debate', $_POST);
                $this->set('errors', $errors);
            }
        }
    }

    public function action_logs()
    {
        $list = ORM::factory('Debate_Log')->order_by('id', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }

    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $log = ORM::factory('Debate_Log', $id);
        if (!$log->loaded()) {
            $this->redirect('manage/debate');
        }
        $this->set('item',$log);
    }
}