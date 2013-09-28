<?php
/**
 * L'objectif de cette classe est d'effectuer la migration des xburl dans les templates
 * 
 * avant : {xburl page=MaPage app="MonModule"}
 * apr�s : {$this->Url(['controller' => 'MaPage', 'module' => 'MonModule'])}
 */
class Xulub_Migrate_Command_ReplaceXburl extends Xulub_Migrate_Command_Replace 
{
 
    /**
     * Execute la conversion des xburl
     */
    public function execute()
    {
        $this->_convertXburl();
        parent::execute();
    }
    
    /**
     * Recherche l'ensemble des occurences � convertir et g�n�re les nouvelles
     * chaines � utiliser
     * 
     */
    private function _convertXburl()
    {
        $content = file_get_contents($this->_path);
        $matches = array();
        
        /**
         * Recherche tous les {xburl ...} et stocke dans $matches[0],
         * toutes les occurences
         * 
         */
        $replace = preg_match_all(
            '/{xburl([aA-zZ0-9=\$ _."\n]*)}/', 
            $content, 
            $matches
        );

        /**
         * Pour toutes les occurences trouv�es, on va appliquer la m�thode 
         * de conversion des xburl
         */
        foreach($matches[0] as $match) {
                $replace = $this->_createXburl($match);
                $this->addStrReplace(
                    $match, 
                    $replace
                );
        }
    }
    
    /**
     * Convertit une chaine {xburl ...} en {$this->Url(...)}
     * 
     * @param string $match
     * @return string 
     */
    private function _createXburl($match)
    {
        // On supprime les �ventuels retours chariots
        $replace = array("\n", "\r");
        $string = str_replace($replace, '', $match);
        // les tabulations sont remplac�s par des espaces
        $string = str_replace("\t", ' ', $string);

        // Supprime tous les espaces en trop
        $string = preg_replace('/([ ]{2,30})/', ' ', $string);

        // On supprime xburl et les accolades
        $string = preg_replace('/{xburl (.*)}/', '$1', $string);

        // on supprime les �ventuels espace avant/apr�s
        $string = trim($string);

        // On recr�e un tableau avec les param�tres de xburl
        $tmpParams = explode(" ", $string);
        foreach($tmpParams as $param)
        {
            list($key, $value) = explode("=", $param);
            $params[$key] = $value;
        }

        // On reconstruit la chaine : 
        $return = '{$this->Url([';

        $count = 0;
        foreach($params as $key => $value) {
            if ($count > 0) {
                $return .= ', ';
            }

            switch ($key) {
                 case 'page' :
                    $return .= "'controller' => $value";
                    break;
                case 'app' :
                     $return .= "'module' => $value";
                    break;
                case '_fusion':
                    $flagReset = false;
                    break;
                default:
                    $return .= "'$key' => $value";
                    break;
            }
            $count++;
        }

        $return .= ']';

        $flagReset = false;
        if ($flagReset === true) {
            $return .= ', null , true';
        }

        $return .= ')}';
        return $return;
    }
}


