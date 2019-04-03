<?php if(!defined('_thisFileDIR')) header('Location:..');

class fileHandler extends OfanCoreFramework
{
    private $_param;
    private $_cdnUser;
    private $_cdnIcon;
    private $_cdnProduct;
    private $_cdnSeller;
    private $_cdnBanner;
    private $_fullpath;
    private $_filename;
    private $_dir;

	function __construct($param=null)
	{
		$this->_param = $param;
        if(isset($param['filename']) && isset($param['dir']))
        {
            $this->_fullpath = $param['dir'].'/'.$param['filename'];
        }

        if(isset($param['filename']))
        {
            $this->_filename = $param['filename'];
        }

        if(isset($param['dir']))
        {
            $this->_dir = $param['dir'];
        }
        
		$this->_cdnUser = parent::_cdnDirectoryUser();
		$this->_cdnIcon = parent::_cdnDirectoryIcon();
		$this->_cdnProduct = parent::_cdnDirectoryProduct();
		$this->_cdnSeller = parent::_cdnDirectorySeller();
		$this->_cdnBanner = parent::_cdnDirectoryBanner();
    }

    public function checkExist($returnBoolean=false)
    {
        if(file_exists($this->_fullpath))
        {
            return ($returnBoolean == true ? true : $this->_fullpath);
        }
        else
        {
            return false;
        }
    }
	
	public function userFolder($username=null)
	{
		if(is_null($username)) return false;
		return $username == false ? $this->_dir : $this->_cdnUser .'/'. $username;
	}

    public function checkAvatar($replacement=null, $withDirectory=true)
    {
        if(is_null($replacement)) return false;
        $check = $this->checkExist(false);
        $dir = $withDirectory == true ? $this->_cdnUser.'/' : '';
        return ($check == false ? $dir.$replacement : $check);
    }

	public function sellerFolder($slug=null)
	{
		if(is_null($slug)) return false;
		return $slug == false ? $this->_dir : $this->_cdnSeller .'/'. $slug;
	}

    public function checkBannerSeller($replacement=null, $withDirectory=true)
    {
        if(is_null($replacement)) return false;
        $check = $this->checkExist(false);
        $dir = $withDirectory == true ? $this->_cdnSeller.'/' : '';
        return ($check == false ? $dir.$replacement : $check);
    }

    public function removeFiles($planning=null)
    {
        if(!in_array($planning, array('product', 'avatar', 'profile', 'banner'))) return false;
        $directory = $_SERVER['DOCUMENT_ROOT'].'/cdn/data_' . $planning . '/';
        $filename = $this->_filename;
        $this->_fullpath = $directory . $filename;
        if($this->checkExist(true) == false) return false;
        unlink($this->_fullpath);
        return true;
    }
}