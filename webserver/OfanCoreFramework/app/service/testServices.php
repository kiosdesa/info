<?php if(!defined('_thisFileDIR')) header('Location:..');

class TestServices extends OfanCoreFramework
{
	/** 
	 * Load Library 
	 */
	private static function loadLibrary()
	{
		parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler'));
	}


	/** 
	 * Identifikasi Nama Database 
	 */
	private static function identifyClusterDBuser($param=null)
	{
		return (isset($param['cluster']) ? $param['cluster'] : 'sensus');
	}



	protected static function TestConnectDB($param)
	{
		self::loadLibrary();
		$cluster = self::identifyClusterDBuser();

		$getUserDB = function_exists('_proposeCrudServices') ? 
			_proposeCrudServices($cluster)->showData($param['table']) : false;

		if($getUserDB)
		{
			return $getUserDB;
		}
		else
		{
			return false;
		}
	}



	protected static function TestInsertDB($param)
	{
		self::loadLibrary();
		$cluster = self::identifyClusterDBuser();

		$paramSet = array(
			':nama'=>$param['nama'],
			':tanggal'=>$param['tanggal'],
			':status'=>$param['status']
		);

		$insertDB = _proposeCrudServices($cluster)->insertData('test', $paramSet);
		if($insertDB)
		{
			return $insertDB;
		}
		else
		{
			return false;
		}
	}
}
?>