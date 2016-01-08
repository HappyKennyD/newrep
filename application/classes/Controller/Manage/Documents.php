<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Documents extends Controller_Manage_Core {

	public function action_index()
	{
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/documents/search/'.$search);
        }

        $documents = ORM::factory('Document');
        $paginate = Paginate::factory($documents)->paginate(NULL, NULL, 10)->render();
        $documents = $documents->find_all();
        $this->set('documents', $documents);
        $this->set('paginate', $paginate);
	}

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(name_ru, name_kz, name_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $documents = ORM::factory('Document')->where($query_m, '', $query_a)->find_all();
        $this->set('documents', $documents);

        $totalcount = sizeof($documents);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $document = ORM::factory('Document', $id);
        $errors = 0;

        if ($this->request->method() == 'POST')
        {
            try
            {
                $document->name = Security::xss_clean($_POST['name']);
                $document->save();

                $this->redirect('manage/documents');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $document);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            ORM::factory('Document',$id)->delete();
            $this->redirect('manage/documents');
        }
        else
        {
            $document = ORM::factory('Document', $id);
            if ($document->loaded())
            {
                $this->set('record', $document)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/document'));
            }
            else
            {
                $this->redirect('manage/document');
            }
        }
    }

}
