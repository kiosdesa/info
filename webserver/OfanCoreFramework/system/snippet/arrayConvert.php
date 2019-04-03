<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Converts a Array into stdObject.
 * @return array
 */
function ArrayToObject($array)
{
    if (!is_array($array)) 
    {
        return $array;
    }
    
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) 
    {
        foreach ($array as $name=>$value) 
        {
            $name = strtolower(trim($name));
            if (!empty($name)) 
            {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    }
    else 
    {
        return FALSE;
    }
}


/**
 * Converts a stdObject into Array.
 * @return array
 */
function ObjectToArray($object)
{
    if( !is_object( $object ) && !is_array( $object ) )
    {
        return $object;
    }
    if( is_object( $object ) )
    {
        $object = get_object_vars( $object );
    }
    return array_map( 'objectToArray', $object );
}


/**
 * Take XML content and convert
 * if to a PHP array.
 * @param string $xml Raw XML data.
 * @param string $main_heading If there is a primary heading within the XML that you only want the array for.
 * @return array XML data in array format.
 */
function xmlToArray($xml,$main_heading = '') 
{
    $deXml = simplexml_load_string($xml);
    $deJson = json_encode($deXml);
    $xml_array = json_decode($deJson,TRUE);
    if (! empty($main_heading)) {
        $returned = $xml_array[$main_heading];
        return $returned;
    } else {
        return $xml_array;
    }
}
?>