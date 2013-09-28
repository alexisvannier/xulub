<?php
/**
 *  Fonction de validation d'une r�gle QuickForm
 * V�rifie que le champ date saisie respecte bien 
 * la syntaxe jj/mm/yy (ou jj-mm-yy)
 * 
 */

require_once('HTML/QuickForm/Rule.php');
class HTMLQuickFormRuleDate extends HTML_QuickForm_Rule
{
	/**
	 * V�rifie si la variable $value :
	 *  * respecte bien le format jj-mm-yy
	 *  * est une date valide
	 *
	 * @param string $value
	 * @return boolean
	 */
    public function validate($value)
    {
    	return xbDate::verifDateFormat($value);
    }
}

?>