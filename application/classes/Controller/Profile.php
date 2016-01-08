<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Profile extends Controller_Core
{

    public function before()
    {
        parent::before();

        if (Auth::instance()->logged_in()) {
        $user_id = $this->user->id;
        $user = $this->user;
        $this->set('user', $user);
        $profile_socials = ORM::factory('User_Social')->where('user_id', '=', $user_id)->limit(5)->find_all();
        $this->set('profile_socials', $profile_socials);

        $avatar_uploader = View::factory('storage/avatar')->set('user_id', $this->user->id)->render();
        $this->set('avatar_uploader', $avatar_uploader);
        }/*else{
            $this->redirect('/',301);
        }*/

    }

    public function action_index()
    {
        if (!$this->user)
            throw new HTTP_Exception_404();
        $user_id = $this->user->id;
        $user = ORM::factory('User', $user_id);
        $counts = array();
        $debates = ORM::factory('Debate')->where_open()->where('author_id', '=', $user_id)->or_where('opponent_id', '=', $user_id)->where_close()->and_where('is_closed', '=', 0)->and_where('is_public', '=', 1);
        $clone = clone $debates;
        $counts['debate'] = $clone->count_all();
        $debates = $debates->find_all();
        $subscriptions = ORM::factory('Subscription')->where('user_id', '=', $user_id);
        $clone = clone $subscriptions;
        $counts['subs'] = $clone->count_all();
        $subscriptions = $subscriptions->find_all();
        $materials = ORM::factory('Material')->where('user_id', '=', $user_id);
        $clone = clone $materials;
        $counts['materials'] = $clone->count_all();
        $materials = $materials->find_all();
        $this->set('user', $user);
        $this->set('debates', $debates);
        $this->set('subscriptions', $subscriptions);
        $this->set('materials', $materials);
        $this->set('counts', $counts);
        $this->add_cumb('User profile', '/');
    }

    public function action_view()
    {
        $user_id = (int)$this->request->param('id',0);
        $user = ORM::factory('User', $user_id);
        if (!$user->loaded())
                throw new HTTP_Exception_404();
        $counts = array();
        $debates = ORM::factory('Debate')->where_open()->where('author_id', '=', $user_id)->or_where('opponent_id', '=', $user_id)->where_close()->and_where('is_closed', '=', 0)->and_where('is_public', '=', 1);
        $clone = clone $debates;
        $counts['debate'] = $clone->count_all();
        $debates = $debates->find_all();
        $subscriptions = ORM::factory('Subscription')->where('user_id', '=', $user_id);
        $clone = clone $subscriptions;
        $counts['subs'] = $clone->count_all();
        $subscriptions = $subscriptions->find_all();
        $materials = ORM::factory('Material')->where('user_id', '=', $user_id);
        $clone = clone $materials;
        $counts['materials'] = $clone->count_all();
        $materials = $materials->find_all();
        $this->set('user', $user);
        $this->set('debates', $debates);
        $this->set('subscriptions', $subscriptions);
        $this->set('materials', $materials);
        $this->set('counts', $counts);
        $this->add_cumb('User profile', '/');
    }

    public function action_materials()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user_id = $this->user->id;
        $materials=ORM::factory('Material')->where('user_id','=',$user_id);
        $paginate = Paginate::factory($materials)->paginate(NULL, NULL, 3)->render();
        $materials = $materials->find_all();
        $this->set('materials', $materials);
        $this->set('paginate', $paginate);
        $uploader = View::factory('storage/materials')->set('user_id', $user_id)->render();
        $this->set('uploader', $uploader);
        if ($this->request->method() == Request::POST) {
            $journal = (int)Arr::get($_POST, 'material_type', 0);
            if ($journal !== 1) {
                $journal = 0;
            }
            try {
                $material = ORM::factory('Material');
                $material->theme = substr(Arr::get($_POST, 'theme', ''), 0, 250);
                $material->message = substr(Arr::get($_POST, 'message', ''), 0, 500);
                $material->user_id = $user_id;
                $material->date = date('Y-m-d H:i:s');
                $material->lang_notice=strtolower(Kohana_I18n::lang());
                if ($journal) {
                    $material->is_journal = 1;
                    $material->is_moderator = 0;
                }
                $material->save();
                $files = Arr::get($_POST, 'files', '');
                if ($files) {
                    foreach ($files as $key => $file) {
                        if ($key > 7) {
                            break;
                        }
                        if ($file) {
                            $storage=ORM::factory('Storage',(int)$file);
                            try {
                                $upload = ORM::factory('Material_File');
                                $upload->user_id = $user_id;
                                $upload->material_id = $material->id;
                                $upload->date = date('Y-m-d H:i:s');
                                $upload->storage_id = (int)$file;
                                $upload->filesize=filesize($storage->file_path);
                                $upload->save();
                            } catch (ORM_Validation_Exception $e) {
                            }
                        }
                    }
                }
                Message::success(i18n::get('The material sent to the moderator. Thank you!'));

                $user=ORM::factory('User',$user_id);
                $user_email=$user->email;
                Email::connect();
                Email::View('review_now_'.i18N::lang());
                Email::set(array('message'=>I18n::get('Оставленный вами материал находится на рассмотрении редакционной коллегии портала')));
                Email::send($user_email, array('no-reply@e-history.kz', 'e-history.kz'), I18n::get('Рассмотрение материала на портале "История Казахстана" e-history.kz'), '', true);

                if ($journal != 1) {
                    $material_type='Интересные материалы';
                    $url=URL::media('/manage/materials', TRUE);
                } else {
                    $material_type='Журнал e-history';
                    $url=URL::media('/manage/materials/ehistory', TRUE);
                }

                $user_profile=ORM::factory('User_Profile',$user_id);
                if($user_profile->first_name!=''){
                    $user_name=$user_profile->first_name.' '.$user_profile->last_name;
                }else
                {
                    $user_name=$user->username;
                }

                $email='kaz.ehistory@gmail.com';

                Email::connect();
                Email::View('new_material');
                Email::set(array('url' => $url, 'material_type'=>$material_type, 'username'=>$user_name));
                Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), "Новый материал на e-history.kz", '', true);

                $this->redirect('profile/materials' ,301);
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $files = Arr::get($_POST, 'files', '');
                $this->set('errors', $errors)->set('material', $_POST)->set('files',$files);
            }
        }
        $this->add_cumb('User profile', 'profile');
        $this->add_cumb('Downloaded Content', '/');
    }

    public function action_information()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user_id = $this->user->id;
        $user = ORM::factory('User', $user_id);

        if (!$user->profile->is_visited) {
            $user->profile->user_id = $user->id;
            $user->profile->is_visited = 1;
            $user->profile->email = $user->email;
            $user->profile->save();
        }

        $socials = ORM::factory('User_Social')->where('user_id', '=', $user_id)->limit(5)->find_all();
        $this->set('socials', $socials);
        if ($this->request->method() == Request::POST) {
            $error = false;
            $links = Array();
            $keys = Array();
            for ($i = 1; $i <= 5; $i++) {
                $key = Arr::get($_POST, 'soc_link_' . $i . '_type', '');
                switch ($key) {
                    case '':
                    case 'vk':
                    case 'twitter':
                        break;
                    default:
                        $key = 'facebook';
                }
                $link = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'soc_link_' . $i, ''))));
                $links[$i - 1] = $link;
                $keys[$i - 1] = $key;
            }

            foreach ($keys as $key => $item) {
                if ($keys[$key] == '') {
                    continue;
                }

                if (($links[$key] != '') and (!isset($socials[$key]))) {
                    try {
                        $social = ORM::factory('User_Social');
                        $social->user_id = $user_id;
                        $social->social = $keys[$key];
                        $social->link = $links[$key];
                        $social->save();
                    } catch (ORM_Validation_Exception $e) {
                        $this->set('social_errors', true);
                        $error = true;
                    }
                }

                if (($links[$key] != '') and (isset($socials[$key])) and ($socials[$key]->link != $links[$key])) {
                    try {
                        $social = ORM::factory('User_Social', $socials[$key]->id);
                        $social->user_id = $user_id;
                        $social->social = $keys[$key];
                        $social->link = $links[$key];
                        $social->save();
                    } catch (ORM_Validation_Exception $e) {
                        $this->set('social_errors', true);
                        $error = true;
                    }
                }

                if (($links[$key] == '') and (isset($socials[$key]))) {
                    $social = ORM::factory('User_Social', $socials[$key]->id)->delete();
                }
            }
            $first_name = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'first_name', ''))));
            $last_name = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'last_name', ''))));
            $specialization = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'specialization', ''))));
            $about = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'about', ''))));
            try {
                if ($first_name !== $user->profile->first_name) {
                    $user->profile->first_name = $first_name;
                }
                if ($last_name !== $user->profile->last_name) {
                    $user->profile->last_name = $last_name;
                }
                if ($specialization !== $user->profile->specialization) {
                    $user->profile->specialization = $specialization;
                }
                if ($about !== $user->profile->about) {
                    $user->profile->about = $about;
                }
                $user->save();
                $user->profile->save();
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('errors', $errors)->set('item', $_POST);
                $error = true;
            }
            if (!$error) {
                Message::success(i18n::get('Your personal details are kept'));
                $this->redirect('profile/information' ,301);
            } else {
                $socials = new Massiv();
                for ($i = 0; $i < 5; $i++) {
                    $socials[$i] = array('link' => $links[$i], 'social' => $keys[$i]);
                }
                $this->set('socials', $socials);
            }
        }
        $this->add_cumb('User profile', 'profile');
        $this->add_cumb('Personal data', '/');
    }

    public function action_settings()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user = ORM::factory('User', $this->user->id);
        $this->set('user', $user);
        if ($this->request->method() == Request::POST) {
            $email = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'email', ''))));
            $old_password = Auth::instance()->hash(Arr::get($_POST, 'password', ''));
            $new_password = Arr::get($_POST, 'new_password', '');
            $new_password_repeat = Arr::get($_POST, 'confirmation_password', '');
            $error = Array();
            try {
                if ($email !== $user->profile->email && $email != '') {
                    $user->profile->email = $email;
                }
                if (!$user->network_reg) {

                    if ($email !== $user->email) {
                        $user->email = $email;
                    }
                }
                if ($new_password != '' && $old_password == $user->password && $new_password == $new_password_repeat) {
                    $user->password = $new_password;
                }
                $user->save();
                $user->profile->save();
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
                $this->set('post', $_POST);
                $error['save'] = true;
            }
            if ($old_password == $user->password && $new_password != $new_password_repeat) {
                $error['other_pass'] = true;
            }
            if ($old_password != Auth::instance()->hash('') && $old_password != $user->password) {
                $error['false_pass'] = true;
            }
            if (!$error) {
                Message::success(i18n::get('Account settings are stored'));
                $this->redirect('profile/settings' ,301);
            } else {
                $this->set('error', $error);
            }
        }

        $this->add_cumb('User profile', 'profile');
        $this->add_cumb('Account Settings', '/');
    }

    public function action_feedback()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user_id = $this->user->id;
        $questions = ORM::factory('Feedback_Question')->where('user_id', '=', $user_id)->where('is_spam', '=', 0)->order_by('date', 'DESC');
        $paginate = Paginate::factory($questions)->paginate(NULL, NULL, 10)->render();
        $questions = $questions->find_all();
        $this->set('questions', $questions);
        $this->set('paginate', $paginate);
      /*  if ($this->request->method() == Request::POST) {
            try {
                $question = ORM::factory('Feedback_Question');
                $question->user_id = $user_id;
                $question->question = Arr::get($_POST, 'question', '');
                $question->date = date('Y-m-d H:i:s');
                $question->save();
                Message::success(i18n::get('Your question was sent moderators portal'));
                $this->redirect('profile/feedback');
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                $this->set('question', $_POST);
                $this->set('errors', $errors);
            }
        }*/
        $this->add_cumb('User profile', 'profile');
        $this->add_cumb('Feedback', '/');
    }

    public function action_subscription()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user_id = $this->user->id;
        if ($this->request->method() == Request::POST) {
            $parent_id = (int)Arr::get($_POST, 'parent_id', 0);
            $child_id = (int)Arr::get($_POST, 'child_id', 0);
            $sub_check = ORM::factory('Subscription')->where('user_id', '=', $user_id)->and_where('category_id', '=', $child_id)->find();
            if (!$sub_check->loaded()) {
                try {
                    $subscription = ORM::factory('Subscription');
                    $subscription->user_id = $user_id;
                    $subscription->category_id = $child_id;
                    $subscription->date = date('Y-m-d H:i:s');
                    $subscription->save();
                    Message::success(i18n::get('Subscribe to the selected partition saved'));
                } catch (ORM_Validation_Exception $e) {
                }
            } else {
                Message::success(i18n::get('You are already subscribed to this forum'));
            }
            $this->redirect('profile/subscription' ,301);
        }

        $keys = ORM::factory('Page')->where('key', '=', 'subscription')->or_where('key', '=', 'menu')->order_by('id', 'ASC')->find_all();
        $parent = ORM::factory('Page')->where('id', '<', 0);
        foreach ($keys as $key) {
            $parent = $parent->or_where('parent_id', '=', $key->id);
        }
        $parent = $parent->order_by('id', 'ASC')->find_all();


        if (!isset($parent_id) or !$parent_id) {
            $parent_id = $parent[0]->id;
        }
        $parent_id_control = ORM::factory('Page', $parent_id);
        if (!$parent_id_control->loaded() or $parent_id_control->lvl != 2) {
            $parent_id = $parent[0]->id;
        }
        $child = ORM::factory('Page')->where('parent_id', '=', $parent_id)->order_by('id', 'ASC')->find_all();
        $this->set('parent', $parent);
        $this->set('child', $child);
        $this->set('parent_id', $parent_id);

        $subscriptions = ORM::factory('Subscription')->where('user_id', '=', $user_id);
        $clone = clone $subscriptions;
        $counts['subs'] = $clone->count_all();
        $subscriptions = $subscriptions->find_all();
        $this->set('counts', $counts);
        $this->set('subscriptions', $subscriptions);
        $settings = ORM::factory('Subscription_Setting')->where('user_id', '=', $user_id)->find();
        $this->set('settings', $settings);

        $this->add_cumb('User profile', 'profile');
        $this->add_cumb('Subscriptions', '/');
    }

    public function action_subset()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        if ($this->request->method() == Request::POST) {
            $lang = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'lang', ''))));
            $period = (int)Arr::get($_POST, 'period', 0);
            $user_id = $this->user->id;
            if ($lang != 'ru' and $lang != 'en') {
                $lang = 'kz';
            }
            if ($period != 1 and $period != 3) {
                $period = 2;
            }
            try {
                $settings = ORM::factory('Subscription_Setting')->where('user_id', '=', $user_id)->find();
                $settings->user_id = $user_id;
                $settings->lang = $lang;
                $settings->period = $period;
                $settings->save();
                Message::success(i18n::get('The subscription settings saved'));
            } catch (ORM_Validation_Exception $e) {
            }
        }
        $this->redirect('profile/subscription' ,301);
    }

    public function action_deletesubscription()
    {
        if (!Auth::instance()->logged_in()) {
            $this->redirect('/',301);
        }
        $user_id = $this->user->id;
        $id = (int)$this->request->param('id', 0);
        $subscription = ORM::factory('Subscription', $id);
        if ($subscription->user_id == $user_id) {
            $subscription->delete();
            Message::success(i18n::get('Subscribe removed'));
        }
        $this->redirect('profile/subscription' ,301);
    }
}