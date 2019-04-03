<?php if(!defined('_thisFileDIR')) header('Location:..');

class dateHandler extends OfanCoreFramework
{
    public function mimeToStringTime($time)
    {
        return date("D, F, Y", $time);
    }
    
    public function stringToMimeTime($time)
    {
        return strtotime($time);
    }
    
    public function dateToStringTime($time)
    {
        return date("D, d F, Y", strtotime($time));
    }
    
    public function stringToDateTime($time)
    {
        return date("Y-m-d", strtotime($time));
    }
    
    public function dateToAgoTime($time)
    {}
    
    public function romanNumerals($number) 
    {
        $map = array(
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 
            'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        );
        $returnValue = '';
        while($number > 0) 
        {
            foreach ($map as $roman => $int)
            {
                if($number >= $int)
                {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
    
    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    public function isDate($date)
    {
        if(!is_string($date)) return false;
        if(date('Y-m-d', strtotime($date)) == $date) 
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function dateTranslate($date, $lang)
    {
        setlocale(LC_TIME, $lang['locale'] . '.UTF-8');
        switch($lang['flag'])
        {
            case 'en':
                return strftime("%B %e, %G", $date);
            break;
            case 'en_short':
                return strftime("%B %e, %G %T", $date);
            break;
            case 'id':
            return strftime("%e %B %G", $date);
            break;
            case 'id_short':
            return strftime("%e %b %G %T", $date);
            break;
            default:
                return strftime("%e %B %G", $date);
            break;
        }
    }
    
    public function dateLangFormat($format, $syntax)
    {
        // http://php.net/manual/en/public function.strftime.php
        $strf_syntax = [
            // Day - no strf eq : S (created one called %O)
            '%O', '%d', '%a', '%e', '%A', '%u', '%w', '%j',
            // Week - no date eq : %U, %W
            '%V',
            // Month - no strf eq : n, t
            '%B', '%m', '%b', '%-m',
            // Year - no strf eq : L; no date eq : %C, %g
            '%G', '%Y', '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
            '%P', '%p', '%l', '%I', '%H', '%M', '%S',
            // Timezone - no strf eq : e, I, P, Z
            '%z', '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
            '%s'
        ];
        // http://php.net/manual/en/public function.date.php
        $date_syntax = [
            'S', 'd', 'D', 'j', 'l', 'N', 'w', 'z',
            'W',
            'F', 'm', 'M', 'n',
            'o', 'Y', 'y',
            'a', 'A', 'g', 'h', 'H', 'i', 's',
            'O', 'T',
            'U'
        ];
        switch ( $syntax ) {
            case 'date':
                $from = $strf_syntax;
                $to   = $date_syntax;
                break;
            case 'strf':
                $from = $date_syntax;
                $to   = $strf_syntax;
                break;
            default:
                return false;
        }
        $pattern = array_map(
            function ( $s ) {
                return '/(?<!\\\\|\%)' . $s . '/';
            },
            $from
        );
        return preg_replace( $pattern, $to, $format );
    }
}
?>