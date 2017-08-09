<?php

use Moosend\TrackerFactory;

if ( !function_exists('moo_tracker') ) {
	/**
	 * Wrapper function that creates Tracker instance. On some non-object oriented environments sometimes this is easier
	 *
	 * @param string $siteId
	 * @param string $userAgent
	 * @param string $requestIPAddress
	 * @throws Exception
	 * @return Moosend\Tracker
	 */
	function moo_tracker($siteId, $userAgent = '', $requestIPAddress = '')
	{
	    $trackerFactory = new TrackerFactory();

	    return $trackerFactory->create($siteId, $userAgent, $requestIPAddress);
	}
}
