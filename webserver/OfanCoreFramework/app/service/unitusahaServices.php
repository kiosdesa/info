<?php if(!defined('_thisFileDIR')) header('Location:..');

class UnitusahaServices extends OfanCoreFramework
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
		self::$_thisTable = 'unitusaha';
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
			$unset = parent::_access('deputi', self::$_userExist) ? array(0,3,4,5,6) : array(0,1,3,4,5,6,8);
			$fetch = parent::_handler('crud', self::$_ClusterDB)->showRowSchema('public', 'unitusaha');
			if(!$fetch) return self::$_lang['error']['403_message'];
			$fetch = reindexInput($fetch, array('column_name', array('/unitusaha_/', '/_user_id/', '/user_id_/', '/_code/')), $unset);
			$return['data'] = json_decode($fetch);
		}
		$return['readwrite'] = $accessDecision;
		return $return;
	}



	protected static function allUnitType()
	{
		self::load();
		if(self::$_userExist)
		{
			$allUnitTypeDB = parent::_handler('crud', self::$_ClusterDB)
			->showData('unitusaha', 'unitusaha_id, unitusaha_name, unitusaha_code, unitusaha_bumdesa_code, unitusaha_unit_code', array('row'=>'unitusaha_add_date', 'sort'=>'DESC'));
			if($allUnitTypeDB)
			{
				return array(
					'title'=>'Data Unit Usaha', 
					'data'=>$allUnitTypeDB,
					'total'=>parent::_count(self::$_ClusterDB, self::$_thisTable, 'unitusaha_add_date'),
					'icon'=>'ios-calculator'
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
	protected static function checkUnitUsahaName($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':unitusaha_name'=>$filter);
		$select = array(
			'unitusaha_code',
			'unitusaha_name',
			'unitusaha_add_date'
		);

		$unitUsahaTypeDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unitusaha', $select, $where, null);
		if($unitUsahaTypeDB)
		{
			return $unitUsahaTypeDB;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkUnitUsahaCode($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':unitusaha_code'=>$filter);
		$select = array(
			'unitusaha_code',
			'unitusaha_name',
			'unitusaha_add_date'
		);

		$unitUsahaTypeDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unitusaha', $select, $where, null, null, null);

		if($unitUsahaTypeDB)
		{
			return $unitUsahaTypeDB;
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
	protected static function detailUnitUsaha($param)
	{
		self::load($param);
		parent::_snippet(array('replaceIndexArray', 'dateConvert'));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		//parent::_snippet(array('breakSymbolArray'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = is_null($filter) ? false : (is_string($filter) ? array(':unitusaha_id'=>$filter) : false);

		$getDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('unitusaha', null, $where, null, null, null);

		if($getDB)
		{
			$relationDecision = isset($param['relation']) ? $param['relation'] : false;
			$reformatDateDecision = isset($param['reformatdate']) ? $param['reformatdate'] : false;
			$trueValueDecision = isset($param['truevalue']) ? $param['truevalue'] : false;
			$accessDecision = parent::_access('deputi', self::$_userExist) ? true : (self::$_userExist[0]['user_bumdescode'] == $getDB[0]['unitusaha_bumdesa_code'] ? true : false);
			if($relationDecision)
			{
				/* Handle Original Database */
				$userFound = array(
					$getDB[0]['unitusaha_user_id_add_data'], 
					$getDB[0]['unitusaha_user_id_edit_data']
				);
				$userMatchDB = parent::_relation(
					array($getDB[0], 'user_id'), 
					array('account', 'users', 
						array('user_id', 'user_name', 'user_fullname'), 
						array('user_id', $userFound)
					),true, true
				);
				
				$bumdesMatchDB = parent::_relation(null,
					array('sensus', 'bumdesa', 'bumdesa_name', 
						array(':bumdesa_code'=>$userMatchDB[0]['unitusaha_bumdesa_code'])
					),false
				);
				
				$unitMatchDB = parent::_relation(null,
					array('sensus', 'unit', 'unit_name', 
						array(':unit_code'=>$userMatchDB[0]['unitusaha_unit_code'])
					),false
				);

				$userMatchDB[0]['unitusaha_bumdesa_code'] = $bumdesMatchDB.' ('.$userMatchDB[0]['unitusaha_bumdesa_code'].')';
				$userMatchDB[0]['unitusaha_unit_code'] = $unitMatchDB.' ('.$userMatchDB[0]['unitusaha_unit_code'].')';
				$returnDB = $userMatchDB;
			}
			else
			{
				$returnDB = $getDB;
			}

			$unsetIndexArray = array(
				'unitusaha_user_id_add_data', 
				'unitusaha_add_date',
				'unitusaha_user_id_edit_data',
				'unitusaha_edit_date'
			);
			if(!$reformatDateDecision) $returnDB = unsetLoop($returnDB[0], $unsetIndexArray);
			
			$return = [];
			$return['id'] = $returnDB[0]['unitusaha_id'];
			unset($returnDB[0]['unitusaha_id']);
			$return['data'] = reindexLoop($returnDB, 'unitusaha_', array('user_id', '_code'), $reformatDateDecision, $trueValueDecision, self::$_lang);
			$return['title'] = $returnDB[0]['unitusaha_name'];
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
	protected static function searchUnitUsaha($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('lookup'));
		if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

		$lookup = $param['lookup'];
		$search = array(':unitusaha_code'=>$lookup, ':unitusaha_bumdesa_code'=>$lookup, ':unitusaha_unit_code'=>$lookup, ':unitusaha_name'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;

		$unitUsahaDB = parent::_handler('crud', self::$_ClusterDB)->searchData('unitusaha', $search, null, $limit);
		if($unitUsahaDB)
		{
			return array(
				'title'=>'Unit Usaha', 
				'data'=>$unitUsahaDB,
				'icon'=>'ios-calculator'
			);
		}
		else
		{
			return false;
		}
	}



	protected static function filterUnitUsaha($param)
	{
		self::load($param);
		parent::_snippet(array('globalService'));

	}



	protected static function updateUnitUsaha($param)
	{
		if(!isset($param['unitusahaid'])) return array('approve'=>false,'message'=>self::$_lang['crud']['update']['denied']);
		if(!isset($param['code'])) $param['code'] = null;
		if(isset($param['code']) && empty($param['code']) && strlen($param['code']) < 1) $param['code'] = null;
		
		/* memastikan index ID tidak dirubah di reTrace */
		$unitusahaid = $param['unitusahaid'];

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
				$whereCheck = array(':unitusaha_code'=>$param['code']);
				$unitExistID = $selfCrud->getDataWhere('unitusaha', array('unitusaha_id', 'unitusaha_code'), $whereCheck, null, null, null);
				$unitExistIDBool = !$unitExistID ? true : ($unitExistID[0]['unitusaha_id'] == intval($unitusahaid) ? true : false);

				if($unitExistIDBool == false) 
				{
					return array('approve'=>false,'message'=>self::$_lang['crud']['create']['failed']);
				}
				else
				{
					$paramSet = array(
						':unitusaha_user_id_edit_data'=>self::$_userExist[0]['user_id'],
						':unitusaha_edit_date'=>date('Y-m-d')
					);

					if(parent::_access('deputi', self::$_userExist))
					{
						if(isset($param['code'])) $paramSet[':unitusaha_code'] = $param['code'];
					}

					if(isset($param['unit'])) $paramSet[':unitusaha_unit_code'] = $param['unit'];
					if(isset($param['bumdesa'])) $paramSet[':unitusaha_bumdesa_code'] = $param['bumdesa'];
					if(isset($param['name'])) $paramSet[':unitusaha_name'] = $param['name'];
					if(isset($param['status'])) $paramSet[':unitusaha_status'] = intval($param['status']);

					$whereUpdate = array(':unitusaha_id'=>intval($unitusahaid));
					$updateUser = $selfCrud->updateData('unitusaha', $whereUpdate, $paramSet);
					if($updateUser)
					{

						return array(
							'approve'=>true,
							'message'=>self::$_lang['crud']['update']['success']
						);
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



	protected static function statusUnitUsaha($param)
	{
		self::load($param);
		parent::_handler('validate', $param)->issetFalse(array('unitusahaid','typeStatus'));
		switch($param['typeStatus'])
		{
			case 'enable': $statusValue = 1; break;
			case 'disable': $statusValue = 0; break;
			default: $statusValue = 0; break;
		}

		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
			{
				$whereUpdate = array(
					':unitusaha_id'=>$param['unitusahaid']
				);

				$paramSet = array(':unitusaha_status'=>$statusValue);
				$disableUnitUsahaType = parent::_handler('crud', self::$_ClusterDB)->updateData('unitusaha', $whereUpdate, $paramSet);
				if($disableUnitUsahaType)
				{
					return $disableUnitUsahaType;
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
		$countTotal = parent::_handler('crud', 'sensus')->count('unitusaha', 'unitusaha_id', array());
		$paramGenerate = array('initial'=>$initial, 'prefix'=>date('d'), 'suffix'=>($countTotal+1));
		$generator = parent::_generate('id', $paramGenerate);
		return $generator;
	}



	protected static function insertUnitUsaha($param)
	{
		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param, 'unitusaha_', null);
		}

		self::load($param);
		/* Menentukan Hak Akses */
		$deputiAccess = parent::_access('deputi', self::$_userExist);
		$validateParam = array('name','unit','status');
		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);
		
		/* Reformat UNIT USAHA CODE */
		$userBumdescode = self::$_userExist[0]['user_bumdescode'];
		$bumdesaCode = $deputiAccess == true ? isset($param['bumdesa']) ? ($param['bumdesa'] == '' ? $userBumdescode : (strlen($param['bumdesa']) > 9 ? $param['bumdesa'] : $userBumdescode)) : $userBumdescode : $userBumdescode;

		/* Reformat UNIT USAHA CODE */
		$unitUsahaCode = isset($param['code']) ? (strlen($param['code']) > 5 ? $param['code'] : self::getID($param['name'])) : self::getID($param['name']);

		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(parent::_access('partner', self::$_userExist))
			{
				$selfCrud = parent::_handler('crud', self::$_ClusterDB);
				$unitExist = $selfCrud->getDataWhere('unitusaha', 'unitusaha_id', array(':unitusaha_code'=>$unitUsahaCode), null, null);
				$decisionApproveSave = $unitExist !== false ? (count($unitExist) > 0 ? true : false) : false;
				/* Nilai True maka akan di block & return failed */
				if($decisionApproveSave == true) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['exist']);

				$paramSet = array(
					':unitusaha_user_id_add_data'=>strtolower(self::$_userExist[0]['user_id']),
					':unitusaha_add_date'=>date('Y-m-d')
				);

				if(isset($param['code'])) $paramSet[':unitusaha_code'] = $unitUsahaCode;
				if(isset($param['unit'])) $paramSet[':unitusaha_unit_code'] = $param['unit'];
				if(isset($param['bumdesa'])) $paramSet[':unitusaha_bumdesa_code'] = $bumdesaCode;
				if(isset($param['name'])) $paramSet[':unitusaha_name'] = $param['name'];
				if(isset($param['status'])) $paramSet[':unitusaha_status'] = intval($param['status']);

				$insertUnitType = $selfCrud->insertData('unitusaha', $paramSet);
				if($insertUnitType)
				{
					$return = array(
						'approve'=>true,
						'message'=>self::$_lang['crud']['create']['success']
					);

					return $return;
				}
				else
				{
					return false;
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




	protected static function deleteUnitUsaha($param)
	{
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(parent::_access('intel', self::$_userExist))
			{
				$whereDelete = array(':unitusaha_id'=>$param['unitusahaid']);
				$DeleteUser = parent::_handler('crud', self::$_ClusterDB)->deleteData('unitusaha', $whereDelete);
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
			var_dump('here');
			return false;
		}
	}
}
?>