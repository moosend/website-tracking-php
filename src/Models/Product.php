<?php namespace Moosend\Models;

use Moosend\PayloadProperties;

class Product implements SerializablePayload
{

    /**
     * @var string
     */
    private $itemCode;
    /**
     * @var int|string
     */
    private $itemPrice;
    /**
     * @var string
     */
    private $itemName;
    /**
     * @var string
     */
    private $itemImage;
    /**
     * @var array
     */
    private $properties;
    /**
     * @var string
     */
    private $itemUrl;

    /**
     * @param string $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     *
     * @throws InvalidArgumentException
     */
    public function __construct($itemCode, $itemPrice, $itemUrl, $itemName = '', $itemImage = '', $properties = [])
    {
        if (!$itemCode || !$itemPrice || !$itemUrl) {
            throw new \InvalidArgumentException(PayloadProperties::ITEM_CODE . ' and ' . PayloadProperties::ITEM_PRICE . ' and ' . PayloadProperties::ITEM_URL . ' cannot be empty or null');
        }

        if (!is_numeric($itemPrice)) {

            throw new \InvalidArgumentException('$itemPrice should be a numeric type');
        }

        if (!is_array($properties)) {

            throw new \InvalidArgumentException('$properties should be an array');
        }


        $this->itemCode = (String) $itemCode;
        $this->itemPrice = floatval($itemPrice);
        $this->itemUrl = (String) $itemUrl;

        if (!empty($itemName)) {
            $this->itemName = (String) $itemName;
        }

        if (!empty($itemImage)) {
            $this->itemImage = (String) $itemImage;
        }

        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function toArray()
    {

        $product = [
            PayloadProperties::ITEM_CODE => $this->itemCode,
            PayloadProperties::ITEM_PRICE => $this->itemPrice,
            PayloadProperties::ITEM_URL => $this->itemUrl
        ];

        $product = array_merge($this->properties, $product);

        if(! empty($this->itemName)){
            $product[PayloadProperties::ITEM_NAME] = $this->itemName;
        }

        if(! empty($this->itemImage)){
            $product[PayloadProperties::ITEM_IMAGE] = $this->itemImage;
        }

        return $product;
    }
} 