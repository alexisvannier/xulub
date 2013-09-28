<?php
/**
 *  Fonction de validation d'une règle QuickForm
 * Vérifie que le champ date saisie respecte bien 
 * la syntaxe jj/mm/yy (ou jj-mm-yy)
 * 
 */

require_once('HTML/QuickForm/Rule.php');
class HTMLQuickFormRuleDate extends HTML_QuickForm_Rule
{
	/**
	 * Vérifie si la variable $value :
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