<?php
/**
 * Classe de validation d'un fichier XML pour Zend_Navigation
 */
class Xulub_Validate_Navigation extends Zend_Validate_Abstract {

    const INVALID   = 'invalid';
    const XML_INVALID = 'xmlInvalid';
    const NAVIGATION_INVALID = 'navigationInvalid';

    protected $_messageTemplates = array(
        self::INVALID => "Le fichier XML que vous avez transmis est invalide.",
        self::XML_INVALID => "Le fichier XML que vous avez transmis n'est pas un fichier XML valide.",
        self::NAVIGATION_INVALID => "Le fichier XML que vous avez transmis n'est pas un fichier valide pour Zend_Navigation.",
        );

    public function __construct()
    {
    }

    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        try {
            // on vérifie que le fichier XML est valide
            $config = new Zend_Config_Xml($value);
        }
        catch(Zend_Config_Exception $e)
        {
            $this->_error(self::XML_INVALID);
            return false;
        }

        try {
            // on vérifie que le fichier XML correspond au format attendu par Zend_Navigation
            $nav = new Zend_Navigation($config);
        }
        catch(Zend_Navigation_Exception $e)
        {
            $this->_error(self::NAVIGATION_INVALID);
            return false;
        }

        return true;
    }

}
