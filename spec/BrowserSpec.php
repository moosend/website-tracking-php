<?php namespace spec\Moosend;

use Moosend\Browser;
use PhpSpec\ObjectBehavior;

class BrowserSpec extends ObjectBehavior {

    function it_should_throw_error_if_initialized() {
        $this->shouldThrow('\Exception')->during('__construct');
    }

}