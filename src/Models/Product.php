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
     * @var int
     */
    private $itemQuantity;
    /**
     * @var int|string
     */
    private $itemTotal;

    /**
     * @param string $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param int $itemQuantity
     * @param int $itemTotal
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     *
     */
    public function __construct($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotal = 0, $itemName = '', $itemImage = '', $properties = [])
    {
        if (!$itemCode || !$itemPrice || !$itemUrl || !$itemQuantity) {

            throw new \InvalidArgumentException(PayloadProperties::ITEM_CODE . ' and ' . PayloadProperties::ITEM_PRICE . ' and ' . PayloadProperties::ITEM_URL . ' and ' . PayloadProperties::ITEM_QUANTITY . ' cannot be empty or null');
        }

        if (!is_numeric($itemPrice)) {

            throw new \InvalidArgumentException('$itemPrice should be a numeric type');
        }

        if (!is_int($itemQuantity)) {

            throw new \InvalidArgumentException('$itemQuantity should be a integer type');
        }

        if (!!$itemTotal && !is_numeric($itemTotal)) {

            throw new \InvalidArgumentException('$itemTotal should be a numeric type');
        }

        if (!is_array($properties)) {

            throw new \InvalidArgumentException('$properties should be an array');
        }


        $this->itemCode = (String)$itemCode;
        $this->itemPrice = floatval($itemPrice);
        $this->itemUrl = (String)$itemUrl;
        $this->itemQuantity = intval($itemQuantity);

        if($itemTotal){
            $this->itemTotal = floatval($itemTotal);
        }

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
        ];

        $product = array_merge($this->properties, $product);

        if (!empty($this->itemName)) {
            $product[PayloadProperties::ITEM_NAME] = $this->itemName;
        }

        if (!empty($this->itemImage)) {
            $product[PayloadProperties::ITEM_IMAGE] = $this->itemImage;
        }

        if (!empty($this->itemTotal)) {
            $product[PayloadProperties::ITEM_TOTAL] = $this->itemTotal;
        }

        return $product;
    }
} 