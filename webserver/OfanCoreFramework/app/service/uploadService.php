<?php if(!defined('_thisFileDIR')) header('Location:..');

class UploadServices extends OfanCoreFramework
{
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_cdnIcon;
	private static $_cdnProduct;
	private static $_cdnSeller;
	private static $_thisComponentIonic;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('jsonHandler', 'validateHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
	}

	protected static function productUpload($param)
	{
        self::load($param);
        return $param;
    }

	protected static function profileUpload($param)
	{
        self::load($param);
        return $param;
    }

	protected static function avatarUpload($param)
	{
        self::load($param);
        return $param;
    }

	protected static function bannerUpload($param)
	{
        self::load($param);
        return $param;
    }
}
?>