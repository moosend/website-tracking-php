<?php namespace Moosend;

use InvalidArgumentException;
use Exception;
use Sinergi\BrowserDetector\Language;

/**
 * Class Payload
 * @package Moosend
 */
class Payload
{

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var Language
     */
    private $language;

    /**
     * @var
     */
    private $siteId;

    /**
     * @var
     */
    private $userId;

    public function __construct(Cookie $cookie, Language $language, $siteId, $userId)
    {
        if (empty($siteId) || empty($userId)) {
            throw new Exception('$siteId or $userId cannot be empty');
        }

        $this->cookie = $cookie;
        $this->language = $language;
        $this->siteId = $siteId;
        $this->userId = $userId;
    }

    /**
     * Generates payload for identify events
     *
     * @param $email
     * @param string $name
     * @param array $properties
     * @return array
     * @throws InvalidArgumentException
     */
    public function getIdentify($email, $name = '', $properties = [])
    {
        if (!$email) {
            throw new InvalidArgumentException('E-mail cannot be empty or null');
        }

        //props that we will combine with default props
        $props = [
            PayloadProperties::EMAIL => $email
        ];

        if ($name) {
            $props[PayloadProperties::NAME] = $name;
        }

        if ($properties) {
            $props[PayloadProperties::PROPERTIES] = $properties;
        }

        return $this->getTrackPayload(ActionTypes::IDENTIFY, $props);
    }

    /**
     * Generates payload for page view events
     *
     * @param $url
     * @param array $properties
     * @return array
     * @throws Exception
     */
    public function getPageView($url, $properties = [])
    {
        if (empty($url)) {
            throw new Exception('url cannot be empty');
        }

        $props = [
            PayloadProperties::URL => $url
        ];

        if ($properties) {
            $props[PayloadProperties::PROPERTIES] = $properties;
        }

        return $this->getTrackPayload(ActionTypes::PAGE_VIEW, $props);
    }

    /**
     * Generates payload for add to order events
     *
     * @param $itemCode
     * @param $itemPrice
     * @param array $properties
     * @return array
     */
    public function getAddToOrder($itemCode, $itemPrice, $properties = [])
    {

        if (!$itemCode || !$itemPrice) {
            throw new InvalidArgumentException(PayloadProperties::ITEM_CODE . ' and ' . PayloadProperties::ITEM_PRICE . ' cannot be empty or null');
        }

        //props that we will combine with default props
        $props = [
            PayloadProperties::PROPERTIES => [
                PayloadProperties::ITEM_CODE => $itemCode,
                PayloadProperties::ITEM_PRICE => $itemPrice
            ]
        ];

        if ($properties) {
            $props[PayloadProperties::PROPERTIES] = array_merge($props[PayloadProperties::PROPERTIES], $properties);
        }

        return $this->getTrackPayload(ActionTypes::ADD_TO_ORDER, $props);
    }

    /**
     * Generates payload for order completed events
     *
     * @param array $properties
     * @return array
     */
    public function getOrderCompleted($properties = [])
    {
        $properties = empty($properties) ? [] : [PayloadProperties::PROPERTIES => $properties];

        return $this->getTrackPayload(ActionTypes::ORDER_COMPLETED, $properties);
    }

    /**
     * Generates payload for custom events
     *
     * @param $event
     * @param array $properties
     * @return array
     */
    public function getCustom($event, $properties = [])
    {

        $properties = empty($properties) ? [] : [PayloadProperties::PROPERTIES => $properties];

        return $this->getTrackPayload($event, $properties);
    }

    private function getTrackPayload($actionType, $props)
    {

        switch ($actionType) {
            case ActionTypes::IDENTIFY:

                $mandatoryProps = [
                    PayloadProperties::CONTACT_ID => $this->userId,
                    PayloadProperties::SITE_ID => $this->siteId
                ];

                return array_merge($props, $mandatoryProps);
                break;

            case ActionTypes::ADD_TO_ORDER:

                $mandatoryProps = $this->getMandatoryProps($actionType);

                return array_merge($props, $mandatoryProps);
                break;

            case ActionTypes::ORDER_COMPLETED:

                $mandatoryProps = $this->getMandatoryProps($actionType);

                return array_merge($props, $mandatoryProps);
                break;

            default:

                $mandatoryProps = $this->getMandatoryProps($actionType);

                return array_merge($props, $mandatoryProps);
                break;

        }
    }

    private function getMandatoryProps($actionType)
    {
        $email = $this->cookie->getCookie(CookieNames::USER_EMAIL);

        return [
            PayloadProperties::ACTION_TYPE => $actionType,
            PayloadProperties::CONTACT_ID => $this->userId,
            PayloadProperties::SITE_ID => $this->siteId,
            PayloadProperties::EMAIL => $email
        ];
    }
}
