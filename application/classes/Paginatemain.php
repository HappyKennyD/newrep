<?php defined('SYSPATH') or die('No direct script access.');
class Paginatemain
{
    /**
     * @param ORM $orm
     * @return Paginate
     */
    static public function factory($orm)
    {
        return new self($orm);
    }

    protected $object;
    protected $view;
    protected $config;

    protected $view_vars = array(
        'page'  => 1,
        'key'   => 'page',
        'count' => 10,
        'link'  => ''
    );

    protected function __construct($orm)
    {
        $this->config = Kohana::$config->load('paginate');
        $this->view = $this->config->get('view', 'paginatemain');
        $this->count = $this->config->get('count', 10);
        $this->orm = $orm;

    }

    public function count_all()
    {
        $clone = clone $this->orm;
        return $clone->count_all();
    }

    public function page_count()
    {
        $count = ceil($this->count_all() / $this->count);
        if ($count <= 0) $count = 0;
        return $count;
    }

    public function set_view($view)
    {
        $this->view = $view;

        return $this;
    }

    public function get_view()
    {
        return View::factory($this->view);
    }

    public function paginate($page = null, $link = null, $count = null, $mosaic = null)
    {
        if ($page == null)
        {
            if (!isset($_GET['page']))
            {
                $page = (int)Request::initial()->param('page', 1);
            }
            else
            {
                $page = (int)Arr::get($_GET, 'page', 1);
            }
        }

        if (!empty($_GET['item_count']))
        {
            $this->count = (int)Arr::get($_GET,'item_count');
            $count = $this->count;
        }
        else
        {
            if ($count == null)
            {
                $count = $this->count;
            }
            else
            {
                $this->count = (int) $count;
            }
        }



        if ($link == null)
        {
            $link = Request::initial()->uri();
            $link = explode('/', $link);

            foreach ($link as $l)
            {
                if (mb_strpos($l,'page-')===false)
                {
                    $link_new[] = $l;
                }
            }
            $link = implode('/', $link_new);
        }

        $count  = (int)$count;
        $page   = (int)$page;
        $start  = ($page * $count) - $count;

        $max_page = $this->page_count();
        if ($page!=1 && $page>$max_page) throw new HTTP_Exception_404;

        if ($page < 1) {
            throw new HTTP_Exception_404;
        }
        else {
            $page = min($page, $max_page);
        }

        $prev = ($page == 1)?false:true;
        $next = ($page == $max_page)?false:true;
        if (get_class($this->orm)=="Massiv")
            $this->orm->limit_offset($count,$start);
        else
            $this->orm->limit($count)->offset($start);



        $this->view_vars = array(
            'page'      => $page,//текущий выбранный
            'max_page'  => $max_page,//количество всего кнопок
            'key'       => $this->config->get('key', 'page'),//для передачи индекса
            'count'     => $count,//количество отображаемых элементов
            'link'      => HTML::chars($link),//тек ссылка
            'next'      => $next,//отображается ли кнопка некст
            'prev'      => $prev,//отображается ли кнопка пред
            'mosaic'    => $mosaic
        );
        return $this;
    }

    public function render()
    {
        return $this->get_view()->set($this->view_vars)->render();
    }


}