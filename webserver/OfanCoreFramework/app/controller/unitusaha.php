<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk UnitUsaha
 */
import_service('Unitusaha');

class Unitusaha extends UnitusahaServices
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
			$unitUsahaDB = parent::allUnitType();
		}
		elseif($filterType === 'detail')
		{
			$parameters['relation'] = true;
			$parameters['reformatdate'] = true;
			$parameters['truevalue'] = true;
			$unitUsahaDB = parent::detailUnitUsaha($parameters);
		}
		elseif($filterType === 'this')
		{
			$unitUsahaDB = parent::detailUnitUsaha($parameters);
		}
		elseif($filterType === 'search')
		{
			$unitUsahaDB = parent::searchUnitUsaha($parameters);
		}
		elseif($filterType === 'checkname')
		{
			$unitUsahaDB = parent::checkUnitUsahaName($parameters);
		}
		elseif($filterType === 'checkcode')
		{
			$unitUsahaDB = parent::checkUnitUsahaCode($parameters);
		}
		elseif($filterType === 'scheme')
		{
			$unitUsahaDB = parent::showSchemeDB();
		}
		else
		{
			$unitUsahaDB = false;
		}

		if($unitUsahaDB)
		{
			//$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = parent::_handler('json')->formatStatusOK($unitUsahaDB, $section.$filterType, $filterType.ucfirst($section));
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
	 * Method login UnitUsaha untuk login UnitUsaha
	 */
	public static function lookFilter($param=null)
	{
		if(is_null($param)) return false;
		$parameters = array('cluster'=>self::getSection());
		$filterUnitUsahaDatabase = parent::filterUnitUsaha($parameters);
		if($filterUnitUsahaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($filterUnitUsahaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method add UnitUsaha untuk memasukan UnitUsaha baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$insertUnitUsahaDatabase = parent::insertUnitUsaha($fieldForm);
		if($insertUnitUsahaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($insertUnitUsahaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method change UnitUsaha untuk mengubah data UnitUsahas
	 */
	public static function change($fieldForm=null, $unitusahaid=null)
	{
		if(is_null($fieldForm)) return false;
		if(is_null($unitusahaid)) return false;
		$fieldForm['cluster'] = self::getSection(); 
		$fieldForm['unitusahaid'] = $unitusahaid;
		$updateUnitUsahaDatabase = parent::updateUnitUsaha($fieldForm);
		if($updateUnitUsahaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($updateUnitUsahaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Remove UnitUsaha untuk menghapus UnitUsahas
	 */
	 
	public static function remove($unitusahaid=null)
	{
		if(is_null($unitusahaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitusahaid'=>$unitusahaid
		);

		$deleteUnitUsahaDatabase = parent::deleteUnitUsaha($param);
		if($deleteUnitUsahaDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($deleteUnitUsahaDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend UnitUsaha untuk menonaktifkan UnitUsahas
	 */
	public static function activate($unitusahaid=null)
	{
		if(is_null($unitusahaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitusahaid'=>$unitusahaid,
			'typeStatus'=>'enable'
		);

		$suspendUnitUsaha = parent::statusUnitUsaha($param);
		if($suspendUnitUsaha)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendUnitUsaha, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend UnitUsaha untuk menonaktifkan UnitUsahas
	 */
	public static function suspend($unitusahaid=null)
	{
		if(is_null($unitusahaid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitusahaid'=>$unitusahaid,
			'typeStatus'=>'disable'
		);

		$suspendUnitUsaha = parent::statusUnitUsaha($param);
		if($suspendUnitUsaha)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendUnitUsaha, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}
}