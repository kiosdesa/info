<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk App
 */
import_service('App');

class App extends AppServices
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

		if($filterType === 'payconfirm')
		{
			$appDB = parent::paymenConfirmFormatData($parameters);
		}
		elseif($filterType === 'info')
		{
			$appDB = parent::appInfoSensus();
		}
		elseif($filterType === 'infokios')
		{
			$appDB = parent::appInfoEcommerce();
		}
		elseif($filterType === 'notify')
		{
			$appDB = parent::appNotifSensus();
		}
		elseif($filterType === 'graph')
		{
			$appDB = parent::appGraphSensus($parameters);
		}
		elseif($filterType === 'graphDetail')
		{
			$appDB = parent::appGraphDetail($parameters);
		}
		elseif($filterType === 'announce')
		{
			$appDB = parent::appAnnouncement($parameters);
		}
		elseif($filterType === 'getID')
		{
			$appDB = parent::appGetID($parameters);
		}
		elseif($filterType === 'dash')
		{
			$parameters['load'] = false;
			$appDB = array_merge(
				array('notif'=>parent::appNotifSensus()), 
				array('graph'=>parent::appGraphSensus($parameters)),
				array('announce'=>parent::appAnnouncement($parameters))
			);
		}
		elseif($filterType === 'home')
		{
			$appDB = parent::appHomeShop($parameters);
		}
		elseif($filterType === 'categoryProduct')
		{
			$appDB = parent::getCategoryProduct($parameters);
		}
		elseif($filterType === 'sortProduct')
		{
			$appDB = parent::sortProduct($parameters);
		}
		elseif($filterType === 'filterProduct')
		{
			$appDB = parent::filterProduct($parameters);
		}
		else
		{
			$appDB = false;
		}

		if($appDB)
		{
			$jsonHandler = parent::_handler('json')->formatStatusOK($appDB, ($section.$filterType), $filterType.ucfirst($section));
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

	public static function home($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$home = parent::appHomeShop($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $home);
	}

	public static function notif($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$notif = parent::notifApp($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $notif);
	}
}
?>