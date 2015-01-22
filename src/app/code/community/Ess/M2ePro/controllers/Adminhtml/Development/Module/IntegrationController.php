<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_IntegrationController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Revise Total"
     * @description "Full Force Revise"
     * @new_line
     */
    public function reviseTotalAction()
    {
        $html = '';
        foreach (Mage::helper('M2ePro/Component')->getActiveComponents() as $component) {

            $reviseAllStartDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                "/{$component}/templates/revise/total/", 'start_date'
            );

            $reviseAllEndDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                "/{$component}/templates/revise/total/", 'end_date'
            );

            $reviseAllInProcessingState = !is_null(
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                    "/{$component}/templates/revise/total/", 'last_listing_product_id'
                )
            );

            $urlHelper = Mage::helper('adminhtml');

            $runNowUrl = $urlHelper->getUrl('*/*/processReviseTotal', array('component' => $component));
            $resetUrl = $urlHelper->getUrl('*/*/resetReviseTotal', array('component' => $component));

            $html .= <<<HTML
<div>
    <span style="display:inline-block; width: 100px;">{$component}</span>
    <span style="display:inline-block; width: 150px;">
        <button onclick="window.location='{$runNowUrl}'">turn on</button>
        <button onclick="window.location='{$resetUrl}'">stop</button>
    </span>
    <span id="{$component}_start_date" style="color: indianred; display: none;">
        Started at - {$reviseAllStartDate}
    </span>
    <span id="{$component}_end_date" style="color: green; display: none;">
        Finished at - {$reviseAllEndDate}
    </span>
</div>

HTML;
            $html.= "<script type=\"text/javascript\">";
            if ($reviseAllInProcessingState) {
                $html .= "document.getElementById('{$component}_start_date').style.display = 'inline-block';";
            } else {

                if ($reviseAllEndDate) {
                    $html .= "document.getElementById('{$component}_end_date').style.display = 'inline-block';";
                }
            }
            $html.= "</script>";
        }

        echo $html;
    }

    /**
     * @title "Process Revise Total for Component"
     * @hidden
    */
    public function processReviseTotalAction()
    {
        $component = $this->getRequest()->getParam('component', false);

        if (!$component) {
            $this->_getSession()->addError('Component is not presented.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
        }

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'start_date', Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'end_date', null
        );

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'last_listing_product_id', 0
        );

        $this->_redirect('*/*/reviseTotal');
    }

    /**
     * @title "Reset Revise Total for Component"
     * @hidden
     */
    public function resetReviseTotalAction()
    {
        $component = $this->getRequest()->getParam('component', false);

        if (!$component) {
            $this->_getSession()->addError('Component is not presented.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
        }

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'last_listing_product_id', null
        );

        $this->_redirect('*/*/reviseTotal');
    }

    //#############################################

    /**
     * @title "Reset eBay 3rd Party"
     * @description "Clear all eBay 3rd party items for all Accounts"
     */
    public function resetOtherListingsAction()
    {
        $listingOther = Mage::getModel('M2ePro/Listing_Other');
        $ebayListingOther = Mage::getModel('M2ePro/Ebay_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other')->getSelect()->query();

        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $ebayListingOther->setData($row);

            $listingOther->setChildObject($ebayListingOther);
            $ebayListingOther->setParentObject($listingOther);

            $listingOther->deleteInstance();
        }

        foreach (Mage::helper('M2ePro/Component_Ebay')->getCollection('Account') as $account) {
            $account->setData('other_listings_last_synchronization',NULL)->save();
        }

        $this->_getSession()->addSuccess('Successfully removed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    /**
     * @title "Reset eBay Images Hashes"
     * @description "Clear eBay images hashes for listing products"
     * @prompt "Please enter Listing Product ID or `all` code for reset all products."
     * @prompt_var "listing_product_id"
     */
    public function resetEbayImagesHashesAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');

        $listingProducts = array();
        if (strtolower($listingProductId) == 'all') {

            $listingProducts = Mage::getModel('M2ePro/Listing_Product')->getCollection()
                ->addFieldToFilter('component_mode', 'ebay');
        } else {

            $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load((int)$listingProductId);
            $listingProduct && $listingProducts[] = $listingProduct;
        }

        if (empty($listingProducts)) {
            $this->_getSession()->addError('Failed to load listing product.');
            return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
        }

        $affected = 0;
        foreach ($listingProducts as $listingProduct) {

            $additionalData = $listingProduct->getAdditionalData();

            if (!isset($additionalData['ebay_product_images_hash']) &&
                !isset($additionalData['ebay_product_variation_images_hash'])) {
                continue;
            }

            unset($additionalData['ebay_product_images_hash'],
                  $additionalData['ebay_product_variation_images_hash']);

            $affected++;
            $listingProduct->setData('additional_data', json_encode($additionalData))
                           ->save();
        }

        $this->_getSession()->addSuccess("Successfully removed for {$affected} affected products.");
        return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    /**
     * @title "Set eBay EPS Images Mode"
     * @description "Set EPS Images Mode = true for listing products"
     * @prompt "Please enter Listing Product ID or `all` code for all products."
     * @prompt_var "listing_product_id"
     */
    public function setEpsImagesModeAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');

        $listingProducts = array();
        if (strtolower($listingProductId) == 'all') {

            $listingProducts = Mage::getModel('M2ePro/Listing_Product')->getCollection()
                ->addFieldToFilter('component_mode', 'ebay');
        } else {

            $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load((int)$listingProductId);
            $listingProduct && $listingProducts[] = $listingProduct;
        }

        if (empty($listingProducts)) {
            $this->_getSession()->addError('Failed to load listing product.');
            return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
        }

        $affected = 0;
        foreach ($listingProducts as $listingProduct) {

            $additionalData = $listingProduct->getAdditionalData();

            if (!isset($additionalData['is_eps_ebay_images_mode']) ||
                $additionalData['is_eps_ebay_images_mode'] == true) {
                continue;
            }

            $additionalData['is_eps_ebay_images_mode'] = true;
            $affected++;

            $listingProduct->setData('additional_data', json_encode($additionalData))
                           ->save();
        }

        $this->_getSession()->addSuccess("Successfully set for {$affected} affected products.");
        return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################

    /**
     * @title "Show eBay Nonexistent Templates"
     * @description "Show Nonexistent Templates [eBay]"
     * @new_line
     */
    public function showNonexistentTemplatesAction()
    {
        $nonexistentTemplates = array();

        $simpleTemplates = array('category', 'other_category');
        foreach ($simpleTemplates as $templateName) {

            $tempResult = $this->getNonexistentTemplatesBySimpleLogic($templateName);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        $difficultTemplates = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
        );
        foreach ($difficultTemplates as $templateName) {

            $tempResult = $this->getNonexistentTemplatesByDifficultLogic($templateName);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        if (count($nonexistentTemplates) <= 0) {
            echo $this->getEmptyResultsHtml('There are no any nonexistent templates.');
            return;
        }

        $tableContent = <<<HTML
<tr>
    <th>Template Code</th>
    <th>Listing Product ID</th>
    <th>Listing ID</th>
    <th>Template ID</th>
    <th>My Mode</th>
    <th>Parent Mode</th>
</tr>
HTML;

        foreach ($nonexistentTemplates as $templateName => $items) {

            foreach ($items as $index => $itemInfo) {

                $myMode = '';
                if (isset($itemInfo['my_mode'])) {
                    $myMode = 'parent';
                    (int)$itemInfo['my_mode'] == 1 && $myMode = 'custom';
                    (int)$itemInfo['my_mode'] == 2 && $myMode = 'template';
                }

                $parentMode = '';
                if (isset($itemInfo['parent_mode']) && isset($itemInfo['my_mode']) && (int)$itemInfo['my_mode'] == 0) {
                    $parentMode = (int)$itemInfo['parent_mode'] == 1 ? 'custom' : 'template';
                }

                $tableContent .= <<<HTML
<tr>
    <td>{$templateName}</td>
    <td>{$itemInfo['my_id']}</td>
    <td>{$itemInfo['listing_id']}</td>
    <td>{$itemInfo['my_needed_id']}</td>
    <td>{$myMode}</td>
    <td>{$parentMode}</td>
</tr>
HTML;
            }
            $tableContent .= "</tr>";
        }

        echo $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Nonexistent templates
            <span style="color: #808080; font-size: 15px;">( entries)</span>
        </h2>
        <br>
        <table class="grid" cellpadding="0" cellspacing="0">
            {$tableContent}
        </table>
    </body>
</html>
HTML;
    }

    //todo change when description will be horizontal
    private function getNonexistentTemplatesByDifficultLogic($templateCode)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_write');

        $subSelect = $connRead->select()
            ->from(
                array('melp' => $resource->getTableName('m2epro_ebay_listing_product')),
                array(
                    'my_id'          => 'listing_product_id',
                    'my_mode'        => "template_{$templateCode}_mode",
                    'my_template_id' => "template_{$templateCode}_id",
                    'my_custom_id'   => "template_{$templateCode}_custom_id",

                    'my_needed_id'   => new Zend_Db_Expr(
                    "CASE
                        WHEN melp.template_{$templateCode}_mode = 2 THEN melp.template_{$templateCode}_id
                        WHEN melp.template_{$templateCode}_mode = 1 THEN melp.template_{$templateCode}_custom_id
                        WHEN melp.template_{$templateCode}_mode = 0 THEN IF(mel.template_{$templateCode}_mode = 1,
                                                                            mel.template_{$templateCode}_custom_id,
                                                                            mel.template_{$templateCode}_id)
                    END"
                    ))
            )
            ->joinLeft(
                array('mlp' => $resource->getTableName('m2epro_listing_product')),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array('mel' => $resource->getTableName('m2epro_ebay_listing')),
                'mlp.listing_id = mel.listing_id',
                array(
                    'parent_mode'        => "template_{$templateCode}_mode",
                    'parent_template_id' => "template_{$templateCode}_id",
                    'parent_custom_id'   => "template_{$templateCode}_custom_id"
                )
            );

        $templateIdName = 'id';
        $horizontalTemplates = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        );
        in_array($templateCode, $horizontalTemplates) && $templateIdName = "template_{$templateCode}_id";

        $result = $connRead->select()
           ->from(
               array('subselect' => new Zend_Db_Expr('('.$subSelect->__toString().')')),
               array(
                   'subselect.my_id',
                   'subselect.listing_id',
                   'subselect.my_mode',
                   'subselect.parent_mode',
                   'subselect.my_needed_id',
               )
           )
           ->joinLeft(
               array('template' => $resource->getTableName("m2epro_ebay_template_{$templateCode}")),
               "subselect.my_needed_id = template.{$templateIdName}",
               array()
           )
           ->where("template.{$templateIdName} IS NULL")
           ->query()->fetchAll();

        return $result;
    }

    private function getNonexistentTemplatesBySimpleLogic($templateCode)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_write');

        $select = $connRead->select()
            ->from(
                array('melp' => $resource->getTableName('m2epro_ebay_listing_product')),
                array(
                    'my_id'        => 'listing_product_id',
                    'my_needed_id' => "template_{$templateCode}_id",
                )
            )
            ->joinLeft(
                array('mlp' => $resource->getTableName('m2epro_listing_product')),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array('template' => $resource->getTableName("m2epro_ebay_template_{$templateCode}")),
                "melp.template_{$templateCode}_id = template.id",
                array()
            )
            ->where("melp.template_{$templateCode}_id IS NOT NULL")
            ->where("template.id IS NULL");

        return $select->query()->fetchAll();
    }

    //#############################################

    /**
     * @title "Add Products into Listing"
     * @description "Mass Action by SKU or Magento Product ID"
     */
    public function addProductsToListingAction()
    {
        $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/processAddProductsToListing');
        $formKey = Mage::getSingleton('core/session')->getFormKey();

        $collection = Mage::getModel('M2ePro/Listing')->getCollection()
                                                      ->addOrder('component_mode');
        $currentOptGroup = null;
        $listingsOptionsHtml = '';

        /** @var Ess_M2ePro_Model_Listing $listing */
        foreach ($collection as $listing) {

            $currentOptGroup != $listing->getComponentMode() && !is_null($currentOptGroup)
                && $listingsOptionsHtml .= '</optgroup>';

            $currentOptGroup != $listing->getComponentMode()
                && $listingsOptionsHtml .= '<optgroup label="'.$listing->getComponentMode().'">';

            $tempValue = "[{$listing->getId()}]  {$listing->getTitle()}]";
            $listingsOptionsHtml .= '<option value="'.$listing->getId().'">'.$tempValue.'</option>';

            $currentOptGroup = $listing->getComponentMode();
        }

        echo <<<HTML
<form method="post" enctype="multipart/form-data" action="{$actionUrl}">

    <input name="form_key" value="{$formKey}" type="hidden" />

    <label style="display: inline-block; width: 150px;">Source:&nbsp;</label>
    <input type="file" accept=".csv" name="source" required /><br>

    <label style="display: inline-block; width: 150px;">Identifier Type:&nbsp;</label>
    <select style="width: 250px;" name="source_type" required>
        <option value="sku">SKU</option>
        <option value="id">Product ID</option>
    </select><br>

    <label style="display: inline-block; width: 150px;">Target Listing:&nbsp;</label>
    <select style="width: 250px;" name="listing_id" required>
        <option style="display: none;"></option>
        {$listingsOptionsHtml}
    </select><br>

    <input type="submit" title="Run Now" onclick="return confirm('Are you sure?');" />
</form>
HTML;
    }

    /**
     * @title "Process Adding Products into Listing"
     * @hidden
     */
    public function processAddProductsToListingAction()
    {
        $sourceType = $this->getRequest()->getPost('source_type', 'sku');
        $listing = Mage::getModel('M2ePro/Listing')->load($this->getRequest()->getPost('listing_id'));

        if (empty($_FILES['source']['tmp_name']) || !$listing) {
            $this->_getSession()->addError('Some required fields are empty.');
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/processAddProductsToListing'));
        }

        $csvParser = new Varien_File_Csv();
        $tempCsvData = $csvParser->getData($_FILES['source']['tmp_name']);

        $csvData = array();
        $headers = array_shift($tempCsvData);
        foreach ($tempCsvData as $csvRow) {
            $csvData[] = array_combine($headers, $csvRow);
        }

        $success = 0;
        foreach ($csvData as $csvRow) {

            $magentoProduct = $sourceType == 'id'
                ? Mage::getModel('catalog/product')->load($csvRow['id'])
                : Mage::getModel('catalog/product')->loadByAttribute('sku', $csvRow['sku']);

            if (!$magentoProduct) {
                continue;
            }

            $listingProduct = $listing->addProduct($magentoProduct);
            if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                $success++;
            }
        }

        $this->_getSession() ->addSuccess("Success '{$success}' products.");
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################

    private function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl();

        return <<<HTML
    <h2 style="margin: 20px 0 0 10px">
        {$messageText} <span style="color: grey; font-size: 10px;">
        <a href="{$backUrl}">[back]</a>
    </h2>
HTML;
    }

    //#############################################
}