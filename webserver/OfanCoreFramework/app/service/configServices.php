<?php if(!defined('_thisFileDIR')) header('Location:..');

class ConfigServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_cdnIcon;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$cluster = 'config';
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true)
		{
            parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler'));
            self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
        }
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
    }


    protected static function getTableValue($param)
    {
        self::load($param);
		//if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		//$whereUser = array(':user_token'=>self::$_token);
		//$findUserID = parent::_handler('crud', 'account')->getDataWhere('users', 'id', $whereUser);
        //if(!$findUserID) return array('approve'=>false, 'message'=>self::$_lang['access']['failed']);
        
        $table = isset($param['table']) ? $param['table'] : null;
        $select = isset($param['select']) ? $param['select'] : null;
        $where = isset($param['where']) ? $param['where'] : null;
        $limit = isset($param['limit']) ? $param['limit'] : null;
        $order = isset($param['order']) ? $param['order'] : null;
        if(is_null($where))
        {
            $getConfig = parent::_handler('crud', self::$_ClusterDB)->showData($table, $select, $order);
        }
        else
        {
            $getConfig = parent::_handler('crud', self::$_ClusterDB)->getDataWhere($table, $select, $where, $limit);
        }
        return $getConfig;
    }


	/** 
	 * Method Search Seller untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchDB($param)
	{
        self::load($param);
        if(is_null(self::$_token)) return false;
        $table = isset($param['table']) ? $param['table'] : null;
        $select = isset($param['select']) ? $param['select'] : null;
        $where = isset($param['where']) ? $param['where'] : null;
        $limit = isset($param['limit']) ? $param['limit'] : null;

		$searchDB = parent::_handler('crud', self::$_ClusterDB)->searchData($table, $param['search'], $select, $limit);
		if(!$searchDB) return false;
		return $searchDB;
    }
    
    protected static function searchCategory($param)
    {
        self::load($param);
		if(!isset($param['query'])) return false;
        if($param['query'] === ' ' OR empty($param['query']) OR strlen($param['query']) <= 3 ) return false;
		$query = strtolower($param['query']);
        $value = array(
            'search'=>array(':name'=>$query, ':slug'=>$query),
            'table'=>'category_product',
            'select'=>array('id','slug','name'),
            'where'=>array(':status'=>1),
            'order'=>array('row'=>'id', 'sort'=>'ASC')
        );
        
        if(isset($param['where'])) $value['where'] = $param['where'];
        $value['load'] = false;
        $searchDB = self::searchDB($value);

        if(!$searchDB) return false;
        return array('approve'=>true, 'data'=>$searchDB);
    }

    protected static function paymentList($param)
    {
        self::load($param);
        if(is_null(self::$_token)) return false;

        $value = array(
            'table'=>'payment',
            'select'=>array('tax','slug','name','code','account','account_name','type'),
            'where'=>array(':status'=>1),
            'order'=>array('row'=>'id', 'sort'=>'ASC')
        );
        
        if(isset($param['where'])) $value['where'] = $param['where'];
        $value['load'] = false;
        $getDB = self::getTableValue($value);

        if(!$getDB) return false;
        $balance = parent::_handler('crud', 'account')->getDataWhere(
            'buyer', 'balance', array(':idsecure'=>self::$_userExist[0]['idsecure'])
        );

        //$balance[0]['balance_format'] = parent::_kurs($balance[0]['balance']);
        $balance[0]['balance'] = (int)$balance[0]['balance'];

        $return = array(
            'symbol_currency'=>self::$_lang['lang']['currency']['symbol'],
            'server'=>array(
                'icon'=>self::$_cdnIcon
            ),
            'data'=>$getDB,
            'balance'=>$balance
        );
        $return = array_merge($return, $balance[0]);
        return $return;
    }


    protected static function companyOptions($param)
    {
        self::load($param);
		$paramValidate = array('type', 'name');
		$handlerValidate = parent::_handler('validate', $param);
        if($handlerValidate->issetAndEmptyFalse($paramValidate) == false) return false;
        $table = isset($param['options']) ? $param['options'] : 'company_options';
        $value = array(
            'table'=>$table,
            'select'=>array('type','name','value'),
            'where'=>array(':type'=>$param['type'], ':name'=>$param['name']),
            'order'=>array('row'=>'id', 'sort'=>'ASC')
        );

        $getDB = self::getTableValue($value);

        if(!$getDB) return false;
        return $getDB;
    }


    protected static function shippingList($param)
    {
        $options = isset($param['options']) ? (is_bool($param['options']) ? $param['options'] : false ) : false;
        $value = array(
            'table'=>($options == true ? $options : 'shipping'),
            'select'=>array('id','slug','name','code'),
            'where'=>array(':status'=>1),
            'order'=>array('row'=>'id', 'sort'=>'ASC')
        );

        if(isset($param['where'])) $value['where'] = $param['where'];
        $getDB = self::getTableValue($value);

        if(!$getDB) return false;
        if(isset($param['formatted']))
        {
            if($param['formatted'] == true)
            {
                $pushShipping = [];
                foreach($getDB as $k=>$v)
                {
                    array_push($pushShipping, array(
                        'name'=>$v['slug'],
                        'label'=>$v['name'],
                        'type'=>"checkbox",
                        'value'=>$v['slug'],
                        'checked'=>false
                    ));
                }
                return $pushShipping;
            }
        }
        else
        {
            return $getDB;
        }
    }


    protected static function statusOrderList($param)
    {
        $value = array(
            'table'=>'status_order',
            'order'=>array('row'=>'id', 'sort'=>'ASC')
        );

        if(isset($param['where'])) $value['where'] = $param['where'];
        $getDB = self::getTableValue($value);

        if(!$getDB) return false;
        return $getDB;
    }


    protected static function addConfig($param)
    {}
    protected static function updateConfig($param)
    {}
    protected static function deleteConfig($param)
    {}
}