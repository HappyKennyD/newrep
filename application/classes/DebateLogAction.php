<?php defined('SYSPATH') or die('No direct script access.');

class DebateLogAction
{
    public static function give($str)
    {
        switch ($str) {
            case 'create':
                return "Create the debate ";
            case 'close':
                return "Hide the debate ";
                break;
            case 'edit':
                return "Edit opinions debater ";
                break;
            case 'open':
                return "Display the debate ";
                break;
            case 'hide':
                return "Hide comments ";
                break;
            case 'show':
                return "Display comments ";
                break;
            case 'time':
                return "Change the duration of the debate ";
                break;
            case 'title':
                return "Changing the subject of debate ";
                break;
            case 'member':
                return "Change the debater ";
                break;
            case 'description':
                return "Change the description of the debate ";
                break;

        }
    }
}