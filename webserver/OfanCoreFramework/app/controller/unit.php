<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk Unit
 */
import_service('Unit');

class Unit extends UnitServices
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
			$UnitDB = parent::allUnit();
		}
		elseif($filterType === 'detail')
		{
			$parameters['relation'] = true;
			$parameters['reformatdate'] = true;
			$parameters['truevalue'] = true;
			$UnitDB = parent::detailUnit($parameters);
		}
		elseif($filterType === 'this')
		{
			$UnitDB = parent::detailUnit($parameters);
		}
		elseif($filterType === 'search')
		{
			$UnitDB = parent::searchUnit($parameters);
		}
		elseif($filterType === 'checkname')
		{
			$UnitDB = parent::checkUnitName($parameters);
		}
		elseif($filterType === 'checkcode')
		{
			$UnitDB = parent::checkUnitCode($parameters);
		}
		elseif($filterType === 'scheme')
		{
			$UnitDB = parent::showSchemeDB();
		}
		else
		{
			$UnitDB = false;
		}

		if($UnitDB)
		{
			//$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = parent::_handler('json')->formatStatusOK($UnitDB, ($section.$filterType), $filterType.ucfirst($section));
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
	 * Method login Unit untuk login Unit
	 */
	public static function lookFilter($param=null)
	{
		if(is_null($param)) return false;

		$parameters = array('cluster'=>self::getSection());
		$filterUnitDatabase = parent::filterUnit($parameters);

		if($filterUnitDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($filterUnitDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method add Unit untuk memasukan Unit baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();

		$insertUnitDatabase = parent::insertUnit($fieldForm);
		if($insertUnitDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($insertUnitDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method change Unit untuk mengubah data Units
	 */
	public static function change($fieldForm=null, $unitid=null)
	{
		if(is_null($fieldForm)) return false;
		if(is_null($unitid)) return false;

		$fieldForm['cluster'] = self::getSection(); 
		$fieldForm['unitid'] = $unitid;
		$updateUnitDatabase = parent::updateUnit($fieldForm);
		if($updateUnitDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($updateUnitDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Remove Unit untuk menghapus Units
	 */
	 
	public static function remove($unitid=null)
	{
		if(is_null($unitid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitid'=>$unitid
		);

		$deleteUnitDatabase = parent::deleteUnit($param);

		if($deleteUnitDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($deleteUnitDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend Unit untuk menonaktifkan Units
	 */
	public static function activate($unitid=null)
	{
		if(is_null($unitid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitid'=>$unitid,
			'typeStatus'=>'enable'
		);

		$suspendUnit = parent::statusUnit($param);
		if($suspendUnit)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendUnit, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Suspend Unit untuk menonaktifkan Units
	 */
	public static function suspend($unitid=null)
	{
		if(is_null($unitid)) return false;
		$param = array(
			'cluster'=>self::getSection(), 
			'unitid'=>$unitid,
			'typeStatus'=>'disable'
		);

		$suspendUnit = parent::statusUnit($param);
		if($suspendUnit)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($suspendUnit, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}
}