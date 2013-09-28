<?php
/**
 * Fonction de remplacement de xbUrl
 *
 * Renvoie une url
 *
 * Usage (dans un template Smarty) :
 * {zfUrl controller="Accueil" module="toto" action="index" ...}
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_XbUrl($params, &$smarty)
{
    trigger_error(
        'La fonction smarty XbUrl (ou xbUrl) est deprecated',
        E_DEPRECATED
    );
    $url = new Xulub_View_Helper_XbUrl();
    return $url->XbUrl($params);
}