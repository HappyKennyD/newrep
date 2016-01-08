<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Contacts extends Controller_Manage_Core {

	public function action_index()
	{
        $contacts = ORM::factory('Contact')->where('name_ru', '!=', 'main')->order_by('order','ASC')->order_by('id');
        $paginate = Paginate::factory($contacts)->paginate(NULL, NULL, 10)->render();
        $contacts = $contacts->find_all();
        $this->set('contacts', $contacts);
        $this->set('paginate', $paginate);
        $this->set('contacts_main', '1');
	}

    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id);
        if ($contact->loaded())
        {
            $this->set('contact', $contact);
        }
        else
        {
            $this->redirect('manage/contacts');
        }
    }

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id);
        $errors = 0;

        if ($this->request->method() == 'POST')
        {
            try
            {
                $last = ORM::factory('Contact')->where('name_ru', '!=', 'main')->order_by('order','Desc')->find();
                $contact->name = Security::xss_clean($_POST['name']);
                $contact->order = ($last->order + 1);
                $contact->save();

                $this->redirect('manage/contacts/view/'.$contact->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $contact);
    }

    public function action_editPost()
    {
        $id = (int)$this->request->param('id', 0);
        if (isset($_GET['c_id'])) $this->set('contact_id', (int)$_GET['c_id']);

        $attach = ORM::factory('Contacts_Attachment', $id);
        $errors = 0;

        if ($this->request->method() == 'POST')
        {
            try
            {
                $attach->post = Security::xss_clean($_POST['post']);
                $attach->name = Security::xss_clean($_POST['name']);
                $attach->phone = Security::xss_clean($_POST['phone']);
                $attach->fax = Security::xss_clean($_POST['fax']);
                $attach->cabinet = Security::xss_clean($_POST['cabinet']);
                if (isset($_POST['contact_id'])) $attach->contact_id = (int)$_POST['contact_id'];
                $attach->save();

                $this->redirect('manage/contacts/view/'.$attach->contact_id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $attach);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $attach = $contact->attach->find_all();
            foreach ($attach as $att)
            {
                ORM::factory('Contacts_Attachment', $att->id)->delete();
            }
            $contact->delete();
            $this->redirect('manage/contacts');
        }
        else
        {

            if ($contact->loaded())
            {
                $this->set('record', $contact)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/contacts'));
            }
            else
            {
                $this->redirect('manage/contacts');
            }
        }
    }

    public function action_deletePost()
    {
        $id = (int) $this->request->param('id', 0);
        $attach = ORM::factory('Contacts_Attachment', $id);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $contact_id = $attach->contact_id;
            $attach->delete();
            $this->redirect('manage/contacts/view/'.$contact_id);
        }
        else
        {

            if ($attach->loaded())
            {
                $this->set('record', $attach)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/contacts'));
            }
            else
            {
                $this->redirect('manage/contacts');
            }
        }
    }

    public function action_head()
    {
        $contact = ORM::factory('Contact')->where('name_ru', '=', 'main')->find();
        $attach = $contact->attach->find();
        $this->set('attach', $attach);
        $this->set('contacts_main', '-1');
    }

    public function action_headedit()
    {
        $id = (int)$this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id)->where('name_ru', '=', 'main');
        $errors = 0;
        if (!$contact->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $attach = $contact->attach->find();
        if ($this->request->method() == 'POST')
        {
            try
            {
                $attach->post = Security::xss_clean($_POST['post']);
                $attach->phone = Security::xss_clean($_POST['phone']);
                $attach->fax = Security::xss_clean($_POST['fax']);
                $attach->cabinet = Security::xss_clean($_POST['cabinet']);
                $attach->name = Security::xss_clean($_POST['name']);
                $attach->phone_smi = Security::xss_clean($_POST['phone_smi']);
                $attach->email_smi = Security::xss_clean($_POST['email_smi']);

                $attach->save();

                $this->redirect('manage/contacts/head');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }
        $this->set('item', $attach);
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id);
        if (!$contact->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $contact->order = ($contact->order+1);
        $contact->save();

        $this->redirect('manage/contacts');
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $contact = ORM::factory('Contact', $id);
        if (!$contact->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($contact->order > 0)
        {
            $contact->order = ($contact->order-1);
            $contact->save();
        }

        $this->redirect('manage/contacts');
    }
}
