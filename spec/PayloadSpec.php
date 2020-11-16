<?php namespace spec\Moosend;

use Moosend\Cookie;
use Moosend\ActionTypes;
use Moosend\CookieNames;
use Moosend\Models\Order;
use Moosend\PayloadProperties;
use Moosend\Models\Product;
use Moosend\Utils\Uuid;
use Moosend\Utils\Encryption;
use PhpSpec\ObjectBehavior;

class PayloadSpec extends ObjectBehavior
{
    function let(Cookie $cookie)
    {
        $this->beConstructedWith($cookie, 'user1', Uuid::v4(), Uuid::v4());

        $cookie->getCookie(CookieNames::USER_EMAIL)->willReturn('some@mail.com');
        $cookie->getCookie(CookieNames::USER_ID)->willReturn(Uuid::v4());
        $cookie->getCookie(CookieNames::CAMPAIGN_ID)->willReturn(Uuid::v4());
        $cookie->getCookie(CookieNames::SESSION_ID)->willReturn(Uuid::v4());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Payload');
    }

    function it_should_throw_exception_if_site_id_is_empty(Cookie $cookie) {
        $this->shouldThrow('\Exception')->during('__construct', [$cookie, null, null, null]);
    }

    function it_generates_an_identify_payload_by_e_mail()
    {
        $this->getIdentify('some@mail.com')->shouldHaveKeyWithValue(PayloadProperties::EMAIL, Encryption::decode('some@mail.com'));
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

    function it_generates_added_to_order_payload_by_itemCode_and_itemPrice()
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemTotalprice = 22.45;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = ['color' => 'red'];

        $propertiesAfter = [
            [
                PayloadProperties::PRODUCT => [
                    'color' => 'red',
                    PayloadProperties::ITEM_CODE => $itemCode,
                    PayloadProperties::ITEM_PRICE => $itemPrice,
                    PayloadProperties::ITEM_URL => $itemUrl,
                    PayloadProperties::ITEM_QUANTITY => $itemQuantity,
                    PayloadProperties::ITEM_TOTAL => $itemTotalprice,
                    PayloadProperties::ITEM_NAME => $itemName,
                    PayloadProperties::ITEM_IMAGE => $itemImage
                ]
            ]
        ];

        $product = new Product($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotalprice, $itemName, $itemImage, $properties);

        $this->getAddToOrder($product)->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, $propertiesAfter);
        $this->getAddToOrder($product)->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, ActionTypes::ADDED_TO_ORDER);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_generates_added_to_order_payload_default_values()
    {
        $itemCode = '123-Code';
        $itemUrl = 'http://item.com';
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = ['color' => 'red'];

        $propertiesAfter = [
            [
                PayloadProperties::PRODUCT => [
                    'color' =>  'red',
                    PayloadProperties::ITEM_CODE => $itemCode,
                    PayloadProperties::ITEM_PRICE => floatval(0),
                    PayloadProperties::ITEM_URL => $itemUrl,
                    PayloadProperties::ITEM_QUANTITY => 1,
                    PayloadProperties::ITEM_TOTAL => floatval(0),
                    PayloadProperties::ITEM_NAME => $itemName,
                    PayloadProperties::ITEM_IMAGE => $itemImage
                ]
            ]
        ];

        $product = new Product($itemCode, null, $itemUrl, null, null, $itemName, $itemImage, $properties);

        $this->getAddToOrder($product)->shouldHaveKeyWithValue(PayloadProperties::PROPERTIES, $propertiesAfter);
        $this->getAddToOrder($product)->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, ActionTypes::ADDED_TO_ORDER);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::PROPERTIES);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getAddToOrder($product)->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_generates_order_completed_payload()
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemTotalprice = 22.45;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = ['color' => 'red'];

        $orderTotal = 120;

        $propertiesAfter = [
            [
                PayloadProperties::ORDER_TOTAL_PRICE => $orderTotal,
                PayloadProperties::PRODUCTS => [
                    [
                        PayloadProperties::ITEM_CODE => $itemCode,
                        PayloadProperties::ITEM_PRICE => $itemPrice,
                        PayloadProperties::ITEM_URL => $itemUrl,
                        PayloadProperties::ITEM_QUANTITY => $itemQuantity,
                        PayloadProperties::ITEM_NAME => $itemName,
                        PayloadProperties::ITEM_IMAGE => $itemImage,
                        PayloadProperties::ITEM_TOTAL => $itemTotalprice,
                        PayloadProperties::PROPERTIES => $properties
                    ]
                ]
            ]
        ];

        $order = new Order($orderTotal);

        $order->addProduct($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotalprice, $itemName, $itemImage, $properties);

        $this->getOrderCompleted($order)->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, ActionTypes::ORDER_COMPLETED);
        $this->getOrderCompleted($order)->shouldHaveKey(PayloadProperties::PROPERTIES);
        $this->getOrderCompleted($order)->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getOrderCompleted($order)->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getOrderCompleted($order)->shouldHaveKey(PayloadProperties::CONTACT_ID);
    }

    function it_should_throw_exception_if_order_has_no_products() {
        $order = new Order(0);
        $this->shouldThrow('\Exception')->duringGetOrderCompleted($order);
    }

    function it_generates_custom_events_payload()
    {
        $this->getCustom('ORDER_CANCELED')->shouldHaveKeyWithValue(PayloadProperties::ACTION_TYPE, 'ORDER_CANCELED');
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey(PayloadProperties::SITE_ID);
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey(PayloadProperties::EMAIL);
        $this->getCustom('ORDER_CANCELED')->shouldHaveKey(PayloadProperties::CONTACT_ID);
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

    function it_should_throw_exception_if_page_view_url_is_empty() {
        $this->shouldThrow('\Exception')->duringGetPageView(null);
    }
}
