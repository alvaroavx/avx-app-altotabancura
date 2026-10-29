<?php
if(session_id() == ''){session_start();}
/**********************************************************************************************************************/
if(isset($_SERVER['APPL_PHYSICAL_PATH'])){
	define('Root_Fisica', $_SERVER['APPL_PHYSICAL_PATH']);
	define('WebServer','IIS');
}
else{
	$DocumentRoot = isset($_SERVER['CONTEXT_DOCUMENT_ROOT']) && $_SERVER['CONTEXT_DOCUMENT_ROOT'] !== ''
		? $_SERVER['CONTEXT_DOCUMENT_ROOT']
		: $_SERVER['DOCUMENT_ROOT'];
	define('Root_Fisica', rtrim($DocumentRoot, '/\\').'/');
	define('WebServer','APACHE');
}
define('Root_Valk','valk/');
define('File_Constants',constant('Root_Valk').'constants.php');
define('File_Tools', constant('Root_Valk').'tools.php');
/**********************************************************************************************************************/
require_once(constant('Root_Fisica').constant('File_Constants'));
require_once(constant('Root_Fisica').constant('File_Tools'));
Params($Constants);
require_once(constant('Root_Fisica').constant('File_Preloader'));
Params($Manifest);
Params($Var);
Params($Keys);
require_once(constant('Root_Fisica').constant('File_Wiss'));

require_once(constant('Root_Fisica').constant('File_Valk'));
/**********************************************************************************************************************/
date_default_timezone_set(constant("TimeZone"));
error_reporting((constant("Entorno_Error")) ? E_ALL : 0);
ini_set('display_errors', constant("Entorno_Error"));
/**********************************************************************************************************************/
$Wiss = new Wiss();
