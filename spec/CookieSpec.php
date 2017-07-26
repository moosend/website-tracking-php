<?php namespace spec\Moosend;

use Moosend\Cookie;
use PhpSpec\ObjectBehavior;

class CookieSpec extends ObjectBehavior {

    function it_should_work() {
        $this->setCookie('test', 123);
        $this->getCookie('test')->shouldReturn(123);
    }

}