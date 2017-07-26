<?php namespace spec\Moosend;

use Moosend\TrackerFactory;
use PhpSpec\ObjectBehavior;

class TrackerFactorySpec extends ObjectBehavior {

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\TrackerFactory');
    }

    function it_should_not_create_an_instance_if_site_id_is_empty() {
        $this->shouldThrow('\Exception')->duringCreate(null);
    }

    function it_should_return_tracker_instance() {
        $_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36";
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
        
        $this->create("some-site-id")->shouldReturnAnInstanceOf("Moosend\Tracker");
    }

}