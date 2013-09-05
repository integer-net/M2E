<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Amazon_Validation extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function isASIN($string)
    {
        return !empty($string) &&
               $string{0} == 'B' &&
               strlen($string) == 10;
    }

    public function isISBN($string)
    {
        $string = (string)$string;

        if (strlen($string) == 10) {

            $subTotal = 0;
            $mpBase = 10;
            for ($x=0; $x<=8; $x++) {
                $mp = $mpBase - $x;
                $subTotal += ($mp * $string{$x});
            }

            $rest = $subTotal % 11;
            $checkDigit = $string{9};
            if (strtolower($checkDigit) == "x") {
                $checkDigit = 10;
            }

            return $checkDigit == (11 - $rest);

        } elseif (strlen($string) == 13) {

            $subTotal = 0;
            for ($x=0; $x<=11; $x++) {
                $mp = ($x + 1) % 2 == 0 ? 3 : 1;
                $subTotal += $mp * $string{$x};
            }

            $rest = $subTotal % 10;
            $checkDigit = $string{12};
            if (strtolower($checkDigit) == "x") {
                $checkDigit = 10;
            }

            return $checkDigit == (10 - $rest);
        }

        return false;
    }

    //-----------------------------------------

    public function isUPC($upc)
    {
        return $this->isWorldWideId($upc,'UPC');
    }

    public function isEAN($ean)
    {
        return $this->isWorldWideId($ean,'EAN');
    }

    // ########################################

    private function isWorldWideId($worldWideId,$type)
    {
        $adapters = array(
            'UPC' => array(
                '8'  => 'Upce',
                '12' => 'Upca'
            ),
            'EAN' => array(
                '8'  => 'Ean8',
                '13' => 'Ean13'
            )
        );

        $length = strlen($worldWideId);

        if (!isset($adapters[$type],$adapters[$type][$length])) {
            return false;
        }

        try {
            $validator = new Zend_Validate_Barcode($adapters[$type][$length]);
            return $validator->isValid($worldWideId);
        } catch (Zend_Validate_Exception $e) {
            return true;
        }
    }

    // ########################################
}