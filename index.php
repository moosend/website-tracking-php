<?php

use Moosend\TrackerFactory;

/**
 * Wrapper function that creates Tracker instance. On some non-object oriented environments sometimes this is easier
 *
 * @param $siteId
 * @param string $userAgent
 * @param string $requestIPAddress
 * @throws Exception
 * @return Moosend\Tracker
 */
function tracker($siteId, $userAgent = '', $requestIPAddress = ''){

    $trackerFactory = new TrackerFactory();

    return $trackerFactory->create($siteId, $userAgent, $requestIPAddress);
}