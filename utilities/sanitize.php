<?php

class Sanitize {

    public static function toHTMLChars($string) {
        $string = htmlspecialchars_decode($string, ENT_QUOTES);
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function unsanitizeHTMLChars($string) {
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }

    public static function safeEcho($string) {
        echo self::toHTMLChars($string);
    }

}

?>