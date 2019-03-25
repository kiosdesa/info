<?php
require_once('../system/error.php');
if($_SERVER['REQUEST_METHOD'] !== 'GET') die(_error(null, 'Denied Access Method Level I [:drx]', 405));
if(isset($_GET['section']))
{
    /* @require Memasukan library ajax system terpisah sesuai kebutuhan */
    require_once('../load.php');
    if(class_exists('OfanCoreFramework'))
    {
        @header("X-Robots-Tag: noindex");
        @header("Access-Control-Allow-Headers: Content-Type");
        @header("Access-Control-Allow-Methods: GET");
        @header("Access-Control-Allow-Origin: *");
        //@header("Access-Control-Allow-Origin: "._thisAllowDomain);
        @header("X-Powered-By: "._this_X_Powered);

        $section = isset($_GET['section']) ? $_GET['section'] : null;
        $parameters = array(
            'section'=>$section,
            'cluster'=>(isset($_GET['cluster']) ? $_GET['cluster'] : null),
            'filterType'=>(isset($_GET['filterType']) ? $_GET['filterType'] : null),
            'filter'=>(isset($_GET['filter']) ? $_GET['filter'] : (isset($_GET['code']) ? $_GET['code'] : null)),
            'lookup'=>(isset($_GET['lookup']) ? strtolower($_GET['lookup']) : null),
            'order'=>(isset($_GET['order']) ? $_GET['order'] : null),
            'limit'=>(isset($_GET['limit']) ? $_GET['limit'] : null)
        );

        $objectNameReturn = strtolower(is_null($parameters['filterType']) ? '' : $parameters['filterType']).(ucfirst($section));

        $loadController = _thisControllerDIR.strtolower($section).'.php';
        if(file_exists($loadController))
        {
            require_once($loadController);
            $callMethodSection = ucfirst($section);
            if(method_exists($callMethodSection, 'get') && is_callable(array($callMethodSection, 'get')))
            {
                $theGetReturn = call_user_func_array(array($callMethodSection, 'get'), $parameters);
                if($theGetReturn)
                {
                    echo $theGetReturn;
                }
                elseif(is_null($theGetReturn))
                {
                    echo _error($objectNameReturn, 'Data not found/NULL [:drx]', 204);
                }
                else
                {
                    echo _error($objectNameReturn, 'Problem Process Data [:drx]', 403);
                }
            }
            else
            {
                echo _error($objectNameReturn, 'Section Method Failed Run [:drx]', 401);
            }
        }
        else
        {
            echo _error($objectNameReturn, 'Identify Section not Found [:drx]', 401);
        }
    }
    else
    {
        echo _error(null, 'Library Core is NULL [:drx]', 401);
    }
}
else
{
    echo _error(null, 'Error Method Level II [:drx]', 403);
}
?>