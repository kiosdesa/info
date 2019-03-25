<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk User
*/
import_service('Test');

class Test extends TestServices
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

		if($filterType === 'connect')
		{
			$sellerDatabase = parent::TestConnectDB($parameters);
		}
		else
		{
			$sellerDatabase = false;
		}

		if($sellerDatabase)
		{
			$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = _proposeJsonHandler(array('db'=>$sellerDatabase, 'named'=>($section.$filterType), 'section'=>$section.ucfirst($filterType)));
			return parent::reformatUserArrayDB($jsonHandler);
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



	public static function connect($param)
	{
		if(is_null($param)) return false;
		$checkTable = parent::TestConnectDB($param);

		if($checkTable)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return _proposeJsonHandler(array('db'=>$checkTable, 'named'=>strtolower(join($sectionName,'-')), 'section'=>join($sectionName,'')));
		}
		else
		{
			return false;
		}
	}



	public static function insert($param)
	{
		if(is_null($param)) return false;
		$insertTable = parent::TestInsertDB($param);

		if($insertTable)
		{
			$sectionName = array(__FUNCTION__, __CLASS__);
			return _proposeJsonHandler(array('db'=>$insertTable, 'named'=>strtolower(join($sectionName,'-')), 'section'=>join($sectionName,'')));
		}
		else
		{
			return false;
		}
	}
}
?>