<?php 
/**
 * @author OFAN
 * @copyright OFAN WEB DEVELOPER
 * @since 2014 - 2018
 * @return require_once() config.php
 */
session_name('OFANCORESSID');
//session_set_cookie_params((24*60*60));
session_start();
define('_thisDIR', dirname(__DIR__).'/');
define('_thisFileDIR', dirname(__FILE__).'/');
define('_thisManifestDIR', _thisFileDIR.'manifest/');
define('_thisApp', _thisFileDIR.'app/');
define('_thisSystem', _thisFileDIR.'system/');
define('_thisPackage', _thisFileDIR.'package/');
require_once(_thisManifestDIR.'config.php');
?>