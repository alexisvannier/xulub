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
 * @desc Plugin de test ZFDebug pour valider qu'il est possible de surcharger
 * les plugins de ZFDebug
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Controller_Plugin_Debug_Plugin_Test extends Zend_Controller_Plugin_Abstract implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'test';

    /**
     * Creating time plugin
     * @return void
     */
    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return 'Tab de test';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $panel = '<h4>Onglet de tab</h4>';
        $panel .= 'contenu de l\'onglet de test';
        return $panel;
    }
}