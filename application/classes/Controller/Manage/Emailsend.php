<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Emailsend extends Controller_Manage_Core
{
    public function action_index()
    {
        $this->redirect('/');
    }

    public function action_mailtest(){

        $report_list=array();
        //получаем список юзеров имеющих подписки
        $user_list=ORM::factory('Subscription')->group_by('user_id')->find_all();
        foreach($user_list as $oneuser){
            $start = microtime(true);
            set_time_limit(30);
            $user_id=$oneuser->user_id;
            //var_dump($user_id);



            $lang=ORM::factory('Subscription_Setting')->where('user_id','=',$user_id)->find()->lang;

            $lang_arr = array('ru', 'en','kz','RU', 'EN', 'KZ');
            if (!in_array($lang, $lang_arr))
            {
                $lang='ru';
            }

            $period=ORM::factory('Subscription_Setting')->where('user_id','=',$user_id)->find()->period;
            $user=ORM::factory('User',$user_id);
            $user_profile=ORM::factory('User_Profile',$user_id);

            $update_time=false;
            $period_arr = array('1', '2','3');
            if (!in_array($period, $period_arr))
            {
                $update_time=true;
            }
            if ($period=='1'){
                $update_time=true;
            }

            if ($period=='2'){
                if ((date('N'))==1){
                    $update_time=true;
                }
            }

            if ($period=='3'){
                if ((date('j'))==1){
                    $update_time=true;
                }
            }

            //Как обращаться к пользователю
            if($user_profile->first_name!=''){
                $user_name=$user_profile->first_name.' '.$user_profile->last_name;
            }else
            {
                $user_name=$user->username;
            }

            //На какое мыло слать
            $user_email=$user->email;

            //выставляем язык для рассылки
            I18n::lang($lang);

            //получаем список подписок текущего пользователя
            $sub_list=ORM::factory('Subscription')->where('user_id','=',$user_id)->find_all();
            $links= array();

           //var_dump($sub_list->as_array());

            //ищем самую старую дату из всех подписок
            $lastdate='3000-01-01 00:00:00';
            foreach ($sub_list as $sub){
                if ($lastdate>$sub->date){
                    $lastdate=$sub->date;
                    //var_dump($sub->date);
                }
            }





            //если пришло время обновляться
            if ($update_time==true) {
                //перебираем список подписок текущего юзера
                foreach ($sub_list as $sub){

                    //Получаем запись из Pages, на которую подписан юзер
                    $page=ORM::factory('Page',$sub->category_id);

                    //Смотрим ключ записи, если ключ пустой то ищем имя ключа родителя
                    $key=$page->key;

                    if ($page->key=='' and $page->lvl=='3'){
                        $page_lvl=ORM::factory('Page',$page->parent_id)->as_array(null,'key');
                        $key=$page_lvl['key'];
                    }

                    //перебираем все варианты ключей
                    //История казахстана и подразделы
                    if ($key=='history'){
                        set_time_limit(30);
                        if ($page->lvl==2){
                            $allsubhistory=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all()->as_array(null,'id');
                            $allsubhistory=ORM::factory('Page')->where('parent_id','in',$allsubhistory)->find_all()->as_array(null,'id');
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                if (count(Subtool::findincontents($inlog,$allsubhistory))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }

                        if ($page->lvl==3){
                            $allsubhistory=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all()->as_array(null,'id');
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                if (count(Subtool::findincontents($inlog,$allsubhistory))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }
                    }

                    //Первый президент и подразделы
                    if ($key=='president'){
                        set_time_limit(30);
                        if ($page->lvl==3){
                            if ($page->key==''){
                                $find_child=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all()->as_array(null,'id');
                                $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                if (count($find_child)>0){
                                    if (count($inlog)>0){
                                        if (count(Subtool::findincontents($inlog,$find_child))>0){
                                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                        };
                                    }
                                }else{
                                    if (count($inlog)>0){
                                        $find_child[]=$sub->category_id;
                                        if (count(Subtool::findincontents($inlog,$find_child))>0){
                                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                        };
                                    }
                                }

                            }
                        }
                        if ($page->lvl==2){
                            $president=false;
                            $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all();
                            foreach($find_child_lvl3 as $child_lvl3){
                                if ($president==false){
                                    if ($child_lvl3->key==''){
                                        $find_child=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all()->as_array(null,'id');
                                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                        if (count($find_child)>0){
                                            if (count($inlog)>0){
                                                if (count(Subtool::findincontents($inlog,$find_child))>0){
                                                    $president=true;
                                                };
                                            }
                                        }else{
                                            if (count($inlog)>0){
                                                $find_child[]=$sub->category_id;
                                                if (count(Subtool::findincontents($inlog,$find_child))>0){
                                                    $president=true;
                                                };
                                            }
                                        }

                                    }
                                    $e = explode('_', $page->key);
                                    if ($e[0]=='books' and isset($e[2])){
                                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                        $cats[]=$e[2];
                                        if (count($inlog)>0){
                                            if (count(Subtool::findinbooks($inlog,$cats))>0){
                                                $president=true;
                                            };
                                        }
                                    }
                                }
                            }

                            if ($president==true){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        }
                    }

                    //Экспертный взгляд и подразделы
                    if ($key=='expert' and $page->key=='expert' and $page->lvl==3){
                        set_time_limit(30);
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Expert_Opinion'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                        if (count($inlog)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='expert' and $page->key=='' and $page->lvl==3){
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                        if (count($inlog)>0){
                            $allsub[]=$sub->category_id;
                            if (count(Subtool::findincontents($inlog,$allsub))>0){

                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        }
                    }
                    if ($key=='expert' and $page->lvl==2){
                        set_time_limit(30);
                        $expert=false;
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all();
                        foreach($find_child_lvl3 as $child_lvl3){
                            if ($expert==false){
                                if ($child_lvl3->key=='expert'){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Expert_Opinion'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $expert=true;
                                    }
                                }
                                if ($child_lvl3->key==''){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsub[]=$child_lvl3->id;
                                        if (count(Subtool::findincontents($inlog,$allsub))>0){
                                            $expert=true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($expert==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //Электронный архив и подразделы
                    if ($key=='archive' and $page->lvl==3){
                        set_time_limit(30);
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all();
                        if (count($find_child_lvl3)>0){
                            $archive_lvl3=false;
                            foreach ($find_child_lvl3 as $child_lvl3){
                                if ($archive_lvl3==false){
                                    $allsub=array();
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsub[]=$child_lvl3->id;
                                        if (count(Subtool::findincontents($inlog,$allsub))>0){
                                            $archive_lvl3=true;
                                        }
                                    }
                                }
                            }
                            if ($archive_lvl3==true){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        } else{
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                $allsub[]=$sub->category_id;
                                if (count(Subtool::findincontents($inlog,$allsub))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }
                    }

                    if ($key=='archive' and $page->lvl==2){
                        $find_all_child=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all();
                        $archive_lvl3=false;
                        foreach ($find_all_child as $finded_child){
                            if ($archive_lvl3==false){
                                set_time_limit(30);
                                $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$finded_child->id)->find_all();
                                $archive_lvl3=false;
                                if (count($find_child_lvl3)>0){
                                    foreach ($find_child_lvl3 as $child_lvl3){
                                        $allsub=array();
                                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                        if (count($inlog)>0){
                                            $allsub[]=$child_lvl3->id;
                                            if (count(Subtool::findincontents($inlog,$allsub))>0){
                                                $archive_lvl3=true;
                                            }
                                        }
                                    }
                                } else{
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsub[]=$finded_child->id;
                                        if (count(Subtool::findincontents($inlog,$allsub))>0){
                                            $archive_lvl3=true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($archive_lvl3==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }


                    //Персоналии
                    if ($key=='biography' and $page->lvl==3){
                        $all_biography_lvl4=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        foreach ($all_biography_lvl4 as $biography_lvl4){
                            set_time_limit(30);
                            $newbiography=false;
                            $e = explode('_', $biography_lvl4->key);
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Biography'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                $biography=ORM::factory('Biography')->where('id','in',$inlog)->and_where('category_id','=',$e[1])->find_all()->as_array(null,'id');
                                if (count($biography)>0){
                                    $newbiography=true;
                                }
                            }
                        }
                        if ($newbiography==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='personal' and $page->lvl==2){
                        $all_biography_lvl3=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all()->as_array(null,'id');
                        $all_biography_lvl4=ORM::factory('Page')->where('parent_id','in',$all_biography_lvl3)->find_all();
                        $newbiography=false;
                        foreach ($all_biography_lvl4 as $biography_lvl4){
                            set_time_limit(30);
                            if ($newbiography==false){
                                $e = explode('_', $biography_lvl4->key);
                                $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Biography'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                if (count($inlog)>0){
                                    $biography=ORM::factory('Biography')->where('id','in',$inlog)->and_where('category_id','=',$e[1])->find_all()->as_array(null,'id');
                                    if (count($biography)>0){
                                        $newbiography=true;
                                    }
                                }
                            }
                        }
                        if ($newbiography==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //Книги любые
                    $e = explode('_', $page->key);
                    if ($e[0]=='books' and isset($e[2])){
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                        $cats[]=$e[2];
                        if (count($inlog)>0){
                            if (count(Subtool::findinbooks($inlog,$cats))>0){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            };
                        }
                    }

                    // Историческое образование
                    if ($key=='education' and $page->lvl==3){
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all();
                        if (count($find_child_lvl3)>0){
                            $education_lvl3=false;
                            foreach ($find_child_lvl3 as $child_lvl3){
                                if ($education_lvl3==false){
                                    $allsub=array();
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $e = explode('_', $child_lvl3->key);
                                        $allsub[]=$e[1];
                                        if (count(Subtool::findinbooks($inlog,$allsub))>0){
                                            $education_lvl3=true;
                                        }
                                    }
                                }
                            }
                            if ($education_lvl3==true){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        }
                    }
                    if ($key=='education' and $page->lvl==2){
                        $find_child_lvl2=ORM::factory('Page')->where('parent_id','=',$sub->category_id)->find_all()->as_array(null,'id');
                        $neweducation=false;
                        foreach ($find_child_lvl2 as $child_lvl2){
                            set_time_limit(30);
                            if ($neweducation==false){
                                $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$child_lvl2)->find_all();
                                if (count($find_child_lvl3)>0){
                                    foreach ($find_child_lvl3 as $child_lvl3){
                                        $allsub=array();
                                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                        if (count($inlog)>0){
                                            $e = explode('_', $child_lvl3->key);
                                            $allsub[]=$e[1];
                                            if (count(Subtool::findinbooks($inlog,$allsub))>0){
                                                $neweducation=true;
                                            }
                                        }
                                    }
                                }else{
                                    $booksfore=ORM::factory('Page',$child_lvl2); //опасный момент
                                    $e = explode('_', $booksfore->key);
                                    if ($e[0]=='books' and isset($e[2])){
                                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                        $cats[]=$e[2];
                                        if (count($inlog)>0){
                                            if (count(Subtool::findinbooks($inlog,$cats))>0){
                                                $neweducation=true;
                                            };
                                        }
                                    }
                                }
                            }
                        }
                        if ($neweducation==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //Организации образования и науки
                    if ($key=='institute' and $page->lvl==3){
                        set_time_limit(30);
                        $find_child_lvl4=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        if(count($find_child_lvl4->as_array(null,'id'))>0){
                            foreach ($find_child_lvl4 as $child_lvl4){
                                $find_child_lvl5=ORM::factory('Page')->where('parent_id','=',$child_lvl4->id)->find_all()->as_array(null,'id');
                                if (count($find_child_lvl5)>0){
                                    $child_lvl5[]=$find_child_lvl5[0];
                                }
                            }
                            if (count($child_lvl5)>0){
                                $find_organization=ORM::factory('Organization')->where('page_id','in',$child_lvl5)->and_where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                if (count($find_organization)>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }else{
                                $child_lvl4_ids=$find_child_lvl4->as_array(null,'id');
                                $find_organization=ORM::factory('Organization')->where('page_id','in',$child_lvl4_ids)->and_where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                if (count($find_organization)>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }else{
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                $allsubs[]=$page->id;
                                if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }
                    }
                    if ($key=='institute' and $page->lvl==2){
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $newinstitute=false;
                        foreach($find_child_lvl3 as $child_lvl3){
                            set_time_limit(30);
                            if ($newinstitute==false){
                                $find_child_lvl4=ORM::factory('Page')->where('parent_id','=',$child_lvl3->id)->find_all();
                                if(count($find_child_lvl4->as_array(null,'id'))>0){
                                    foreach ($find_child_lvl4 as $child_lvl4){
                                        set_time_limit(30);
                                        $find_child_lvl5=ORM::factory('Page')->where('parent_id','=',$child_lvl4->id)->find_all()->as_array(null,'id');
                                        if (count($find_child_lvl5)>0){
                                            $child_lvl5[]=$find_child_lvl5[0];
                                        }
                                    }
                                    if (count($child_lvl5)>0){
                                        $find_organization=ORM::factory('Organization')->where('page_id','in',$child_lvl5)->and_where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                        if (count($find_organization)>0){
                                            $newinstitute=true;
                                        }
                                    }else{
                                        $child_lvl4_ids=$find_child_lvl4->as_array(null,'id');
                                        $find_organization=ORM::factory('Organization')->where('page_id','in',$child_lvl4_ids)->and_where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                        if (count($find_organization)>0){
                                            $newinstitute=true;
                                        }
                                    }
                                }else{
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsubs[]=$page->id;
                                        if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                            $newinstitute=true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($newinstitute==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    if ($key=='people' and $page->lvl==3){
                        $find_child_lvl4=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all()->as_array(null,'id');
                        if (count($find_child_lvl4)>0){
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                $allsubs=$find_child_lvl4;
                                if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }else{
                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                            if (count($inlog)>0){
                                $allsubs[]=$page->id;
                                if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                    $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                                }
                            }
                        }
                    }

                    if ($key=='people' and $page->lvl==2){
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $newpeople=false;
                        foreach ($find_child_lvl3 as $child_lvl3){
                            set_time_limit(30);
                            if ($newpeople==false){
                                $find_child_lvl4=ORM::factory('Page')->where('parent_id','=',$child_lvl3->id)->find_all()->as_array(null,'id');
                                if (count($find_child_lvl4)>0){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsubs=$find_child_lvl4;
                                        if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                            $newpeople=true;
                                        }
                                    }
                                }else{
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsubs[]=$child_lvl3->id;
                                        if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                            $newpeople=true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($newpeople==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //публикации
                    if ($key=='publications' and $page->lvl==2){
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Publication'))->and_where('language','=',$lang)->and_where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($inlog)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }


                    //Электронный журнал "e-history.kz"
                    if (($key=='metodology') or ($key=='crossstudy') or ($key=='metodicheskie')){
                        $find_childs_lvl4=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $finded_lvl3=false;
                        foreach ($find_childs_lvl4 as $childs_lvl4){
                            set_time_limit(30);
                            if ($finded_lvl3==false){
                                set_time_limit(30);
                                $find_childs_lvl5=ORM::factory('Page')->where('parent_id','=',$childs_lvl4->id)->find_all()->as_array(null,'id');
                                if (count($find_childs_lvl5)>0){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsubs=$find_childs_lvl5;
                                        if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                            $finded_lvl3=true;
                                        }
                                    }
                                } else {
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $allsubs[]=$childs_lvl4->id;
                                        if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                            $finded_lvl3=true;
                                        }
                                    }
                                }
                            }
                        }
                        if ($finded_lvl3==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    if ($key=='journal_ehistory'){
                        $find_childs_lvl3=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $newehistory=false;
                        foreach ($find_childs_lvl3 as $childs_lvl3){
                            set_time_limit(30);
                            if ($newehistory==false){
                                $find_childs_lvl4=ORM::factory('Page')->where('parent_id','=',$childs_lvl3->id)->find_all();
                                $finded_lvl3=false;
                                foreach ($find_childs_lvl4 as $childs_lvl4){
                                    if ($finded_lvl3==false){
                                        set_time_limit(30);
                                        $find_childs_lvl5=ORM::factory('Page')->where('parent_id','=',$childs_lvl4->id)->find_all()->as_array(null,'id');
                                        if (count($find_childs_lvl5)>0){
                                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                            if (count($inlog)>0){
                                                $allsubs=$find_childs_lvl5;
                                                if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                                    $finded_lvl3=true;
                                                    $newehistory=true;
                                                }
                                            }
                                        } else {
                                            $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Pages_Content'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                            if (count($inlog)>0){
                                                $allsubs[]=$childs_lvl4->id;
                                                if (count(Subtool::findincontents($inlog,$allsubs))>0){
                                                    $finded_lvl3=true;
                                                    $newehistory=true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($newehistory==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //блок интерактив
                    if ($key=='debate'){
                        $debates=ORM::factory('Debate')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($debates)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='briefings'){
                        $briefings=ORM::factory('Briefing')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($briefings)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='scorm'){
                        $educations=ORM::factory('Education')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($educations)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='ent'){
                        $ent=ORM::factory('Ent')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($ent)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    if ($key=='interactive'){
                        $interactives=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $newinteractive=false;
                        foreach ($interactives as $child){
                            set_time_limit(30);
                            if ($newinteractive==false){
                                $child_key=$child->key;
                                if ($child_key=='debate'){
                                    $debates=ORM::factory('Debate')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($debates)>0){
                                        $newinteractive=true;
                                    }
                                }
                                if ($child_key=='briefings'){
                                    $briefings=ORM::factory('Briefing')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($briefings)>0){
                                        $newinteractive=true;
                                    }
                                }
                                if ($child_key=='scorm'){
                                    $educations=ORM::factory('Education')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($educations)>0){
                                        $newinteractive=true;
                                    }
                                }
                                if ($child_key=='ent'){
                                    $ent=ORM::factory('Ent')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($ent)>0){
                                        $newinteractive=true;
                                    }
                                }
                            }
                        }
                        if ($newinteractive==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    if ($key=='photosets'){
                        $photosets=ORM::factory('Photoset')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($photosets)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='video'){
                        $videos=ORM::factory('Video')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($videos)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='audio'){
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Audio'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                        if (count($inlog)>0){
                            $audios=ORM::factory('Audio')->where('id','in',$inlog)->find_all()->as_array(null,'id');
                            if (count($audios)>0){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        }
                    }
                    if ($key=='infographs'){
                        $infographs=ORM::factory('Infograph')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                        if (count($infographs)>0){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }
                    if ($key=='library'){
                        $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                        if (count($inlog)>0){
                            $books=ORM::factory('Book')->where('id','in',$inlog)->find_all()->as_array(null,'id');
                            if (count($books)>0){
                                $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                            }
                        }
                    }

                    if ($key=='multimedia'){
                        $find_child_lvl3=ORM::factory('Page')->where('parent_id','=',$page->id)->find_all();
                        $newmultimedia=false;
                        foreach ($find_child_lvl3 as $child_lvl3){
                            set_time_limit(30);
                            if ($newmultimedia==false){
                                $child_key=$child_lvl3->key;
                                if ($child_key=='photosets'){
                                    $photosets=ORM::factory('Photoset')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($photosets)>0){
                                        $newmultimedia=true;
                                    }
                                }
                                if ($child_key=='video'){
                                    $videos=ORM::factory('Video')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($videos)>0){
                                        $newmultimedia=true;
                                    }
                                }
                                if ($child_key=='audio'){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Audio'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $audios=ORM::factory('Audio')->where('id','in',$inlog)->find_all()->as_array(null,'id');
                                        if (count($audios)>0){
                                            $newmultimedia=true;
                                        }
                                    }
                                }
                                if ($child_key=='infographs'){
                                    $infographs=ORM::factory('Infograph')->where('date','>',$lastdate)->find_all()->as_array(null,'id');
                                    if (count($infographs)>0){
                                        $newmultimedia=true;
                                    }
                                }
                                if ($child_key=='library'){
                                    $inlog=ORM::factory('Log')->where(DB::expr('lower(model)'),'LIKE',strtolower('Model_Book'))->and_where('date','>',$lastdate)->find_all()->as_array(null,'content_id');
                                    if (count($inlog)>0){
                                        $books=ORM::factory('Book')->where('id','in',$inlog)->find_all()->as_array(null,'id');
                                        if (count($books)>0){
                                            $newmultimedia=true;
                                        }
                                    }
                                }
                            }
                        }
                        if($newmultimedia==true){
                            $links[]=array('link'=>URL::media($lang.'/contents/list/'.$sub->category_id), 'title'=>$page->name);
                        }
                    }

                    //$subupdate=ORM::factory('Subscription',$sub->id);
                    //$subupdate->date=date("Y-m-d H:i:s");
                    //$subupdate->save();

                }
                $time = microtime(true) - $start;
                //var_dump($links);


                if (count($links)!=0){
                    $report_list[]=(array('time'=>$time, 'user_id'=>$user_id));
                    Email::connect();
                    if (($lang=='ru') or ($lang=='RU')){
                        Email::View('distribution_ru');}

                    if (($lang=='kz') or ($lang=='KZ')){
                        Email::View('distribution_kz');}

                    if (($lang=='en') or ($lang=='EN')){
                        Email::View('distribution_en');}

                    Email::set(array('user'=>$user_name, 'period1'=>$lastdate,'period2'=>date("Y-m-d H:i:s"),
                        'unsublink'=>Subtool::media('/'.$lang.'/profile/subscription'), 'links'=>$links));
                    set_time_limit(60);
                    Email::send('kolledgpgu@mail.ru',array('no-reply@e-history.kz', 'e-history.kz'), I18n::get('Обновления на портале Истории Казахстана e-history.kz'),'',true);
                }
            }
        }
        //exit;

        set_time_limit(60);
        if (count($report_list)!=0){
            Email::connect();
            Email::View('successsub');
            Email::set(array('message'=>'Все рассылки успешно разослались', 'list'=>$report_list));
            Email::send('kolledgpgu@mail.ru', array('no-reply@e-history.kz', 'e-history.kz'), "Рассылки на истории", '', true);
        } else{
            Email::connect();
            Email::View('default');
            Email::set(array('message'=>'Сегодня никому ничего не отправлено'));
            Email::send('kolledgpgu@mail.ru', array('no-reply@e-history.kz', 'e-history.kz'), "Рассылки на истории", '', true);
        }
        var_dump($report_list);
        exit;
    }

    public function action_updateorm(){
        DB::update('subscriptions')->value('date','2013-11-26 01:00:00')->execute();
    }
}