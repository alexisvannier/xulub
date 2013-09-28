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
 * @package    Xulub_View
 * @subpackage Xulub_View_Smarty
 * @see Zend_View_Abstract
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_View_Smarty extends Zend_View_Abstract
{
    /**
     * Objet Smarty
     *
     * @var Xulub_Smarty
     */
    protected $_smarty = null;

    /**
     * Tableau contenant l'ensemble des formulaires de la vue
     * Est 'public' car appel� depuis le heler Form
     *
     * @var array
     */
    public $forms = array();

    /**
     * Smarty config
     * @var array
     */
    private $_config = null;

    /**
     * Constructeur
     *
     * @param array $config tableau de configuration
     */
    public function __construct($config = array())
    {
        $this->_config = $config;
        $this->_loadSmarty();

    }

    /**
     * Renvoie l'objet Smarty
     *
     * @return Xulub_Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    /**
     * Zwraca ?ciezk? do plik�w tpl
     *
     * @return string ?cie?ka
     */
    public function getScriptPath($name)
    {
        return $this->_smarty->template_dir;
    }

    /**
     * Ustawienie parametru
     *
     * @param string $key klucz
     * @param mixed $value warto??
     */
    public function setParam($key, $value)
    {
        $this->_smarty->$key = $value;
    }

    /**
     * Ustawia zmienn? w widoku
     *
     * @param string $key nazwa zmiennej
     * @param mixed $value warto?? zmiennej
     */
    public function __set($key, $value)
    {
        $this->_smarty->assign($key, $value);
    }

    /**
     * Pobiera zmienn? z widoku
     *
     * @param string $key nazwa zmiennej
     * @return mixed warto?? zmiennej
     */
    public function __get($key)
    {
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * Sprawdzenie czy zmienna jest ustawiona w widoku
     *
     * @param string $key nazwa zmiennej
     * @return boolean czy zmienna jest ustawiona
     */
    public function __isset($key)
    {
        return null === $this->_smarty->getTemplateVars($key);
    }

    /**
     * Usuni?cie zmiennej z widoku
     *
     * @param string $key nazwa zmiennej
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }

    /**
     * Przypisywanie zmiennych do widoku
     *
     * @param string|array $var nazwa zmiennej lucz tablica par
     * (klucz => warto??)
     * @param mixed $value warto?? zmiennej
     */
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            $this->_smarty->assign($var);
            return;
        }

        $this->_smarty->assign($var, $value);
    }

    /**
     * Usuni?cie wszystkich przypisanych do widoku zmiennych
     */
    public function clearVars()
    {
        $this->_smarty->clearAllAssign();
        return $this;
    }

    /**
     * Rozszerzenie abstrakcyjnej metody klasy nadrz?dnej
     */
    protected function _run()
    {

    }

    /**
     * Sets the template engine object
     *
     * @return smarty object
     */
    public function setEngine($smarty)
    {
        $this->_smarty = $smarty;
    }

    /**
     * Renvoie toutes les variables envoy�s au ViewRenderer
     *
     * @return array
     */
    public function getVars()
    {
        return $this->_smarty->tpl_vars;
    }

    public function getVar($key)
    {
        return isset($this->_smarty->tpl_vars[$key])
            ? $this->_smarty->tpl_vars[$key] : null;
    }

    /**
     * Dans Zend_Layout, addScriptPath est utilis� � la place de
     * setScriptPath. On est donc oblig� de d�clarer cette m�thode
     *
     * @param string $path
     */
    public function addScriptPath($path)
    {
        $this->_setTemplateDir($path);
    }

    /**
     * Set templates directory
     *
     * @param string $path
     */
    private function _setTemplateDir($path)
    {
        $this->_smarty->template_dir = $path;
    }

    /**
     * M�thode qui s'occupe de faire le rendu des formulaires quickforms
     * Ce code ne peut pas �tre int�gr� dans le postDispatch du helper->Form
     * car le postDispatch est ex�cut� apr�s le rendu
     */
    private function _renderQuickForm()
    {
        if (isset($this->forms)) {

            $forms = $this->forms;

            while (current($forms) !== false) {
                $form = current($forms);
                // La version par d�faut ne fonctionne pas avec Smarty3
                // Il est n�cessaire de le surcharger, c'est le r�le de
                // XulubArraySmarty
                require_once FRAMEWORK_PATH . DIRECTORY_SEPARATOR .
                             'library' . DIRECTORY_SEPARATOR .
                             'vendor' . DIRECTORY_SEPARATOR .
                             'Quickform' . DIRECTORY_SEPARATOR .
                             'XulubArraySmarty.php';
                $renderer = new XulubArraySmarty($this->_smarty, true);
                $renderer->setRequiredTemplate(
                    '{if $error}
                    <span class="required">* {$label}</span>
                    {else}
                    {if $required}
                        <span class="required">*</span>
                    {/if}
                    {$label}
                    {/if}'
                );
                $renderer->setErrorTemplate(
                    '{if $error}
                        <span class="error">{$label}</span>
                     {else}
                        {$label}
                    {/if}
                    '
                );

                // Enregistre ce rendu comme �tant celui du formulaire
                $form->accept($renderer);

                $formName = key($forms);

                // la classe de rendu ne g�re pas les commentaires/messages pour
                // un formulaire QuickForm donc on se d�merde!
                // on passe par un tableau interm�diaire pour y ajouter les
                // commentaires
                $tableauDeRendu = $renderer->toArray();
                $tableauDeRendu['comments'] = $form->getComment();

                // Passe le tableau � Smarty
                $this->_smarty->assign($formName, $tableauDeRendu);
                next($forms);
            }
        }
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        // G�n�re le rendu des formulaire Quickform
        $this->_renderQuickForm();

        // permet de pouvoir acc�der aux helpers de vue
        // dans les templates smarty en faisant
        // $this->headLink()
        // @todo : v�rifier qu'au niveau performance ce n'est pas trop gourmand
        $this->_smarty->assign('this', $this);

        $allVars = $this->_smarty->getTemplateVars();

        // n�cessaire de r�initialiser le start_time
        // (sinon l'identifiant de cache change tout le temps)
        // Quand on modifie $this, on modifie �galement
        // $this->_smarty->assign('this')
        $this->_smarty->start_time = 0;

        // g�n�ration de l'identifiant du cache de Smarty
        // On cr�e une cl� unique � partir
        // * des variables pass�s � Smarty
        // * des variables $_POST et $_GET
        // On ne prend pas les variables de
        // * $_SESSION car elles sont normalements pr�sents $allVars
        // * $_COOKIE sinon le cache va �tre reg�n�r� pour chaque page
        // (pr�sence du PHPSESSID dans $_COOKIE)
        $cacheId = md5(
            serialize($allVars)
            . serialize($_POST)
            . serialize($_GET)
            . serialize($_SESSION)
        );

        // On g�n�re un identifiant compile_id par nom d'h�te
        // Utile pour EspacePro
        $compileId = md5($_SERVER['SERVER_NAME']);

        /**
         * Renvoie le template g�n�r�
         */
        return $this->_smarty->fetch($name, $cacheId, $compileId);
    }

    /**
     * Magic clone method, on clone create diferent smarty object
     */
    public function __clone()
    {
        $this->_loadSmarty();
    }

    /**
     * Initializes the smarty and populates config params
     *
     * @throws Zend_View_Exception
     * @return void
     */
    private function _loadSmarty()
    {
        /**
         * @todo : g�rer le cas ou Xulub_Smarty renvoie
         * une exception de type SmartyException
         */
        $this->_smarty = new Xulub_Smarty();
        $this->_smarty->setZendView($this);

        foreach ($this->_config['params'] as $key => $value) {
            $this->_smarty->$key = $value;
        }

        $this->setScriptPath($this->_config['scriptPath']);

        // ?cie?ki do helper�w
        foreach ($this->_config['helperDirs'] as $path => $prefix) {
            $this->addHelperPath($path, $prefix);
        }
    }
}