<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Searchadd extends Controller_Manage_Core
{
    public function action_add()
{
    $search = $this->request->param('string', "");
$id = $this->request->param('material_id', 0);
$id_project = $this->request->param('project_id',0);
$type = $this->request->param('type',0);
$article = ORM::factory('Material_Project');

$article->material_id = $id;
$article->project_id = $id_project;
$article->type = $type;


$article->save();
    Message::success(i18n::get('Материал успешно добавлен в спец.проект!'));
    $this->redirect('manage/search/'.$id_project.'/all/'.$search);



}

    public function action_delete()
    {
        $search = $this->request->param('string', "");
        $id = $this->request->param('material_id', 0);
        $id_project = $this->request->param('project_id',0);
        $type = $this->request->param('type',0);


        $article = ORM::factory('Material_Project')->where('material_id', '=', $id)->where('project_id','=', $id_project)->where('type','=', $type)->find();

        $id = $article->id;
        $article = ORM::factory('Material_Project',$id);

        $article->delete();
        Message::success('Удалено');
        $this->redirect('manage/search/'.$id_project.'/all/'.$search);
        /*if (!$article->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$article->material_id);
            $loger->logThis($article);
            $article->delete();



            Message::success('Удалено');
            $this->redirect('manage/project/'.$id_project );
        }
        else
        {
            $this->set('record', $article)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/project/'.$id_project));
        }

*/


    }

}
