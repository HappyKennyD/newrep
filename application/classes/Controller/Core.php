<?php
class Controller_Core extends Controller_Kotwig
{
//    protected $language = 'kz';

    /** @var Model_Auth_User */
    protected $user = false;
    /**
     * @var Model_Metadata
     */
    protected $metadata;

    protected $breadcumbs = array();

    protected $device;

//    protected function detect_language()
//    {
//        $this->language = mb_strtolower((string)$this->request->param('language', false));
//        if (!$this->language) {
//            $rr = Request::initial()->uri();
//            $rr = trim($rr, '/');
//            $rr = explode('/', $rr);
//            if (in_array($rr[0], Application::instance()->get('language.list'))) {
//                array_shift($rr);
//            }
//            $rr = Application::instance()->get('language.default') . '/' . implode('/', $rr);
//            $this->redirect($rr,301);
//        }
//        I18n::lang($this->language);
//        URL::$language = $this->language;
//        return $this->language;
//    }

    protected function detect_device()
    {
        if (isset($_COOKIE['noapp'])){
            return FALSE;
        }

        if(strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')){
            return 'ios';
        } elseif (strstr($_SERVER['HTTP_USER_AGENT'],'Android')){
            return 'android';
        } else {
            return FALSE;
        }
    }

    public function before()
    {
        parent::before();
        // detecting language, setting it
//        $this->detect_language();
//        $this->set('_language', $this->language);
        $this->set('device', $this->detect_device());
        //var_dump($_COOKIE);

        // creating and attaching page metadata
        $this->metadata = new Model_Metadata();
        $this->metadata->title(__(Application::instance()->get('title')), false);
        $this->set('_metadata', $this->metadata);
        $this->set('_token', Security::token());
        // Handles return urls, cropping language out of it (will be appended by url.site at redirect time)
//        $rr = Request::initial()->uri();
//        $rr = trim($rr, '/');
//        $rr = explode('/', $rr);
//        if (in_array($rr[0], Application::instance()->get('language.list'))) {
//            array_shift($rr);
//        }
//        $rr = implode('/', $rr);
//        $this->set('_return', $rr . Url::query());

        View::bind_global('_breadcumbs', $this->breadcumbs);

        // detecting if user is logged in
        if (method_exists(Auth::instance(), 'auto_login')) {
            Auth::instance()->auto_login();
        }
        //
        if (Auth::instance()->logged_in())
        {
            $id = Auth::instance()->get_user()->id;
            $user = ORM::factory('user',$id);
            $input = $user->has('roles',ORM::factory('role', array('name' => 'admin')));
            $this->set("admin_mode",$input);
        }else{
            $this->set("admin_mode",false);
        }
        //
        $this->user = Auth::instance()->get_user();
        $this->set('_user', $this->user);
        if (Auth::instance()->logged_in() && $this->user->profile->is_visited != 1 && (strtolower($this->request->controller())) == 'profile') {
            $this->redirect('profile/information',301);
        }
        $sliders = ORM::factory('Slider')->where('is_active', '=', 1)->and_where('type', '=', 'slider')->order_by('order', 'asc')->find_all();
        $this->set('sliders', $sliders);
        $banner = ORM::factory('Slider')->where('is_active', '=', 1)->and_where('type', '=', 'banner')->find_all();
        $this->set('menu_banner', $banner);
        $menu = ORM::factory('Page')->where('key', '=', 'menu')->find();
        //Для SEO (сортировка)
        if ($this->request->controller()=='Auth' or $this->request->controller()=='Search' or $this->request->controller()=='Profile')
        {
            $this->set('sort',1);
        }
        //end SEO
        $page_roots = ORM::factory('Page', $menu->id)->children();
        $page_main = array();
        $children_pages = array();
        $children_pages_last = array();
        $children_pages_last_last = array();
        foreach ($page_roots as $p) {
            $page_main[$p->key] = array('id' => $p->id, 'name' => $p->name, 'description' => $p->description);
            $children_pages[$p->key] = $p->children(); //ORM::factory('Page')->where('parent_id','=',$p->id)->find_all();//только первый уровень детей
            //второй уровень детей.
            foreach ($children_pages[$p->key] as $ch_p) {
                if ($ch_p->id == 232 OR $ch_p->id == 159)
                {
                    continue;
                }
                $children_pages_last[$ch_p->id] = $ch_p->children();
                //третий уровень детей
                foreach ($children_pages_last[$ch_p->id] as $ch_p_l) {
                    $children_pages_last_last[$ch_p_l->id] = $ch_p_l->children();
                }
            }
        }
        $page_halyk_kaharmany = ORM::factory('Page', 319);
        $this->set('page_halyk_kaharmany',$page_halyk_kaharmany);
        $this->set('_pages', $page_main)
            ->set('_children_pages', $children_pages)
            ->set('_children_pages_last', $children_pages_last)
            ->set('_children_pages_last_last', $children_pages_last_last)
            ->set('_return_url', URL::media(Request::initial()->uri()));
        //для вывода сообщения о регистрации восстановления пароля и прочих действий
        //само сообщение выводится в модальном окне, находится в шаблоне footer

        if (Message::get()) {
            $this->set('basic_message', Message::display('/message/basic'));
        }

        $nofollowlink=url::media(Request::initial()->uri());
        $controller=strtolower(Request::initial()->controller());
        $action=strtolower(Request::initial()->action());
        if ($controller=='books' and $action=='index'){
            $params=explode('books/',$_SERVER['REQUEST_URI']);
            if (isset($params[1]) and isset($params[0])){
                $params=explode('?',$params[1]);
                $razdel=$params[0];
            }
            else{
                $razdel='';
            }

            if (isset($params[1])){
                $params=explode('=',$params[1]);
                $idbook=$params[1];
            }
            else{
                $idbook='';
            }
            $wherestring='books_'.$razdel.'_'.$idbook;
            $page = ORM::factory('Page')->where('key','=',$wherestring)->find();
            $linkid=$page->id;
            $nofollowlink=URL::site('contents/list/'.$linkid);
        }

        if ($controller=='biography' and $action=='index'){
            $params=explode('biography/',$_SERVER['REQUEST_URI']);
            if (isset($params[1])){
                $idbiography=$params[1];
            }
            else
            {
                $idbiography='';
            }
            $wherestring='biography'.'_'.$idbiography;
            $page = ORM::factory('Page')->where('key','LIKE',$wherestring.'%')->find();
            $linkid=$page->id;
            $nofollowlink=URL::site('contents/list/'.$linkid);
        }

        if ($controller=='organization' and $action=='show'){
            $params=explode('organization/show/',$_SERVER['REQUEST_URI']);
            if (isset($params[1])){
                $idorganization=$params[1];
            }
            else{
                $idorganization='';
            }
            $wherestring='organization'.'_'.$idorganization;
            $page = ORM::factory('Page')->where('key','LIKE',$wherestring.'%')->find();
            $linkid=$page->id;
            $nofollowlink=URL::site('contents/list/'.$linkid);
        }

        if ($controller=='expert' and $action=='index'){
            $nofollowlink=URL::site('contents/list/301');
        }//в базе дублируется запись 4 и 301

        if ($controller=='publications' and $action=='index'){
            $nofollowlink=URL::site('contents/list/231');
        }

        if ($controller=='debate' and $action=='index'){
            $nofollowlink=URL::site('contents/list/335');
        }

        if ($controller=='debate' and $action=='index'){
            $nofollowlink=URL::site('contents/list/335');
        }

        $this->set('nofollowlink',$nofollowlink);  //потому-что SEO.
    }
    public function add_cumb($title, $url)
    {
        $this->metadata->title($title);
        $this->breadcumbs[] = array('title' => $title, 'url' => $url);
        return $this;
    }


}
