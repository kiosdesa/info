<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Class Method Static OfanCoreFramework
 * Adalah Core level 3 sebagai pengelola setelah autoload pada system Web Service
 * 
 * Nama System: OfanCoreFramework
 * Nama Class: OfanCoreFramework
 * Constructor @param tidak ada
 * @author OFAN
 * @since 2018
 * @version 1.0
 * @copyright GNU & GPL license
 */
class OfanCoreFramework
{
	protected static function _loadDatabaseConfig()
	{
		return unserialize(_configDB);
	}


	protected static function _loadUserConfig()
	{
		return unserialize(_configUSER);
	}


	protected static function _languageConfig()
	{
		return unserialize(_configLANG);
	}


	protected static function _thisDomain()
	{
		return _thisDomain;
	}


	protected static function _apiDomain()
	{
		return _apiDomain;
	}


	protected static function _cdnDomain()
	{
		return _cdnDomain;
	}


	protected static function _thisPackage()
	{
		return _thisPackage;
	}


	protected static function _cdnDirectoryBanner()
	{
		return _cdnDirectoryBanner;
	}


	protected static function _cdnDirectoryIcon()
	{
		return _cdnDirectoryIcon;
	}


	protected static function _cdnDirectorySeller()
	{
		return _cdnDirectorySeller;
	}


	protected static function _cdnDirectoryUser()
	{
		return _cdnDirectoryUser;
	}


	protected static function _cdnDirectoryProduct()
	{
		return _cdnDirectoryProduct;
	}
	

	// Menmasukan atau Include file PHP
	private static function LoaderFilePHP($array=array(), $path=null)
	{
		$path = is_null($path) ? '' : $path.'/';
		if(is_array($array))
		{
			foreach($array as $urutan => $namaFile)
			{
				$filename = $path.$namaFile.'.php';
				if(!file_exists($filename)) die(_error(null, 'Error Method Files System Not Loaded & Return Undefined', 500));
				include($filename);
			}
		}
		else
		{
			return false;
		}
	}


	// Memanggil Class yang ada di file Library dengan metode detil new class()
	protected static function _proposeClass($classname=null, $param=null)
	{
		if(is_null($classname)) return false;
		// Join is handling naming class from array like array('SomeClass', 'Handler')
		$namedClass = is_array($classname) ? join('', $classname) : $classname;
		if(!class_exists($namedClass)) return false;
		return new $namedClass($param);
	}


	// Memanggil Class yang ada di file Library dengan metode penyederhanaan _handler()
	public static function _handler($classNamePrefix=null, $param=null)
	{
		return self::_proposeClass($classNamePrefix.'Handler', $param);
	}


	// Mengambil atau Import file function snippet
	public static function _snippet($array=array())
	{
		self::LoaderFilePHP($array, _thisSnippetDIR);
	}


	// Mengambil atau Import file Class Library
	public static function _library($array=array())
	{
		self::LoaderFilePHP($array, _thisLibraryDIR);
	}


	public static function lang()
	{
		$lang = self::_languageConfig();
		return $lang['lang']['flag_id'];
	}


	// Menghitung jumlah data dalam table lengkap dengan kondisi/where
	protected static function _count($cluster, $table, $dateRow, $where=array())
	{
		return self::_handler('crud', $cluster)->count($table, $dateRow, $where);
	}


	// Mencari hak akses pengguna sesuai data di .config dan di return boolean
	public static function _access($type=null, $userLevel=null)
	{
		$suffix = '_access';
		$userConfig = self::_loadUserConfig();
		$userLevel = isset($userLevel[0]['user_level']) ? $userLevel[0]['user_level'] : (
			isset($userLevel[0]['level']) ? $userLevel[0]['level'] : $userLevel
		);

		$checkin = in_array($userLevel, $userConfig[$type.$suffix]);
		//$preReturn = $checkin == true ? true : false;
		return $checkin;
	}


	// Memanggil Class JSON untuk format output API
	public static function _return($cl=null,$fn=null,$db=null)
	{
		if($db)
		{
			$sectionName = array($fn, $cl);
			return self::_handler('json')->formatStatusOK($db, strtolower(join($sectionName,'-')), join($sectionName,''));
		}
		else
		{
			return false;
		}
	}


	// Generate kode
	public static function _generate($type=null, $param=null, $import=true)
	{
		if(!class_exists('generatorHandler'))
		{
			self::_library(array('generatorHandler'));
		}

		$Generator = self::_handler('generator');
		//var_dump($Generator);
		switch($type)
		{
			case 'id':
				$param['initial'] = $Generator->getFirstLetterWords($param['initial']);
				$param['suffix'] = $Generator->addZeroBefore(6, $param['suffix'].$param['prefix']);
				$return = $param['initial'].$param['suffix'];
			break;
			case 'sku':
				$param['initial'] = $Generator->getFirstLetterWords($param['initial']);
				$param['suffix'] = $Generator->addZeroBefore(3, $param['suffix'].$param['prefix']);
				$return = $param['initial'].$param['suffix'];
			break;
			case 'avatar':
				$return = $Generator->avatarRandom();
			break;
			case 'bannerseller':
				$return = $Generator->bannerSellerRandom();
			break;
			case 'tokencart':
				$return = $Generator->generatorTokenCart($param);
			break;
			case 'invoice':
				$return = $Generator->generatorInvoiceCode($param);
			break;
			default:
				$return = false;
			break;
		}

		return $return;
	}

	// Mencocokan nilai database sesuai relasi yang sudah dintentukan
	public static function _relation($dataReference=null, $dataFind=null, $withIN=true, $merge=false)
	{
		if(!function_exists('ArrayMergeIndexValueDB'))
		{
			if($merge == true)
			{
				import_snippet('arrayMerge');
			}
		}

		$cluster = $dataFind[0];
		$table = $dataFind[1];
		$select = $dataFind[2];
		$where = $dataFind[3];
		if($merge == true)
		{
			$where[1] = array_filter($where[1]);
			$where[1] = count($where[1]) > 0 ? $where[1] : array(0);
		}
		
		$connect = self::_handler('crud', $cluster);
		if($withIN == true)
		{
			$DB = $connect->getDataWhereIn($table, $select, $where);
		}
		else
		{
			$DB = $connect->getDataWhere($table, $select, $where);
		}
		
		if(!$DB) return false;
		$DB = is_array($select) ? $DB : (is_null($select) ? $DB[0] : $DB[0][$select]);
		if($merge == true)
		{
			$source = $dataReference[0];
			$indexMatch = $dataReference[1];
			$return[0] = ArrayMergeIndexValueDB($DB, $source, $indexMatch);
			//var_dump($return);
		}
		else
		{
			$return = $DB;
		}

		return $return;
	}


	// Rekonstruksi fungsi unserialize() jika terjadi error
	public static function fixunserialize($data=null)
	{
		if(is_null($data)) return false;
		return unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $data));
	}


	// Melakukan unserialize() data dengan pengulangan/looping
	public static function _loopunserialize($db=null, $index=null)
	{
		if(is_null($db)) return false;
		if(is_null($index)) return false;
		for($i=0;$i<count($db);$i++)
		{
			if(is_array($index))
			{
				if(isset($index['parent']) && isset($index['sub']))
				{
					$db[$i][$index['parent']][$index['sub']] = unserialize($db[$i][$index['parent']][$index['sub']]);
				}
				else
				{
					foreach($index as $k=>$v)
					{
						$db[$i][$v] = unserialize($db[$i][$v]);
					}
				}
			}
			else
			{
				$db[$i][$index] = unserialize($db[$i][$index]);
			}
		}
		return $db;
	}


	// Mengubah hitungan dan simbol nilai mata uang sesuai language/bahasa yang dipilih
	public static function _kurs($data)
	{
		$_lang = self::_languageConfig();
		$curs = $_lang['lang']['currency']['curs'];
		$curs = ((intval($data) * intval($curs[0])) / intval($curs[1]));
		return $_lang['lang']['currency']['symbol'].number_format($curs,0,',','.');
	}


	// Mengubah hitungan dan simbol nilai mata uang sesuai language/bahasa yang dipilih dengan pengulangan/loop
	public static function _loopkurs($db=null, $index=null, $keeporiginal=false)
	{
		if(is_null($db)) return false;
		if(is_null($index)) return false;
		for($i=0;$i<count($db);$i++)
		{
			if(is_array($index))
			{
				if(isset($index['rootparent']) && isset($index['rootsub']))
				{
					for($si=0;$si<count($db[$i][$index['rootparent']]);$si++)
					{
						if($keeporiginal == true)
						{
							$db[$i][$index['rootparent']][$si][$index['rootsub'].'_origin'] = (int)$db[$i][$index['rootparent']][$si][$index['rootsub']];
						}
						$db[$i][$index['rootparent']][$si][$index['rootsub']] = self::_kurs($db[$i][$index['rootparent']][$si][$index['rootsub']]);
					}
				}
				elseif(isset($index['parent']) && isset($index['sub']))
				{
					if($keeporiginal == true)
					{
						$db[$i][$index['parent']][$si][$index['sub'].'_origin'] = (int)$db[$i][$index['parent']][$si][$index['sub']];
					}
					$db[$i][$index['parent']][$index['sub']] = self::_kurs($db[$i][$index['parent']][$index['sub']]);
				}
				else
				{
					foreach($index as $k=>$v)
					{
						if($keeporiginal == true)
						{
							$db[$i][$v.'_origin'] = (int)$db[$i][$v];
						}
						$db[$i][$v] = self::_kurs($db[$i][$v]);
					}
				}
			}
			else
			{
				if($keeporiginal == true)
				{
					$db[$i][$index.'_origin'] = (int)$db[$i][$index];
				}
				$db[$i][$index] = self::_kurs($db[$i][$index]);
			}
		}
		return $db;
	}


	public static function _calculateprice($data, $compareValueIndex=array(), $multipleValueIndex=array())
	{
		if(is_null($data)) return false;
		$allPrice = [];
		for($i=0;$i<count($data);$i++)
		{
			if(is_array($compareValueIndex) && count($compareValueIndex) == 2)
			{
				if($data[$i][$compareValueIndex[0]] > $data[$i][$compareValueIndex[1]])
				{
					if(is_array($multipleValueIndex) && count($multipleValueIndex) == 2)
					{
						// Kalikan adalah nilai perkalian jika jenis angka maka tak usah ngambil dari looping
						// Kalo string maka nilai ngambil dari nilai index
						$kalikan = is_numeric($multipleValueIndex[1]) ? $multipleValueIndex[1] : $data[$i][$multipleValueIndex[1]];
						$calculate = ($data[$i][$multipleValueIndex[0]] * $kalikan);
						array_push($allPrice, $calculate);
					}
				}
			}
			else
			{
				if(is_array($multipleValueIndex) && count($multipleValueIndex) == 2)
				{
					$calculate = ($data[$i][$multipleValueIndex[0]] * $data[$i][$multipleValueIndex[1]]);
					array_push($allPrice, $calculate);
				}
			}
		}		
		return array_sum($allPrice);
	}


	/*
	 * Merubah nama index teratas pada loop array
	 * Menjadi nama nilai yang ditentukan di index lain atau sub-index lain dalam array
	 * Sesuai urutan looping nya
	 */
	public static function _grabtoindextop($param, $index=array())
	{
		//var_dump($param, $index);
		if(!is_array($index)) return false;
		if(count($index) < 1) return false;

		$i_A = isset($index['index']) ? $index['index'] : null;
		if(is_null($i_A)) return false;

		$return = [];
		for($i=0;$i<count($param);$i++)
		{
			$var = $param[$i][$i_A];
			unset($param[$i][$i_A]);
			$return[$var] = $param[$i];
		}
		return $return;
	}
}
?>