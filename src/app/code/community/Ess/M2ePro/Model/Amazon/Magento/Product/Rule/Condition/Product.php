<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Condition_Product
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Product
{
    protected function getCustomFilters()
    {
        $amazonFilters = array(
            'amazon_sku'               => 'AmazonSku',
            'amazon_general_id'        => 'AmazonGeneralId',
            'amazon_online_qty'        => 'AmazonOnlineQty',
            'amazon_online_price'      => 'AmazonOnlinePrice',
            'amazon_online_sale_price' => 'AmazonOnlineSalePrice',
            'amazon_is_afn_chanel'     => 'AmazonIsAfnChanel',
            'amazon_status'            => 'AmazonStatus',
            'amazon_general_id_state'  => 'AmazonGeneralIdState'
        );

        return array_merge_recursive(
            parent::getCustomFilters(),
            $amazonFilters
        );
    }

    /**
     * @param $filterId
     * @return Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
     */
    protected function getCustomFilterInstance($filterId)
    {
        $parentFilters = parent::getCustomFilters();
        if (isset($parentFilters[$filterId])) {
            return parent::getCustomFilterInstance($filterId);
        }

        $customFilters = $this->getCustomFilters();
        $this->_customFiltersCache[$filterId] = Mage::getModel(
            'M2ePro/Amazon_Magento_Product_Rule_Custom_'.$customFilters[$filterId]
        );

        return $this->_customFiltersCache[$filterId];
    }

    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        if ($this->getInputType() == 'date' && !empty($validatedValue) && !is_numeric($validatedValue)) {
            $validatedValue = strtotime($validatedValue);
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        if ($this->getInputType() == 'date' && !empty($value) && !is_numeric($value)) {
            $value = strtotime($value);
        }

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
            if (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue)) {

                    // hack for amazon status
                    if ($this->getAttribute() == 'amazon_status') {
                        if ($op == '==') {
                            $result = !empty($validatedValue[$value]);
                        } else {
                            $result = true;
                            foreach ($validatedValue as $status => $childrenCount) {
                                if ($status != $value && !empty($childrenCount)) {
                                    // will be true at the end of this method
                                    $result = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                    }
                } else {
                    $result = $this->_compareValues($validatedValue, $value);
                }
            }
            break;

            case '<=': case '>':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue <= $value;
            }
            break;

            case '>=': case '<':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue >= $value;
            }
            break;

            case '{}': case '!{}':
            if (is_scalar($validatedValue) && is_array($value)) {
                foreach ($value as $item) {
                    if (stripos($validatedValue,$item)!==false) {
                        $result = true;
                        break;
                    }
                }
            } elseif (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue)) {
                    $result = in_array($value, $validatedValue);
                } else {
                    $result = $this->_compareValues($value, $validatedValue, false);
                }
            }
            break;

            case '()': case '!()':
            if (is_array($validatedValue)) {
                $result = count(array_intersect($validatedValue, (array)$value))>0;
            } else {
                $value = (array)$value;
                foreach ($value as $item) {
                    if ($this->_compareValues($validatedValue, $item)) {
                        $result = true;
                        break;
                    }
                }
            }
            break;
        }

        if ('!=' == $op || '>' == $op || '<' == $op || '!{}' == $op || '!()' == $op) {
            $result = !$result;
        }

        return $result;
    }
}