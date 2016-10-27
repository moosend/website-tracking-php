<?php namespace Moosend;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Ramsey\Uuid\Uuid;

/**
 * Class TrackerFactory
 * @package Moosend
 */
class TrackerFactory {

    public function __construct(){

    }

    /**
     * Creates a Tracker instance
     *
     * @param $siteId
     * @return Tracker
     * @throws \Exception
     */
    public function create($siteId)
    {
        if(empty($siteId)){
            throw new \Exception('Cannot create an instance without a site id');
        }

        $cookie = new Cookie();

        $userId = $cookie->getCookie(CookieNames::USER_ID);
        $userId = ! empty($userId) ? $userId : Uuid::uuid4()->toString();

        $payload = new Payload(new Cookie(), $siteId, $userId);

        $requestHeaders = [];

        $browserUserAgent = Browser::getUserAgent();
        $browserIPAddress = Browser::getRequestIPAddress();

        if(! empty($browserUserAgent)){

            $requestHeaders['X-Original-User-Agent'] = $browserUserAgent;
        }

        if(! empty($browserIPAddress)){

            $requestHeaders['X-Original-Request-IP-Address'] = $browserIPAddress;
        }

        $client = new Client([
            'base_uri' => API::ENDPOINT,
            RequestOptions::HEADERS => $requestHeaders
        ]);

        return new Tracker($cookie, $payload, $client);
    }
} 