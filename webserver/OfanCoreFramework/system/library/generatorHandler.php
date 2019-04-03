<?php
class generatorHandler extends OfanCoreFramework
{
    private $_color;

    function __construct()
    {
        $this->_color = array('pink', 'purple', 'bluesea', 'greentea', 'orange');
        if(!class_exists('dateHandler'))
        {
            parent::_library(array('dateHandler'));
        }
    }

    public function getFirstLetterWords($data)
    {
        $regexI = "/[^\p{L}\p{N}\s]/u";
        $regexII = "/^\s*\w/";
        $regexIII = "/[\s,_-]+/";
        $data = preg_replace($regexI, '', $data); // Replace All Symbol
        $data = preg_replace($regexII, '', $data);
        $words = preg_split($regexIII, $data);
        $acronym = "";
        foreach($words as $w)
        {
            $acronym .= empty($w[0]) ? '' : $w[0];
        }
        //var_dump($acronym);die();
        return strtoupper($acronym);
    }

    public function addZeroBefore($totalZero=10, $data)
    {
        //$countZero = ($totalZero - intval(strlen($data)));
        return str_pad($data, $totalZero, '0', STR_PAD_LEFT);
    }

    public function avatarRandom()
    {
        $color = $this->_color;
        $suffleColor = shuffle($color);
        $prefix = 'avatar-';
        $suffix = '.svg';
        return $prefix.($color[$suffleColor]).$suffix;
    }

    public function bannerSellerRandom()
    {
        $color = $this->_color;
        $suffleColor = shuffle($color);
        $prefix = 'banner-seller-';
        $suffix = '.svg';
        return $prefix.($color[$suffleColor]).$suffix;
    }

    /**
     * @since v.1.0
     * Fungsi private
     * untuk menghasilkan kode acak yg digunakan pada invoice
     * Cara penggunaan fungsi:
     * generatorTokenCart(array('length'=>5,'id_buyer'=>1,'id_seller'=>2))
     */
    public function generatorTokenCart($data)
    {
        $data['length'] = isset($data['length']) ? $data['length'] : 5;
        $characters = 'ABCDEFGHIJKLMNOPQVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $data['length']; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $initCodecart = is_array($data) ? join($data, '|') : (is_string($data) ? $data : date('Y/m/d h:i:s'));
        $initCodecart = _thisBrand.'|'.$initCodecart;
        return md5($initCodecart.$randomString);
    }

    /**
     * @since v.1.0
     * Fungsi private
     * untuk menghasilkan kode acak yg digunakan pada invoice
     */
    public function generatorInvoiceCode($data)
    {
        $data['length'] = isset($data['length']) ? $data['length'] : 5;
        $characters = 'ABCDEFGHIJKLMNOPQVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $data['length']; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $random = rand(00000, 99999);
        unset($data['length']);
        $initCodecart = is_array($data) ? join($data, '') : (is_string($data) ? ($data = '' ? $random : $data ) : date('his'));
        $Year = parent::_handler('date')->romanNumerals(date('Y'));
        $MonthDay = date('md');
        $prefix = isset($data['prefix']) ? $data['prefix'] : $this->getFirstLetterWords(_thisBrand);
        return ($prefix.$initCodecart.'/'.$Year.'/'.$randomString.$MonthDay);
    }
}
?>