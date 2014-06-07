<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_OperationHistory extends Ess_M2ePro_Model_OperationHistory
{
    //#########################################

    private $timePoints = array();
    private $leftPadding = 0;
    private $bufferString = '';

    // ########################################

    public function addEol()
    {
        $this->appendEol();
        $this->saveBufferString();
    }

    public function appendEol()
    {
        $this->appendText();
    }

    // ----------------------------------------

    public function addLine($char = '-')
    {
        $this->appendLine($char);
        $this->saveBufferString();
    }

    public function appendLine($char = '-')
    {
        $this->appendText(str_repeat($char, 30));
    }

    // ----------------------------------------

    public function addText($text = NULL)
    {
        $this->appendText($text);
        $this->saveBufferString();
    }

    public function appendText($text = NULL)
    {
        $text && $text = str_repeat(' ',$this->leftPadding).$text;
        $this->bufferString .= (string)$text.PHP_EOL;
    }

    // ----------------------------------------

    public function saveBufferString()
    {
        $profilerData = (string)$this->getContentData('profiler');
        $this->setContentData('profiler',$profilerData.$this->bufferString);
        $this->bufferString = '';
    }

    // ########################################

    public function addTimePoint($id, $title)
    {
        foreach ($this->timePoints as &$point) {
            if ($point['id'] == $id) {
                $this->updateTimePoint($id);
                return true;
            }
        }

        $this->timePoints[] = array(
            'id' => $id,
            'title' => $title,
            'time' => microtime(true)
        );

        return true;
    }

    public function updateTimePoint($id)
    {
        foreach ($this->timePoints as $point) {

            if ($point['id'] == $id) {

                $point['time'] = microtime(true);
                return true;
            }
        }

        return false;
    }

    public function saveTimePoint($id, $immediatelySave = true)
    {
        foreach ($this->timePoints as $point) {

            if ($point['id'] == $id) {

                $this->appendText(
                    $point['title'].': '.round(microtime(true) - $point['time'],2).' sec.'
                );

                $immediatelySave && $this->saveBufferString();
                return true;
            }
        }

        return false;
    }

    // ########################################

    public function increaseLeftPadding($count = 5)
    {
        $this->leftPadding += (int)$count;
    }

    public function decreaseLeftPadding($count = 5)
    {
        $this->leftPadding -= (int)$count;
        $this->leftPadding < 0 && $this->leftPadding = 0;
    }

    //####################################

    public function getProfilerInfo($nestingLevel = 0)
    {
        if (is_null($this->getObject())) {
            return NULL;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#',80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));
        strpos($nick,'_') !== false && $nick = str_replace('SYNCHRONIZATION_','',$nick);

        $profilerData = preg_replace('/^/m', "{$offset}", $this->getContentData('profiler'));

        return <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$profilerData}
{$offset}{$separationLine}

INFO;
    }

    public function getFullProfilerInfo($nestingLevel = 0)
    {
        if (is_null($this->getObject())) {
            return NULL;
        }

        $profilerInfo = $this->getProfilerInfo($nestingLevel);

        $childObjects = Mage::getModel('M2ePro/OperationHistory')->getCollection()
                                ->addFieldToFilter('parent_id', $this->getObject()->getId())
                                ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {

            $object = Mage::getModel('M2ePro/Synchronization_OperationHistory');
            $object->setObject($item);

            $profilerInfo .= $object->getFullProfilerInfo($nestingLevel);
        }

        return $profilerInfo;
    }

    //####################################
}