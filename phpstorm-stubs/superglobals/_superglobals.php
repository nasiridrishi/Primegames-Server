<?php
/**
 * @xglobal $GLOBALS array
 * Contains a reference to every variable which is currently available within the global scope of the script.
 *   The keys of this array are the names of the global variables.
 *   $GLOBALS has existed since PHP 3.
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$GLOBALS = array();

/**
 * @xglobal $_ENV array
 * @xglobal $HTTP_ENV_VARS array
 *
 * Variables provided to the script via the environment.
 * Analogous to the old $HTTP_ENV_VARS array (which is still available, but deprecated).
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">
 * https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$_ENV = array();

/**
 * @xglobal $_SERVER array
 * @xglobal $HTTP_SERVER_VARS array
 *
 * Variables set by the web server or otherwise directly related to the execution environment of the current script.
 * Analogous to the old $HTTP_SERVER_VARS array (which is still available, but deprecated).
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">
 * https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$_SERVER = array();
/**
 * @deprecated 4.1.0
 */
$HTTP_SERVER_VARS = array();

$_SERVER['PHP_SELF'] = '';
$_SERVER['argv'] = '';
$_SERVER['argc'] = '';
$_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_SOFTWARE'] = '';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_TIME'] = '';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['DOCUMENT_ROOT'] = '';
$_SERVER['HTTP_ACCEPT'] = '';
$_SERVER['HTTP_ACCEPT_CHARSET'] = 'iso-8859-1,*,utf-8';
$_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
$_SERVER['HTTP_CONNECTION'] = 'Keep-Alive';
$_SERVER['HTTP_HOST'] = '';
$_SERVER['HTTP_REFERER'] = '';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.5 [en] (X11; U; Linux 2.2.9 i586).';
$_SERVER['HTTPS'] = '';
$_SERVER['REMOTE_ADDR'] = '';
$_SERVER['REMOTE_HOST'] = '';
$_SERVER['REMOTE_PORT'] = '';
$_SERVER['SCRIPT_FILENAME'] = '';
$_SERVER['SERVER_ADMIN'] = '';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_SIGNATURE'] = '';
$_SERVER['PATH_TRANSLATED'] = '';
$_SERVER['SCRIPT_NAME'] = '';
$_SERVER['REQUEST_URI'] = '/index.html';
$_SERVER['PHP_AUTH_DIGEST'] = '';
$_SERVER['PHP_AUTH_USER'] = '';
$_SERVER['PHP_AUTH_PW'] = '';
$_SERVER['AUTH_TYPE'] = '';
$_SERVER['PATH_INFO'] = '';
$_SERVER['ORIG_PATH_INFO'] = '';

/**
 * @xglobal $argc int
 *
 * The number of arguments passed to script
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">
 * https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$argc = 0;

/**
 *  @xglobal $argv array
 *
 * Array of arguments passed to script
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">
 * https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$argv = array();

/**
 * @xglobal $php_errormsg string
 *  The previous error message
 *
 * <p><a href="https://secure.php.net/manual/en/reserved.variables.php">
 * https://secure.php.net/manual/en/reserved.variables.php</a>
 */
$php_errormsg = '';
