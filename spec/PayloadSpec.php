<?php

namespace spec\Moosend;

use Moosend\Cookie;
use Moosend\ActionTypes;
use Moosend\CookieNames;
use Moosend\VisitorTypes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sinergi\BrowserDetector\Language;

class PayloadSpec extends ObjectBehavior
{
    function let(Cookie $cookie, Language $language)
    {
        $this->beConstructedWith($cookie, $language, 'user1', 'site1');
        $cookie->getCookie(CookieNames::USER_EMAIL)->willReturn('some@mail.com');
        $cookie->getCookie(CookieNames::USER_ID)->willReturn('1');
        $cookie->getCookie(CookieNames::VISITOR_TYPE)->willReturn(VisitorTypes::VISITOR_TYPE_RETURNED);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Payload');
    }

    function it_generates_an_identify_payload_by_e_mail()
    {

        $this->getIdentify('some@mail.com')->shouldHaveKeyWithValue('email', 'some@mail.com');
        $this->getIdentify('some@mail.com')->shouldHaveKey('userId');
        $this->getIdentify('some@mail.com')->shouldHaveKey('siteId');
        $this->getIdentify('some@mail.com')->shouldHaveKey('sessionNumber');
    }

    function it_throws_exception_when_generating_identify_without_e_mail()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringGetIdentify('');
    }

    function it_generates_an_identify_payload_by_e_mail_and_name()
    {

        $this->getIdentify('some@mail.com', 'John Doe')->shouldHaveKeyWithValue('name', 'John Doe');
    }

    function it_generates_an_identify_payload_by_e_mail_and_name_and_extra_properties()
    {
        $properties = [
            'color' => 'blue',
            'role' => 'customer'
        ];

        $this->getIdentify('some@mail.com', 'John Doe', $properties)->shouldHaveKeyWithValue('properties', $properties);
    }

    function it_generates_order_payload_by_itemCode_and_itemPrice()
    {

        $propertiesAfter = [
            'itemCode' => '01',
            'itemPrice' => '35eur'
        ];

        $this->getAddToOrder('01', '35eur')->shouldHaveKeyWithValue('properties', $propertiesAfter);
        $this->getAddToOrder('01', '35eur')->shouldHaveKeyWithValue('actionType', ActionTypes::ADD_TO_ORDER);
        $this->getAddToOrder('01', '35eur')->shouldHaveKey('siteId');
        $this->getAddToOrder('01', '35eur')->shouldHaveKey('email');
        $this->getAddToOrder('01', '35eur')->shouldHaveKey('session');
        $this->getAddToOrder('01', '35eur')->shouldHaveKey('userId');
    }

    function it_generates_order_payload_by_itemCode_itemPrice_and_extra_props()
    {

        $propertiesAfter = [
            'itemCode' => '01',
            'itemPrice' => '35eur',
            'color' => 'blue'
        ];

        $this->getAddToOrder('01', '35eur', ['color' => 'blue'])->shouldHaveKeyWithValue('properties', $propertiesAfter);
    }

    function it_generates_order_completed_payload()
    {

        $this->getOrderCompleted()->shouldHaveKeyWithValue('actionType', ActionTypes::ORDER_COMPLETED);
        $this->getOrderCompleted()->shouldHaveKey('siteId');
        $this->getOrderCompleted()->shouldHaveKey('email');
        $this->getOrderCompleted()->shouldHaveKey('session');
        $this->getOrderCompleted()->shouldHaveKey('userId');
    }

    function it_generates_order_completed_payload_with_extra_props()
    {
        $this->getOrderCompleted(['color' => 'blue'])->shouldHaveKeyWithValue('properties', ['color' => 'blue']);
    }

    function it_generates_custom_events_payload()
    {
        $this->getCustom('ORDER_CANCELED')->shouldHaveKeyWithValue('actionType', 'ORDER_CANCELED');
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey('siteId');
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey('email');
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey('session');
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey('userId');
    }

    function it_generates_custom_events_payload_with_extra_props()
    {
        $this->getCustom('ORDER_CANCELED', ['color' => 'blue'])->shouldHaveKeyWithValue('properties', ['color' => 'blue']);
    }

    function it_generates_page_view_payload()
    {
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKeyWithValue('Url', 'http://someurl.com');
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey('siteId');
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey('email');
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey('session');
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey('userId');
    }
}