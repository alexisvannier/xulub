<?php
/*
 * plugin de fonction smarty pour appeler une action Zend Framework directement
 * depuis un template Smarty
 */
function smarty_block_wiki($params, $text, &$smarty)
{
    // Si l'objet wiki a déjà été initialisé, on l'utilise
    if(!(isset($smarty->tpl_vars['wiki'])
            && $smarty->tpl_vars['wiki'] instanceof wiki2xhtml)
    ) {
        $smarty->tpl_vars['wiki'] = new Xulub_View_Helper_XbWiki();
    }

    return $smarty->tpl_vars['wiki']->XbWiki($text);
}