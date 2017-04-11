<?php

// debug line must be commented out for production:
error_reporting(E_ALL);
ini_set("display_errors",true);

/* -------------------------------------------------------------------------------------------------------------------
	Note that this file must not be installed within document root, one level above should be fine
	
	WS_DEBUG_LEVEL:

	Defines what debug/log data in stored and when admin emails are sent: 
	0: db logging on, file logging off, ws api admin email only on critical errors
	1: db logging on, file logging on, ws api admin email only on critical errors 
	2: db logging on, file logging on, ws api admin email sent for every request processed 

*/

// Give this standalone instance a name:
define("INSTANCE_NAME","WS-SACAP-001");

define("INSTANCE_DESCRIPTION","SACAP-WS-ENGINE");

// define the default logging db and access credentials:
define("API_DB_HOST","localhost");
define("API_DB_SCHEMA","ws_logs_db");
define("API_DB_USER","wslogs_usr");
define("API_DB_PWD","eTcLPquz948");
define("API_DB_TABLENAME","ws_logs");

$WS_UserConfigs = [ // array keys are the usernames:

// -------------------------------------------------------------------------------------------------------------------
// USER - SACAP - STUDENTBASE

    "SACAP-EDUDEX" => [
        
        // ENABLE/DISABLE ACCESS:
        "ACCESS_ENABLED" => true, 	

        "CLIENTNAME" => "SACAP EduDex Reporting User",
        "PRIMARY_FILESTORE_PATH" => false,
        "SECONDARY_FILESTORE_PATHS" => [], // any other locations to store files to
        "FILENAME_PREFIX" => false,

        // AUTHENTIFICATION PARAMETERS:
        "AUTH_USERNAME" => "SACAP-EDUDEX", // same as parent array key
        "AUTH_PUBLIC_KEY" => "nhSnG34jMzQvBTmv", // must be 16 chars... only alphanumeric, can be any random string
        "AUTH_PRIVATE_KEY" => "ZjExOGIxYzAxZDQ0MmRmZjg2ODZkY2Y1OTc1M2FiZmQ4Yzg5NjkxM2QwZTk1ZWQ2N2FmNjEzZmNhYzM1NmQxNg==", // a sha256 hash of the AUTH_SALT_STRING, which is then based 64 encoded
        "AUTH_SALT_STRING" => "We keep our classes deliberately small to encourage active participation and maximize your learning. It’s a core feature of our educational philosophy.",
        "AUTH_ALLOW_METHODS" => [ // name of the primary method function call in /classes/webservices.class.php
            "api_test.get",
            "api_test.post",
			"morne_sample_method.get"
        ],

        // REQUEST LOGGING DB CONFIGS:
        "LOG_DB_HOST" => "localhost",
        "LOG_DB_SCHEMA" => "wslogs_db",
        "LOG_DB_USERNAME" => "wslogs_usr",
        "LOG_DB_PASSWORD" => "eTcLPquz948",
        "LOG_DB_TABLENAME" => "ws_logs_onlapps", // don't forget to create new db table for new user(manual) 

        // DEBUG LEVEL:
        "WS_DEBUG_LEVEL" => 1, // see above for level description, overrides default setting of 2

        "ADMIN_EMAIL_RECIPIENTS" => array(
            "bretton@sacap.edu.za",
			"angela@sacap.edu.za",
			"morne@sacap.edu.za"
        )

    ],
	
	// -------------------------------------------------------------------------------------------------------------------
	
];