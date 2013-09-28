<?php

/**
 * Définition de la classe de migration de la version 0.7 vers la version 0.8.0
 */
class Xulub_Migrate_MigrationV7V8 extends Xulub_Migrate_MigrationAbstract
{    
    /**
     * répertoire de stockage de apps
     * @var string
     */
    private $_appsDir;
    
    /**
     * répertoire contenant les objets
     * @var string
     */
    private $_objetsDir;
    

    public function __construct(
        $projetDir, 
        $projectNamespace, 
        $templateDir,
        $excludeDir = array(),
        $scmPath = '/opt/csw/bin/svn'
    )
    {
        parent::__construct(
            $projetDir, 
            $projectNamespace, 
            $templateDir, 
            $excludeDir, 
            $scmPath
        );

        $this->_appsDir = $this->_projetDir . '/apps';
        $this->_objetsDir = $this->_projetDir . '/objets';
    }

    public function __destruct()
    {
        $this->toDo();
    }

    /**
     * Liste les actions à réaliser
     */
    public function toDo()
    {
        $this->log(' @todo : gestion des redirections');
        $this->log(' @todo : gérer le cas des vues utilisant une même classe');
        $this->log(' @todo : require_once');
    }

    public function start()
    {
        $this->excludeDir();

        $this->prepare();

        /**
         * déplacement des objets dans library/ProjetNamespace/Model
         */
        $this->migrateObjetsToModel();

        /**
         * déplacement de apps dans application
         */
        $this->migrateAppsDir();
        
        /**
         * Déplacement du répertoire public_html
         */
        $this->migratePublicHtml();
        
        /**
         * Modification des vues
         */
        $this->replaceContentView();
        
        /**
         * Modification des scripts PHP
         */
        $this->replaceContentScriptPhp();
        
	/**
         * ajout des fichiers par défaut
         */
	$this->addDefaultFiles();
    }

    /**
     * Préparation à la migration
     * Crée les répertoires qui seront utilisés par la version 0.8
     */
    public function prepare()
    {
        $this->log('');
        $this->log(' # prepare ');
        $this->log(' # -------- ');
        $this->log('');

        /**
         * répertoire de stockage de l'application
         */
        $this->addDirectory($this->_projetDir . '/application');
        $this->addDirectory($this->_controllerDir);
        $this->addDirectory($this->_configDir);
        
        /**
         * répertoire de stockage des classes de l'application
         */
        $this->addDirectory($this->_projetDir . '/library');
        $this->addDirectory($this->_libraryNamespaceDir);
        $this->addDirectory($this->_modelDir);
        
        /**
         * On crée les répertoires temporaires / de travail
         */
        $this->createWorkingDirectory();
    }

    /**
     * Déplacement des répertoires et des fichiers objets dans library/Model
     *
     */
    public function migrateObjetsToModel()
    {
        $this->log('');
        $this->log(' # déplacement Objets en Model ');
        $this->log(' # -------- ');
        $this->log('');
        $dirIt = $this->_getIterator($this->_objetsDir);

        foreach ($dirIt as $file_info) {
            if (strpos($file_info->getPath(), '/.svn') !== false) {
                continue;
            }
            
            // on ne traite que les répertoires
            if ($file_info->isDir()) {
                $rep = $this->_modelDir . ucfirst(basename($file_info->getPathname()));
                $this->addDirectory($rep);
            }
            else
            {
                /**
                 * On récupère tous les niveaux de répertoire
                 * Exemple : 
                 * Si on avait un répertoire présent dans objets/Module/SousModule,
                 * on récupère Module/SousModule
                 */
                $modules = substr(dirname($file_info->getPathname()), strlen($this->_objetsDir)+1);
                
                /**
                 * Contient la liste des modules/sous-modules en tableau
                 * Les modules-sous-modules doivent commencer par une majuscule
                 */
                $aModules = explode('/', $modules);
                $aModules = array_map('ucfirst', $aModules);
                
                $class = basename($file_info, '.php');
                
                $replacement = $this->_projectNamespace 
                             . '_Model_' . implode('_', $aModules) 
                             . '_' . $class;
                $this->_classToReplace[$class] = $replacement;
                
                $newBasename = $this->_modelDir . implode('/', $aModules);
                $this->addDirectory($newBasename);
                $this->move($file_info->getPathname(), $newBasename . '/' . basename($file_info));
            }
        }
    }

    /**
     *  Déplacement des contenus des répertoires modules de apps/<Module> 
     *  vers application/controllers/<module>
     * 
     * On le fait en 2 fois : 
     *  * Création du répertoire cible
     *  * svn mv des fichiers
     *  
     * On ne fait pas de svn move des répertoires, 
     * sinon, on ne pourra plus faire de svn move des fichiers
     *  
     * 
     *  - on boucle sur le répertoire apps
     *  - on déplace chaque fichier (php et tpl) individuellement en le renommant
     *  - on déplace le fichier languages.xml
     *  - @todo : supprimer les fichiers restants
     */
    public function migrateAppsDir()
    {
        $this->log('');
        $this->log(' # migration du répertoire Apps');
        $this->log(' # ----------------------------');
        $this->log('');

        $adi = $this->_getIterator($this->_appsDir);
        foreach ($adi as $object) {
            
            if (strpos($object->getPath(), '/.svn') !== false) {
                continue;
            }

            if ($object->isDir()) {
                // le filtre utilisé avec AdvancedDirectoryIterator ne recupere pas les repertoires...
                $newPath = $this->_controllerDir . '/' . self::ConvertCamelCaseToSeparateWord(basename($object->getPathname()));
                $this->move($object->getPathname(), $newPath);
                $moduleName = basename($object->getPathname());
            }
            else {
                $extension = substr($object->getPathname(), -4);
                $fileNoExtension = basename($object->getPathname(), $extension);

                $this->log('    - traitement de ' . $object->getPathname());
                
                $this->addDirectory($this->_controllerDir);
                switch ($extension) {
                    case '.php' :
                        $moduleName = basename(dirname($object->getPathname()));
                        $moduleName = self::ConvertCamelCaseToSeparateWord($moduleName);
                        $newBasename = $this->_controllerDir . '/' . $moduleName;
                        $this->addDirectory($newBasename);
                        $newPath = $newBasename . '/' . $fileNoExtension . 'Controller.php';
                        $this->move($object->getPathname(), $newPath);
                        break;

                    case '.tpl' :
                        $moduleName = basename(dirname(dirname($object->getPathname())));
                        $moduleName = self::ConvertCamelCaseToSeparateWord($moduleName);
                        $newBasename = $this->_controllerDir . '/' . $moduleName . '/template';
                        $this->addDirectory($this->_controllerDir . '/' . $moduleName);
                        $this->addDirectory($newBasename);
                        $newPath = $newBasename . '/' . self::ConvertCamelCaseToSeparateWord($fileNoExtension) . '_index.tpl';
                        $this->move($object->getPathname(), $newPath);
                        break;

                    case '.xml' :
                        if ($fileNoExtension == 'languages') {
                            $moduleName = basename(dirname($object->getPathname()));
                            $moduleName = self::ConvertCamelCaseToSeparateWord($moduleName);
                            $newBasename = $this->_controllerDir . '/' . $moduleName;
                            $this->addDirectory($newBasename);
                            $newPath = $newBasename . '/' . $fileNoExtension . $extension;
                            $this->move($object->getPathname(), $newPath);
                            $this->replaceContentLanguagesXml($newPath);
                        }
                        break;
                }
            }
        }
    }

    public function migratePublicHtml()
    {
        $this->log('');
        $this->log(' # migration du répertoire public_html');
        $this->log(' # ----------------------------');
        $this->log('');
        
        $this->move('public_html', 'public');
        /**
         * @todo : créer un lien symbolique
         */
        $this->log('');
    }
    
    /**
     * Remplacement du contenu dans les scripts PHP
     */
    public function replaceContentScriptPhp()
    {
        $this->log('');
        $this->log('  # Remplacement dans les scripts PHP');
        $this->log('  ----------------------------------');
        $this->log('');        
        
        foreach ($this->_commands as $command) {
            
            if (method_exists($command, 'getNewPath') 
                && strstr($command->getNewPath(), '.php')) {
                $filePath = $command->getNewPath();
                
                $this->log('');
                $this->log('  - traitement : ' . $filePath);
                $this->log('');
                
                $myCommand = new Xulub_Migrate_Command_Replace($filePath);
                
                if (strpos($filePath, 'Controller.php') !== false) {
                    $this->replaceContentController($myCommand);
                } else {
                    $this->replaceContentModel($myCommand);
                }
                
                $this->replaceOldMethodPhpFile($myCommand);
                
                $this->addCommand($myCommand);
                
                $this->log('');
            }
        }
    }

    /**
     * Remplacement du contenu dans les controllers
     */
    public function replaceContentController($command)
    {
        $filePath = $command->getPath();
           
        # Remplacement du nom de la classe dans les entêtes de fichier
        $controller_file = basename($filePath, '.php');
        $module = basename(dirname($filePath));

        # Remplacement de Class MaPage extends xbComposant par
        $replacement = self::convertSeparateWordToCamelCase($module) . '_' . $controller_file . ' extends';
        $command->addReplace(
            '/class ([aA-zZ]+) extends/',
            'class ' . $replacement
        );
        
        # remplacement de function build() par function indexAction()
        $command->addStrReplace(
            'function build()', 
            'function indexAction()'
        );
        
        # remplacement de function build() par function indexAction()
        $command->addStrReplace(
            'parent::build();', 
            '// Dans la plupart des cas, ce n\'est plus utile '. PHP_EOL . '// parent::indexAction();'
        );

        
        # remplacement de $this->user par $this->_helper->user() (attention aux parenthèse)
        $command->addStrReplace(
            '$this->user', 
            '$this->getUser()'
        );

        /**
         * remplacement de $this->addForm par $this->_helper->Form->addForm
         * remplacement de $this->getForm par $this->_helper->Form->getForm
         */
        $command->addStrReplace(
            '$this->addForm(', 
            '$this->_helper->xbForm->addForm('
        );
        $command->addStrReplace(
            '$this->getForm(', 
            '$this->_helper->xbForm->getForm('
        );

        /**
         * remplacement de translate() par $this->_helper->Translate
         */
        $command->addStrReplace(
            'translate(', 
            '$this->view->translate('
        );
        
         /**
         * remplacement de xbHttp::redirect() par $this->_redirect()
         */
        $command->addStrReplace(
            'xbHttp::redirect(', 
            '$this->_redirect('
        );
    }
    
    public function replaceContentModel($command)
    {
        $filePath = $command->getPath();
        $class = basename($filePath, '.php');
        
        /**
         * On récupère tous les niveaux de répertoire
         * Exemple : 
         * Si on avait un répertoire présent dans objets/Module/SousModule,
         * on récupère Module/SousModule
         */
        $modules = substr(dirname($filePath), strlen($this->_modelDir));

        /**
         * Contient la liste des modules/sous-modules en tableau
         * Les modules-sous-modules doivent commencer par une majuscule
         */
        $aModules = explode('/', $modules);
        $aModules = array_map('ucfirst', $aModules);
        
        # Remplacement de Class MaPage extends xbComposant par
        $replacement = $this->_projectNamespace . '_Model_'
                     . implode('_', $aModules) . '_' . $class . ' extends';
        $command->addReplace(
            '/class ([aA-zZ]+) extends/',
            'class ' . $replacement
            );
        
    }

    /**
     * Remplacement du contenu dans les controllers
     */
    public function replaceContentView()
    {
        $this->log('');
        $this->log('  # Remplacement dans les vues');
        $this->log('  ----------------------------------');
        $this->log('');
        
        foreach ($this->_commands as $command) {
            
            if (method_exists($command, 'getNewPath') 
                && strstr($command->getNewPath(), '.tpl'))
            {
                $filePath = $command->getNewPath();
                $this->log('  * Vue : ' . $filePath);
                
                /**
                 * Remplace les {xburl ..} par $this->Url([]
                 */
                $replace = new Xulub_Migrate_Command_ReplaceXburl($filePath);
                $this->addCommand($replace);
                
                $replace = new Xulub_Migrate_Command_Replace($filePath);
                // Conversion des chaines de langues
                // Attention : ne gère pas les variables dans les chaines de langue
                $replace->addReplace(
                    '/\{t\}([aA-zZ0-9_]*)\{\/t\}/', 
                    '{$this->translate(\'$1\')}'
                );                
                $replace->addReplace(
                    '/\{t wiki=["]?true["]?\}([aA-zZ0-9_]*)\{\/t\}/', 
                    '{$this->XbWiki($this->translate(\'$1\'))}'
                );
                $replace->addReplace(
                    '/\{t wiki=["]?true["]? removeptag=["]?true["]?\}([aA-zZ0-9_]*)\{\/t\}/', 
                    '{$this->XbWiki($this->translate(\'$1\'), true)}'
                );
                /**
                 * Remplace les espaces après ou avant les accoladdes
                 * @todo remplacer par une expression régulière
                 */
                $replace->addStrReplace(
                    '{ ', 
                    '{'
                );
                $replace->addStrReplace(
                    ' }', 
                    '}'
                );   
                
                /**
                 * Remplace les hook par des placeholders
                 */
                $replace->addReplace(
                    '/{hook name="(.*)"}/',
                    '{$this->placeholder(\'$1\')}'
                    );
                $this->addCommand($replace);
            }
        }
        $this->log('');
    }

    /**
     * Convertit un fichier languages.xml dans son nouveau format
     * 
     * @param string $file 
     */
    public function replaceContentLanguagesXml($file)
    {
        $command = new Xulub_Migrate_Command_Replace($file);
        /**
         * on remplace la première occurence de <translation> par <translations>
         */
        $command->addReplace(
            '/<translation>/',
            '<translations>'
        );        
        /**
         * on remplace la dernière occurence de </translation> en </translations>
         */
        $command->addReplace(
            '/<\/translation>\s*$/', 
            '</translations>'
        );
        
        /**
         * on remplace language="fr" par language="fr_FR"
         */
        $command->addStrReplace(
            'language="fr"',
            'language="fr_FR"'
        );
        $this->addCommand($command);
    }
    
    /**
     * Fonction qui permet de remplacer les fonctions devenues obsolètes dans les scripts PHP
     */
    public function replaceOldMethodPhpFile($command)
    {        
        /**
         * Remplace XULUB_APP_DIR par le chemin vers les controllers
         */
        $command->addStrReplace(
            'XULUB_APP_DIR', 
            'APPLICATION_PATH . \'/controllers\''
        );
        
        
        /**
         * On supprime les require_once, les include_once puisqu'on utilise l'autoloading
         */
        $command->addReplace(
            '/(.*)require_once\((.*)\);/', 
            '// désactive les require_once puisque on utilise l\'autoload ' . PHP_EOL
            . '// $1$require_once($2);'
        );
        $command->addReplace(
            '/(.*)include_once\((.*)\);/', 
            '// désactive les include_once puisque on utilise l\'autoload ' . PHP_EOL
            . '// $1$include_once($2);'
        );
        $command->addReplace(
            '/(.*)require\((.*)\);/', 
            '// désactive les require_once puisque on utilise l\'autoload ' . PHP_EOL
            . '// $1$require($2);'
        );
        $command->addReplace(
            '/(.*)include\((.*)\);/', 
            '// désactive les include_once puisque on utilise l\'autoload ' . PHP_EOL
            . '// $1$include($2);'
        );        
        
        # Suppression des debug_display
        $command->addStrReplace(
            'debug_display(', 
            '// debug_display('
        );
        
        
        /**
         * Remplace les addComposant par des placeholders (à valider)
         * Mauvais au niveau performance
         */
        $command->addReplace(
            '/\$this->addComposant\((.*), ?(.*), ?(.*), ?(.*)\);/', 
            '$this->view->placeholder($3)->set($this->view->action(\'index\', $2, $1, $4));'
        );
        /**
         * Remplace les addComposant sans params par des placeholders (à valider)
         * Mauvais au niveau performance
         */
        $command->addReplace(
            '/\$this->addComposant\((.*), ?(.*), ?(.*)\);/', 
            '$this->view->placeholder($3)->set($this->view->action(\'index\', $2, $1));'
        );
        
              
        /**
         * remplacement de xbRegistry par Zend_Registry
         */
        $command->addStrReplace(
            'xbRegistry', 
            'Zend_Registry'
        );

        /**
         * remplacement de xbComposant par Xulub_Action_Controller
         */
        $command->addStrReplace(
            'xbComposant', 
            'Xulub_Controller_Action '
        );

        # remplacement de xbDbExecute par Xulub_Db
        # Attention : il peut y avoir des problèmes avec le selectLimit 
        $command->addStrReplace(
            'xbDbExecute', 
            'Xulub_Db'
        );
        
        # remplacement de xbDb par Xulub_Db
        $command->addStrReplace(
            'xbDb', 
            'Xulub_Db'
        );

        # remplacement de xbUser par Xulub_User
        $command->addStrReplace(
            'xbUser', 
            'Xulub_User'
        );

        # remplacement de xbUrl par $this->_helper->Url
        $command->addStrReplace(
            'xburl(', 
            '$this->view->Url('
        );

        /**
         * remplacement de Zend_Registry::getInstance()->url par Zend_Registry::get(\'url\')
         * Facilitera le remplacement ensuite
         */
        $command->addStrReplace(
            'Zend_Registry::getInstance()->url', 
            'Zend_Registry::get(\'url\')'
        );        
        
        $command->addStrReplace(
            'Zend_Registry::get(\'url\')->getApp()', 
            '$this->_request->getModuleName()'
        );
        
        $command->addStrReplace(
            'Zend_Registry::get(\'url\')->getArguments()', 
            '$this->_request->getParams()'
        );
        
        
        
        # remplacement de Zend_Registry::getInstance()->url->getPage() par $this->_request->getModuleName()
        $command->addStrReplace(
            'Zend_Registry::get(\'url\')->getPage()', 
            '$this->_request->getControllerName()'
        );

        # remplacement de Zend_Registry::getInstance()->url->getPage() par $this->_request->getModuleName()
        $command->addStrReplace(
            'Zend_Registry::get(\'url\')->getProfil()', 
            '$this->_request->getParam(\'profil\')'
        );
        
        $command->addStrReplace(
            'Zend_Registry::get(\'url\')->getArgument(', 
            '$this->_request->getParam('
        );
        
        $command->addReplace(
            '/(.*)parametreUrl\(\'(.*)\'\)([ ;=])/', 
            '// parametreUrl renvoyait false si la variable n\'existait pas ' . PHP_EOL
            . '$1$this->_request->getParam(\'$2\', false)$3'
        );

        # remplacement de xbSession par Xulub_Session
        $command->addStrReplace(
            'xbSession', 
            'Xulub_Session'
        );

        # Gestion des fichiers CSS
        # Remplacement de xbMeta::addCss($css_file, $media) par $this->view->headLink()->appendStylesheet($css_file, $media);
        $command->addReplace(
            '/xbMeta::addCssItem\((.*),(.*)(,|\))/', 
            '$this->view->headLink()->appendStylesheet($1, $2);#'
        );

        # Gestion des fichiers JS
        # Remplacement de xbMeta::addJs($file, true, 'group' par $this->view->headScript()->appendFile($file))
        $command->addReplace(
            '/xbMeta::addJsItem\((.*)(,|\))/', 
            '$this->view->headScript()->appendFile($1);#'
        );

        # Remplacement de xbString par Xulub_Utils_String
        $command->addStrReplace(
            'xbString::', 
            'Xulub_Utils_String'
        );

        # Remplacement de xbPaginator par Zend_Paginator
        $command->addStrReplace(
            'xbPaginator::', 
            'Zend_Paginator'
        );

        # Gestion de la balise Title
        # Remplacement de xbMeta::setTitle par $this->view->headTitle
        $command->addStrReplace(
            'xbMeta::setTitle', 
            '$this->view->headTitle'
        );

        # gestion des balises keywords
        $command->addStrReplace(
            'xbMeta::addMeta("keywords",', 
            '$this->view->headMeta()->appendName(\'keywords\', '
        );
        $command->addStrReplace(
            'xbMeta::addMeta("description",', 
            '$this->view->headMeta()->appendName(\'description\', '
        );

        # Remplacement de $GLOBALS['config'] par
        $command->addStrReplace(
            '$GLOBALS[\'config\']', 
            'Zend_Registry::get(\'appli-config\')'
        );

        /**
         * Remplace toutes les classes dans les objets et les controllers
         */
        foreach ($this->_classToReplace as $class => $replace) {
            $command->addReplace(
                '/([\s.,\(]+)' . $class . '::/', 
                '$1' . $replace . '::'
            );
            $command->addStrReplace(
                'new ' . $class . '(', 
                'new ' . $replace . '('
            );
            $command->addReplace(
                '/class ' . $class . '([\s{]+)/', 
                'class ' . $replace . '$1'
            );
            $command->addStrReplace(
                'extends ' . $class, 
                'extends ' . $replace
            );
        }

        /**
         * Spéficifiques aux applications utilisant auth-intranet
         */
        $command->addReplace(
            '/extends (.*)IntranetComposant/',
            'extends Authintranet_Controller_IntranetAbstract'
        );
    }
    
    public function addDefaultFiles()
    {
        $this->log('');
        $this->log(' # ajout des fichiers par défaut ');
        $this->log(' # -------- ');
        $this->log('');
        
        $iterator = $this->_getIterator($this->getTemplateDir());
        foreach($iterator as $file)
        {
            if (strpos($file->getPath(), '/.svn') !== false) {
                continue;
            } 
            
            $filePath = $file->getPath() . '/' . $file->getBasename(); 
            $this->log(    ' -  ' . $filePath);
            $content = file_get_contents($filePath);

            $sousArbo = substr($filePath, strlen($this->getTemplateDir())+1);
            $newPath = $this->_projetDir . DIRECTORY_SEPARATOR . $sousArbo;

            $command = new Xulub_Migrate_Command_Add(
                $newPath,
                $content
            );
            $this->addCommand($command);
        }
    }

    /**
     * Fonction qui permet de créer les répertoires temporaires.
     * Ces répertoires ne doivent pas être ajoutés à subversion
     * Ils pourront ensuite être créés via le script d'installation.
     */
    public function createWorkingDirectory()
    {
        $this->log('');
        $this->log(' # Création working directory ');
        $this->log(' # @todo : ne pas faire de add ');
        $this->log(' # -------- ');
        $this->log('');

        $temp_dir = array(
            $this->_projetDir . '/application/share/cache/database',
            $this->_projetDir . '/application/share/locale/fr_FR/LC_MESSAGES',
            $this->_projetDir . '/application/share/logs',
            $this->_projetDir . '/application/share/session',
            $this->_projetDir . '/application/share/smarty/templates_c',
        );
        
        $this->addDirectory($this->_projetDir . '/application');
        $this->addDirectory($this->_projetDir . '/application/share');
        $this->addDirectory($this->_projetDir . '/application/share/cache');
        $this->addDirectory($this->_projetDir . '/application/share/locale');
        $this->addDirectory($this->_projetDir . '/application/share/locale/fr_FR');
        $this->addDirectory($this->_projetDir . '/application/share/smarty');
        
        foreach ($temp_dir as $dir) {
            $this->addDirectory($dir);
        }
        $this->log('');
    }

    /**
     * Transforme CamelCase
     *  * en camel-case si lower = true
     *  * en Camel-Case si lower = false
     *
     * @param string $string
     * @param boolean $lower
     * @return string
     */
    public static function ConvertCamelCaseToSeparateWord($string, $lower = true)
    {
        $pattern = '/([^A-Z-])([A-Z])/';
        $replace = '$1-$2';

        $replace = preg_replace($pattern, $replace, $string);
        if ($lower === true) {
            $replace = strtolower($replace);
        }

        return $replace;
    }

    /**
     * Convertit une chaine séparée par un caractère (- par défaut) en une chaine CamelCase
     *
     * @param string $string
     * @param string $separate
     * @retirn string
     */
    public static function ConvertSeparateWordToCamelCase($string, $separate = '-')
    {
        $split = explode($separate, $string);
        $string_camel_case = implode(array_map('ucfirst', $split));
        return $string_camel_case;
    }
}
