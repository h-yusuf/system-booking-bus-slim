<?php
namespace App\Lib;
/**
 * Language
 *
 * @author    
 */
class Language
{
    public function output(){
        $lang_path = dirname( dirname(__DIR__)).'/resources/lang/en/app.json';
        $lang = file_get_contents($lang_path);
        return json_decode($lang, true);
    }
}
