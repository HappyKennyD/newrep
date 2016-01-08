
<?php
class Model_Book extends ORM
{
    protected $_belongs_to = array(
        'cover' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'cover_id'
        ),
        'category' => array(
            'model'=> 'Library_Category',
            'far_key'=> 'id',
            'foreign_key'=>'category_id'
        ),
        'pdf' => array(
            'model' => 'Storage',
            'far_key' => 'id',
            'foreign_key' => 'storage_id'
        ),
    );

    protected $_has_many = array(
        'chapters' => array(
            'model'=> 'Book_Chapter',
            'foreign_key'=> 'book_id',
        ),
    );

    public function filters()
    {
        return array(
            'description' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

    public function plus_view()
    {
        $this->count_view = $this->count_view + 1;
        $this->update();
    }

    public function byViewsCount($limit)
    {
        $except_url = explode('/',$_SERVER['REQUEST_URI']);
        $except_url = $except_url[count($except_url)-1];
        $books = ORM::factory('Book')->where('show_'.I18n::$lang,'=',1)->and_where('id','<>',$except_url)->order_by('views_count', 'DESC')->limit($limit)->find_all();
        return $books;
    }

    public function category($id)
    {

        $sc = ORM::factory('Library_Category',$id);
        $cat = ORM::factory('Library_Category')->where('scope','=',$sc->scope)->find();
        if ($cat->key=='')
            $cat->key = 'library';
        return $cat->key;
    }

    public function translation($lang)
    {
    switch($lang){
        case 'ru': if ($this->show_ru == '0') {return FALSE;} break;
        case 'kz': if ($this->show_kz == '0') {return FALSE;} break;
        case 'en': if ($this->show_en == '0') {return FALSE;} break;
    }
        return TRUE;
    }
}