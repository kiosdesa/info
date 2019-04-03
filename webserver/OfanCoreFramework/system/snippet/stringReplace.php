<?php
function reformatArrayingIntegerDB($data)
{
	$find = array('/\{([\w\,]+)\}/');
	return json_decode(preg_replace($find, array('[$1]'), $data), true);
}

function replaceTextOldToNew($old, $new, $marked=false)
{
    $from_start = strspn($old ^ $new, "\0");        
    $from_end = strspn(strrev($old) ^ strrev($new), "\0");

    $old_end = strlen($old) - $from_end;
    $new_end = strlen($new) - $from_end;

    $start = substr($new, 0, $from_start);
    $end = substr($new, $new_end);
    $new_diff = substr($new, $from_start, $new_end - $from_start);  
    $old_diff = substr($old, $from_start, $old_end - $from_start);

	if($marked == true)
	{
		$new = "$start<ins style='background-color:#ccffcc'>$new_diff</ins>$end";
		$old = "$start<del style='background-color:#ffcccc'>$old_diff</del>$end";
	}
	else
	{
		$new = $start.$new_diff.$end;
		$old = $start.$old_diff.$end;
	}

    return array("old"=>$old, "new"=>$new);
}
?>