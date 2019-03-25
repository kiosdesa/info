<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk Advice
 */
//import_service('Advice');
Imports::name('Advice')->from('service');

class Advice extends AdviceServices
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
			$AdviceDB = parent::allAdvice();
		}
		elseif($filterType === 'detail')
		{
			$AdviceDB = parent::detailAdvice($parameters);
		}
		elseif($filterType === 'search')
		{
			$AdviceDB = parent::searchAdvice($parameters);
		}
		else
		{
			$AdviceDB = false;
		}

		if($AdviceDB)
		{
			$jsonHandler = parent::_handler('json')->formatStatusOK($AdviceDB, ($section.$filterType), $filterType.ucfirst($section));
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
	 * Method add Advice untuk memasukan Advice baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();

		$insertAdviceDatabase = parent::insertAdvice($fieldForm);
		if($insertAdviceDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($insertAdviceDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Remove Advice untuk menghapus Advices
	 */
	public static function remove($adviceid=null)
	{
		$param = array(
			'cluster'=>self::getSection(), 
			'adviceid'=>$adviceid
		);

		$deleteAdviceDatabase = parent::deleteAdvice($param);
		if($deleteAdviceDatabase)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return parent::_handler('json')->formatStatusOK($deleteAdviceDatabase, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}
}