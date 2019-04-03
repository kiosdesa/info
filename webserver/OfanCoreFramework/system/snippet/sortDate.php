<?php
function sortDate($data1, $data2)
{
    foreach($data1 as $k => $v)
    {
        $data2[$k] = $data1[$k]; 
    }

    return array_values($data2);
}

function usortDate($data)
{
    usort($data, "compare_months");
    return $data;
}

function compareMonths($a, $b) 
{
    $monthA = date_parse($a);
    $monthB = date_parse($b);
    return $monthA["month"] - $monthB["month"];
}
?>