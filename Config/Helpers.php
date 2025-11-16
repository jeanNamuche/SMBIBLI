<?php
function strClean($cadena)
{
    $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $cadena);
    $string = trim($string);
    $string = stripslashes($string);
    $string = str_ireplace('<script>', '', $string);
    $string = str_ireplace('</script>', '', $string);
    $string = str_ireplace('<script type=>', '', $string);
    $string = str_ireplace('<script src>', '', $string);
    $string = str_ireplace('SELECT * FROM', '', $string);
    $string = str_ireplace('DELETE FROM', '', $string);
    $string = str_ireplace('INSERT INTO', '', $string);
    $string = str_ireplace('SELECT COUNT(*) FROM', '', $string);
    $string = str_ireplace('DROP TABLE', '', $string);
    $string = str_ireplace("OR '1'='1", '', $string);
    $string = str_ireplace('OR ´1´=´1', '', $string);
    $string = str_ireplace('IS NULL', '', $string);
    $string = str_ireplace('LIKE "', '', $string);
    $string = str_ireplace("LIKE '", '', $string);
    $string = str_ireplace('LIKE ´', '', $string);
    $string = str_ireplace('OR "a"="a', '', $string);
    $string = str_ireplace("OR 'a'='a", '', $string);
    $string = str_ireplace('OR ´a´=´a', '', $string);
    $string = str_ireplace('--', '', $string);
    $string = str_ireplace('^', '', $string);
    $string = str_ireplace('[', '', $string);
    $string = str_ireplace(']', '', $string);
    $string = str_ireplace('==', '', $string);
    return $string;
}

// Safe encoding helpers to avoid deprecated utf8_encode/utf8_decode usage
if(!function_exists('safe_utf8_encode')){
    function safe_utf8_encode($str)
    {
        if($str === null) return $str;
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }
        if(function_exists('iconv')){
            return iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $str);
        }
        // Fallback (rare): use utf8_encode if available
        if(function_exists('utf8_encode')){
            return utf8_encode($str);
        }
        return $str;
    }
}

if(!function_exists('safe_utf8_decode')){
    function safe_utf8_decode($str)
    {
        if($str === null) return $str;
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        }
        if(function_exists('iconv')){
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
        }
        // Fallback (rare): use utf8_decode if available
        if(function_exists('utf8_decode')){
            return utf8_decode($str);
        }
        return $str;
    }
}
