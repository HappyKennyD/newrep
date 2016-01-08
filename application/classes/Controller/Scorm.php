<?php
class Controller_Scorm extends Controller_Core
{
    public function action_index()
    {
        $courses = ORM::factory('Education')->where('language', '=', $this->language)->where('published','=', 1)->order_by('number');
        $paginate = Paginate::factory($courses)->paginate(NULL, NULL, 30)->render();
        $courses = $courses->find_all();

        $this->set('paginate', $paginate);
        $this->set('courses', $courses);

        $this->add_cumb('Цифровые образовательные ресурсы', false);

        /* метатэг description */
        $this->metadata->description( i18n::get('Цифровые образовательные ресурсы, интерактивные материалы, интерактивные тесты') );
    }
    
    public function action_course()
    {
        $id = (int)$this->request->param('id', 0);

        $material = ORM::factory('Education', $id);

        if (!$material->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $this->set('materials', $material->materials->find_all());
        $this->set('material', $material);

        $this->add_cumb('Цифровые образовательные ресурсы', 'scorm')->add_cumb($material->title, false);
    }

    public function action_resource()
    {
        $id = (int)$this->request->param('id', 0);
        $material =  ORM::factory('Education_Material', $id);

        if (!$material->loaded())
        {
            throw new HTTP_Exception_404;
        }

        if ($material->type == 'text')
        {
            //$content = simp
        }
        else
        {

        }
    }


}