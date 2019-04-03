<?php if(!defined('_thisFileDIR')) header('Location:..');

class jsonHandler extends OfanCoreFramework
{
	/**
	 * headerJSON() Fungsi penetapan nilai informasi HEADER XHR
	 * @param $section - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	public function headerJSON($named=false)
	{
		$namedFileJSON = $named ? (md5(strtolower($named))) : 'error';
		$codeHeader = $named ? array('HTTP/1.1 200 OK', 200) : array('HTTP/1.1 500 Internal Server Error', 500);
		@header("Access-Control-Allow-Headers: Content-Type");
		@header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
        @header("Access-Control-Allow-Origin: *");
		//@header("Access-Control-Allow-Origin: "._thisDomain);
		@header('Content-Type: application/json; charset=utf-8');
		@header('Content-Disposition: inline; filename="'.$namedFileJSON.'.json"');
		@header($codeHeader[0], true, $codeHeader[1]);
	}



	/**
	 * statusHeader() Fungsi penetapan nilai status Header Response JSON
	 * @return array()
	 * @author OFAN @since 2017 @version 1.0.0
	 */
	public function statusHeader()
	{
		//$this->_lang = parent::lang();
		return array('status'=>array('name'=>'ok','code'=>200,'copyright'=>_thisCOPYRIGHT));
	}



	/**
	 * callbackFormatStatusOK() Fungsi pemformatan ulang ketika semua request DATA Json sesuai dengan ketentuan
	 * @param $data - Nilai ID server yg digunakan (alias untuk ID Google Spreadsheet)
	 * @param $section - Nilai ID Cluster Data yang digunakan (alias untuk worksheet Google Spreadsheeet)
	 * @param $callback - Nilai untuk memanggil fungsi pemformatan data JSON dan di masukan ke dalam index JSON
	 * ---------------------------------------------------------
	 * @return json_encode()
	 * @author OFAN @since 2017 @version 1 (dugunakan di file: 'api/webservices/library/reformatJSON.php')
	 */
	public function formatStatusOK($data, $named, $callback=null)
	{
		$reformat_rows_object_root = array(
			$callback=>array_merge($this->statusHeader(), array('items'=>$data))
		);

		$this->headerJSON($named);
		return json_encode($reformat_rows_object_root);
	}
}
?>