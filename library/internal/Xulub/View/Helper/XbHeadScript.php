<?php
/**
 * Classe de gestion d'ajout des fichiers JS dans une page HTML
 *
 * Ce helper g�re �galement la concat�nation et la minification des JS
 *
 * Cet helper ne g�re pas tous les param�tres possibles :
 *  defer, ...
 *
 * @todo : ajouter une dur�e de vie au cache
 * @todo : ajouter la possibilit� de sp�cifier un domaine pour les fichiers
 *
 * sse Zend_View_Helper_HeadScript
 */
class Xulub_View_Helper_XbHeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * @var string registry key
     */
    protected $_regKey = 'Xulub_View_Helper_XbHeadScript';

    /**
     * Boolean indiquant si les fichiers doivent �tre concat�n�s en un seul fichier ou non
     *
     * @var bool
     */
    protected $_enableConcatenate = true;

    /**
     * D�termine si on minifie les fichiers JS ou non
     *
     * @var bool
     */
    protected $_enableMinify = true;

    /**
     * R�pertoire de base pour les fichiers JS
     * @var
     */
    protected $_baseUrl;

    /**
     * Domaine � utiliser pour charger les JS
     *
     * @var string
     */
    protected $_domain;

    /**
     * Contient le num�ro de version de l'application
     *
     * @var string
     */
    protected $_version;

    public function __construct()
    {
        parent::__construct();
    }

    public function xbHeadScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        parent::headScript($mode, $spec, $placement, $attrs, $type);
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
     * Renvoie le r�pertoire de stockage de
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
     * D�finit le num�ro de version
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
     * Retour le num�ro de version
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
        $this->_prepareJsFiles();

        if ($this->isEnableConcatenate() === true)
        {
            $this->_concatenateJs();
        }
        return parent::toString($indent);
    }

    /**
     * Convertit les fichiers normaux en fichiers minifi�s
     * Ajoute le num�ro de version si la version est d�finie dans le fichier de configuration
     *
     */
    protected function _prepareJsFiles()
    {
        foreach ($this as $key => $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            // On utilise les fichiers minifi�s s'ils sont pr�sents
            if($this->isEnableMinify() === true)
            {
                $minifyExtension = '-min.js';
                $jsFile = $item->attributes['src'];
                $jsMinFile = str_replace('.js', $minifyExtension, $jsFile);
                $jsMinFile = str_replace($this->getBaseUrl(), '', $jsMinFile);
                $physicalJsMinFile = $this->_getDocumentRootDir() . $jsMinFile;
                if (file_exists($physicalJsMinFile))
                {
                    $item->attributes['src'] = $this->getBaseUrl() . $jsMinFile;
                }
            }

            // on ajoute la version si n�cessaire
            if (!empty($this->_version))
            {
                $item->attributes['src'] = $item->attributes['src'] . '?version=' . $this->getVersion();
            }

            $this->offsetSet($key, $item);

        }
    }


    /**
     * Renvoie le r�pertoire de base de l'application
     *
     * @return string
     */
    protected function _getDocumentRootDir()
    {
        return APPLICATION_PATH . '/../public';
    }

    /**
     * Concatenation des fichiers JS dans le r�pertoire /js/concat
     *
     */
    protected function _concatenateJs()
    {
        $publicDir = $this->_getDocumentRootDir();
        $cachedDir = '/js/concat';

        if (!is_dir($publicDir . $cachedDir) || !is_writable($publicDir . $cachedDir))
        {
            // on lance un trigger plut�t qu'une exception car la m�thode __toString n'autorise
            // pas l'appel des exceptions
            trigger_error('Le r�pertoire ' . $publicDir . $cachedDir . ' n\'existe pas ou vous ne disposez pas des droits d\'�criture.', E_USER_WARNING);
        }
        else
        {
            // contient les fichiers qui vont �tre concat�n�s
            $concatFiles = array();
            foreach ($this as $key => $item) {
                if (!$this->_isValid($item)) {
                    continue;
                }

                $file = $item->attributes['src'];
                $file = str_replace($this->getBaseUrl(), '', $file);
                // on filtre le fichier s'il contient le num�ro de version
                $file = preg_replace('/((\?|\&)version=(.*))/', '', $file);
                $file = $publicDir . $file;

                // On liste les fichiers qu'il va falloir concat�n�r
                if ( file_exists($file) && empty($item->attributes['conditional']))
                {
                    // on stocke la key de l'�l�ment pour ensuite pouvoir supprimer
                    // le fichier avec cette key dans la pile des fichiers � traiter
                    $concatFiles[$key] = $file;
                }
            }

            // on g�n�re l'identifiant
            $id = md5(serialize($concatFiles) . $this->getVersion());

            $urlCachedFile = $cachedDir . '/' . $id . '.js';
            $physicalCachedFile = $publicDir . $urlCachedFile;

            // Si le fichier n'existe pas, on le recr�e
            if (!file_exists($physicalCachedFile))
            {
                $content = '';
                // On cr�e le fichier
                foreach($concatFiles as $key => $item)
                {
                    $content .= file_get_contents($item);
                }

                // on met le contenu
                // on locke le fichier pour �tre s�r que personne n'�crit dans le fichier
                // au m�me moment
                file_put_contents($physicalCachedFile, $content, LOCK_EX);
            }

            // on supprime les �l�ments de la pile concat�n�s
            $keysToDelete = array_keys($concatFiles);
            foreach($keysToDelete as $key)
            {
                $this->offsetUnset($key);
            }

            $concatFile = $this->getBaseUrl() . $urlCachedFile;
            $this->prependFile($concatFile);
        }
    }
}
