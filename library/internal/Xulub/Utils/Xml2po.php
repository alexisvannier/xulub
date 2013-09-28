<?php
/**
 * This file is part of XULUB.
 *
 * XULUB is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * XULUB is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with XULUB; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Xulub
 * @package    Xulub_Utils
 * @subpackage Xulub_Utils_Xml2po
 *
 * @desc Classe qui convertit les fichiers de langues (languages.xml)
 * en des fichiers po compilables par les utilitaires de gettext
 *
 * @todo Gérer le charset des fichiers gettext
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Utils_Xml2po
{
    /**
     * basename du fichier .po ou .mo
     */
    const GETTEXT_DOMAIN = 'messages';

    const LANGUAGE_NAME_FILE = 'languages.xml';

    /**
     * Contient le répertoire de travail des locales
     *
     * @var string
     */
    protected $_localeDir;

    /**
     * répertoire de recherche des fichiers de langues languages
     * @var <type>
     */
    protected $_searchDir;

    /**
     * Chemin complet vers le fichier de language contenant l'ensemble des
     *  traductions
     *
     * @var string
     */
    protected $_globalLanguagesFile;

    /**
     * Contient l'ensemble des langues disponibles pour l'application
     *
     * @var array
     */
    protected $_languages = array();

    /**
     * Tableau contenant l'ensemble des clés de langues
     *
     * @var array
     */
    protected $_keys = array();

    /**
     * Tableau contenant la liste des mesages en doublon
     *
     * @var array
     */
    protected $_duplicate = array();

    /**
     * Chemin vers le binaire de compilation gettext
     *
     * @var string
     */
    protected $_gettextCompiler;


    public function  __construct($searchDir = '', $localeDir = '',
        $gettextCompiler = '')
    {
        if (empty($searchDir) || !is_dir($searchDir)) {
            throw new Zend_Exception(
                'Le répertoire ' . $searchDir . ' n\'existe pas.'
            );
        }

        if (empty($localeDir) || !is_dir($localeDir)) {
            throw new Zend_Exception(
                'Le répertoire ' . $localeDir . ' n\'existe pas.'
            );
        }

        $this->setSearchDir($searchDir);
        $this->setLocaleDir($localeDir);
        $this->setGlobalLanguagesFile($localeDir . '/global-languages.xml');
        $this->setGettextCompiler($gettextCompiler);
    }

    public function  __destruct()
    {
        if (file_exists($this->getGlobalLanguagesFile())) {
            unlink($this->getGlobalLanguagesFile());
        }
    }

    /**
     *
     * @param string $value
     * @return Xulub_Utils_Xml2po
     */
    public function setSearchDir($value)
    {
        $this->_searchDir = (string) $value;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSearchDir()
    {
        return $this->_searchDir;
    }

    /**
     *
     * @param string $value
     * @return Xulub_Utils_Xml2po
     */
    public function setLocaleDir($value)
    {
        $this->_localeDir = (string) $value;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getLocaleDir()
    {
        return $this->_localeDir;
    }

    /**
     * Définit le chemin complet le fichier languages.xml global
     *
     * @param string $value
     * @return Xulub_Utils_Xml2po
     */
    public function setGlobalLanguagesFile($value)
    {
        $this->_globalLanguagesFile = $value;
        return $this;
    }

    /**
     * Définit le chemin complet le fichier languages.xml global
     *
     * @return string
     */
    public function getGlobalLanguagesFile()
    {
        return $this->_globalLanguagesFile;
    }

    /**
     * Définit l'ensemble des langues disponibles dans le fichier de traduction
     *
     * @param string $value
     * @return Xulub_Utils_Xml2po
     */
    public function setLanguages($value)
    {
        $this->_languages = (array) $value;
        return $this;
    }

    /**
     * renvoie un tableau contenant l'ensemble des langues disponibles
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->_languages;
    }

    public function addLanguage($value)
    {
        if (!in_array($this->_languages)) {
            $this->_languages[] = value;
        }
        return $this;
    }

    protected function _getGettextLanguageDir($language)
    {
        return $this->getLocaleDir() . '/'. $language . '/LC_MESSAGES/';
    }

    public function setGettextCompiler($value)
    {
        $this->_gettextCompiler = $value;
        return $this;
    }

    public function getGettextCompiler()
    {
        return $this->_gettextCompiler;
    }

    /**
     * Fonction qui lance la conversion des fichiers languages.xml en po
     *
     */
    public function convert()
    {
        // On compile tous les fichiers en 1 seul
        $this->_concatLanguageFiles($this->getSearchDir());

        $languages = $this->_parseXmlFilesForLanguages();
        $this->setLanguages($languages);

        // on crée les répertoires pour toutes les langues définies
        $this->_createDirectories();

        foreach ($languages as $language) {
            // Pour chaque langue définie, on lance la récupération de toutes
            // les traductions présentes dans le répertoire controllers
            $this->_parseXmlFile($language);

            // on crée le fichier PO
            $content_po_file = $this->_createPoFile($language);

            $po_file = $this->_getGettextLanguageDir($language) . self::GETTEXT_DOMAIN . '.po';

            // on sauvegarde le contenu dans un fichier po
            $this->_savePoFile($po_file, $content_po_file);

            // on convertit le fichier po en mo
            $this->compilPo2Mo($po_file, $language);
        }
    }


    /**
     * Parcours l'ensemble des répértoires pour trouver un fichier de langue
     * Appel la fonction de récupération des traductions pour créer fichier po
     *
     * @param string $dir : répertoire à partir duquel on cherche les fichiers
     *  po
     * @param string $lang : langue
     */
    protected function _concatLanguageFiles($dir)
    {
        // on recupere tous les fichiers de langues présents dans le dossier
        // des controllers
        $files = Xulub_Utils_File::rglob(self::LANGUAGE_NAME_FILE, 0, $dir);

        foreach ($files as $filename) {

           if (file_exists($filename)) {

                $xml = file_get_contents($filename);
                file_put_contents(
                    $this->getGlobalLanguagesFile(),
                    $xml,
                    FILE_APPEND
                );
            }
        }
        // on supprime les entêtes des fichiers XML
        $content = file_get_contents($this->getGlobalLanguagesFile());

        $data = preg_replace(
            '/(<\/translations>\s*<\?xml version="1\.0" encoding="ISO-8859-15" \?>\s*<translations>)/s',
            '',
            $content
        );

        file_put_contents($this->getGlobalLanguagesFile(), $data);
    }

    /**
     * Renvoie un tableau contenant l'ensemble des langues définies dans les
     * fichiers de langue
     *
     * @return array
     */
    protected function _parseXmlFilesForLanguages()
    {
        $xml = simplexml_load_file($this->getGlobalLanguagesFile());

        $values = $xml->xpath(
            '/translations/sentence/translation[not(@language=preceding::sentence/translation/@language)]/@language'
        );

        if ($values === false) {
            throw new Zend_Exception(
                'Format d`un des fichiers de langues non valides'
            );
        }

        foreach ($values as $language) {
            $languages[] = (string)$language;
        }

        return $languages;
    }

    protected function _createDirectories()
    {
        $languages = $this->getLanguages();
        foreach ($languages as $language) {

            $gettextDir = $this->getLocaleDir() . '/'. $language . '/LC_MESSAGES/';

            if (!is_dir($gettextDir)) {

                if (!is_writeable($gettextDir)) {

                    mkdir($gettextDir, 0777, true);
                } else {
                    throw new Zend_Exception(
                        'Impossible de créer le répertoire ' . $gettextDir
                    );
                }
            }
        }
    }


    /**
     * Parcours le fichier de langue languages.xml
     * et récupère l'ensemble des traductions existantes
     *
     * Les traductions récupèrés sont stockés dans la variable $keys
     * Les doublons sont stockés dans $duplicate
     *
     * @param string $file fichier de langues (languages.xml)
     * @param string $lang : langue à récupérer
     */
    protected function _parseXmlFile($lang)
    {
        $xml = simplexml_load_file($this->getGlobalLanguagesFile());
        foreach ($xml->sentence as $sentence) {

            foreach ($sentence->translation as $trans) {

                if ($trans['language'] == $lang) {

                    if (!isset($this->_keys[$lang])) {
                        $this->_keys[$lang] = array();
                    }

                    // initialisation des attributes
                    $attribute = '';

                    //Parcours les attributs (recherche de la clé)
                    foreach ($sentence->attributes() as $attributeKey => $attributeObject) {

                        if ($attributeKey == 'key') {
                            $attribute = substr($attributeObject, 0);
                        }

                        if ($attributeKey == 'wiki') {
                            $use_wiki = true;
                        }
                    }

                    // recherche de la chaine traduite
                    if (isset($trans['value'])) {
                        $chaineTraduite = (string) $trans['value'];
                    } else {
                        $chaineTraduite = $trans;
                    }

                    //Vérification que la clé n'est pas dupliquée
                    if (!array_key_exists($attribute, $this->_keys[$lang])) {
                        $this->_keys[$lang][$attribute] = self::_fs(
                            $chaineTraduite
                        );
                    } else {
                        $this->_duplicate[$lang][$attribute][] = self::_fs(
                            $chaineTraduite
                        );
                    }
                }
            }
        }
    }

    /**
     * Fonctions qui va créer le contenu du fichier PO
     * à partir des informations récupérés dans le fichier languages.xml
     *
     * Renvoie le contenu du fichier générés.
     *
     * @return $content
     */
    private function _createPoFile($lang)
    {
        $supportTechEmail = 'support@cnfpt.local';
        $supportTechName = 'dev local';
        $defaultCharset = 'iso-8859-15';

        if (class_exists('Zend_Registry') &&
            Zend_Registry::isregistered('appli-config') &&
            isset(Zend_Registry::get('appli-config')->mail->support_tech->email)
        ) {
            $supportTechEmail = Zend_Registry::get('appli-config')->mail->support_tech->email;
        }

        if (class_exists('Zend_Registry') &&
            Zend_Registry::isregistered('appli-config') &&
            isset(Zend_Registry::get('appli-config')->mail->support_tech->name)
        ) {
            $supportTechName = Zend_Registry::get('appli-config')->mail->support_tech->name;
        }

        if (class_exists('Zend_Registry') &&
            Zend_Registry::isregistered('appli-config') &&
            isset(Zend_Registry::get('appli-config')->i18n->default_charset)
        ) {
            $defaultCharset = Zend_Registry::get('appli-config')->i18n->default_charset;
        }

        $content = '';
        $traductions = $this->_keys[$lang];

        $entete = 'msgid ""' . PHP_EOL;
        $entete .= 'msgstr "Project-Id-Version: 1\n"' . PHP_EOL;
        $entete .= '"Report-Msgid-Bugs-To: ' . $supportTechEmail . '\n"' . PHP_EOL;
        $entete .= '"POT-Creation-Date: ' . date('Y-m-d H:iO', time()) . '\n"' . PHP_EOL;
        $entete .= '"PO-Revision-Date: ' . date('Y-m-d H:iO', time()) . '\n"' . PHP_EOL;
        $entete .= '"Last-Translator: ' . $supportTechName . ' <' . $supportTechEmail . '>\n"' . PHP_EOL;
        $entete .= '"Language-Team: ' . $supportTechName . ' <' . $supportTechEmail . '>\n"' . PHP_EOL;
        $entete .= '"MIME-Version: 1.0\n"' . PHP_EOL;
        $entete .= '"Content-Type: text/plain; charset=' . $defaultCharset . '\n"' . PHP_EOL;
        $entete .= '"Content-Transfer-Encoding: ' . $defaultCharset . '\n"' . PHP_EOL;

        $content .= $entete;
        $languageCourant = '';

        foreach ($traductions as $key => $traduction)
        {
            $content .= 'msgid "' . $key . '"' . PHP_EOL;
            $content .= 'msgstr "' . utf8_decode($traduction) . '"' . "\n\n";
        }
        return $content;
    }

    /**
     * Fonction de sauvegarde du fichier po
     *
     * @param string $file : chemin vers le fichier ppo
     * @param string $content : contenu du fichier po
     */
    private static function _savePoFile($file, $content)
    {
        file_put_contents($file, $content);
    }

    /**
     * Fonction qui convertit la chaine récupérée
     * en une chaine compréhensible par gettext :
     * * échappement des guillemets
     * * suppression des tabulations inutiles
     * *
     *
     * @param string $str
     * @return string
     */
    private static function _fs($str)
    {
        // les fonctions preg_replace ne savent pas gérer correctement
        // l'UTF8 --> on encode puis decode
        $str = utf8_decode($str);

        //$str = str_replace('/'.chr(195).'/', '@AAA@', $str);
        $str = preg_replace('/^([\s]+)/', '', $str);
        $str = preg_replace('/([ ]+)/', ' ', $str);

        // suppression des tabulations
        $str = preg_replace('/([\t]+)/', ' ', $str);

        $str = stripslashes($str);

        // on supprime les derniers retours chariots de la chaine traduite
        $str = preg_replace('/([[:space:]]+)$/', '', $str);

        // les autres retours chariots sont transformés en leur équivalent
        // textuel '\n' (pour gettext)
        $str = str_replace(PHP_EOL, '\n', $str);

        // on échappe les guillemets
        $str = str_replace('"', '\"', $str);

        // on réencode
        $str = utf8_encode($str);
        return $str;
    }

    /**
     * Fonction qui compile un fichier po
     *
     * @param string $savepath : répertoire de compilation du fichier mo
     * @return boolean
     */
    public function compilPo2Mo($poFile)
    {
        // On contruit le compiler MO
        if (is_file($poFile)) {
            $moFile = dirname($poFile) . '/' . self::GETTEXT_DOMAIN . '.mo';

            if (is_file($moFile)) {
                unlink($moFile);
            }

            /**
             * @todo : à revoir
             */
            $cmd = $this->getGettextCompiler() . ' -o ' . $moFile . ' ' . $poFile;
            $cmd = escapeshellcmd($cmd);
            exec($cmd);

            // @todo : gérer le retour d'erreur
            return true;
        } else {
            return false;
        }
    }
}