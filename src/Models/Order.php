<?php namespace Moosend\Models;

class Order implements SerializablePayload
{

    protected $order = [];

    /**
     * Adds a new product to order collection
     *
     * @param string $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     * @return \Moosend\Models\Product
     */
    public function addProduct($itemCode, $itemPrice, $itemUrl, $itemName = '', $itemImage = '', $properties = [])
    {
        $product = new Product($itemCode, $itemPrice, $itemUrl, $itemName, $itemImage, $properties);

        array_push($this->order, $product);

        return $product;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($product) {

            return $product->toArray();
        }, $this->order);
    }

    /**
     * @return bool
     */
    public function hasProducts()
    {
        return !!count($this->order);
    }
}