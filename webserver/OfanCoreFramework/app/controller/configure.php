<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Cart Order
*/
//import_service('Cart');
Imports::name('Config')->from('service');

class Configure extends ConfigServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}


	public static function company($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$company = parent::companyOptions($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $company);
	}


	/** 
	 * Method Insert Product untuk payment list 
	 */
	public static function payment($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$payment = parent::paymentList($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $payment);
	}


	/** 
	 * Method Insert Product untuk shipping list 
	 */
	public static function shipping($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$shipping = parent::shippingList($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $shipping);
	}


	/** 
	 * Method look untuk orderstatus list
	 */
	public static function orderstatus($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$order = parent::statusOrderList($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $order);
	}


	/** 
	 * Method look untuk orderstatus list
	 */
	public static function searchcategory($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$search = parent::searchCategory($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $search);
	}


	/** 
	 * Method Insert Product untuk add configurasi baru 
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$add = parent::addConfig($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $add);
	}


	/** 
	 * Method Insert Product untuk update configurasi baru 
	 */
	public static function update($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$update = parent::updateConfig($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $update);
	}


	/** 
	 * Method Insert Product untuk remove configurasi baru 
	 */
	public static function remove($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$remove = parent::deleteConfig($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $remove);
	}
}
?>