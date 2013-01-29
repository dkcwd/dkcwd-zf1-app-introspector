<?php 
return array(
    'modules' => array(
	    'cms' => array(
		    'controllers' => array(
		        'Another' => array(
                    'className' => 'Cms_AnotherController',
                    'actions' => array(
                        'indexAction',
                        '__construct'                        
                    ),
                ),
		        'Index' => array(
		            'className' => 'Cms_IndexController',
		            'actions' => array(
		                'indexAction',
                        '__construct'
	                ),
	            ),
            ),
        ),
        'default' => array(
            'controllers' => array(
                'Another' => array(
                    'className' => 'AnotherController',
                    'actions' => array(
                        'indexAction',
                        '__construct'
                    ),
                ),
                'Index' => array(
                    'className' => 'IndexController',
                    'actions' => array(
                        'indexAction',
                        '__construct'
                    ),
                ),
            ),
        ),            
        'user' => array(
            'controllers' => array(
                'Another' => array(
                    'className' => 'User_AnotherController',
                    'actions' => array(
                        'indexAction',
                        '__construct'
                    ),
                ),
                'Index' => array(
                    'className' => 'User_IndexController',
                    'actions' => array(
                        'indexAction',
                        '__construct'
                    ),
                ),
            ),
        ),    	        
    ),		
);