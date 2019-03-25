<?php
@header('Content-Type: application/json; charset=utf-8');
require_once('../load.php');
if(!class_exists('dbHandler')) OfanCoreFramework::_library(array('dbHandler'));
$db = OfanCoreFramework::_handler('db')->check();
$headerStatus = $db ? array(true, 200, 'OK') : array(false, 403, 'ERROR');

@header('HTTP/1.1 '.$headerStatus[1].' '.$headerStatus[2], $headerStatus[0], $headerStatus[1]);
echo '{"code":'.$headerStatus[1].', "result":"'.$headerStatus[2].'", "database":'.($headerStatus[0] ? 'true' : 'false').'}';
?>