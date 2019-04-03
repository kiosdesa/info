<?php if(!defined('_thisFileDIR')) header('Location:..');

function changeZeroNumberToIndoCode($data)
{
	return preg_replace('/^0?/', '62', $data);
}

function achorEmail($data=null)
{
	if(is_null($data)) return false;
	return '<a href="mailto:'.$data.'">'.$data.'</a>';
}

function achorWeb($data=null, $target="_self")
{
	if(is_null($data)) return false;
	return '<a href="http://'.$data.'" target="'.$target.'">'.$data.'</a>';
}

function achorWhatsapp($number=null, $text='', $target="_self", $withTag=true)
{
	if(is_null($number)) return false;
	$formatnumber = changeZeroNumberToIndoCode($number);
	$url = 'https://api.whatsapp.com/send?phone='.$formatnumber.'&text='.$text;
	return ($withTag == true ? '<a href="'.$url.'" target="'.$target.'">'.$number.'</a>' : $url);	
}

function achorPhone($data=null)
{
	if(is_null($data)) return false;
	return '<a href="tel:'.$data.'">'.$data.'</a>';	
}

function mentionPerson($data=null)
{}
?>