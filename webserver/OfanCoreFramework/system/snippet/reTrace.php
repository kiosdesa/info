<?php
function reTrace($param, $replacePrefix=null, $unset=null)
{
	$push = [];
	foreach($param['trace'] as $kunci=>$nilai)
	{
		$fieldName = isset($nilai['fieldlabel']) ? $nilai['fieldlabel'] : $nilai['field'];
		$field = is_null($replacePrefix) ? $fieldName : str_replace($replacePrefix, '', $fieldName);
		$push[$field] = $nilai['value'];
	}

	if(!is_null($unset))
	{
		foreach($unset as $isset)
		{
			unset($push[$isset]);
		}
	}

	return $push;
}
?>