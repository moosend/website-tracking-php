<?php

namespace spec\Moosend;

use GuzzleHttp\Client;
use Moosend\Payload;
use Moosend\VisitorTypes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Moosend\Cookie;
use Moosend\CookieNames;

class TrackerSpec extends ObjectBehavior
{
    function let(Cookie $cookie, Payload $payload, Client $client)
    {
        $this->beConstructedWith($cookie, $payload, $client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Tracker');
    }

    function it_saves_user_id_cookie_for_new_visitors($cookie)
    {
        $cookie->getCookie(CookieNames::USER_ID)->shouldBeCalled();
        $cookie->setCookie(CookieNames::SITE_ID, 'some-site')->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_ID, Argument::type('string'))->shouldBeCalled();
        $cookie->setCookie(CookieNames::VISITOR_TYPE, VisitorTypes::VISITOR_TYPE_NEW)->shouldBeCalled();

        $this->init('some-site');
    }

    function it_changes_visitor_type_when_visitor_returns($cookie)
    {
        $cookie->getCookie(Argument::type('string'))->willReturn('0101');
        $cookie->setCookie(Argument::type('string'), Argument::type('string'))->willReturn(true);

        $this->init('some-site');
    }

    function it_tracks_identify_events($cookie, $payload, $client)
    {
        $email = 'some@mail.com';
        $name = 'some name';
        $props = ['color' => 'blue'];

        //stubs
        $payload->getIdentify($email, $name, $props)->willReturn([
            'email' => $email,
            'name' => $name,
            'properties' => $props,
        ]);

        $client->request('POST', '/identify', Argument::type('array'))->willReturn([
            'success' => 'ok'
        ]);

        //expectations
        $payload->getIdentify($email, $name, $props)->shouldBeCalled();
        $client->request('POST', '/identify', Argument::type('array'))->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_EMAIL, $email)->shouldBeCalled();

        $this->identify($email, $name, $props);
    }

    function it_tracks_add_to_order_events($payload, $client)
    {
        $itemCode = '1';
        $itemPrice = '33eur';
        $props = ['color' => 'blue'];

        //stubs
        $payload->getAddToOrder($itemCode, $itemPrice, $props)->willReturn([
            'properties' => [
                'itemCode' => $itemCode,
                'itemPrice' => $itemPrice,
                'color' => 'blue',
            ]
        ]);

        $client->request('POST', '/track', Argument::type('array'))->willReturn([
            'success' => 'ok'
        ]);

        //expectations
        $payload->getAddToOrder($itemCode, $itemPrice, $props)->shouldBeCalled();
        $client->request('POST', '/track', Argument::type('array'))->shouldBeCalled();

        $this->addToOrder($itemCode, $itemPrice, $props);
    }

    function it_tracks_order_completed_events($payload, $client)
    {

        $props = ['color' => 'blue'];

        $payload->getOrderCompleted($props)->willReturn([
            'properties' => $props
        ]);

        $client->request('POST', '/track', Argument::type('array'))->willReturn([
            'success' => 'ok'
        ]);

        //expectations
        $payload->getOrderCompleted($props)->shouldBeCalled();
        $client->request('POST', '/track', Argument::type('array'))->shouldBeCalled();

        $this->orderCompleted($props);
    }

    function it_tracks_page_view_events($payload, $client)
    {
        $props = ['color' => 'blue'];

        $payload->getPageView('http://google.com', $props)->willReturn([
            'url' => 'http://google.com',
            'properties' => $props
        ]);

        $client->request('POST', '/track', Argument::type('array'))->willReturn([
            'success' => 'ok'
        ]);

        //expectations
        $payload->getPageView('http://google.com', $props)->shouldBeCalled();
        $client->request('POST', '/track', Argument::type('array'))->shouldBeCalled();

        $this->pageView('http://google.com', $props);
    }

    function it_by_passes_has_user_id_when_initialized_with_force_true($cookie)
    {
        $cookie->getCookie(CookieNames::USER_ID)->willReturn('someId');

        $cookie->getCookie(CookieNames::USER_ID)->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_ID, Argument::type('string'))->shouldBeCalled();
        $cookie->setCookie(CookieNames::SITE_ID, 'some-site')->shouldBeCalled();
        $cookie->setCookie(CookieNames::VISITOR_TYPE, VisitorTypes::VISITOR_TYPE_NEW)->shouldBeCalled();

        $this->init('some-site', true);
    }
}

