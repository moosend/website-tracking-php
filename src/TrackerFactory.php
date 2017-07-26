<?php namespace Moosend;

use GuzzleHttp\Client;
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

        $payload = new Payload(new Cookie(), $siteId, $userId);

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
            'base_url' => API::ENDPOINT,
            'defaults'  =>  [
                'headers'   =>  $requestHeaders
            ]
        ]);

        return new Tracker($cookie, $payload, $client);
    }
}
