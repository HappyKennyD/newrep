<?php

class Controller_Manage_Exhibits extends Controller_Manage_Core {

    public function action_index(){
        $exhibits = ORM::factory('Exhibit')->find_all();
        $this->set('exhibits',$exhibits);
    }

    public function action_edit(){
        $id = (int)$this->request->param('id');
        $exhibit = ORM::factory('Exhibit',$id);
        if ($this->request->method() == Request::POST){
            try{
                $exhibit->values($_POST,array('title','description','image_storage_id','published'))->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$exhibit->title);
                $loger->logThis($exhibit);
                $this->redirect('manage/exhibits');

            }catch (ORM_Validation_Exception $e)
            {
                $this->set('error',$e->errors('error'));
            }
        }

        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('item',$exhibit)->set('uploader',$uploader);
    }

    public function action_album_edit(){
        $id = (int)$this->request->param('id');
        $exhibit_id = (int)Arr::get($_GET,'exhibit_id');
        $exhibit_album = ORM::factory('Exhibit_Album',$id);
        if ($this->request->method() == Request::POST){
            try{
                $exhibit_album->values($_POST,array('title','description','text','image_storage_id'));
                $exhibit_album->exhibit_id = $exhibit_id;
                $exhibit_album->save();
                $this->redirect('manage/exhibits');
            }
            catch(ORM_Validation_Exception $e){
                $this->set('error',$e->errors(''));
            }
        }
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);
        $this->set('item',$exhibit_album);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id');
        $exhibit = ORM::factory('Exhibit',$id);

        if (!$exhibit->loaded()){
            throw new HTTP_Exception_404;
        }

        if ($this->request->method() == Request::POST){
            if (Security::check(Arr::get($_POST,'token'))){
                DB::delete('exhibit_albums')->where('exhibit_id','=',$id)->execute();
                $exhibit->delete();
                $this->redirect('manage/exhibits');
            }
        }
        $this->set('item',$exhibit)->set('token',Security::token(true));
    }

    public function action_album_delete()
    {
        $id = (int) $this->request->param('id');
        $exhibit = ORM::factory('Exhibit_Album',$id);
        if (!$exhibit->loaded()){
            throw new HTTP_Exception_404;
        }
        if ($this->request->method() == Request::POST){
            if (Security::check(Arr::get($_POST,'token'))){
                $exhibit->delete();
                $this->redirect('manage/exhibits');
            }
        }
        $this->set('item',$exhibit)->set('token',Security::token(true));
    }
}