<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Repair extends Ess_M2ePro_Model_Abstract
{
    const TYPE_UNKNOWN   = 0;
    const TYPE_VARIATION = 1;

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Repair');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function getType()
    {
        return (int)$this->getData('type');
    }

    public function getInputData()
    {
        return (array)json_decode($this->getData('input_data'), true);
    }

    public function getOutputData()
    {
        return (array)json_decode($this->getData('output_data'), true);
    }

    public function getComponent()
    {
        return $this->getData('component');
    }

    public function isTypeVariation()
    {
        return $this->getType() == self::TYPE_VARIATION;
    }

    public static function create(
        $productId,
        array $input,
        array $output,
        $component,
        $type = self::TYPE_UNKNOWN,
        $hash = null
    ) {
        if (is_null($productId) || count($input) == 0 || count($output) == 0) {
            throw new InvalidArgumentException('Invalid repair data.');
        }

        if (!in_array($type, array(self::TYPE_UNKNOWN, self::TYPE_VARIATION))) {
            throw new InvalidArgumentException('Invalid repair type.');
        }

        if (is_null($hash)) {
            $hash = self::generateHash($input);
        }

        $repairCollection = Mage::getModel('M2ePro/Order_Repair')
            ->getCollection()
                ->addFieldToFilter('product_id', (int)$productId)
                ->addFieldToFilter('hash', $hash)
                ->addFieldToFilter('type', $type);
        $repair = $repairCollection->getFirstItem();

        $repair->addData(array(
            'product_id'  => (int)$productId,
            'input_data'  => json_encode($input),
            'output_data' => json_encode($output),
            'hash'        => $hash,
            'component'   => $component,
            'type'        => (int)$type
        ));

        $repair->save();
    }

    public static function generateHash(array $input)
    {
        if (count($input) == 0) {
            return null;
        }

        return sha1(serialize($input));
    }
}