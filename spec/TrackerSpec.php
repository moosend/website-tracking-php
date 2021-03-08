<?php namespace spec\Moosend;

use GuzzleHttp\Client;
use Moosend\Models\Order;
use Moosend\Models\Product;
use Moosend\Payload;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Moosend\Cookie;
use Moosend\CookieNames;
use Moosend\Utils\Encryption;
use GuzzleHttp\Promise\Promise;

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
        $cookie->setCookie(CookieNames::USER_ID, Argument::type('string'), time() + 60 * 60 * 24 * 3650)->shouldBeCalled();
        $cookie->getCookie(CookieNames::SESSION_ID)->shouldBeCalled();
        $cookie->setCookie(CookieNames::SESSION_ID, "", time() + 86400)->shouldBeCalled();

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

        $encryptedEmail = Encryption::encode($email);

        //stubs
        $payload->getIdentify($encryptedEmail, $name, $props)->willReturn([
            'email' => $email,
            'name' => $name,
            'properties' => $props,
        ]);

        $client->request('POST', 'identify', Argument::type('array'))->shouldBeCalled();

        //expectations
        $payload->getIdentify($encryptedEmail, $name, $props)->shouldBeCalled();
        $client->request('POST', 'identify', Argument::type('array'))->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_EMAIL, $encryptedEmail)->shouldBeCalled();

        $this->identify($email, $name, $props);
    }

    function it_tracks_asynchronous_identify_events($cookie, $payload, $client)
    {
        $email = 'some@mail.com';
        $name = 'some name';
        $props = ['color' => 'blue'];

        $encryptedEmail = Encryption::encode($email);

        //stubs
        $payload->getIdentify($encryptedEmail, $name, $props)->willReturn([
            'email' => $email,
            'name' => $name,
            'properties' => $props,
        ]);

        $client->requestAsync('POST', 'identify', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getIdentify($encryptedEmail, $name, $props)->shouldBeCalled();
        $client->requestAsync('POST', 'identify', Argument::type('array'))->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_EMAIL, $encryptedEmail)->shouldBeCalled();

        $this->identify($email, $name, $props, true);
    }

    function it_tracks_add_to_order_events($payload, $client)
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        //stubs
        $payload->getAddToOrder(Argument::type(Product::class))->willReturn([]);

        //$client->request('POST', 'track', Argument::type('array'))->willReturn();

        //expectations
        $payload->getAddToOrder(Argument::type(Product::class))->shouldBeCalled();
        $client->request('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->addToOrder($itemCode, $itemPrice, $itemUrl,$itemQuantity, null, $itemName, $itemImage, $properties);
    }

    function it_tracks_remove_from_order_events($payload, $client)
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemTotal = 22.45;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        //$client->request('POST', 'track', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getAddToOrder(Argument::type(Product::class))->shouldBeCalled();
        $payload->getAddToOrder(Argument::that(function($obj) {
            $productArray = $obj->toArray();
            if ($productArray['itemQuantity'] === -1) {
                return true;
            };
            return false;
        }))->shouldBeCalled();
        $client->request('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->removeFromOrder($itemCode, $itemPrice, $itemUrl, null, $itemName, $itemImage, $properties);
    }

    function it_tracks_asynchronous_add_to_order_events($payload, $client)
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        //stubs
        $payload->getAddToOrder(Argument::type(Product::class))->willReturn([]);

        $client->requestAsync('POST', 'track', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getAddToOrder(Argument::type(Product::class))->shouldBeCalled();
        $client->requestAsync('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->addToOrder($itemCode, $itemPrice, $itemUrl,$itemQuantity, null, $itemName, $itemImage, $properties, true);
    }

    function it_throws_exception_if_itemCode_is_empty() {
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        $this->shouldThrow('\InvalidArgumentException')->duringAddToOrder(null, $itemPrice, $itemUrl,$itemQuantity, null, $itemName, $itemImage, $properties);
    }

    function it_throws_exception_if_itemUrl_is_empty() {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemQuantity = 1;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        $this->shouldThrow('\InvalidArgumentException')->duringAddToOrder($itemCode, $itemPrice, null, $itemQuantity, null, $itemName, $itemImage, $properties);
    }

    function it_throws_exception_if_itemQuantity_is_empty() {
        $itemCode = '123-Code';
        $itemUrl = 'http://item.com';
        $itemPrice = 22.45;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = [ 'color' => 'red' ];

        $this->shouldThrow('\InvalidArgumentException')->duringAddToOrder($itemCode, $itemPrice, $itemUrl, null, null, $itemName, $itemImage, $properties);
    }

    function it_throws_exception_if_properties_is_not_an_array() {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = false;

        $this->shouldThrow('\InvalidArgumentException')->duringAddToOrder($itemCode, $itemPrice, $itemUrl, $itemQuantity, null, $itemName, $itemImage, $properties);
    }

    function it_tracks_order_completed_events($payload, $client)
    {
        $orderTotal = 120;

        $order = new Order($orderTotal);

        $payload->getOrderCompleted(Argument::type(Order::class))->willReturn([]);

        //$client->request('POST', 'track', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getOrderCompleted(Argument::exact($order))->shouldBeCalled();
        $client->request('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->orderCompleted($order);
    }

    function it_tracks_asynchronous_order_completed_events($payload, $client)
    {
        $orderTotal = 120;

        $order = new Order($orderTotal);

        $payload->getOrderCompleted(Argument::type(Order::class))->willReturn([]);

        $client->requestAsync('POST', 'track', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getOrderCompleted(Argument::exact($order))->shouldBeCalled();
        $client->requestAsync('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->orderCompleted($order, true);
    }

    function it_tracks_page_view_events($payload, $client)
    {
        $props = ['color' => 'blue'];

        $payload->getPageView('http://google.com', $props)->willReturn([
            'url' => 'http://google.com',
            'properties' => $props
        ]);

        //$client->request('POST', 'track', Argument::type('array'))->willReturn(new Promise());

        //expectations
        $payload->getPageView('http://google.com', $props)->shouldBeCalled();
        $client->request('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->pageView('http://google.com', $props);
    }

    function it_by_passes_has_user_id_when_initialized_with_force_true($cookie)
    {
        $cookie->getCookie(CookieNames::USER_ID)->willReturn('someId');

        $cookie->getCookie(CookieNames::USER_ID)->shouldBeCalled();
        $cookie->setCookie(CookieNames::USER_ID, Argument::type('string'), time() + 60 * 60 * 24 * 3650)->shouldBeCalled();
        $cookie->setCookie(CookieNames::SITE_ID, 'some-site')->shouldBeCalled();
        $cookie->getCookie(CookieNames::SESSION_ID)->shouldBeCalled();
        $cookie->setCookie(CookieNames::SESSION_ID, "", time() + 86400)->shouldBeCalled();

        $this->init('some-site', true);
    }

    function it_does_an_asynchronous_pageview_request_when_async_is_set_to_true($payload, $client)
    {
        $props = ['color' => 'blue'];

        $payload->getPageView('http://google.com', $props)->willReturn([
            'url' => 'http://google.com',
            'properties' => $props
        ]);

        $client->requestAsync('POST', 'track', Argument::type('array'))->willReturn(new Promise());
        //expectations
        $payload->getPageView('http://google.com', $props)->shouldBeCalled();
        $client->requestAsync('POST', 'track', Argument::type('array'))->shouldBeCalled();

        $this->pageView('http://google.com', $props, true);
    }

    function it_should_throw_exception_if_campaign_id_has_invalid_uuid() {
        $this->shouldThrow('\InvalidArgumentException')->duringStoreCampaignId(123);
    }

    function it_should_store_campaign_id() {
        $this->shouldNotThrow('\InvalidArgumentException')->duringStoreCampaignId("05809235-13f1-44b7-bd65-b523dd33c5f1");
    }

    function it_should_return_true_if_uuid_is_valid() {
        $this->isValidUUID("05809235-13f1-44b7-bd65-b523dd33c5f1")->shouldReturn(true);
    }

    function it_should_return_false_if_uuid_is_invalid() {
        $this->isValidUUID("05809235")->shouldReturn(false);
    }

    function it_should_return_true_if_uuid_is_without_dashes() {
        $this->isValidUUID("0580923513f144b7bd65b523dd33c5f1")->shouldReturn(true);
    }

    function it_creates_order() {
        $this->createOrder(120)->shouldReturnAnInstanceOf("Moosend\Models\Order");
    }

    function it_should_render_js_snippet() {
        $this->addSubscriptionForms('123')->shouldBeNull();
    }
}

