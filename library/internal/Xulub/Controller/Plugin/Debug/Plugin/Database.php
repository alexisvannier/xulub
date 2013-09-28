<?php

class Xulub_Controller_Plugin_Debug_Plugin_Database extends ZFDebug_Controller_Plugin_Debug_Plugin_Database
{
    /**
     * Fonction permettant d'ajouter un adapter en cours de traitement
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     * @param string $name
     */
    public function addAdapter($adapter, $name)
    {
        if ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $adapter->getProfiler()->setEnabled(true);
            $this->_db[$name] = $adapter;
        }
    }

    /**
     * On surcharge le getPanel afin d'ajouter les paramètres passées à
     * l'ensemble des URL
     *
     * @return string
     */
    public function getPanel()
    {
        if (!$this->_db) {
            return '';
        }

        $html = '<h4>Database queries</h4>';
        if (Zend_Db_Table_Abstract::getDefaultMetadataCache()) {
            $html .= 'Metadata cache is ENABLED';
        } else {
            $html .= 'Metadata cache is DISABLED';
        }

        foreach ($this->_db as $name => $adapter) {

            if ($profiles = $adapter->getProfiler()->getQueryProfiles()) {

                $html .= '<h4>Adapter '.$name.'</h4><ol>';

                foreach ($profiles as $profile) {
                    $html .= '<li><strong>['
                    . round($profile->getElapsedSecs()*1000, 2)
                    . ' ms]</strong> '
                    . htmlspecialchars($profile->getQuery());

                    if (count($profile->getQueryParams())) {
                        $html .= ' <br />params : <br />'
                              . '&nbsp;&nbsp;' . var_export($profile->getQueryParams(), 1)
                              . '';
                    }
                    $html .= '</li>';
                }
                $html .= '</ol>';
            }
        }
        return $html;
    }
}