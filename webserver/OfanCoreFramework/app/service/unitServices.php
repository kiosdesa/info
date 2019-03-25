<?php if(!defined('_thisFileDIR')) header('Location:..');

class UnitServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler'));
		self::$_ClusterDB = (isset($param['cluster']) ? $param['cluster'] : 'sensus');
		self::$_thisTable = 'unit';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		self::$_userExist = parent::_handler('validate', self::$_token)->userToken();
	}


	protected static function showSchemeDB()
	{
		self::load();
		/* Menentukan hak akses untuk merubah data */
		parent::_snippet(array('replaceIndexArray'));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision)
		{
			$unset = parent::_access('deputi', self::$_userExist) ? array(0,6,7,8,9) : array(0,1,3,6,7,8,9);
			$fetch = parent::_handler('crud', self::$_ClusterDB)->showRowSchema('public', 'unit');
			if(!$fetch) return self::$_lang['error']['403_message'];
			$fetch = reindexInput($fetch, array('column_name', array('/unit_/', '/_user_id/', '/user_id_/', '/_code/')), $unset);
			$return['data'] = json_decode($fetch);
		}
		$return['readwrite'] = $accessDecision;
		return $return;
	}



	protected static function allUnit()
	{
		self::load();
		if(self::$_userExist)
		{
			$allUnitDB = parent::_handler('crud', self::$_ClusterDB)
			->showData('unit', 'unit_id, unit_name, unit_code, unit_bumdesa_code', array('row'=>'unit_date_add', 'sort'=>'DESC'));
			if($allUnitDB)
			{
				return array(
					'title'=>'Data Unit BUMDES', 
					'data'=>$allUnitDB,
					'total'=>parent::_count(self::$_ClusterDB, self::$_thisTable, 'unit_date_add'),
					'icon'=>'ios-people'
				);
			}
			else
			{
				return self::$_lang['error']['404_message'];
			}
		}
		else
		{
			return self::$_lang['error']['403_message'];
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkUnitName($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':unit_name'=>$filter);

		$select = array(
			'unit_code',
			'unit_name',
			'unit_add_date'
		);

		$UnitDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unit', $select, $where, null, null, null);

		if($UnitDB)
		{
			return $UnitDB;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkUnitCode($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':unit_code'=>$filter);

		$select = array(
			'unit_code',
			'unit_name',
			'unit_add_date'
		);

		$UnitDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unit', $select, $where, null, null, null);

		if($UnitDB)
		{
			return $UnitDB;
		}
		else
		{
			return false;
		}
	}




	/**
	 * self::detailUser() - Private static function untuk mengambil database product detil
	 * self::reformatTitikKomaArray() - Memformat ulang database nilai array dalam PostgreSQL
	 * parent::_handler('crud', ) di file crudHandlerPDO.php
	 */
	protected static function detailUnit($param)
	{
		self::load($param);
		parent::_snippet(array('replaceIndexArray', 'dateConvert'));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		//parent::_snippet(array('breakSymbolArray'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = is_null($filter) ? false : (is_string($filter) ? array(':unit_id'=>$filter) : false);

		$getDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unit', null, $where, null, null, null);

		if($getDB)
		{
			$relationDecision = isset($param['relation']) ? $param['relation'] : false;
			$reformatDateDecision = isset($param['reformatdate']) ? $param['reformatdate'] : false;
			$trueValueDecision = isset($param['truevalue']) ? $param['truevalue'] : false;
			$accessDecision = parent::_access('deputi', self::$_userExist) ? true : (self::$_userExist[0]['user_bumdescode'] == $getDB[0]['unit_bumdesa_code'] ? true : false);
			if($relationDecision)
			{
				$getDB[0]['unit_modal'] = parent::_kurs($getDB[0]['unit_modal']);

				/* Handle Original Database */
				$userFound = array(
					$getDB[0]['unit_ketua_user_id'], 
					$getDB[0]['unit_user_id_add_data'], 
					$getDB[0]['unit_user_id_edit_data']
				);
				$userMatchDB = parent::_relation(
					array($getDB[0], 'user_id'), 
					array('account', 'users', 
						array('user_id', 'user_name', 'user_fullname'), 
						array('user_id', $userFound)
					),true, true
				);
				
				$bumdesMatchDB = parent::_relation(
					null, 
					array('sensus', 'bumdesa', 'bumdesa_name', 
						array(':bumdesa_code'=>$userMatchDB[0]['unit_bumdesa_code'])
					),false
				);

				//var_dump($userMatchDB);
				$userMatchDB[0]['unit_bumdesa_code'] = $bumdesMatchDB.' ('.$userMatchDB[0]['unit_bumdesa_code'].')';
				$returnDB = $userMatchDB;
			}
			else
			{
				$returnDB = $getDB;
			}

			$unsetIndexArray = array(
				'unit_user_id_add_data', 
				'unit_date_add',
				'unit_user_id_edit_data',
				'unit_edit_date'
			);
			if(!$reformatDateDecision) $returnDB = unsetLoop($returnDB[0], $unsetIndexArray);
			
			/* Handle Modify Database */
			$return = [];
			$return['id'] = $returnDB[0]['unit_id'];
			unset($returnDB[0]['unit_id']);
			$return['data'] = reindexLoop($returnDB,'unit_', array('user_id','_code'), $reformatDateDecision, $trueValueDecision, self::$_lang);
			$return['title'] = $returnDB[0]['unit_name'];
			$return['readwrite'] = $accessDecision;
			return $return;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Search User untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchUnit($param)
	{
		if(!isset($param['lookup'])) return false;
		if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];

		$lookup = $param['lookup'];
		$search = array(':unit_code'=>$lookup, ':unit_bumdesa_code'=>$lookup, ':unit_name'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;

		$UnitDB = parent::_handler('crud', self::$_ClusterDB)->searchData('unit', $search, null, $limit);
		if($UnitDB)
		{
			return array(
				'title'=>'Unit BUMDES', 
				'data'=>$UnitDB,
				'icon'=>'ios-people'
			);
		}
		else
		{
			return false;
		}
	}



	protected static function filterUnit($param)
	{
		self::load($param);
		parent::_snippet(array('globalService'));

	}



	protected static function updateUnit($param)
	{
		if(!isset($param['unitid'])) return array('approve'=>false,'message'=>self::$_lang['crud']['update']['denied']);
		if(!isset($param['code'])) $param['code'] = null;
		if(isset($param['code']) && empty($param['code']) && strlen($param['code']) < 1) $param['code'] = null;
		
		/* memastikan index ID tidak dirubah di reTrace */
		$unitid = $param['unitid'];

		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param, 'unit_', array(
				'unit_user_id_add_data', 
				'unit_date_add', 
				'unit_user_id_add_data',
				'unit_edit_date'
			));
		}

		self::load($param);
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			/* Menentukan hak akses untuk merubah data */
			$accessDecision = parent::_access('deputi', self::$_userExist) ? true : (self::$_userExist[0]['user_bumdescode'] == $param['bumdesa'] ? true : false);
			if($accessDecision)
			{
				// Untuk aplikasi kosongkan parameter 'code' untuk me Update unit usaha type yg sedang di ubah
				$selfCrud = parent::_handler('crud', self::$_ClusterDB);
				$whereCheck = array(':unit_code'=>$param['code']);
				$unitExistID = $selfCrud->getDataWhere('unit', array('unit_id', 'unit_code'), $whereCheck, null, null, null);
				$unitExistIDBool = !$unitExistID ? true : ($unitExistID[0]['unit_id'] == intval($unitid) ? true : false);

				if($unitExistIDBool == false) 
				{
					return array('approve'=>false,'message'=>self::$_lang['crud']['create']['failed']);
				}
				else
				{
					$paramSet = array(
						':unit_user_id_edit_data'=>strtolower(self::$_userExist[0]['user_id']),
						':unit_edit_date'=>date('Y-m-d')
					);

					if(parent::_access('deputi', self::$_userExist))
					{
						if(isset($param['code'])) $paramSet[':unit_code'] = $param['code'];
					}
						
					if(isset($param['name'])) $paramSet[':unit_name'] = $param['name'];
					if(isset($param['bumdesa'])) $paramSet[':unit_bumdesa_code'] = $param['bumdesa'];
					if(isset($param['ketua'])) $paramSet[':unit_ketua_user_id'] = $param['ketua'];
					if(isset($param['register_date'])) $paramSet[':unit_register_date'] = $param['register_date'];
					if(isset($param['status'])) $paramSet[':unit_status'] = intval($param['status']);
					if(isset($param['sk'])) $paramSet[':unit_sk'] = $param['sk'];
					if(isset($param['no_perdes'])) $paramSet[':unit_no_perdes'] = $param['no_perdes'];
					if(isset($param['since_date'])) $paramSet[':unit_since_date'] = $param['since_date'];
					if(isset($param['modal'])) $paramSet[':unit_modal'] = $param['modal'];

					$whereUpdate = array(':unit_id'=>intval($unitid));
					$updateUser = $selfCrud->updateData('unit', $whereUpdate, $paramSet);

					if($updateUser)
					{

						$return = array(
							'approve'=>true,
							'message'=>self::$_lang['crud']['update']['success']
						);
						
						return $return;
					}
					else
					{
						return array(
							'approve'=>false,
							'message'=>self::$_lang['crud']['access']['denied']
						);
					}
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['crud']['update']['denied']
				);
			}
		}
	}



	protected static function statusUnit($param)
	{
		self::load($param);
		parent::_handler('validate', $param)->issetFalse(array('bumdesacode', 'typeStatus'));

		switch($param['typeStatus'])
		{
			case 'enable': $statusValue = 1; break;
			case 'disable': $statusValue = 0; break;
			default: $statusValue = 0; break;
		}

		// Check user exist
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
			{
				$whereUpdate = array(
					':unit_id'=>$param['unitid']
				);

				$paramSet = array(':unit_status'=>$statusValue);
				$disableUnitType = parent::_handler('crud', self::$_ClusterDB)->updateData('unit', $whereUpdate, $paramSet);
				if($disableUnitType)
				{
					return $disableUnitType;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}



	protected static function getID($initial)
	{
		$countTotal = parent::_handler('crud', 'sensus')->count('unit', 'unit_id', array());
		$paramGenerate = array('initial'=>$initial, 'prefix'=>date('d'), 'suffix'=>($countTotal+1));
		$generator = parent::_generate('id', $paramGenerate);
		return $generator;
	}



	protected static function insertUnit($param)
	{
		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param, 'unit_', null);
		}
		
		self::load($param);
		/* Menentukan Hak Akses */
		$deputiAccess = parent::_access('deputi', self::$_userExist);
		$validateParam = array(
			'name','ketua','register_date',
			'status','sk','no_perdes','since_date','modal'
		);

		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		/* Hak Akses Merubah BUMDES CODE */
		$userBumdescode = self::$_userExist[0]['user_bumdescode'];
		$bumdesaCode = $deputiAccess == true ? (isset($param['bumdesa']) ? ($param['bumdesa'] == '' ? $userBumdescode : (strlen($param['bumdesa']) > 9 ? $param['bumdesa'] : $userBumdescode)) : $userBumdescode) : $userBumdescode;
		
		/* Reformat UNIT CODE */
		$unitCode = isset($param['code']) ? (strlen($param['code']) > 5 ? $param['code'] : self::getID($param['name'])) : self::getID($param['name']);
		//var_dump($deputiAccess, $validateParam, $bumdesaCode, $unitCode, $param['bumdesa']);
		//return false;

		if(self::$_userExist == false)
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['access']['failed']
			);
		}
		else
		{
			if(parent::_access('partner', self::$_userExist))
			{
				$selfCrud = parent::_handler('crud', self::$_ClusterDB);
				$unitExist = $selfCrud->getDataWhere('unit', 'unit_id', array(':unit_code'=>$unitCode), null, null, null);
				$decisionApproveSave = $unitExist !== false ? (count($unitExist) > 0 ? true : false) : false;
				/* Nilai True maka akan di block & return failed */
				if($decisionApproveSave == true) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['exist']);

				$paramSet = array();
				if(isset($param['name'])) $paramSet[':unit_name'] = $param['name'];
				if(isset($param['ketua'])) $paramSet[':unit_ketua_user_id'] = $param['ketua'];
				if(isset($param['register_date'])) $paramSet[':unit_register_date'] = $param['register_date'];
				if(isset($param['status'])) $paramSet[':unit_status'] = intval($param['status']);
				if(isset($param['sk'])) $paramSet[':unit_sk'] = $param['sk'];
				if(isset($param['no_perdes'])) $paramSet[':unit_no_perdes'] = $param['no_perdes'];
				if(isset($param['since_date'])) $paramSet[':unit_since_date'] = $param['since_date'];
				if(isset($param['modal'])) $paramSet[':unit_modal'] = $param['modal'];
				$paramSet[':unit_code'] = $unitCode;
				$paramSet[':unit_bumdesa_code'] = $bumdesaCode;
				$paramSet[':unit_status'] = isset($param['status']) ? intval($param['status']) : 0;
				$paramSet[':unit_user_id_add_data'] = strtolower(self::$_userExist[0]['user_id']);
				$paramSet[':unit_date_add'] = date('Y-m-d');

				$insertUnit = $selfCrud->insertData('unit', $paramSet);

				if($insertUnit)
				{
					$return = array(
						'approve'=>true,
						'message'=>self::$_lang['crud']['create']['success']
					);

					return $return;
				}
				else
				{
					return array(
						'approve'=>false,
						'message'=>self::$_lang['crud']['create']['failed']
					);
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['crud']['create']['denied']
				);
			}
		}
	}



	protected static function deleteUnit($param)
	{
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(parent::_access('intel', self::$_userExist))
			{
				$whereDelete = array(':unit_id'=>$param['unitid']);
				$DeleteUser = parent::_handler('crud', self::$_ClusterDB)->deleteData('unit', $whereDelete);
				if($DeleteUser)
				{
					$DeleteUser['message'] = self::$_lang['crud']['delete']['success'];
					return $DeleteUser;
				}
				else
				{
					return array(
						'approve'=>false,
						'message'=>self::$_lang['crud']['delete']['failed']
					);
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['crud']['delete']['denied']
				);
			}
		}
		else
		{
			return false;
		}
	}
}
?>