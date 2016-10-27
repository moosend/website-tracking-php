<?php

namespace spec\Moosend\Models;

use Moosend\PayloadProperties;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductSpec extends ObjectBehavior
{
    protected $itemCode = '123-Code';
    protected $itemPrice = 22.45;
    protected $itemUrl = 'http://item.com';
    protected $itemName = 'T-shirt';
    protected $itemImage = 'http://item.com/image';
    protected $properties = ['color' => 'red'];

    public function let()
    {
        $this->beConstructedWith($this->itemCode, $this->itemPrice, $this->itemUrl, $this->itemName, $this->itemImage, $this->properties);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Models\Product');
    }

    function it_returns_an_array_created_from_product_properties()
    {
        $arrayToReturn = [
            PayloadProperties::ITEM_CODE => $this->itemCode,
            PayloadProperties::ITEM_PRICE => $this->itemPrice,
            PayloadProperties::ITEM_URL => $this->itemUrl,
            PayloadProperties::ITEM_NAME => $this->itemName,
            PayloadProperties::ITEM_IMAGE => $this->itemImage,
        ];

        $arrayToReturn = array_merge($this->properties, $arrayToReturn);

        $this->toArray()->shouldReturn($arrayToReturn);
    }
}
