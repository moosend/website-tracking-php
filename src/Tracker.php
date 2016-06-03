<?php namespace Moosend;

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
    public function init($siteId, $force = false)
    {

        $hasUserId = $this->cookie->getCookie(CookieNames::USER_ID);
        $hasUserId = !empty($hasUserId);

        $this->cookie->setCookie(CookieNames::SITE_ID, $siteId);

        if (!$hasUserId || $force) {

            $this->cookie->setCookie(CookieNames::USER_ID, Uuid::uuid4()->toString());
            $this->cookie->setCookie(CookieNames::VISITOR_TYPE, VisitorTypes::VISITOR_TYPE_NEW);
            return;
        }

        $this->cookie->setCookie(CookieNames::VISITOR_TYPE, VisitorTypes::VISITOR_TYPE_RETURNED);
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

        return $this->client->request('POST', '/identify', [
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

        return $this->client->request('POST', '/track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * @param $itemCode
     * @param $itemPrice
     * @param array $properties
     * @return mixed
     */
    public function addToOrder($itemCode, $itemPrice, $properties = [])
    {
        $payload = $this->payload->getAddToOrder($itemCode, $itemPrice, $properties);

        return $this->client->request('POST', '/track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }

    /**
     * @param array $properties
     * @return mixed
     */
    public function orderCompleted($properties = [])
    {
        $payload = $this->payload->getOrderCompleted($properties);

        return $this->client->request('POST', '/track', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }
    
    /**
     * @param string $email
     * @return boolean
     */
    public function isIdentified($email)
    {
        $userId = $this->cookie->getCookie(CookieNames::USER_ID);
        $storedEmail = $this->cookie->getCookie(CookieNames::USER_EMAIL);
        
        if(empty($userId) || empty($storedEmail)){
            return false;
        }
        
        if($storedEmail != $email){
            return false;
        }
        
        return true;
    }
}