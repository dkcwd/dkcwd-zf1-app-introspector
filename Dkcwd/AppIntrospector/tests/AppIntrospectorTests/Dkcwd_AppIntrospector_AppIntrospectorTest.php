<?php
/** 
 * @link http://github.com/dkcwd/dkcwd-zf1-app-introspector for the canonical source repository
 * @author Dave Clark dave@dkcwd.com.au 
 * @copyright Dave Clark 2013
 * @license http://opensource.org/licenses/mit-license.php
 */
require_once 'library\Dkcwd\AppIntrospector\AppIntrospector.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'MockController.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Dkcwd_AppIntrospector_AppIntrospector test case.
 */
class Dkcwd_AppIntrospector_AppIntrospectorTest extends PHPUnit_Framework_TestCase 
{    
    
	/**
	 * @var Dkcwd_AppIntrospector_AppIntrospector
	 */
	private $Dkcwd_AppIntrospector_AppIntrospector;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
				
		defined('DS') || define('DS', DIRECTORY_SEPARATOR);
		
		// instantiate AppIntrospector object - supply a valid application path and module path
		// first define a valid application path
		$appPath = realpath(__DIR__ . DS . 'mock-application' . DS . 'application');
		// then define a valid module path
		$modPath = $appPath . DS . 'modules';
		
		$this->Dkcwd_AppIntrospector_AppIntrospector = new Dkcwd_AppIntrospector_AppIntrospector(
		    $appPath,
		    $modPath
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated Dkcwd_AppIntrospector_AppIntrospectorTest::tearDown()

		$this->Dkcwd_AppIntrospector_AppIntrospector = null;

		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {}

	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->__construct()
	 */
	public function testConstructorWorksAsExpectedWhenValidApplicationPathSupplied() {		
	    // The only required param for instantiation is the application path.
	    // The methods used in __construct() are setApplicationPath($applicationPath)
	    // and setModulePath($modulePath) which are tested separately in this suite.
	    $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->__construct(
                realpath(__DIR__ . DS . 'mock-application' . DS . 'application')
	    );
	    
	    $this->assertInstanceOf('Dkcwd_AppIntrospector_AppIntrospector', $actual);
	}
	
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->__construct()
	 */
	public function testConstructorThrowsExceptionWhenInvalidModulePathSupplied() 
	{	    
	    try {
	        $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->__construct(
	            realpath(__DIR__ . DS . 'mock-application' . DS . 'application'),	            
	            array() // Method expects param to be a string and a valid readable directory
	        );
	    } catch (Exception $e) {
	        $actual = $e;
	    }
	     
	    $this->assertInstanceOf('InvalidArgumentException', $actual);	    
	}
		
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->getClassNameFromControllerFile()
	 */
	public function testGetClassNameFromControllerFileThrowsExceptionWithInvalidController()
	{
	    try {
	        $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->getClassNameFromControllerFile(
	            array() // Method expects param to be a string and a valid readable directory
	        );
	    } catch (Exception $e) {
	        $actual = $e;
	    }
	     
	    $this->assertInstanceOf('InvalidArgumentException', $actual);
	}
	
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->getControllerActions()
	 */
	public function testGetControllerActionsThrowsExceptionWithInvalidController()
	{
	    try {
	        $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->getControllerActions(
	            array() // Method expects param to be a string and a valid readable directory
	        );
	    } catch (Exception $e) {
	        $actual = $e;
	    }
	    
	    $this->assertInstanceOf('InvalidArgumentException', $actual);
	}    

	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->getControllerActions()
	 */
	public function testGetControllerActionsWorksAsExpectedWithValidController()
	{	    
	    // The expected behaviour is for an array to be returned which contains public methods only.
	    // To illustrate expected behaviour under controlled circumstances, the mock application 
	    // features controller classes which extend a mock controller class.	    
	    // Rather than extending Zend_Controller_Action and creating an array of expected data which 
	    // might change over time, the mock controller contains 1 public method, 1 protected method
	    // and 1 private method.  
	    // The controllers in the mock application inherit the public and protected method from the
	    // mock controller and contain one additional public method defined as 'indexAction()'.   

	    // the stub we include below returns an array containing only the 2 expected public methods.
	    $expected = include $this->Dkcwd_AppIntrospector_AppIntrospector->applicationPath . DS . 'data' . DS . 'getControllerActionsStub.php';

	    $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->getControllerActions(
	            $this->Dkcwd_AppIntrospector_AppIntrospector->modulePath . DS . 'cms' . DS . 'controllers' . DS . 'IndexController.php'
	    );	     
	    
	    $this->assertSame($actual, $expected);		
	}

	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->buildAppStructureArray()
	 */
	public function testBuildAppStructureArray() 
	{		
	    $actual = (array) $this->Dkcwd_AppIntrospector_AppIntrospector->setApplicationPath(
	            realpath(__DIR__ . DS . 'mock-application' . DS . 'application')
	        )->buildAppStructureArray();
	    
	    $expected = include $this->Dkcwd_AppIntrospector_AppIntrospector->applicationPath . DS . 'data' . DS . 'buildAppStructureArrayStub.php';    
	    
	    $this->assertSame($actual, $expected);
	}

	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->setModulePath()
	 */
	public function testSetModulePathWorksAsExpectedWithValidDirectory() 
	{		
	        // set the path to the current directory this file resides in 
		$this->Dkcwd_AppIntrospector_AppIntrospector->setModulePath(
		    realpath(__DIR__)
		);
		
		// test whether the module path has been set as expected
		$this->assertSame(
		    realpath(__DIR__),
		    $this->Dkcwd_AppIntrospector_AppIntrospector->modulePath
		);
	}
	
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->setModulePath()
	 */
	public function testSetModulePathThrowsExceptionWithInvalidDirectory()
	{
	    try {
	        $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->setModulePath(	            
	            array() // Method expects param to be a string and a valid directory
	        );
	    } catch (Exception $e) {
	        $actual = $e;
	    }
	    
	    $this->assertInstanceOf('InvalidArgumentException', $actual);
	}
	
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->setApplicationPath()
	 */
	public function testSetApplicationPathWorksAsExpectedWithValidDirectory()
	{
	    $this->Dkcwd_AppIntrospector_AppIntrospector->setApplicationPath(
	            $this->Dkcwd_AppIntrospector_AppIntrospector->applicationPath
	    );
	    
	    $this->assertSame(
	            $this->Dkcwd_AppIntrospector_AppIntrospector->applicationPath,
	            $this->Dkcwd_AppIntrospector_AppIntrospector->applicationPath
	    );
	}
		
	/**
	 * Tests Dkcwd_AppIntrospector_AppIntrospector->setApplicationPath()
	 */
	public function testSetApplicationPathThrowsExceptionWithInvalidDirectory()
	{   
	    try {
	        $actual = $this->Dkcwd_AppIntrospector_AppIntrospector->setApplicationPath(	                
	                array() // method expects param to be both a string and a valid directory
	        );
	    } catch (Exception $e) {
	        $actual = $e;
	    }
	     
	    $this->assertInstanceOf('InvalidArgumentException', $actual);	    
	}	

}
