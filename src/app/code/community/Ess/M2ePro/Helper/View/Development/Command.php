<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Development_Command extends Mage_Core_Helper_Abstract
{
    // ########################################

    const CONTROLLER_MODULE_MODULE          = 'adminhtml_development_module_module';
    const CONTROLLER_MODULE_SYNCHRONIZATION = 'adminhtml_development_module_synchronization';
    const CONTROLLER_MODULE_EBAY            = 'adminhtml_development_module_ebay';

    const CONTROLLER_TOOLS_M2EPRO_GENERAL   = 'adminhtml_development_tools_m2ePro_general';
    const CONTROLLER_TOOLS_M2EPRO_INSTALL   = 'adminhtml_development_tools_m2ePro_install';
    const CONTROLLER_TOOLS_MAGENTO          = 'adminhtml_development_tools_magento';
    const CONTROLLER_TOOLS_ADDITIONAL       = 'adminhtml_development_tools_additional';

    const CONTROLLER_DEBUG                  = 'adminhtml_development';

    const CONTROLLER_BUILD                  = 'adminhtml_development_build';

    // ########################################

    public function parseGeneralCommandsData($controller)
    {
        $tempClass = Mage::helper('M2ePro/View_Development_Controller')->loadControllerAndGetClassName($controller);

        $reflectionClass = new ReflectionClass ($tempClass);
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        // Get actions methods
        //----------------------------------
        $actions = array();
        foreach ($reflectionMethods as $reflectionMethod) {

            $className = $reflectionClass->getMethod($reflectionMethod->name)
                                         ->getDeclaringClass()->name;
            $methodName = $reflectionMethod->name;

            if (substr($className,0,10) != 'Ess_M2ePro') {
                continue;
            }
            if ($methodName == 'indexAction') {
                continue;
            }
            if (substr($methodName,strlen($methodName)-6) != 'Action') {
                continue;
            }

            $methodName = substr($methodName,0,strlen($methodName)-6);

            $actions[] = $methodName;
        }
        //----------------------------------

        // Print method actions
        //----------------------------------
        $methods = array();
        foreach ($actions as $action) {

            $controllerName = Mage::helper('M2ePro/View_Development_Controller')->getControllerClassName($controller);
            $reflectionMethod = new ReflectionMethod ($controllerName,$action.'Action');

            $commentsString = $this->getMethodComments($reflectionMethod);

            preg_match('/@hidden/', $commentsString, $matchesHidden);

            if (isset($matchesHidden[0])) {
                continue;
            }

            preg_match('/@title[\s]*\"(.*)\"/', $commentsString, $matchesTitle);

            $methodTitle = '';
            if (isset($matchesTitle[1])) {
                $methodTitle = $matchesTitle[1];
            } else {
                $methodTitle = $action;
            }

            preg_match('/@description[\s]*\"(.*)\"/', $commentsString, $matchesDescription);

            $methodDescription = '';
            if (isset($matchesDescription[1])) {
                $methodDescription = $matchesDescription[1];
            }

            $methodUrl = Mage::helper('adminhtml')->getUrl('*/'.$controller.'/'.$action);

            $methodContent = '';
            $fileContent = file($reflectionMethod->getFileName());
            for($i=$reflectionMethod->getStartLine()+2;$i<$reflectionMethod->getEndLine();$i++) {
                $methodContent .= $fileContent[$i-1];
            }

            preg_match('/@new_line/', $commentsString, $matchesNewLine);
            $methodNewLine = isset($matchesNewLine[0]);

            preg_match('/@confirm[\s]*\"(.*)\"/', $commentsString, $matchesConfirm);
            $methodConfirm = '';
            if (isset($matchesConfirm[1])) {
                $methodConfirm = $matchesConfirm[1];
            }

            preg_match('/@components[ ]*(.*)/', $commentsString, $matchesComponents);
            $methodComponents = isset($matchesComponents[0])
                ? (!empty($matchesComponents[1]) ? explode(',', $matchesComponents[1]) : true)
                : false;

            preg_match('/new_window/', $commentsString, $matchesNewWindow);
            $methodNewWindow = isset($matchesNewWindow[0]);

            $methods[] = array(
                'title' => $methodTitle,
                'description' => $methodDescription,
                'url' => $methodUrl,
                'content' => $methodContent,
                'new_line' => $methodNewLine,
                'confirm' => $methodConfirm,
                'components' => $methodComponents,
                'new_window' => $methodNewWindow
            );
        }
        //----------------------------------

        return $methods;
    }

    //-----------------------------------------

    public function parseDebugCommandsData($controller)
    {
        $tempClass = Mage::helper('M2ePro/View_Development_Controller')->loadControllerAndGetClassName($controller);

        $reflectionClass = new ReflectionClass ($tempClass);
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        // Get actions methods
        //----------------------------------
        $actions = array();
        foreach ($reflectionMethods as $reflectionMethod) {

            $className = $reflectionClass->getMethod($reflectionMethod->name)->getDeclaringClass()->name;
            $methodName = $reflectionMethod->name;

            if (substr($className,0,10) != 'Ess_M2ePro') {
                continue;
            }
            if ($methodName == 'indexAction') {
                continue;
            }
            if (substr($methodName,strlen($methodName)-6) != 'Action') {
                continue;
            }

            $methodName = substr($methodName,0,strlen($methodName)-6);

            $actions[] = $methodName;
        }
        //----------------------------------

        // Print method actions
        //----------------------------------
        $methods = array();
        foreach ($actions as $action) {

            $controllerName = Mage::helper('M2ePro/View_Development_Controller')->getControllerClassName($controller);
            $reflectionMethod = new ReflectionMethod ($controllerName,$action.'Action');

            $commentsString = $this->getMethodComments($reflectionMethod);

            preg_match('/@hidden/', $commentsString, $matchesHidden);

            if (isset($matchesHidden[0])) {
                continue;
            }

            preg_match('/@title[\s]*\"(.*)\"/', $commentsString, $matchesTitle);
            preg_match('/@description[\s]*\"(.*)\"/', $commentsString, $matchesDescription);

            if (!isset($matchesTitle[1]) || !isset($matchesDescription[1])) {
                continue;
            }

            $methodTitle = $matchesTitle[1];
            $methodDescription = $matchesDescription[1];

            $methodUrl = Mage::helper('adminhtml')->getUrl('*/'.$controller.'/'.$action);

            preg_match('/@confirm[\s]*\"(.*)\"/', $commentsString, $matchesConfirm);
            $methodConfirm = '';
            if (isset($matchesConfirm[1])) {
                $methodConfirm = $matchesConfirm[1];
            }

            preg_match('/new_window/', $commentsString, $matchesNewWindow);
            $methodNewWindow = isset($matchesNewWindow[0]);

            $methods[] = array(
                'title' => $methodTitle,
                'description' => $methodDescription,
                'url' => $methodUrl,
                'confirm' => $methodConfirm,
                'new_window' => $methodNewWindow
            );
        }
        //----------------------------------

        return $methods;
    }

    // ########################################

    private function getMethodComments(ReflectionMethod $reflectionMethod)
    {
        $contentPhpFile = file_get_contents($reflectionMethod->getFileName());
        $contentPhpFile = explode(chr(10),$contentPhpFile);

        $commentsArray = array();
        for ($i=$reflectionMethod->getStartLine()-2;$i>0;$i--) {
            $contentPhpFile[$i] = trim($contentPhpFile[$i]);
            $commentsArray[] = $contentPhpFile[$i];
            if ($contentPhpFile[$i] == '/**' ||
                $contentPhpFile[$i] == '}') {
                break;
            }
        }

        $commentsArray = array_reverse($commentsArray);
        $commentsString = implode(chr(10),$commentsArray);

        return $commentsString;
    }

    // ########################################
}