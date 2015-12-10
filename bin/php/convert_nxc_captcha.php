<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Converte NXC captch in ReCAPTCHA\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$newDataTypeString = 'ocrecaptcha';

try
{
    $cond = array( 'data_type_string' => 'nxccaptcha' );
    /** @var eZContentClassAttribute[] $classAttributes */
    $classAttributes = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(), null, $cond );
    foreach( $classAttributes as $classAttribute )
    {
        $classAttribute->setAttribute( 'data_type_string', $newDataTypeString );
        $classAttribute->setAttribute( OcReCaptchaType::PUBLIC_KEY_FIELD, '6LeI2gETAAAAAGToGuCR4fTuvdpNF6jfYu4TOQHf' );
        $classAttribute->setAttribute( OcReCaptchaType::PRIVATE_KEY_FIELD, '6LeI2gETAAAAAJzx7-XR6L_DIA6KE76Ggj7RXKZg' );
        $classAttribute->store();

        /** @var eZContentObjectAttribute[] $objectAttributes */
        $objectAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $classAttribute->attribute( 'id' ), true );
        foreach( $objectAttributes as $objectAttribute )
        {
            $objectAttribute->setAttribute( 'data_type_string', $newDataTypeString );
            $objectAttribute->store();
        }
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
