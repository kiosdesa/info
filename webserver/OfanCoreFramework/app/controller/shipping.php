<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Shipping
*/
Imports::name('Shipping')->from('service');

class Shipping extends ShippingServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function timeout($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$counts = parent::checkTimeoutShipping($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $counts);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function calculate($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$counts = parent::shippingCalculate($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $counts);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function lists($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$lists = parent::listShipping($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $lists);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function city($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$city = parent::cityShipping($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $city);
	}
}
?>