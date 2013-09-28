<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id: select.php,v 1.26 2004/02/28 22:10:16 avb Exp $

require_once('HTML/QuickForm/select.php');

/**
 * Class to dynamically create an HTML SELECT
 *
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_selectArray extends HTML_QuickForm_select {
    // {{{ toHtml()

    /**
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            if ($this->getComment() != '') {
                $strHtml .= $tabs . '<!-- ' . $this->getComment() . " //-->\n";
            }

            // XULUB - Debut Modif pour obtenir toujorus un tableau en retour
            $myName = $this->getName();
            $this->setName($myName . '[]');
            // XULUB - Fin Modif pour obtenir toujorus un tableau en retour

            if (!$this->getMultiple()) {
                $attrString = $this->_getAttrString($this->_attributes);
            } else {
                $myName = $this->getName();
                $this->setName($myName . '[]');
                $attrString = $this->_getAttrString($this->_attributes);
                $this->setName($myName);
            }
            $strHtml .= $tabs . '<select' . $attrString . ">\n";

            foreach ($this->_options as $option) {
                if (is_array($this->_values) && in_array((string)$option['attr']['value'], $this->_values)) {
                    $this->_updateAttrArray($option['attr'], array('selected' => 'selected'));
                }
                $strHtml .= $tabs . "\t<option" . $this->_getAttrString($option['attr']) . '>' .
                            $option['text'] . "</option>\n";
            }

            return $strHtml . $tabs . '</select>';
        }
    } //end func toHtml

    // {{{ exportValue()

   /**
    * We check the options and return only the values that _could_ have been
    * selected. We also return a scalar value if select is not "multiple"
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        } elseif(!is_array($value)) {
            $value = array($value);
        }
        if (is_array($value) && !empty($this->_options)) {
            $cleanValue = null;
            foreach ($value as $v) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                    if ($v == $this->_options[$i]['attr']['value']) {
                        $cleanValue[] = $v;
                        break;
                    }
                }
            }
        } else {
            $cleanValue = $value;
        }

        return $this->_prepareValue($cleanValue, $assoc);
    }

    function getLibelle()
    {
		$value = $this->getValue();
		if(!is_array($value)) {
            $value = array($value);
        }
        if (is_array($value) && !empty($this->_options)) {
            $cleanLabel = null;
            foreach ($value as $v) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++)
                {
                    if ($v == $this->_options[$i]['attr']['value'])
                    {
                        $cleanLabel[] = $this->_options[$i]['text'];
                    }
                }
            }
        } else {
            $cleanValue = array();
        }
		return $cleanLabel;
    }


    // }}}
} //end class HTML_QuickForm_select
?>
