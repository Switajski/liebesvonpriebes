<?php

namespace PayPal\Api;

use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\UrlValidator;

/**
 * Class WebhookSimulate.
 *
 * One or more webhook objects.
 *
 *
 * @property string url
 * @property \PayPal\Api\WebhookEventType event_type
 */
class WebhookSimulate extends WebhookEventType
{
    /**
     * @param string $url
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setUrl($url)
    {
        UrlValidator::validate($url, 'Url');
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \PayPal\Api\WebhookEventType $event_type
     *
     * @return $this
     */
    public function setEventType($event_type)
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * @return \PayPal\Api\WebhookEventType
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * @param ApiContext     $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall   is the Rest Call Service that is used to make rest calls
     *
     * @return \PayPal\Api\WebhookEventType
     */
    public function simulate($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            '/v1/notifications/simulate-event',
            'POST',
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookEventType();
        $ret->fromJson($json);

        return $ret;
    }
}
