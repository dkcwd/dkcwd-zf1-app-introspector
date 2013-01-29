<?php 
/** 
 * A class for use with Zend Framework (v1) applications. 
 * Provides a way to quickly generate an array representing the structure of your application.
 * @link http://github.com/dkcwd/dkcwd-zf1-app-introspector for the canonical source repository
 * @author Dave Clark dave@dkcwd.com.au 
 * @copyright Dave Clark 2013
 * @license http://opensource.org/licenses/mit-license.php
 */
class Dkcwd_AppIntrospector_AppIntrospector
{    
    /**
     * The value for the application path property.
     * If using the standard Zend Framework application directory structure generated by
     * Zend_Tool this will be equivalent to: 'full-path-to-project/application'
     * 
     * @var string $applicationPath 
     */
    public $applicationPath = false;   
     
    /**
     * The value for the module path property.
     * If using the standard Zend Framework application directory structure generated by
     * Zend_Tool this will be equivalent to: 'full-path-to-project/application/modules'
     *
     * @var string $modulePath
     */
    public $modulePath = false;
    
    /**
     * The default module name within ZF1 applications is 'default'.      
     *
     * @var string $applicationPath
     */
    public $defaultApplicationModuleName = 'default';  

    /**
     * An array to hold class names which we have explicitly loaded. 
     *
     * @var array $loadedClasses
     */
    public $loadedClasses = array();
    
    /**
     * An array to hold module names which should be excluded from operations. 
     *
     * @var array $excludeModules
     */
    public $excludeModules = array();
    
    /**
     * An array to hold the names of modules which have been located.
     *
     * @var array $moduleList
     */
    protected $moduleList = array();  

    /**
     * An array representing the structure of the application.
     *
     * @var array $appStructure
     */
    protected $appStructure = array();
    
    /**
     * Builds a Dkcwd_AppIntrospector_AppIntrospector object. 
     * You must supply a valid $applicationPath parameter and, if you use modules
     * in your ZF1 application, you should specify the path to the folder your 
     * modules are stored in as a second parameter.
     *
     * @param string $applicationPath
     * @param string $modulePath [optional]
     * @return Dkcwd_AppIntrospector_AppIntrospector $this
     */
    public function __construct($applicationPath, $modulePath = null)
    {
        $this->setApplicationPath($applicationPath);
        if (!is_null($modulePath)) $this->setModulePath($modulePath);        
        return $this;
    }
	
    /**
     * Returns a string representing the class name of a given controller.
     * 
     * @param string $controller
     * @return string $className
     * @throws InvalidArgumentException 
     */
    public function getClassNameFromControllerFile($controller)
    {
        // We will attempt to open the controller file and get the class name
        if (is_string($controller) && is_file($controller) && is_readable($controller)) {
            
            // try opening the controller file for reading
            $fp = fopen($controller, 'r');
            
            $className = '';
            $buffer = '';
            $i = 0;
            
            while ($className == '') {                
	        if (feof($fp)) break;
	        $buffer .= fread($fp, 512);
	        $tokens = token_get_all($buffer);
	           
	        // if we can't find a '{' in the 512 bytes we read into $buffer
	        // we should get the next 512 bytes from the file and try again
	        if (strpos($buffer, '{') === false) continue;
	        for ($i=0; $i < count($tokens); $i++) {
	            if ($tokens[$i][0] === T_CLASS) {	                    	                    
	                for ($j=$i+1; $j < count($tokens); $j++) {
	                    if ($tokens[$j] === '{') {	                            
	                        $className = $tokens[$i+2][1];
	                        return $className;
	                    }
	                }
	            }
	        }
            }
        }
        
        throw new InvalidArgumentException('The controller file path supplied is not valid or the file is not readable');
    }
	
    /**
     * Returns an array representing the defined and inherited public actions of a given
     * controller based on the public actions which can be located.
     * 
     * @param string $controller
     * @return array $actions
     * @throws InvalidArgumentException
     */
    public function getControllerActions($controller)
    {
        // To get the controller actions we will use get_class_methods($className).
        // To ensure this is successful we have to make sure the class has been loaded.
        if (is_string($controller) && is_file($controller) && is_readable($controller)) {
            $className = $this->getClassNameFromControllerFile($controller);
            
            if (in_array($className, $this->loadedClasses)) {
                // no need to autoload since we can tell the class is already in scope
            } else {
                // set Zend_Loader to use include_once instead of require_once as this
                // avoids redeclaration errors
                Zend_Loader::loadFile($controller, null, true);
                $this->loadedClasses[] = $className;
            }
            
            $actions = get_class_methods($className);
            return $actions;
        }
        
        throw new InvalidArgumentException('The controller file path supplied is not valid or the file is not readable');
    }
    
    /**
     * Returns an array representing the application structure based on the
     * readable modules, controllers and public actions which can be located.
     * 
     * @return array $this->appStructure
     * @throws InvalidArgumentException
     */
     public function buildAppStructureArray()
     {
         $this->appStructure = array();
         
         // get current working directory before building the array
         $owd = getcwd();
         $this->moduleList = $this->buildModulesList();
         
         if (is_array($this->moduleList)) {
             foreach ($this->moduleList as $module) {
                 $this->buildControllersList($module);
             }
         }
         
         // force change to original working directory
         chdir($owd);
         
         return $this->appStructure;
     }
     
    /**
     * Updates the $appStructure property with details of the controllers
     * and their public actions
     * 
     * @param string $module
     * @return void
     * @throws InvalidArgumentException
     */
    public function buildControllersList($module)
    {
        if (!is_string($module)) throw new InvalidArgumentException('An invalid module name was supplied');
        
        // Typically the 'default' module in a ZF1 application is located in the
        // main 'application' folder.  A switch statement based on the module name
        // should be sufficient for handling this.  
        switch ($module) {
            case $this->defaultApplicationModuleName:
	        // change working directory to $this->applicationPath
	    	chdir($this->applicationPath);
	    	    
	    	// search for 'controllers' folder
	    	$controllersfolder = glob('controllers');
	    	    
	    	// if we have a result for the controllers folder get the controllers
	    	if (is_array($controllersfolder)) {
	    	    chdir($this->applicationPath . DIRECTORY_SEPARATOR . $controllersfolder[0]);	    	        
	    	    $controllers = glob('*Controller.php');
	    	        
	    	    if (is_array($controllers)) {
	    	        foreach($controllers as $controller) {
	    	            if (is_string($controller)) {
	    	                $tempArray = explode('Controller.php', $controller);
	    	                $con = $tempArray[0];
	    	                $controllerFullName = strstr($controller, '.php', true);
	    	                    
	    	                $data = array(
	    	                    'module' => $module,
	    	                    'controller' => $con,
	    	                    'controllerPath' => getcwd() . DIRECTORY_SEPARATOR . $controller,
	    	                    'controllerFullName' => $controllerFullName,
	    	                );
	    	                    
	    	                $this->appStructure['modules'][$data['module']]['controllers'][$data['controller']]['className']
	    	                    = $this->getClassNameFromControllerFile($data['controllerPath']);
	    	                $this->appStructure['modules'][$data['module']]['controllers'][$data['controller']]['actions']
	    	                    = $this->getControllerActions($data['controllerPath']);
	    	                }
	    	            }
	    	        }
	    	    }
	    	break;
	    	    
	    default:
	        // change working directory to $this->modulePath . DIRECTORY_SEPARATOR . $module
	        $moduleSpecificDirectory = $this->modulePath . DIRECTORY_SEPARATOR . $module;
	        chdir($moduleSpecificDirectory);
	    	    
	        // search for 'controllers' folder
	        $controllersfolder = glob('controllers');
	    	    
	        // if we have a result for the controllers folder get the controllers
	        if (is_array($controllersfolder)) {
	            chdir($moduleSpecificDirectory . DIRECTORY_SEPARATOR . $controllersfolder[0]);
	    	        
	            $controllers = glob('*Controller.php');
	    	        
	            if (is_array($controllers)) {
	                foreach($controllers as $controller) {
	                    if (is_string($controller)) {
	                        $tempArray = explode('Controller.php', $controller);
	                        $con = $tempArray[0];
	                        $controllerFullName = strstr($controller, '.php', true);
	                        $data = array(
	                            'module' => $module,
	                            'controller' => $con,
	                            'controllerPath' => getcwd() . DIRECTORY_SEPARATOR . $controller,
	                            'controllerFullName' => $controllerFullName,
	                        );
	    	                    
	                        $this->appStructure['modules'][$data['module']]['controllers'][$data['controller']]['className']
	                            = $this->getClassNameFromControllerFile($data['controllerPath']);
	                        
	                        $this->appStructure['modules'][$data['module']]['controllers'][$data['controller']]['actions']
	                            = $this->getControllerActions($data['controllerPath']);
	                    }
	                }
	            }
	        }	    
        }
    }
    
    /**
     * Returns a list of accessible module names sorted alphabetically from A-Z.
     * 
     * @return array $sortedModuleList
     * @throws InvalidArgumentException
     */
     public function buildModulesList()
     {
         $modulesList = array();
         
         if (is_string($this->modulePath) && is_dir($this->modulePath) && is_readable($this->modulePath)) {
             chdir($this->modulePath);
             $modulesList = glob('*');
         }
         
         array_push($modulesList, $this->defaultApplicationModuleName);
         asort($modulesList);
         $sortedModuleList = array_values($modulesList);
         
         return $sortedModuleList;
     }
     
    /**
     * Sets the value for the module path property.
     * 
     * If using the standard Zend Framework application directory structure generated by
     * Zend_Tool this will be equivalent to: 'full-path-to-project/application/modules'
     * 
     * @param string $modulePath
     * @return Dkcwd_AppIntrospector_AppIntrospector $this
     * @throws InvalidArgumentException
     */
     public function setModulePath($modulePath)
     {
         if (is_string($modulePath) && is_dir($modulePath) && is_readable($modulePath)) {
             $this->modulePath = $modulePath;
         } else {
             throw new InvalidArgumentException('An invalid module path was specified');
         }
         
         return $this;
     }
     
    /**
     * Sets the value for the application path property.
     * 
     * If using the standard Zend Framework application directory structure generated by
     * Zend_Tool this will be equivalent to: 'full-path-to-project/application'
     * 
     * @param string $applicationPath
     * @return Dkcwd_AppIntrospector_AppIntrospector $this
     * @throws InvalidArgumentException
     */
     public function setApplicationPath($applicationPath) {
         if (is_string($applicationPath) && is_dir($applicationPath) && is_readable($applicationPath)) {
             $this->applicationPath = $applicationPath;
         } else {
             throw new InvalidArgumentException('An invalid application path was specified');
         }
         
         return $this;
     }	
}
