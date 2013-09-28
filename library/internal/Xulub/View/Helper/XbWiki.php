<?php
/**
 * Helper de vue permettant de faire la traduction wiki
 */
class Xulub_View_Helper_XbWiki extends Zend_View_Helper_Abstract
{
    /**
     * @var wiki2xhtml
     */
    private $_wiki;

    public function __construct()
    {
        $this->_init();
    }

    private function _init()
    {
        require_once FRAMEWORK_PATH . '/library/vendor/wiki2xhtml/wiki2xhtml.class.php';

        $this->_wiki = new wiki2xhtml();

        // modification des options du wiki
        # Activation des notes de bas de page
        $this->_wiki->setOpt('active_footnotes', 0);
        # Activation des mots wiki
        $this->_wiki->setOpt('active_wikiwords', 0);
        # Activation des macros {{{ }}}
        $this->_wiki->setOpt('active_macros', 0);

        # Parser l'int�rieur de blocs <pre> ?
        $this->_wiki->setOpt('parse_pre', 0);

        # Fixe les caract�res MS
        $this->_wiki->setOpt('active_fix_word_entities', 1);
        # Corrections syntaxe FR
        $this->_wiki->setOpt('active_fr_syntax', 1);

    # acronym
    #$this->_wiki->setOpt(
    //'acronyms_file',
    //xbRegistry::get('appli-config')->wiki->acronyms_file
    //);
    #$this->acro_table = $this->__getAcronyms();
    }

    /**
     * M�thode permettant de convertir une chaine de caract�res utilisant une
     * syntaxe wiki en xhtml
     *
     * wiki2xhtml n'accepte que des chaines utf-8.
     * Si l'appli n'est pas encod�e en utf-8, on convertit la chaine en utf-8
     * pour le passer � wiki2xhtml puis on la d�code en utf8.
     *
     * @param string $in
     * @param boolean $removeptag
     * @return string
     */
    private function _transform($in, $removeptag = false)
    {
        $flagViewUtf8 = false;
        if (isset($this->view) && $this->view->getEncoding() == 'UTF-8') {
            $flagViewUtf8 = true;
        }

        if ($flagViewUtf8 === false) {
            $in = utf8_encode($in);
        }

        $out = $this->_wiki->transform($in);

        if ($flagViewUtf8 === false) {
            $out = utf8_decode($out);
        }

        if ($removeptag === true) {
            $out = $this->_removePTag($out);
        }

        return $out;
    }

    /**
     * @desc Retire la premiere et la derni�re balise p d'une chaine Html.
     * Utilis� pour les traductions wiki.
     *
     * @param string $chaine
     * @return string
     */
    private function _removePTag($chaine)
    {
        // supprime les espaces �ventuels
        $chaine = trim($chaine);

        if (strlen($chaine) >= 7) {

            if ( substr_compare($chaine, '<p>', 0, 3, true) == 0 ) {
                $chaine = substr_replace($chaine, '', 0, 3);
            }

            $len = strlen($chaine) - 4;

            if ( substr_compare($chaine, '</p>', $len, 4) == 0 ) {
                $chaine = substr_replace($chaine, '', $len, 4);
            }
        }
        return $chaine;
    }

    /*
     * @param string $in
     * @param boolean $removeptag
     * @return string
     */
    public function XbWiki($in, $removeptag = false)
    {
        return $this->_transform($in, $removeptag);
    }

    /**
     * Retourne le parser wiki2xhtml
     *
     * @return wiki2xhtml
     */
    public function getWiki2xhtml()
    {
        return $this->_wiki;
    }
}
