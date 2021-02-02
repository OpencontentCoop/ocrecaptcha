<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class OcRecaptchaField extends FieldConnector
{
    private function isEnabled()
    {
        return !eZUser::isCurrentUserRegistered();
    }

    public function getData()
    {
        return !$this->isEnabled() ? 'ok' : null;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        if (!$this->isEnabled()) {
            $schema['required'] = false;
        }

        return $schema;
    }


    public function getOptions()
    {
        if (!$this->isEnabled()){
            $options = array(
                "type" => 'hidden',
            );
        }else {
            $options = array(
                "helper" => $this->attribute->attribute('description'),
                "type" => 'recaptcha',
                "sitekey" => $this->attribute->attribute(OcReCaptchaType::PUBLIC_KEY_FIELD)
            );
        }

        return $options;
    }

    public function setPayload($postData)
    {
        if (!$this->isEnabled()) {
            return 'ok';
        }

        if (!empty($postData)){
            $isValid = OcReCaptchaType::validateCaptcha(
                $postData,
                $this->attribute->attribute(OcReCaptchaType::PRIVATE_KEY_FIELD)
            );
            if (!$isValid){
                throw new Exception("Invalid ReCaptcha");
            }
        }
        return 'ok';
    }
}
