<?php
/**
 * Fonction de validation d'une règle QuickForm
 * Vérifie que le champ date saisie respecte bien 
 * la syntaxe jj/mm/yy hh:mi (ou jj-mm-yy hh:mi)
 * 
 */

require_once('HTMLQuickFormRuleDate.php');
class HTMLQuickFormRuleDateHeure extends HTMLQuickFormRuleDate
{
	/**
	 * Vérifie si le $value est une date suivie d'une heure
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value)
	{
		// on initialise les valeurs de retour
		$return1 = false;
		$return2 = false;
		if($value)
		{

			// on découpe la chaine de caractères
			// on sépare la date, de l'heure
			list($date,$heure) = split('[ ]', $value);

			// on vérifie que la date respecte bien le format dd/mm/yy
			$return1 = xbDate::verifDateFormat($date);

			if($heure)
			{
				// on vérifie que la variable $heure est une heure valide
				$return2 = xbDate::verifHeureFormat($heure);
			}

			if($return1 && $return2)
			{
				return true;
			}
		}
		return false;
	}
}
?>