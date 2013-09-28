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
 * @uses       Xulub_Controller_Dispatcher_Standard
 * @category   Xulub
 * @package    Xulub_Controller
 * @subpackage Xulub_Controller_Dispatcher
 *
 * @desc Helper for creating Forms
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */

class Xulub_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Standard
{

    /**
     * Méthode qui permet de déterminer le nom du contrôleur à appeler selon
     * le nom du contrôleur utilisé dans l'URL.
     *
     * Dans notre cas, on souhaite accepter que les mots en CamelCaseController
     * Par défaut, CamelCaseController est converti en CamelcaseController
     * On ne souhaite pas ce comportement par défaut
     *
     * Cette permet donc de transformer :
     *  * toto en TotoController
     *  * Toto en TotoController
     *  * MaToto en MaTotoController
     *  * Ma-Toto en MaTotoController
     *  * ma-toto en MaTotoController
     *
     * @param string $unformatted
     * @return string
     */
    public function formatControllerName($unformatted)
    {
        return $this->_formatName($unformatted) . 'Controller';
    }

    /**
     * Autorise le nom des modules en majuscule ou en minuscule
     * Exemple :
     *  * Catalogue ou catalogue
     *  * MonCatalogue
     *
     * @param string $path
     * @param string $module
     * @return Xulub_Controller_Dispatcher_Standard
     */
    public function  addControllerDirectory($path, $module = null)
    {
        $module = str_replace('-', ' ', $module);
        $module = ucwords($module);
        $module = str_replace(' ', '', $module);
        // en majuscule
        parent::addControllerDirectory($path, $module);
        // en minuscule
        parent::addControllerDirectory($path, strtolower($module));
        //var_dump($this->_controllerDirectory);
        return $this;
    }

    /**
     * Conversion du module du format CamelCase en un format camel-case
     * Cette méthode est utilisées par le viewrenderer pour connaître le
     * répertoire où se trouve les templates
     *
     * @param string $unformatted
     * @return string
     */
    public function  formatModuleName($unformatted)
    {
        if (($this->_defaultModule == $unformatted)
            && !$this->getParam('prefixDefaultModule')
        ) {
            return $unformatted;
        }

        // convertit un CamelCase en camel-case
        $pattern = '/([^A-Z-])([A-Z])/';
        $replace = '$1-$2';
        $retour = preg_replace($pattern, $replace, $unformatted);
        $moduleName = strtolower($retour);
        return $moduleName;
    }

    /**
     * Renvoie le nom de la classe du contrôleur à utiliser
     * Cette classe est préfixée par le nom du Module
     *
     * @param string $moduleName
     * @param string $className
     * @return string
     */
    public function formatClassName($moduleName, $className)
    {
        $prefixModule = str_replace('-', ' ', $moduleName);
        $prefixModule = ucwords($prefixModule);
        $prefixModule = str_replace(' ', '', $prefixModule);
        $retour = $prefixModule . '_' . $className;
        return $retour;
    }

    /**
     * Voir commentaire de la méthode formatControllerName()
     *
     *
     * @param string $unformatted
     * @param bool $isAction
     * @return string
     */
    protected function _formatName($unformatted, $isAction = false)
    {
        // preserve directories
        if (!$isAction) {
            $segments = explode($this->getPathDelimiter(), $unformatted);
        } else {
            $segments = (array) $unformatted;
        }

        foreach ($segments as $key => $segment) {
            $segment = str_replace($this->getWordDelimiter(), ' ', $segment);
            $segment =  $this->_convertCamelCaseToSeparateWords($segment);
            $segment = ucwords($segment);
            $segment = preg_replace('/[^aA-zZ0-9 ]/', '', $segment);
            $segments[$key] = $segment;
        }
        return implode('_', $segments);
    }

    /**
     * Convertit une chaine en CamelCase en camel-case
     *
     * @param string $string
     * @return string
     */
    private function _convertCamelCaseToSeparateWords($string)
    {
        $pattern = '/([^A-Z-])([A-Z])/';
        $replace = '$1-$2';
        $replace = preg_replace($pattern, $replace, $string);
        return $string;
    }
}
