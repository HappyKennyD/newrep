<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Jobs extends Controller_Manage_Core {

	public function action_index()
	{
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/jobs/search/'.$search);
        }

        $jobs = ORM::factory('Jobs')->where('published', '!=', -1)->order_by('date', 'DESC');
        $paginate = Paginate::factory($jobs)->paginate(NULL, NULL, 10)->render();
        $jobs = $jobs->find_all();
        $this->set('jobs', $jobs);
        $this->set('paginate', $paginate);
        $job_pub = 1;
        $this->set('job_pub', $job_pub);
	}

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(post_ru, post_kz, post_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $jobs = ORM::factory('Jobs')->where($query_m, '', $query_a)->find_all();
        $this->set('jobs', $jobs);

        $totalcount = sizeof($jobs);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $jobs = ORM::factory('Jobs', $id);
        if ($jobs->loaded())
        {
            $this->set('jobs', $jobs);
        }
        else
        {
            $this->redirect('manage/jobs');
        }
    }

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $jobs = ORM::factory('Jobs', $id);
        $errors = 0;

        if ($this->request->method() == 'POST')
        {
            try
            {
                $jobs->post = Security::xss_clean($_POST['post']);
                $jobs->text = Security::xss_clean($_POST['text']);
                $jobs->note = Security::xss_clean($_POST['note']);
                $jobs->date = date('Y-m-d H:i:s');
                $jobs->count = (int) $_POST['count'];
                $jobs->published = isset($_POST['published'])?1:0;
                $jobs->save();

                $this->redirect('manage/jobs/view/'.$jobs->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $jobs);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            ORM::factory('Jobs',$id)->delete();
            $this->redirect('manage/jobs');
        }
        else
        {
            $jobs = ORM::factory('Jobs', $id);
            if ($jobs->loaded())
            {
                $this->set('record', $jobs)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/jobs'));
            }
            else
            {
                $this->redirect('manage/jobs');
            }
        }
    }

    public function action_head()
    {
        $jobs = ORM::factory('Jobs')->where('published', '=', -1)->find();
        $this->set('jobs', $jobs);
        $job_pub = -1;
        $this->set('job_pub', $job_pub);
    }

    public function action_headedit()
    {
        $id = (int)$this->request->param('id', 0);
        $jobs = ORM::factory('Jobs', $id)->where('published', '=', '-1');
        $errors = 0;
        if (!$jobs->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($this->request->method() == 'POST')
        {
            try
            {
                $jobs->post = Security::xss_clean($_POST['post']);
                $jobs->text = Security::xss_clean($_POST['text']);
                $jobs->note = Security::xss_clean($_POST['note']);
                $jobs->save();

                $this->redirect('manage/jobs/head');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $jobs);
    }

}
