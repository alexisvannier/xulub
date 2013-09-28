<?php
/**
 * Classe de gestion d'ajout des fichiers CSS dans une page HTML
 *
 * Ce helper gère également la concaténation et la minification des CSS
 *
 * Cet helper ne gère pas tous les paramètres possibles :
 *  defer, ...
 *
 * @todo : ajouter une durée de vie au cache
 * @todo : ajouter la possibilité de spécifier un domaine pour les fichiers
 *
 * sse Zend_View_Helper_HeadLink
 */
class Xulub_View_Helper_XbHeadLink extends Zend_View_Helper_HeadLink
{

     /**
     * @var string registry key
     */
    protected $_regKey = 'Xulub_View_Helper_XbHeadLink';

    /**
     * Boolean indiquant si les fichiers doivent être concaténés en un seul
     * fichier ou non
     *
     * @var bool
     */
    protected $_enableConcatenate = true;

    /**
     * Détermine si on minifie les fichiers JS ou non
     *
     * @var bool
     */
    protected $_enableMinify = true;

    /**
     * Répertoire de base pour les fichiers JS
     * @var
     */
    protected $_baseUrl;

    /**
     * Domaine à utiliser pour charger les JS
     *
     * @var string
     */
    protected $_domain;

    /**
     * Contient le numéro de version de l'application
     *
     * @var string
     */
    protected $_version;

    public function __construct()
    {
        parent::__construct();
    }

    public function xbHeadLink(array $attributes = null, $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
    {
        parent::headLink($attributes, $placement);
        return $this;
    }

    public function setEnableMinify($value)
    {
        $this->_enableMinify = (bool) $value;
        return $this;
    }

    public function enableMinify()
    {
        $this->_enableMinify = true;
        return $this;
    }

    public function disableMinify()
    {
        $this->_enableMinify = false;
        return $this;
    }

    public function isEnableMinify()
    {
        return $this->_enableMinify;
    }

    public function enableConcatenate()
    {
        $this->_enableConcatenate = true;
        return $this;
    }

    public function disableConcatenate()
    {
        $this->_enableConcatenate = false;
        return $this;
    }

    public function setEnableConcatenate($value)
    {
        $this->_enableConcatenate = (bool) $value;
        return $this;
    }

    public function isEnableConcatenate()
    {
        return $this->_enableConcatenate;
    }

    public function setBaseUrl($value)
    {
        $this->_baseUrl = $value;
        return $this;
    }

    /**
     * Renvoie le répertoire de stockage de
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (is_null($this->_baseUrl))
        {
            $this->_baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
        }
        return $this->_baseUrl;
    }

    /**
     * Définit le numéro de version
     *
     * @param string $value
     * @return Xulub_View_Helper_XbHeadLink
     */
    public function setVersion($value)
    {
        $this->_version = (string) $value;
        return $this;
    }

    /**
     * Retour le numéro de version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $this->_prepareCssFiles();
        if ($this->isEnableConcatenate() === true)
        {
            $this->_concatenateCss();
        }
        return parent::toString($indent);
    }

    /**
     * Convertit les fichiers normaux en fichiers minifiés
     * Ajoute la version si nécessaire
     *
     */
    protected function _prepareCssFiles()
    {
        foreach ($this as $key => $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            if ($this->isEnableMinify() === true)
            {
                $minifyExtension = '-min.css';
                $cssMinFile = str_replace('.css', $minifyExtension, $item->href);
                $cssMinFile = str_replace($this->getBaseUrl(), '', $cssMinFile);
                $physicalCssMinFile = $this->_getDocumentRootDir() . $cssMinFile;
                if (file_exists($physicalCssMinFile))
                {
                    $item->href = $this->getBaseUrl() . $cssMinFile;
                }
            }

            if ( !empty($this->_version) )
            {
                $separator = "?";
                if ( strpos($item->href, $separator) != false )
                {
                    $separator = "&";
                }
                $item->href = $item->href . $separator . 'version=' . $this->getVersion();
            }
            $this->offsetSet($key, $item);
        }
    }

    /**
     * Renvoie le répertoire de base de l'application
     *
     * @return string
     */
    protected function _getDocumentRootDir()
    {
        return APPLICATION_PATH . '/../public';
    }

    /**
     * Concatenation des fichiers CSS dans le répertoire /css/concat
     *
     */
    protected function _concatenateCss()
    {
        $publicDir = $this->_getDocumentRootDir();
        $cachedDir = '/css/concat';

        if (!is_dir($publicDir . $cachedDir) || !is_writable($publicDir . $cachedDir))
        {
            // on lance un trigger plutôt qu'une exception car la méthode __toString n'autorise
            // pas l'appel des exceptions
            trigger_error('Le répertoire ' . $publicDir . $cachedDir . ' n\'existe pas ou vous ne disposez pas des droits d\'écriture.', E_USER_WARNING);
        }
        else
        {
            $concatFiles = array();
            foreach ($this as $key => $item) {
                if (!$this->_isValid($item)) {
                    continue;
                }

                $file = $item->href;
                $file = str_replace($this->getBaseUrl(), '', $file);
                $file = preg_replace('/((\?|\&)version=(.*))/', '', $file);
                // On liste les fichiers qu'il va falloir concaténer
                // on test si fichier PCSS
                $media = $item->media;
                // Si le fichier JS est chargé selon des conditionnels stylesheets
                // on ne traite pas le fichier
                if (!empty($item->conditionalStylesheet))
                {
                    continue;
                }

                if ( strpos($file,'.pcss') !== false ) {
                    // test si chemin contient deja http ou https
                    if ( strpos($file,'http') !== false ) {
                        // pas de traitement particulier
                        $concatFiles[$media][$key] = $file;
                    }
                    else {
                        // on y ajoute l'url de base afin de recupere le fichier pcss
                        $concatFiles[$media][$key] =  $this->_getFullBaseUrl() . $file;
                    }
                }
                else {
                    // test si le chemin contient deja http ou https
                    if ( strpos($file, 'http') !== false ) {
                        // pas de traitement particulier
                        $concatFiles[$media][$key] = $file;
                    }
                    else
                    {
                        if ( file_exists($publicDir . $file) ) {
                            // cas des fichier CSS classique, si fichier existant on le recupere en direct (chemin absolu)
                            $concatFiles[$media][$key] = $publicDir . $file;
                        }
                        // si le fichier n'existe pas, on garde le fichier dans la pile
                        // il générera une 404 sur l'appli
                    }
                }
            }
            foreach($concatFiles as $media => $mediaConcatFiles)
            {
                // on génère l'identifiant
                $id = $media . '-' . md5(serialize($mediaConcatFiles) . $this->getVersion());

                $urlCachedFile = $cachedDir . '/' . $id . '.css';
                $physicalCachedFile = $publicDir . $urlCachedFile;

                // Si le fichier n'existe pas, on le recrée
                if (!file_exists($physicalCachedFile))
                {
                    $content = '';
                    // On crée le fichier
                    foreach($mediaConcatFiles as $item)
                    {
                        $content .= file_get_contents($item);
                    }
                    // on met le contenu
                    // on locke le fichier pour être sûr que personne n'écrit dans le fichier
                    // au même moment
                    file_put_contents($physicalCachedFile, $content, LOCK_EX);
                }

                // On a stocké les clés des éléments à supprimer comme index du tableau
                // pour les récupérer, on fait un array_keys
                $keysToDelete = array_keys($mediaConcatFiles);
                foreach($keysToDelete as $key)
                {
                    $this->offsetUnset($key);
                }
                $this->prependStylesheet($this->getBaseUrl() . $urlCachedFile, $media);
            }
        }
    }

    /**
     * Renvoie l'URL complète de l'application (http://domaine/...)
     *
     * @return string
     */
    private function _getFullBaseUrl()
    {
        /**
         * @todo : verrouiller l'url si on tente un appel d'une url du type
         * http://monsite/index.php. la css cherché devient une url de la forme
         * http://monsite/index.php/css/media/print/ma.css ce qui déclenche une
         * erreur du file_get_content à suivre (ligne 299)
         */
        return $this->view->serverUrl();
    }
}