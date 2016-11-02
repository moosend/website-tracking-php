<?php

namespace spec\Moosend\Models;

use Moosend\Models\Product;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OrderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Moosend\Models\Order');
    }

    function it_returns_an_array_created_from_products()
    {
        $itemCode = '123-Code';
        $itemPrice = 22.45;
        $itemUrl = 'http://item.com';
        $itemQuantity = 1;
        $itemTotalPrice = 22.45;
        $itemName = 'T-shirt';
        $itemImage = 'http://item.com/image';
        $properties = ['color' => 'red'];

        $product = new Product($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotalPrice, $itemName, $itemImage, $properties);

        $this->addProduct($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotalPrice, $itemName, $itemImage, $properties)->shouldReturnAnInstanceOf(Product::class);

        $this->shouldHaveProducts();

        $this->toArray()->shouldReturn([
            $product->toArray()
        ]);
    }
}
