<?php namespace Moosend;

class Cookie
{
    public function setCookie($name, $value = "", $expire = 0, $path = "/", $domain = "", $secure = false, $httponly = false)
    {
        $_COOKIE[$name] = $value;
        return setrawcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function getCookie($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
}
