<?php

class eZSurveyRecaptcha extends eZSurveyQuestion
{

    function __construct($row = false)
    {
        $row['type'] = 'Recaptcha';
        $row['text2'] = $this->getPublicKey();
        $this->eZSurveyQuestion($row);
    }

    function processViewActions(&$validation, $params)
    {
        $gRecaptchaResponse = eZHTTPTool::instance()->postVariable('g-recaptcha-response');
        $isValid = OcReCaptchaType::validateCaptcha($gRecaptchaResponse, $this->getPrivateKey());
        if (!$isValid) {
            $validation['error'] = true;
            $validation['errors'][] = array('message' => ezpI18n::tr('survey', 'Please fill recaptcha', null,
                array(
                    '%number' => $this->questionNumber())),
                    'question_number' => $this->questionNumber(),
                    'code' => 'fill_recaptcha',
                    'question' => $this
            );
        }
    }

    function questionNumberIterate(&$iterator)
    {
        //We don't want to increment the question number since it's not a question.
    }

    private function getPublicKey()
    {
        list($public, $private) = explode('$$$', $this->getSiteData()->attribute('value'));

        return $public;
    }

    private function getPrivateKey()
    {
        list($public, $private) = explode('$$$', $this->getSiteData()->attribute('value'));

        return $private;
    }

    private function getSiteData()
    {
        $data = eZSiteData::fetchByName('GoogleRecaptcha');
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData(array(
                'name' => 'GoogleRecaptcha',
                'value' => 'no-public$$$no-secret'
            ));
        }

        return $data;
    }
}

eZSurveyQuestion::registerQuestionType(ezpI18n::tr('survey', 'Recaptcha'), 'Recaptcha');