<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Translation extends Mage_Core_Helper_Abstract
{
    private $text;
    private $placeholders = array();

    private $values = array();
    private $args = array();

    private $translatedText;
    private $processedPlaceholders = array();
    private $processedArgs = array();

    // ##################################

    public function translate(array $args)
    {
        $this->reset();

        $this->parseInput($args);
        $this->parsePlaceholders();

        if (count($this->placeholders) <= 0) {
            array_unshift($this->args, $this->text);
            return call_user_func_array(array($this,'__'), $this->args);
        }

        $this->translatedText = parent::__($this->text);

        $this->replacePlaceholdersByValue();
        $this->replacePlaceholdersByArgs();

        $unprocessedArgs = array_diff($this->args, $this->processedArgs);
        if(!$unprocessedArgs) {
            return $this->translatedText;
        }

        return vsprintf($this->translatedText, $unprocessedArgs);
    }

    // ##################################

    private function reset()
    {
        $this->text = null;
        $this->values = array();
        $this->args = array();
        $this->placeholders = array();
        $this->processedPlaceholders = array();
        $this->processedArgs = array();
        $this->translatedText = null;
    }

    // ----------------------------------

    private function parseInput(array $input)
    {
        $this->text = array_shift($input);

        if (is_array(current($input))) {
            $this->values = array_shift($input);
        }

        $this->args = $input;
    }

    private function parsePlaceholders()
    {
        preg_match_all('/%[\w\d]+%/', $this->text , $placeholders);
        $this->placeholders = array_unique($placeholders[0]);
    }

    // ##################################

    private function replacePlaceholdersByValue()
    {
        foreach ($this->values as $placeholder=>$value) {

            $newText = str_replace('%'.$placeholder.'%', $value, $this->translatedText, $count);

            if ($count <= 0) {
                continue;
            }

            $this->translatedText = $newText;
            $this->processedPlaceholders[] = '%'.$placeholder.'%';
        }
    }

    private function replacePlaceholdersByArgs()
    {
        $unprocessedPlaceholders = array_diff($this->placeholders, $this->processedPlaceholders);
        $unprocessedArgs = $this->args;

        foreach ($unprocessedPlaceholders as $placeholder) {

            $value = array_shift($unprocessedArgs);

            if (is_null($value)) {
                break;
            }

            $this->translatedText = str_replace($placeholder, $value, $this->translatedText);

            $this->processedPlaceholders[] = $placeholder;
            $this->processedArgs[] = $value;
        }
    }

    // ##################################
}