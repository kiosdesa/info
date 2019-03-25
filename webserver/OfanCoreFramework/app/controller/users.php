<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk User
 */
Imports::name('Users')->from('service');

class Users extends UsersServices
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

		$filterDefine = array('all','detail','search');
		if(!isset($_SESSION['login_token']) AND in_array($filterType, $filterDefine)) return null;

		if($filterType === 'all')
		{
			$userDB = parent::allUser($parameters);
		}
		elseif($filterType === 'detail')
		{
			$userDB = parent::detailUser($parameters);
		}
		elseif($filterType === 'search')
		{
			$userDB = parent::searchUser($parameters);
		}
		elseif($filterType === 'checkusername')
		{
			$userDB = parent::checkUsername($parameters);
		}
		elseif($filterType === 'checkemail')
		{
			$userDB = parent::checkEmail($parameters);
		}
		elseif($filterType === 'activate')
		{
			$userDB = parent::activateUsers($parameters);
			if($userDB)
			{
				if($userDB['approve'] == true)
				{
					header('Location: '._thisDomain);
				}
				else
				{
					header('Location: '._thisDomain.'/404.html');
				}
			}
			else
			{
				header('Location: '._thisDomain.'/403.html');
			}
			//var_dump($userDB);
			exit();
		}
		else
		{
			$userDB = false;
		}

		if($userDB)
		{
			//$filterType = $filterType === 'detail' ? '' : $filterType;
			$jsonHandler = parent::_handler('json')->formatStatusOK($userDB, ($section.$filterType), $filterType.ucfirst($section));
			return $jsonHandler;
		}
		else
		{
			return false;
		}
	}



	private static function getSection()
	{
		// section adalah identifikasi nama database untuk cluster
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}

	

	/** 
	 * Method login User untuk login User
	 */
	public static function login($email=null, $password=null)
	{
		if(is_null($email) && is_null($password)) return false;

		$parameters = array('email'=>$email, 'password'=>$password, 'cluster'=>self::getSection());
		$login = parent::loginUser($parameters);

		if($login)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $login);
		}
		else
		{
			return false;
		}
	}


	/** 
	 * Method logout User untuk logout User
	 */
	public static function logout()
	{
		$parameters = array('cluster'=>self::getSection());
		$logout = parent::logoutUser($parameters);

		if($logout)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $logout);
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method reset User untuk reset User
	 */
	public static function self($token=null)
	{
		if(is_null($token)) return false;
		$parameters['cluster'] = self::getSection();
		$parameters['token'] = $token;
		$self = parent::selfInfoUsers($parameters);
		if($self)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $self);
		}
		else
		{
			return false;
		}
	}


	public static function address()
	{
		$parameters = array('cluster'=>self::getSection());
		$address = parent::selfAddress($parameters);
		return parent::_return(__CLASS__, __FUNCTION__, $address);
	}
	

	// BELUM SELESAI MEMBUAT FLOW NYA NANTI DITERUSKAN
	/** 
	 * Method reset User untuk reset User
	 */
	public static function reset($email=null, $phone=null)
	{
		if(is_null($email)) return false;
		if(is_null($phone)) return false;
		$parameters['cluster'] = self::getSection();
		$parameters['email'] = $email;
		$parameters['phone'] = $phone;
		$reset = parent::resetOtp($parameters);

		if($reset)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $reset);
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method reset User untuk reset User
	 */
	public static function otp($email=null, $otp=null)
	{
		if(is_null($email)) return null;
		if(is_null($otp)) return null;
		$parameters['cluster'] = self::getSection();
		$parameters['email'] = $email;
		$parameters['otp'] = $otp;
		$otp = parent::confirmOTP($parameters);
		if($otp)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $otp);
		}
		else
		{
			return false;
		}
	}



	/** 
	 * Method reset User untuk reset User
	 */
	public static function token($token=null)
	{
		if(is_null($token)) return false;
		$parameters['cluster'] = self::getSection();
		$parameters['token'] = $token;
		$token = parent::confirmToken($parameters);
		if($token)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $token);
		}
		else
		{
			return false;
		}
	}


	/** 
	 * Method add User untuk memasukan User baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$add = parent::insertUser($fieldForm);
		if($add)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $add);
		}
		else
		{
			return false;
		}
	}


	/** 
	 * Method change User untuk mengubah data Users
	 */
	public static function change($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$change = parent::updateUser($fieldForm);
		if($change)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $change);
		}
		else
		{
			return false;
		}
	}


	/** 
	 * Method Remove User untuk menghapus Users
	 */
	public static function remove($id=null)
	{
		$param = array(
			'cluster'=>self::getSection(),
			'typeStatus'=>'enable'
		);

		$remove = parent::statusUser($param);
		if($remove)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $remove);
		}
		else
		{
			return false;
		}
	}


	/** 
	 * Method Suspend User untuk menonaktifkan Users
	 */
	public static function suspend($token=null)
	{
		$param = array(
			'cluster'=>self::getSection(), 
			'typeStatus'=>'disable'
		);

		$suspend = parent::statusUser($param);
		if($suspend)
		{
			return parent::_return(__CLASS__, __FUNCTION__, $suspend);
		}
		else
		{
			return false;
		}
	}
}
?>