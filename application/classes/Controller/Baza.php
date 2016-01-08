<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Baza extends Controller_Core {

    public function action_index()
    {
        $qv = ORM::factory('qv')->order_by('id')->find_all();
        $this->set('qv', $qv);

        if ($this->request->method() == Request::POST) {
            $input1 = Security::xss_clean(Arr::get($_POST, '1', 0));
            $input2 = Security::xss_clean(Arr::get($_POST, '2', 0));
            $input3 = Security::xss_clean(Arr::get($_POST, '3', 0));
            $input4 = Security::xss_clean(Arr::get($_POST, '4', 0));
            $input5 = Security::xss_clean(Arr::get($_POST, '5', 0));
            $input6 = Security::xss_clean(Arr::get($_POST, '6', 0));
            $input7 = Security::xss_clean(Arr::get($_POST, '7', 0));
            $input8 = Security::xss_clean(Arr::get($_POST, '8', 0));
            $input9 = Security::xss_clean(Arr::get($_POST, '9', 0));
            $input10 = Security::xss_clean(Arr::get($_POST, '10', 0));
            $input11 = Security::xss_clean(Arr::get($_POST, '11', 0));
            $input12 = Security::xss_clean(Arr::get($_POST, '12', 0));
            $input13 = Security::xss_clean(Arr::get($_POST, '13', 0));
            $input14 = Security::xss_clean(Arr::get($_POST, '14', 0));
            $input15 = Security::xss_clean(Arr::get($_POST, '15', 0));
            $input16 = Security::xss_clean(Arr::get($_POST, '16', 0));
            $input17 = Security::xss_clean(Arr::get($_POST, '17', 0));
            $input18 = Security::xss_clean(Arr::get($_POST, '18', 0));
            $input19 = Security::xss_clean(Arr::get($_POST, '19', 0));
            $input20 = Security::xss_clean(Arr::get($_POST, '20', 0));




        }
    }

}
