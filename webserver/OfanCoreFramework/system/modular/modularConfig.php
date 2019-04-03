<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class Object Configurate
 * Adalah Core level 1 untuk menentukan Variable Konfigurasi pada system Web Service
 * 
 * Nama System: OfanCoreFramework
 * Nama Class: Configurate
 * Constructor @param $configJSON default value NULL
 * @author OFAN
 * @since 2018
 * @version 1.0
 * @copyright GNU & GPL license
 */
class Configurate
{
	private $_siteConfig;
	private $_databaseConfig;
	private $_globConfig;
	private $_userConfig;
	private $_profileConfig;

	function __construct($configJSON=null)
	{
		if(is_null($configJSON)) return false;

		// Load file Config JSON (./config/.config)
		$getData = @file_get_contents($configJSON);
		if(!$getData) return false;

		$decodeData = json_decode($getData, true);
		$this->_siteConfig = $decodeData;
		$this->_globConfig = $decodeData['global_config'];
		$this->_databaseConfig = $decodeData['db_config'];
		$this->_userConfig = $decodeData['user_config'];
		$this->_profileConfig = $decodeData['profiles'];
	}

	public function globConfig()
	{
		return $this->_globConfig;
	}

	public function i18n()
	{
		// Variable $lang adalah bentuk REQUEST parameter pada URL jika kosong pake default (di file .config)
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->_globConfig['i18n']['default'];
		return $lang;
	}

	public function siteConfig()
	{
		return $this->_siteConfig;
	}

	public function databaseConfig()
	{
		return $this->_databaseConfig;
	}

	public function userConfig()
	{
		return $this->_userConfig;
	}

	public function profileConfig()
	{
		return $this->_profileConfig;
	}

	public function thisDomainConfig()
	{
		return str_replace('//', '/', join('/',$this->_globConfig['root']));
	}

	public function apiDomainConfig()
	{
		return str_replace('//', '/', join('/',$this->_globConfig['api']));
	}

	public function cdnDomainConfig()
	{
		$cdnServer = $this->_globConfig['cdn']['server'];
		return ($cdnServer == false ? '' : join('', $cdnServer).'/');
	}

	public function cdnDirectoryConfig()
	{
		return $this->_globConfig['cdn']['directory'];
	}

	public function allowAjaxMethod()
	{
		return $this->_globConfig['xhttp']['allow'];
	}

	public function getClientIP()
	{
	    $ipaddress = '';
	    if(isset($_SERVER['HTTP_CLIENT_IP']))
	    {
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	    {
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    elseif(isset($_SERVER['HTTP_X_FORWARDED']))
	    {
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    }
	    elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))
	    {
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    }
	    elseif(isset($_SERVER['HTTP_FORWARDED']))
	    {
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    }
	    elseif(isset($_SERVER['REMOTE_ADDR']))
	    {
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    }
	    else
	    {
	        $ipaddress = 'UNKNOWN';
	    }
	 
	    return $ipaddress;
	}
}
?>