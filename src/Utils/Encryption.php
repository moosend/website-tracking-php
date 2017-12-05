<?php

namespace Moosend\Utils;

class Encryption
{
    private static function is_base64_encoded_string($string) {
        return base64_decode(strtr($string, '-_', '+/'), true);
    }

    public static function encode($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

    public static function decode($string)
    {
        if (self::is_base64_encoded_string($string)) {
            return base64_decode(strtr($string, '-_', '+/'));
        }
        return $string;
    }
}
