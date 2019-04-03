<?php if(!defined('_thisFileDIR')) header('Location:..');
function replaceIndexDB($data=null, $findprefix=null)
{
    if(is_null($data)) return false;
    if(is_null($findprefix)) return false;

    $return = array();
    foreach($data as $k => $v)
    {
        if(strpos($k, $findprefix) !== false)
        {
            $replaceKey = str_replace($findprefix,'',$k);
            $return[0][$replaceKey] = $v;
        }
        else
        {
            $return[0][$k] = $v;
        }
    }

    return $return;
}



/**
 * @method fungsi reindexLoop()
 * Memiliki kesamaan dengan reindexInput() beda nya merubah format loop index dari object array ke array object
 * *
 * @param $data = berisi data array loop dari DB atau Variable
 * @param $findprefix = mencari dan merubah nama index yang akan di reformat
 * @param $textreplace =  variable nama baru untuk penambahan nama index ke dalam index yang telah dirubah
 * @param $timeconvert = boolean untuk menentukan perubahan format tanggal
 * @param $trueValue = untuk merubah nilai boolean ke numerik 0 atau 1
 * @param $_lang = nilai variable berisi bahasa translasi internasional (i18n) dari sistem
 */
function reindexLoop($data=null, $findprefix=null, $textreplace=null, $timeconvert=false, $trueValue=false, $_lang)
{
    if(!function_exists('dateToStringTime'))
    {
        import_snippet('dateConvert');
    }

    if(is_null($data)) return false;
    if(is_null($findprefix)) return false;

    $return = array();
    foreach($data[0] as $k => $v)
    {
        $replaceKey = str_replace($findprefix,'',$k);
        if(!is_null($textreplace))
        {
            foreach($textreplace as $k2 => $v2)
            {
                $replaceKey = strpos($replaceKey, $v2) !== false ? str_replace("$v2",'',$replaceKey) : $replaceKey;
            }
        }

        $renameLabel = labelingFormatText(str_replace('_',' ',$replaceKey));
        $fieldlabel = str_replace('_','',$replaceKey);

        $type = isDate($v) ? 'date' : (is_numeric($v) ? 'number' : (is_string($v) ? 'text' : (is_bool($v) ? 'boolean' : (is_null($v) ? 'text' : 'disable'))));

        if(strpos($k, '_date') !== false)
        {
            $v = $timeconvert !== false ? dateToStringTime($v) : $v;
        }
        
        $v = $trueValue ? (is_bool($v) ? ($v == true ? $_lang['bool']['true'] : $_lang['bool']['false']) : $v) : (is_bool($v) ? ($v == true ? 1 : 0) : $v);
        $dataArray = array(
            'field'=>$k, 
            'fieldlabel'=>$fieldlabel, 
            'label'=>$renameLabel, 
            'type'=>$type, 
            'value'=>$v
        );

        array_push($return, $dataArray);
    }

    return $return;
}



/**
 * @method fungsi reindexInput()
 * Memiliki kesamaan dengan reindexLoop() hanya bedanya ini dari loop per nama di reformat
 * *
 * @param $data = berisi data array loop dari DB atau Variable
 * @param $options = brtisi array untuk meReplace nama index dan mengambil nilai index yg ditemukan
 * @param $unset =  berisi array dengan nama index yang akan di hilangkan datanya
 */
function reindexInput($data=null, $options=null, $unset=null)
{
    if(is_array($unset)) 
    {
        $data = unsetLoop($data, $unset); /* Awas Unset dengan numeric ini rawan berganti posisi */
        $data = array_values($data[0]);
    }
    
    $regexArray = isset($options[1]) ? $options[1] : null; /* ReGex untuk merubah nama index (isinya array = beberapa index yg dirubah */
    $indexSearch = isset($options[0]) ? $options[0] : 'column_name'; /* Nama index nilai yang dicari & ditampilkan */
    $push = array();
    foreach($data as $k=>$v)
    {
        $pack = array();
        $type = strpos($v[$indexSearch], 'date') !== false ? 'date' : (strpos($v[$indexSearch], 'status') !== false ? 'boolean' : 'text');
        $val = (strpos($v[$indexSearch], 'status') !== false ? 0 : '');
        $fieldlabel = is_null($regexArray) ? $v[$indexSearch] : preg_replace($regexArray, '', $v[$indexSearch]);
        $dataArray = array(
            //'field'=>$v[$indexSearch],
            'fieldlabel'=>$fieldlabel,
            'field'=>$fieldlabel,
            'label'=>labelingFormatText(str_replace('_',' ',$fieldlabel)),
            'placeholder'=>($type == 'date' ? 'Year / Month / Day' : ucwords(str_replace('_',' ',$v[$indexSearch]))),
            'type'=>$type,
            'value'=>$val
        );
        $pack = $dataArray;
        array_push($push, $pack);
    }
    
    return json_encode($push);
}



function labelingFormatText($text=null)
{
    if(is_null($text)) return false;
    $countTextForBigger = strlen($text) > 3 ? false : true;
    $return = $countTextForBigger == true ? strtoupper($text) : ucwords($text);
    return $return;
}




/**
 * @method fungsi unsetLoop()
 * Menghilangkan data index pada loop array
 * *
 * @param $data = berisi data array loop dari DB atau Variable
 * @param $indexName = brisi array untuk meReplace nama index dan mengambil nilai index yg ditemukan
 */
function unsetLoop($data=null, $indexName=array())
{
    $push = [];
    if(is_null($data)) return false;
    if(!is_array($indexName)) return false;
    if(count($indexName) < 1) return false;

    foreach($indexName as $k=>$v)
    {
        unset($data[$v]);
    }
    array_push($push, $data);
    return $push;
}
?>