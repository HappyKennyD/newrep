<?php
class ORM extends Kohana_ORM
{
    protected $_i18n_fields = array();
    protected $_log_fields = array();

    public function __get($column)
    {
        if (in_array($column, $this->_i18n_fields))
        {
            $column.= '_'.I18n::lang();
        }

        return parent::__get($column);
    }

    public function set($column, $value)
    {
        if (in_array($column, $this->_i18n_fields))
        {
            $column.= '_'.I18n::lang();
        }

        return parent::set($column, $value);
    }

    public function __isset($column)
    {
        if (in_array($column, $this->_i18n_fields))
        {
            $column.= '_'.I18n::lang();
        }

        return parent::__isset($column);
    }

    public static function factory($model, $id = NULL)
    {
        $model = ucfirst($model);
        return parent::factory($model, $id);
    }

    public function save(Validation $validation = null)
    {
        $event = ($this->loaded())?'edit':'create';
        $save = parent::save($validation);
        $count = 0;
        if (count($this->_log_fields) && $save)
        {
            $log = ORM::factory('Log');
            $log->event = $event;
            $log->model = get_class($this);

            if (isset(Auth::instance()->get_user()->id)){
                $log->user_id = Auth::instance()->get_user()->id;
            }
            else{
                $log->user_id = 0;
            }

            $log->content_id = $this->pk();
            $log->date = date("Y.m.d H:i:s");
            $log->language = I18n::$lang;
            if (isset($this->_log_fields['title'])){
                $log->title = (!empty($this->{$this->_log_fields['title']}))?$this->{$this->_log_fields['title']}:'Пустой заголовок';
            }else {
                $log->title = 'Без заголовка';
            }
            foreach($this->_log_fields as $field){
                $count += $log->countThis($this->{$field});
            }
            $log->count = $count;
            $log->save();
        }
        return $save;
    }

    public function delete()
    {
        $id = $this->pk();
        if (isset($this->_log_fields['title'])){
            $title = $this->{$this->_log_fields['title']};
        } else {
            $title = 'Без заголовка';
        }
        $delete = parent::delete();
        if (count($this->_log_fields) && $delete){
            $log = ORM::factory('Log');
            $log->event = 'delete';
            $log->model = get_class($this);
            $log->content_id = $id;
            $log->date = date("Y.m.d H:i:s");
            $log->title = $title;
            if (isset(Auth::instance()->get_user()->id)){
                $log->user_id = Auth::instance()->get_user()->id;
            }
            else{
                $log->user_id = 0;
            }
            $log->save();
        }
        return $delete;
    }


}