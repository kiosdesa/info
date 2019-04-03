<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * @author OFAN
 * @copyright 2014 - 2017
 * Fungsi AJAX Handler
 * Penyederhanaan code dengan mengganti beberapa fungsi standar kedalam MVC Library
 * Fungsi dasar berasal dari project ticket tracker (2013) yang dimodifikasi dari kode wordpress
 * Kemudian di tambahkan beberapa fungsi error handler dan dynamic return untuk format response
 * -----------------------------------------------------------------------------------------------------
 * Standar penggunaan file AjaxHandler.php harus dengan JavaScript Framework & module XHR Request/XHTTP
 * Beberapa parameter yang menjadi rumus baku adalah:
 * init = berupa object json berisi metode pada MVC
 * pack = nilai parameter untuk fungsi yg dipanggil
 * Kedua parameter harus dalam bentuk array
 */
class AjaxHandler extends OfanCoreFramework
{
    /**
     * @since v.2.0
     * Menangani AJAX Proses tahap kedua setelah GLOBAL Method
     * Kemudian akan di kirim ke fungsi responseHandler() jika berhasil
     * atau dikirim ke fungsi ajaxError() jika gagal
     */
    public function _Process($init=null, $params=null, $call=null)
    {
        parent::_snippet(array('arrayConvert'));
        $this->nocache_headers();

        /* Menetapkan variable $initTrim berdasakan isi dari variable $init
         * Jika nilai $init terdapat minus sign "-" maka class dan fungsi akan di explode dan dijadikan Array
         */
        $initTrim = preg_match('/\-/', $init) ? explode('-', $init) : $init;
        $class = is_array($initTrim) ? (array_key_exists(0, $initTrim) ? $initTrim[0] : null) : (is_null($call) ? null : strtolower($call));
        $function = is_array($initTrim) ? (array_key_exists(1, $initTrim) ? $initTrim[1] : null) : (is_null($init) ? null : $init);

        /* Urutan kondisi index pada XHR dibalik dari identifikasi FUNGSI kemudian ke CLASS
         * Ini dimaksudkan untuk meminimalisir ERROR + penulisan kode ketika XHR hanya memanggil function tanpa class
         */
        //var_dump($init);
        if($function == null)
        {
            echo _error(null, 'Error Function [:ajxh]', 401);
        }
        else
        {
            /**
             * $injectFilePHP adalah variable yang mendefinisikan nama file library untuk Class yang dipanggil di XHR
             * Beberapa flow kondisi yang di atur sehingga memudahkan untuk pemanggilan metode (class, function) pada XHR
             */
            $injectFilePHP = is_null($call) ? $class : (strtolower($call) == strtolower($class) ? $class : $call);
            if($injectFilePHP == null)
            {
                echo _error(null, 'Error Library is NULL [:ajxh]', 401);
            }
            else
            {
                /**
                 * Mendefinisikan nama files library untuk nama Class pada panggilan XHR
                 * Lihat di variable $injectFilePHP
                 */
                $theControllerFiles = _thisControllerDIR.strtolower($injectFilePHP).'.php';
                if(file_exists($theControllerFiles))
                {
                    /**
                     * Memanggil file library yang di gunakan di dalam Class pada XHR
                     * Misal kan memanggil class Cart {} (nama class)
                     * Maka files PHP yang memuat class tersebut akan dipanggil sesuai nama method
                     */
                    require_once($theControllerFiles);
                    if($class == null)
                    {
                        $instanceMethod = array('', $function);
                    }
                    else
                    {
                        $instanceMethod = array(ucfirst($class), $function);
                    }

                    /**
                     * Merubah jenis nilai pada parameter dari apapun menjadi Array
                     */
                    $paramSend = is_null($params) ? array('isNull') : ((is_object($params) ? ObjectToArray($params) : (is_array($params) ? $params : json_decode($params, true))));
                    /*if($params === false){echo _error(null, 'Error Parameter [:ajxh]', 401);}else{return $this->_ResponseHandler($instanceMethod,$params);}*/
                    return $this->_ResponseHandler($instanceMethod, $paramSend);
                }
                else
                {
                    echo _error(null, 'Error Load Library/No Exist [:ajxh]', 401);
                }
            }
        }
    }



    /**
     * @since v.2.0
     * Menangani AJAX Proses tahap kedua setelah $this->_Process()
     * Lebih mendetail karena akan memisahkan nilai dari parameter sesuai kebutuhan
     * Yaitu memisahkan nama Class, nama Function dan isi/nilai Parameter fungsi berupa data Array()
     * Kemudian akan di kirim ke fungsi _ReturnResponseHeader() jika berhasil
     * atau dibuat ErrorHandler() jika gagal
     */
    private function _ResponseHandler($method, $params)
    {
        $methodLabel = $method[1].$method[0];

        /* Cek ketersediaan nama Class */
        if(class_exists($method[0]))
        {
            /* Cek apakah fungsi di dalam Class bisa digunakan & dipanggil dengan fungsi lain */
            if(method_exists($method[0], $method[1]) && is_callable(array($method[0], $method[1])))
            {
                /* Menggunakan call_user_func_array untuk memanggil fungsi dalam Class beserta parameter yg dibutuhkan
                 * Nama Class dan Function masuk kedalam array()
                 * Parameter Function masuk kedalam array, variable $params adalah berisi data yang di berikan XHR
                 */
                $response = @call_user_func_array(array($method[0], $method[1]), $params);
                $this->_ReturnResponseHeader($response, $methodLabel);
            }
            else
            {
                echo _error(null, 'Error Method Class [:ajxh]', 401);
            }
        }
        else
        {
            /* Kondisi untuk memeriksa ketersediaan function tanpa Class */
            if(function_exists($method[1]))
            {
                $response = @call_user_func_array($method[1], $params);
                $this->_ReturnResponseHeader($response, $methodLabel);
            }
            /* Jika Function tidak ada maka respon akan dikirm ke ErrorHandler() */
            else
            {
                echo _error(null, 'Error Method Function [:ajxh]', 401);
            }
        }
    }


   /**
     * @since v.2.0
     * Menangani AJAX Proses Response output
     * dari fungsi _ResposneHandler()
     * Berisi nilai header (code & message) kemudian di return echo
     * atau return ErrorHandler() jika kondisi if else sudah berakhir
     */
    private function _ReturnResponseHeader($response, $method=null)
    {
        if(is_array($response))
        {
            header("Content-Type: application/json; charset=utf-8");
            header("HTTP/1.0 200 Data Array/JSON");
            echo json_encode($response);
        }
        elseif(is_object($response))
        {
            header("Content-Type: application/json; charset=utf-8");
            header("HTTP/1.0 200 Data Object/JSON");
            $responseObject = ObjectToArray($response);
            echo json_encode($responseObject);
        }
        elseif(json_decode($response))
        {
            header("Content-Type: application/json; charset=utf-8");
            header("HTTP/1.0 200 JSON/String/Integer from Decode");
            echo $response;
        }
        elseif(is_string($response))
        {
            header("Content-Type: text/html; charset=utf-8");
            header("HTTP/1.0 200 String");
            echo $response;
        }
        elseif(is_bool($response))
        {
            if($response === true)
            {
                echo parent::_hanlder('json', array('db'=>$response, 'named'=>'global-response', 'section'=>'global'));
            }
            else
            {
                echo _error($method, 'Please Dude! it\'s Confusing & False :p [:ajxh]', 403);
            }
        }
        elseif(is_null($response))
        {
            echo _error($method, 'Data is NULL [:ajxh]', 204);
        }
        else
        {
            echo _error($method, 'Error Format Result Response with Extends [:ajxh]', 401);
        }
    }



	/**
	 * @since v.1.0
	 * get_nocache_headers()
	 * Mengatasi cache pada header yang dikirm kepada client browser melalui ajax
	 * Fungsi ini digunakan untuk nocache_headers()
	 * @return $headers
	 */ 
	public function get_nocache_headers()
	{
		$headers = array(
			'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
			'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
			'Pragma' => 'no-cache',
		);

		$headers['Last-Modified'] = false;
		return $headers;
	}


	/**
	 * @since v.1.0
	 * nocache_headers()
	 * Mengatasi cache pada header yang dikirm kepada client browser melalui ajax
	 * @return headers()
	 */ 
	public function nocache_headers()
	{
		$headers = $this->get_nocache_headers();
		unset($headers['Last-Modified']);
		if(function_exists('header_remove'))
		{
			@header_remove('Last-Modified');
		}
		else
		{
			foreach(headers_list() as $header)
			{
				if(0 === stripos($header, 'Last-Modified' ))
				{
					$headers['Last-Modified'] = '';
					break;
				}
			}
		}

		foreach( $headers as $name => $field_value )
			@header("{$name}: {$field_value}");
	}
}
?>