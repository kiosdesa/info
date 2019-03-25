<?php if(!defined('_thisFileDIR')) header('Location:..');

class BumdesaServices extends OfanCoreFramework
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
		self::$_thisTable = 'bumdesa';
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
		$accessDecision = parent::_access('deputi', self::$_userExist);
		if($accessDecision)
		{
			$fetch = parent::_handler('crud', self::$_ClusterDB)->showRowSchema('public', 'bumdesa');
			if(!$fetch) return false;
			// Info Index Field: 6,7,8,9 adalah Code Lokasi/Kota/Provinsi di Indonesia
			$fetch = reindexInput($fetch, array('column_name', array('/bumdesa_/', '/_user_id/', '/user_id_/', '/_code/')), array(0,6,7,8,9,10,11,12,13));
			$return['data'] = json_decode($fetch);
		}
		$return['readwrite'] = $accessDecision;
		return $return;
	}


	
	protected static function allBumdesa()
	{
		self::load();
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		$allBumdesaDB = parent::_handler('crud', self::$_ClusterDB)
		->showData(self::$_thisTable, 'bumdesa_id, bumdesa_address, bumdesa_code, bumdesa_name', array('row'=>'bumdesa_date_add_data', 'sort'=>'DESC'));
		if($allBumdesaDB)
		{
			return array(
				'title'=>'Data BUMDESA', 
				'data'=>$allBumdesaDB,
				'total'=>parent::_count(self::$_ClusterDB, self::$_thisTable, 'bumdesa_date_add_data'),
				'icon'=>'ios-briefcase'
			);
		}
		else
		{
			return self::$_lang['error']['500_message'];
		}
	}



	/** 
	 * Method Search User untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchBumdesa($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

		$lookup = $param['lookup'];
		$search = array(':bumdesa_code'=>$lookup, ':bumdesa_name'=>$lookup, ':bumdesa_address'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;

		$BumdesaDB = parent::_handler('crud', self::$_ClusterDB)->searchData('bumdesa', $search, null, $limit);
		if($BumdesaDB)
		{
			return array(
				'title'=>'BUMDESA', 
				'data'=>$BumdesaDB,
				'icon'=>'ios-briefcase'
			);
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkBumdesaName($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate', $param)->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':bumdesa_name'=>$filter);
		$select = array(
			'bumdesa_code',
			'bumdesa_ketua_user_id',
			'bumdesa_sekretaris_user_id',
			'bumdesa_bendahara_user_id',
			'bumdesa_address',
			'bumdesa_desa_code',
			'bumdesa_kecamatan_code',
			'bumdesa_kabupaten_code',
			'bumdesa_provinsi_code',
			'bumdesa_status'
		);

		$BumdesaDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('bumdesa', $select, $where, null, null, null);

		if($BumdesaDB)
		{
			return $BumdesaDB;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkBumdesaCode($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		parent::_handler('validate')->issetFalse(array('filter'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':bumdesa_code'=>$filter);
		$select = array(
			'bumdesa_code',
			'bumdesa_ketua_user_id',
			'bumdesa_sekretaris_user_id',
			'bumdesa_bendahara_user_id',
			'bumdesa_address',
			'bumdesa_desa_code',
			'bumdesa_kecamatan_code',
			'bumdesa_kabupaten_code',
			'bumdesa_provinsi_code',
			'bumdesa_status'
		);

		$BumdesaDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('bumdesa', $select, $where, null, null, null);
		if($BumdesaDB)
		{
			return $BumdesaDB;
		}
		else
		{
			return false;
		}
	}




	/**
	 * self::detailBumdesa() - Private static function untuk mengambil database product detil
	 * self::reformatTitikKomaArray() - Memformat ulang database nilai array dalam PostgreSQL
	 * parent::_handler('crud', ) di file crudHandlerPDO.php
	 */
	protected static function detailBumdesa($param)
	{
		self::load($param);
		parent::_snippet(array('replaceIndexArray', 'dateConvert', 'arrayMerge'));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = is_null($filter) ? false : (is_string($filter) ? array(':bumdesa_id'=>$filter) : false);

		$getDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('bumdesa', null, $where, null, null, null);
		if($getDB)
		{
			$relationDecision = isset($param['relation']) ? $param['relation'] : false;
			$reformatDateDecision = isset($param['reformatdate']) ? $param['reformatdate'] : false;
			$trueValueDecision = isset($param['truevalue']) ? $param['truevalue'] : false;
			$accessDecision = parent::_access('deputi', self::$_userExist) ? true : (self::$_userExist[0]['user_bumdescode'] == $getDB[0]['bumdesa_code'] ? true : false);
			if($relationDecision)
			{
				$getDB[0]['bumdesa_asset'] = parent::_kurs($getDB[0]['bumdesa_asset']);

				/* Handle Original Database */
				$userFound = array(
					$getDB[0]['bumdesa_ketua_user_id'], 
					$getDB[0]['bumdesa_sekretaris_user_id'], 
					$getDB[0]['bumdesa_bendahara_user_id'], 
					$getDB[0]['bumdesa_user_id_add_by'], 
					$getDB[0]['bumdesa_user_id_edit_by']
				);
				$userMatchDB = parent::_relation(
					array($getDB[0], 'user_id'), 
					array('account', 'users', 
						array('user_id', 'user_name', 'user_fullname'), 
						array('user_id', $userFound)
					),true, true
				);
				$returnDB = $userMatchDB;
			}
			else
			{
				$returnDB = $getDB;
			}

			$unsetIndexArray = array(
				//'bumdesa_desa_code', 
				//'bumdesa_kecamatan_code', 
				//'bumdesa_kabupaten_code', 
				//'bumdesa_provinsi_code', 
				'bumdesa_user_id_add_by', 
				'bumdesa_date_add_data',
				'bumdesa_user_id_edit_by',
				'bumdesa_edit_date'
			);

			if(!$reformatDateDecision) $returnDB = unsetLoop($returnDB[0], $unsetIndexArray);

			/* Handle Modify Database */
			$return = [];
			$return['id'] = $returnDB[0]['bumdesa_id'];
			unset($returnDB[0]['bumdesa_id']);
			unset($returnDB[0]['bumdesa_desa_code']);
			unset($returnDB[0]['bumdesa_kecamatan_code']);
			unset($returnDB[0]['bumdesa_kabupaten_code']);
			unset($returnDB[0]['bumdesa_provinsi_code']);
			$return['data'] = reindexLoop($returnDB,'bumdesa_', array('user_id','_code'), $reformatDateDecision, $trueValueDecision, self::$_lang);
			$return['title'] = $returnDB[0]['bumdesa_name'];
			$return['readwrite'] = $accessDecision;
			return $return;
		}
		else
		{
			return false;
		}
	}



	protected static function filterBumdesa($param)
	{
		self::load($param);
		parent::_snippet(array('globalService'));
	}



	protected static function updateBumdesa($param)
	{	
		if(!isset($param['bumdesaid'])) return array('approve'=>false,'message'=>self::$_lang['crud']['update']['denied']);//$param['bumdesaid'] = null;
		if(!isset($param['code'])) $param['code'] = null;
		if(isset($param['code']) && empty($param['code']) && strlen($param['code']) < 1) $param['code'] = null;
		
		/* memastikan index ID tidak dirubah di reTrace */
		$bumdesaid = $param['bumdesaid'];

		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param, 'bumdesa_', array(
				'bumdesa_user_id_add_by', 
				'bumdesa_date_add_data', 
				'bumdesa_user_id_edit_by',
				'bumdesa_edit_date'
			));
		}

		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load($param);
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			/* Menentukan hak akses untuk merubah data */
			$accessDecision = parent::_access('deputi', self::$_userExist) ? true : (self::$_userExist[0]['user_bumdescode'] == $param['code'] ? true : false);
			if($accessDecision)
			{
				// Untuk aplikasi kosongkan parameter 'code' untuk me Update unit usaha type yg sedang di ubah
				$selfCrud = parent::_handler('crud', self::$_ClusterDB);
				$whereCheck = array(':bumdesa_code'=>$param['code']);
				$bumdesaExistID = $selfCrud->getDataWhere('bumdesa', array('bumdesa_id', 'bumdesa_code'), $whereCheck, null, null, null);
				$bumdesaExistIDBool = !$bumdesaExistID ? true : ($bumdesaExistID[0]['bumdesa_id'] == intval($bumdesaid) ? true : false);

				if($bumdesaExistIDBool == false) 
				{
					return array('approve'=>false,'message'=>self::$_lang['crud']['update']['failed']);
				}
				else
				{
					$paramSet = array(
						':bumdesa_user_id_edit_by'=>self::$_userExist[0]['user_id'],
						':bumdesa_edit_date'=>date('Y-m-d')
					);
					
					if(isset($param['code'])) $paramSet[':bumdesa_code'] = $param['code'];
					if(isset($param['ketua'])) $paramSet[':bumdesa_ketua_user_id'] = $param['ketua'];
					if(isset($param['sekretaris'])) $paramSet[':bumdesa_sekretaris_user_id'] = $param['sekretaris'];
					if(isset($param['bendahara'])) $paramSet[':bumdesa_bendahara_user_id'] = $param['bendahara'];
					if(isset($param['address'])) $paramSet[':bumdesa_address'] = $param['address'];
					if(isset($param['desa'])) $paramSet[':bumdesa_desa_code'] = $param['desa'];
					if(isset($param['kecamatan'])) $paramSet[':bumdesa_kecamatan_code'] = $param['kecamatan'];
					if(isset($param['kabupaten'])) $paramSet[':bumdesa_kabupaten_code'] = $param['kabupaten'];
					if(isset($param['provinsi'])) $paramSet[':bumdesa_provinsi_code'] = $param['provinsi'];
					if(isset($param['status'])) $paramSet[':bumdesa_status'] = intval($param['status']);
					if(isset($param['name'])) $paramSet[':bumdesa_name'] = $param['name'];
					if(isset($param['asset'])) $paramSet[':bumdesa_asset'] = $param['asset'];
					if(isset($param['komisaris'])) $paramSet[':bumdesa_komisaris'] = $param['komisaris'];
					if(isset($param['pengawas'])) $paramSet[':bumdesa_pengawas'] = $param['pengawas'];

					$whereUpdate = array(':bumdesa_id'=>intval($bumdesaid));
					$updateBumdesa = $selfCrud->updateData('bumdesa', $whereUpdate, $paramSet);

					if($updateBumdesa)
					{

						$return = array(
							'approve'=>true,
							'message'=>self::$_lang['crud']['update']['success']
						);
						
						return $return;
					}
					else
					{
						return false;
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



	protected static function statusBumdesa($param)
	{
		self::load($param);
		parent::_handler('validate', $param)->issetFalse(array('bumdesaid','typeStatus'));
		switch($param['typeStatus'])
		{
			case 'enable': $statusValue = 1; break;
			case 'disable': $statusValue = 0; break;
			default: $statusValue = 0; break;
		}

		//$code = $param['filter'];
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(parent::_access('deputi', self::$_userExist))
			{
				$whereUpdate = array(
					':bumdesa_id'=>$param['bumdesaid']
				);

				$paramSet = array(':bumdesa_status'=>$statusValue);
				$disableBumdesaType = parent::_handler('crud', self::$_ClusterDB)->updateData('bumdesa', $whereUpdate, $paramSet);
				if($disableBumdesaType)
				{
					return $disableBumdesaType;
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
		//self::load();
		$countTotal = parent::_handler('crud', 'sensus')->count('bumdesa', 'bumdesa_id', array());
		$paramGenerate = array('initial'=>$initial, 'prefix'=>date('d'), 'suffix'=>($countTotal+1));
		//var_dump($paramGenerate);
		$generator = parent::_generate('id', $paramGenerate);
		return $generator;
	}



	protected static function insertBumdesa($param)
	{
		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param, 'bumdesa_', null);
		}
		
		self::load($param);
		$validateParam = array(
			'name','ketua','sekretaris','bendahara','address','desa',
			'kecamatan','kabupaten','provinsi','status','asset','komisaris','pengawas'
		);

		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		$bumdesaCode = isset($param['code']) ? (strlen($param['code']) > 9 ? $param['code'] : self::getID($param['name'])) : self::getID($param['name']);

		if(self::$_userExist == false)
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['access']['failed']
			);
		}
		else
		{
			if(parent::_access('deputi', self::$_userExist))
			{
				$selfCrud = parent::_handler('crud', self::$_ClusterDB);
				$unitExist = $selfCrud->getDataWhere('bumdesa', 'bumdesa_id', array(':bumdesa_code'=>$bumdesaCode), null, null, null);
				$decisionApproveSave = $unitExist !== false ? (count($unitExist) > 0 ? true : false) : false;
				//var_dump($decisionApproveSave);
				/* Nilai True maka akan di block & return failed */
				if($decisionApproveSave == true) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['exist']);

				$paramSet = array();
				if(isset($param['ketua'])) $paramSet[':bumdesa_ketua_user_id'] = $param['ketua'];
				if(isset($param['sekretaris'])) $paramSet[':bumdesa_sekretaris_user_id'] = $param['sekretaris'];
				if(isset($param['bendahara'])) $paramSet[':bumdesa_bendahara_user_id'] = $param['bendahara'];
				if(isset($param['address'])) $paramSet[':bumdesa_address'] = $param['address'];
				if(isset($param['desa'])) $paramSet[':bumdesa_desa_code'] = $param['desa'];
				if(isset($param['kecamatan'])) $paramSet[':bumdesa_kecamatan_code'] = $param['kecamatan'];
				if(isset($param['kabupaten'])) $paramSet[':bumdesa_kabupaten_code'] = $param['kabupaten'];
				if(isset($param['provinsi'])) $paramSet[':bumdesa_provinsi_code'] = $param['provinsi'];
				if(isset($param['status'])) $paramSet[':bumdesa_status'] = intval($param['status']);
				if(isset($param['asset'])) $paramSet[':bumdesa_asset'] = $param['asset'];
				if(isset($param['name'])) $paramSet[':bumdesa_name'] = $param['name'];
				if(isset($param['komisaris'])) $paramSet[':bumdesa_komisaris'] = $param['komisaris'];
				if(isset($param['pengawas'])) $paramSet[':bumdesa_pengawas'] = $param['pengawas'];
				$paramSet[':bumdesa_code'] = $bumdesaCode;
				$paramSet[':bumdesa_user_id_add_by'] = self::$_userExist[0]['user_id'];
				$paramSet[':bumdesa_date_add_data'] = date('Y-m-d');

				$insertBumdesa = $selfCrud->insertData('bumdesa', $paramSet);
				//var_dump($paramSet);

				if($insertBumdesa)
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




	protected static function deleteBumdesa($param)
	{
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(parent::_access('intel', self::$_userExist))
			{
				$whereDelete = array(':bumdesa_id'=>$param['bumdesaid']);
				$deleteBumdesa = parent::_handler('crud', self::$_ClusterDB)->deleteData('bumdesa', $whereDelete);
				if($deleteBumdesa !== false)
				{
					$return['message'] = self::$_lang['crud']['delete']['success'];
					return $return;
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