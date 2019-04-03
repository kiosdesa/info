<?php if(!defined('_thisFileDIR')) header('Location:..');

class codeHandler extends OfanCoreFramework
{
	private $_param;

	function __construct($param=null)
	{
		$this->_param = $param;
	}


	public function generatorToken($encrypt=true)
	{
		if(is_null($this->_param)) return false;
		$param = $this->_param;
		if(count($param) < 2) return false;

	    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQVWXYZ0123456789';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < 5; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

		$level_first_prase = is_array($param) ? implode('|', $param) : $param;
		$initDate = date('Y|m|d');

		$return = ($level_first_prase.'-'.$initDate.'-'.$randomString);
		return ($encrypt == true ? md5($return) : $return);
	}


	public function generatorOTP($long=6)
	{
	    $randomDate = str_shuffle(date('Ymdhis'));
	    $otp = substr($randomDate, 1, $long);
	    return $otp;
	}
}
?>