<?php
function my_array_merge(&$array1, &$array2) {
    $result = Array();
    foreach($array1 as $key => &$value)
    {
        $result[$key] = array_merge($value, $array2[$key]);
    }
    return array_filter($result);
}

function ArrayMerge($array1, $array2)
{
	return my_array_merge($array1, $array2);
}

function my_array_keyexist(&$array1, &$array2)
{
	$array3 = array();
	foreach($array1 as $k=>$v)
	{
	    if(array_key_exists($k, $array2))
	    {
	        $array3[$k] = array($v, $array2[$k]);
	    }
		else
		{
		    $array3[$k] = array($v, null);
		}
	}

	return $array3;
}

function ArrayMergeIndexValueDB($reference=null, $source=null, $indexMatch=null, $keepSource=false)
{
	if(is_null($reference)) return false;
	if(is_null($source)) return false;
	if(is_null($indexMatch)) return false;

	/* Variable Push Array User Reformat Indexing to Number Loop */
	$referencePush = [];
	for($i=0;$i<count($reference);$i++)
	{
		$referencePush[$i] = $reference[$i][$indexMatch];
	}

	/* Reformat Value Data Source */
	foreach($source as $k => $v)
	{
		if(strpos($k, $indexMatch) !== false)
		{
			for($i=0;$i<count($referencePush);$i++)
			{
				if($keepSource == false)
				{
					$source[$k] = $source[$k] == $referencePush[$i] ? $reference[$i] : $source[$k];
					$source[$k] = $source[$k] == 0 ? null : $source[$k];
				}
				else
				{
					$source['origin_'.$k] = $source[$k];
					$source[$k] = $source[$k] == $referencePush[$i] ? $reference[$i] : $source[$k];
					$source[$k] = $source[$k] == 0 ? null : $source[$k];
				}
			}
		}
	}
	return $source;
}

function getLoopValueFromOneIndexArray($param)
{
	$reformatData = $param['data'];
	$getArrayPush = '';
	for($i = 0; $i < count($param['data']); $i++)
	{
		$getArrayPush[] = $param['data'][$i][$param['cellGrab']];
		if(isset($param['cellUnset'])) unset($reformatData[$i][$param['cellUnset']]);
	}

	return $getArrayPush;
}
?>