<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Payment extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Payment');
    }

    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_payment_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_payment_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_payment_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_payment_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        $this->marketplaceModel = NULL;

        $this->delete();
        return true;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    // #######################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Ebay_Template_Payment_Service[]
     */
    public function getServices($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Ebay_Template_Payment_Service','template_payment_id',
                                            $asObjects, $filters);
    }

    // #######################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    //--------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // #######################################

    public function isPayPalEnabled()
    {
        return (bool)$this->getData('pay_pal_mode');
    }

    public function getPayPalEmailAddress()
    {
        return $this->getData('pay_pal_email_address');
    }

    public function isPayPalImmediatePaymentEnabled()
    {
        return (bool)$this->getData('pay_pal_immediate_payment');
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    public function getUsedAttributes()
    {
        return array();
    }

    // #######################################

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();

        $data['services'] = $this->getServices();

        foreach ($data['services'] as &$serviceData) {
            foreach ($serviceData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }

        return $data;
    }

    public function getDefaultSettingsSimpleMode()
    {
        return array(
            'pay_pal_mode'              => 0,
            'pay_pal_email_address'     => '',
            'pay_pal_immediate_payment' => 0,
            'services'                  => array()
        );
    }

    public function getDefaultSettingsAdvancedMode()
    {
        return $this->getDefaultSettingsSimpleMode();
    }

    // #######################################

    public function getAffectedListingProducts($asObjects = false, $key = NULL)
    {
        if (is_null($this->getId())) {
            throw new LogicException('Method require loaded instance first');
        }

        $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;

        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate($template);

        $listingProducts = $templateManager->getAffectedItems(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT,
            $this->getId(), array(), $asObjects, $key
        );

        $ids = array();
        foreach ($listingProducts as $listingProduct) {
            $ids[] = is_null($key) ? $listingProduct['id'] : $listingProduct;
        }

        $listingProducts && $listingProducts = array_combine($ids, $listingProducts);

        $listings = $templateManager->getAffectedItems(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING,
            $this->getId()
        );

        foreach ($listings as $listing) {

            $tempListingProducts = $listing->getChildObject()
                                           ->getAffectedListingProducts($template,$asObjects,$key);

            foreach ($tempListingProducts as $listingProduct) {
                $id = is_null($key) ? $listingProduct['id'] : $listingProduct;
                !isset($listingProducts[$id]) && $listingProducts[$id] = $listingProduct;
            }
        }

        return array_values($listingProducts);
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        if (!$this->getResource()->isDifferent($newData,$oldData)) {
            return;
        }

        $ids = $this->getAffectedListingProducts(false,'id');

        if (empty($ids)) {
            return;
        }

        $templates = array('paymentTemplate');

        Mage::getSingleton('core/resource')->getConnection('core_read')->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$templates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$templates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $ids).')')
        );
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_payment');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_payment');
        return parent::delete();
    }

    // #######################################
}