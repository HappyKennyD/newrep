<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Core extends Controller{

    protected $language = 'kz';

    protected $data = array();

    protected $api;

    protected $post = array();

    protected $limit;

    protected $offset;

    protected $entryType;

    protected $entryId;

    protected $text;

    protected $searchText;

    protected $id;

    protected $auth_token='';

    protected function detect_language()
    {
        $this->language = mb_strtolower((string)$this->request->param('language', false));
        I18n::lang($this->language);
        return $this->language;
    }

    public function before()
    {
        parent::before();


        $this->detect_language();

        /* Вспомогательный класс */
        $this->api=new Api();

        $this->auth_token=$this->request->headers('tokenAuth');

        /* Обрабатываем POST со строкой json */
        $this->post=json_decode($HTTP_RAW_POST_DATA = file_get_contents('php://input'), true);

        /* Инициализация параметров limit и offset для запроса, по умолчанию limit = 10, offset = 0 */
        $this->offset = Security::xss_clean(Arr::get($this->post, 'offset', 0));
        $this->limit = Security::xss_clean(Arr::get($this->post, 'limit', 10));

        //Инициализация типа для запроса и id Для запроса
        $option = Security::xss_clean(Arr::get($this->post, 'option', array()));

        $this->entryType = strtolower(Security::xss_clean(Arr::get($option, 'entryType', '')));
        $this->entryId = Security::xss_clean(Arr::get($option, 'entryId', ''));
        /* строка поиска */
        $this->searchText = Security::xss_clean(Arr::get($option, 'searchText', ''));


        /* текст коммента */
        $this->text = Security::xss_clean(Arr::get($this->post, 'text', ''));

        $this->id = (int)$this->request->param('id', 0);

        /* обновление времени жизни токена     если он существует и если его ещё надо обновлять (живой ли?) */
        if( !empty($this->auth_token) )
        {
            if ( $this->api->token_expires($this->auth_token) ){
                $token_auth = Security::xss_clean(Arr::get($this->post, 'tokenAuth', ''));
                $this->api->update_token($token_auth);
            }
        }

    }

    public function after()
    {
        $this->response->headers('Content-Type','application/json');
        parent::after();
    }
}