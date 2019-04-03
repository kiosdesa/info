<?php if(!defined('_thisFileDIR')) header('Location:..');

class uploadHandler extends OfanCoreFramework
{
    private $_lang;
	private $_token;
    private $_param;
    private $_return;
    private $_max_size = 1024;
    private $_upload_purpose;
    private $_regex_image = "/\.(jpg|png|jpeg)$/";

	/** 
	 * Load Library 
	 */
	function __construct($param=null)
	{
        if(is_null($param)) return false;
        $this->_param = $param;
		$this->_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
        $this->_lang = parent::_languageConfig();
        if(!class_exists('fileHandler')) parent::_library(array('fileHandler'));
    }
    
    public function upload($fileDestiny)
    {
        $this->_upload_purpose = $fileDestiny;
        $chooseName = strip_tags($this->_param->name);
        $chooseName = strtolower(preg_replace('/[^a-zA-Z0-9\_\-\.]/','_',date('Ymdhs ').$chooseName));
        $fileData = strip_tags($this->_param->file);
        // Format the supplied base64 data URI so that we remove the initial base64 identifier
        $uri = substr($fileData,strpos($fileData,",")+1);

        $checkSize = $this->getimagesizefromstring($uri);
        // Replace any spaces in the formatted base64 URI string with pluses to avoid corrupted file data
        $encodedData = str_replace(' ','+',$uri);
        // Decode the formatted base64 URI string
        $decodedData = base64_decode($encodedData);
        $directory = $_SERVER['DOCUMENT_ROOT'].'/cdn/data_' . $fileDestiny . '/';

        if(preg_match($this->_regex_image, strtolower($chooseName)))
        {
            $this->exec($directory, $chooseName, $decodedData, $checkSize);
            return $this;
        }
        else
        {
            $this->_return = false;
            return $this;
        }
    }

    public function printable($bool=false)
    {
        if(is_string($this->_return) || is_numeric($this->_return))
        {

            if(!class_exists('jsonHandler')) parent::_library(array('jsonHandler'));
            if(is_numeric($this->_return))
            {
                $resp = array(
                    'approve'=>false, 
                    'message'=>'Allow size max '.$this->_return.' & is not square'
                );
            }
            else
            {
                $resp = array(
                    'approve'=>true, 
                    'message'=>$this->_lang['global']['success'],
                    'data'=>$this->_return
                );
            }
            $return =  parent::_return('Upload', $this->_upload_purpose, $resp);
        }
        else
        {
            $return = _error(null, 'Something Wrong', 500);
        }

        if($bool == true) { echo $return; } else { return $this->_return; };
    }
    
    protected function exec($directory, $chooseName, $data, $check_size)
    {
        if(!file_exists($directory))
        {
            $this->_return = false;
            return $this;
        }
        else
        {
            $width = $check_size[0];
            $height = $check_size[1];
            if($width > $this->_max_size && $height > $this->_max_size)
            {
                $this->_return = $this->_max_size;
                return $this;
            }
            elseif($this->_upload_purpose != 'banner' && $width > $height + 0.50 || $height > $width + 0.50)
            {
                $this->_return = $this->_max_size;
                return $this;
            }
            else
            {
                try 
                {
                    $doUpload = file_put_contents($directory . '/' . $chooseName, $data);
                    if($doUpload)
                    {
                        // Handle replace foto pada saat request
                        if(property_exists($this->_param, 'replace'))
                        {
                            $fileNameReplace = strip_tags($this->_param->replace);
                            parent::_handler('file', array(
                                'filename'=>$fileNameReplace)
                            )->removeFiles($this->_upload_purpose);
                        }

                        $this->_return = $chooseName;
                        return $this;
                    }
                    else
                    {
                        $this->_return = false;
                        return $this;
                    }
                }
                catch(Exception $e)
                {
                    $this->_return = null;
                    return $this;
                }
            }
        }
    }
    
    protected function getimagesizefromstring($data)
    {
        $uri = 'data://application/octet-stream;base64,' . $data;
        return getimagesize($uri);
    }
}