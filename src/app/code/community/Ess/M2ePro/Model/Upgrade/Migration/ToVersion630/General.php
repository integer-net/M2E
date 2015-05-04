<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_General
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //####################################

    /*

        DROP TABLE `m2epro_attribute_set`;

        ALTER TABLE m2epro_listing_log
            ADD COLUMN additional_data TEXT DEFAULT NULL AFTER product_title,
            ADD COLUMN parent_listing_product_id int(11) UNSIGNED DEFAULT NULL AFTER listing_product_id,
            ADD INDEX parent_listing_product_id (parent_listing_product_id);

        ALTER TABLE m2epro_amazon_template_synchronization
            ADD COLUMN revise_update_details tinyint(2) UNSIGNED NOT NULL AFTER revise_update_price,
            ADD COLUMN revise_update_images tinyint(2) UNSIGNED NOT NULL AFTER revise_update_details,
            ADD COLUMN revise_change_description_template tinyint(2) UNSIGNED NOT NULL AFTER revise_update_images;

    */

    //####################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->processAttributeSet();
        $this->processRegistry();
        $this->processLog();
        $this->processAmazonTemplates();
        $this->processWizard();
    }

    //####################################

    private function isNeedToSkip()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_template_synchronization');
        if ($connection->tableColumnExists($tempTable, 'revise_change_description_template') !== false) {
            return true;
        }

        return false;
    }

    //####################################

    private function processAttributeSet()
    {
        $connection = $this->getInstaller()->getConnection();

        $tempTable = $this->getInstaller()->getTable('m2epro_attribute_set');

        if ($connection->isTableExists($tempTable) !== false) {
            $connection->dropTable($tempTable);
        }
    }

    private function processRegistry()
    {
        $this->getInstaller()->run(<<<SQL

    DROP TABLE IF EXISTS m2epro_registry;
    CREATE TABLE m2epro_registry (
      id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `key` VARCHAR(255) NOT NULL,
      value TEXT DEFAULT NULL,
      update_date DATETIME DEFAULT NULL,
      create_date DATETIME DEFAULT NULL,
      PRIMARY KEY (id),
      INDEX `key` (`key`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
        );
    }

    private function processLog()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_listing_log');
        $stmt = $connection->query("SELECT count(*) FROM {$tempTable}");

        if ((int)$stmt->fetchColumn() > 100000) {
            $this->installer->run("TRUNCATE TABLE `m2epro_listing_log`");
        }

        if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
            $connection->addColumn(
                $tempTable, 'additional_data',
                'TEXT DEFAULT NULL AFTER product_title'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'parent_listing_product_id') === false) {
            $connection->addColumn(
                $tempTable, 'parent_listing_product_id',
                'int(11) UNSIGNED DEFAULT NULL AFTER listing_product_id'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!isset($tempTableIndexList[strtoupper('parent_listing_product_id')])) {
            $connection->addKey($tempTable, 'parent_listing_product_id', 'parent_listing_product_id');
        }
    }

    private function processAmazonTemplates()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_template_synchronization');

        if ($connection->tableColumnExists($tempTable, 'relist_send_data') === false) {
            $connection->addColumn(
                $tempTable, 'relist_send_data',
                'TINYINT(2) UNSIGNED NOT NULL after relist_filter_user_lock'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_update_details') === false) {
            $connection->addColumn(
                $tempTable, 'revise_update_details',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_price'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_update_images') === false) {
            $connection->addColumn(
                $tempTable, 'revise_update_images',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_details'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_change_description_template') === false) {
            $connection->addColumn(
                $tempTable, 'revise_change_description_template',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_images'
            );
        }

        $this->getInstaller()->run(<<<SQL

    UPDATE `m2epro_amazon_template_selling_format`
    SET `sale_price_mode` = 0
    WHERE `sale_price_mode` = 4;

SQL
        );
    }

    private function processWizard()
    {
        $tempTable = $this->installer->getTable('m2epro_wizard');
        $tempQuery = "SELECT * FROM `{$tempTable}` WHERE `nick` = 'migrationNewAmazon'";

        $tempRow = $this->installer->getConnection()
                                   ->query($tempQuery)
                                   ->fetch();

        if ($tempRow !== false) {
            return;
        }

        $this->getInstaller()->run(<<<SQL

    INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
    VALUES ('migrationNewAmazon', 'common', 0, NULL, 0, 6);

SQL
        );
    }

    //####################################
}