<?php namespace Moosend;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Sinergi\BrowserDetector\Language;

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

        $payload = new Payload(new Cookie(), new Language(), $siteId, $userId);
        $client = new Client([
            'base_uri' => API::ENDPOINT
        ]);

        return new Tracker($cookie, $payload, $client);
    }
} 