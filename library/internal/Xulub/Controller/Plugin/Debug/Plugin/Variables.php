<?php
/**
 *
 * Plugin de Xulub_Controller_Plugin_Debug permettant d'afficher les variables
 * en cours d'utilisation dans un paneau
 *
 * @category Xulub
 * @package Xulub_Controller
 * @subpackage Xulub_Controller_Plugin_Debug::
 *
 */
class Xulub_Controller_Plugin_Debug_Plugin_Variables extends ZFDebug_Controller_Plugin_Debug_Plugin_Variables implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Traitement identique à la fonction de la classe père à l'exception de la
     * gestion de l'affichage des variables Smarty
     *
     * @param array $values
     * @return string
     */
    protected function _cleanData($values)
    {
        if (is_array($values)) {
            ksort($values);
        }

        $retVal = '<div class="pre">';

        foreach ($values as $key => $value) {

            $key = htmlspecialchars($key);

            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.'<br />';
            } else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlspecialchars($value).'\'<br />';
            } else if (is_array($value)) {
                $retVal .= $key.' => '.self::_cleanData($value);
            } else if ($value instanceof Smarty_Variable) {
                // Ajouté par François
                // Permet de gérer l'affichage des variables Smarty
                // On utilise la méthode debug_print_var de Smarty
                // qu'il est nécessaire de charger à la main
                require_once FRAMEWORK_PATH . DIRECTORY_SEPARATOR
                             . 'library' . DIRECTORY_SEPARATOR
                             . 'vendor' . DIRECTORY_SEPARATOR
                             . 'Smarty' . DIRECTORY_SEPARATOR
                             .'libs' . DIRECTORY_SEPARATOR
                             . 'plugins' . DIRECTORY_SEPARATOR
                             . 'modifier.debug_print_var.php';
                $retVal .= $key . ' => ' . smarty_modifier_debug_print_var($value) . '<br /><br />';
            } else if (is_object($value)) {
                $retVal .= $key.' => '.get_class($value).' Object()<br />';
            } else if (is_null($value)) {
                $retVal .= $key.' => NULL<br />';
            }
        }
        return $retVal.'</div>';
    }
}