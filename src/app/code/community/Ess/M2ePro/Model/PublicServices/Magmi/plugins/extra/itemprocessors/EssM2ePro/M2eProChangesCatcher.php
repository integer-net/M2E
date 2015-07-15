<?php

class M2eProChangesCatcher extends Magmi_ItemProcessor
{
    const CHANGE_UPDATE_ATTRIBUTE_CODE = '__INSTANCE__';
    const CHANGE_UPDATE_ACTION         = 'update';
    const CHANGE_INITIATOR_DEVELOPER   = 4;

    protected $changes = array();

    // ########################################

    public function initialize($params) {}

    public function getPluginInfo()
    {
        return array(
            "name"    => "Ess M2ePro Product Changes Inspector",
            "author"  => "ESS",
            "version" => "1.0.0.1",
            "url"     => "" //todo doc
        );
    }

    // ########################################

    public function processItemAfterId(&$item, $params = null)
    {
        $result = parent::processItemAfterId($item, $params);

        $this->changes[$params['product_id']] = array(
            'product_id'    => $params['product_id'],
            'action'        => self::CHANGE_UPDATE_ACTION,
            'attribute'     => self::CHANGE_UPDATE_ATTRIBUTE_CODE,
            'initiators'    => self::CHANGE_INITIATOR_DEVELOPER,
            'update_date'   => $date = date('Y-m-d H:i:s'),
            'create_date'   => $date
        );

        return $result;
    }

    public function afterImport()
    {
        $result = parent::afterImport();

        $this->filterOnlyAffectedChanges();
        $this->insertChanges();

        return $result;
    }

    // ########################################

    private function filterOnlyAffectedChanges()
    {
        if (count($this->changes) <= 0) {
            return;
        }

        $listingProductTable  = $this->tablename('m2epro_listing_product');
        $variationOptionTable = $this->tablename('m2epro_listing_product_variation_option');
        $listingOtherTable    = $this->tablename('m2epro_listing_other');

        $stmt = $this->select("SELECT DISTINCT `product_id` FROM `{$listingProductTable}`
                               UNION
                               SELECT DISTINCT `product_id` FROM `{$variationOptionTable}`
                               UNION
                               SELECT DISTINCT `product_id` FROM `{$listingOtherTable}`
                               WHERE `component_mode` = 'ebay' AND
                                     `product_id` IS NOT NULL");

        $productsInListings = array();
        while ($row = $stmt->fetch()) {
            $productsInListings[] = (int)$row['product_id'];
        }

        foreach ($this->changes as $key => $change) {

            if (!in_array($change['product_id'], $productsInListings)) {
                unset($this->changes[$key]);
            }
        }
    }

    private function insertChanges()
    {
        if (count($this->changes) <= 0) {
            return;
        }

        $tableName = $this->tablename('m2epro_product_change');
        $stmt = $this->select("SELECT *
                               FROM `{$tableName}`
                               WHERE `product_id` IN (?)", array_keys($this->changes));

        $existedChanges = array();
        while ($row = $stmt->fetch()) {
            $existedChanges[] = $row['product_id'].'##'.$row['attribute'];
        }

        $insertSql = "INSERT INTO `{$tableName}`
                      (`product_id`,`action`,`attribute`,`initiators`,`update_date`,`create_date`)
                      VALUES (?,?,?,?,?,?)";

        foreach ($this->changes as $productId => $change) {

            if (in_array($change['product_id'].'##'.$change['attribute'], $existedChanges)) {
                continue;
            }

            $this->insert($insertSql, array($change['product_id'],
                                            $change['action'],
                                            $change['attribute'],
                                            $change['initiators'],
                                            $change['update_date'],
                                            $change['create_date']));
        }
    }

    // ########################################
}