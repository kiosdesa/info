<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Cart Order
*/
Imports::name('Cart')->from('service');

class Carts extends CartServices
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
		$counts = parent::countCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $counts);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function lists($fieldForm)
	{
		$fieldForm['cluster'] = self::getSection();
		$lists = parent::listCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $lists);
	}


	/** 
	 * Method look untuk detail product
	 */
	public static function detail($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$detail = parent::detailCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $detail);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$add = parent::insertCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $add);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function update($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$update = parent::updateCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $update);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function remove($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$remove = parent::deleteCart($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $remove);
	}
}
?>