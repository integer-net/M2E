<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Other extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Listing_Other', 'id');
    }

    public function getItemsWhereIsProduct($productId)
    {
        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('lo' => $this->getMainTable()),array('id','component_mode','account_id','marketplace_id'))
            ->where("`lo`.`product_id` IS NOT NULL AND `lo`.`product_id` = ?",(int)$productId);

        $newData = array();
        $oldData = $this->_getWriteAdapter()->fetchAll($dbSelect);

        $itemsIds = array();
        $accountMarketplaceStoreId = array();
        foreach ($oldData as $item) {

            if (in_array($item['id'],$itemsIds)) {
                continue;
            }

            if (isset($accountMarketplaceStoreId[$item['account_id'].'_'.$item['marketplace_id']])) {
                $storeId = $accountMarketplaceStoreId[$item['account_id'].'_'.$item['marketplace_id']];
            } else {
                $accountObj = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                    $item['component_mode'],'Account',$item['account_id']
                );
                $storeId = $accountObj->getChildObject()->getRelatedStoreId($item['marketplace_id']);
                $accountMarketplaceStoreId[$item['account_id'].'_'.$item['marketplace_id']] = $storeId;
            }

            $item['id'] = (int)$item['id'];
            $item['store_id'] = (int)$storeId;
            unset($item['account_id'],$item['marketplace_id']);

            $newData[] = $item;
        }

        return $newData;
    }
}