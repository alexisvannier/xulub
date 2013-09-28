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
 *
 * @category Xulub
 * @package Xulub_Application
 * @subpackage Xulub_Application_Resource
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Application_Resource_XbView extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Contient l'ensemble des options de xbview
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * @return Xulub_View_Smarty
     */
    public function init()
    {
        $this->_options = $this->getOptions();

        // on définit les helpers de vue par défaut pour l'application
        $defaultHelperDirs = array(
            'Xulub/View/Helper' => 'Xulub_View_Helper_',
            'Zend/View/Helper' => 'Zend_View_Helper_'
        );

        $configHelperDirs = array();
        if (isset($this->_options['helperDirs'])
            && is_array($this->_options['helperDirs'])
        ) {
            $configHelperDirs = array_flip($this->_options['helperDirs']);
        }

        $helperDirs = array_merge($defaultHelperDirs, $configHelperDirs);

        // Po??czenie Zend View - Smarty
        $view = new Xulub_View_Smarty(
            array(
                'scriptPath' => APPLICATION_PATH,
                'params' => $this->_options['smarty'],
                'helperDirs' => $helperDirs
            )
        );

        // View Renderer
        Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer')
                ->setViewScriptPathSpec(APPLICATION_PATH . '/controllers/:module/template/:controller_:action.:suffix')
                ->setViewSuffix('tpl')
                ->setView($view);

        if (isset($this->_options['doctype'])) {
            $view->doctype($this->_options['doctype']);
        }
        
        if (isset($this->_options['encoding'])) {
            $view->setEncoding($this->_options['encoding']);
        }

        // active la minification des fichiers JS
        if (isset($this->_options['js']['minify'])) {
            $view->xbHeadScript()->setEnableMinify(
                $this->_options['js']['minify']
            );
        }

        // active la concaténation des fichiers JS
        if (isset($this->_options['js']['concatenate'])) {
            $view->xbHeadScript()->setEnableConcatenate(
                $this->_options['js']['concatenate']
            );
        }

        // active la minification des fichiers CSS
        if (isset($this->_options['css']['minify'])) {
            $view->xbHeadLink()->setEnableMinify(
                $this->_options['css']['minify']
            );
        }

        // active la concaténation des fichiers JS
        if (isset($this->_options['css']['concatenate'])) {
            $view->xbHeadLink()->setEnableConcatenate(
                $this->_options['css']['concatenate']
            );
        }

        if ($this->getBootstrap()->hasOption('version')) {
            $view->xbHeadScript()->setVersion(
                $this->getBootstrap()->getOption('version')
            );

            $view->xbHeadLink()->setVersion(
                $this->getBootstrap()->getOption('version')
            );
        }

        // Zend Layout
        $view->layout = Zend_Layout::startMvc(
            array(
                'layoutPath' => APPLICATION_PATH . '/controllers/layout/template',
                'inflectorTarget' => APPLICATION_PATH . '/controllers/layout/template/:script.:suffix',
                //'layout' => 'agents', à voir si utile
                'viewSuffix' => 'tpl'
            )
        )->setView($view);

        return $view;
    }
}