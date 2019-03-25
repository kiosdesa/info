<?php if(!defined('_thisFileDIR')) header('Location:..');

class AdviceServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? $param['cluster'] : 'cabinet');
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
	}



	protected static function allAdvice()
	{
		self::load();
		if(true)
		{
			$allAdviceDB = parent::_handler('crud', self::$_ClusterDB)->showData('saran');
			if($allAdviceDB)
			{
				/* Handle Original Database */
				$userFound = array(
					$allAdviceDB[0]['saran_user_id']
				);
				$userMatchDB = parent::_relation(
					array($allAdviceDB, 'id'), 
					array('account', 'buyer', 
						array('id', 'user_name', 'real_name'), 
						array('id', $userFound)
					),true, true
				);

				return $userMatchDB[0];
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



	protected static function detailAdvice($param)
	{
		self::load($param);
		if(self::$_userExist)
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
			{
				parent::_snippet(array('breakSymbolArray'));
				$filter = isset($param['filter']) ? $param['filter'] : null;
				$where = is_null($filter) ? false : (is_string($filter) ? array(':saran_id'=>$filter) : false);

				$getUserDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere('saran', null, $where, null, null, null);

				if($getUserDB)
				{
					return $getUserDB;
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
		else
		{
			return false;
		}
	}



	/** 
	 * Method Search User untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchAdvice($param)
	{
		self::load($param);
		if(self::$_userExist)
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
			{
				if(!isset($param['lookup'])) return false;
				if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

				self::load($param);
				$lookup = $param['lookup'];
				$search = array(':saran_subject'=>$lookup, ':saran_description'=>$lookup);
				$limit = isset($param['limit']) ? $param['limit'] : null;

				$AdviceDB = parent::_handler('crud', self::$_ClusterDB)->searchData('saran', $search, null, $limit);
				if($AdviceDB)
				{
					return $AdviceDB;
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
		else
		{
			return false;
		}
	}




	protected static function insertAdvice($param)
	{
		/* Fungsi ReTrace untuk memecah data loop dari parameter xHTTP */
		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
			$param = reTrace($param);
		}

		self::load($param);
		parent::_handler('validate', $param)->issetFalse(array('message','subject'));
		if(!self::$_userExist == false) return false;
		$accessDecision = parent::_access('member', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$paramSet = array();
		if(isset($param['subject'])) $paramSet[':saran_subject'] = $param['subject'];
		if(isset($param['message'])) $paramSet[':saran_description'] = $param['message'];
		$paramSet[':saran_user_id'] = intval(self::$_userExist[0]['id']);
		$paramSet[':saran_ip_address'] = _ipUSER;
		$paramSet[':saran_platform'] = _userAgent;
		$paramSet[':saran_add_date'] = date('Y-m-d');

		$insertAdvice = parent::_handler('crud', self::$_ClusterDB)->insertData('saran', $paramSet);
		if($insertAdvice)
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



	protected static function deleteAdvice($param)
	{
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
			{
				$whereDelete = array(':saran_id'=>intval($param['adviceid']));
				$DeleteUser = parent::_handler('crud', self::$_ClusterDB)->deleteData('saran', $whereDelete);

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