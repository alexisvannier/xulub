<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
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
// $Id: advcheckbox.php,v 1.14 2004/04/19 11:40:01 avb Exp $

require_once('HTML/QuickForm/checkbox.php');
require_once('HTML/QuickForm/element.php');

/**
 * HTML class for an advanced checkbox type field
 *
 * Basically this fixes a problem that HTML has had
 * where checkboxes can only pass a single value (the
 * value of the checkbox when checked).  A value for when
 * the checkbox is not checked cannot be passed, and 
 * furthermore the checkbox variable doesn't even exist if
 * the checkbox was submitted unchecked.
 *
 * It works by creating a hidden field with the passed-in name
 * and creating the checkbox with no name, but with a javascript
 * onclick which sets the value of the hidden field.
 * 
 * @author       Jason Rust <jrust@php.net>
 * @since        2.0
 * @access       public
 */
class HTML_QuickForm_advcheckboxgroup extends HTML_QuickForm_element
{
    // {{{ properties
        
    /**
     * Name of the element
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_name = '';

    /**
     * Label of the field
     * @var       string
     * @since     1.3
     * @access    private
     */
    var $_label = '';
    
    /**
     * Array of grouped elements
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_elements = array();
	
    /**
     * Attributes of the field
     * @var       string
     * @since     1.3
     * @access    private
     */
    var $_attributes = '';    
    // }}}	
    // {{{ constructor
    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field label 
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @param     mixed     $values         (optional)Values to pass if checked or not checked 
     *
     * @since     1.0
     * @access    public
     * @return    void
     */
   function HTML_QuickForm_advcheckboxgroup($elementName=null, $elementLabel=null, $attributes=null, $values=null)
   {
   		$this->setAttributes($attributes);
	   	$this->setName($elementName);
	   	$this->setLabel($elementLabel);
		$this->setValue($values);   		
		$this->loadArray($values);		
	
    } //end constructor
    
    
    // }}}
    // {{{ setName()

    /**
     * Sets the group name
     * 
     * @param     string    $name   Group name
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->_name = $name;
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the group name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->_name;
    } //end func getName

    // }}}        
    // {{{ setLabel()

    /**
     * Sets display text for the element
     * 
     * @param     string    $label  Display text for the element
     * @since     1.3
     * @access    public
     * @return    void
     */
    function setLabel($label)
    {
        $this->_label = $label;
    } //end func setLabel

    // }}}
    // {{{ getLabel()

    /**
     * Returns display text for the element
     * 
     * @since     1.3
     * @access    public
     * @return    string
     */
    function getLabel()
    {
        return $this->_label;
    } //end func getLabel

    // }}}    
    // {{{ _prepareValue()

   /**
    * Used by exportValue() to prepare the value for returning
    *
    * @param  mixed   the value found in exportValue()
    * @param  bool    whether to return the value as associative array
    * @access private
    * @return mixed
    */
    function _prepareValue($value, $assoc)
    {
        if (null === $value) {
            return null;
        } elseif (!$assoc) {
            return $value;
        } else {
            $name = $this->getName();
            if (!strpos($name, '[')) {
                return array($name => $value);
            } else {
                $valueAry = array();
                $myIndex  = "['" . str_replace(array(']', '['), array('', "']['"), $name) . "']";
                eval("\$valueAry$myIndex = \$value;");
                return $valueAry;
            }
        }
    }
    
    // }}}    
    // {{{ exportValue()
	
   /**
    * Return true if the checkbox is checked, null if it is not checked (getValue() returns false)
    */
    function exportValue(&$submitValues, $assoc = false)
    {   
        $value = $this->_findValue($submitValues);

        // Si le _findValue n'a rien trouvé, on cherche directement les chekcboxes cochées
        if(null === $value)
        {
	    	foreach($this->_elements as $key => $group)
	    	{	    		
	    		foreach($group['QuickForm_checkboxes'] as $key => $checkbox)
	        		{
	        			if($checkbox->getChecked()) 
	        			{
	        				$liste_values[] = $checkbox->getValue();
	        			} 			        				
	        		}    		
	    	}        	
        }

		return $this->_prepareValue($value, $assoc);       
    }
    
    // }}}  
    // {{{ setValue()      
    /**
     * Sets the values used by the hidden element
     *
     * @param   mixed   $values The values, either a string or an array
     *
     * @access public
     * @return void
     */
    function setValue($values)
    {
    	if(is_array($values) && count($values) > 0)
    	{
    		$this->_values = $values;    		
    	}

   		if(is_array($this->_elements) && count($this->_elements) > 0)
   		{
	    	foreach ($this->_elements as $key => $group)
	    	{
	    		foreach ($group['QuickForm_checkboxes'] as $key_checkbox => $checkbox)
	    		{	    			
	   				$checkbox->setChecked($values);
	    		}
	    	}    	    		
   		}

    }
    
    // }}}    
    // {{{ getValue()

   /**
    * Returns the element's value
    *
    * @access   public
    * @return   mixed
    */
    function getValue()
    {
		$output = array();
		
   		if(is_array($this->_elements) && count($this->_elements) > 0)
   		{
	    	foreach ($this->_elements as $key => $group)
	    	{
	    		foreach ($group['QuickForm_checkboxes'] as $key_checkbox => $checkbox)
	    		{	    			
	    			if($checkbox->getChecked())
	    			{
	   					$output[] = $checkbox->_value;
	    			}
	    		}
	    	}    	    		
	    	return $output;
   		}
		else {
            return null;
        }   		   		   	
    }

    // {{{ loadArray()

    /**
     * Loads the options from an associative array
     * 
     * @param     array    $arr     Associative array of options
     * @param     mixed    $values  (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function loadArray($values)
    {
        if (!is_array($values)) {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadArray is not a valid array');
        }
        
        $attributes = $this->getAttributes();
        
		$options_attributes = null;
	   	if( isset($attributes['options']) && ($attributes['options']!='') )
	   		$options_attributes = $attributes['options'];
		
	   	if(!is_null($values))
	   	{
		   	$group = array();   	
		   	foreach ($values as $key => $checkboxes)
		   	{
	
		   		$checkbox_list = array();
		   		foreach ($checkboxes as $checkbox_key => $checkbox_val)
		   		{
		   			$checkbox_list[] = new HTML_QuickForm_checkboxValue($this->getName()."[]", $checkbox_key, $checkbox_val, $options_attributes, $checkbox_key);
		   		}
		   		$group[] = array( 'opt_name' => $key,
		   						  'class' => isset($attributes['class']) ? $attributes['class'] : '',
		   						  'QuickForm_checkboxes' => $checkbox_list );
		   	}
		   	$this->_elements = $group;
	   	}
	   	
        return true;
    } // end func loadArray

    // }}}    
    
	function setAttributes($value)
	{
		$this->_attributes = $value;
	}
    
	function getAttributes()
	{
		return $this->_attributes;
	}

	
   /**
    * Returns the element's value
    *
    * @access   public
    * @return   mixed
    */
    function getLibelle()
    {
		$output = array();
		
   		if(is_array($this->_elements) && count($this->_elements) > 0)
   		{
			$i = 0;   			
	    	foreach ($this->_elements as $key => $group)
	    	{
	    		foreach ($group['QuickForm_checkboxes'] as $key_checkbox => $checkbox)
	    		{	    			
	    			if($checkbox->getChecked())
	    			{
	    				$output[$i]['group'] = $group['opt_name'];
	   					$output[$i]['elements'][] = $checkbox->_text;
	    			}
	    		}
				$i++;
	    	}
	    	return $output;
   		}
		else {
            return null;
        }   		   		   	
    }    
    
    // }}}
    // {{{ toHtml()

    /**
     * Returns the checkbox element in HTML
     * and the additional hidden element in HTML
     * 
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return parent::toHtml();
        } else {
        	
	    	$strHtml = "<div id=\"".$this->getName()."\" class=\"menuswitch\" >\n";     	
        	foreach($this->_elements as $key => $group)
        	{
        		$class = (!empty($group['class']))? ' class="'.$group['class'].'" ' : '';
        		
        		$strHtml .= "\t<dl>\t\t<dt".$class.">".$group['opt_name']."</dt>\n";        		
        		
        		foreach($group['QuickForm_checkboxes'] as $key => $checkbox)
        		{  			
        			$strHtml .= "\t\t\t<dd>".$checkbox->toHtml()."</dd>\n";
        		}
        		$strHtml .= "\t</dl>\n";
        	}
        	$strHtml .= "\t</div>\n";
        	
            return $strHtml;            
        }
    } //end func toHtml
    
    // }}}    
} //end class HTML_QuickForm_advcheckbox






/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
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
// $Id: checkbox.php,v 1.19 2004/02/28 22:10:16 avb Exp $


/**
 * HTML class for a checkbox type field
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.1
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_checkboxValue extends HTML_QuickForm_checkbox
{
    // {{{ constructor
	var $_value;
    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field value
     * @param     string    $text           (optional)Checkbox display text
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_checkboxValue($elementName=null, $elementLabel=null, $text='', $attributes=null, $value = '')
    {
        HTML_QuickForm_input::HTML_QuickForm_input($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_text = $text;
        $this->setType('checkbox');
        $this->_value = $value;
        $this->updateAttributes(array('value'=> $value));
        $this->_generateId();
    } //end constructor    
    // }}} 
    /**
     * Sets whether a checkbox is checked
     * 
     * @param     bool    $checked  Whether the field is checked or not
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setChecked($value)
    {

		if(is_array($value))
		{
	        if (isset($this->_value) && in_array($this->_value, $value) ) {      
	            $this->updateAttributes(array('checked'=>'checked'));
	        } else {
	        	$this->removeAttribute('checked');            
	        }
		}
		else 
		{
	        if (isset($this->_value) && ($this->_value == $value) ) {
	            $this->updateAttributes(array('checked'=>'checked'));
	        } else {
	        	$this->removeAttribute('checked');            
	        }			
		}
    } //end func setChecked    

    // {{{ getChecked()

    /**
     * Returns whether a checkbox is checked
     * 
     * @since     1.0
     * @access    public
     * @return    bool
     */
    function getChecked()
    {
        return (bool)$this->getAttribute('checked');
    } //end func getChecked
    
    // }}}        
    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setValue($value)
    {
    	$this->_value = $value;
        return $this->setChecked($value);
    } // end func setValue    
}
?>