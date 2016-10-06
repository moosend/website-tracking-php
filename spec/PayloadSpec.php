<?php

namespace spec\Moosend;

use Moosend\Cookie;
use Moosend\ActionTypes;
use Moosend\CookieNames;
use Moosend\VisitorTypes;
use Moosend\PayloadProperties;
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

        $this->getIdentify('some@mail.com')->shouldHaveKeyWithValue(PayloadProperties::EMAIL, 'some@mail.com');
        $this->getIdentify('some@mail.com', 'John Doe')->shouldHaveKey(PayloadProperties::NAME);
        $this->getIdentify('some@mail.com')->shouldHaveKey(PayloadProperties::SITE_ID);
    }

    function it_throws_exception_when_generating_identify_without_e_mail()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringGetIdentify('');
    }

    function it_generates_an_identify_payload_by_e_mail_and_name()
    {

        $this->getIdentify('some@mail.com', 'John Doe')->shouldHaveKeyWithValue(PayloadProperties::NAME, 'John Doe');
    }

    function it_generates_an_identify_payload_by_e_mail_and_name_and_extra_properties()
    {
        $properties = [
            'color' => 'blue',
            'role' => 'customer'
        ];

        $this->getIdentify('some@mail.com', 'John Doe', $properties)->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, $properties);
    }

    function it_generates_order_payload_by_itemCode_and_itemPrice()
    {
        $propertiesAfter = [
            PayloadProperties::ITEM_CODE => '01',
            PayloadProperties::ITEM_PRICE => '35eur'
        ];

        $this->getAddToOrder('01', '35eur')->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, $propertiesAfter);
        $this->getAddToOrder('01', '35eur')->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, ActionTypes::ADD_TO_ORDER);
        $this->getAddToOrder('01', '35eur')->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getAddToOrder('01', '35eur')->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getAddToOrder('01', '35eur')->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_generates_order_payload_by_itemCode_itemPrice_and_extra_props()
    {
        $propertiesAfter = [
            PayloadProperties::ITEM_CODE => '01',
            PayloadProperties::ITEM_PRICE => '35eur',
            'color' => 'blue'
        ];

        $this->getAddToOrder('01', '35eur', ['color' => 'blue'])->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, $propertiesAfter);
    }

    function it_generates_order_completed_payload()
    {
        $this->getOrderCompleted()->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, ActionTypes::ORDER_COMPLETED);
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_generates_order_completed_payload_with_extra_props()
    {
        $this->getOrderCompleted(['color' => 'blue'])->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, ['color' => 'blue']);
    }

    function it_generates_custom_events_payload()
    {
        $this->getCustom('ORDER_CANCELED')->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, 'ORDER_CANCELED');
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getOrderCompleted()->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_generates_custom_events_payload_with_extra_props()
    {
        $this->getCustom('ORDER_CANCELED', ['color' => 'blue'])->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, ['color' => 'blue']);
    }

    function it_generates_page_view_payload()
    {
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKeyWithValue('Url', 'http://someurl.com');
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getPageView('http://someurl.com', ['color' => 'blue'])->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }
}