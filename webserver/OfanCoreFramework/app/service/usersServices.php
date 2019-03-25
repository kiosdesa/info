<?php if(!defined('_thisFileDIR')) header('Location:..');

class UsersServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_thisTable;
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
			parent::_library(array(
				'dbHandler', 
				'crudHandlerPDO', 
				'jsonHandler', 
				'validateHandler', 
				'codeHandler', 
				'curlHandler', 
				'smsHandler'
			));
			self::$_userExist = parent::_handler('validate', self::$_token)->userToken();
		}

		parent::_snippet(array('dateConvert'));
		$cluster = 'account';
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'users';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
	}



	private static function activateMessage($data)
	{
		return array(
			'approve'=>false,
			'token'=>$data['token'],
			'url'=>_apiDomain.'/v1/users/activate?code='.$data['verification'],
			'message'=>self::$_lang['global']['inactive']
		);
	}




	private static function getShortNameUsers($param)
	{
		$identifyData = preg_match('/[\s]/', $param) ? explode(' ', $param) : $param;
		$countData = is_array($identifyData) ? count($identifyData) > 1 ? $identifyData[0].' '.$identifyData[1] : $identifyData[0] : $identifyData;
		return $countData;
	}



	/**
	 * Mengirim kan kode OTP melalui SMS gateway
	 */
	protected static function generateSmsUserOTP($data=null)
	{
		if(is_null($data)) return false;
		if(_smsGateway == false) return false;

		$paramSMS = array(
			'number'=>$data['user_phone'],
			'message'=>$data['prefix_message'].' '.$data['code_otp']
		);

		$sms = parent::_handler('sms', 1)->send($paramSMS);
		$return = $sms ? array('status'=>true, 'code'=>$data['code_otp'], 'response'=>$sms) : false;
		//var_dump($sms);
		return $return;
	}



	protected static function activateUsers($param)
	{
		self::load($param);
		$codeActivate = base64_decode($param['filter']);
		$codeActivate = preg_match('/|/', $codeActivate) ? explode('|', $codeActivate) : null;
		if(is_null($codeActivate)) return fale;
		$emailUsers = $codeActivate[0];
		$otpUsers = $codeActivate[1];

		$checkOTP = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', array('user_token'), array(':user_email'=>$emailUsers, ':user_otp'=>$otpUsers), null);
		if($checkOTP)
		{
			$userToken = $checkOTP[0]['user_token'];
			$activateUsers = parent::_handler('crud', self::$_ClusterDB)
			->updateData('users', array(':user_token'=>$userToken), array(':user_status'=>1, ':user_otp'=>null));
			if($activateUsers)
			{
				return array(
					'approve'=>true,
					'message'=>self::$_lang['activate']['success']
				);
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['activate']['failed']
				);
			}
		}
		else
		{
			return false;
		}
	}



	protected static function allUser($param)
	{
		/**
		 * @param Void
		 */
		self::load($param);
		if(self::$_userExist)
		{
			$selfCrud = parent::_handler('crud', self::$_ClusterDB);
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['king_access']))
			{
				$allUser = $selfCrud->showData('users');
			}
			else
			{
				if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']))
				{
					$select = array('user_id','user_nik','user_name','user_fullname','user_bornplace','user_borndate','user_registerdate','user_lastlogin','user_status','user_phone');
				}
				else
				{
					$select = array('user_nik','user_name','user_fullname','user_phone');
				}

				$allUser = $selfCrud->getDataFilter('users', $select, null, null, array('user_id'), null);
			}

			if($allUser)
			{
				return $allUser;
			}
			else
			{
				return null;
			}
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkUsername($param)
	{
		/**
		 * @param filter
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('filter')) == false) return false;
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':user_name'=>$filter);
		$select = array('user_name', 'user_fullname');
		
		$getUserDB = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', $select, $where, null, null, null);
		if($getUserDB)
		{
			$Result = array();
			$Result['text'] = self::$_lang['crud']['read']['success'];
			$Result['count'] = count($getUserDB);
			$Result['data'] = $getUserDB[0];
			return $Result;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Chack User username untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function checkEmail($param)
	{
		/**
		 * @param filter
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('filter')) == false) return false;
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = array(':user_email'=>$filter);
		$select = array('user_email', 'user_fullname');

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', $select, $where, null, null, null);
		if($getUserDB)
		{
			$Result = array();
			$Result['text'] = self::$_lang['crud']['read']['success'];
			$Result['count'] = count($getUserDB);
			$Result['data'] = $getUserDB[0];
			return $Result;
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
	protected static function detailUser($param)
	{
		/**
		 * @param filter
		 */
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];

		//parent::_snippet(array('breakSymbolArray'));
		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = is_null($filter) ? false : (is_string($filter) ? array(':user_name'=>$filter) : false);

		if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['king_access']))
		{
			$select = null;
		}
		else
		{
			$select = array(
				//'user_id',
				'user_nik',
				'user_name',
				'user_email',
				'user_fullname',
				'user_borndate',
				'user_bornplace',
				'user_phone',
				'user_bumdescode',
				'user_level',
				'user_status'
			);
		}

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', $select, $where, null, null, null);
		if($getUserDB)
		{
			return $getUserDB;
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method Search User untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchUser($param)
	{
		/**
		 * @param lookup
		 * @param limit
		 */
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];

		if(parent::_handler('validate', $param)->issetFalse(array('lookup')) == false) return false;
		if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

		$lookup = $param['lookup'];
		$search = array(':user_name'=>$lookup, ':user_fullname'=>$lookup, ':user_email'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;

		$select = array(
			'user_id',
			'user_name',
			'user_fullname',
			'user_email'
		);

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)->searchData('users', $search, $select, $limit);
		if($getUserDB)
		{
			return $getUserDB;
		}
		else
		{
			return false;
		}
	}



	protected static function loginUser($param)
	{
		/**
		 * @param email
		 * @param password
		 */
		
		self::load($param);
		if(parent::_handler('validate', $param)->isEmptyFalse(array('email', 'password')) == false) return false;

		// Memastikan nilai PASSWORD apakah sudah di encrypt/belum, digunakan untuk OTP
		$password = isset($param['md5']) ? ($param['md5'] == true ? md5($param['password']) : $param['password']) : md5($param['password']);
		$paramValidate = array(
			'select'=>array(
				'user_id', 
				'user_name', 
				'user_fullname', 
				'user_level', 
				'user_lastlogin', 
				'user_status', 
				'user_token', 
				'user_phone', 
				'user_otp',
				'user_bumdescode'
			), 
			'where'=>array(':user_email'=>$param['email'], ':user_password'=>$password)
		);

		// Check user exist
		$userExistLOGIN = parent::_handler('validate')->userCheck($paramValidate);
		//var_dump($userExistLOGIN);
		if(parent::_access('partner', $userExistLOGIN) == false) return array('approve'=>false, 'message'=>self::$_lang['login']['denied']);
		if($userExistLOGIN == false)
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['login']['failed']
			);
		}
		else
		{
			$site_token = parent::_handler('code', array($userExistLOGIN[0]['user_id'], $userExistLOGIN[0]['user_level']))->generatorToken(true);
			$whereUpdate = array(':user_id'=>$userExistLOGIN[0]['user_id']);
			/**
			 * Check STATUS user jika false 
			 * maka akun tersebut harus di aktifasi
			 */
			if($userExistLOGIN[0]['user_status'] == false)
			{
				if(is_null($userExistLOGIN[0]['user_token']))
				{
					$paramInactive = array(':user_token'=>$site_token);
					$userExistLOGIN[0]['user_token'] = $site_token;
					parent::_handler('crud', self::$_ClusterDB)->updateData('users', $whereUpdate, $paramInactive);
				}

				return self::activateMessage(array(
					'token'=>$userExistLOGIN[0]['user_token'], 
					'verification'=>base64_encode($param['email'].'|'.$userExistLOGIN[0]['user_otp'])
				));
			}
			else
			{
				/**
				 * STATUS user adalah true/aktif 
				 * maka langsung melakukan update database untuk konfirmasi login
				 */
				$isMustUpdate = is_null($userExistLOGIN[0]['user_token']) ? true : false;
				$update = false;

				$paramSet = array(
					':user_otp'=>null,
					':user_login_from'=>_userAgent,
					':user_ip_login'=>_ipUSER
				);

				/**
				 * Inject Array data untuk mengubah nilai user_login & user_token
				 * Jika user_token = null atau kosong setelah pengecekan User valid
				 */
				if($isMustUpdate == true)
				{
					//if(!is_null(self::$_token)) unset($_SESSION['login_token']);
					$paramSet[':user_lastlogin'] = date('Y-m-d');
					$paramSet[':user_token'] = $site_token;
					$update = true;
				}
				
				if(!is_null($userExistLOGIN[0]['user_otp'])) $paramSet[':user_otp'] = null;

				/**
				 * Melakukan Update Database user setelah semua verifikasi akun valid
				 */
				$updateStatus = parent::_handler('crud', self::$_ClusterDB)->updateData('users', $whereUpdate, $paramSet);

				$getToken = $isMustUpdate ? $site_token : $userExistLOGIN[0]['user_token'];				
				//if(is_null(self::$_token)) 
				$_SESSION['login_token'] = $getToken;

				unset($userExistLOGIN[0]['user_id']);
				unset($userExistLOGIN[0]['user_name']);
				unset($userExistLOGIN[0]['user_lastlogin']);
				unset($userExistLOGIN[0]['user_phone']);
				unset($userExistLOGIN[0]['user_otp']);

				$userExistLOGIN[0]['user_shortname'] = self::getShortNameUsers($userExistLOGIN[0]['user_fullname']);
				return array(
					'data'=>$userExistLOGIN[0],
					'update'=>$update,
					'approve'=>$updateStatus,
					'token'=>$_SESSION['login_token'], 
					'verified'=>$userExistLOGIN[0]['user_status'], 
					'message'=>self::$_lang['login']['success']
				);
			}
		}
	}



	protected static function logoutUser($param)
	{
		/**
		 * @param Void
		 */
		
		self::load($param);
		// Check user exist
		if(self::$_userExist == false)
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['logout']['failed']
			);
		}
		else
		{
			if(self::$_userExist[0]['user_status'] == false)
			{
				return self::activateMessage(self::$_userExist[0]['user_token']);
			}
			else
			{
				$whereUpdate = array(':user_id'=>self::$_userExist[0]['user_id']);
				$paramSet = array(':user_token'=>null, ':user_lastlogin'=>_thisDateYMD);
				$updateUser = parent::_handler('crud', self::$_ClusterDB)->updateData('users', $whereUpdate, $paramSet);
				if(isset($_SESSION['login_token'])) unset($_SESSION['login_token']);

				$return = array(
					'approve'=>$updateUser,
					'message'=>self::$_lang['logout']['success']
				);

				return $return;
			}
		}
	}



	protected static function statusUser($param)
	{
		/**
		 * @param typeStatus
		 */

		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		//var_dump(self::$_token);
		if(self::$_userExist)
		{
			switch($param['typeStatus'])
			{
				case 'enable': $statusValue = 1; break;
				case 'disable': $statusValue = 0; break;
				default: $statusValue = 0; break;
			}

			/**
			 * Membatasi akses perubahan status
			 * FLOW:
			 * 1. self::$_userExist = Jika pengguna bukan bersangkutan atau tidak tervalidasi maka ---->
			 * 2. self::_userExist[0]['user_level'] = Akan di cek apakah level user setara admin atau bukan, kemudian ---->
			 * 3. Fungsi statusUser() akan di @return boolean
			 */
			$decisionStatusChange = self::$_userExist == false ? (in_array(self::$_userExist[0]['user_level'], self::$_userConfig['deputi_access']) ? true : false) : self::$_userExist;
			if($decisionStatusChange == false)
			{
				return false;
			}
			else
			{
				$whereUpdate = array(
					':user_id'=>self::$_userExist[0]['user_id']
				);

				$paramSet = array(':user_status'=>$statusValue);
				$disableUsers = parent::_handler('crud', self::$_ClusterDB)
				->updateData('users', $whereUpdate, $paramSet);
				if($disableUsers)
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
						'message'=>self::$_lang['crud']['update']['failed']
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



	protected static function selfAddress($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$getAddress = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, array('user_address_meta'), array(':user_token'=>self::$_token)
		);
		
		if(!$getAddress) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['denied']);
		$returnAddress = unserialize($getAddress[0]['user_address_meta']);
		for($i=0;$i<count($returnAddress);$i++)
		{
			$returnAddress[$i]['code_format'] = $returnAddress[$i]['metadata']['district_id'] .'|'. $returnAddress[$i]['metadata']['zip_code'];
		}
		return array('approve'=>true, 'data'=>$returnAddress);
	}




	protected static function selfInfoUsers($param)
	{
		/**
		 * @param
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token')) == false) return false;
		$CheckUser = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', null, array(':user_token'=>$param['token']), null);
		if($CheckUser)
		{
			unset($CheckUser[0]['user_id']);
			unset($CheckUser[0]['user_ip_login']);
			unset($CheckUser[0]['user_password']);
			unset($CheckUser[0]['user_level']);
			unset($CheckUser[0]['user_status']);
			unset($CheckUser[0]['user_token']);
			unset($CheckUser[0]['user_otp']);
			unset($CheckUser[0]['user_address_meta']);
			$tmpArray = array();
			foreach($CheckUser[0] as $konci => $nilai)
			{
				if(in_array($konci, array('user_name', 'user_fullname', 'user_bornplace', 'user_bumdescode'))) $type = 'text';
				if(in_array($konci, array('user_email'))) $type = 'email';
				if(in_array($konci, array('user_borndate'))) $type = 'date';
				if(in_array($konci, array('user_nik', 'user_phone'))) $type = 'number';
				if(in_array($konci, array('user_login_from', 'user_lastlogin', 'user_registerdate'))) $type = 'disable';
				if(in_array($konci, array('user_name', 'user_bumdescode'))) 
				{
					$type = parent::_access('deputi', self::$_userExist) ? 'text' : ($nilai == '' ? 'text' : 'disable');
				}

				if(in_array($konci, array('user_lastlogin', 'user_registerdate'))) $nilai = dateToStringTime($nilai);

				$dataArray = array(
					'field'=>$konci, 
					'value'=>$nilai, 
					'type'=>$type
				);

				// Merubah Label text untuk input row username
				$dataArray['label'] = $konci == 'user_name' ? ucwords(str_replace('_',' ',$konci)) : ucwords(str_replace('_',' ',str_replace('user_','',$konci)));
				array_push($tmpArray, $dataArray);
			}

			return array(
				'approve'=>true,
				'message'=>self::$_lang['crud']['read']['success'],
				'data'=>$tmpArray
			);
		}
		else
		{
			return false;
		}
	}




	protected static function resetOtp($param)
	{
		/**
		 * @param phone
		 * @param email
		 */
		self::load($param);
		$paramValidate = array('email', 'phone');
		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($paramValidate) == false) return false;
		$CheckUser = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', array('user_id'), array(':user_email'=>$param['email'], ':user_phone'=>$param['phone']), null);
		if($CheckUser)
		{
			$codeOTP = parent::_handler('code')->generatorOTP();
			$whereUpdate = array(
				':user_id'=>$CheckUser[0]['user_id']
			);

			$reset = parent::_handler('crud', self::$_ClusterDB)
			->updateData('users', $whereUpdate, array(':user_otp'=>$codeOTP));
			if($reset)
			{
				return array(
					'data'=>array('code_otp'=>$codeOTP),
					'approve'=>$reset,
					'message'=>self::$_lang['crud']['update']['success']
				);
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['crud']['update']['failed']
				);
			}
		}
		else
		{
			return false;
		}
	}



	
	protected static function confirmOTP($param)
	{
		/**
		 * @param otp
		 * @param email
		 */
		self::load($param);
		$paramValidate = array('email', 'otp');
		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($paramValidate) == false) return false;

		$checkOTP = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', array('user_email', 'user_password'), array(':user_email'=>$param['email'], ':user_otp'=>$param['otp']), null);
		if($checkOTP)
		{
			$reParam = array(
				'email'=>$checkOTP[0]['user_email'],
				'password'=>$checkOTP[0]['user_password'],
				'md5'=>false,
				'cluster'=>$param['cluster'],
				'load'=>false,
			);
			//var_dump($reParam);
			return self::loginUser($reParam);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['otp']['denied']
			);
		}
	}



	
	protected static function confirmToken($param)
	{
		/**
		 * @param otp
		 * @param email
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token')) == false) return false;
		$select = array(
			//'user_nik',
			//'user_name',
			'user_fullname',
			//'user_email',
			//'user_phone',
			'user_bumdescode',
			'user_token',
			'user_level',
			//'user_borndate',
			//'user_bornplace'
		);

		$fetchUsers = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', $select, array(':user_token'=>$param['token']), null);
		if($fetchUsers)
		{
			$tokenFetch = $fetchUsers[0]['user_token'];
			//if(is_null(self::$_token)) 
			$_SESSION['login_token'] = $tokenFetch;
			$fetchUsers[0]['user_shortname'] = self::getShortNameUsers($fetchUsers[0]['user_fullname']);

			/* Handle Original Database */
			$userMatchDB = parent::_relation(
				array($fetchUsers[0], 'user_bumdescode'), 
				array('sensus', 'bumdesa', 'bumdesa_name', 
					array(':bumdesa_code'=>$fetchUsers[0]['user_bumdescode'])
				),false
			);
			
			$fetchUsers[0]['user_bumdesaname'] = $userMatchDB;
			return array(
				'data'=>$fetchUsers[0],
				'token'=>$tokenFetch,
				'message'=>self::$_lang['login']['success']
			);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['login']['failed']
			);
		}
	}



	
	protected static function updateToken($param)
	{
		/**
		 * @param token
		 * @return boolean
		 */
		
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token', 'generate')) == false) return false;
		$checkUsers = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere('users', array('user_id', 'user_level'), array(':user_token'=>$param['token']), null);
		if($checkUsers)
		{
			$GenerateToken = $param['generate'] == true ? parent::_handler('code', array($userExistLOGIN[0]['user_id'], $userExistLOGIN[0]['user_level']))->generatorToken(true) : null;
			$disableUsers = parent::_handler('crud', self::$_ClusterDB)
			->updateData('users', array(':user_id'=>$checkUsers[0]['user_id']), array(':user_token'=>$GenerateToken));
			if($param['generate'] == true) $_SESSION['login_token'] = $GenerateToken;
			return array(
				'token'=>$GenerateToken,
				'message'=>self::$_lang['login']['success']
			);
		}
		else
		{
			return false;
		}
	}




	protected static function updateUser($param)
	{
		/**
		 * @param user_name
		 * @param password
		 * @param fullname
		 * @param phone
		 * @param borndate
		 * @param bornplace
		 * @param bumdescode
		 * @param nik
		 * @param email
		 */

		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
		}

		if(isset($param['trace'])) $param = reTrace($param, 'user_', array('user_lastlogin', 'user_login_from', 'user_registerdate'));

		self::load($param);
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(self::$_userExist[0]['user_status'] == false)
			{
				return self::activateMessage(self::$_userExist[0]['user_token']);
			}
			else
			{	
				if(isset($param['user_name']) OR isset($param['name']))
				{
					$param['username'] = isset($param['user_name']) ? $param['user_name'] : isset($param['name']) ? $param['name'] : $param['username'];
				}

				$paramSet = array();
				if(isset($param['username'])) $paramSet[':user_name'] = $param['username'];
				//if(isset($param['avatar'])) $paramSet[':avatar'] = $param['avatar'];
				if(isset($param['password'])) $paramSet[':user_password'] = md5($param['password']);
				if(isset($param['fullname'])) $paramSet[':user_fullname'] = $param['fullname'];
				if(isset($param['phone'])) $paramSet[':user_phone'] = $param['phone'];
				if(isset($param['borndate'])) $paramSet[':user_borndate'] = $param['borndate'];
				if(isset($param['bornplace'])) $paramSet[':user_bornplace'] = $param['bornplace'];
				if(isset($param['bumdescode'])) $paramSet[':user_bumdescode'] = $param['bumdescode'];
				if(isset($param['nik'])) $paramSet[':user_nik'] = $param['nik'];
				if(isset($param['email'])) $paramSet[':user_email'] = $param['email'];
				if(in_array(self::$_userExist[0]['user_level'], array(0,1,2)))
				{
					if(isset($param['level'])) $paramSet[':user_level'] = intval($param['level']);
				}

				$whereUpdate = array(':user_id'=>self::$_userExist[0]['user_id']);
				$updateUser = parent::_handler('crud', self::$_ClusterDB)->updateData('users', $whereUpdate, $paramSet);
				if($updateUser)
				{
					$return = array(
						'approve'=>$updateUser,
						'message'=>self::$_lang['crud']['update']['success']
					);
					
					//if(!is_null(self::$_token)) $return['token'] = self::$_token;
					return $return;
				}
				else
				{
					return array(
						'approve'=>false,
						'message'=>self::$_lang['crud']['update']['failed']
					);
				}
			}
		}
	}



	protected static function insertUser($param)
	{
		/**
		 * @param username
		 * @param password
		 * @param fullname
		 * @param phone
		 * @param borndate
		 * @param bornplace
		 * @param bumdescode
		 * @param nik
		 * @param email
		 */
		self::load($param);

		$validateParam = array(
			'nik',
			'username',
			'email',
			'password',
			'fullname',
			'bornplace',
			'borndate',
			'phone',
			//'bumdescode'
		);

		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return false;

		$CheckUser = parent::_handler('validate')->userCheck(array(
			'select'=>array('user_id'), 
			'where'=>array(':user_name'=>$param['username'], ':user_email'=>$param['email']),
			'crudtype'=>'whereOR'
		));

		if($CheckUser)
		{
			$messageExtend = count($CheckUser) > 1 ? ' (Email/Username Unavailable)' : '';
			return array(
				'approve'=>false,
				'message'=>(self::$_lang['register']['denied']).$messageExtend 
			);
		}
		else
		{
			$codeOTP = parent::_handler('code')->generatorOTP();
			$paramSet = array(
			    ':user_nik'=>$param['nik'],
			    ':user_name'=>$param['username'],
			    ':user_email'=>$param['email'],
			    ':user_password'=>md5($param['password']),
			    ':user_fullname'=>$param['fullname'],
			    ':user_bornplace'=>$param['bornplace'],
			    ':user_borndate'=>$param['borndate'],
			    ':user_phone'=>$param['phone'],
			    ':user_bumdescode'=>$param['bumdescode'],
			    ':user_registerdate'=>date('Y-m-d'),
			    ':user_lastlogin'=>date('Y-m-d'),
			    ':user_otp'=>$codeOTP,
				':user_login_from'=>_userAgent,
				':user_ip_login'=>_ipUSER
			);

		    $paramSet[':user_level'] = isset($param['level']) ? intval($param['level']) : 5;
		    $paramSet[':user_status'] = isset($param['status']) ? intval($param['status']) : 0;

			$selfCrud = parent::_handler('crud', self::$_ClusterDB);
			$createUser = $selfCrud->insertData('users', $paramSet);
			//var_dump($paramSet);
			if($createUser)
			{
				// Check user exist
				$userExist = parent::_handler('validate')->userCheck(array(
					'select'=>array('user_id','user_level','user_phone','user_otp'), 
					'where'=>array(':user_email'=>$param['email'], ':user_password'=>md5($param['password']))
				));
				if($userExist)
				{
					// Generating site_token
					$site_token = parent::_handler('code', array($userExistLOGIN[0]['user_id'], $userExistLOGIN[0]['user_level']))->generatorToken(true);
					$site_token_md5 = $site_token;
					$paramUpdate = array(':user_token'=>$site_token_md5);
					$updateUser = $selfCrud->updateData('users', array(':user_id'=>$userExist[0]['user_id']), $paramUpdate);
					if($updateUser)
					{
						// Lihat file manifest/.config (ubah boolean untuk aktifasi/nonaktif smsgateway)
						$otp = self::generateSmsUserOTP(array(
							'user_phone'=>$userExist[0]['user_phone'], 
							'prefix_message'=>self::$_lang['sms']['message_login'], 
							'code_otp'=>$userExist[0]['user_otp']
						));

						$return = array(
							'approve'=>true,
							'message'=>self::$_lang['register']['success'],
							'data'=>array('token'=>$site_token_md5)
						);

						if($otp != false) $return['data']['otp'] = $otp;
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
						'message'=>self::$_lang['error']['404_message']
					);
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['register']['failed']
				);
			}
		}
	}




	protected static function deleteUser($param)
	{
		/**
		 * @param user_id
		 */
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(in_array(self::$_userExist[0]['user_level'], self::$_userConfig['king_access']))
			{
				$whereDelete = array(':user_id'=>$param['user_id']);
				$DeleteUser = parent::_handler('crud', self::$_ClusterDB)->deleteData('users', $whereDelete);
				if($DeleteUser)
				{
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