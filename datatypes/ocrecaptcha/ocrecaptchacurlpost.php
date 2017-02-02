<?php

use ReCaptcha\RequestMethod\Curl;
use ReCaptcha\RequestMethod\CurlPost;
use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;

/**
 * Sends cURL request to the reCAPTCHA service.
 * Note: this requires the cURL extension to be enabled in PHP
 * @see http://php.net/manual/en/book.curl.php
 */
class OcReCaptchaCurlPost implements RequestMethod
{
    /**
     * Curl connection to the reCAPTCHA service
     * @var Curl
     */
    private $curl;

    public function __construct(Curl $curl = null)
    {
        if (!is_null($curl)) {
            $this->curl = $curl;
        } else {
            $this->curl = new Curl();
        }
    }

    /**
     * Submit the cURL request with the specified parameters.
     *
     * @param RequestParameters $params Request parameters
     * @return string Body of the reCAPTCHA response
     */
    public function submit(RequestParameters $params)
    {
        $handle = $this->curl->init(CurlPost::SITE_VERIFY_URL);

        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params->toQueryString(),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLINFO_HEADER_OUT => false,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true
        );

        $ini = eZINI::instance();
        $proxy = $ini->hasVariable( 'ProxySettings', 'ProxyServer' ) ? $ini->variable( 'ProxySettings', 'ProxyServer' ) : false;
        if ( $proxy )
        {
            $options[CURLOPT_PROXY] = $proxy;
            $userName = $ini->hasVariable( 'ProxySettings', 'User' ) ? $ini->variable( 'ProxySettings', 'User' ) : false;
            $password = $ini->hasVariable( 'ProxySettings', 'Password' ) ? $ini->variable( 'ProxySettings', 'Password' ) : false;
            if ( $userName )
            {
                $options[CURLOPT_PROXYUSERPWD] = "$userName:$password";
            }
        }

        $this->curl->setoptArray($handle, $options);

        $response = $this->curl->exec($handle);
        $this->curl->close($handle);

        return $response;
    }
}
