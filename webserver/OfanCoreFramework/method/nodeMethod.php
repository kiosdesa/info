<?php
require_once('../system/error.php');
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
{
    if(isset($_SERVER['HTTP_ORIGIN']))
    {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
    {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
 
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
 
        exit(0);
    }

    $postdata = @file_get_contents("php://input");
    //var_dump(parse_str(urldecode($postdata), $output), $output, json_decode($output['pack']));
    if($postdata)
    {
        /* @require Memasukan library ajax system terpisah sesuai kebutuhan */
        require_once('../load.php');

        if(class_exists('OfanCoreFramework'))
        {
            OfanCoreFramework::_library(array('ajaxHandler'));
            /* @set Header content type untuk format output HTML */
            @header("X-Robots-Tag: noindex");
            //@header("Access-Control-Allow-Headers: Content-Type");
            @header("Access-Control-Allow-Methods: POST");
            //@header("Access-Control-Allow-Origin: *");
            //@header("Access-Control-Allow-Origin: "._thisAllowDomain);
            @header("X-Powered-By: "._this_X_Powered);
            /**
             * @since v.2.0
             * Global parameter GET/POST method for AJAX Request function
             * Semua parameter di metode form atau XHTTP akan dikirm ke $AjaxHandler->_Process()
             * Dengan parameter fungsi:
             * $method (alias param -> init) = untuk type pemanggilan class, fungsi & MVC
             * $params (alias param -> pack) = data input/parameter ajax dalam bentuk array
             * $filenameClass (alias param -> call) = Jika nama method dan nama file class berbeda
             */
            parse_str(urldecode($postdata), $request);
            $method = isset($request['init']) ? $request['init'] : null;
            $params = isset($request['pack']) ? $request['pack'] : null;
            $filenameClass = isset($request['call']) ? $request['call'] : null;
            //var_dump($method, $params, $filenameClass);
            return OfanCoreFramework::_handler('ajax')->_Process($method, $params, $filenameClass);
        }
        else
        {
            echo _error(null, 'Library Core is NULL [:ndx]', 401);
        }
    }
    else
    {
        /* Jika tidak ada GLOBAL method POST/maka akan di alihkan ke errorHandler */
        echo _error(null, ' Error POST/No Method Defined [:ndx]', 401);
    }
}
else
{
    /* Jika bukan XHTTP Request di alihkan ke errorHandler */
    echo _error(null, 'Denied Access Method Level III [:ndx]', 405);
}
?>