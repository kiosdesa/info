<?php if(!defined('_thisFileDIR')) header('Location:..');

class ShippingServices extends OfanCoreFramework
{

	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;
	private static $_cdnIcon;
	private static $_cdnProduct;
	private static $_cdnSeller;
	private static $_thisComponentIonic;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$cluster = 'config';
		/**
		 * Untuk mengisi nilai boolean pada where (filter)
		 * di parameter isikan nilai 1 atau 'true' untuk true
		 * di parameter isikan nilai kosong atau '' untuk false
		 */
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'shippingHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'shipping';
		self::$_thisComponentIonic = 'ShippingPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
	}


	protected static function checkTimeoutShipping($param)
	{
		self::load($param);
		$type = isset($param['type']) ? $param['type'] : null;
		$from = isset($param['from']) ? $param['from'] : 'now';
		$to = isset($param['to']) ? $param['to'] : null;
		$check = parent::_handler('shipping', array('lang'=>self::$_lang))->fromdate($from)->todate($to)->checktimeout($type);
		return $check;
	}


	protected static function cityShipping($param)
	{
		self::load($param);
		$getCity = parent::_handler('shipping', $param)->get('city');
		if(!$getCity) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		return array(
			'approve'=>true, 'message'=>self::$_lang['global']['success'], 
			'data'=>$getCity['data'], 'next'=>$getCity['next_available'],
			'title'=>self::$_lang['geolocation']['district']
		);
	}
    


	protected static function listShipping($param)
	{
		self::load($param);
		$switch = isset($param['switch']) ? $param['switch'] : 'rate';
		$getTariff = parent::_handler('shipping', $param)->get($switch);
		//return $getTariff;
		if($getTariff)
		{
			return array(
				'approve'=>true,
				'server'=>array('icon'=>self::$_cdnIcon),
				'data'=>$getTariff['attributes'][0]['products']
			);
		}
		else
		{
			
			return array(
				'approve'=>false,
				'message'=>self::$_lang['exist']['false']
			);
		}
	}


	protected static function insertShipping($param)
	{
		self::load($param);

		$insertShipping = null;
		if($insertShipping)
		{}
		else
		{}
	}


	protected static function updateShipping($param)
	{
		self::load($param);

		$updateShipping = null;

		if($updateShipping)
		{}
		else
		{}
	}
}
?>