<?php
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/**
 * Afin de ne pas modifier HTML_QuickForm_Renderer_ArraySmarty,
 * on surcharge la classe HTML_QuickForm_Renderer_ArraySmarty
 *
 */
class XulubArraySmarty extends HTML_QuickForm_Renderer_ArraySmarty
{
    /**
    * Called when an element is required
    *
    * This method will add the required tag to the element label and/or the element html
    * such as defined with the method setRequiredTemplate.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    boolean     The element required
    * @param    string      The element error
    * @see      setRequiredTemplate()
    * @access   private
    * @return   void
    */
    function _renderRequired(&$label, &$html, &$required, &$error)
    {
        $this->_tpl->assign(array(
            'label'    => $label,
            'html'     => $html,
            'required' => $required,
            'error'    => $error
        ));
        if (!empty($label) && strpos($this->_required, $this->_tpl->left_delimiter . '$label') !== false) {
            $label = $this->_tplFetch($this->_required);
        }
        if (!empty($html) && strpos($this->_required, $this->_tpl->left_delimiter . '$html') !== false) {
            $html = $this->_tplFetch($this->_required);
        }

        // Remplacement de clear_assign par clearAssign
        $this->_tpl->clearAssign(array('label', 'html', 'required'));
    } // end func _renderRequired

   /**
    * Called when an element has a validation error
    *
    * This method will add the error message to the element label or the element html
    * such as defined with the method setErrorTemplate. If the error placeholder is not found
    * in the template, the error will be displayed in the form error block.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    string      The element error
    * @see      setErrorTemplate()
    * @access   private
    * @return   void
    */
    function _renderError(&$label, &$html, &$error)
    {
        $this->_tpl->assign(array('label' => '', 'html' => '', 'error' => $error));
        $error = $this->_tplFetch($this->_error);
        $this->_tpl->assign(array('label' => $label, 'html'  => $html));

        if (!empty($label) && strpos($this->_error, $this->_tpl->left_delimiter . '$label') !== false) {
            $label = $this->_tplFetch($this->_error);
        } elseif (!empty($html) && strpos($this->_error, $this->_tpl->left_delimiter . '$html') !== false) {
            $html = $this->_tplFetch($this->_error);
        }
        // Remplacement de clear_assign par clearAssign
        $this->_tpl->clearAssign(array('label', 'html', 'error'));
    } // end func _renderError



   /**
    * On réécrit cette méthode car à partir de Smarty3,
    * la fonction smarty_function_eval disparait
    *
    * On stocke donc en local le fichier tpl d'error et de required nécessaire
    *
    * @param    string      The template source
    * @access   private
    * @return   void
    */
    function _tplFetch($tplSource)
    {
        $allVars = $this->_tpl->getTemplateVars();

        // Pour calculer l'identifiant de cache, on ne s'occupe que des valeurs utilisés
	// dans _renderError et _renderRequired ($defaultArray)
        $defaultArray = array('label', 'html', 'error', 'required');
        $vars = array_intersect_key($allVars, array_flip($defaultArray));
        $cacheId = md5(serialize($vars));

        /**
         * see http://www.smarty.net/docs/en/template.resources.tpl
         * pour la syntaxe string:
         */
        $template_compile = $this->_tpl->fetch('string:'.$tplSource, $cacheId);
        return $template_compile;
    }// end func _tplFetch
}
?>