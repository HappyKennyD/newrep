<?php defined('SYSPATH') OR die('No direct access allowed.');

class SitemapGen extends Kohana_Sitemap
{
	public static function build()
	{
	//урл корня
	$site = 'http://e-history.kz';
	//объявляем массив языков
	$langs = array('kz','ru','en');
	// Создаем экземпляр класса Sitemap 
	$sitemap = new Sitemap;

	// Добавляем URL.
	$url = new Sitemap_URL;

    // Объявляем приоритет для второстепенных страниц
    $priority = "0.7";

	//Добавляем необходимые урлы к нашей карте сайта
	//сначала главные страницы
	$url->set_loc($site."/kz")
					->set_priority(1);
	$sitemap->add($url);
	$url->set_loc($site."/ru")
					->set_priority(1);
	$sitemap->add($url);
	$url->set_loc($site."/en")
					->set_priority(1);
	$sitemap->add($url);
	//директории
	//ссылки вида http://$site/{{lang}}/contents/list/{{id}}
	$results_list = DB::select('id')->from('pages')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			$url->set_loc($site."/".$lang."/contents/list/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
		}
	}
	//контент
	//ссылки вида http://$site/{{lang}}/contents/view/{{id}}
	$results_list = DB::select('id','title_kz','title_ru','title_en')->from('pages_contents')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
            //если есть, добавляем урл
			if ($lists['title_'.$lang]!=='')
            {
			$url->set_loc($site."/".$lang."/contents/view/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
        }
	}
	//страница публикации
	$url->set_loc($site.'/kz/publications')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/publications')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/publications')
					->set_priority($priority);
	$sitemap->add($url);
	//список публикаций
	//ссылки вида http://$site/{{lang}}/publications/view/{{id}}
	$results_list = DB::select('id','title_kz','title_ru','title_en')->from('publications')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
            //если есть, добавляем урл
			if ($lists['title_'.$lang]!=='')
            {
			$url->set_loc($site."/".$lang."/publications/view/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
        }
	}
	//персоналии главная
	$url->set_loc($site.'/kz/biography')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/biography')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/biography')
					->set_priority($priority);
	$sitemap->add($url);
	//персоналии
	//ссылки вида $site/{{lang}}/biography/view/{{id}}
	$results_list = DB::select('id','name_kz','name_ru','name_en')->from('biography')->where('published','=',1)->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
            //если есть, добавляем урл
			if ($lists['name_'.$lang]!='')
            {
			$url->set_loc($site."/".$lang."/biography/view/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
		}
	}
	//экспертное мнение - главная
	$url->set_loc($site.'/kz/expert')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/expert')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/expert')
					->set_priority($priority);
	$sitemap->add($url);
	//'экспертное мнение
	//ссылки вида $site/{{lang}}/expert/view/{{id}}
	$results_list = DB::select('id','title_kz','title_ru','title_en')->from('expert_opinions')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
            //если есть, добавляем урл
			if ($lists['title_'.$lang]!=='')
            {
			$url->set_loc($site."/".$lang."/expert/view/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
		}
	}
	//книги главная
	$url->set_loc($site.'/kz/books')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/books')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/books')
					->set_priority($priority);
	$sitemap->add($url);
	//историческое образование
	$url->set_loc($site.'/kz/books/education')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/books/education')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/books/education')
					->set_priority($priority);
	$sitemap->add($url);
	//библиотека главная
	$url->set_loc($site.'/kz/books/library')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/books/library')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/books/library')
					->set_priority($priority);
	$sitemap->add($url);
	//книги
	//ссылки вида $site/{{lang}}/books/library/{{view|read}}/{{id}}
	//здесь нужно смотреть не по title а по переводу. если в поле show_$lang 1, то страница есть
		
	//$results_list = DB::select('id','show_kz','show_ru','show_en')->from('books')->execute();
	/*
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
            //если есть, добавляем урл
			if ($lists['show_'.$lang]== 1)
            {
			$url->set_loc($site."/".$lang."/books/library/view/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
			$url->set_loc($site."/".$lang."/books/library/read/".$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
		}
	}*/
	//организации
	//ссылки вида $site/{{lang}}/organization/show/{{id}}
	//также нужно проверить, есть ли страница->смотрим в таблицу pages
	$results_list = DB::select('organizations.page_id','organizations.title_kz','organizations.title_ru','organizations.title_en','pages.id')->from('organizations')->join('pages')->on('organizations.page_id','=','pages.id')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            //из-за того, что не все переводы существуют, проверяем на наличие title
			//если есть, добавляем урл
			if ($lists['title_'.$lang]!= '')
            {
			$url->set_loc($site."/".$lang."/organization/show/".$lists['page_id'])
				->set_priority($priority);
			$sitemap->add($url);
			}
		}
	}
	//дебаты главная
	$url->set_loc($site.'/kz/debate')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/debate')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/debate')
					->set_priority($priority);
	$sitemap->add($url);
	//дебаты
	$results_list = DB::select('id')->from('debates')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
                $url->set_loc($site."/".$lang."/debate/view/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
        }
	}
	//брифинги главная
	$url->set_loc($site.'/kz/briefings')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/briefings')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/briefings')
					->set_priority($priority);
	$sitemap->add($url);
	//брифинги
	$results_list = DB::select('id')->from('briefings')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
                $url->set_loc($site."/".$lang."/briefings/view/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
        }
	}
	//цифровые образовательные ресурсы главная
	$url->set_loc($site.'/kz/scorm')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/scorm')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/scorm')
					->set_priority($priority);
	$sitemap->add($url);
	//цифровые образовательные ресурсы
	$results_list = DB::select('id','published')->from('educations')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			if ($lists['published']==1)
			{
                $url->set_loc($site."/".$lang."/scorm/course/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			}
		}
	}
	//ЕНТ главная
	$url->set_loc($site.'/kz/ent')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/ent')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/ent')
					->set_priority($priority);
	$sitemap->add($url);
	//ЕНТ 
	$results_list = DB::select('id','published')->from('ent')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			if ($lists['published']==1)
			{
                $url->set_loc($site."/".$lang."/ent/test/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			}
		}
	}
	//фотогалерея главные
	$url->set_loc($site.'/kz/photosets')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/photosets')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/photosets')
					->set_priority($priority);
	$sitemap->add($url);
	//категории фотогалереи
	$results_list = DB::select('id')->from('photosets_categories')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			    $url->set_loc($site."/".$lang."/photosets/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
		}
	}
	//фотогалерея
	$results_list = DB::select('id','published')->from('photosets')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			if ($lists['published']==1)
			{
                $url->set_loc($site."/".$lang."/photosets/view/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			}
		}
	}
	//видео главные
	$url->set_loc($site.'/kz/video')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/video')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/video')
					->set_priority($priority);
	$sitemap->add($url);
	//видео
	$results_list = DB::select('id','title')->from('video')->execute();
	
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			if ($lists['title']!='')
			{
                $url->set_loc($site."/".$lang."/video/view/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			}
		}
	}
	//video категории
	$results_list = DB::select('id')->from('categories')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			    $url->set_loc($site."/".$lang."/video/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			
		}
	}
	//аудио главные
	$url->set_loc($site.'/kz/audio')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/audio')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/audio')
					->set_priority($priority);
	$sitemap->add($url);
	//нужно посчитать страницы с аудио отдельно по языкам
	foreach ($langs as $lang)
	{
		$result = DB::select('id')->from('audio')->where('show_'.$lang,'=','1')->and_where('published','=','1')->and_where('storage_id','>','0')->execute();
		$count_of_el = (ceil((count($result)))/12)+1;//12 - число записей на странице/ +1 - т.к. будем начинать со второй страницы => если не добавим, одну потеряем
		for ($i=2;$i<=$count_of_el;$i++)
		{
			$url->set_loc($site.'/'.$lang.'/audio/page-'.$i)
					->set_priority($priority);
			$sitemap->add($url);
		}
	}
	//инфографика главные
	$url->set_loc($site.'/kz/infographs')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/infographs')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/infographs')
					->set_priority($priority);
	$sitemap->add($url);
	//инфографика
	foreach ($langs as $lang)
	{
		$result = DB::select('id','title_'.$lang)->from('infographs')->where('published','=','1')->execute();
		foreach ($result as $lists)
		{
		if ($lists['title_'.$lang]!='')
            {
            $url->set_loc($site.'/'.$lang.'/infographs/view/'.$lists['id'])
				->set_priority($priority);
			$sitemap->add($url);
            }
		}
		
	}
	//благодарности главные
	$url->set_loc($site.'/kz/thanks')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/thanks')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/thanks')
					->set_priority($priority);
	$sitemap->add($url);
	//полезные ссылки главные
	$url->set_loc($site.'/kz/links')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/links')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/links')
					->set_priority($priority);
	$sitemap->add($url);
	
	//календарь главные
	$url->set_loc($site.'/kz/calendar')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/ru/calendar')
					->set_priority($priority);
	$sitemap->add($url);
	$url->set_loc($site.'/en/calendar')
					->set_priority($priority);
	$sitemap->add($url);
	//календарь
	$results_list = DB::select('id','day','month')->from('calendar')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			    $month = (int)$lists['month'];
				$day = (int)$lists['day'];
				if ($month<10)
					$month = "0".$month;
				if ($day<10)
					$day = "0".$day;
				$url->set_loc($site."/".$lang."/calendar/event/".$month."/".$day)
                    ->set_priority($priority);
                $sitemap->add($url);
				$url->set_loc($site."/".$lang."/calendar/event/".$lists['id'])
                    ->set_priority($priority);
                $sitemap->add($url);
			
		}
	}
	//книги
	$results_list = DB::select('books.id','books.type','books.published','books.show_ru','books.show_en',"books.show_kz","books.category_id")->from('books')->join('library_categories')->on('books.category_id','=','library_categories.id')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
            if (($lists['published']== 1) AND ($lists['show_'.$lang]==1)) 
            {
				if (($lists['category_id']==20) OR ($lists['category_id']==23) OR ($lists['category_id']==24) OR ($lists['category_id']==25) OR ($lists['category_id']==26) OR ($lists['category_id']==45) OR ($lists['category_id']==28) OR ($lists['category_id']==30))
				{
					$url->set_loc($site."/".$lang."/books/education/view/".$lists['id'])
						->set_priority($priority);
					$sitemap->add($url);
					if ($lists['type']!='txt')
					{
						$url->set_loc($site."/".$lang."/books/education/read/".$lists['id'])
							->set_priority($priority);
						$sitemap->add($url);
					}
				}
				elseif (($lists['category_id']==18) OR ($lists['category_id']==43))
				{
					$url->set_loc($site."/".$lang."/books/president/view/".$lists['id'])
						->set_priority($priority);
					$sitemap->add($url);
					if ($lists['type']!='txt')
					{
						$url->set_loc($site."/".$lang."/books/president/read/".$lists['id'])
							->set_priority($priority);
						$sitemap->add($url);
					}
				}
				else
				{
				$url->set_loc($site."/".$lang."/books/library/view/".$lists['id'])
					->set_priority($priority);
				$sitemap->add($url);
				if ($lists['type']!='txt')
				{
					$url->set_loc($site."/".$lang."/books/library/read/".$lists['id'])
						->set_priority($priority);
					$sitemap->add($url);
				}
				}
			}
		}
	}

	//позиции экспертов
	$results_list = DB::select('id')->from('biography_categories')->execute();
	foreach ($results_list as $lists) 
	{
		foreach ($langs as $lang)
		{
			$url->set_loc($site."/".$lang."/biography/".$lists['id'])
                ->set_priority($priority);
            $sitemap->add($url);
		}
	}
	// Генерируем xml
	$response = urldecode($sitemap->render()).'<!--  Generated - '.date('d.m.Y H:i:s').'   -->';

	//Записываем в файл
	file_put_contents('sitemap.xml', $response);
	}
}