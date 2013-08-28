<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_CmdController extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        if (Mage::helper('M2ePro/Module')->isMaintenanceEnabled()) {
            Mage::helper('M2ePro')->setSessionValue('warning_message', 'Maintenance is Active.');
        }

        return $this;
    }

    //#############################################

    public function indexAction()
    {
        $this->printCommandsList();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro');
    }

    //#############################################

    public function postDispatch()
    {
        parent::postDispatch();

        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_cmd_footer');
        $this->getResponse()->appendBody($block->toHtml());
    }

    //#############################################

    protected function getGroupsCommandsData()
    {
        $reflectionClass = new ReflectionClass ('Ess_M2ePro_Adminhtml_CmdController');
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

            $reflectionMethod = new ReflectionMethod ('Ess_M2ePro_Adminhtml_CmdController',$action.'Action');
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

            $methodUrl = $this->getUrl('*/*/'.$action);

            $methodContent = '';
            $fileContent = file($reflectionMethod->getFileName());
            for($i=$reflectionMethod->getStartLine()+2;$i<$reflectionMethod->getEndLine();$i++) {
                $methodContent .= $fileContent[$i-1];
            }

            preg_match('/@group[\s]*\"(.*)\"/', $commentsString, $matchesGroup);

            $methodGroup = '';
            if (isset($matchesGroup[1])) {
                $methodGroup = $matchesGroup[1];
            } else {
                $methodGroup = 'Default';
            }
            $methodGroup = strtolower($methodGroup);

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

            if (!isset($methods[$methodGroup])) {
                $methods[$methodGroup] = array();
            }

            $methods[$methodGroup][] = array(
                'title' => $methodTitle,
                'description' => $methodDescription,
                'group' => ucfirst($methodGroup),
                'url' => $methodUrl,
                'content' => $methodContent,
                'new_line' => $methodNewLine,
                'confirm' => $methodConfirm,
                'components' => $methodComponents
            );
        }
        //----------------------------------

        return $methods;
    }

    private function getMethodComments(ReflectionMethod $reflectionMethod)
    {
        $contentPhpFile = file_get_contents($reflectionMethod->getFileName());
        $contentPhpFile = explode(chr(10),$contentPhpFile);

        $commentsArray = array();
        for ($i=$reflectionMethod->getStartLine()-2;$i>0;$i--) {
            $contentPhpFile[$i] = trim($contentPhpFile[$i]);
            $commentsArray[] = $contentPhpFile[$i];
            if ($contentPhpFile[$i] == '/**') {
                break;
            }
        }

        $commentsArray = array_reverse($commentsArray);
        $commentsString = implode(chr(10),$commentsArray);

        return $commentsString;
    }

    //#############################################

    protected function printBack()
    {
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_cmd_backButton');
        echo $block->toHtml();
    }

    protected function printCommandsList()
    {
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_cmd_controlPanel');

        $params = array('groups'=>$this->getGroupsCommandsData());

        $tempMessage = Mage::helper('M2ePro')->getSessionValue('success_message', true);
        $tempMessage && $params['success_message'] = $tempMessage;

        $tempMessage = Mage::helper('M2ePro')->getSessionValue('error_message', true);
        $tempMessage && $params['error_message'] = $tempMessage;

        $tempMessage = Mage::helper('M2ePro')->getSessionValue('warning_message', true);
        $tempMessage && $params['warning_message'] = $tempMessage;

        $block->setData($params);
        echo $block->toHtml();
    }

    //#############################################
}