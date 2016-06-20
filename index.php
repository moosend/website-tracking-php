<?php

use Moosend\API;
use Moosend\Payload;
use Moosend\Tracker;
use Moosend\Cookie;
use GuzzleHttp\Client;
use Moosend\TrackerFactory;
use Ramsey\Uuid\Uuid;
use Sinergi\BrowserDetector\Language;

/**
 * Wrapper function that creates Tracker instance. On some non-object oriented environments sometimes this is easier
 *
 * @param $siteId
 * @return Tracker
 */
function tracker($siteId){

    $cookie = new Cookie();

    $userId = $cookie->getCookie(\Moosend\CookieNames::USER_ID);
    $userId = ! empty($userId) ? $userId : Uuid::uuid4()->toString();

    $payload = new Payload(new Cookie(), new Language(), $siteId, $userId);
    $client = new Client([
        'base_uri' => API::ENDPOINT
    ]);

    return new Tracker($cookie, $payload, $client);
}