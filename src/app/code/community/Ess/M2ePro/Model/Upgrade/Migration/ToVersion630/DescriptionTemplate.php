<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_DescriptionTemplate
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    private $forceAllSteps = false;

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    // -----------------------------------

    public function setForceAllSteps($value = true)
    {
        $this->forceAllSteps = $value;
    }

    //####################################

    /*

        ALTER TABLE m2epro_amazon_listing_product
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED DEFAULT NULL,
            DROP INDEX template_new_product_id,
            ADD INDEX template_description_id (template_description_id);

        RENAME TABLE m2epro_amazon_template_new_product TO m2epro_amazon_template_description;
        RENAME TABLE m2epro_amazon_template_new_product_description TO m2epro_amazon_template_description_definition;
        RENAME TABLE m2epro_amazon_template_new_product_specific TO m2epro_amazon_template_description_specific;

        CREATE TABLE m2epro_template_description (
            id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            component_mode varchar(10) DEFAULT NULL,
            update_date datetime DEFAULT NULL,
            create_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX component_mode (component_mode),
            INDEX title (title)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        ALTER TABLE m2epro_ebay_template_description
            DROP COLUMN title,
            DROP COLUMN create_date,
            DROP COLUMN update_date,
            CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id);

        ALTER TABLE m2epro_amazon_template_description
            DROP COLUMN title,
            DROP COLUMN create_date,
            DROP COLUMN update_date,
            DROP COLUMN node_title,
            DROP COLUMN xsd_hash,
            DROP COLUMN identifiers,DEFAULT NULL
            CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
            CHANGE COLUMN category_path category_path VARCHAR(255) DEFAULT NULL,
            ADD COLUMN is_new_asin_accepted TINYINT(2) UNSIGNED DEFAULT 0 AFTER marketplace_id,
            ADD COLUMN product_data_nick VARCHAR(255) DEFAULT NULL AFTER is_new_asin_accepted,
            ADD COLUMN browsenode_id DECIMAL(20, 0) UNSIGNED DEFAULT NULL AFTER category_path,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id),
            ADD INDEX is_new_asin_accepted (is_new_asin_accepted),
            ADD INDEX product_data_nick (product_data_nick),
            ADD INDEX browsenode_id (browsenode_id);

        ALTER TABLE m2epro_amazon_template_description_definition
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED NOT NULL,
            CHANGE COLUMN brand_template brand_custom_attribute VARCHAR(255) DEFAULT NULL,
            CHANGE COLUMN manufacturer_template manufacturer_custom_attribute VARCHAR(255) DEFAULT NULL,
            CHANGE COLUMN target_audience_custom_value target_audience TEXT NOT NULL,
            ADD COLUMN brand_custom_value VARCHAR(255) DEFAULT NULL AFTER brand_mode,
            ADD COLUMN manufacturer_custom_value VARCHAR(255) DEFAULT NULL AFTER manufacturer_mode,

            ADD COLUMN item_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER manufacturer_part_number_custom_attribute,
            ADD COLUMN item_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_mode,
            ADD COLUMN item_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_length_custom_value,
            ADD COLUMN item_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_width_custom_value,
            ADD COLUMN item_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_height_custom_value,
            ADD COLUMN item_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_length_custom_attribute,
            ADD COLUMN item_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_width_custom_attribute,
            ADD COLUMN item_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_volume_height_custom_attribute,
            ADD COLUMN item_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_unit_of_measure_mode,
            ADD COLUMN item_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_unit_of_measure_custom_value,
            ADD COLUMN item_dimensions_weight_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_volume_unit_of_measure_custom_attribute,
            ADD COLUMN item_dimensions_weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL
                AFTER item_dimensions_weight_mode,
            ADD COLUMN item_dimensions_weight_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_custom_value,
            ADD COLUMN item_dimensions_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_weight_custom_attribute,
            ADD COLUMN item_dimensions_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_unit_of_measure_mode,
            ADD COLUMN item_dimensions_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_unit_of_measure_custom_value,
            ADD COLUMN package_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_weight_unit_of_measure_custom_attribute,

            ADD COLUMN package_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_mode,
            ADD COLUMN package_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_length_custom_value,
            ADD COLUMN package_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_width_custom_value,
            ADD COLUMN package_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_height_custom_value,
            ADD COLUMN package_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_length_custom_attribute,
            ADD COLUMN package_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_width_custom_attribute,
            ADD COLUMN package_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER package_dimensions_volume_height_custom_attribute,
            ADD COLUMN package_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_unit_of_measure_mode,
            ADD COLUMN package_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_unit_of_measure_custom_value,

            DROP COLUMN target_audience_custom_attribute,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id);

        ALTER TABLE m2epro_amazon_template_description_specific
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED NOT NULL,
            DROP INDEX template_new_product_id,
            ADD INDEX template_description_id (template_description_id);

    */

    //####################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->saveWizardNecessaryData();

        $this->processListingProduct();
        $this->clearTables();
        $this->renameTables();
        $this->createTables();
        $this->migrateEbay();
        $this->alterTables();
    }

    //####################################

    private function isNeedToSkip()
    {
        if ($this->forceAllSteps) {
            return false;
        }

        $connection = $this->installer->getConnection();

        $oldSpecific = $this->installer->getTable('m2epro_amazon_template_new_product_specific');
        $newSpecific = $this->installer->getTable('m2epro_amazon_template_description_specific');

        if (!$this->installer->tableExists($oldSpecific) &&
            $this->installer->tableExists($newSpecific) &&
            $connection->tableColumnExists($newSpecific, 'template_description_id') !== false) {
            return true;
        }

        return false;
    }

    //####################################

    private function saveWizardNecessaryData()
    {
        $marketplace = $this->installer->getTable('m2epro_marketplace');
        $templateNewProduct = $this->installer->getTable('m2epro_amazon_template_new_product');

        if (!$this->installer->tableExists($templateNewProduct)) {
            return;
        }

        $connection = $this->installer->getConnection();

        $result = $connection->query(<<<SQL

        SELECT `main_table`.`title`,
               `main_table`.`category_path`,
               `second_table`.`title` AS `marketplace_title`
        FROM `{$templateNewProduct}` as `main_table`
        INNER JOIN `{$marketplace}` AS `second_table`
        ON (`main_table`.`marketplace_id` = `second_table`.`id`);

SQL
);
        $registryKey = 'wizard_new_amazon_description_templates';

        $dataForInsert = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $dataForInsert[] = $row;
        }

        $dateForInsert = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
        $dataForInsert = $connection->quote(json_encode($dataForInsert));

        $this->installer->run(<<<SQL

        INSERT INTO `m2epro_registry` (`key`, `value`, update_date, create_date)
        VALUES ('{$registryKey}', {$dataForInsert}, {$dateForInsert}, {$dateForInsert});

SQL
);
    }

    //####################################

    private function processListingProduct()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_listing_product');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
            $connection->dropKey($tempTable, 'template_new_product_id');
        }

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false) {

            $this->installer->run(<<<SQL

    UPDATE `m2epro_amazon_listing_product`
    SET template_new_product_id = NULL;

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false &&
            $connection->tableColumnExists($tempTable, 'template_description_id') === false) {
            $connection->changeColumn(
                $tempTable, 'template_new_product_id', 'template_description_id',
                'int(11) UNSIGNED DEFAULT NULL'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('template_description_id')])) {
            $connection->addKey($tempTable, 'template_description_id', 'template_description_id');
        }
    }

    //####################################

    private function clearTables()
    {
        $tempTable = $this->installer->getTable('m2epro_amazon_template_new_product');

        if ($this->installer->tableExists($tempTable)) {
            $this->installer->run("TRUNCATE TABLE `m2epro_amazon_template_new_product`");
        }

        $tempTable = $this->installer->getTable('m2epro_amazon_template_new_product_description');

        if ($this->installer->tableExists($tempTable)) {
            $this->installer->run("TRUNCATE TABLE `m2epro_amazon_template_new_product_description`");
        }

        $tempTable = $this->installer->getTable('m2epro_amazon_template_new_product_specific');

        if ($this->installer->tableExists($tempTable)) {
            $this->installer->run("TRUNCATE TABLE `m2epro_amazon_template_new_product_specific`");
        }
    }

    private function renameTables()
    {
        $connection = $this->installer->getConnection();

        $oldTable = $this->installer->getTable('m2epro_amazon_template_new_product');
        $newTable = $this->installer->getTable('m2epro_amazon_template_description');

        if ($this->installer->tableExists($oldTable) && !$this->installer->tableExists($newTable)) {
            $connection->query("RENAME TABLE {$oldTable} TO {$newTable}");
        }

        $oldTable = $this->installer->getTable('m2epro_amazon_template_new_product_description');
        $newTable = $this->installer->getTable('m2epro_amazon_template_description_definition');

        if ($this->installer->tableExists($oldTable) && !$this->installer->tableExists($newTable)) {
            $connection->query("RENAME TABLE {$oldTable} TO {$newTable}");
        }

        $oldTable = $this->installer->getTable('m2epro_amazon_template_new_product_specific');
        $newTable = $this->installer->getTable('m2epro_amazon_template_description_specific');

        if ($this->installer->tableExists($oldTable) && !$this->installer->tableExists($newTable)) {
            $connection->query("RENAME TABLE {$oldTable} TO {$newTable}");
        }
    }

    private function createTables()
    {
        $this->installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_template_description`;
CREATE TABLE `m2epro_template_description` (
    id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    component_mode varchar(10) DEFAULT NULL,
    update_date datetime DEFAULT NULL,
    create_date datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX component_mode (component_mode),
    INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );
    }

    //####################################

    private function migrateEbay()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_ebay_template_description');

        if ($connection->tableColumnExists($tempTable, 'id') !== false &&
            $connection->tableColumnExists($tempTable, 'title') !== false &&
            $connection->tableColumnExists($tempTable, 'create_date') !== false &&
            $connection->tableColumnExists($tempTable, 'update_date') !== false) {
            $this->installer->run(<<<SQL

INSERT INTO `m2epro_template_description` (id, title, update_date, create_date)
SELECT DISTINCT metd.id, metd.title, metd.update_date, metd.create_date
FROM `m2epro_ebay_template_description` metd;

UPDATE `m2epro_template_description` mtd
  SET mtd.component_mode = "ebay";

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'id') !== false) {
            $this->installer->run(<<<SQL

ALTER TABLE `m2epro_ebay_template_description`
    CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (template_description_id);

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'title') !== false) {
            $connection->dropColumn($tempTable, 'title');
        }

        if ($connection->tableColumnExists($tempTable, 'create_date') !== false) {
            $connection->dropColumn($tempTable, 'create_date');
        }

        if ($connection->tableColumnExists($tempTable, 'update_date') !== false) {
            $connection->dropColumn($tempTable, 'update_date');
        }
    }

    //####################################

    private function alterTables()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_template_description');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'title') !== false) {
            $connection->dropColumn($tempTable, 'title');
        }

        if ($connection->tableColumnExists($tempTable, 'create_date') !== false) {
            $connection->dropColumn($tempTable, 'create_date');
        }

        if ($connection->tableColumnExists($tempTable, 'update_date') !== false) {
            $connection->dropColumn($tempTable, 'update_date');
        }

        if ($connection->tableColumnExists($tempTable, 'node_title') !== false) {
            $connection->dropColumn($tempTable, 'node_title');
        }

        if ($connection->tableColumnExists($tempTable, 'xsd_hash') !== false) {
            $connection->dropColumn($tempTable, 'xsd_hash');
        }

        if ($connection->tableColumnExists($tempTable, 'identifiers') !== false) {
            $connection->dropColumn($tempTable, 'identifiers');
        }

        if ($connection->tableColumnExists($tempTable, 'id') !== false &&
            $connection->tableColumnExists($tempTable, 'template_description_id') === false) {
            $this->installer->run(<<<SQL

ALTER TABLE `m2epro_amazon_template_description`
    CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (template_description_id);

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'category_path') !== false) {
            $connection->changeColumn(
                $tempTable, 'category_path', 'category_path',
                'VARCHAR(255) DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_new_asin_accepted') === false) {
            $connection->addColumn(
                $tempTable, 'is_new_asin_accepted',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER marketplace_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'product_data_nick') === false) {
            $connection->addColumn(
                $tempTable, 'product_data_nick',
                'VARCHAR(255) DEFAULT NULL AFTER is_new_asin_accepted'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'browsenode_id') === false) {
            $connection->addColumn(
                $tempTable, 'browsenode_id',
                'DECIMAL(20, 0) UNSIGNED DEFAULT NULL AFTER category_path'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('is_new_asin_accepted')])) {
            $connection->addKey($tempTable, 'is_new_asin_accepted', 'is_new_asin_accepted');
        }

        if (!isset($tempTableIndexList[strtoupper('product_data_nick')])) {
            $connection->addKey($tempTable, 'product_data_nick', 'product_data_nick');
        }

        if (!isset($tempTableIndexList[strtoupper('browsenode_id')])) {
            $connection->addKey($tempTable, 'browsenode_id', 'browsenode_id');
        }

        // ------------------------------

        $tempTable = $this->installer->getTable('m2epro_amazon_template_description_definition');

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false &&
            $connection->tableColumnExists($tempTable, 'template_description_id') === false) {
            $this->installer->run(<<<SQL

ALTER TABLE `m2epro_amazon_template_description_definition`
    CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED NOT NULL,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (template_description_id);

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'brand_custom_attribute') === false &&
            $connection->tableColumnExists($tempTable, 'brand_template') !== false) {
            $connection->changeColumn(
                $tempTable, 'brand_template', 'brand_custom_attribute', 'VARCHAR(255) DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'manufacturer_custom_attribute') === false &&
            $connection->tableColumnExists($tempTable, 'manufacturer_template') !== false) {
            $connection->changeColumn(
                $tempTable, 'manufacturer_template', 'manufacturer_custom_attribute', 'VARCHAR(255) DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'target_audience') === false &&
            $connection->tableColumnExists($tempTable, 'target_audience_custom_value') !== false) {
            $connection->changeColumn(
                $tempTable, 'target_audience_custom_value', 'target_audience', 'TEXT NOT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'brand_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'brand_custom_value', 'VARCHAR(255) DEFAULT NULL AFTER `brand_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'manufacturer_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'manufacturer_custom_value', 'VARCHAR(255) DEFAULT NULL AFTER manufacturer_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'target_audience_custom_attribute') !== false) {
            $connection->dropColumn(
                $tempTable, 'target_audience_custom_attribute'
            );
        }

        // -----

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_mode') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER manufacturer_part_number_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_length_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_length_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_width_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_width_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_length_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_height_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_height_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_width_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_length_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_length_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_height_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_width_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_width_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_length_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_height_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_height_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_width_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_volume_unit_of_measure_mode') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_unit_of_measure_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER item_dimensions_volume_height_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable,
                                           'item_dimensions_volume_unit_of_measure_custom_value') === false
        ) {

            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_unit_of_measure_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_unit_of_measure_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable,
                                           'item_dimensions_volume_unit_of_measure_custom_attribute') === false
        ) {

            $connection->addColumn(
                $tempTable, 'item_dimensions_volume_unit_of_measure_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_volume_unit_of_measure_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_weight_mode') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER item_dimensions_volume_unit_of_measure_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_weight_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_custom_value',
                'DECIMAL(10, 2) UNSIGNED DEFAULT NULL AFTER item_dimensions_weight_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_weight_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_weight_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_weight_unit_of_measure_mode') === false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_unit_of_measure_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER item_dimensions_weight_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable,'item_dimensions_weight_unit_of_measure_custom_value') ===
            false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_unit_of_measure_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_weight_unit_of_measure_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'item_dimensions_weight_unit_of_measure_custom_attribute') ===
            false) {
            $connection->addColumn(
                $tempTable, 'item_dimensions_weight_unit_of_measure_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER item_dimensions_weight_unit_of_measure_custom_value'
            );
        }

        // -----

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_mode') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER item_dimensions_weight_unit_of_measure_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_length_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_length_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_width_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_width_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_length_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_height_custom_value') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_height_custom_value',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_width_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_length_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_length_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_height_custom_value'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_width_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_width_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_length_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_height_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_height_custom_attribute',
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_width_custom_attribute'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'package_dimensions_volume_unit_of_measure_mode') === false) {
            $connection->addColumn(
                $tempTable, 'package_dimensions_volume_unit_of_measure_mode',
                'TINYINT(2) UNSIGNED DEFAULT 0 AFTER package_dimensions_volume_height_custom_attribute'
            );
        }

        $columnName = 'package_dimensions_volume_unit_of_measure_custom_value';
        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName,
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_unit_of_measure_mode'
            );
        }

        $columnName = 'package_dimensions_volume_unit_of_measure_custom_attribute';
        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName,
                'VARCHAR(255) DEFAULT NULL AFTER package_dimensions_volume_unit_of_measure_custom_value'
            );
        }

        // ------------------------------

        $tempTable = $this->installer->getTable('m2epro_amazon_template_description_specific');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
            $connection->dropKey($tempTable, 'template_new_product_id');
        }

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false &&
            $connection->tableColumnExists($tempTable, 'template_description_id') === false) {
            $connection->changeColumn(
                $tempTable, 'template_new_product_id', 'template_description_id',
                'int(11) UNSIGNED NOT NULL'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('template_description_id')])) {
            $connection->addKey($tempTable, 'template_description_id', 'template_description_id');
        }

        // ------------------------------
    }

    //####################################
}