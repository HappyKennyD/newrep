<?php defined('SYSPATH') or die('No direct script access.');

class FileData
{
    public static function size($size)
    {
        $file = round($size / (1024 * 1024), 2);
        return $file . " " . i18n::get('MB');
    }

    public static function name($id)
    {
        $file = ORM::factory('Storage', $id);
        if ($file->loaded()) {
            return $file->name;
        } else {
            return '';
        }
    }

    public static function filesize($id)
    {
        $file = ORM::factory('Storage', $id);
        if (!$file->loaded()) {
            return '';
        }
        $size = filesize($file->file_path);
        return FileData::size($size);
    }

    public static function filepath($id){
        $file = ORM::factory('Storage', $id);
        if (!$file->loaded()) {
            return '';
        }
        return $file->file_path;
    }
}