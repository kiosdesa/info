<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * CacheHandler Class
 * Menangani proses penyimpanan cache berupa file atau database
 * Metode caching dibagi menjadi dua jenis:
 * 1. Type save cache File (berupa json/xml/text)
 * 2. Type save cache Database (MySQL)
 *
 * Berisi metode dari implementasi CRUD, yaitu:
 * Create (save cache), Read (check cache), Update (merge dengan save cache), Delete (drop cache)
 *
 * @author Ofan Ebob
 * @since 2014 (v.1)
 * @copyright GNU & GPL license
 */
class CacheHandler extends OfanCoreFramework
{
	protected static $_args;
	protected static $_cache_expire;
	protected static $_cache_prefix;
	protected static $_cache_name;
	protected static $_cache_id;

	const CACHE_DIR = 'cache';

	function __construct()
	{
		$this->_cache_expire = strtotime('+5 Day');
		$this->_cache_prefix = 'emall';
		$this->_cache_id = md5(date('Y-m-d h:i:s'));
	}



	private static function touchFile($data, $fileToFolder, $cache_expire)
	{
		if(is_null($data))
		{
			return array(false, 'message'=>'Pengambilan Data JSON Tidak Valid');
		}
		else
		{
			if($fopen = fopen($fileToFolder, 'w+'))
			{
				fwrite($fopen, $data);
				fclose($fopen);
			}

			//chmod($cache_dir, 0777);

			/* @PHP rubah meta time nya */
			touch($fileToFolder, $cache_expire);

			/* @return Definisikan nilai $data */
			return array(true, $data);
		}
	}



	/**
	 * save()
	 * Fungsi akses publik setelah semua proses permintaan data
 	 * @throws CacheHandlerException
 	 * @return $save
	 */
	public static function save($args=false)
	{
		/* @var Memberikan nilai default $_args pada konstruksi */
		self::$_args = is_array($args) ? $args : null;

		/* @var Mengembalikan nilai default $_args dari konstruksi
		   ke variable $argum difungsi save */
		$argum = self::$_args;

		if($argum == null)
		{
			/* @throw Jika nilai parameter $argum NULL atau tidak didefinisikan */
			return array(false, 'message'=>'Parameter Cache Tidak Valid [cache level 1]');
		}
		else
		{
			/* @var Menentukan nilai cache_prefix default jika index tidak ada di $argum */
			$cache_prefix = isset($argum['cache_prefix']) ? $argum['cache_prefix'] : self::$_cache_prefix;

			/* @var Menentukan nilai cache_id default jika index tidak ada di $argum */
			$cache_id = isset($argum['cache_id']) ? $argum['cache_id'] : self::$_cache_id;
			//$cache_id = preg_replace('/ /','',$cache_id);

			/* @var Menentukan nilai cache_expire untuk konstruktor */
			self::$_cache_expire = isset($argum['cache_expire']) ? $argum['cache_expire'] : self::$_cache_expire;

			/* @var Menentukan nilai cache_name untuk konstruktor */
			self::$_cache_name = $cache_prefix.'-'.md5(strtolower($cache_id));

			$save = self::_CacheToFile();

			return $save;
		}
	}


	/**
	 * _CacheToFile()
	 * Fungsi menangani penyimpanan cache berupa file
 	 * @return $cache OR NULL
	 */
	protected static function _CacheToFile()
	{
		/* @var Menentukan nilai default $argum dari nilai $_args di konstruksi */
		$argum = self::$_args;

		/* @cond Jika nilai $argum NULL maka dikembalikan ke nilai $argum */
		if($argum == null)
		{
			return $argum;
		}
		else
		{
			/* @var Menentukan nilai cache_name dari nilai $_cache_name di konstruksi */
			$cache_name = self::$_cache_name;

			/* @var Menentukan nilai cache_expire dari nilai $_cache_expire di konstruksi */
			$cache_expire = self::$_cache_expire;

			/* @var Menentukan nilai basepath jika _thisDIR tidak di define di konfigurasi */
			$DIRECTORY = defined('_thisDIR') ? _thisDIR : dirname(__FILE__).'/../';

			$cache_dir = isset($argum['cache_dir']) ? $argum['cache_dir'] : $DIRECTORY.'/'.self::CACHE_DIR;

			$callback_function = isset($argum['callback_function']) ? 
				(is_array($argum['callback_function']) ? $argum['callback_function'] : null) : null;

			$merge_files = isset($argum['with_merge_files']) ? 
				(is_string($argum['with_merge_files']) ? 
					(strlen($argum['with_merge_files']) > 0 ? 
						$argum['with_merge_files'] : null) : null) : null;

			$cacheble = isset($argum['cacheble']) ? $argum['cacheble'] : null;

			$url = isset($argum['cache_url']) ? $argum['cache_url'] : null;

			$stored = $cache_dir.'/'.$cache_name;

			/* @cond Buat file cache baru jika file tidak ditemukan di direktori */
			if( file_exists($stored) AND (filemtime($stored) > strtotime('now')) AND !is_null($cacheble) )
			{
				/* @return Nilai data dari lokal file cache */
				$localJSON = @file_get_contents($stored);
				return array(true, $localJSON);
			}
			else
			{
				if(is_null($url))
				{
					$dari_url = is_null($url) ? 'Dari URL' : '';
					return array(false, 'message'=>'URL atau Parameter API Tidak Valid '.$dari_url.' [cache level 2]');
				}
				else
				{
					$cache = @file_get_contents($url);

					if(strlen($cache) > 0 OR $cache !== false)
					{
						if(is_null($callback_function))
						{
							$thisCache = $cache;
						}
						else
						{
							$callbackParams = is_null($merge_files) ? 
								$callback_function['param'] : array_merge(array($merge_files=>$cache), $callback_function['param']);
							
							$thisCache = call_user_func_array($callback_function['method'], $callbackParams);
						}

						if(is_null($thisCache))
						{
							return array(false, 'message'=>'Pengambilan Data JSON Tidak Valid [cache level 3]');
						}
						else
						{
							$returnCache = is_null($cacheble) ? 
										array(true, $thisCache) : self::touchFile($thisCache, $stored, $cache_expire);

							return $returnCache;
						}
					}
					else
					{
						/* @cond Jika proses cURL gagal maka sistem kembali mengambil data lokal */
						if( file_exists($stored) )
						{
							/* @return Nilai data dari lokal file cache */
							$localJSON = @file_get_contents($stored);
							return array(true, $localJSON);
						}
						else
						{
							/* @return Jika file cache tidak ditemukan maka nilai NULL */
							return array(false, 'message'=>'Data API JSON Tidak Ditemukan [cache level 4]');
						}
					}
				}
			}
		}
	}



	/**
	 * drop()
	 * Fungsi akses publik untuk menghapus data cache
 	 * @throws CacheHandlerException
 	 * @return $cache
	 */
	public static function drop($args=false)
	{
		$argum = self::$_args;

		if($argum == null)
		{
			return null;
		}
		else
		{
			$cache_name = self::$_cache_name;

			$save = self::_DropCacheDatabase();

			if($save == null)
			{
				throw new CacheHandlerException('Failed Drop Cache.');
			}
			else
			{
				return true;
			}
		}
	}


	/**
	 * _DropCacheFile()
	 * Fungsi menangani penghapusan cache berupa file
 	 * @return true OR null
	 */
	protected static function _DropCacheFile()
	{
		$cache_name = self::$_cache_name;

		$DIRECTORY = defined('_thisDIR') ? _thisDIR : self::BASE_PATH;
		$cache_dir = $DIRECTORY.'/'.self::CACHE_DIR;
		$stored = $cache_dir.'/'.$cache_name.'.json';

		if( file_exists($stored) AND ( filemtime($stored) < strtotime('now') ) )
		{
			unlink($stored);
			return true;
		}
		else{
			return null;
		}
	}
}

/**
 * CacheHandlerException extends
 */
class CacheHandlerException extends Exception{}
?>