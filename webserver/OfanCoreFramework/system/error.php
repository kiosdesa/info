<?php
/**
 * @author OFAN
 * @copyright OFAN WEB DEVELOPER
 * @since 2014 - 2018
 * @return _error() json_encode()
 */
function _error($callback=null, $message=null, $code=200, $injectItems=null)
{
    $codeHeader = is_null($message) ? array('HTTP/1.1 500 Internal Server Error', 500) : array('HTTP/1.1 '.$message, $code);

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: inline; filename="error'.$codeHeader[1].'.json"');
    header($codeHeader[0], true, $codeHeader[1]);
    header("X-Powered-By: Web Service OfanCoreFramework/1.0.0");

    $callback = (is_null($callback) or $callback == '' or strlen($callback) < 1) ? '$null' : $callback;
    $ErrorMessage = array(
    	$callback=>array(
    		'status'=>array(
    			'name'=>'ERROR',
    			'code'=>$codeHeader[1], 
    			'message'=>$message, 
    			'copyright'=>'OWD 2016 - '.date('Y')
    		)
    	)
    );

    if(!is_null($injectItems))
    {
        $ErrorMessage[$callback]['items'] = $injectItems;
    }

    return json_encode($ErrorMessage);
}
?>