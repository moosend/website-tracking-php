<?php

use Moosend\TrackerFactory;

/**
 * Wrapper function that creates Tracker instance. On some non-object oriented environments sometimes this is easier
 *
 * @param $siteId
 * @return Tracker
 */
function tracker($siteId){

    $trackerFactory = new TrackerFactory();

    return $trackerFactory->create($siteId);
}