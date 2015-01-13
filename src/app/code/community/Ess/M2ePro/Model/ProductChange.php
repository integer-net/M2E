<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_ProductChange extends Ess_M2ePro_Model_Abstract
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const CREATOR_TYPE_UNKNOWN = 0;
    const CREATOR_TYPE_OBSERVER = 1;
    const CREATOR_TYPE_SYNCHRONIZATION = 2;

    const UPDATE_ATTRIBUTE_CODE = '__INSTANCE__';

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ProductChange');
    }

    //####################################

    public function addCreateAction($productId, $creatorType = self::CREATOR_TYPE_UNKNOWN)
    {
        $tempCollection = Mage::getModel('M2ePro/ProductChange')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('action', self::ACTION_CREATE);

        $tempChanges = $tempCollection->toArray();

        if ($tempChanges['totalRecords'] <= 0) {

            $dataForAdd = array('product_id' => $productId,
                                'action' => self::ACTION_CREATE,
                                'creator_type' => $creatorType);

            Mage::getModel('M2ePro/ProductChange')
                     ->setData($dataForAdd)
                     ->save();

            return true;
        }

        return false;
    }

    public function addDeleteAction($productId, $creatorType = self::CREATOR_TYPE_UNKNOWN)
    {
        $tempCollection = Mage::getModel('M2ePro/ProductChange')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('action', self::ACTION_DELETE);

        $tempChanges = $tempCollection->toArray();

        if ($tempChanges['totalRecords'] <= 0) {

            $dataForAdd = array('product_id' => $productId,
                                'action' => self::ACTION_DELETE,
                                'creator_type' => $creatorType);

            Mage::getModel('M2ePro/ProductChange')
                     ->setData($dataForAdd)
                     ->save();

            return true;
        }

        return false;
    }

    public function addUpdateAction($productId, $creatorType = self::CREATOR_TYPE_UNKNOWN)
    {
        $tempCollection = Mage::getModel('M2ePro/ProductChange')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('action', self::ACTION_UPDATE)
                                ->addFieldToFilter('attribute', self::UPDATE_ATTRIBUTE_CODE);

        $tempChanges = $tempCollection->toArray();

        if ($tempChanges['totalRecords'] <= 0) {

            $dataForAdd = array('product_id' => $productId,
                                'action' => self::ACTION_UPDATE,
                                'attribute' => self::UPDATE_ATTRIBUTE_CODE,
                                'creator_type' => $creatorType);

            Mage::getModel('M2ePro/ProductChange')
                     ->setData($dataForAdd)
                     ->save();

            return true;
        }

        return false;
    }

    //-----------------------------------

    public function updateAttribute($productId, $attribute,
                                    $valueOld, $valueNew,
                                    $creatorType = self::CREATOR_TYPE_UNKNOWN,
                                    $storeId = NULL)
    {
        $tempCollection = Mage::getModel('M2ePro/ProductChange')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('action', self::ACTION_UPDATE)
                                ->addFieldToFilter('attribute', $attribute);

        if (is_null($storeId)) {
            $tempCollection->addFieldToFilter('store_id', array('null'=>true));
        } else {
            $tempCollection->addFieldToFilter('store_id', $storeId);
        }

        $tempChanges = $tempCollection->toArray();

        if ($tempChanges['totalRecords'] <= 0) {

             if ($valueOld == $valueNew) {
                 return false;
             }

             $dataForAdd = array('product_id' => $productId,
                                 'store_id' => $storeId,
                                 'action' => self::ACTION_UPDATE,
                                 'attribute' => $attribute,
                                 'value_old' => $valueOld,
                                 'value_new' => $valueNew,
                                 'count_changes' => 1,
                                 'creator_type' => $creatorType);

             Mage::getModel('M2ePro/ProductChange')
                     ->setData($dataForAdd)
                     ->save();

             return true;
        }

        if ($tempChanges['items'][0]['value_old'] == $valueNew) {

              Mage::getModel('M2ePro/ProductChange')
                    ->setId($tempChanges['items'][0]['id'])
                    ->delete();

              return true;

        } else if ($valueOld != $valueNew) {

             $dataForUpdate = array('value_new' => $valueNew,
                                    'count_changes' => $tempChanges['items'][0]['count_changes']+1,
                                    'creator_type' => $creatorType);

             Mage::getModel('M2ePro/ProductChange')
                     ->load($tempChanges['items'][0]['id'])
                     ->addData($dataForUpdate)
                     ->save();

             return true;
        }

        return false;
    }

    //####################################

    public function removeDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $productsChanges = Mage::getModel('M2ePro/ProductChange')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->getItems();

        foreach ($productsChanges as $productChange) {
            $productChange->deleteInstance();
        }
    }

    //####################################

    public function clearLastProcessed($date, $maxPerOneTime)
    {
        $stmt = $this->getResource()->getReadConnection()
            ->select()
            ->from(array('pc' => $this->getResource()->getMainTable()),'id')
            ->order(array('id ASC'))
            ->limit($maxPerOneTime)
            ->query();

        $ids = array();
        while ($ids[] = (int)$stmt->fetchColumn());
        $ids = array_values(array_unique(array_filter($ids)));

        if (empty($ids)) {
            return;
        }

        $ids = implode(',',$ids);
        $creatorObserver = self::CREATOR_TYPE_OBSERVER;

        $this->clear("id IN ({$ids}) AND (update_date <= '{$date}' OR creator_type != {$creatorObserver})");
    }

    public function clearOutdated($maxLifeTime)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tempDate = new DateTime('now', new DateTimeZone('UTC'));
        $tempDate->modify('-'.$maxLifeTime.' seconds');
        $tempDate = Mage::helper('M2ePro')->getDate($tempDate->format('U'));

        Mage::getModel('M2ePro/ProductChange')->clear(
            'update_date <= ' . $connRead->quote($tempDate)
        );
    }

    public function clearExcessive($maxProductsChanges)
    {
        $countOfProductChanges = Mage::getModel('M2ePro/ProductChange')->getCollection()->getSize();

        if (($countOfProductChangesToDelete = $countOfProductChanges - $maxProductsChanges) > 0) {
            Mage::getModel('M2ePro/ProductChange')->clear(NULL, $countOfProductChangesToDelete);
        }
    }

    //####################################

    public function clear($where = NULL, $limit = NULL)
    {
        if ($limit < 0) {
            $limit = NULL;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = $this->getResource()->getMainTable();

        $where && $where = "AND {$where}";
        $limit && $limit = "LIMIT {$limit}";

        $sql = "DELETE FROM {$tableName} WHERE 1 {$where} ORDER BY id ASC {$limit}";

        $connWrite->query($sql);
    }

    //####################################
}