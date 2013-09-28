<?php

class Xulub_View_Helper_XbUrl extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     *
     * @param array $params
     * @return string
     *
     * @todo : étudier l'utilisation d'un helper en remplacement
     * $this->_helper->url()
     */
    public function buildUrl ($params = array())
    {
        if (is_null($params)) {
            $params = array();
        }

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $paramRouter = array(
            'langue' => $request->getParam('langue'),
            'profil' => $request->getParam('profil'),
            'module' => $request->getParam('module'),
            'controller' => $request->getParam('controller'),
            'action' => $request->getParam('action')
        );
        $defaultParams = $request->getParams();

        $addHost = false;

        // protocole
        $proto = null;

        // serveur
        $server = null;

        $args = array();

        // flag permettant de déterminer si on souhaite réinitialiser les
        // arguments
        $reset = true;

        // flag permettant de déterminer si on souhaite fusionner les arguments
        $fusion = false;

        foreach ($params as $key => $value) {
            switch ($key) {
                case "lang" :
                    $paramRouter['langue'] = $value;
                    break;
                case "profil" :
                    $paramRouter['profil'] = $value;
                    break;
                case "module" :
                case "app":
                    $paramRouter['module'] = $value;
                    break;
                case "controller" :
                case "page":
                    $paramRouter['controller'] = $value;
                    break;
                case "_fusion":
                    $fusion = true;
                    break;
                default:
                    $paramRouter[$key] = $value;
                    break;
            }
        }

        if ($fusion === true) {
            $paramRouter = array_merge($paramRouter, $defaultParams);
        }

        // la route a construire : :langue/:profil/:module/:controller/*
        $url = $front->getRouter()->assemble($paramRouter, null, true);
        return $url;
    }

    /**
     * Perform helper when called as $this->_helper->url() from an action
     * controller
     *
     * Proxies to {@link simple()}
     *
     * @param  array  $params
     * @return string
     */
    public function XbUrl ($params = array())
    {
        return $this->buildUrl($params);
    }
}