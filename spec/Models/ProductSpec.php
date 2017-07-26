<?php namespace spec\Moosend\Models;

use Moosend\PayloadProperties;
use PhpSpec\ObjectBehavior;

class ProductSpec extends ObjectBehavior
{
    protected $itemCode = '123-Code';
    protected $itemPrice = 22.45;
    protected $itemUrl = 'http://item.com';
    protected $itemQuantity = 1;
    protected $itemTotalPrice = 22.45;
    protected $itemName = 'T-shirt';
    protected $itemImage = 'http://item.com/image';
    protected $properties = ['color' => 'red'];

    public function let()
    {
        $this->beConstructedWith($this->itemCode, $this->itemPrice, $this->itemUrl, $this->itemQuantity, $this->itemTotalPrice, $this->itemName, $this->itemImage, $this->properties);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Models\Product');
    }

    function it_should_implement_serializablepayload_interface() {
        $this->shouldImplement('Moosend\Models\SerializablePayload');
    }

    function it_returns_an_array_created_from_product_properties()
    {
        $arrayToReturn = [
            PayloadProperties::ITEM_CODE => $this->itemCode,
            PayloadProperties::ITEM_PRICE => $this->itemPrice,
            PayloadProperties::ITEM_URL => $this->itemUrl,
            PayloadProperties::ITEM_QUANTITY => $this->itemQuantity,
            PayloadProperties::ITEM_TOTAL => $this->itemTotalPrice,
            PayloadProperties::ITEM_NAME => $this->itemName,
            PayloadProperties::ITEM_IMAGE => $this->itemImage
        ];

        $arrayToReturn = array_merge($this->properties, $arrayToReturn);

        $this->toArray()->shouldReturn($arrayToReturn);
    }

    function it_should_throw_exception_if_itemCode_is_empty() {
        $this->shouldThrow('\InvalidArgumentException')->during('__construct', [null, 0, 'https://some-url', 1, 0]);
    }

    function it_should_throw_exception_if_properties_is_not_array() {
        $this->shouldThrow('\InvalidArgumentException')->during('__construct', ['101', 0, 'https://some-url', 1, 0, 'Some Name', 'https://some-url.com/image.png', false]);
    }
}
