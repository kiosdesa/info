<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Order
*/
Imports::name('Order')->from('service');

class Orders extends OrderServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}

	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function counts($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$counts = parent::countOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $counts);
	}

	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function lists($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$lists = parent::listOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $lists);
	}

	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function checkout($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$lists = parent::listOrderFromCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $lists);
	}

	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function email($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$email = parent::emailOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $email);
	}

	/** 
	 * Method look untuk detail product
	 */
	public static function detail($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$detail = parent::detailOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $detail);
	}

	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$add = parent::insertOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $add);
	}

	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function update($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$update = parent::updateOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $update);
	}

	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function remove($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$remove = parent::deleteOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $remove);
	}

	/** 
	 * Method Insert feedback Order
	 */
	public static function feedback($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$feedback = parent::feedbackOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $feedback);
	}

	/** 
	 * Method Insert newcomplaint
	 */
	public static function newcomplaint($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$newcomplaint = parent::newComplaintOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $newcomplaint);
	}

	/** 
	 * Method Insert newcomplaint
	 */
	public static function listcomplaint($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$listcomplaint = parent::listComplaintOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $listcomplaint);
	}
}
?>