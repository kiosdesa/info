<?php if(!defined('_thisFileDIR')) header('Location:..');

class BuyerServices extends OfanCoreFramework
{
	private static $_ClusterDB;
    private static $_thisTable;
    private static $_secondTable;
    private static $_thirdTable;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_cdnIcon;
	private static $_cdnUser;
	private static $_cdnSeller;
	private static $_endPointActivate;

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
				'fileHandler',
				'curlHandler', 
				'smsHandler',
				'cryptoHandler'
			));
			if(!is_null(self::$_token)) self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
			parent::_snippet(array('dateConvert'));
		}

		$cluster = 'account';
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'buyer';
		self::$_secondTable = 'users';
		self::$_thirdTable = 'seller';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnUser = parent::_cdnDirectoryUser();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
		self::$_endPointActivate = _apiDomain.'/v1/buyer/activate?filter=';
	}

	/*
	 * Fungsi untuk mengambil data nama depan akun buyer
	 */
	private static function getShortNameBuyer($param)
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
			'number'=>$data['phone'],
			'message'=>$data['prefix_message'].' '.$data['otp']
		);
		//return $data['otp'];
		//var_dump($paramSMS);die();

		$sms = parent::_handler('sms', 1)->send($paramSMS);
		$return = $sms ? array(
			'status'=>true, 'message'=>self::$_lang['otp']['message'], 'response'=>$sms
		) : false;
		return $return;
	}

	/*
	 * Fungsi untuk memformat pesan aktifasi akun
	 */
	private static function activateMessage($data)
	{
		$return = array(
			'approve'=>false,
			'active'=>false,
			'sms'=>_smsGateway,
			'message'=>self::$_lang['global']['inactive']
		);

		if(isset($data['phone'])) $return['phone'] = $data['phone'];
		if(isset($data['token'])) $return['token'] = $data['token'];
		if(isset($data['verification'])) $return['url'] = self::$_endPointActivate.$data['verification'];
		return $return;
	}

	/*
	 * Fungsi untuk aktifasi akun buyer dengan status false
	 */
	protected static function activateBuyer($param)
	{
		/*
		 * CATATAN:
		 * return untuk direct link belum di konversi ke HTML atau masih dalam bentuk JSON
		 * Untuk menangani request HTML ketika buyer mengklik URL dari inbox email nya harap buat landing page khusus HTML
		 * fungsi nya untuk ngasih notice status aktifasi user berhasil/tidak
		 */
		self::load($param);
		if(!isset($param['code'])) return false;
		$codeActivate = base64_decode($param['code']);
		$codeActivate = preg_match('/|/', $codeActivate) ? explode('|', $codeActivate) : null;
		if(is_null($codeActivate)) return false;
		$emailBuyer = $codeActivate[0];
		$otpBuyer = $codeActivate[1];

		$checkOTP = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
            self::$_thisTable, array('id'), array(':email'=>$emailBuyer, ':otp'=>$otpBuyer)
        );
		//var_dump($checkOTP);die();
		if(!$checkOTP) return false;
		$userID = $checkOTP[0]['id'];
		$idSecure = parent::_handler('crypto')->numhash($checkOTP[0]['id'])->encrypt();
		$activateBuyer = parent::_handler('crud', self::$_ClusterDB)->updateData(
			self::$_thisTable, array(':id'=>$userID), array(':idsecure'=>$idSecure, ':status'=>1, ':otp'=>null)
		);
		//var_dump($activateBuyer); die();
		if(!$activateBuyer) return false;
		return true;
	}

	/*
	 * Fungsi untuk menampilkan data akun buyer untuk card profile
	 */
	protected static function cardBuyer($param)
	{
		self::load($param);
		if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('token')) == false)
		{
			if(is_null(self::$_token))
			{
				return self::$_lang['error']['403_message'];
			}
			else
			{
				//return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);
				$param['token'] = self::$_token;
			}
		}

		$getAccount = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, 
			array('idsecure','user_name','email','real_name','avatar','balance'), 
			array(':token'=>$param['token'])
		);

		if(!$getAccount) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['failed']);
		$getFollowed = parent::_handler('crud', 'cabinet')->getDataWhere('follow_seller', null, array(':id_buyer'=>$getAccount[0]['idsecure']));
		if($getFollowed)
		{
			import_snippet('arrayMerge');
			$collectFollow = getLoopValueFromOneIndexArray(array('data'=>$getFollowed,'cellGrab'=>'id_seller'));
			$followed = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(
				self::$_thirdTable, array('id','logo','slug','name','premium_shop_type'), array('id', array_unique($collectFollow))
			);

			if(!$followed) $followed = [];
			for($i=0;$i<count($followed);$i++)
			{
				$followed[$i]['logo'] = parent::_handler('file', array(
					'dir'=>self::$_cdnSeller.'/'.$followed[$i]['slug'], 'filename'=>$followed[$i]['logo']
				))->checkAvatar(parent::_generate('avatar'));
				$followed[$i]['followed'] = true;
			}
		}
		else
		{
			$followed = [];
		}

		Imports::name('Order')->from('service');
		$order = OrderServices::countOrder(array('load'=>false,'user_check'=>$getAccount[0]['idsecure']));
		if($order)
		{
			unset($order['approve']);
			unset($order['message']);
		}
		
		$getAccount[0]['avatar'] = parent::_handler('file', array(
			'dir'=>self::$_cdnUser.'/'.$getAccount[0]['user_name'], 'filename'=>$getAccount[0]['avatar']
		))->checkAvatar(parent::_generate('avatar'));
		
		$getAccount[0]['balance'] = (int)$getAccount[0]['balance'];

		return array(
			'approve'=>true,
			'server'=>array(
				'seller'=>self::$_cdnSeller,
				'user'=>self::$_cdnUser,
				'icon'=>self::$_cdnIcon
			),
			'data'=>array(
				'self'=>$getAccount[0],
				'followed'=>$followed,
				'order'=>$order['count']
			),
			'symbol'=>self::$_lang['lang']['currency']['symbol']
		);
	}

	/*
	 * Fungsi untuk menampilkan semua data akun buyer
	 */
	protected static function allBuyer($param)
	{
		/**
		 * @param Void
		 */
		self::load($param);
		if(self::$_userExist)
		{
			$selfCrud = parent::_handler('crud', self::$_ClusterDB);
			if(in_array(self::$_userExist[0]['level'], self::$_userConfig['king_access']))
			{
				$buyer = $selfCrud->showData(self::$_thisTable);
			}
			else
			{
				if(in_array(self::$_userExist[0]['level'], self::$_userConfig['deputi_access']))
				{
					$select = array('idsecure','name','real_name','born_date','register_date','login_date','status','phone');
				}
				else
				{
					$select = array('name','real_name','phone');
				}

				$buyer = $selfCrud->getDataFilter(self::$_thisTable, $select, null, null, array('idsecure'), null);
			}

			if($buyer)
			{
				return $buyer;
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
		$select = array('user_name', 'real_name');
		
		$getUserDB = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere(self::$_thisTable, $select, $where, null, null, null);
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
		$where = array(':email'=>$filter);
		$select = array('email', 'real_name');

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere(self::$_thisTable, $select, $where, null, null, null);
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
	 * self::detailBuyer() - Private static function untuk mengambil database product detil
	 * self::reformatTitikKomaArray() - Memformat ulang database nilai array dalam PostgreSQL
	 * parent::_handler('crud', ) di file crudHandlerPDO.php
	 */
	protected static function detailBuyer($param)
	{
		/**
		 * @param filter
		 */
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];

		$filter = isset($param['filter']) ? $param['filter'] : null;
		$where = is_null($filter) ? false : (is_string($filter) ? array(':name'=>$filter) : false);

		if(in_array(self::$_userExist[0]['level'], self::$_userConfig['king_access']))
		{
			$select = null;
		}
		else
		{
			$select = array(
				'name','email','real_name',
				'born_date','phone','level','status'
			);
		}

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
            self::$_thisTable, $select, $where, null, null, null
        );
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
	 * Method Search buyer untuk mencari data akun buyer sesuai kata kunci
	 */
	protected static function searchBuyer($param)
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
		$search = array(':user_name'=>$lookup, ':real_name'=>$lookup, ':email'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;

		$select = array(
			'idsecure',
			'user_name',
			'real_name',
			'email'
		);

		$getUserDB = parent::_handler('crud', self::$_ClusterDB)->searchData(self::$_thisTable, $search, $select, $limit);
		if($getUserDB)
		{
			return $getUserDB;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Fungsi untuk login akun buyer
	 */
	protected static function loginBuyer($param)
	{
		/**
		 * @param email
		 * @param password
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->isEmptyFalse(array('email', 'password')) == false) return false;

		// Memastikan nilai PASSWORD apakah sudah di encrypt/belum, digunakan untuk OTP
		$password = isset($param['md5']) ? (
			$param['md5'] == true ? md5($param['password']) : $param['password']
		) : md5($param['password']);
		$paramValidate = array(
			'select'=>array(
				'idsecure','user_name','real_name','level',
				'login_date','status','token','phone','otp','avatar'
			), 
			'where'=>array(':email'=>$param['email'], ':password'=>$password)
		);

		// Check user exist
		$userExistLOGIN = parent::_handler('validate')->buyerCheck($paramValidate);
		//var_dump($userExistLOGIN);
		//return false;
		if(parent::_access('member', $userExistLOGIN) == false) return array('approve'=>false, 'message'=>self::$_lang['login']['denied']);
		if($userExistLOGIN == false)
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['login']['failed']
			);
		}
		else
		{
			$site_token = parent::_handler('code', array($userExistLOGIN[0]['idsecure'], $userExistLOGIN[0]['level']))->generatorToken(true);
			$whereUpdate = array(':idsecure'=>$userExistLOGIN[0]['idsecure']);
			/**
			 * Check STATUS user jika false 
			 * maka akun tersebut harus di aktifasi
			 */
			if($userExistLOGIN[0]['status'] == false)
			{
				if(is_null($userExistLOGIN[0]['token']))
				{
					$paramInactive = array(':token'=>$site_token);
					$userExistLOGIN[0]['token'] = $site_token;
					parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, $whereUpdate, $paramInactive);
				}

				return self::activateMessage(array(
					'token'=>$userExistLOGIN[0]['token'], 
					'verification'=>base64_encode($param['email'].'|'.$userExistLOGIN[0]['otp'])
				));
			}
			else
			{
				/**
				 * STATUS user adalah true/aktif 
				 * maka langsung melakukan update database untuk konfirmasi login
				 */
				$isMustUpdate = is_null($userExistLOGIN[0]['token']) ? true : false;
				$update = false;

				$paramSet = array(
					':otp'=>null,
					':device_log'=>_userAgent,
					':ip_last_log'=>_ipUSER
				);

				/**
				 * Inject Array data untuk mengubah nilai login & token
				 * Jika token = null atau kosong setelah pengecekan User valid
				 */
				if($isMustUpdate == true)
				{
					//if(!is_null(self::$_token)) unset($_SESSION['login_token']);
					$paramSet[':login_date'] = date('Y-m-d');
					$paramSet[':token'] = $site_token;
					$update = true;
				}
				
				if(!is_null($userExistLOGIN[0]['otp'])) $paramSet[':otp'] = null;

				/**
				 * Melakukan Update Database user setelah semua verifikasi akun valid
				 */
				$updateStatus = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, $whereUpdate, $paramSet);

				$getToken = $isMustUpdate ? $site_token : $userExistLOGIN[0]['token'];		
				$_SESSION['login_token'] = $getToken;

				//unset($userExistLOGIN[0]['idsecure']);
				//unset($userExistLOGIN[0]['user_name']);
				unset($userExistLOGIN[0]['login_date']);
				unset($userExistLOGIN[0]['phone']);
				unset($userExistLOGIN[0]['otp']);

				$userExistLOGIN[0]['shortname'] = self::getShortNameBuyer($userExistLOGIN[0]['real_name']);
				$userExistLOGIN[0]['avatar'] = parent::_handler('file', array(
					'dir'=>self::$_cdnUser.'/'.$userExistLOGIN[0]['user_name'], 'filename'=>$userExistLOGIN[0]['avatar']
				))->checkAvatar(parent::_generate('avatar'));
				return array(
					'data'=>$userExistLOGIN[0],
					'update'=>$update,
					'sms'=>_smsGateway,
					'approve'=>$updateStatus,
					'token'=>$_SESSION['login_token'], 
					'verified'=>$userExistLOGIN[0]['status'], 
					'message'=>self::$_lang['login']['success']
				);
			}
		}
	}

	/*
	 * Fungsi untuk logout akun buyer
	 */
	protected static function logoutBuyer($param)
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
			if(self::$_userExist[0]['status'] == false)
			{
				return self::activateMessage(array(
					'token'=>$_userExist[0]['token'], 
					'verification'=>base64_encode($_userExist[0]['email'].'|'.$_userExist[0]['otp'])
				));
			}
			else
			{
				$whereUpdate = array(':idsecure'=>self::$_userExist[0]['idsecure']);
				$paramSet = array(':token'=>null, ':login_date'=>_thisDateYMD);
				$updateUser = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, $whereUpdate, $paramSet);
				if(isset($_SESSION['login_token'])) unset($_SESSION['login_token']);

				$return = array(
					'approve'=>$updateUser,
					'message'=>self::$_lang['logout']['success']
				);

				return $return;
			}
		}
	}

	/*
	 * Fungsi untuk merubah informasi data status akun buyer
	 */
	protected static function statusBuyer($param)
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
			 * 2. self::_userExist[0]['level'] = Akan di cek apakah level user setara admin atau bukan, kemudian ---->
			 * 3. Fungsi statusBuyer() akan di @return boolean
			 */
			$decisionStatusChange = self::$_userExist == false ? (in_array(self::$_userExist[0]['level'], self::$_userConfig['deputi_access']) ? true : false) : self::$_userExist;
			if($decisionStatusChange == false)
			{
				return false;
			}
			else
			{
				$whereUpdate = array(
					':idsecure'=>self::$_userExist[0]['idsecure']
				);

				$paramSet = array(':status'=>$statusValue);
				$disableBuyer = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, $whereUpdate, $paramSet);
				if($disableBuyer)
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

	/*
	 * Fungsi untuk mengenerate data alamat akun buyer
	 */
	protected static function selfAddress($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$getAddress = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, array('address_meta'), array(':token'=>self::$_token)
		);
		
		if(!$getAddress) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['denied']);
		$returnAddress = unserialize($getAddress[0]['address_meta']);
		if(count($getAddress) < 1) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['failed']);
		for($i=0;$i<count($returnAddress);$i++)
		{
			$returnAddress[$i]['code_format'] = $returnAddress[$i]['metadata']['district_id'] .'|'. $returnAddress[$i]['metadata']['zip_code'];
		}
		return array('approve'=>true, 'data'=>$returnAddress);
	}

	/*
	 * Fungsi untuk mengenerate informasi akun buyer
	 */
	protected static function selfInfoBuyer($param)
	{
		/**
		 * @param
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token')) == false) return false;
		$CheckUser = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
            self::$_thisTable, 
            array('user_name', 'real_name', 'email', 'born_date', 'phone', 'device_log', 'login_date', 'register_date'), 
			array(':token'=>$param['token']), null
		);
		if($CheckUser)
		{
			$tmpArray = array();
			foreach($CheckUser[0] as $konci => $nilai)
			{
				if(in_array($konci, array('user_name', 'real_name'))) $type = 'text';
				if(in_array($konci, array('email'))) $type = 'email';
				if(in_array($konci, array('born_date'))) $type = 'date';
				if(in_array($konci, array('phone'))) $type = 'number';
				if(in_array($konci, array('device_log', 'login_date', 'register_date'))) $type = 'disable';
				if(in_array($konci, array('user_name'))) 
				{
					$type = parent::_access('deputi', self::$_userExist) ? 'text' : ($nilai == '' ? 'text' : 'disable');
				}

				if(in_array($konci, array('login_date', 'register_date'))) $nilai = dateToStringTime($nilai);

				$dataArray = array(
					'field'=>$konci, 
					'value'=>$nilai, 
					'type'=>$type
				);

				// Merubah Label text untuk input row username
				$dataArray['label'] = $konci == 'name' ? ucwords(str_replace('_',' ',$konci)) : ucwords(str_replace('_',' ',str_replace('','',$konci)));
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

	protected static function sendOTP($param)
	{
		self::load($param);
		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse(array('token')) == false) return false;

		// Lihat file manifest/.config (ubah boolean untuk aktifasi/nonaktif smsgateway)
		$getOTP = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, array('otp', 'phone'), array(':token'=>$param['token'])
		);
		//var_dump($getOTP);die();

		$param['phone'] = isset($param['phone']) ? $param['phone'] : $getOTP[0]['phone'];
		if(!$getOTP) return false;
		$otp = self::generateSmsUserOTP(array(
			'phone'=>$param['phone'], 
			'prefix_message'=>sprintf(self::$_lang['sms']['message_verifikasi'], _thisBrand), 
			'otp'=>$getOTP[0]['otp']
		));
		if(!$otp) return false;
		return $otp;
	}

	/*
	 * Fungsi untuk mereset/reset Code OTP
	 */
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
		$CheckUser = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, array('id','phone'), array(':email'=>$param['email'], ':phone'=>$param['phone'])
		);
		if($CheckUser)
		{
			$codeOTP = parent::_handler('code')->generatorOTP();
			$whereUpdate = array(
				':id'=>$CheckUser[0]['id']
			);

			$reset = parent::_handler('crud', self::$_ClusterDB)->updateData(
				self::$_thisTable, $whereUpdate, array(':otp'=>$codeOTP)
			);
			if($reset)
			{
				$otp = self::generateSmsUserOTP(array(
					'phone'=>$CheckUser[0]['phone'], 
					'prefix_message'=>sprintf(self::$_lang['sms']['message_verifikasi'], _thisBrand), 
					'otp'=>$codeOTP
				));
				if(!$otp) return array(
					'approve'=>false, 'message'=>self::$_lang['crud']['update']['failed']
				);
				
				return array(
					'data'=>$otp,
					'approve'=>$reset,
					'sms'=>_smsGateway,
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

	/*
	 * Fungsi untuk konfirmasi Code OTP
	 */
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

		$checkOTP = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, array('email', 'password'), array(':email'=>$param['email'], ':otp'=>$param['otp']), null
		);
		if($checkOTP)
		{
			$reParam = array(
				'email'=>$checkOTP[0]['email'],
				'password'=>$checkOTP[0]['password'],
				'md5'=>false,
				'cluster'=>$param['cluster'],
				'load'=>false,
			);

			// If jika form dari login kondisi buyer belum aktif
			if(isset($param['from'])) if($param['from'] == 'login') return self::activateBuyer(
				array('code'=>base64_encode($param['email'].'|'.$param['otp']), 'load'=>false, 'return'=>'redirect')
			);
			// Else jika bukan dari form login (kondisi buyer sudah aktif)
			return self::loginBuyer($reParam);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['otp']['denied']
			);
		}
	}

	/*
	 * Fungsi untuk konfirmasi token
	 */
	protected static function confirmToken($param)
	{
		/**
		 * @param otp
		 * @param email
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token')) == false) return false;
		$select = array(
            'avatar',
            'user_name',
			'real_name',
			'token',
			'level'
		);

		$fetchBuyer = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, $select, array(':token'=>$param['token']), null
		);
		if($fetchBuyer)
		{
			$tokenFetch = $fetchBuyer[0]['token'];
			//if(is_null(self::$_token)) 
			$_SESSION['login_token'] = $tokenFetch;
			$fetchBuyer[0]['shortname'] = self::getShortNameBuyer($fetchBuyer[0]['real_name']);
			$fetchBuyer[0]['avatar'] = parent::_handler('file', array(
				'dir'=>self::$_cdnUser.'/'.$fetchBuyer[0]['user_name'], 'filename'=>$fetchBuyer[0]['avatar']
			))->checkAvatar(parent::_generate('avatar'));
			
			return array(
				'data'=>$fetchBuyer[0],
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

	/*
	 * Fungsi untuk update/regenerate token
	 */
	protected static function updateToken($param)
	{
		/**
		 * @param token
		 * @return boolean
		 */
		self::load($param);
		if(parent::_handler('validate', $param)->issetFalse(array('token', 'generate')) == false) return false;
		$checkBuyer = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere(self::$_thisTable, array('idsecure', 'level'), array(':token'=>$param['token']), null);
		if($checkBuyer)
		{
			$GenerateToken = $param['generate'] == true ? parent::_handler('code', array($userExistLOGIN[0]['idsecure'], $userExistLOGIN[0]['level']))->generatorToken(true) : null;
			$disableBuyer = parent::_handler('crud', self::$_ClusterDB)
			->updateData(self::$_thisTable, array(':idsecure'=>$checkBuyer[0]['idsecure']), array(':token'=>$GenerateToken));
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

	/*
	 * Fungsi untuk update/mengubah informasi buyer
	 */
	protected static function updateBuyer($param)
	{
		/**
		 * @param name
		 * @param password
		 * @param real_name
		 * @param phone
		 * @param born_date
		 * @param bornplace
		 * @param email
		 */

		if(isset($param['trace']))
		{
			parent::_snippet(array('reTrace'));
		}

		if(isset($param['trace'])) $param = reTrace($param, '', array('login_date', 'device_log', 'register_date'));

		self::load($param);
		if(self::$_userExist == false)
		{
			return false;
		}
		else
		{
			if(self::$_userExist[0]['status'] == false)
			{
				return self::activateMessage(array(
					'token'=>$_userExist[0]['token'], 
					'verification'=>base64_encode($_userExist[0]['email'].'|'.$_userExist[0]['otp'])
				));
			}
			else
			{	
				if(isset($param['name']) OR isset($param['name']))
				{
					$param['username'] = isset($param['name']) ? $param['name'] : isset($param['name']) ? $param['name'] : $param['username'];
				}

				$paramSet = array();
				if(isset($param['user_name'])) $paramSet[':user_name'] = $param['user_name'];
				if(isset($param['avatar'])) $paramSet[':avatar'] = $param['avatar'];
				if(isset($param['password'])) $paramSet[':password'] = md5($param['password']);
				if(isset($param['real_name'])) $paramSet[':real_name'] = $param['real_name'];
				if(isset($param['phone'])) $paramSet[':phone'] = $param['phone'];
				if(isset($param['born_date'])) $paramSet[':born_date'] = $param['born_date'];
				if(isset($param['email'])) $paramSet[':email'] = $param['email'];
				if(in_array(self::$_userExist[0]['level'], array(0,1,2)))
				{
					if(isset($param['level'])) $paramSet[':level'] = intval($param['level']);
				}

				$whereUpdate = array(':idsecure'=>self::$_userExist[0]['idsecure']);
				$updateUser = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, $whereUpdate, $paramSet);
				if($updateUser)
				{
					$return = array(
						'approve'=>$updateUser,
						'message'=>self::$_lang['crud']['update']['success']
					);
					
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
	

	/*
	 * Fungsi untuk membuat account buyer baru
	 */
	protected static function insertBuyer($param)
	{
		/**
		 * @param user_name
		 * @param password
		 * @param real_name
		 * @param phone
		 * @param born_date
		 * @param email
		 */
		self::load($param);

		$validateParam = array(
			'user_name','email','password',
			'real_name','born_date','phone'
		);

		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return false;

		$CheckUser = parent::_handler('validate')->buyerCheck(array(
			'select'=>array('idsecure'), 
			'where'=>array(':user_name'=>$param['user_name'], ':email'=>$param['email']),
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
			    ':email'=>$param['email'],
			    ':phone'=>$param['phone'],
			    ':user_name'=>$param['user_name'],
			    ':real_name'=>$param['real_name'],
			    ':password'=>md5($param['password']),
			    ':born_date'=>$param['born_date'],
			    ':register_date'=>date('Y-m-d'),
			    ':login_date'=>date('Y-m-d'),
			    ':otp'=>$codeOTP,
				':device_log'=>_userAgent,
				':ip_last_log'=>_ipUSER
			);

		    $paramSet[':level'] = isset($param['level']) ? intval($param['level']) : 6;
		    $paramSet[':status'] = isset($param['status']) ? intval($param['status']) : 0;

			$selfCrud = parent::_handler('crud', self::$_ClusterDB);
			$createUser = $selfCrud->insertData(self::$_thisTable, $paramSet);
			//var_dump($paramSet);
			if($createUser)
			{
				// Check user exist
				$userExist = parent::_handler('validate')->buyerCheck(array(
					'select'=>array('id','level','phone','otp','email'), 
					'where'=>array(':email'=>$param['email'], ':password'=>md5($param['password']))
				));
				if($userExist)
				{
					// Khusus untuk insert new akun buyer value data idsecure menggunakan crypto handler bukan dari get database
					$idSecure = parent::_handler('crypto')->numhash($userExist[0]['id'])->encrypt();
					// Generating site_token
					$site_token = parent::_handler('code', array($idSecure, $userExist[0]['level']))->generatorToken(true);
					$site_token_md5 = $site_token;
					$updateUser = $selfCrud->updateData(
						self::$_thisTable, array(':id'=>$userExist[0]['id']), array(':token'=>$site_token_md5)
					);
					if($updateUser)
					{
						$return = array(
							'approve'=>true,
							'message'=>self::$_lang['register']['success'],
							'data'=>array(
								'token'=>$site_token_md5
							)
						);

						if(_eMailing == true)
						{
							// Persiapan kirim email aktifasi
							$parMail = array(
								'load'=>false, 'to'=>$userExist[0]['email'], 'to_name'=>$param['real_name'],
								'link_activating'=>self::$_endPointActivate.base64_encode($userExist[0]['email'].'|'.$userExist[0]['otp'])
							);

							Imports::name('Mail')->from('service');
							$emailingActivate = MailServices::emailActivateUser($parMail);
							$return['data']['email_status'] = $emailingActivate;
						}

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

	/*
	 * Fungsi untuk menghapus akun buyer
	 */
	protected static function deleteBuyer($param)
	{
		/**
		 * @param id
		 */
		self::load($param);
		// Check user exist
		if(self::$_userExist)
		{
			if(in_array(self::$_userExist[0]['level'], self::$_userConfig['king_access']))
			{
				$whereDelete = array(':idsecure'=>$param['idsecure']);
				$DeleteUser = parent::_handler('crud', self::$_ClusterDB)->deleteData(self::$_thisTable, $whereDelete);
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