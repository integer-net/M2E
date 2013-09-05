<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Other
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Other', 'id');
    }

    // ########################################

    public function getItemsWhereIsProduct($productId)
    {
        $resultItems = array();

        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('lo' => $this->getMainTable()),array('id','component_mode','account_id','marketplace_id'))
            ->where("`lo`.`product_id` IS NOT NULL AND `lo`.`product_id` = ?",(int)$productId);
        $currentItems = $this->_getWriteAdapter()->fetchAll($dbSelect);

        $accountMarketplaceStoreIdCache = array();
        foreach ($currentItems as $currentItem) {

            $accountMarketplaceCacheKeyTemp = $currentItem['account_id'].'_'.$currentItem['marketplace_id'];

            if (isset($accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp])) {
                $storeId = $accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp];
            } else {
                $accountObj = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                    $currentItem['component_mode'],'Account',$currentItem['account_id']
                );
                $storeId = $accountObj->getChildObject()->getRelatedStoreId($currentItem['marketplace_id']);
                $accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp] = $storeId;
            }

            $currentItem['id'] = (int)$currentItem['id'];
            $currentItem['store_id'] = (int)$storeId;
            unset($currentItem['account_id'],$currentItem['marketplace_id']);

            $resultItems[] = $currentItem;
        }

        return $resultItems;
    }

    // ########################################
}