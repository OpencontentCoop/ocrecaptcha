<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class OcRecaptchaField extends FieldConnector
{
    public function getData()
    {
        return null;
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "type" => 'recaptcha',
            "sitekey" => $this->attribute->attribute(OcReCaptchaType::PUBLIC_KEY_FIELD)
        );
    }

    public function setPayload($postData)
    {
        if (!empty($postData)){
            $isValid = OcReCaptchaType::validateCaptcha(
                $postData,
                $this->attribute->attribute(OcReCaptchaType::PRIVATE_KEY_FIELD)
            );
            if (!$isValid){
                throw new Exception("Invalid ReCaptcha");
            }
        }
        return null;
    }
}
