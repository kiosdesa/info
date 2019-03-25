<?php if(!defined('_thisFileDIR') AND !method_exists('CacheHandler', 'save')) header('Location:..');
class Sheet extends Modular
{
	/**
	 * get() Fungsi mengambil data JSON dari web service
	 * @param $server - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $worksheet - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * @param $limitdata - Nilai pembatas jumlah Looping data JSON
	 * @param $shortby - Nilai pengatur ulang susunan data JSON sesuai nama index yang ditentukan
	 * @param $cacheble - Nilai (boolan) untuk pengaturan penyimpanan data JSON
	 * ---------------------------------------------------------
	 * @return fixFormatJSON()
	 * @return jsonReturnError()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/googlesheetJson.php')
	 */
	public static function get($server, $worksheet, $callback, $limitdata=false, $shortby=false, $cacheble=true)
	{
		$cacheble = $cacheble ? ($cacheble == false ? null : $cacheble) : null;
		$shortby = $shortby ? ($shortby == false ? null : $shortby) : null;
		$limitdata = $limitdata ? ($limitdata == false ? null : $limitdata) : null;

		parent::_snippet(array('shortingText', 'arrayGroup', 'BreakSymbolArray', 'sheetRender'));

		//Merubah Nomor urut server kedalam kode/ID google spreadsheet kedalam URL
		$serverHandlerGet = serverHandler::_Get($server, $worksheet);

		$callbackFunction = array(
			'method'=>array('Sheet', 'reformatOnlineJSON'), 
			'param'=>array('limitdata'=>$limitdata, 'callback'=>$callback, 'serverCluster'=>array($worksheet, $server))
		);

		$file = CacheHandler::save(array(
				'callback_function'=>$callbackFunction,
				'with_merge_files'=>'files',
				'cacheble'=>$cacheble,
				'cache_expire'=>strtotime(_cacheTime), 
				'cache_prefix'=>'emallSheet', 
				'cache_id'=>$worksheet . (is_null($callback) ? '' : $callback) . $serverHandlerGet['sheetKey'],
				'cache_url'=>$serverHandlerGet['url'])
		);


		if( is_array($file) )
		{
			if($file[0] === true)
			{
				return self::fixFormatJSON($file[1], $serverHandlerGet['sheetKey'], $callback, $shortby);
			}
			else
			{
				return self::jsonReturnError($callback, $file['message']);
			}
		}
		else
		{
			return self::jsonReturnError(null, 'Entitas Data Array Tidak Valid');
		}
	}




	/**
	 * headerJSON() Fungsi penetapan nilai informasi HEADER XHR
	 * @param $spreadID - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	private static function headerJSON($spreadID=false)
	{
		$namedFileJSON = $spreadID ? (md5(strtolower($spreadID))) : 'error';
		$codeHeader = $spreadID ? array('HTTP/1.1 200 OK', 200) : array('HTTP/1.1 500 Internal Server Error', 500);

		header("Access-Control-Allow-Headers: Content-Type");
		header("Access-Control-Allow-Methods: GET, POST");
		header("Access-Control-Allow-Origin: "._thisDomain);
		header('Content-Type: application/json');
		header('Content-Disposition: inline; filename="'.$namedFileJSON.'.json"');
		header($codeHeader[0], true, $codeHeader[1]);
	}



	/**
	 * statusHeader() Fungsi penetapan nilai status Header Response JSON
	 * @return array()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	private static function statusHeader()
	{
		return array('status'=>array('name'=>'ok','code'=>200));
	}



	/**
	 * swicthFunctionCall() Fungsi memanggil fungsi lain untuk pemformatan ulang tahap kedua
	 * @param $data - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $limitdata - Nilai pembatas jumlah Looping data JSON
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * @param $serverCluster - Nilai ID & Worksheet Google Spreadsheet dalam bentuk array
	 * ---------------------------------------------------------
	 * @return call_user_func_array()
	 * @return null()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	private static function switchFunctionCall($data, $limitdata, $callback, $serverCluster)
	{
		$callback = $callback . 'SheetRender';
		if(function_exists($callback))
		{
			return call_user_func_array($callback, array($data, $limitdata, $serverCluster));
		}
		else
		{
			return null;
		}
	}



	/**
	 * callbackFormatStatusOK() Fungsi pemformatan ulang ketika semua request DATA Json sesuai dengan ketentuan
	 * @param $data - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $spreadID - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * ---------------------------------------------------------
	 * @return json_encode()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	private static function callbackFormatStatusOK($data, $spreadID, $callback=null)
	{
		$statusHeader = self::statusHeader();

		$reformat_rows_object_root = array(
			$callback=>array_merge(
				$statusHeader,
				array('items'=>$data)
			)
		);

		self::headerJSON($spreadID);
		return json_encode($reformat_rows_object_root);
	}



	/**
	 * jsonReturnError() Fungsi menangani kesalahan request DATA Json
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * @param $message - Nilai string untuk debuging format JSON ketika terjadi kesalahan
	 * ---------------------------------------------------------
	 * @return json_encode()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	private static function jsonReturnError($callback, $message = 'undifined resource')
	{
		$callback = (is_null($callback) or $callback == '' or strlen($callback) < 1) ? '$null' : $callback;
		$ErrorMessage = array($callback=>array('status'=>array('name'=>'error','code'=>500, 'message'=>$message)));
		self::headerJSON();
		return json_encode($ErrorMessage);
	}



	/**
	 * reformatOnlineJson() Fungsi format ulang pertama ketika file_get_contents di eksekusi
	 * @param $file - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $limitdata - Nilai pembatas jumlah Looping data JSON
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * @param $serverCluster - Nilai ID Cluster Data & Worksheet yang digunakan dalam bentuk array
	 * ---------------------------------------------------------
	 * @return switchFunctionCall()
	 * @return null atau @return json_encode()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	public static function reformatOnlineJSON($file, $limitdata, $callback, $serverCluster)
	{
		$json = json_decode($file);
		$data = $json->{'feed'}->{'entry'};
		$return = self::switchFunctionCall($data, $limitdata, $callback, $serverCluster);
		return (is_null($return)) ? null : json_encode($return);
	}



	/**
	 * fixFormatJSON() Fungsi mengambil data JSON dari web service
	 * @param $data - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $spreadID - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * @param $shortby - Nilai pengatur ulang susunan data JSON sesuai nama index yang ditentukan
	 * ---------------------------------------------------------
	 * @return callbackFormatStatusOK()
	 * @return jsonReturnError()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	protected static function fixFormatJSON($data, $spreadID, $callback, $shortby=null)
	{
		$dataDecode = json_decode($data, true);

		if(is_array($dataDecode))
		{
			// See Files: 'library/arrayGroup.php'
			$dataDecode = is_null($shortby) ? $dataDecode: array_group_by($dataDecode, $shortby);
			return self::callbackFormatStatusOK($dataDecode, $spreadID, $callback);
		}
		else
		{
			return self::jsonReturnError($callback, 'Format Data JSON Tidak Valid');
		}
	}
}
?>