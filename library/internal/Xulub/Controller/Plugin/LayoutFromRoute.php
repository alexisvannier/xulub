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
 * @uses       Zend_Controller_Plugin_Abstract
 * @category   Xulub
 * @package    Xulub_Controller
 * @subpackage Xulub_Controller_Plugin
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 *
 * @todo : Etudier le remplacement de ce plugin pour l'aide d'action
 * ContextSwitch
 */
class Xulub_Controller_Plugin_LayoutFromRoute extends Zend_Controller_Plugin_Abstract
{
    /**
     * Partie de la route devant Ãªtre prise du sous domaine...
     *
     * @var String
     */
    private $_pieceOfRoute;

    /**
     * constructeur
     *
     * @param array $options tableau contenant le nom de la clé qui va être
     * utilisé pour extraire le layout depuis l'objet request
     *
     * on peut également définir le chargement depuis le fichier application.ini
     * avec 2 lignes du types :
     *  resources.frontController.plugins.route = Xulub_Controller_Plugin_LayoutFromRoute
     *  resources.frontController.params.route.pieceOfRoute = profil
     *
     * La configuration dans le fichier ini à la priorité sur la config
     *  manuelle...
     */
    public function __construct(array $options = array(
        'pieceOfRoute' => 'module')
    )
    {
        $params = Zend_Controller_Front::getInstance()->getParam('layout');

        if (is_array($params) && array_key_exists('pieceOfRoute', $params)) {
            extract($params);
        } else {
           extract($options);
        }
        $this->_pieceOfRoute = $pieceOfRoute;
    }

    /**
     * Récupère le template qui va servir de layout
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setViewSuffix('tpl');
        $layoutName = $request->getParam($this->_pieceOfRoute);

        if (!is_null($layoutName)) {
            $layout->setLayout($layoutName);
        }
    }
}