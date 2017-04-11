<?php

error_reporting(E_ALL);

ini_set("display_errors", TRUE);

define("WS_BASE_DIR", '/vhosts/webservices-wrapper.local/');

define("WS_STREAM_OUT_PATH", WS_BASE_DIR . 'out/'); // hardcoded

define("WS_USER_CONFIGS",WS_BASE_DIR . "ws-user.configs.php"); // contains usernames, public and private keys

/*  WS_DEBUG_LEVEL:

	defines what debug/log data in stored and when admin emails are sent: 
	0: db logging on, file logging off, ws api admin email only on critical errors
	1: db logging on, file logging on, ws api admin email only on critical errors 
	2: db logging on, file logging on, ws api admin email sent for every request processed 

*/

// Requests from the same server don't have a HTTP_ORIGIN header, so define it:
if (!isset($_SERVER['HTTP_ORIGIN'])){

	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

if(!file_exists(WS_USER_CONFIGS)){
	
	echo json_encode(array("Fatal Start-up Error", "Fatal Error: webservices user configurations could not be loaded"));
	
	exit;	
}

if(!file_exists(WS_STREAM_OUT_PATH)){

	echo json_encode(array("Fatal Start-up Error", "Fatal Error: webservices gz output path does not exist"));

	exit;
}

if(!is_writable(WS_STREAM_OUT_PATH)){

	echo json_encode(array("Fatal Start-up Error", "Fatal Error: webservices gz output path is not writeable"));

	exit;
}

// reference to the global configs:
require WS_USER_CONFIGS; // in the root vhosts dir

// reference the ftg api object:
require __DIR__ . '/classes/webservices.class.php';

if(isset($_GET['request'])){ 

	//Standalone SACAP Webservices API:

	$API = new SACAP_API($_GET['request'], $_SERVER['HTTP_ORIGIN'],  $WS_UserConfigs); 

	$API->ProcessRequest();
}