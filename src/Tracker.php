<?php namespace Moosend;

use Moosend\Models\Order;
use Moosend\Models\Product;
use Moosend\Utils\Uuid;
use Moosend\Utils\Encryption;
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
     * @param string $siteId
     * @param bool $force
     */
    public function init($siteId = '', $force = false)
    {
        $siteId = !empty($siteId) ? $siteId : $this->payload->getSiteId();
        $hasUserId = $this->cookie->getCookie(CookieNames::USER_ID);
        $hasUserId = !empty($hasUserId);

        //store siteId on cookies
        $this->cookie->setCookie(CookieNames::SITE_ID, $siteId);

        if(empty($this->cookie->getCookie(CookieNames::SESSION_ID))) {
            $this->cookie->setCookie(CookieNames::SESSION_ID, $this->replace_dashes($this->payload->getSessionId()), time() + 86400);
        }

        //store campaignId on cookies
        $this->storeCampaignIdIfExists();
        if (!$hasUserId || $force) {
            $newUserId = $this->replace_dashes(Uuid::v4());
            $this->cookie->setCookie(CookieNames::USER_ID, $newUserId, time() + 60 * 60 * 24 * 3650);
            return;
        }
    }

    /**
     * @param string $email
     * @param string string $name
     * @param array $properties
     * @param bool async
     * @return mixed
     */
    public function identify($email, $name = '', $properties = [], $async = false)
    {
        $encryptedEmail = Encryption::encode($email);

        $payload = $this->payload->getIdentify($encryptedEmail, $name, $properties);

        //set user email cookie
        $this->cookie->setCookie(CookieNames::USER_EMAIL, $encryptedEmail);

        return $this->client->{$async === true ? 'requestAsync' : 'request'}('POST', 'identify', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * @param string $url
     * @param array $properties
     * @param bool async
     * @return mixed
     * @throws \Exception
     */
    public function pageView($url, $properties = [], $async = false)
    {
        $payload = $this->payload->getPageView($url, $properties);

        return $this->client->{$async === true ? 'requestAsync' : 'request'}('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * @param string|int $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param int $itemQuantity
     * @param int $itemTotal
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     * @param bool $async
     * @return mixed
     */
    public function addToOrder($itemCode, $itemPrice, $itemUrl, $itemQuantity = 1, $itemTotal = 0, $itemName = '', $itemImage = '', $properties = [], $async = false)
    {
        if (empty($itemCode)) {
            throw new \InvalidArgumentException('$itemCode should not be empty');
        }

        if (!is_numeric($itemPrice)) {
            $itemPrice = 0;
        }

        if (empty($itemUrl)) {
            throw new \InvalidArgumentException('$itemUrl should not be empty');
        }

        if (empty($itemQuantity)) {
            throw new \InvalidArgumentException('$itemQuantity should not be empty');
        }

        if (!is_numeric($itemQuantity)) {
            $itemQuantity = 1;
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

        $payload = $this->payload->getAddToOrder(new Product($itemCode, $itemPrice, $itemUrl, $itemQuantity, $itemTotal, $itemName, $itemImage, $properties));

        return $this->client->{$async === true ? 'requestAsync' : 'request'}('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * @param string|int $itemCode
     * @param number $itemPrice
     * @param string $itemUrl
     * @param int $itemTotal
     * @param string $itemName
     * @param string $itemImage
     * @param array $properties
     * @param bool $async
     * @return mixed
     */
    public function removeFromOrder($itemCode, $itemPrice, $itemUrl, $itemTotal = 0, $itemName = '', $itemImage = '', $properties = [], $async = false) {
        return $this->addToOrder($itemCode, $itemPrice, $itemUrl, -1, $itemTotal, $itemName, $itemImage, $properties, $async);
    }

    /**
     * @param Order $order
     * @param bool $async
     * @return mixed
     */
    public function orderCompleted(Order $order, $async = false)
    {
        $payload = $this->payload->getOrderCompleted($order);

        return $this->client->{$async === true ? 'requestAsync' : 'request'}('POST', 'track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Creates an order collection|aggregate
     *
     * @param number $orderTotal
     * @return Order
     */
    public function createOrder($orderTotal)
    {
        return new Order($orderTotal);
    }

    /**
     * @param string $email
     * @return boolean
     */
    public function isIdentified($email)
    {
        $userId = $this->cookie->getCookie(CookieNames::USER_ID);
        $storedEmail = $this->getEmail();

        if (empty($userId) || empty($storedEmail)) {
            return false;
        }

        if ($storedEmail != $email) {
            return false;
        }

        return true;
    }

    /**
    * @param string $string
    */
    public function isValidUUID($string)
    {
        $validUUIDRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        return preg_match($validUUIDRegex, $string) || preg_match($validUUIDRegex, preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i', "$1-$2-$3-$4-$5", $string));
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

    private function replace_dashes($string) {
        $string = str_replace("-", "", $string);
        return $string;
    }

    /**
     * @return string
     */
    private function getEmail() {
        $email = urlencode(Encryption::decode($this->cookie->getCookie(CookieNames::USER_EMAIL)));
        return rawurldecode($email);
    }

    /**
     * @param string $siteId
     */
    public function addSubscriptionForms($siteId) {
        ?>
        <script>
            // Moosend Tracking and Forms library
            !function (t, n, e, o, a) {
                function d(t) {
                    var n = ~~(Date.now() / 3e5), o = document.createElement(e);
                    o.async = !0, o.src = t + "?ts=" + n;
                    var a = document.getElementsByTagName(e)[0];
                    a.parentNode.insertBefore(o, a)
                }

                t.MooTrackerObject = a, t[a] = t[a] || function () {
                    return t[a].q ? void t[a].q.push(arguments) : void (t[a].q = [arguments])
                }, window.attachEvent ? window.attachEvent("onload", d.bind(this, o)) : window.addEventListener("load", d.bind(this, o), !1)
            }(window, document, "script", "//cdn.stat-track.com/statics/moosend-tracking.min.js", "mootrack");
            mootrack('init', '<?php echo $siteId; ?>');
        </script>
        <?php
    }
}
