<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk Bumdesa
 */
import_service('Bumdesa');

class Bumdesa extends BumdesaServices
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
		
		if($filterType === 'all')
		{
			$BumdesaDB = parent::allBumdesa();
		}
		elseif($filterType === 'detail')
		{
			$parameters['relation'] = true;
			$parameters['reformatdate'] = true;
			$parameters['truevalue'] = true;
			$BumdesaDB = parent::detailBumdesa($parameters);
		}
		elseif($filterType === 'this')
		{
			$BumdesaDB = parent::detailBumdesa($parameters);
		}
		elseif($filterType === 'search')
		{
			$BumdesaDB = parent::searchBumdesa($parameters);
		}
		elseif($filterType === 'checkname')
		{
			$BumdesaDB = parent::checkBumdesaName($parameters);
		}
		elseif($filterType === 'checkcode')
		{
			$BumdesaDB = parent::checkBumdesaCode($parameters);
		}
		elseif($filterType === 'scheme')
		{
			$BumdesaDB = parent::showSchemeDB();
		}
		else
		{
			$BumdesaDB = false;
		}

		if($BumdesaDB)
		{
			//$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = parent::_handler('json')->formatStatusOK($BumdesaDB, ($section.$filterType), $filterType.ucfirst($section));
			return $jsonHandler;
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
	 * Method login Bumdesa untuk login Bumdesa
	 */
	public static function lookFilter($param=null)
	{
		if(is_null($param)) return false;

		$parameters = array('cluster'=>self::getSection());
		$filterBumdesaDatabase = parent::filterBumdesa($parameters);

		if($filterBumdesaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($filterBumdesaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method add Bumdesa untuk memasukan Bumdesa baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();

		$insertBumdesaDatabase = parent::insertBumdesa($fieldForm);

		if($insertBumdesaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($insertBumdesaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method change Bumdesa untuk mengubah data Bumdesas
	 */
	public static function change($fieldForm=null, $bumdesaid=null)
	{
		//var_dump($fieldForm, $bumdesaid);
		if(is_null($fieldForm)) return false;
		if(is_null($bumdesaid)) return false;

		$fieldForm['cluster'] = self::getSection(); 
		$fieldForm['bumdesaid'] = $bumdesaid;
		//var_dump($fieldForm);
		$updateBumdesaDatabase = parent::updateBumdesa($fieldForm);
		if($updateBumdesaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($updateBumdesaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Remove Bumdesa untuk menghapus Bumdesas
	 */
	 
	public static function remove($bumdesaid=null)
	{
		if(is_null($bumdesaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'bumdesaid'=>$bumdesaid
		);

		$deleteBumdesaDatabase = parent::deleteBumdesa($param);

		if($deleteBumdesaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($deleteBumdesaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend Bumdesa untuk menonaktifkan Bumdesas
	 */
	public static function activate($bumdesaid=null)
	{
		if(is_null($bumdesaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'bumdesaid'=>$bumdesaid,
			'typeStatus'=>'enable'
		);

		$suspendBumdesa = parent::statusBumdesa($param);
		if($suspendBumdesa)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendBumdesa, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend Bumdesa untuk menonaktifkan Bumdesas
	 */
	public static function suspend($bumdesaid=null)
	{
		if(is_null($bumdesaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'bumdesaid'=>$bumdesaid,
			'typeStatus'=>'disable'
		);

		$suspendBumdesa = parent::statusBumdesa($param);
		if($suspendBumdesa)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendBumdesa, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}
}