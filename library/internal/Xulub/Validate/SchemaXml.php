<?php
/**
 * Valdiation d'un schema xml via une xsd en utilisant Zend_Validate
 */
class Xulub_Validate_SchemaXml extends Zend_Validate_Abstract
{
    const INVALID   = 'xmlInvalid';

    const XSD_NOT_EXIST = 'xsdNotExist';

    protected $_messageTemplates = array(
        self::INVALID => 'Le fichier XML que vous avez transmis ne correspond pas au schéma XSD.'
    );

    /**
     * Définit le chemin vers le schéma Xsd
     *
     * @var string
     */
    private $_schemaXsd = '';

    /**
     *
     * @param  string chemin vers le schemaXsd
     * @return void
     */
    public function __construct($schemaXsd)
    {
        $this->_schemaXsd = $schemaXsd;
    }

    public function getSchemaXsd()
    {
        return $this->_schemaXsd;
    }

    public function setSchemaXsd($schemaXsd)
    {
        if (!is_file($schemaXsd)) {

            throw new Zend_Validate_Exception(
                'Le schéma XSD ' . $schemaXsd . 'n\'existe pas.'
            );
        }

        $this->_schemaXsd = $schemaXsd;

        return $this;
    }

    /**
     * Méthode de validation du schéma XSD
     * @todo : Ajouter les erreurs XML, ...
     *
     * @see http://www.blog-nouvelles-technologies.fr/archives/1345/validation-d%E2%80%99un-document-xml-a-l%E2%80%99aide-d%E2%80%99un-schema-xsd-en-php/
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        // Instanciation d'un DOMDocument
        $dom = new DOMDocument("1.0");

        // Charge du XML depuis un fichier
        $dom->loadXML($value);

        // Validation du document XML
        $validate = $dom->schemaValidate($this->_schemaXsd);

        // Affichage du résultat
        if ($validate) {
            return true;
        } else {
            $this->_error(self::INVALID);
            return false;
        }
        return true;
    }
}
