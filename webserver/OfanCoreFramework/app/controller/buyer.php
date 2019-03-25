<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class untuk Buyer
 */
Imports::name('Buyer')->from('service');

class Buyer extends BuyerServices
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
			$userDB = parent::allBuyer($parameters);
		}
		elseif($filterType === 'detail')
		{
			$userDB = parent::detailBuyer($parameters);
		}
		elseif($filterType === 'search')
		{
			$userDB = parent::searchBuyer($parameters);
		}
		elseif($filterType === 'checkusername')
		{
			$userDB = parent::checkUserName($parameters);
		}
		elseif($filterType === 'checkemail')
		{
			$userDB = parent::checkEmail($parameters);
		}
		elseif($filterType === 'activate')
		{
			$params = array('code'=>$filter);
			$userDB = parent::activateBuyer($params);
			if($userDB == true)
			{
				$redir = _thisDomain.'/cdn/success.html';
			}
			else
			{
				$redir = _thisDomain.'/404.html';
			}
			header("Location: $redir");
			exit();die();
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
	 * Method login Buyer untuk login Buyer
	 */
	public static function login($email=null, $password=null)
	{
		if(is_null($email) && is_null($password)) return false;

		$parameters = array('email'=>$email, 'password'=>$password, 'cluster'=>self::getSection());
		$login = parent::loginBuyer($parameters);

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
	 * Method logout Buyer untuk logout Buyer
	 */
	public static function logout()
	{
		$parameters = array('cluster'=>self::getSection());
		$logout = parent::logoutBuyer($parameters);

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
	 * Method reset Buyer untuk reset Buyer
	 */
	public static function self($token=null)
	{
		if(is_null($token)) return false;
		$parameters['cluster'] = self::getSection();
		$parameters['token'] = $token;
		$self = parent::selfInfoBuyer($parameters);
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

	public static function card($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$address = parent::cardBuyer($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $address);
	}
	
	/** 
	 * Method reset Buyer untuk reset Buyer
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
	 * Method reset Buyer untuk reset Buyer
	 */
	public static function sendotp($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$otp = parent::sendOTP($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $otp);
	}

	/** 
	 * Method reset Buyer untuk reset Buyer
	 */
	public static function otp($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$otp = parent::confirmOTP($fieldForm);
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
	 * Method reset Buyer untuk reset Buyer
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
	 * Method add Buyer untuk memasukan Buyer baru
	 */
	public static function add($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$add = parent::insertBuyer($fieldForm);
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
	 * Method change Buyer untuk mengubah data Buyer
	 */
	public static function change($fieldForm=null)
	{
		if(is_null($fieldForm)) return false;
		$fieldForm['cluster'] = self::getSection();
		$change = parent::updateBuyer($fieldForm);
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
	 * Method Remove Buyer untuk menghapus Buyer
	 */
	public static function remove($id=null)
	{
		$param = array(
			'cluster'=>self::getSection(),
			'typeStatus'=>'enable'
		);

		$remove = parent::statusBuyer($param);
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
	 * Method Suspend Buyer untuk menonaktifkan Buyer
	 */
	public static function suspend($token=null)
	{
		$param = array(
			'cluster'=>self::getSection(), 
			'typeStatus'=>'disable'
		);

		$suspend = parent::statusBuyer($param);
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