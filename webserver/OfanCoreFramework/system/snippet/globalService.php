<?php
class GlobalService extends OfanCoreFramework
{
	public static function arrayWhereInLoopReformat($param)
	{
		parent::_snippet(array('arrayMerge'));

		$reformatData = $param['data'];
		$getArrayPush = '';
		for($i = 0; $i < count($param['data']); $i++)
		{
			$getArrayPush[] = $param['data'][$i][$param['cellGrab']];
			unset($reformatData[$i][$param['cellUnset']]);
		}

		$finalPush = _proposeCrudServices($param['database']['cluster'])->getDataWhereIn($param['database']['table'], $param['database']['select'], array($param['database']['where'], array_unique($getArrayPush)));

		// Fixed duplicate value from multidimension array
		$reformatDataFixed = array_map("unserialize", array_unique(array_map("serialize", $reformatData)));
		return my_array_merge($reformatDataFixed, $finalPush);
	}


	/** 
	 * Mengambil Database lain setelah mencocokan dengan ID 
	 */
	public static function matchRelation($cluster, $table, $select, $where, $withIN=false)
	{
		$connect = function_exists('_proposeCrudServices') ? _proposeCrudServices($cluster) : null;

		if($withIN === false)
		{
			$DB = is_null($connect) ? false : $connect->getDataWhere($table, $select, $where);
		}
		else
		{
			$DB =  is_null($connect) ? false : $connect->getDataWhereIn($table, $select, $where);
		}
		
		if(!$DB) return false;
		
		$return = is_array($select) ? $DB : $DB[0][$select];
		return $return;
	}


	public static function avatarRandom()
	{
		$color = array('pink', 'purple', 'bluesea', 'greentea', 'orange');
		$suffleColor = shuffle($color);
		$prefix = 'avatar-';
		$suffix = '.svg';
		return $prefix.($color[$suffleColor]).$suffix;
	}
}

function GLOBAL_SERVICE_CALL($array=array())
{
	return call_user_func_array(array('GlobalService', $array['function']), $array['param']);
}
?>