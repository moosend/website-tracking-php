<?php namespace Moosend;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Moosend\Utils\Uuid;

/**
 * Class TrackerFactory
 * @package Moosend
 */
class TrackerFactory
{
    public function __construct()
    {
    }

    /**
     * Creates a Tracker instance
     *
     * @param string $siteId
     * @param string $userAgent
     * @param string $requestIPAddress
     * @throws \Exception
     * @return Tracker
     */
    public function create($siteId, $userAgent = '', $requestIPAddress = '')
    {
        if (empty($siteId)) {
            throw new \Exception('Cannot create an instance without a site id');
        }

        $cookie = new Cookie();

        $userId = $cookie->getCookie(CookieNames::USER_ID);
        $userId = ! empty($userId) ? $userId : Uuid::v4();

        $sessionId = $cookie->getCookie(CookieNames::SESSION_ID);
        $sessionId = ! empty($sessionId) ? $sessionId : Uuid::v4();

        $payload = new Payload(new Cookie(), $siteId, $userId, $sessionId);

        $requestHeaders = [];

        $browserUserAgent = !empty($userAgent) ? $userAgent : Browser::getUserAgent();
        $browserIPAddress = !empty($requestIPAddress) ? $requestIPAddress : Browser::getRequestIPAddress();

        if (! empty($browserUserAgent)) {
            $requestHeaders['X-Original-User-Agent'] = $browserUserAgent;
        }

        if (! empty($browserIPAddress)) {
            $requestHeaders['X-Original-Request-IP-Address'] = $browserIPAddress;
        }

        $client = new Client([
            'base_uri' => API::ENDPOINT,
            RequestOptions::HEADERS => $requestHeaders,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::VERIFY => false
        ]);

        return new Tracker($cookie, $payload, $client);
    }
}
