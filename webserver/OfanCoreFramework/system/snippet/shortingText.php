<?php if(!defined('_thisFileDIR')) header('Location:..');
function shortingText($string, $limit=500)
{
	$string = strip_tags($string);
	if (strlen($string) > $limit) 
	{
	    // truncate string
	    $stringCut = substr($string, 0, $limit);
	    // make sure it ends in a word so assassinate doesn't become ass...
	    $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
	}
	
	return $string;
}

function discount($price_ori, $price_sale)
{
	if(intval($price_sale) > 0)
	{
		$formula = ((intval($price_ori) / intval($price_sale)) * 10);
		return intval(number_format($formula, 1));
	}
	else
	{
		return 0;
	}
}
?>