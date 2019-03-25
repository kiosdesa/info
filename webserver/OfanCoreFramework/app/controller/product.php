<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Product
*/
//import_service('Product');
Imports::name('Product')->from('service');

class Product extends ProductServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}



	/** 
	 * Method look untuk detail product
	 */
	public static function detail($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$filterProduct = parent::detailProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $filterProduct);
	}
	


	/** 
	 * Method look untuk filter product list
	 */
	public static function lookup($fieldForm=null, $filterType=null)
	{
		//if(is_null($fieldForm)) return false;
		//if(is_null($filterType)) return false;
		$fieldForm['cluster'] = self::getSection();
		$filterProduct = parent::filterProduct($fieldForm, $filterType);
		return parent::_return(__CLASS__, __FUNCTION__, $filterProduct);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function forms($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$forms = parent::fieldProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $forms);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$insertProductDatabase = parent::insertProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $insertProductDatabase);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function change($fieldForm=null, $id=null)
	{
		if(is_null($fieldForm)) return false;
		if(is_null($id)) return false;
		$fieldForm['cluster'] = self::getSection();
		$change = parent::updateProduct($fieldForm, $id);
		return parent::_return(__CLASS__, __FUNCTION__, $change);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function remove($id=null)
	{
		if(is_null($id)) return false;
		$fieldForm['cluster'] = self::getSection(); 
		$fieldForm['id'] = $id;
		$deleteProductDatabase = parent::deleteProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $deleteProductDatabase);
	}


	/** 
	 * Method Insert Product untuk memasukan produk baru 
	 */
	public static function hide($id=null)
	{
		if(is_null($id)) return false;
		$fieldForm['cluster'] = self::getSection(); 
		$fieldForm['id'] = $id;
		$disableProductDB = parent::disableProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $disableProductDB);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function favorite($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$fav = parent::favoritingProduct($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $fav);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function favoritelist()
	{
		$fieldForm = array('cluster'=>self::getSection());
		$list = parent::favoriteList($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $list);
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function favoritesearch($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$search = parent::favoriteSearch($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $search);
	}
}
?>