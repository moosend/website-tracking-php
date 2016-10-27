<?php namespace Moosend;

use Moosend\Models\Order;
use Moosend\Models\Product;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;

/**
 * Class Tracker
 * @package Moosend
 */
class Tracker
{

    /**
     * @var Cookie
     * @var Payload
     * @var Client
     */
    private $cookie;
    private $payload;
    private $client;

    public function __construct(Cookie $cookie, Payload $payload, Client $client)
    {
        $this->cookie = $cookie;
        $this->payload = $payload;
        $this->client = $client;
    }

    /**
     * Stores a cookie that tells if a user is a new one or returned
     *
     * @param $siteId
     */
    public function init($siteId = '', $force = false)
    {
        $siteId = !empty($siteId) ? $siteId : $this->payload->getSiteId();
        $hasUserId = $this->cookie->getCookie(CookieNames::USER_ID);
        $hasUserId = !empty($hasUserId);

        //store siteId on cookies
        $this->cookie->setCookie(CookieNames::SITE_ID, $siteId);

        //store campaignId on cookies
        $this->storeCampaignIdIfExists();

        if (!$hasUserId || $force) {

            $this->cookie->setCookie(CookieNames::USER_ID, Uuid::uuid4()->toString());
            return;
        }
    }

    /**
     * @param $email
     * @param string $name
     * @param array $properties
     * @return mixed
     */
    public function identify($email, $name = '', $properties = [])
    {
        $payload = $this->payload->getIdentify($email, $name, $properties);

        //set user email cookie
        $this->cookie->setCookie(CookieNames::USER_EMAIL, $email);

        return $this->client->request('POST', 'identify', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * @param $url
     * @param array $properties
     * @return mixed
     * @throws \Exception
     */
    public function pageView($url, $properties = [])
    {
        $payload = $this->payload->getPageView($url, $properties);

        return $this->client->request('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * @param $itemCode
     * @param number $itemPrice
     * @param array $properties
     * @param string $itemUrl
     * @param string $itemName
     * @param string $itemImage
     * @return mixed
     */
    public function addToOrder($itemCode, $itemPrice, $itemUrl, $itemName = '', $itemImage = '', $properties = [])
    {

        if (empty($itemCode)) {
            throw new \InvalidArgumentException('$itemCode should not be empty');
        }

        if (!is_numeric($itemPrice)) {

            throw new \InvalidArgumentException('$itemPrice should be a numeric type');
        }

        if (empty($itemUrl)) {
            throw new \InvalidArgumentException('$itemUrl should not be empty');
        }

        if (!is_array($properties)) {

            throw new \InvalidArgumentException('$properties should be an array');
        }

        if (!empty($itemName)) {

            $properties[PayloadProperties::ITEM_NAME] = $itemName;
        }

        if (!empty($itemImage)) {

            $properties[PayloadProperties::ITEM_IMAGE] = $itemImage;
        }

        $payload = $this->payload->getAddToOrder(new Product($itemCode, $itemPrice, $itemUrl, $itemName, $itemImage, $properties));

        return $this->client->request('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function orderCompleted(Order $order)
    {
        $payload = $this->payload->getOrderCompleted($order);

        return $this->client->request('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * Creates an order collection|aggregate
     *
     * @return Order
     */
    public function createOrder()
    {

        return new Order();
    }

    /**
     * @param string $email
     * @return boolean
     */
    public function isIdentified($email)
    {
        $userId = $this->cookie->getCookie(CookieNames::USER_ID);
        $storedEmail = $this->cookie->getCookie(CookieNames::USER_EMAIL);

        if (empty($userId) || empty($storedEmail)) {
            return false;
        }

        if ($storedEmail != $email) {
            return false;
        }

        return true;
    }

    /**
     * Store $campaignId on cookies
     *
     * @param $campaignId
     */
    public function storeCampaignId($campaignId)
    {
        if (!preg_match('/^\{?[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}\}?$/', $campaignId)) {
            throw new \InvalidArgumentException('$campaignId should be a valid uuid');
        }

        $this->cookie->setCookie(CookieNames::CAMPAIGN_ID, $campaignId);
    }

    private function storeCampaignIdIfExists()
    {
        if (isset($_GET[QueryStringParams::CAMPAIGN_ID]) && !empty($_GET[QueryStringParams::CAMPAIGN_ID])) {
            $this->storeCampaignId($_GET[QueryStringParams::CAMPAIGN_ID]);
        }
    }
}