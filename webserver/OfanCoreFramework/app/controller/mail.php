<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Mail
*/
Imports::name('Mail')->from('service');

class Mail extends MailServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}


	/** 
	 * Method Insert Product untuk wishlist product 
	 */
	public static function send($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$send = parent::sendEmail($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $send);
	}


	public static function activate($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$send = parent::emailActivateUser($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $send);
	}


	public static function order($fieldForm=array())
	{
		$fieldForm['cluster'] = self::getSection();
		$send = parent::emailOrder($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $send);
	}
}
?>