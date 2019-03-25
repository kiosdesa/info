<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * @author OFAN
 * @copyright GNU & GPL License
 * @since 2018
 * @version 1.0
 *
 * File konfigurasi untuk me-load semua informasi awal yang di butuhkan oleh sistem
 *
 * File yang di load adalah:
 *		1 .config (format json) suplai data statis domain, server, kredensial dll
 *		2 Language Object (format json dalam class) suplai data statis Bahasa
 */
date_default_timezone_set("Asia/Jakarta");

define('_thisController','controller');
define('_thisService','service');
define('_thisLibrary','library');
define('_thisModular','modular');
define('_thisSnippet','snippet');
define('_thisLang','i18n');
define('_thisLangDIR', _thisApp._thisLang.'/');
define('_thisControllerDIR', _thisApp._thisController.'/');
define('_thisServiceDIR', _thisApp._thisService.'/');
define('_thisLibraryDIR', _thisSystem._thisLibrary.'/');
define('_thisModularDIR', _thisSystem._thisModular.'/');
define('_thisSnippetDIR', _thisSystem._thisSnippet.'/');
define('_thisDateYMD', date('Y-m-d'));
define('_userAgent', $_SERVER['HTTP_USER_AGENT']);
//var_dump(_thisDIR, _thisFileDIR, _thisServiceDIR);

/**
 * Load modular file
 */
require _thisModularDIR.'modularImport.php';
require _thisModularDIR.'modularConfig.php';
require _thisModularDIR.'modularLang.php';
/**
 * Mengambil data pengaturan web
 */
$Configurate = new Configurate(_thisManifestDIR.'.config');
define('_configDB', serialize($Configurate->databaseConfig()));
define('_configUSER', serialize($Configurate->userConfig()));
define('_ipUSER', $Configurate->getClientIP());
define('_thisAllowRequest', serialize($Configurate->allowAjaxMethod()));
/**
 * Variable Config
 */
$_globConfig = $Configurate->globConfig();
$_profileConfig = $Configurate->profileConfig();
/**
 * Mengambil REQUEST parameter URL 'lang' pada $Configurate->i18n()
 * Mengambil text translate bahasa sistem pada $Language->translate() kemudian dibuat global variable
 */
$Language = new Language($Configurate->i18n());
define('_configLANG', serialize($Language->translate()));
/**
 * Membuat Variable untuk informasi URL CDN service
 */
$_cdnDomain = $Configurate->cdnDomainConfig();
$_cdnDirectory = $Configurate->cdnDirectoryConfig();
/**
 * Menentukan nilai define untuk kebutuhan Global variable statis di berbagai library
 */
define('_eMailing', $_globConfig['mailing']);
define('_smsGateway', $_globConfig['smsgateway']);
define('_cacheble', $_globConfig['cacheConfig']['cacheble']);
define('_cacheTime', $_globConfig['cacheConfig']['cacheTime']);
define('_thisDomain', $Configurate->thisDomainConfig());
define('_apiDomain', $Configurate->apiDomainConfig());
define('_cdnDomain', $_cdnDomain);
define('_thisAllowDomain', join(' ', array(_thisDomain, _apiDomain, (_thisDomain.'/othersite'))));
define('_cdnDirectoryUser', $_cdnDomain.$_cdnDirectory['user']);
define('_cdnDirectorySeller', $_cdnDomain.$_cdnDirectory['seller']);
define('_cdnDirectoryProduct', $_cdnDomain.$_cdnDirectory['product']);
define('_cdnDirectoryBanner', $_cdnDomain.$_cdnDirectory['banner']);
define('_cdnDirectoryIcon', $_cdnDomain.$_cdnDirectory['icon']);
define('_this_X_Powered', $_profileConfig['x_powered']);
define('_thisCOPYRIGHT', '© '.$_profileConfig['brand'].($_profileConfig['live_start'] == date('Y') ? ' - '.date('Y') : ' | '.$_profileConfig['live_start'].'-'.date('Y')));
define('_thisBrand', $_profileConfig['brand']);
/**
 * Load modular method file
 */
require _thisModularDIR.'modularLoad.php';
?>