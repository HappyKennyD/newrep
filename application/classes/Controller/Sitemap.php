<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Sitemap extends Controller_Core
{
	public function before() {
 
        parent::before();
 
        $browser = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($browser,'Wget')!== 0)
        {
              throw new HTTP_Exception_404;
        }
    }
	
	public function action_build()
        {
			SitemapGen::build();
		}
}