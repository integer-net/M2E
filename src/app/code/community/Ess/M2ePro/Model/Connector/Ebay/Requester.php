<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Requester extends Ess_M2ePro_Model_Connector_Requester
{
    const COMPONENT = 'Ebay';
    const COMPONENT_VERSION = 8;

    const MODE_SANDBOX = 'sandbox';
    const MODE_PRODUCTION = 'production';

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    /**
     * @var null|int
     */
    protected $mode = NULL;

    // ########################################

    public function __construct(array $params = array(),
                                Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                                Ess_M2ePro_Model_Account $account = NULL,
                                $mode = NULL)
    {
        if ($mode != self::MODE_SANDBOX && $mode != self::MODE_PRODUCTION) {
            $mode = NULL;
        }

        $this->marketplace = $marketplace;
        $this->account = $account;
        $this->mode = $mode;

        parent::__construct($params);
    }

    // ########################################

    protected function getComponent()
    {
        return self::COMPONENT;
    }

    protected function getComponentVersion()
    {
        return self::COMPONENT_VERSION;
    }

    // ########################################

    public function process()
    {
        if (!is_null($this->marketplace)) {
            $this->requestExtraData['marketplace'] = $this->marketplace->getNativeId();
        }

        if (!is_null($this->account)) {
            $this->requestExtraData['account'] = $this->account->getChildObject()->getServerHash();
        }

        if (!is_null($this->mode)) {
            $this->requestExtraData['mode'] = $this->mode;
        }

        parent::process();
    }

    // ########################################

    public static function ebayTimeToString($time)
    {
        return (string)self::getEbayDateTimeObject($time)->format('Y-m-d H:i:s');
    }

    public static function ebayTimeToTimeStamp($time)
    {
        return (int)self::getEbayDateTimeObject($time)->format('U');
    }

    private static function getEbayDateTimeObject($time)
    {
        $dateTime = NULL;

        if ($time instanceof DateTime) {
            $dateTime = clone $time;
            $dateTime->setTimezone(new DateTimeZone('UTC'));
        } else {
            is_int($time) && $time = '@'.$time;
            $dateTime = new DateTime($time, new DateTimeZone('UTC'));
        }

        if (is_null($dateTime)) {
            throw new Exception('eBay DateTime object is null');
        }

        return $dateTime;
    }

    // ########################################
}