<?php
require_once('../system/error.php');
if($_SERVER['REQUEST_METHOD'] !== 'POST') die(_error(null, 'Denied Access Method Level I [:ajx]', 405));
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
{
    if(isset($_POST['init']))
    {
        /* @require Memasukan library ajax system terpisah sesuai kebutuhan */
        require_once('../load.php');
        if(in_array($_SERVER['HTTP_X_REQUESTED_WITH'], unserialize(_thisAllowRequest)) == false) die(_error(null, 'Denied Access Method Level II [:ajx]', 405));

        if(class_exists('OfanCoreFramework'))
        {
            OfanCoreFramework::_library(array('ajaxHandler'));
            /* @set Header content type untuk format output HTML */
            @header("X-Robots-Tag: noindex");
            @header("Access-Control-Allow-Headers: Content-Type");
            @header("Access-Control-Allow-Methods: POST");
            @header("Access-Control-Allow-Origin: *");
            //@header("Access-Control-Allow-Origin: "._thisAllowDomain);
            @header("X-Powered-By: "._this_X_Powered);
            /**
             * @since v.2.0
             * Global parameter GET/POST method for AJAX Request function
             * Semua parameter di metode form atau XHTTP akan dikirm ke $AjaxHandler->_Process()
             * Dengan parameter fungsi:
             * $method = untuk type pemanggilan class, fungsi & MVC
             * $params = data input/parameter ajax dalam bentuk array
             */
            $method = $_POST['init'];
            $params = isset($_POST['pack']) ? $_POST['pack'] : null;
            $filenameClass = isset($_POST['call']) ? $_POST['call'] : null;
            return OfanCoreFramework::_handler('ajax')->_Process($method, $params, $filenameClass);
        }
        else
        {
            echo _error(null, 'Library Core is NULL [:ajx]', 401);
        }
    }
    else
    {
        /* Jika tidak ada GLOBAL method POST/maka akan di alihkan ke errorHandler */
        echo _error(null, ' Error POST/No Method Defined [:ajx]', 401);
    }
}
else
{
    /* Jika bukan XHTTP Request di alihkan ke errorHandler */
    echo _error(null, 'Denied Access Method Level III [:ajx]', 405);
}
?>