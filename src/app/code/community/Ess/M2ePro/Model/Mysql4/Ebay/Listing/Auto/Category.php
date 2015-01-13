<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing_Auto_Category
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Auto_Category', 'id');
    }

    // ########################################

    public function assignGroup($listingId, $groupId, array $categories)
    {
        try {
            $this->beginTransaction();

            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                array(
                    'category_id NOT IN (?)' => $categories,
                    'listing_id = ?' => $listingId,
                    'group_id = ?' => $groupId
                )
            );

            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('group_id' => $groupId),
                array('category_id IN (?)' => $categories, 'listing_id = ?' => $listingId)
            );

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
        }
    }

    // ########################################
}