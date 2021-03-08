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
    private $itemUrl;
    /**
     * @var int
     */
    private $itemQuantity;
    /**
     * @var int|string
     */
    private $itemTotalPrice;
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
     * @param string $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param int $itemQuantity
     * @param int $itemTotalPrice
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     */
    public function __construct($itemCode, $itemPrice, $itemUrl, $itemQuantity = 1, $itemTotalPrice = 0, $itemName = '', $itemImage = '', $properties = [])
    {
        if (!$itemCode || !$itemUrl) {
            throw new \InvalidArgumentException(PayloadProperties::ITEM_CODE . ' and ' . PayloadProperties::ITEM_URL . ' cannot be empty or null');
        }

        if (!is_numeric($itemPrice)) {
            $itemPrice = 0;
        }

        if (!is_numeric($itemQuantity)) {
            $itemQuantity = 1;
        }

        if (!is_numeric($itemTotalPrice)) {
            $itemTotalPrice = 0;
        }

        if (!is_array($properties)) {
            throw new \InvalidArgumentException('$properties should be an array');
        }

        $this->itemCode = (String)$itemCode;
        $this->itemPrice = floatval($itemPrice);
        $this->itemUrl = (String)$itemUrl;
        $this->itemQuantity = intval($itemQuantity);
        $this->itemTotalPrice = floatval($itemTotalPrice);

        if (!empty($itemName)) {
            $this->itemName = (String)$itemName;
        }

        if (!empty($itemImage)) {
            $this->itemImage = (String)$itemImage;
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
            PayloadProperties::ITEM_URL => $this->itemUrl,
            PayloadProperties::ITEM_QUANTITY => $this->itemQuantity,
            PayloadProperties::ITEM_TOTAL => $this->itemTotalPrice
        ];

        $product = array_merge($this->properties, $product);

        if (!empty($this->itemName)) {
            $product[PayloadProperties::ITEM_NAME] = $this->itemName;
        }

        if (!empty($this->itemImage)) {
            $product[PayloadProperties::ITEM_IMAGE] = $this->itemImage;
        }

        return $product;
    }
}
