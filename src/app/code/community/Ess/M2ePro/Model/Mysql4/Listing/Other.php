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

    public function getItemsByProductId($productId)
    {
        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                           array('lo' => $this->getMainTable()),
                           array('id','component_mode','account_id','marketplace_id')
                       )
                       ->where("`lo`.`product_id` IS NOT NULL AND `lo`.`product_id` = ?",(int)$productId);

        $result = array();
        $accountMarketplaceStoreIdCache = array();

        foreach ($select->query()->fetchAll() as $item) {

            $accountMarketplaceCacheKeyTemp = $item['account_id'].'_'.$item['marketplace_id'];

            if (isset($accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp])) {
                $storeId = $accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp];
            } else {
                $accountObj = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                    $item['component_mode'],'Account',$item['account_id']
                );
                $storeId = $accountObj->getChildObject()->getRelatedStoreId($item['marketplace_id']);
                $accountMarketplaceStoreIdCache[$accountMarketplaceCacheKeyTemp] = $storeId;
            }

            $item['id'] = (int)$item['id'];
            $item['store_id'] = (int)$storeId;
            unset($item['account_id'],$item['marketplace_id']);

            $result[] = $item;
        }

        return $result;
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $componentMode = NULL,
                                    $withStoreFilter = false)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $limit = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($productsChangesTable,'*')
                       ->order(array('id ASC'))
                       ->limit($limit);

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('pc' => $select),
                            array(
                                'changed_attribute'=>'attribute',
                                'changed_to_value'=>'value_new',
                            )
                       )
                       ->join(
                            array('lo' => $this->getMainTable()),
                            '`pc`.`product_id` = `lo`.`product_id`',
                            'id'
                       )
                       ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                       ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        if ($withStoreFilter) {

            $whereStatement = '';

            if (!is_null($componentMode)) {
                $components = array($componentMode);
            } else {
                $components = Mage::helper('M2ePro/Component')->getActiveComponents();
            }

            foreach ($components as $component) {

                $accounts = Mage::helper('M2ePro/Component')
                                    ->getComponentCollection($component,'Account')
                                    ->getItems();

                $marketplaces = Mage::helper('M2ePro/Component')
                                        ->getComponentCollection($component,'Marketplace')
                                        ->getItems();

                foreach ($accounts as $account) {
                    /** @var $account Ess_M2ePro_Model_Account */
                    foreach ($marketplaces as $marketplace) {
                        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
                        $whereStatement != '' && $whereStatement .= ' OR ';
                        $whereStatement .= ' ( `lo`.`account_id` = '.(int)$account->getId().' ';
                        $whereStatement .= ' AND `lo`.`marketplace_id` = '.(int)$marketplace->getId().' ';
                        $whereStatement .= ' AND `lo`.`component_mode` = \''.$component.'\' ';
                        $whereStatement .= ' AND `pc`.`store_id` = '.
                                        (int)$account->getChildObject()
                                            ->getRelatedStoreId($marketplace->getId()).' ) ';
                    }
                }
            }

            $whereStatement != '' && $select->where($whereStatement);
        }

        !is_null($componentMode) && $select->where("`lo`.`component_mode` = ?",(string)$componentMode);

        $results = array();

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    // ########################################
}