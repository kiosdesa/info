<?php if(!defined('_thisFileDIR')) header('Location:..');

class validateHandler extends OfanCoreFramework
{
	private $_param;

	function __construct($param=null)
	{
		$this->_param = $param;
	}


	private function executeDB($param=null)
	{
		$paramConstruct = $this->_param;
		$cluster = $paramConstruct['cluster'];
		$table = $paramConstruct['table'];

		$Method = parent::_handler('crud', $cluster);
		if($param['crudtype']=='whereIN')
		{
			$exist = $Method->getDataWhereIn($table, $param['select'], $param['where']);
		}
		elseif($param['crudtype']=='whereOR')
		{
			$exist = $Method->getDataWhereOR($table, $param['select'], $param['where'], null);
		}
		else
		{
			$exist = $Method->getDataWhere($table, $param['select'], $param['where'], null);
		}

		if(!$exist) return false;
		return $exist;
	}


	protected function dbCheck($data=null)
	{
		if(!isset($data['cluster'])) return false;
		if(!isset($data['table'])) return false;

		$this->_param = array('cluster'=>$data['cluster'],'table'=>$data['table']);
		$crudtype = isset($data['crudtype']) ? $data['crudtype'] : null;
		$select = isset($data['select']) ? $data['select'] : null;
		$where = isset($data['where']) ? $data['where'] : null;
		return $this->executeDB(array('select'=>$select,'where'=>$where,'crudtype'=>$crudtype));
	}


	public function userCheck($data=null)
	{
		if(!isset($data['cluster'])) $data['cluster'] = 'account';
		if(!isset($data['table'])) $data['table'] = 'users';
		return $this->dbCheck($data);
	}


	public function buyerCheck($data=null)
	{
		if(!isset($data['cluster'])) $data['cluster'] = 'account';
		if(!isset($data['table'])) $data['table'] = 'buyer';
		//var_dump($data);
		return $this->dbCheck($data);
	}


	public function sellerCheck($data=null)
	{
		if(!isset($data['cluster'])) $data['cluster'] = 'account';
		if(!isset($data['table'])) $data['table'] = 'seller';
		return $this->dbCheck($data);
	}


	public function buyerToken($select=null)
	{
		$dataCompare = $this->_param;
		$select = is_null($select) ? array('idsecure', 'user_name', 'status', 'level', 'token', 'otp', 'email', 'avatar', 'real_name') : $select;
		$data = array(
			'select'=>$select,
			'where'=>array(':token'=>$dataCompare)
		);

		return $this->buyerCheck($data);
	}


	public function userToken($select=null)
	{
		$dataCompare = $this->_param;
		$select = is_null($select) ? array('user_id', 'user_name', 'user_status', 'user_level', 'user_token', 'user_bumdescode') : $select;
		$data = array(
			'select'=>$select,
			'where'=>array(':user_token'=>$dataCompare)
		);

		return $this->userCheck($data);
	}


	public function issetFalse($data=null)
	{
		$dataCompare = $this->_param;

		foreach($data as $k=>$v)
		{
			if(!isset($dataCompare[$v])) return false;
			return true;
		}
	}


	public function isEmptyFalse($data=null)
	{
		$dataCompare = $this->_param;
		foreach($data as $k=>$v)
		{
			if(empty($dataCompare[$v])) return false;
			if($dataCompare[$v] == '') return false;
			return true;
		}
	}


	public function issetAndEmptyFalse($data=null)
	{
		$return['bool'] = true;
		$dataCompare = $this->_param;
		foreach($data as $k=>$v)
		{
			if(!isset($dataCompare[$v]) || empty($dataCompare[$v]) || $dataCompare[$v] == '') $return['bool'] = false;
		}

		return $return['bool'];
	}
}
?>