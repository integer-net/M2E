<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 * Shipping method with custom title and price
 */

class Ess_M2ePro_Model_Support
{
    //#############################################

    public function getUserVoiceData($query) {

        $userVoiceEnabled = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/support/uservoice/', 'mode'
        );

        if (!$userVoiceEnabled || is_null($query)) {
            return array();
        }

        $query = strip_tags($query);

        $userVoiceApiUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/support/uservoice/', 'baseurl'
        );
        $client = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'client_key');
        $params = array(
            'client' => $client,
            'query' => $query,
            'page' => 1,
            'per_page' => 10
        );

        $articles = array();
        $articlesAction = 'articles/search.json';
        $articlesResponse = $this->sendRequestAsGet($userVoiceApiUrl, $articlesAction, $params);
        if ($articlesResponse !== false) {
            $articles = json_decode($articlesResponse, true);
        }

        $suggestions = array();
        $suggestionsAction = 'suggestions/search.json';
        $suggestionsResponse = $this->sendRequestAsGet($userVoiceApiUrl, $suggestionsAction, $params);
        if ($suggestionsResponse !== false) {
            $suggestions = json_decode($suggestionsResponse, true);
        }

        return array_merge($articles, $suggestions);
    }

    //---------------------------------------------

    private function sendRequestAsGet($baseUrl, $action, $params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $baseUrl . $action . '?'.http_build_query($params,'','&'));

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        return $response;
    }

    //#############################################

    public function send($data)
    {
        $toEmail   = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/form/', 'mail');
        $fromEmail = $data['contact_mail'];
        $fromName  = $data['contact_name'];
        $subject   = $data['subject'];

        $component = 'None';
        if ($data['component'] == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $component = Ess_M2ePro_Helper_Component_Ebay::TITLE;
        }
        if ($data['component'] == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $component = Ess_M2ePro_Helper_Component_Amazon::TITLE;
        }
        if ($data['component'] == Ess_M2ePro_Helper_Component_Buy::NICK) {
            $component = Ess_M2ePro_Helper_Component_Buy::TITLE;
        }
        if ($data['component'] == Ess_M2ePro_Helper_Component_Play::NICK) {
            $component = Ess_M2ePro_Helper_Component_Play::TITLE;
        }

        $body = $this->createBody($data['subject'],$component,$data['description']);

        $attachments = array();

        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $uploadFileName) {
                if ('' == $uploadFileName) {
                    continue;
                }

                $realName = $uploadFileName;
                $tempPath = $_FILES['files']['tmp_name'][$key];
                $mimeType = $_FILES['files']['type'][$key];

                $attachment = new Zend_Mime_Part(file_get_contents($tempPath));
                $attachment->type        = $mimeType;
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                $attachment->filename    = $realName;

                $attachments[] = $attachment;
            }
        }

        $this->sendMail($toEmail, $fromEmail, $fromName, $subject, $body, $attachments);
    }

    //---------------------------------------------

    private function createBody($subject, $component, $description)
    {
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $body = <<<DATA

{$description}

-------------------------------- GENERAL -----------------------------------------
Date: {$currentDate}
Component: {$component}
Subject: {$subject}


DATA;

        $body .= Mage::helper('M2ePro/Exception')->getGeneralSummaryInfo();

        return $body;
    }

    //---------------------------------------------

    private function sendMail($toEmail, $fromEmail, $fromName, $subject, $body, array $attachments = array())
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->addTo($toEmail)
             ->setFrom($fromEmail, $fromName)
             ->setSubject($subject)
             ->setBodyText($body, null, Zend_Mime::ENCODING_8BIT);

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send();
    }

    //#############################################
}