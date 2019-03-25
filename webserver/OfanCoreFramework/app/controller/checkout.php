<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Order
*/
Imports::name('Checkout')->from('service');

class Checkout extends CheckoutServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function get($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$counts = parent::get($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $counts);
	}
}
?>