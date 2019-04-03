<?php if(!defined('_thisFileDIR')) header('Location:..');

class cryptoHandler extends OfanCoreFramework
{
    private $_type;
    private $_encrypt;
    private $_decrypt;
    
	public function numhash($n)
	{
        $algoHexa = (((0x0000FFFF & $n) << 16) + ((0xFFFF0000 & $n) >> 16));
        if($this->_type == 'decrypt')
        {
            $this->_decrypt = $this->numhash($algoHexa);
        }
        else
        {
            $this->_encrypt = $algoHexa;
        }

        return $this;
	}

	public function numstring($str)
	{
        if($this->_type == 'decrypt')
        {
            $this->_decrypt = join(array_map('chr', str_split($numbers, 3)));
        }
        else
        {
            $this->_encrypt = join(array_map(function($n){ return sprintf('%03d', $n); }, unpack('C*', $str)));
        }

        return $this;
	}
    
    public function encrypt()
    {
        $this->_type == 'encrypt';
        return $this->_encrypt;
    }
    
    public function decrypt()
    {
        $this->_type == 'decrypt';
        return $this->_decrypt;
    }
}
?>