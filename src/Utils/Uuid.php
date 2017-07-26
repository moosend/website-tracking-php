<?php namespace Moosend\Utils;

class Uuid
{
    const CLEAR_VAR = 63;

    const VAR_RFC = 128;

    const CLEAR_VER = 15;

    const VERSION_4 = 64;

    private static function randomBytes($bytes)
    {
        return call_user_func(array('static', static::initRandom()), $bytes);
    }

    private static function initRandom()
    {
        if (function_exists('random_bytes')) {
            return 'randomPhp7';
        }
        return 'randomOpenSSL';
    }

    private static function randomPhp7($bytes)
    {
        return random_bytes($bytes);
    }

    protected static function randomOpenSSL($bytes)
    {
        return openssl_random_pseudo_bytes($bytes);
    }

    private static function convertToString($uuid)
    {
        return bin2hex(substr($uuid, 0, 4)) . "-" .
            bin2hex(substr($uuid, 4, 2)) . "-" .
            bin2hex(substr($uuid, 6, 2)) . "-" .
            bin2hex(substr($uuid, 8, 2)) . "-" .
            bin2hex(substr($uuid, 10, 6));
    }

    public static function v4()
    {
        $uuid = static::randomBytes(16);
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_4);
        return self::convertToString($uuid);
    }
}
