<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Development_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    const REAL_MODULE = 'Ess_M2ePro';

    // ########################################

    public function loadControllerAndGetClassName($controller)
    {
        $controllerFileName = $this->getControllerFileName($controller);
        if (!$this->validateControllerFileName($controllerFileName)) {
            return false;
        }

        $controllerClassName = $this->getControllerClassName($controller);
        if (!$controllerClassName) {
            return false;
        }

        // include controller file if needed
        if (!$this->_includeControllerClass($controllerFileName, $controllerClassName)) {
            return false;
        }

        return $controllerClassName;
    }

    // ########################################

    public function getControllerFileName($controller)
    {
        $parts = explode('_', self::REAL_MODULE);
        $realModule = implode('_', array_splice($parts, 0, 2));
        $file = Mage::getModuleDir('controllers', $realModule);
        if (count($parts)) {
            $file .= DS . implode(DS, $parts);
        }
        $file .= DS.uc_words($controller, DS).'Controller.php';
        return $file;
    }

    public function validateControllerFileName($fileName)
    {
        if ($fileName && is_readable($fileName) && false===strpos($fileName, '//')) {
            return true;
        }
        return false;
    }

    //----------------------------------------

    public function getControllerClassName($controller)
    {
        $class = self::REAL_MODULE.'_'.uc_words($controller).'Controller';
        return $class;
    }

    //----------------------------------------

    protected function _includeControllerClass($controllerFileName, $controllerClassName)
    {
        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return false;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                throw Mage::exception('Mage_Core', Mage::helper('core')->__('Controller file was loaded but class does not exist'));
            }
        }
        return true;
    }

    // ########################################
}