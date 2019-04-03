<?php
function getFirstLetterWords($data)
{
    $words = preg_split("/[\s,_-]+/", $data);
    $acronym = "";

    foreach ($words as $w) {
        $acronym .= $w[0];
    }

    return strtoupper($acronym);
}

function addZeroBefore($totalZero=10, $data)
{
    //$countZero = ($totalZero - intval(strlen($data)));
    return str_pad($data, $totalZero, '0', STR_PAD_LEFT);
}

function avatarRandom()
{
    $color = array('pink', 'purple', 'bluesea', 'greentea', 'orange');
    $suffleColor = shuffle($color);
    $prefix = 'avatar-';
    $suffix = '.svg';
    return $prefix.($color[$suffleColor]).$suffix;
}

function bannerSellerRandom()
{
    $color = array('pink', 'purple', 'bluesea', 'greentea', 'orange');
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
function generatorTokenCart($data)
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
function generatorInvoiceCode($data)
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
    $initCodecart = is_array($data) ? join($data, '') : (is_string($data) ? $data : date('dhis'));
    $initCodecart = date('ym').$initCodecart;
    $prefix = isset($data['prefix']) ? $data['prefix'] : _thisBrand;
    $suffix = isset($param['suffix']) ? $param['suffix'] : 0;

    return ($prefix.$initCodecart.$random.$randomString.$suffix);
}
?>