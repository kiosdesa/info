<?php
require_once('../system/error.php');
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
{
    $fieldJSON = null;
    try {
        $fieldFormXHTTP = file_get_contents("php://input");
        $fieldJSON = json_decode($fieldFormXHTTP);
    }
    catch(Exeption $e)
    {
        echo _error(null, 'Problem Negotiable [:upl]', 500);die();
    }

    //if($fieldJSON === null && json_last_error() !== JSON_ERROR_NONE) echo _error(null, 'Problem Negotiable', 500); return false;die();
    
    $fileDestiny = isset($_REQUEST['section']) ? $_REQUEST['section'] : null;
    if(!in_array($fileDestiny, array('product','banner','profile','avatar')))
    {
        echo _error(null, 'Failed Identify Section [:upl]', 500);
        die();
    }

    require '../load.php';
    @header("X-Robots-Tag: noindex");
    @header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    @header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
    //@header("Access-Control-Allow-Origin: "._thisDomain." "._apiDomain);
    @header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    @header("X-Powered-By: "._this_X_Powered);

    if(class_exists('OfanCoreFramework'))
    {
        OfanCoreFramework::_library(array('uploadHandler'));
        if(method_exists('uploadHandler', 'upload') && is_callable(array('uploadHandler', 'upload')))
        {
            return OfanCoreFramework::_handler('upload', $fieldJSON)->upload($fileDestiny)->printable(true);
        }
        else
        {
            echo _error($callMethodSection, 'Section Method Failed Run [:upl]', 401);
        }
    }
    else
    {
        echo _error(null, 'Identify method failed [:upl]', 401);
    }
}
else
{
    echo _error(null, 'Error Method [:upl]', 403);
}
?>