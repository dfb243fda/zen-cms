<?php

namespace App\Utility;

class StringUtility
{
    public static function preText($text, $length, $clearFormat = true)
    {
        if ($clearFormat) {
            $text = strip_tags($text);
        }
            
        if (mb_strlen($text) > $length) {
            $tmp_length = mb_strripos(mb_substr($text, 0, $length), ' ');
            if ($tmp_length !== false) {
                $length = $tmp_length;
            }
            $text = mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }    
}