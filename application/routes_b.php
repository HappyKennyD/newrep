<?php
/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

//for publications, news
Route::set('manage-public', '<directory>(/<controller>(/<type>)(/page-<page>)(/<action>(/<id>)))', array('directory' => '(manage)', 'controller' => '(publications|specprojects)', 'type' => '(publication|news|1|2|3|4)', 'id' => '(\d+)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'dashboard',
                    'action'     => 'index',
                    'type'       => 'publication'
               ));

/* for forum */
Route::set('forum', '<language>/<controller>/<action>/<id>(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(forum)', 'action' => '(list|show|editmes|deletemes)', 'page' => '(\d+)'))
    ->defaults(array(
        'controller' => 'forum',
        'action'     => 'list',
    ));

// admin panel access
Route::set('manage-paged', '<directory>/<controller>(/<action>(/<id>(/<category>)))/page-<page>', array('directory' => '(manage)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'dashboard',
                    'action'     => 'index',
               ));

Route::set('manage', '<directory>(/<controller>(/<action>(/<id>(/<category>))))', array('directory' => '(manage)'))
    ->defaults(array(
                    'controller' => 'dashboard',
                    'action'     => 'index',
               ));

/*
 * For API
 */
Route::set('api-paramid', '<language>/<directory>/<controller>(/<id>)', array('language' => '(ru|kz|en)', 'directory' => '(api)', 'id' => '\d+'));

Route::set('api', '<language>/<directory>(/<controller>(/<action>(/<id>(/<category>))))', array('language' => '(ru|kz|en)', 'directory' => '(api)'))
    ->defaults(array(
        'controller' => 'auth',
        'action'     => 'index',
    ));

// search
Route::set('search', '<language>/(<controller>(/<category>(/<string>)))(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(search)', 'string' => '([^/]+)'))
    ->defaults(array(
                    'controller' => 'search',
                    'action'     => 'index',
                    'category'   => '',
                    'string'     => '',
               ));
// test
/*Route::set('test', '<version>/<language>/(<controller>(/<string>))(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(test)', 'string' => '([^/]+)'))
    ->defaults(array(
        'controller' => 'test',
        'action'     => 'index',
        'string' => '',
    ));*/

Route::set('contents', '<language>/<controller>/<action>/<id>(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(contents)', 'action' => '(list)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'contents',
                    'action'     => 'list',
               ));

// biography
Route::set('biography', '<language>/<controller>(/<action>)(/<category>)(/<alpha>)(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(biography)', 'action' => '(index)', 'category' => '(\d+)', 'alpha' => '(\D)', 'page' => '(\d+)'))
    ->defaults(array(
        'controller' => 'biography',
        'action'     => 'index'
    ));
/*Route::set('biography', '<language>/<controller>(/<action>)(/<era>)(/<category>)(/<alpha>)(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(biography)', 'action' => '(index)', 'era' => 'era-(\d+)', 'category' => '(\d+)', 'alpha' => '(\D)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'biography',
                    'action'     => 'index'
               ));*/

Route::set('calendar', '<language>/<controller>(/<action>)(/<id>)(/<m>/<d>)', array('language' => '(ru|kz|en)', 'm' => '(\d{2})', 'd' => '(\d{2})', 'controller' => '(calendar)'))
    ->defaults(array(
                    'controller' => 'calendar',
                    'action'     => 'event',
               ));

// video
Route::set('video', '<language>/<controller>(/<action>)(/<id>)(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(video)', 'action' => '(index)', 'page' => '(\d+)', 'id' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'video',
                    'action'     => 'index'
               ));

// photosets
Route::set('photosets', '<language>/<controller>(/<action>)(/<category>)(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(photosets)', 'action' => '(index)', 'category' => '(\d+)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'photosets',
                    'action'     => 'index'
               ));

//books
Route::set('books', '<language>/<controller>/<scope>(/<action>)(/<id>)(/page-<page>)', array('language' => '(ru|kz|en)', 'controller' => '(books)', 'action' => '(index|view|read)', 'page' => '(\d+)', 'id' => '(\d+)'))
    ->defaults(array(
        'controller' => 'books',
        'action'     => 'index',
        'scope'      => 'library',
    ));

// translation
Route::set('versioned-paged', '<language>(/<controller>(/<action>))/page-<page>', array('language' => '(ru|kz|en)', 'page' => '(\d+)'))
    ->defaults(array(
                    'controller' => 'welcome',
                    'action'     => 'index'
               ));

Route::set('versioned', '<language>(/<controller>(/<action>(/<id>)))', array('language' => '(ru|kz|en)'))
    ->defaults(array(
                    'controller' => 'welcome',
                    'action'     => 'index'
               ));

Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults(array(
                    'controller' => 'welcome',
                    'action'     => 'select',
               ));
