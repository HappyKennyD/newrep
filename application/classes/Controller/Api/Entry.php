<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Entry extends Controller_Api_Core{

    public function action_index()
    {

        $explode = explode('_', strtolower($this->entryType));
        if ($explode[0]=='list'){
            $this->entryType=$explode[0];
        }

        switch ($this->entryType) {

            case 'publications':

                $publication = ORM::factory('Publication')->where('id', '=', $this->entryId)->and_where('title_'.$this->language, '<>', '')->and_where('published','=', 1)->find();
                if ( !$publication->loaded() )
                {
                    $this->data['error'] = 'Publication not found';
                }
                else
                {
                    $comments = ORM::factory('Comment')->where('object_id', '=', $this->entryId)->and_where('status', '=', '1')->and_where('table','=', 'publications')->find_all();

                    $coverUrl = '';
                    if(!empty($publication->picture->file_path))
                    {
                        $coverUrl = 'http://'.$_SERVER['SERVER_NAME'].'/'.$publication->picture->file_path;
                    }
                    $this->data['id'] = $publication->id;
                    $this->data['title']         = $publication->title;
                    $this->data['createdDate']   = $publication->date;
                    $this->data['coverUrl']      = $coverUrl;
                    $this->data['text']          = str_replace('/media/upload',URL::media('/media/upload',true),$publication->text);
                    $this->data['commentCount']  = $comments->count();
                    $this->data['url']           = URL::site('/',true) . $this->language . '/publications/view/' . $publication->id;
                }

            break;

            case 'expert_opinion':

                $opinion = ORM::factory('Expert_Opinion')->where('id', '=', $this->entryId)->and_where('title_' . $this->language, '<>', '')->find();
                if ( !$opinion->loaded() )
                {
                    $this->data['error'] = 'Expert opinion not found';
                }
                else
                {
                    $comments = ORM::factory('Expert_Comment')->where('opinion_id', '=', $this->entryId)->and_where('moderator_decision', '=', '1')->find_all();
                    $this->data['id'] = $opinion->id;
                    $this->data['title']           = $opinion->expert->name . ': ' . $opinion->title;
                    $this->data['createdDate']     = $opinion->date;
                    $this->data['text']            = str_replace('/media/upload',URL::media('/media/upload',true),$opinion->text);
                    $this->data['commentCount']    = $comments->count();
                    $this->data['url']             = URL::site('/',true) . $this->language . '/expert/view/' . $opinion->id;
                }

            break;

            case 'list':
                $id = $this->entryId;
                $page_content = ORM::factory('Pages_Content')->where('id', '=', $id)->and_where('published', '=', 1)->and_where('title_'.$this->language, '<>', '')->find();

                $comments = ORM::factory('Comment')->where('object_id', '=', $id)->and_where('status', '=', '1')->find_all();

                if(!$page_content->loaded())
                {
                    $this->data['error'] ='Content not found';
                }
                else
                {
                     $coverUrl = '';
                     if( !empty($page_content->picture->file_path) )
                     {
                         $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $page_content->picture->file_path;
                     }

                     $this->data['id']           = $page_content->id;
                     $this->data['title']        = $page_content->title;
                     $this->data['createdDate']  = $page_content->date;
                     $this->data['coverUrl']     = $coverUrl;
                     $this->data['text']         = str_replace('/media/upload',URL::media('/media/upload',true),$page_content->text);
                     $this->data['commentCount'] = $comments->count();
                     $this->data['url']          = URL::site('/', true) . $this->language . '/contents/view/'. $id;
                }


            break;

            case 'debate':

                $status = false;
                $nowdate = date('Y-m-d H:i:s');

                $debate = ORM::factory('Debate', $this->entryId);

                if ( (!$debate->loaded()) OR ($debate->is_closed) )
                {
                    $this->data['error'] = 'Unknown Exception';
                }
                else
                {
                    if ($debate->is_closed == 0)
                    {
                        if (Date::translate($debate->end_time, 'U') < Date::translate($nowdate, 'U') AND Date::translate($debate->end_time, 'U') != 0)
                        {
                            $status = false;
                        }
                        else
                        {
                            $status = true;
                        }
                    }

                    $this->data['id']                 = $debate->id;
                    $this->data['title']              = $debate->title;
                    $this->data['preview']            = $debate->description;
                    $this->data['createdDate']        = $debate->date;
                    $this->data['authorId']           = $debate->author->id;
                    $this->data['authorUsername']     = $debate->author->username;
                    $this->data['opponentId']         = $debate->opponent->id;
                    $this->data['opponentUsername']   = $debate->opponent->username;
                    $this->data['isActive']           = $status;
                    $this->data['endTime']            = $debate->end_time;
                    $this->data['url']                = URL::site('/', true) . $this->language . '/debate/view/' . $debate->id;
                    $this->data['commentCount']       = $debate->comments_count;

                    //меняем флаг уведомлений(считаем просмотренными)
                    if( !empty($this->auth_token) AND $this->api->token_expires($this->auth_token) )
                    {
                        $user = ORM::factory('Api_Token')->where('token', '=', $this->auth_token)->find();

                        if($user->loaded())
                        {
                            $notices = ORM::factory('Api_Notice')->where('user_id', '=', $user->user_id)->and_where('object_id', '=', $this->entryId)->and_where('flag', '=', 0)->find_all();

                            if( $notices->count() > 0 )
                            {
                                foreach($notices as $notice )
                                {
                                    $notice->flag = 1;
                                    $notice->update();
                                }
                            }
                        }
                    }
                }

            break;

            case 'today':

                $coverUrl = '';
                $list = ORM::factory('Calendar')->where('id', '=', $this->entryId)->and_where('title_' . $this->language, '<>', '')->and_where('text_' . $this->language, '<>', '')->find();

                if( $list->loaded() )
                {
                    $month = $list->month;
                    $day   = $list->day;

                    if( $list->month <10 )
                    {
                        $month = '0' . $list->month;
                    }

                    if( $list->day <10 )
                    {
                        $day = '0' . $list->day;
                    }

                    if( !empty($list->picture->file_path) )
                    {
                        $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $list->picture->file_path;
                    }

                    $this->data['id']                 = $list->id;
                    $this->data['dateHistory']        = trim($list->year).'-'.trim($list->month).'-'.trim($list->day);
                    $this->data['title']              = $list->title;
                    $this->data['preview']            = $list->desc;
                    $this->data['text']               = str_replace('/media/upload',URL::media('/media/upload',true),$list->text);
                    $this->data['coverUrl']           = $coverUrl;
                    $this->data['url']                = URL::site('/', true) . $this->language . '/calendar/event/' . $month . '/' . $day;
                }

            break;
        }

        $this->response->body(json_encode($this->data));
    }

}