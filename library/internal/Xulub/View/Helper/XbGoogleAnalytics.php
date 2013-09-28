<?php
/**
 * Helper de vue qui permet d'afficher le code GoogleAnalytics.
 *
 * Usage :
 * $this->view->XbGoogleAnalytics('id tracker');
 *
 * $this->view->XbGoogleAnalytics()->addTrackerOption(
 *  'trackPageview',
 *  'ma valeur'
 * );
 *
 * Dans le template :
 * // smarty
 * {$xbGoogleAnalytics}
 *
 * // non-smary
 * $this->xbGoogleAnalytics();
 *
 * @category   Xulub
 * @package    Xulub_View
 * @subpackage Helper
 */
class Xulub_View_Helper_XbGoogleAnalytics extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * @var string registry key
     */
    protected $_regKey = 'Xulub_View_Helper_XbGoogleAnalytics';

    /**
     *
     * @var string contient l'identifiant de GoogleAnalytics
     */
    protected $_trackerId = null;

    /**
     *
     * @var array tableau des options disponibles
     */
    protected $_trackerOptions = array();

    /**
     * Available Trackers options
     */
    protected $_availableOptions = array
        (
        // Standard Options
        'trackPageview',
        'trackPageLoadTime',
        'setVar',
        // ECommerce Options
        'addItem',
        'addTrans',
        'trackTrans',
        // Tracking Options
        'setClientInfo',
        'setAllowHash',
        'setDetectFlash',
        'setDetectTitle',
        'setSessionTimeOut',
        'setCookieTimeOut',
        'setDomainName',
        'setAllowLinker',
        'setAllowAnchor',
        // Campaign Options
        'setCampNameKey',
        'setCampMediumKey',
        'setCampSourceKey',
        'setCampTermKey',
        'setCampContentKey',
        'setCampIdKey',
        'setCampNoKey',
        // Other
        'addOrganic',
        'addIgnoredOrganic',
        'addIgnoredRef',
        'setSampleRate',
    );

    /**
     *
     * @param string $trackerId the google analytics tracker id
     * @param array
     * @return $this for more fluent interface
     */
    public function XbGoogleAnalytics($trackerId = null,
        array $options = array())
    {
        if (!is_null($trackerId)) {
            $this->setTrackerId($trackerId);

            if (!empty($options)) {
                $this->setTrackerOptions($options);
            }
        }

        return $this;
    }

    /**
     * Définit le tracker de GoogleAnalytics
     *
     * @param string $trackerId
     */
    public function setTrackerId($trackerId)
    {
        $this->_trackerId = $trackerId;
    }

    /**
     * Retourne l'identifiant de Tracker utilisé par GoogleAnalytics
     *
     * @return string
     */
    public function getTrackerId()
    {
        return $this->_trackerId;
    }

    /**
     *
     * Ajoute des options à passer au tracker de GoogleAnalytics
     *
     * @param array $options tableau des options à passer à google anyltics
     */
    public function setTrackerOptions(array $options)
    {
        $this->_trackerOptions = $options;
    }

    /**
     * Possibilité d'ajouter une option dynaniquement aux options de
     * GoogleAnalitcs
     *
     * @todo : ajouter un contrôle sur l'option
     * @param string $key clé à utiliser
     * @param string $value valeur de la clé
     */
    public function addTrackerOption($key, $value)
    {
        $this->_trackerOptions[$key] = $value;
    }

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Rendering Google Anaytics Tracker script
     * @return string
     */
    public function toString()
    {
        // Si le trackerId est renseigné et que c'est une chaine
        if (!empty($this->_trackerId) && is_string($this->_trackerId)) {

            $html = array();
            $html[] = '<script type="text/javascript">';
            $html[] = 'var _gaq = _gaq || [];';

            // init tracker
            $html[] = "_gaq.push(['_setAccount', '" . $this->_trackerId . "']);";

            // add options
            foreach ($this->_trackerOptions as $key => $value) {
                // build tracker func call
                $optionName = '_' . $key;

                if (!empty($value)) {
                    $option = is_numeric($value) ? $value : '' . addslashes($value) . '';

                    // add options
                    $html[] = "_gaq.push(['" . $optionName . "', '" . $option . "']);";
                } else {
                    $html[] = "_gaq.push(['" . $optionName . "']);";
                }
            }

            // on test si on ne track pas deja une page (évite les double
            // comptage dans les stats)
            if ((!key_exists('trackPageview', $this->_trackerOptions)
                || $this->_trackerOptions['trackPageview'] == '')
            ) {
                // on track la page courante
                $html[] = "_gaq.push(['trackPageview']);";
            }

            $html[] = "(function() {";
            $html[] = "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
            $html[] = "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'";
            $html[] = "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
            $html[] = "})();";
            $html[] = "</script>";

            return implode("\n", $html);
        }
        return '';
    }
}