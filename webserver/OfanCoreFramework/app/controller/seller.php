<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Seller
*/
//import_service('Seller');
Imports::name('Seller')->from('service');

class Seller extends SellerServices
{
	/** 
	 * fungsi get untuk diakses publik 
	 */
	public static function get($section=null, $cluster=null, $filterType=null, $filter=null, $lookup=null, $order=null, $limit=null)
	{
		$parameters = array();
		if(!is_null($cluster)) $parameters['cluster'] = $cluster;
		if(!is_null($filterType)) $parameters['filterType'] = $filterType;
		if(!is_null($filter)) $parameters['filter'] = $filter;
		if(!is_null($lookup)) $parameters['lookup'] = strtolower($lookup);
		if(!is_null($order)) $parameters['order'] = $order;
		if(!is_null($limit)) $parameters['limit'] = $limit;

		if($filterType === 'search')
		{
			$sellerDatabase = parent::searchSeller($parameters);
		}
		else
		{
			$sellerDatabase = false;
		}

		if($sellerDatabase)
		{
			$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = _proposeJsonHandler(array('db'=>$sellerDatabase, 'named'=>($section.$filterType), 'section'=>$section.ucfirst($filterType)));
			return parent::reformatSellerArrayDB($jsonHandler);
		}
		else
		{
			return false;
		}
	}


	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}


	/** 
	 * Method get detail Seller untuk mendapatkan info Toko sendiri 
	 */
	public static function detail($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$detail = parent::detailSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $detail);
	}


	/** 
	 * Method get detail Seller untuk mendapatkan info Toko sendiri 
	 */
	public static function kios($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::cardKios($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}



	/** 
	 * Method Insert Seller untuk memasukan Toko baru 
	 */
	public static function addnew($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$new = parent::insertSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $new);
	}


	/** 
	 * Method Modify Seller untuk mengubah data toko
	 */
	public static function modify($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::modifySeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}


	/** 
	 * Method Modify Seller untuk mengubah data toko
	 */
	public static function self($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$self = parent::fieldSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $self);
	}

	
	/** 
	 * Method Remove Seller untuk menghapus toko
	 */
	public static function remove($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::removeSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}


	/** 
	 * Method Suspend User untuk menghapus Users
	 */
	public static function closed($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::closedSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}



	/** 
	 * Method Subscriber User untuk menghapus Users
	 */
	public static function followers($fieldForm=null)
	{
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::followerSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}



	/** 
	 * Method Suspend User untuk menghapus Users
	 */
	public static function following($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$favoritingDB = parent::followingSeller($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $favoritingDB);
	}
}
?>