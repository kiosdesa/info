<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
* Class untuk Seller
*/
//import_service('Payment');
Imports::name('Payment')->from('service');

class Payment extends PaymentServices
{
	public static function getVendor(){}



	public static function placePayment(){}



	public static function checkPayment(){}



	public static function getWallet(){}



	public static function redeemWallet(){}



	public static function depositWallet(){}
}