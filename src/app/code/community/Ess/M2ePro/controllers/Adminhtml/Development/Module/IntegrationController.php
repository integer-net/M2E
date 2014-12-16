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
     * @title "Set EPS Images Mode"
     * @description "Set EPS Images Mode = true for listing products"
     * @prompt "Please enter Listing Product ID or `all` code for all products."
     * @prompt_var "listing_product_id"
     * @new_line
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
}