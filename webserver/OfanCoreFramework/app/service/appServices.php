<?php if(!defined('_thisFileDIR')) header('Location:..');

class AppServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_lang;
	private static $_cdnBanner;
	private static $_cdnIcon;
	private static $_cdnDomain;
	private static $_cdnProduct;
	private static $_cdnSeller;
	private static $_cdnBuyer;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$library = array('jsonHandler');
		$libraryAdd = isset($param['library']) ? array_merge($library, $param['library']) : $library;
		self::$_lang = parent::_languageConfig();
		self::$_cdnDomain = parent::_cdnDomain();
		self::$_cdnBanner = parent::_cdnDirectoryBanner();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
		self::$_cdnBuyer = parent::_cdnDirectoryUser();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_ClusterDB = (isset($param['cluster']) ? $param['cluster'] : 'sensus');
		parent::_library($libraryAdd);
		parent::_snippet(array('htmlComponent', 'sortDate', 'arrayGroup'));
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if(class_exists('dbHandler') AND class_exists('crudHandler') AND class_exists('validateHandler'))
		{
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
	}

	protected static function formatingMessagesConfirm($message, $data)
	{
		if(preg_match('/\|/', $data) < 1) return '';
		$traceData = explode('|', $data);
		if(count($traceData) < 4) return '';
		//var_dump($message);die();
		return sprintf($message, $traceData[0], $traceData[1], parent::_kurs($traceData[2]), $traceData[3]);
	}

	protected static function paymenConfirmFormatData($param)
	{
		self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		if(!self::$_userExist) return self::$_lang['crud']['read']['denied'];
		if(!isset($param['filter'])) return self::$_lang['crud']['create']['isset'];
		if(!isset($param['lookup']))
		{
			$getCompanyOpts = parent::_handler('crud', 'config')->getDataWhere(
				'company_options', array('type','name','value'), array(':type'=>'number',':name'=>'customer')
			);
			$param['lookup'] = $getCompanyOpts[0]['value'];
		}

		$return['message'] = self::$_lang['ecommerce']['payment']['confirm']['manual'];

		$return['options'] = array(
			array(
				'type'=>'achor',
				'name'=>'Whatsapp',
				'icon'=>array('name'=>'logo-whatsapp', 'color'=>'secondary'),
				'content'=>achorWhatsapp(
					$param['lookup'], 
					self::formatingMessagesConfirm(self::$_lang['ecommerce']['payment']['message'], (self::$_userExist[0]['idsecure'].'|'.$param['filter'])), 
					'_blank', 
					false
				)
			),
			array(
				'type'=>'frame',
				'name'=>'Formulir',
				'icon'=>array('name'=>'md-clipboard', 'color'=>'dark'),
				'content'=>self::$_cdnDomain.'sendconfirm.php?number='.$param['lookup'].'&text='.self::formatingMessagesConfirm(
						self::$_lang['ecommerce']['payment']['message'], 
						(self::$_userExist[0]['idsecure'].'|'.$param['filter'])
					)
			)
		);

		return $return;
	}

	protected static function globalInfo()
	{
		return array(
			'about'=>array(
				'owner'=>array(
					'Dinas Pemberdayaan Masyarakat dan Desa', 
					'Kabupaten Kuningan', 
					'Phone: '.achorPhone('0232871502'), 
					'Email: '.achorEmail('info@dpmd.kuningankab.go.id'),
					'Web: '.achorWeb('dpmd.kuningankab.go.id', '_blank')
				),
				'project_manager'=>array(
					'Diki Ahmed',
					'Email: '.achorEmail('dikitsan@gmail.com')
				),
				'creator'=>array(
					'Ofan Web Developer | @OfanWebDev', 
					'Whatsapp: '.achorWhatsapp('08112333903', self::$_lang['whatsapp']['message_greeting'], '_blank'), 
					'Email: '.achorEmail('hi@sofandani.com'),
					'Web: '.achorWeb('about.sofandani.com', '_blank'),
					'Github: '.achorWeb('github.com/sofandani', '_blank')
				),
				'creator_map'=>'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.2034059352286!2d108.46464131477333!3d-6.985303994953779!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f15d1b022a1d5%3A0xc07107cd45af2f0a!2sOfan+Web+Developer!5e0!3m2!1sen!2sid!4v1527024809149',
				'community'=>array(
					'Asosiasi Badan Usaha Milik Desa', 
					'BUMDESKU', 
					'Email: '.achorEmail('admin@bumdesku.com'),
					'Web: '.achorWeb('bumdesku.com', '_blank')
				)
			),
			'version'=>array(
				'1.0.1',
				'Copyright '._thisCOPYRIGHT
			)
		);
	}

	protected static function appInfoSensus()
	{
		self::load();
		$app = array(
			'description'=>array(
				'Assalamualaikum Warahmatullahi Wabarakatu',
				'Sampurasun?',
				'Hatur nuhun (terimakasih) untuk saudara-saudari dari BUMDESKU sudah menggunakan aplikasi sensus ini, sesuai dengan ketentuan DPMD bahwa semua unit usaha yang terdaftar sebagai BUMDES harus di catat dan dilaporkan ke pemerintahan yang ada di daerah maupun Nasional tujuannya untuk pendataan dan pengelolaan dalam rangka memberdayakan Masyarakat di desa khusus nya di Kabupaten Kuningan, Jawa Barat, Indonesia'
			),
			'info'=>array(
				'system'=>array('PHP (OfanCore Framework Web Services)', 'PostgreSQL', 'cURL', 'HTTPS', 'Nodejs'),
				'app'=>array('Angular', 'Cordova', 'Electron', 'TypeScript', 'Webpack')
			)
		);

		return $app + self::globalInfo();
	}

	protected static function appInfoEcommerce()
	{
		self::load();
		$app = array(
			'description'=>array(
				'Assalamualaikum Warahmatullahi Wabarakatu',
				'Sampurasun?',
				'Hatur nuhun (terimakasih) untuk saudara/i kami yang sudah menggunakan aplikasi ini, 
				kami menyajikan kepada anda layanan jual/beli online produk BUMDES khusus nya di Kabupaten Kuningan, Jawa Barat, Indonesia'
			),
			'info'=>array(
				'system'=>array('OfanCore Framework Web Services', 'PostgreSQL', 'cURL', 'HTTPS', 'Nodejs'),
				'app'=>array('Angular', 'Cordova', 'ReactJS', 'Bootstrap', 'Gammu', 'Firebase', 'TypeScript', 'Webpack')
			)
		);

		return $app + self::globalInfo();
	}

	protected static function appNotifSensus($param=null)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		$loadArray = array(
			'buyer'=>array('account','user_id', array()),
			'bumdesa'=>array('sensus','bumdesa_id', array()),
			'unit'=>array('sensus','unit_id', array()),
			'unitusaha'=>array('sensus','unitusaha_id', array())
		);

		$total = array();
		foreach($loadArray as $k=>$v)
		{
			$total[$k] = parent::_handler('crud', $v[0])->count($k, $v[1], $v[2]);
		}

		/*$total['buyer_notif'] = $total['buyer'];
		$total['bumdesa_notif'] = $total['unit'];
		$total['unit_notif'] = $total['unit'];
		$total['unitusaha_notif'] = $total['unitusaha'];
		unset($total['buyer'], $total['bumdesa'], $total['unit'], $total['unitusaha']);*/
		return $total;

	}

	protected static function appAnnouncement($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));

		$notifFrom = isset($param['filter']) ? $param['filter'] : 'dashboard';
		$message = 'PENTING! Tentang Aplikasi.';
		$dataAdd = date('2018-05-30 00:00:00');
		$now = date('Y-m-d H:i:s');
		$finish = (strtotime($now) > strtotime($dataAdd));
		if($finish == true) return null;
		$return = array(
			'title'=>$message,
			'duration'=>array($now,$dataAdd),
			'finish'=>$finish
		);
		if($notifFrom == 'page') 
		{
			$return['body'] = array(
				'thumbnail'=>null,
				'attachment'=>null,
				'message'=>'Selamat datang di aplikasi Sensus Bumdesa Kabupaten Kuningan.<br />Ini adalah aplikasi pertama versi Alpha untuk ujicoba.<br />Silahkan berikan saran & kritik ke sofandani@icloud.con. Terimakasih.',
				'date'=>'Mei 2018',
				'commands'=>'Asosiasi Bumdesa Kuningan & DPMD Kabupaten Kuningan',
				'venue'=>'Kabupaten Kuningan'
			);
		}

		return $return;
	}

	protected static function preDB($table, $dateRow)
	{
		$sql = "SELECT date_trunc('day',$dateRow), count(*) from $table GROUP BY date_trunc('day',$dateRow) ORDER BY
		date_trunc('day',$dateRow);";
		$q = parent::_handler('crud', self::$_ClusterDB)->thisDB();
		$q = $q->prepare($sql);
		$q->execute();
		return $q->fetchAll();
	}
	
	protected static function replaceTrunc($data)
	{
		$push = array();
		for($i = 0;$i < count($data);$i++)
		{
			$convert = date('M', strtotime($data[$i]['date_trunc']));
			//$push[$i][$convert] = $data[$i]['count'];
			$push[$i] = $convert.'[]='.$data[$i]['count'];
			unset($data[$i][0], $data[$i][1], $data[$i]['date_trunc'], $data[$i]['count']);
		}

		$track = join('&', $push);
		parse_str($track, $output);
		
		foreach($output as $k => $v)
		{
			$output[$k] = array_sum($output[$k]);
		}

		//$monthDefault = array(0,0,0,0,0,0,0,0,0,0,0,0);
		$monthDefault = array('Jan'=>0,'Feb'=>0,'Mar'=>0,'Apr'=>0,'May'=>0,'Jun'=>0,'Jul'=>0,'Aug'=>0,'Sep'=>0,'Oct'=>0,'Nov'=>0,'Dec'=>0);
		//$mergeData = $output + $monthDefault;
		$sort = sortDate($output, $monthDefault);

		return $sort;
	}

	protected static function appGraphSensus($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$BumdesaDB = self::preDB('bumdesa', 'bumdesa_date_add_data');
		$UnitDB = self::preDB('unit', 'unit_date_add');
		$UnitUsahaDB = self::preDB('unitusaha', 'unitusaha_add_date');
		
		$allGraphDB = array();
		if($BumdesaDB) $allGraphDB['bumdesa'] = self::replaceTrunc($BumdesaDB);
		if($UnitDB) $allGraphDB['unit'] = self::replaceTrunc($UnitDB);
		if($UnitUsahaDB) $allGraphDB['unitusaha'] = self::replaceTrunc($UnitUsahaDB);

		if($allGraphDB)
		{
			return $allGraphDB;
		}
		else
		{
			return false;
		}
	}

	protected static function appGraphDetail($param=null)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$initalPoin = $param['filter'];
		$GroupRowName = $initalPoin . '_status';
		$statusSensus = parent::_handler('crud', self::$_ClusterDB)
		->getDataFilter($initalPoin, array($GroupRowName), null, null, array('row'=>$GroupRowName));

		$push = array();
		for($i = 0;$i < count($statusSensus);$i++)
		{
			$reformat = $statusSensus[$i][$GroupRowName];
			$reformat = $reformat == 1 ? 'active' : 'inactive';
			$push[$i] = $reformat;
		}

		$push = array_count_values($push);

		if(!isset($push['active'])) $push['active'] = 0;
		if(!isset($push['inactive'])) $push['inactive'] = 0;
		$dateRow = $initalPoin == 'bumdesa' ? 'bumdesa_date_add_data' : ($initalPoin == 'unit' ? 'unit_date_add' : ($initalPoin == 'unitusaha' ? 'unitusaha_add_date' : null));
		$db = self::preDB(strtolower($initalPoin), $dateRow);
		$db = self::replaceTrunc($db);
		return array(
			'pie'=>$push, 
			'graph'=>$db, 
			'title'=>array(
				'top'=>ucfirst($param['filter']),
				'others'=>self::$_lang['graph'],
			)
		);
	}


	protected static function appGetID($param)
	{
		self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		$countTotal = parent::_handler('crud', 'sensus')->count('bumdesa', 'bumdesa_id', array());
		$paramGenerate = array('initial'=>$param['lookup'], 'prefix'=>date('d'), 'suffix'=>($countTotal+1));
		$generator = parent::_generate('id', $paramGenerate);
		return $generator;
	}


	protected static function appHomeShop($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		//if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$dataBanner = parent::_handler('crud', 'transaction')->getDataWhere('banner_promo', array('slug','name', 'description', 'image', 'add_date', 'end_date'), array(':status'=>1), null);
		$dataCategory = parent::_handler('crud', 'config')->getDataFilter('category_product', array('id','slug','name', 'section'), array(':status'=>1, ':parent'=>null), null, array('row'=>'id', 'sort'=>'ASC'));
		$dataProduct = parent::_handler('crud', 'sensus')->getDataFilter('product', array('slug','name','thumb','fix_price','sku','returned','flash_sale','stock','minimum_order'), array(':status'=>1), null, array('row'=>'add_date', 'sort'=>'DESC'), null);
		
		//if(!$dataBanner) $dataBanner = null;
		if(!$dataCategory) return false;
		if(!$dataProduct) return false;

		$pushProduct = array();
		for($i=0;$i<count($dataProduct);$i++)
		{
			if($dataProduct[$i]['stock'] > $dataProduct[$i]['minimum_order'])
			{
				$dataProduct[$i]['id'] = $dataProduct[$i]['sku'];
				$dataProduct[$i]['fix_price'] = parent::_kurs($dataProduct[$i]['fix_price']);
				$dataProduct[$i]['component'] = 'ProductDetailPage';
				array_push($pushProduct, $dataProduct[$i]);
			}
		}

		for($i=0;$i<count($dataCategory);$i++)
		{
			$dataCategory[$i]['component'] = $dataCategory[$i]['section'] == 'loket' ? 'ProductLoketPage' : ($dataCategory[$i]['section'] == 'quickshop' ? 'ProductQuickshopPage' : 'ProductCategoryPage');
			$dataCategory[$i]['icon'] = self::$_cdnIcon.'/'.$dataCategory[$i]['slug'].'.svg';
		}

		$return = array(
			'server'=>array(
				'banner'=>self::$_cdnBanner,
				'icon'=>self::$_cdnIcon,
				'product'=>self::$_cdnProduct
			),
			'notif'=>null,
			'banner'=>$dataBanner,
			'menuCategory'=>$dataCategory,
			'product'=>$pushProduct
		);

		$params = $param;
		$params['load'] = false;
		$notifs = self::notifApp($params);
		if(!is_null($notifs)) $return['notif'] = $notifs['notif'];

		return $return;
	}


	public static function notifApp($param)
	{
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));

		$findUserID = self::$_userExist ? (self::$_userExist[0]['idsecure']) : (
			isset($param['userid']) ? $param['userid'] : null
		);

		if(is_null($findUserID)) return array('approve'=>true, 'message'=>self::$_lang['global']['failed'], 'notif'=>null);
		$seller = parent::_handler('crud', 'account')->getDataWhere('seller','id',array(':owner_id'=>$findUserID));
		if(!$seller) return array('approve'=>true, 'message'=>self::$_lang['global']['failed'], 'notif'=>null);
		$sellerID = $seller[0]['id'];

		$order = parent::_handler('crud', 'transaction')->getDataWhereIn(
			'public.order', array('invoice','data_product','total_payment','add_date','status_shipping'), 
			array('status_shipping', array(2,3,4,8)), array('seller_id'=>$sellerID)
		);

		if(!$order) return array('approve'=>true, 'message'=>self::$_lang['global']['failed'], 'notif'=>null);

		$return = array('approve'=>true, 'message'=>self::$_lang['global']['success']);
		$orderCount = 0;
		
		$decisionMessageOrder = count($order) > 0 ? (
			isset($param['notif_message']) ? $param['notif_message'] : true
		) : false;

		$pushMessage = [];
		if($decisionMessageOrder == true)
		{
			foreach($order as $k)
			{
				$messageNotif = '';
				$dataProduct = unserialize($k['data_product']);
				if($k['status_shipping'] == 8)
				{
					$messageNotif .= sprintf(
						self::$_lang['ecommerce']['seller']['notif']['complaint'], 
						$k['invoice'], date('d M, Y', $k['add_date'])
					);
				}
				else
				{
					$messageNotif .= sprintf(
						self::$_lang['ecommerce']['order']['notif'], 
						parent::_kurs($k['total_payment']), count($dataProduct), $k['invoice'], date('d M, Y', $k['add_date'])
					);
				}

				array_push($pushMessage, array(
						'priority'=>true, 'text'=>$messageNotif, 'page'=>'OrderPage',
						'route'=>array('segment'=>'process', 'receive'=>0, 'shipping'=>[$k['status_shipping']], 'seller'=>true, 'group'=>false)
					)
				);
			}
		}

		$return['notif']['message'] = $pushMessage;
		if(count($order) > 0) $orderCount = count($order);
		$return['notif']['count'] = $orderCount;
		return $return;
	}


	protected static function getCategoryProduct($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		//if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$dataCategory = parent::_handler('crud', 'config')->getDataFilter('category_product', array('id','slug','name'), array(':status'=>1), null, array('row'=>'id', 'sort'=>'ASC'));

		for($i=0;$i<count($dataCategory);$i++)
		{
			$dataCategory[$i]['label'] = $dataCategory[$i]['name'];
			//$dataCategory[$i]['type'] = 'radio';
			$dataCategory[$i]['name'] = $dataCategory[$i]['slug'];
			$dataCategory[$i]['value'] = $dataCategory[$i]['id'];
			unset($dataCategory[$i]['id']);
			unset($dataCategory[$i]['slug']);
		}

		return array('typefield'=>'choose', 'field'=>$dataCategory);
	}


	protected static function sortProduct($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load($param);
		//if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		//if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$sort = array(
			'Relevan', 
			'Termurah', 
			'Termahal',
			//'Retur', 
			//'Terjual', 
			//'Diskon'
		);
		for($i=0;$i<count($sort);$i++)
		{
			$return[$i]['label'] = $sort[$i];
			$return[$i]['name'] = strtolower($sort[$i]);
			//$return[$i]['type'] = 'radio';
			$return[$i]['value'] = $i+1;
		}
		
		return array('typefield'=>'sorting', 'field'=>$return);
	}


	protected static function filterProduct($param)
	{
		// Logic kondisi dibalik jika tidak ada param load artinya true dan self::load() tidak akan di running;
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load(array('library'=>array('dbHandler', 'crudHandlerPDO', 'validateHandler')));
		//if(self::$_userExist == false) return self::$_lang['error']['403_message'];

		$filter = array(
			array(
				'name'=>'price_min',
				'label'=>'Harga Min',
				'placeholder'=>'0',
				'value'=>null,
				'type'=>'number'
			),
			array(
				'name'=>'price_max',
				'label'=>'Harga Max',
				'placeholder'=>'1000000',
				'value'=>null,
				'type'=>'number'
			),
			/*array(
				'name'=>'harga_min_max',
				'label'=>'Range harga',
				'value'=>array('min'=>0,'max'=>1000000),
				'type'=>'range'
			),
			array(
				'name'=>'sale_quantity_price',
				'label'=>'Grosiran',
				'value'=>false,
				'type'=>'toggle'
			),
			array(
				'name'=>'discount',
				'label'=>'Diskon',
				'value'=>false,
				'type'=>'toggle'
			),
			array(
				'name'=>'condition',
				'label'=>implode(self::$_lang['condition'],'/'),
				'value'=>false,
				'type'=>'toggle'
			),
			array(
				'name'=>'shipping',
				'label'=>'Pengiriman',
				'type'=>'option',
				'value'=>array_values(parent::_handler('crud', 'config')->getDataFilter('shipping', array('id','slug','name'), array(':status'=>1), null, array('row'=>'id', 'sort'=>'ASC')))
			),
			array(
				'name'=>'delivering',
				'label'=>'Kirim Ke',
				'type'=>'option',
				'value'=>array(
					array('id'=>0,'name'=>'Bandung'),
					array('id'=>1,'name'=>'Jakarta')
				)
			)*/
		);
		
		return array('typefield'=>'multifield', 'field'=>$filter);
	}
}
?>