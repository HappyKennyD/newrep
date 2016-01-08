<?php
class Controller_Ajax extends Controller
{

    public function action_noapp()
    {
        setcookie('noapp', 1, time() + 63244800,'/');
        return TRUE;
    }

    public function action_questedit()
    {
        if (isset($_POST['id']) && !empty($_POST['id'])){
            $subthemes = array();
            $my_html = '';
            $id = intval($_POST['id']);
            $result = ORM::factory('Subthemes')->where('id_themes', '=', $id)->find_all();
//            $this->set('query',$query);
            foreach ($result as $r) {
                $my_array = array("title"=>$r->title,"id"=>$r->id);
                array_push($subthemes,$my_array);
                $my_html .= '<option value="'.$r->id.'" > '.$r->title.' </option>';

            }
//            $data['id'] = $id;
//            $data['result'] = $subthemes;
            $data = $subthemes;
            $data['html'] = $my_html;
        }
        else{
//            die($_POST['id']);
            $data['result'] = 'ID NOT FOUND';
        }

        $doc = $this->response->body(json_encode($data));
//        $doc2 = $this->response->body(utf8_decode($data['result']));
//        $this->response->body(utf8_decode($doc));
    }

    public function action_points()
    {
        $lang = $this->request->param('language', 'ru');
        I18n::lang($lang);

        header('Content-type: application/json');
        $region = Security::xss_clean(Arr::get($_POST, 'region', 0));
        $district = ORM::factory('District')->where('key', '=', $region)->find();
        if (!$district->loaded()) {
            die();
        }
        $points = ORM::factory('Point')->where('district_id', '=', $district->id)->and_where('published', '=', 1)->find_all();
        $data = array();
        foreach ($points as $point) {
            $img = '';
            if (isset($point->picture->file_path)) {
                $img = '<img src=\'' . URL::media('/images/w190/' . $point->picture->file_path) . '\' /><br />';
            }
            $data[] = array('id' => $point->id, 'name' => $point->name, 'desc' => $point->desc,
                'x' => $point->x, 'y' => $point->y, 'img' => $img, 'marker' => $point->marker);
        }
        $this->response->body(json_encode($data));
    }

    public function action_removePresskit()
    {
        $id = (int)Arr::get($_POST, 'presskit_id');
        $ep = ORM::factory('Event_Presskit', $id);
        if ($ep->loaded())
        {
            $ep->delete();
            $this->response->body(1);
        }
        else
        {
            $this->response->body(0);
        }
    }

    public function action_ent()
    {
        $answers = Security::xss_clean(Arr::get($_POST, 'answers', ''));
        $answers = UTF8::substr($answers, 0, UTF8::strlen($answers) - 1);
        $list = explode(',', $answers);
        $total = 0;
        $right = 0;
        $points = array();
        foreach ($list as $item) {
            $total++;
            $e = explode('.', $item);
            $quest = ORM::factory('Ent_Quest', (int)$e[0]);
            if ($quest->loaded()) {
                $variant = ORM::factory('Quest_Variant')
                    ->where('quest_id', '=', $quest->id)
                    ->and_where('id', '=', (int)$e[1])
                    ->and_where('right', '=', '1')
                    ->find();
                if ($variant->loaded()) {
                    $right++;
                    $points[] = array('quest' => $quest->id, 'right' => 1);
                } else {
                    $points[] = array('quest' => $quest->id, 'right' => 0);
                }
            }
        }
        $data = array('total' => $total, 'right' => $right, 'points' => $points);
        $this->response->body(json_encode($data));
    }

    public function action_filepath()
    {
        $id = (int)Arr::get($_POST, 'storage_id', 0);
        $storage = ORM::factory('Storage', $id);
        if ($storage->loaded()) {
            $this->response->body(URL::media($storage->file_path));
        } else {
            throw new HTTP_Exception_404('file Not Found');
        }
    }

    public function action_filename()
    {
        $id = (int)Arr::get($_POST, 'storage_id');
	   $storage = ORM::factory('Storage', $id);
        if ($storage->loaded()) {
            $text = HTML::chars($storage->name);
            $this->response->body($text);
        } else {
            throw new HTTP_Exception_404('Not Found');
        }
    }

    public function action_filesize()
    {
        $id = (int)Arr::get($_POST, 'storage_id');
        $storage = ORM::factory('Storage', $id);
        if ($storage->loaded()) {
            $file = round(filesize($storage->file_path) / (1024*1024),2);

            $lang = (int)Arr::get($_POST, 'lang');
            switch ($lang) {
                case 'ru':
                case 'en': break;
                default: $lang='kz';
            }

            $this->response->body($file." ".i18n::get('MB',$lang));
        } else {
            throw new HTTP_Exception_404('Not Found');
        }
    }

    public function action_era()
    {
        header('Content-type: application/json');
        $id = Arr::get($_POST, 'id');
        $language = Arr::get($_POST, 'language','ru');
        $list_era = ORM::factory('Chronology')->where('lvl', '=', 2)->order_by('lft', 'asc')->find_all();
	$old = Arr::get($_POST, 'old', 0);
        $mas_list_period = array();
        $mas_list_event = array();
        $list_list = $list_era[$id]->children;
        $finish = false;
        if ($old >= 0 AND isset($list_list[$old])) {
            $list_period = $list_list[$old]->children(FALSE, 'ASC', 3);
            foreach ($list_period as $item) {
                $param = explode(";", $item->value);
                $left = (int)(isset($param[0])) ? $param[0] : 0;
                $width = (int)(isset($param[1])) ? $param[1] : 0;
                switch ($language) {
                    case 'ru':
                        $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_ru, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_ru . ' - ' . $item->finish_ru . ')');
                        break;
                    case 'kz':
                        $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_kz, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_kz . ' - ' . $item->finish_kz . ')');
                        break;
                    case 'en':
                        $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_en, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_en . ' - ' . $item->finish_en . ')');
                        break;
                }
            }
            /*
	    $list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->and_where('description_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
            */
	    $list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_alL();
	    foreach ($list_event as $item) {
                if (isset($item->picture->file_path)) {
                    $picture = '<img src="' . URL::media('/images/w276-cct-si/' . $item->picture->file_path, TRUE) . '" alt="" />';
                } else {
                    switch ($language) {
                        case 'ru':
                            $picture = $item->description_ru;
                            break;
                        case 'kz':
                            $picture = $item->description_kz;
                            break;
                        case 'en':
                            $picture = $item->description_en;
                            break;
                    }
                }
                switch ($language) {
                    case 'ru':
                        $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_ru, 'date' => $item->date_ru, 'picture' => $picture);
                        break;
                    case 'kz':
                        $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_kz, 'date' => $item->date_kz, 'picture' => $picture);
                        break;
                    case 'en':
                        $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_en, 'date' => $item->date_en, 'picture' => $picture);
                        break;
                }
            }
        } elseif ($old > 0) {
            if (isset($list_era[$id + 1])) {
                $id++;
                $old = 0;
                $list_list = $list_era[$id]->children;
                if (isset($list_list[$old])) {
                    $list_period = $list_list[$old]->children(FALSE, 'ASC', 3);
                    foreach ($list_period as $item) {
                        $param = explode(";", $item->value);
                        $left = (int)(isset($param[0])) ? $param[0] : 0;
                        $width = (int)(isset($param[1])) ? $param[1] : 0;
                        switch ($language) {
                            case 'ru':
                                $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_ru, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_ru . ' - ' . $item->finish_ru . ')');
                                break;
                            case 'kz':
                                $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_kz, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_kz . ' - ' . $item->finish_kz . ')');
                                break;
                            case 'en':
                                $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_en, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_en . ' - ' . $item->finish_en . ')');
                                break;
                        }
                    }
                    /*
	 	    $list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->and_where('description_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
		    */
		    $list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
		    foreach ($list_event as $item) {
                        if (isset($item->picture->file_path)) {
                            $picture = '<img src="' . URL::media('/images/w276-cct-si/' . $item->picture->file_path, TRUE) . '" alt="" />';
                        } else {
                            switch ($language) {
                                case 'ru':
                                    $picture = $item->description_ru;
                                    break;
                                case 'kz':
                                    $picture = $item->description_kz;
                                    break;
                                case 'en':
                                    $picture = $item->description_en;
                                    break;
                            }
                        }
                        switch ($language) {
                            case 'ru':
                                $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_ru, 'date' => $item->date_ru, 'picture' => $picture);
                                break;
                            case 'kz':
                                $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_kz, 'date' => $item->date_kz, 'picture' => $picture);
                                break;
                            case 'en':
                                $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_en, 'date' => $item->date_en, 'picture' => $picture);
                                break;
                        }
                    }
                }
            } else {
                $finish = true;
                $old--;
            }
        } elseif ($old < 0 AND isset($list_era[$id - 1])) {
            $id--;
            $list_list = $list_era[$id]->children;
            $old = count($list_list) - 1;
            if (isset($list_list[$old])) {
                $list_period = $list_list[$old]->children(FALSE, 'ASC', 3);
                foreach ($list_period as $item) {
                    $param = explode(";", $item->value);
                    $left = (int)(isset($param[0])) ? $param[0] : 0;
                    $width = (int)(isset($param[1])) ? $param[1] : 0;
                    switch ($language) {
                        case 'ru':
                            $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_ru, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_ru . ' - ' . $item->finish_ru . ')');
                            break;
                        case 'kz':
                            $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_kz, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_kz . ' - ' . $item->finish_kz . ')');
                            break;
                        case 'en':
                            $mas_list_period[] = array('id' => $item->id, 'name' => $item->name_en, 'left' => $left, 'width' => $width, 'date' => '(' . $item->start_en . ' - ' . $item->finish_en . ')');
                            break;
                    }
                }
                /*
		$list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->and_where('description_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
                */
		$list_event = $list_list[$old]->lines->where('title_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
		foreach ($list_event as $item) {
                    if (isset($item->picture->file_path)) {
                        $picture = '<img src="' . URL::media('/images/w276-cct-si/' . $item->picture->file_path, TRUE) . '" alt="" />';
                    } else {
                        switch ($language) {
                            case 'ru':
                                $picture = $item->description_ru;
                                break;
                            case 'kz':
                                $picture = $item->description_kz;
                                break;
                            case 'en':
                                $picture = $item->description_en;
                                break;
                        }
                    }
                    switch ($language) {
                        case 'ru':
                            $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_ru, 'date' => $item->date_ru, 'picture' => $picture);
                            break;
                        case 'kz':
                            $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_kz, 'date' => $item->date_kz, 'picture' => $picture);
                            break;
                        case 'en':
                            $mas_list_event[] = array('id' => $item->id, 'line_x' => $item->line_x, 'title' => $item->title_en, 'date' => $item->date_en, 'picture' => $picture);
                            break;
                    }
                }
            } else {
                $finish = true;
                $old++;
            }
        }
        $data = array('period' => $mas_list_period, 'events' => $mas_list_event, 'new_id' => $id, 'new_old' => $old, 'finish' => $finish);
        $this->response->body(json_encode($data));

    }

    public function action_event()
    {
        header('Content-type: application/json');
        $id = Arr::get($_POST, 'id', 0);
        $language = Arr::get($_POST, 'language');
        $event = ORM::factory('Chronology_Line', $id);
        if (!$event->loaded()) {
            throw new HTTP_Exception_404('Not Found');
        }
        $image = 0;
        if (isset($event->picture->file_path)) {
            $image = URL::media('/images/w300-cct-si/' . $event->picture->file_path, TRUE);
        }
        switch ($language) {
            case 'ru':
                $text = Text::limit_words($event->text_ru, 30);
                $data = array('id' => $event->id, 'title' => $event->title_ru, 'description' => $event->description_ru, 'text' => $text, 'date' => $event->date_ru, 'image' => $image);
                break;
            case 'kz':
                $text = Text::limit_words($event->text_kz, 30);
                $data = array('id' => $event->id, 'title' => $event->title_kz, 'description' => $event->description_kz, 'text' => $text, 'date' => $event->date_kz, 'image' => $image);
                break;
            case 'en':
                $text = Text::limit_words($event->text_en, 30);
                $data = array('id' => $event->id, 'title' => $event->title_en, 'description' => $event->description_en, 'text' => $text, 'date' => $event->date_en, 'image' => $image);
                break;
        }
        $this->response->body(json_encode($data));
    }

    public function action_calendar()
    {
        header('Content-type: text/json');
        $month = Arr::get($_POST, 'month', 0);
        $list = ORM::factory('Calendar')->where('month', '=', $month)->find_all();
        $data = array();
        foreach ($list as $item) {
            $data[] = array('id' => $item->id, 'day' => $item->day, 'title' => $item->title);
        }
        $this->response->body(json_encode($data));
    }

    public function action_profile()
    {
        $total = (int)Arr::get($_POST, 'total', 0);
        $lang = Security::xss_clean(trim(strip_tags(Arr::get($_POST, 'lang', ''))));
        $child = ORM::factory('Page')->where('parent_id', '=', $total)->order_by('id', 'ASC')->find_all();
        $data = array();
        $data[$total] = i18n::get('For All', $lang);
        $name = "name_" . $lang;
        foreach ($child as $item) {
            $data[$item->id] = $item->$name;
        }

        $this->response->body(json_encode($data));
    }

    public function action_avatar()
    {
        $id = (int)Arr::get($_POST, 'storage_id', 1);
        $storage = ORM::factory('Storage', $id);
        $user_id = Auth::instance()->get_user()->id;
        if ($storage->loaded()) {
            $profile = ORM::factory('User_Profile')->where('user_id', '=', $user_id)->find();
            $profile->user_id = $user_id;
            $profile->photo = $storage;
            $profile->save();
            $this->response->body(URL::media('/images/w220-h220-ccc-si/' . $storage->file_path));
        } else {
            throw new HTTP_Exception_404('file Not Found');
        }
    }

    public function action_picturecut()
    {
        $x1 = (int)Arr::get($_POST, 'x1', 0);
        $h = (int)Arr::get($_POST, 'h', 0);
        $y1 = (int)Arr::get($_POST, 'y1', 0);
        $w = (int)Arr::get($_POST, 'w', 0);
        $path = Arr::get($_POST, 'path', 0);

        $user_id = Auth::instance()->get_user()->id;

        $storage_id=Storage::instance()->save_jcrop_photo(URL::media($path,true),$user_id);
        $storage=ORM::factory('Storage',$storage_id);
        $newpath=$storage->file_path;

        $targ_w = 280;
        $targ_h = 186;

        $ext=explode('.',$path);
        $ext=$ext[1];

        if ($ext!='png'){
            $img_r = imagecreatefromjpeg(URL::media($newpath,true));
        }else {
            $img_r = imagecreatefrompng(URL::media($newpath,true));
        }
        $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

        imagecopyresampled($dst_r,$img_r,0,0,$x1,$y1,$targ_w,$targ_h,$w,$h);

        if ($ext!='png'){
            imagejpeg($dst_r, $newpath, 90);
        }else {
            imagepng($dst_r, $newpath, 9);
        }

        $result['path']=$newpath;
        $result['id']=$storage_id;

        $this->response->body(json_encode($result));
    }

    public function action_rating()
    {
        header('Content-type: application/json');
        $user = Auth::instance()->get_user();
        $id = Arr::get($_POST, 'id', 0);
        $voice = Arr::get($_POST, 'voice', 0);
        $message = ORM::factory('Forum_Message', $id);
        if ( $message->loaded() AND $user)
        {
            $message->rating = $message->rating + $voice;
            $message->update();
            $user->add('rating',$message);

        }
        $this->response->body(json_encode(true));
    }

    public function action_addview()
    {
        $id = Arr::get($_POST, 'id', 0);
        $theme=ORM::factory('Forum_Theme', $id);
        $theme->count_view=$theme->count_view+1;
        $theme->update();
    }

    public function action_plus()
    {
        if (Auth::instance()->logged_in())
        {
            $user_id = Auth::instance()->get_user()->id;
            $mess_id =(int) Security::xss_clean(Arr::get($_POST, 'mess_id', 0));
            $voice = (int) Security::xss_clean(Arr::get($_POST, 'voice', 0));
            $message = ORM::factory('Forum_Message', $mess_id);
            $poll_user = ORM::factory('Forum_Poll')->where('user_id', '=', $user_id)->and_where('branch_id', '=', $mess_id)->find();
            if ( ( !$poll_user->loaded() ) and ($message->user_id != $user_id) ) {
                $poll = ORM::factory('Forum_Poll');
                $poll->branch_id = $mess_id;
                $poll->variant = 1;
                $poll->user_id = $user_id;
                $poll->save();
                if($voice==1) {
                    $message->plus += 1;
                }
                else {
                    $message->minus += 1;
                }

                $message->save();
                $this->response->body(json_encode(1));
            }
            elseif( ($message->user_id == $user_id) ) {
                $this->response->body(json_encode(-1));
            }
            else {
                $this->response->body(json_encode(-2));
            }
        }
        else {
            $this->response->body(json_encode(-3));
        }
    }
}
