<?php
/**
* Class untuk Shipping
*/
Imports::name('Upload')->from('service');

class Upload extends UploadServices
{
	private static function getSection()
	{
		return isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
	}
    
    protected static function product($param)
    {
		$fieldForm['dir'] = self::getSection();
		$product = parent::productUpload($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $product);
    }
    
    protected static function profile($param)
    {
		$fieldForm['dir'] = self::getSection();
		$profile = parent::profileUpload($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $profile);
    }
    
    protected static function avatar($param)
    {
		$fieldForm['dir'] = self::getSection();
		$avatar = parent::avatarUpload($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $avatar);
    }
    
    protected static function banner($param)
    {
		$fieldForm['dir'] = self::getSection();
		$banner = parent::bannerUpload($fieldForm);
		return parent::_return(__CLASS__, __FUNCTION__, $banner);
    }
}
?>