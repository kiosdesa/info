<?php if(!defined('_thisFileDIR')) header('Location:..');

class SMSHandler extends OfanCoreFramework
{
	private $_curl;
	private $_select;
	private $_number;
	private $_message;

	/**
	 * SMS Gateway masih menggunakan versi gratis via mpssoft.co.id (WORKED!)
	 * Kedepannya jika masih belum memiliki dana buatkan beberapa akun menggunakan minimal 3 nomor berbeda
	 * Kemudian buatkan alogaritma acak credential untuk mengatasi peak request dari users
	 */
	function __construct($select)
	{
		$this->_select = is_null($select) ? rand(1,2) : $select;
        if(!class_exists('curlHandler')) parent::_library(array('curlHandler'));
		$this->_curl = parent::_handler('curl', 'data');
	}


	public function send($param=null)
	{
		$this->_number = $param['number'];
		$this->_message = $param['message'];
		return $this->gatewayProvider();
	}


	protected function gatewayProvider()
	{
		$select = $this->_select;
		switch($select)
		{
			case 1:
				$param = $this->ProviderOne();
			break;
			case 2:
				$param = $this->ProviderTwo();
			break;
			default:
				$param = false;
			break;
		}
		//var_dump($param);die();

		$sendSMS = $this->_curl->execute($param);
		if(is_null($sendSMS))
		{
			/*
			return array(
				'approve'=>false,
				'message'=>self::$_lang['global']['failed']
			);*/
			return false;
		}
		else
		{
			return true;
		}
	}


	private function ProviderOne()
	{
		$apiURL = 'http://www.mpssoft.co.id/smsgateway/api/sendsms';
		$credential = array('085759000374', '0fbd9564d57513a3bef47775f11cfce3');

	    $paramData = array(
			'url'=>$apiURL,
			'args'=>array(
				'data'=>array(
					'username'=>$credential[0],
					'password'=>$credential[1],
					'passwordencrypt'=>1,
					'number'=>$this->_number,
					'message'=>str_replace('/\,/','\n',$this->_message)
				)
			)
		);

		return $paramData;
	}


	private function ProviderTwo()
	{
		$apiURL = 'https://platform.clickatell.com/messages/http/send';
		$credential = 'rn3cTYjNQ2qsAJygClyKRQ==';

	    $paramData = array(
			'url'=>$apiURL,
			'args'=>array(
				'data'=>array(
					'apiKey'=>$credential,
					'to'=>$this->_number,
					'content'=>str_replace('/\,/','\n',$this->_message)
				)
			)
		);

		return $paramData;
	}
}
?>