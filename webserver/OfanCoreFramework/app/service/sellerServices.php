<?php if(!defined('_thisFileDIR')) header('Location:..');

class SellerServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;
	private static $_cdnIcon;
	private static $_cdnProduct;
	private static $_cdnSeller;
	private static $_cdnUser;
	private static $_thisComponentIonic;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{	
	   	$cluster = 'account';
		/**
		* Untuk mengisi nilai boolean pada where (filter)
		* di parameter isikan nilai 1 atau 'true' untuk true
		* di parameter isikan nilai kosong atau '' untuk false
		*/
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			$library = array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'codeHandler', 'fileHandler', 'arrayHandler');
			if(!class_exists('dateHandler')) array_push($library, 'dateHandler');
			if(!class_exists('generatorHandler')) array_push($library, 'generatorHandler');

			parent::_library($library);
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}

		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'seller';
		self::$_thisComponentIonic = 'SellerDetailPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
		self::$_cdnUser = parent::_cdnDirectoryUser();
	}
	
	/*
	 * Method untuk menghitung nilai skor kios
	 */
	public static function calculateScore($param)
	{
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load($param);
		$dbSeller = isset($param['db']) ? $param['db'] : parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, array(':id'=>$param['seller_id']), null, null, null);
		
		if($dbSeller)
		{
			$dbSeller = isset($param['db']) ? $dbSeller : $dbSeller[0];
			$positiveRate = $dbSeller['positive_rate'];
			$negativeRate = $dbSeller['negative_rate'];
			$accurateScore = $dbSeller['accurate_score'];
			$speedService = $dbSeller['speed_service_score'];
			$overallService = $dbSeller['overall_service_score'];
			$rate = ($positiveRate * $negativeRate) / 2;
			$score = ($accurateScore * $speedService) / 2;
			$jumlahkan = ($rate + $score + $overallService);
			$levelNumber = $jumlahkan;
			
			$defaultNameLevel = self::$_lang['status']['fresh'];
			if($levelNumber < 10) return array(
				'number'=>$levelNumber, 'medal'=>[0], 'name'=>$defaultNameLevel, 'slug'=>strtolower($defaultNameLevel),
				'icon'=>array('color'=>'gray', 'file'=>'ios-flash')
			);

			$limitRange = array('special'=>array('row'=>':min_score', 'val'=>intval($levelNumber), 'operator'=>'<='));
			$getLevel = parent::_handler('crud', 'config')->getDataFilter('seller_level', array('id','slug','name','icon'), array(':status'=>1), null, array('row'=>'max_score','sort'=>'DESC'), 1, null, $limitRange);
			$getLevel = $getLevel[0];
			$getLevel['number'] = intval($levelNumber);
			$numberOfScoreMedal = $getLevel['id'];
			if(in_array($numberOfScoreMedal, array(1,2,3)))
			{
				$medal = $numberOfScoreMedal;
			}
			else
			{
				if($numberOfScoreMedal % 3)
				{
					$medal = $numberOfScoreMedal / 3;
				}
				else
				{
					$medal = $numberOfScoreMedal / 2;
				}
			}

			$getLevel['medal'] = range(1, round($medal));
			$getLevel['icon'] = parent::fixunserialize($getLevel['icon']);
			unset($getLevel['id']);
			return $getLevel;
		}
		else
		{
			return false;
		}
	}

	/**
	 * self::detailSeller() - Private static function untuk mengambil database product detil
	 * self::matchSellerRelation() -  Mencocokan database lain sesuai dengan ID pada database product
	 * self::reformatSellerArrayDB() - Memformat ulang database nilai array dalam PostgreSQL
	 * _proposeCrudServices() di file crudHandlerPDO.php
	 */
	protected static function detailSeller($param)
	{
		self::load($param);
		// Check self::$_token --> FROM SESSION SYSTEM
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('slug');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);
		
		$findSeller = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, array(':slug'=>$param['slug']));
		if($findSeller)
		{
			$ownerDetail = parent::_relation(null, array('account', 'buyer', array('user_name','real_name'), array(':idsecure'=>$findSeller[0]['owner_id'])),false,false);
			$findSeller[0]['owner_detail'] = $ownerDetail[0];

			$findSeller[0]['banner'] = parent::_handler('file', array('dir'=>self::$_cdnSeller.'/'.$findSeller[0]['slug'], 'filename'=>$findSeller[0]['banner']))->checkBannerSeller(parent::_generate('bannerseller',null));
			$findSeller[0]['logo'] = parent::_handler('file', array('dir'=>self::$_cdnSeller.'/'.$findSeller[0]['slug'], 'filename'=>$findSeller[0]['logo']))->checkAvatar(parent::_generate('avatar', null, false));
			$findSeller[0]['score']= sellerServices::calculateScore(array('db'=>$findSeller[0], 'load'=>false));
			$findSeller[0]['interact_data'] = array(
				'id_seller'=>$findSeller[0]['id'],
				'id_seller_owner'=>$findSeller[0]['owner_id'],
				'slug_seller'=>$findSeller[0]['slug'],
				'slug_seller_owner'=>$findSeller[0]['owner_detail']['user_name'],
				'seller_name'=>$findSeller[0]['name']
			);
			
			// Check Followed Seller
			$whereFollow = array(':id_seller'=>$findSeller[0]['id'], ':id_buyer'=>self::$_userExist[0]['idsecure']);
			$checkFollowExist = parent::_handler('crud', 'cabinet')->getDataWhere('follow_seller', null, $whereFollow);
			$findSeller[0]['followed'] = $checkFollowExist ? true : false;

			$return = array(
				'approve'=>true, 
				'message'=>self::$_lang['crud']['read']['success'],
				'server'=>array(
					'seller'=>self::$_cdnSeller,
					'icon'=>self::$_cdnIcon,
					'product'=>self::$_cdnProduct
				),
				'seller'=>$findSeller
			);

			if(self::$_userExist)
			{
				$findUserID = self::$_userExist[0]['idsecure'];
			    $nuAing = $findSeller[0]['owner_id'] == $findUserID ? true : false;
			}
			else
			{
			    $nuAing = false;
			}

			$WhereProduct = array(':seller_id'=>$findSeller[0]['id']);
			if($nuAing == false) $WhereProduct[':status'] = 1;
			$getProductSeller = parent::_handler('crud', 'sensus')->getDataWhere(
				'product',array('id','slug','name','thumb','fix_price'), $WhereProduct
			);
			
			$return['seller'][0]['product'] = parent::_loopkurs($getProductSeller, 'fix_price');
			$return['nu_aing'] = $nuAing;

			return $return;
		}
		else
		{
			return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['denied']);
		}
	}

	/** 
	 * Method Card Seller untuk menampilkan informasi seller/kios sesuai akun pemiliknya
	 */
	protected static function cardKios($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(!self::$_userExist) return array('approve'=>false,'message'=>self::$_lang['access']['denied']);
		$findUserID = self::$_userExist[0]['idsecure'];

		$seller = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, array(':owner_id'=>$findUserID));
		if(!$seller) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['denied']);
		$seller[0]['logo'] = parent::_handler('file', array(
			'dir'=>self::$_cdnSeller.'/'.$seller[0]['slug'], 'filename'=>$seller[0]['logo'] 
		))->checkAvatar(parent::_generate('avatar'));

		$data = $seller[0];
		$level = self::calculateScore(array('load'=>false, 'db'=>$data));
		$data['level'] = $level;
		$data['shipping'] = unserialize($data['shipping']);
		return array('approve'=>true, 'data'=>array($data));
	}


	/** 
	 * Method Search Seller untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function searchSeller($param)
	{
		if(!isset($param['lookup'])) return false;
		if($param['lookup'] === ' ' OR empty($param['lookup']) OR strlen($param['lookup']) <= 3 ) return false;

		self::load($param);
		$cluster = self::$_ClusterDB;
		$lookup = $param['lookup'];
		$search = array(':shop_name'=>$lookup, ':shop_slug'=>$lookup, ':shop_location'=>$lookup);
		$limit = isset($param['limit']) ? $param['limit'] : null;
		$select = array(
			'shop_id',
			'shop_icon',
			'shop_name',
			'shop_slug'
		);

		$getSellerDB = parent::_handler('crud', self::$_ClusterDB)->searchData('seller', $search, $select, $limit);
		if(!$getSellerDB) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['failed']);
		return array('approve'=>true, 'data'=>$getSellerDB);
	}

	/** 
	 * Method Filter Seller untuk menampilkan data produk sesuai kategorinya atau penjualnya
	 * Only Premium Purpose
	 */
	protected static function followerSeller($param)
	{}

	/** 
	 * Method Following Seller untuk memproses follow/unfollow seller
	 */
	protected static function followingSeller($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('id_seller','slug_seller');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		// $findUserID is get Users Database with search DB COndition where by user token
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		
		$whereFav = array(':id_seller'=>$param['id_seller'], ':id_buyer'=>$findUserID);
		$checkFollowExist = parent::_handler('crud', 'cabinet')->getDataWhere('follow_seller', null, $whereFav);
		if($param['followed'] == true)
		{
			if(!$checkFollowExist) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['subscribe']['failed']);
			$favoriting = parent::_handler('crud', 'cabinet')->deleteData('follow_seller', array(':id_seller'=>$param['id_seller'], ':id_buyer'=>$findUserID));
			if($favoriting)
			{
				return array(
					'approve'=>true, 
					'message'=>self::$_lang['crud']['delete']['success'], 
					'followed'=>false, 
					'id'=>$param['id_seller']
				);
			}	
		}
		else
		{
			if($checkFollowExist) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['subscribe']['exist']);
			$paramSet = array(
				':id_buyer'=>$findUserID, 
				':id_seller'=>$param['id_seller'], 
				':add_date'=>date('Y-m-d')
			);

			$favoriting = parent::_handler('crud', $param['cluster'])->insertData('follow_seller', $paramSet);
			if($favoriting)
			{
				return array(
					'approve'=>true, 
					'message'=>self::$_lang['ecommerce']['subscribe']['success'], 
					'followed'=>true, 
					'id'=>$param['id_seller']
				);
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['ecommerce']['subscribe']['failed']
				);
			}
		}
	}

	/** 
	 * Method Insert Seller untuk mendaftarkan penjual/kios baru
	 */
	protected static function insertSeller($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('member', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);
		$findUserID = self::$_userExist[0]['idsecure'];
		$getShop = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, 'id', array(':owner_id'=>$findUserID)
		);

		$translateEcommerce = self::$_lang['ecommerce']['seller'];
		if($getShop) return array('approve'=>false, 'message'=>$translateEcommerce['exist']);
		if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('trace')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		$param = parent::_handler('array')->reTrace($param, '');
		$paramWajib = array('name','contact','description','slug','location','district','city','province','postal_code','district_code');
		if(parent::_handler('validate', $param)->issetAndEmptyFalse($paramWajib) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		// Dibuat kondisi karena input dari ionic berupa Array (shipping data: zip_code / popover insertSeller / ShippingService {controller})
		$param['postal_code'] = is_array($param['postal_code']) ? $param['postal_code'][0] : (
			is_numeric($param['postal_code']) ? $param['postal_code'] : null
		);

		$param['slug'] = strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', $param['slug']));
		$checkSlug = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, 'id', array(':slug'=>$param['slug'])
		);

		$paramSet = array(':owner_id'=>$findUserID, ':add_date'=>strtotime('now'), ':status'=>1, ':income'=>0, 
			':name'=>$param['name'], ':contact'=>$param['contact'], ':description'=>$param['description'], ':location'=>$param['location'], 
			':district'=>$param['district'], ':city'=>$param['city'], ':province'=>$param['province'], ':postal_code'=>$param['postal_code'], ':district_code'=>$param['district_code']
		);

		$codeShop = parent::_handler('generator')->getFirstLetterWords($param['name']);
        $Year = parent::_handler('date')->romanNumerals(date('Y'));
		$paramSet[':code_shop'] = $findUserID.$Year.$codeShop;
		$paramSet[':slug'] = $checkSlug ? $param['slug'].(parent::_handler('generator')->addZeroBefore(3, $checkSlug[0]['id'])) : $param['slug'];
		if(isset($param['banner'])) $paramSet[':banner'] = $param['banner'];
		if(isset($param['logo'])) $paramSet[':logo'] = $param['logo'];
		if(isset($param['note'])) $paramSet[':note'] = $param['note'];
		if(isset($param['shipping'])) $paramSet[':shipping'] = $param['shipping'];

		$newShop = parent::_handler('crud', self::$_ClusterDB)->insertData(
			self::$_thisTable, $paramSet
		);
		//var_dump($paramSet);die();

		if(!$newShop) return array('approve'=>false, 'message'=>$translateEcommerce['denied'].' (error new)');
		$updateLevel = parent::_handler('crud', self::$_ClusterDB)->updateData('buyer', array(':idsecure'=>$findUserID), array(':level'=>5));
		$messageTranslate = $translateEcommerce['success'];
		if($updateLevel) $messageTranslate = $messageTranslate." (level up)";

		return array(
			'approve'=>true,
			'message'=>$messageTranslate,
			'data'=>$paramSet
		);
	}

	protected static function fieldInsert($param)
	{
		self::load($param);
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		$accessDecision = parent::_access('member', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);
		
		$translateEcommerce = self::$_lang['ecommerce']['seller'];
		$translateGlobal = self::$_lang['global']['form'];
		$fetch = parent::_handler('crud', self::$_ClusterDB)->showRowSchema('public', self::$_thisTable);
		if(!$fetch) return array('approve'=>false, 'message'=>$translateEcommerce['denied']);

		$fetch = parent::_handler('array')->reindexInput($fetch, array('column_name', array()), array(0,4,5,7,8,11,12,13,14,15,16,22,23,24,25,26));
		for($i=0;$i<count($fetch);$i++)
		{
			$fetch[$i]['label'] = $translateEcommerce['form'][$fetch[$i]['field']];
			if(in_array($fetch[$i]['field'], array('note', 'description', 'location'))) $fetch[$i]['type'] = 'textarea';
			if(in_array($fetch[$i]['field'], array('district'))) $fetch[$i]['type'] = 'autocomplete';
			if(in_array($fetch[$i]['field'], array('contact','postal_code'))) $fetch[$i]['type'] = 'number';
			if(in_array($fetch[$i]['field'], array('city','province','postal_code','district_code'))) 
			{
				$fetch[$i]['placeholder'] = '...';
				$fetch[$i]['readonly'] = true;
			}
			else
			{
				$fetch[$i]['placeholder'] = strtolower(sprintf($translateGlobal['type'], $fetch[$i]['placeholder']));
				$fetch[$i]['readonly'] = false;
			}
			if(in_array($fetch[$i]['field'], array('district_code'))) $fetch[$i]['type'] = 'hide';
		}

		return array(
			'approve'=>true,
			'message'=>self::$_lang['crud']['read']['success'],
			'data'=>$fetch,
			'readwrite'=>$accessDecision
		);
	}
	
	/*
	 * Fungsi untuk mengenerate informasi akun buyer
	 */
	protected static function fieldSeller($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		
		if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('planning')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		$param['load'] = false;
		if($param['planning'] == 'create') return self::fieldInsert($param);

		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$select = array('name','contact','location','district','city','postal_code','province','note','description','district_code');
		$getShop = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, $select, array(':owner_id'=>$findUserID)
		);

		$translateEcommerce = self::$_lang['ecommerce']['seller'];
		$translateGlobal = self::$_lang['global']['form'];
		if(!$getShop) return array('approve'=>false, 'message'=>$translateEcommerce['denied']);
		
		$tmpArray = array();
		foreach($getShop[0] as $konci=>$nilai)
		{
			if(in_array($konci, array('name','district','city','postal_code','province'))) $type = 'text';
			if(in_array($konci, array('contact'))) $type = 'number';
			if(in_array($konci, array('note', 'description', 'location'))) $type = 'textarea';
			if(in_array($konci, array('add_date'))) $type = 'date'; //$nilai = dateToStringTime($nilai);
			if(in_array($konci, array('district'))) $type = 'autocomplete';
			if(in_array($konci, array('slug'))) 
			{
				$type = parent::_access('deputi', self::$_userExist) ? 'text' : ($nilai == '' ? 'text' : 'disable');
			}
			if(in_array($konci, array('district_code'))) $type = 'hide';

			$placeholder = strtolower(sprintf($translateGlobal['type'], $konci));
			$readonly = false;
			if(in_array($konci, array('city','province','postal_code','district_code'))) 
			{
				$placeholder = '...';
				$readonly = true;
			}
			
			$dataArray = array(
				'field'=>$konci, 
				'value'=>$nilai, 
				'type'=>$type,
				'readonly'=>$readonly,
				'placeholder'=>$placeholder
			);

			// Merubah Label text untuk input row username
			$dataArray['label'] = $translateEcommerce['form'][$konci];
			array_push($tmpArray, $dataArray);
		}

		return array(
			'approve'=>true,
			'message'=>self::$_lang['crud']['read']['success'],
			'data'=>$tmpArray
		);
	}

	/** 
	 * Method Update Seller untuk memperbaharui informasi seller/kios
	 */
	protected static function modifySeller($param)
	{
		self::load(array('cluseter'=>$param['cluster'])); // Parameter di definisikan ulang supaya mengurangi memory used
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$getShop = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, 'id', array(':owner_id'=>$findUserID)
		);

		if(!$getShop) return array('approve'=>false, 'message'=>self::$_lang['ecommerce']['seller']['denied']);
		if(isset($param['trace'])) $param = parent::_handler('array')->reTrace($param, '', null, false);

		$paramSet = array();
		// Dibuat kondisi karena input dari ionic berupa Array (shipping data: zip_code / popover insertSeller / ShippingService {controller})
		if(isset($param['postal_code'])) $paramSet[':postal_code'] = is_array($param['postal_code']) ? $param['postal_code'][0] : (
			is_numeric($param['postal_code']) ? $param['postal_code'] : null
		);

		if(isset($param['name'])) $paramSet[':name'] = $param['name'];
		if(isset($param['banner'])) $paramSet[':banner'] = $param['banner'];
		if(isset($param['logo'])) $paramSet[':logo'] = $param['logo'];
		if(isset($param['contact'])) $paramSet[':contact'] = $param['contact'];
		if(isset($param['location'])) $paramSet[':location'] = $param['location'];
		if(isset($param['description'])) $paramSet[':description'] = $param['description'];
		if(isset($param['note'])) $paramSet[':note'] = $param['note'];
		if(isset($param['name'])) $paramSet[':name'] = $param['name'];
		if(isset($param['district'])) $paramSet[':district'] = $param['district'];
		if(isset($param['city'])) $paramSet[':city'] = $param['city'];
		if(isset($param['province'])) $paramSet[':province'] = $param['province'];
		if(isset($param['district_code'])) $paramSet[':district_code'] = $param['district_code'];
		if(isset($param['shipping'])) $paramSet[':shipping'] = serialize($param['shipping']);

		$updateShop = parent::_handler('crud', self::$_ClusterDB)->updateData(
			self::$_thisTable, array(':id'=>$getShop[0]['id']), $paramSet
		);

		if(!$updateShop) return array('approve'=>false, 'message'=>self::$_lang['ecommerce']['seller']['denied'].' (un-update)');
		return array(
			'approve'=>true,
			'message'=>self::$_lang['ecommerce']['seller']['success']
		);
	}

	/** 
	 * Method Delete Seller untuk menghapus data seller/kios
	 */
	protected static function deleteSeller($id=null)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('assist', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);
		
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('seller_id');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		$DeleteSeller = parent::_handler('crud', self::$_ClusterDB)->deleteData(
			self::$_thisTable, array(':id'=>$id)
		);

		if(!$DeleteSeller) return array('approve'=>false, 'message'=>self::$_lang['crud']['delete']['failed']);
		return array('approve'=>false, 'message'=>self::$_lang['crud']['delete']['success']);
	}

	/** 
	 * Method Disable Seller untuk menonaktifkan seller/kios
	 * Purpose jika seller di banner atau melewati 1 tahun tidak aktif
	 */
	protected static function disableSeller($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse(array('seller_id')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		$DeleteSeller = parent::_handler('crud', self::$_ClusterDB)->updateData(
			self::$_thisTable, array(':id'=>$id), array(':status'=>0)
		);

		if(!$DeleteSeller) return array('approve'=>false, 'message'=>self::$_lang['crud']['delete']['failed']);
		return array('approve'=>false, 'message'=>self::$_lang['crud']['delete']['success']);
	}
}
?>