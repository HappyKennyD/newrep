<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Debate extends Controller_Core
{

    public function action_index()
    {
        $list = ORM::factory('Debate')->where_open()->where('is_public', '=', 1);
        $id = (int)$this->request->param('id', 0);
        $status = Security::xss_clean(Arr::get($_GET, 'status', ''));
        //SEO. закрываем сортировку
        if ($id!=0 or $status!='')
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $nowdate = date('Y-m-d H:i:s');
        $user = ORM::factory('User', $id);
        if (!$user->loaded() and $id) {
            $this->redirect("debate", 301);
        }
        if (Auth::instance()->logged_in()) {
            $has_ma_access = Auth::instance()->get_user()->has_access('ma');
        } else {
            $has_ma_access = 0;
        }
        if (!$has_ma_access) {
            $list = $list->and_where('is_closed', '=', 0);
        } else {
            $list = $list->or_where('is_public', '=', 0);
        }
        if ($id) {
            $username = $user->username;
            $this->add_cumb('Debate', 'debate');
            $this->add_cumb($username, '/');
        } else {
            $this->add_cumb('Debate', '/');
        }
        switch ($status) {
            case "active":
                $list->and_where('end_time', '>', $nowdate);
                $this->set('status', 'active');
                break;
            case "ending":
                $list->and_where('end_time', '<', $nowdate);
                $this->set('status', 'ending');
                break;
            default:
                if ($id) {
                    $list->and_where('author_id', '=', $id);
                    $this->set('id', $id);
                } else {
                    if (Auth::instance()->logged_in()) {
                        $list->or_where('author_id', '=', Auth::instance()->get_user()->id)->or_where('opponent_email', '=', Auth::instance()->get_user()->email);
                    }
                }
                $this->set('status', 'all');
        }

        $list->where_close()->and_where('language', '=', $this->language)->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list);
        $this->set('paginate', $paginate);
        $this->set('nowdate', $nowdate);

        $authors = new Massiv();
        $users = array();
        $debate = ORM::factory('Debate')->where('is_public', '=', 1)->and_where('is_closed', '=', 0)->find_all();
        foreach ($debate as $item) {
            if (!in_array($item->author_id, $users)) {
                $users[] = $item->author_id;
                $count = ORM::factory('Debate')->where('is_public', '=', 1)->and_where('author_id', '=', $item->author_id)->count_all();
                $username = $item->author->username;

                $authors[$item->author_id] = array('name' => $username, 'count' => $count);
            }
            if (sizeof($users) == 25) {
                break;
            }
        }
        $this->set('authors', $authors);

        /* метатэг description */
        $this->metadata->description( i18n::get('Дебаты на исторические темы') );
    }

    public function action_new()
    {
        if (!Auth::instance()->logged_in()) {
            Message::success(i18n::get('You must be logged in to create new debates'));
            $this->redirect('debate',301);
        }
        $user_id = Auth::instance()->get_user()->id;
        $debate = ORM::factory('Debate');
        if ($this->request->method() == Request::POST) {
            $email = strtolower(Arr::get($_POST, 'opponent_email', ''));
            if (Auth::instance()->get_user()->email != $email) {
                try {
                    $debate->title = Arr::get($_POST, 'title', '');
                    $debate->description = Arr::get($_POST, 'description', '');
                    $debate->date = date('Y-m-d H:i:s');
                    $debate->lifetime = (int)Arr::get($_POST, 'lifetime', '');
                    $debate->author_id = $user_id;
                    $debate->opponent_email = $email;
                    $debate->language = $this->language;
                    $debate->save();
                    Message::success(i18n::get('Created a new debate. Your opponent to the post office sent an invitation'));
                    $user = ORM::factory('User', $user_id);
                    $author = $user->username;
                    $debate_link = Url::site("debate/view/" . $debate->id, true);
                    $opponent = ORM::factory('User')->where('email', '=', $email)->find();
                    $ending = ($opponent->loaded()) ? true : "<a href='" . Url::site("auth/register", true) . "'>" . Url::site("auth/register", true) . "</a>";

                    Email::connect();
                    Email::View('new_debate');
                    Email::set(array('author' => $author, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>", 'ending' => $ending, 'ending_tr' => true));
                    Email::send($debate->opponent_email, array('no-reply@e-history.kz', 'e-history.kz'), __("Участие в дебатах на тему :theme", array(':theme' => "'" . $debate->title . "'")), '', true);
                    $this->redirect('debate',301);
                } catch (ORM_Validation_Exception $e) {

                    $errors = $e->errors($e->alias());
                    $this->set('errors', $errors);
                    $this->set('debate', $_POST);
                }
            } else {
                $errors['opponent_email'] = 'Enter the correct email';
            }
        }
        $this->add_cumb('Debate', 'debate');
        $this->add_cumb('New debate', '/');
    }

    public function action_edit()
    {
        if (!Auth::instance()->logged_in()) {
            Message::success(i18n::get('You have to login to edit the debate'));
            $this->redirect('debate',301);
        }
        $user_id = Auth::instance()->get_user()->id;
        $return = Security::xss_clean(Arr::get($_GET, 'r', ''));
        $id = (int)$this->request->param('id', 0);
        $debate = ORM::factory('Debate', $id);
        $nowdate = date('Y-m-d H:i:s');
        $date = date('Y-m-d H:i:s', strtotime("+36 hours", strtotime($debate->date)));
        if ((!$debate->loaded()) or ($debate->is_public == 1) or ($debate->is_closed == 1) or ($debate->author_id != $user_id) or ($date < $nowdate)) {
            Message::success(i18n::get('You can not edit this debate'));
            $this->redirect('debate',301);
        }
        $this->set('debate', $debate);
        if ($this->request->method() == Request::POST) {
            $opponent_email=$debate->opponent_email;
            try {
                $debate->title = Arr::get($_POST, 'title', '');
                $debate->description = Arr::get($_POST, 'description', '');
                $debate->date = date('Y-m-d H:i:s');
                $debate->lifetime = (int)Arr::get($_POST, 'lifetime', '');
                $debate->opponent_email = strtolower(Arr::get($_POST, 'opponent_email', ''));
                $debate->language = $this->language;
                $debate->save();
                if ($opponent_email!=$debate->opponent_email){
                    $user = ORM::factory('User', $user_id);
                    $author = $user->username;
                    $debate_link = Url::site("debate/view/" . $debate->id, true);
                    $opponent = ORM::factory('User')->where('email', '=', $debate->opponent_email)->find();
                    $ending = ($opponent->loaded()) ? true : "<a href='" . Url::site("auth/register", true) . "'>" . Url::site("auth/register", true) . "</a>";
                    Email::connect();
                    Email::View('new_debate');
                    Email::set(array('author' => $author, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>", 'ending' => $ending, 'ending_tr' => true));
                    Email::send($debate->opponent_email, array('no-reply@e-history.kz', 'e-history.kz'), __("Участие в дебатах на тему :theme", array(':theme' => "'" . $debate->title . "'")), '', true);
                }
                Message::success(i18n::get('Debate edited'));
                $this->redirect($return,301);
            } catch (ORM_Validation_Exception $e) {

                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
                $this->set('debate', $_POST);
            }
        }
        $this->add_cumb('Debate', 'debate');
        $this->add_cumb('Edit debate', '/');
    }

    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $debate = ORM::factory('Debate', $id);
        if (Auth::instance()->logged_in()) {
            $user_id = Auth::instance()->get_user()->id;
            if (($debate->author_id != $user_id) or ($debate->opponent_id != $user_id)) {
                $has_access = 1;
            }
            $has_ma_access = Auth::instance()->get_user()->has_access('ma');
        }
        if (!isset($has_access)) {
            $has_access = 0;
        }
        if (!isset($has_ma_access)) {
            $has_ma_access = 0;
        }
        if ((!$debate->loaded()) or (!$has_ma_access and ((($debate->is_public != 1) and (!$has_access)) or ($debate->is_closed)))) {
            if ($debate->is_closed) {
                Message::success(i18n::get('The debate closed'));
            } else {
                Message::success(i18n::get('You can not view the debate'));
            }
            $this->redirect('debate',301);
        }

        $nowdate = date('Y-m-d H:i:s');
        $this->set('debate', $debate);
        $this->set('nowdate', $nowdate);
        $opinions = ORM::factory('Debate_Opinion')->where('debate_id', '=', $id)->order_by('date', 'ASC')->find_all();
        $comments = ORM::factory('Debate_Comment')->where('debate_id', '=', $id);
        if (!$has_ma_access) {
            $comments = $comments->and_where('hide', '=', 0);
        }
        $comments = $comments->order_by('date', 'ASC');
        $comments_count = clone $comments;
        $comments_count = $comments_count->count_all();
        $comments = $comments->find_all();
        $this->set('opinions', $opinions)->set('nowdate', date("Y-m-d H:i:s"))->set('comments_count', $comments_count)->set('comments', $comments);
        if ($this->request->method() == Request::POST) {
            if (($debate->replier_id != $user_id) or ($debate->end_time < date("Y-m-d H:i:s"))) {
                $this->redirect('debate',301);
            }
            try {
                $opinion = ORM::factory('Debate_Opinion');
                $opinion->debate_id = $id;
                $opinion->author_id = $user_id;
                $opinion->date = date('Y-m-d H:i:s');
                $opinion->moderator_text = $opinion->opinion = Arr::get($_POST, 'opinion', '');
                $opinion->save();
                $debate->replier_id = ($debate->replier_id == $debate->author_id) ? ($debate->opponent_id) : ($debate->author_id);
                $debate->save();
                Message::success(i18n::get('Your opinion published'));

                $user = ORM::factory('User', $debate->replier_id);
                $email = $user->profile->email;
                $opponent = $user->username;
                $user = ORM::factory('User', $user_id);
                $author = $user->username;
                $debate_link = Url::site("debate/view/" . $debate->id, true);
                Email::connect();
                Email::View('opinion');
                Email::set(array('opponent' => $opponent, 'author' => $author, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>", 'opinion' => $opinion->opinion));
                Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), __("Ответ в дебатах на портале :link", array(':link' => 'History.kz')), '', true);
                $this->redirect('debate/view/' . $id,301);
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
                $this->set('opinion', $_POST);
            }
        }
        $this->add_cumb('Debate', 'debate');
        $this->add_cumb($debate->title, '/');
        $metadesc=$debate->title;

        $this->metadata->snippet($debate->description);
    }

    public function action_close()
    {
        $id = (int)$this->request->param('id', 0);
        if (!Auth::instance()->logged_in()) {
            Message::success(i18n::get('You can not close the debate'));
            $this->redirect('debate',301);
        }
        $return = Security::xss_clean(Arr::get($_GET, 'r', ''));
        $debate = ORM::factory('Debate', $id);
        if (($debate->author_id == Auth::instance()->get_user()->id) and ($debate->is_public = 0)) {
            $debate->is_closed = 1;
            $debate->save();
            Message::success(i18n::get('The debate closed'));
            $this->redirect($return,301);
        } else {
            Message::success(i18n::get('You can not close the debate'));
            $this->redirect('debate',301);
        }

    }

    public function action_agree()
    {
        $id = (int)$this->request->param('id', 0);
        if (!Auth::instance()->logged_in()) {
            Message::success(i18n::get('You can not participate in this debate'));
            $this->redirect('debate',301);
        }
        $debate = ORM::factory('Debate', $id);
        $nowdate = date('Y-m-d H:i:s');
        $date = date('Y-m-d H:i:s', strtotime("+36 hours", strtotime($debate->date)));
        if (($debate->opponent_email == Auth::instance()->get_user()->email) and ($debate->is_closed == 0) and ($debate->is_public == 0) or ($date < $nowdate)) {

            $date = date('Y-m-d H:i:s');
            $debate->start_time = $date;
            $debate->end_time = date('Y-m-d H:i:s', strtotime("+" . $debate->lifetime . " hours", strtotime($date)));
            $debate->opponent_id = Auth::instance()->get_user()->id;
            $debate->is_public = 1;
            $debate->replier_id = $debate->author_id;
            $debate->save();


            $user_id = Auth::instance()->get_user()->id;
            $user = ORM::factory('User', $debate->author_id);
            $email = $user->profile->email;
            $opponent = $user->username;
            $user = ORM::factory('User', $user_id);
            $author = $user->username;
            $debate_link = Url::site("debate/view/" . $debate->id, true);
            Message::success(i18n::get('You have agreed to participate in the debates. Notification to this effect was sent to your opponent'));
            Email::connect();
            Email::View('debate_agree');
            Email::set(array('opponent' => $opponent, 'author' => $author, 'link' => 'e-history.kz', 'theme' => "<a href='" . $debate_link . "'>'" . $debate->title . "'</a>"));
            Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), __("Ваш оппонент согласился участвовать в дебатах на портале :link", array(':link' => 'History.kz')), '', true);
            $this->redirect('debate/view/' . $id,301);
        } else {
            if ($date < $nowdate) {
                Message::success(i18n::get('Expired 36 hours during which you could agree to take part in the debate'));
            } else {
                Message::success(i18n::get('You can not give consent to participate in this debate'));
            }
            $this->redirect('debate',301);
        }
    }

    public function action_disagree()
    {
        $id = (int)$this->request->param('id', 0);
        if (!Auth::instance()->logged_in()) {
            Message::success(i18n::get('You can not participate in this debate'));
            $this->redirect('debate',301);
        }
        $debate = ORM::factory('Debate', $id);
        if (($debate->opponent_email == Auth::instance()->get_user()->email) and ($debate->is_public == 0)) {
            $debate->is_closed = 1;
            $debate->save();
            Message::success(i18n::get('You have refused to participate in debates'));
        } else {
            Message::success(i18n::get('You can not participate in this debate'));
        }
        $this->redirect('debate',301);
    }

    public function action_plus()
    {
        if (Auth::instance()->logged_in()) {
            $user_id = Auth::instance()->get_user()->id;
            $id = (int)$this->request->param('id', 0);
            $opinion = ORM::factory('Debate_Opinion', $id);
            $poll_user = ORM::factory('Debate_Poll')->where('user_id', '=', $user_id)->and_where('branch_id', '=', $id)->find();
            if ((!$poll_user->loaded()) and ($opinion->debate->author_id != Auth::instance()->get_user()->id) and ($opinion->debate->opponent_email != Auth::instance()->get_user()->email)) {
                $poll = ORM::factory('Debate_Poll');
                $poll->branch_id = $id;
                $poll->variant = 1;
                $poll->user_id = $user_id;
                $poll->save();
                $opinion->plus += 1;
                $opinion->save();
                Message::success(i18n::get('Your vote has been counted, thank you!'));
            } elseif (($opinion->debate->author_id == Auth::instance()->get_user()->id) or ($opinion->debate->opponent_email == Auth::instance()->get_user()->email)) {
                Message::success(i18n::get('You can not vote in the debate, to which you'));
            } else {
                Message::success(i18n::get('You have already voted'));
            }
            $return = Security::xss_clean(Arr::get($_GET, 'r', 'debate/view/' . $opinion->debate_id));
        }
        if (!isset ($return)) {
            $return = 'debate';
        }
        $this->redirect($return,301);
    }

    public function action_minus()
    {
        if (Auth::instance()->logged_in()) {
            $user_id = Auth::instance()->get_user()->id;
            $id = (int)$this->request->param('id', 0);
            $opinion = ORM::factory('Debate_Opinion', $id);
            $poll_user = ORM::factory('Debate_Poll')->where('user_id', '=', $user_id)->and_where('branch_id', '=', $id)->find();
            if ((!$poll_user->loaded()) and ($opinion->debate->author_id != Auth::instance()->get_user()->id) and ($opinion->debate->opponent_email != Auth::instance()->get_user()->email)) {
                $poll = ORM::factory('Debate_Poll');
                $poll->branch_id = $id;
                $poll->variant = 1;
                $poll->user_id = $user_id;
                $poll->save();
                $opinion->minus += 1;
                $opinion->save();
                Message::success(i18n::get('Your vote has been counted, thank you!'));
            } elseif (($opinion->debate->author_id == Auth::instance()->get_user()->id) or ($opinion->debate->opponent_email == Auth::instance()->get_user()->email)) {
                Message::success(i18n::get('You can not vote in the debate, to which you'));
            } else {
                Message::success(i18n::get('You have already voted'));
            }
            $return = Security::xss_clean(Arr::get($_GET, 'r', 'debate/view/' . $opinion->debate_id));
        }
        if (!isset ($return)) {
            $return = 'debate';
        }
        $this->redirect($return,301);
    }

    public function action_comment()
    {
        $id = (int)$this->request->param('id', 0);
        $return = Security::xss_clean(Arr::get($_GET, 'r', 'debate/view/' . $id));
        if ($this->request->method() == Request::POST) {
            try {
                if (Auth::instance()->logged_in()) {
                    $user_id = Auth::instance()->get_user()->id;
                    $comment = Arr::get($_POST, 'comment', '');
                    $debate = ORM::factory('Debate_Comment');
                    $debate->debate_id = $id;
                    $debate->date = date('Y-m-d H:i:s');
                    $debate->comment = $comment;
                    $debate->user_id = $user_id;
                    $debate->save();
                    $debate = ORM::factory('Debate', $id);
                    $debate->comments_count += 1;
                    $debate->save();
                    Message::success(i18n::get('Your comment has been saved, thanks!'));
                }
            } catch (ORM_Validation_Exception $e) {
            }
            $this->redirect($return ,301);
        }
    }

}
