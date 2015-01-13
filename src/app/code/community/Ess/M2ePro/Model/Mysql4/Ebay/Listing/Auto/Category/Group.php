<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing_Auto_Category_Group
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Auto_Category_Group', 'id');
    }

    // ########################################

    public function getCategoriesFromOtherGroups($listingId, $groupId = NULL)
    {
        $collection = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')->getCollection();
        $collection->addFieldToFilter('main_table.listing_id', (int)$listingId);
        $collection->getSelect()->joinInner(
            array('melacg' => $this->getMainTable()),
            'main_table.group_id = melacg.id',
            array('group_title' => 'title')
        );

        if ($groupId) {
            $collection->addFieldToFilter('group_id', array('neq' => (int)$groupId));
        }

        $data = array();

        foreach ($collection as $item) {
            $data[$item->getData('category_id')] = $item->getData('group_title');
        }

        return $data;
    }

    // ########################################

    public function deleteEmpty($listingId)
    {
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array(
                'listing_id = ?' => $listingId,
                'id NOT IN (?)' => $this->_getReadAdapter()
                    ->select()
                    ->from(
                        array('melac' => Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category')->getMainTable()),
                        new Zend_Db_Expr('DISTINCT `melac`.`group_id`')
                    )
                    ->where('melac.listing_id = ?', $listingId)
            )
        );
    }

    // ########################################
}