<?php

class OcReCaptchaType extends eZDataType
{
    const DATA_TYPE_STRING = 'ocrecaptcha';

    const PUBLIC_KEY_FIELD = 'data_text1';
    const PUBLIC_KEY_VARIABLE = '_ocrecaptcha_public_key_';
    const PRIVATE_KEY_FIELD = "data_text2";
    const PRIVATE_KEY_VARIABLE = "_ocrecaptcha_private_key_";


    public function __construct()
    {
        $this->eZDataType(
            self::DATA_TYPE_STRING,
            ezpI18n::tr( 'extension/ocrecaptcha', 'reCAPTCHA' ),
            array( 'serialize_supported' => true )
        );
    }

    /**
     * @param eZContentClassAttribute $oldClassAttribute
     * @param eZContentClassAttribute $newClassAttribute
     */
    function cloneClassAttribute( $oldClassAttribute, $newClassAttribute )
    {
        $newClassAttribute->setAttribute( self::PUBLIC_KEY_FIELD, $oldClassAttribute->attribute( self::PUBLIC_KEY_FIELD ) );
        $newClassAttribute->setAttribute( self::PRIVATE_KEY_FIELD, $oldClassAttribute->attribute( self::PRIVATE_KEY_FIELD ) );
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $classAttribute
     *
     * @return bool
     */
    public function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $publicKey = $base . self::PUBLIC_KEY_VARIABLE . $classAttribute->attribute( 'id' );
        $privateKey = $base . self::PRIVATE_KEY_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $publicKey ) )
            $classAttribute->setAttribute( self::PUBLIC_KEY_FIELD, $http->postVariable( $publicKey ) );
        if ( $http->hasPostVariable( $privateKey ) )
            $classAttribute->setAttribute( self::PRIVATE_KEY_FIELD, $http->postVariable( $privateKey ) );
        return true;
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     * @param DOMNode $attributeNode
     * @param DOMDocument $attributeParametersNode
     */
    public function serializeContentClassAttribute(
        $classAttribute,
        $attributeNode,
        $attributeParametersNode
    )
    {
        $dom = $attributeParametersNode->ownerDocument;

        $publicKey = $classAttribute->attribute( self::PUBLIC_KEY_FIELD );
        $publicKeyNode = $dom->createElement( 'public' );
        $publicKeyNode->appendChild( $dom->createTextNode( $publicKey ) );
        $attributeParametersNode->appendChild( $publicKeyNode );

        $privateKey = $classAttribute->attribute( self::PRIVATE_KEY_FIELD );
        $privateKeyNode = $dom->createElement( 'private' );
        $privateKeyNode->appendChild( $dom->createTextNode( $privateKey ) );
        $attributeParametersNode->appendChild( $privateKeyNode );
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     * @param DOMNode $attributeNode
     * @param DOMDocument $attributeParametersNode
     */
    public function unserializeContentClassAttribute(
        $classAttribute,
        $attributeNode,
        $attributeParametersNode
    )
    {
        $publicKey = $attributeParametersNode->getElementsByTagName( 'public' )->item( 0 )->textContent;
        $privateKey = $attributeParametersNode->getElementsByTagName( 'private' )->item( 0 )->textContent;
        $classAttribute->setAttribute( self::PUBLIC_KEY_FIELD, $publicKey );
        $classAttribute->setAttribute( self::PRIVATE_KEY_FIELD, $privateKey );
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $attribute
     *
     * @return int
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $attribute )
    {
        if ($http->hasPostVariable('g-recaptcha-response')){
            if( self::validateCaptcha(
                $http->postVariable( 'g-recaptcha-response' ),
                $attribute->contentClassAttribute()->attribute( self::PRIVATE_KEY_FIELD ) )
            ){
                return eZInputValidator::STATE_ACCEPTED;
            }
        }

        $attribute->setValidationError(ezpI18n::tr('extension/ocrecaptcha', 'Invalid input'));
        return eZInputValidator::STATE_INVALID;
    }

    public function hasObjectAttributeContent( $attribute )
    {
        return false;
    }

    public function isInformationCollector()
    {
        return true;
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $attribute
     *
     * @return int
     */
    public function validateCollectionAttributeHTTPInput( $http, $base, $attribute )
    {
        if ($http->hasPostVariable('g-recaptcha-response')){
            if( self::validateCaptcha(
                $http->postVariable( 'g-recaptcha-response' ),
                $attribute->contentClassAttribute()->attribute( self::PRIVATE_KEY_FIELD ) )
            ){
                return eZInputValidator::STATE_ACCEPTED;
            }
        }

        $attribute->setValidationError(ezpI18n::tr('extension/ocrecaptcha', 'Invalid input'));
        return eZInputValidator::STATE_INVALID;
    }

    public function supportsBatchInitializeObjectAttribute()
    {
        return false;
    }

    public function title( $attribute, $name = null )
    {
        return null;
    }

    public function isIndexable()
    {
        return false;
    }

    public function sortKeyType()
    {
        return false;
    }

    public function metaData( $attribute )
    {
        return null;
    }

    public function diff( $old, $new, $options = false )
    {
        return null;
    }

    /**
     * @param string $gRecaptchaResponse
     * @param string $privateKey
     *
     * @return bool
     */
    public static function validateCaptcha( $gRecaptchaResponse, $privateKey )
    {
        $recaptcha = new \ReCaptcha\ReCaptcha( $privateKey, new OcReCaptchaCurlPost() );
        $result = $recaptcha->verify(
            $gRecaptchaResponse,
            $_SERVER['REMOTE_ADDR']
        );

        if ( $result->isSuccess() )
            return true;

        eZDebug::writeError( var_export( $result->getErrorCodes(), 1 ), __METHOD__ );
        return false;
    }

}

eZDataType::register( OcReCaptchaType::DATA_TYPE_STRING, 'OcReCaptchaType' );
