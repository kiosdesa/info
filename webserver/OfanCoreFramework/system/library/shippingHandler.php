<?php if(!defined('_thisFileDIR')) header('Location:..');

class shippingHandler extends OfanCoreFramework
{
    private $_curl;
    private $_lang;
    private $_type;
    private $_from;
    private $_to;
    private $_weight;
    private $_service;
    private $_thenames;
    private $_page;

    /**
     * Penghitungan biaya kirim masih menggunakan APIs Tokopedia.com (WORKED!)
     * Kedepan buat database tersendiri untuk daftar harga pengiriman barang sesuai vendor
     * Atau melakukan b2b dengan vendor ekspedisi dan meminta mereka untuk memberika akses database harga beserta track
     * Sharing database bisa menggunakan APIs data dari tiap vendor
     */
	function __construct($param=null)
	{
        if(!class_exists('curlHandler'))
        {
            parent::_library(array('curlHandler'));
        }

        if(!class_exists('dateHandler'))
        {
            parent::_library(array('dateHandler'));
        }
        $this->_curl = parent::_handler('curl', 'data');
        $this->_lang = isset($param['lang']) ? $param['lang'] : null;
        $this->_from = isset($param['from']) ? $param['from'] : null;
        $this->_to = isset($param['to']) ? $param['to'] : null;
        $this->_weight = isset($param['weight']) ? $param['weight'] : null;
        $this->_service = isset($param['service']) ? $param['service'] : null;
        $this->_thenames = isset($param['thenames']) ? $param['thenames'] : null;
        $this->_type = isset($param['type']) ? $param['type'] : null;
        $this->_page = isset($param['page']) ? $param['page'] : null;
    }

    protected function bukalapak($param)
    {
        switch($param)
        {
            case 'rate':
                $data = array(
                    'from'=>$this->_from,
                    'to'=>$this->_to,
                    'weight'=>$this->_weight,
                    'courier'=>$this->_service
                );
                $endpoint = 'shipping_fee.json?' . http_build_query($data);
            break;
            default:
                $data = null;
                $endpoint = null;
            break;
        }

        $curl = $this->_curl->execute(
            array(
                'url'=>'https://api.bukalapak.com/v2/' . $endpoint,
                'args'=>array(
                    //'httpheader'=>array("X-Requested-With: XMLHttpRequest"), 
                    'auth'=>'775335:wnFfeo4fDro5jaqRvan'
                )
            )
        );

        if(is_string($curl))
        {
            $decode = json_decode($curl, true);
            //return array_unique($decode['result']);
            return array_map("unserialize", array_unique(array_map("serialize", $decode['result'])));;
        }
        else
        {
            return false;
        }
    }

    protected function tokped($param)
    {
        switch($param)
        {
            case 'rate':
                $data = array(
                    'origin'=>$this->_from,
                    'destination'=>$this->_to,
                    'weight'=>$this->_weight,
                    'service'=>$this->_service,
                    'names'=>$this->_thenames,
                    //'token'=>'Tokopedia+Kero:4gttyyTgnpt4ztrPUCwy5ykHwAE='
                    //'token'=>'Tokopedia+Kero:8urz+l1NTO4yle99DkP1P4JGp/Y='
                );
                $url = 'https://gw.tokopedia.com/v2/rates/pdp?' . http_build_query($data);
            break;
            case 'city':
                $data = array(
                    'query'=>$this->_to,
                    'token'=>'Tokopedia+Kero:4gttyyTgnpt4ztrPUCwy5ykHwAE='
                    //'token'=>'Tokopedia+Kero:8urz+l1NTO4yle99DkP1P4JGp/Y='
                );
                if(!is_null($this->_page)) $data['page'] = $this->_page;
                $url = 'https://kero.tokopedia.com/v2/district-recommendation?' . http_build_query($data);
            break;
            default:
                $data = null;
                $url = null;
            break;
        }

        $curl = $this->_curl->execute(array('httpheader'=>array("X-Requested-With: XMLHttpRequest"), 'url'=>$url));
        if(is_string($curl))
        {
            $decode = json_decode($curl, true);
            if($param == 'city')
            {
                for($i=0;$i<count($decode['data']);$i++)
                {
                    $decode['data'][$i]['code_format'] = $decode['data'][$i]['district_id'].'|'.$decode['data'][$i]['zip_code'][0];
                }
                return $decode;
            }
            else
            {
                return $decode['data'];
            }
        }
        else
        {
            return false;
        }
    }
    

    public function get($type)
    {
        if($this->_type == 'bukalapak')
        {
            return $this->bukalapak($type);
        }
        elseif($this->_type == 'tokped')
        {
            return $this->tokped($type);
        }
    }

    
    public function fromdate($from)
    {
        $this->_from = $from;
        return $this;
    }


    public function todate($to)
    {
        $this->_to = $to;
        return $this;
    }


    public function checktimeout($type=null)
    {
        if(is_string($this->_to))
        {
            $to = (int)$this->_to;
        }
        elseif(is_numeric($this->_to))
        {
            $to = (int)gmdate("d", $this->_to);
        }
        else
        {
            $to = null;
        }

        // Convert detik ke jumlah jam atau hari
        $grabGMdate = $to > 86400 ? array("d", 'day') : array("h", 'hour');
        $source = (int)gmdate($grabGMdate[0], $to);

        $datefrom = is_numeric($this->_from) ? $this->_from : strtotime($this->_from);
        // Merubah standar date ke unix date
        $dateCreate = date_create(date('Y-m-d', $datefrom));
        // Membuat interval date
        date_add($dateCreate, date_interval_create_from_date_string('"'.$source .$grabGMdate[1].'"'));
        //var_dump(date_add($dateCreate, date_interval_create_from_date_string('"'.$source .$grabGMdate[1].'"')));
        // Convert dari Y-m-d ke microtime
        $dateto = strtotime(date_format($dateCreate,"Y-m-d"));
        $compare = $datefrom <= $dateto;

        if($type=null == 'bool')
        {
            return $compare;
        }
        else
        {
            $lang = $this->_lang;
            return array(
                'id'=>$datefrom,
                'validity_period'=>$compare,
                'longtimes'=>array(
                    'value'=>$source, 
                    'label_unix'=>$grabGMdate[1],
                    'label'=>$lang['time'][$grabGMdate[1]]
                ), 
                'start'=>array(
                    'microtimes'=>$datefrom, 
                    'formattimes'=>parent::_handler('date')->dateTranslate($datefrom, array('flag'=>$lang['lang']['flag_id'].'_short', 'locale'=>$lang['lang']['locale']))
                ), 
                'end'=>array(
                    'microtimes'=>$dateto, 
                    'formattimes'=>parent::_handler('date')->dateTranslate($dateto, array('flag'=>$lang['lang']['flag_id'].'_short', 'locale'=>$lang['lang']['locale']))
                )
            );
        }
    }
}