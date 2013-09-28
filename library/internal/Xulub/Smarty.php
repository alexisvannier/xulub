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
 * @package    Xulub_Smarty
 * @uses Smarty
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */

require_once FRAMEWORK_PATH . DIRECTORY_SEPARATOR .
             'library' . DIRECTORY_SEPARATOR .
             'vendor' . DIRECTORY_SEPARATOR .
             'Smarty' . DIRECTORY_SEPARATOR .
             'libs'   . DIRECTORY_SEPARATOR . 'Smarty.class.php';

class Xulub_Smarty extends Smarty
{
    /**
     * Obiekt widoku
     *
     * @var Zend_View_Abstract
     */
    protected $_zendView = null;

    /**
     * Ustawienie widoku
     *
     * @param Zend_View_Abstract $view obiekt widoku
     */
    public function setZendView(Zend_View_Abstract $view)
    {
        $this->_zendView = $view;
    }

    /**
     * Pobranie widoku
     *
     * @param Zend_View_Abstract obiekt widoku
     */
    public function getZendView()
    {
        return $this->_zendView;
    }
}