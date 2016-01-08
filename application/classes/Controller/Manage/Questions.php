<?php

/*
 * Реализация управления вопросами пользователей
 */
class Controller_Manage_Questions extends Controller_Manage_Core
{
    protected $title = 'Вопрос/Ответ';

    /*
    * Список вопросов
    * @id = 1 - одобренные без ответа, 2 - одобренные с ответом, 3 - отклоненные, 0 - новые
    */
    public function action_list()
    {
        $id = (int) $this->request->param('id', 0);
        if ( $id < 0 OR $id > 3 )
        {
            throw new HTTP_Exception_404;
        }

        $questions = ORM::factory('Question')->where('status', '=', $id)->order_by('date_question','desc');
        $paginate = Paginate::factory($questions)->paginate(NULL, NULL, 10)->render();
        $questions = $questions->find_all();
        $this->set('questions', $questions)
             ->set('paginate', $paginate)
             ->set('status', $id);
    }

    /*
     * Редактирование вопроса
     */
    public function action_edit_question()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $post = $this->request->post() )
        {
            try
            {
                $question->text_question = Security::xss_clean(Arr::get($post,'text_question',''));
                $question->save();
                Message::success('Вопрос отредактирован');
                $this->redirect('manage/questions/list/'.$status = ($question->status)?$question->status:'');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('question', $question)
            ->set('status', $question->status)
            ->set('cancel_url', URL::media('manage/questions/list/'.$status = ($question->status)?$question->status:''));
    }

    /*
     * Одобрение вопроса
     */
    public function action_success()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $old_status = $question->status;
        if ( $question->date_answer == NULL )
        {
            $question->status = 1;
            $question->save();
        }
        else
        {
            $question->status = 2;
            $question->save();
        }
        Message::success('Вопрос одобрен');
        $this->redirect('manage/questions/list/'.$status = ($old_status)?$old_status:'');
    }

    /*
     * Отклонение вопроса
     */
    public function action_reject()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $old_status = $question->status;
        $question->status = 3;
        $question->save();
        Message::success('Вопрос отклонен');
        $this->redirect('manage/questions/list/'.$status = ($old_status)?$old_status:'');
    }

    /*
    * Редактирование ответа
    */
    public function action_edit_answer()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $post = $this->request->post() )
        {
            try
            {
                $old_status = $question->status;
                $question->status = 2;
                $question->date_answer = date('Y-m-d H:i:s',strtotime(Arr::get($post,'date_answer',date('Y-m-d H:i:s'))));
                $question->text_answer = Security::xss_clean(Arr::get($post,'text_answer',''));
                $question->author = Security::xss_clean(Arr::get($post,'author',''));
                $extra_rules = Validation::factory($post)
                    ->rule('text_answer','not_empty');
                $question->save($extra_rules);
                Message::success('Ответ на вопрос сохранен');
                $this->redirect('manage/questions/list/'.$status = ($old_status)?$old_status:'');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }

        }
        $this->set('question', $question)
            ->set('status', $question->status)
            ->set('cancel_url', URL::media('manage/questions/list/'.$status = ($question->status)?$question->status:''));
    }

    /*
     * Просмотр вопроса
     */
    public function action_show()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('question',$question)
            ->set('status', $question->status)
            ->set('cancel_url', URL::media('manage/questions/list/'.$status = ($question->status)?$question->status:''));
    }

    /*
     * Удаление вопроса
     */
    public function action_delete_question()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if ( $this->request->post() AND Security::token() === $token)
        {
            $old_status = $question->status;
            $question->delete();
            Message::success('Вопрос удален');
            $this->redirect('manage/questions/list/'.$status = ($old_status)?$old_status:'');
        }
        $this->template->set_filename('manage/questions/delete');
        $this->template->set('question',$question)
            ->set('status', $question->status)
            ->set('title','вопрос')
            ->set('token', Security::token(true))
            ->set('cancel_url', URL::media('manage/questions/list/'.$status = ($question->status)?$question->status:''));
    }

    /*
    * Удаление ответа на вопрос
    */
    public function action_delete_answer()
    {
        $id = (int) $this->request->param('id', 0);
        $question = ORM::factory('Question', $id);
        if ( !$question->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if ( $this->request->post() AND Security::token() === $token)
        {
            $question->values(array('text_answer' => '', 'author' => '', 'date_answer' => NULL, 'status' => 1))->save();
            Message::success('Ответ на вопрос удален');
            $this->redirect('manage/questions/list/'.$status = ($question->status)?$question->status:'');
        }
        $this->template->set_filename('manage/questions/delete');
        $this->template->set('question',$question)
            ->set('status', $question->status)
            ->set('title','ответ на вопрос')
            ->set('token', Security::token(true))
            ->set('cancel_url', URL::media('manage/questions/list/'.$status = ($question->status)?$question->status:''));
    }
}