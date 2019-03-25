<?php if(!defined('_thisFileDIR')) header('Location:..');

class ProductServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_cabinetCluster;
	private static $_accountCluster;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;
	private static $_favoriteTable;
	private static $_followTable;
	private static $_sellerTable;
	private static $_buyerTable;
	private static $_cdnDomain;
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
		$cluster = 'sensus';
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'fileHandler', 'arrayHandler', 'generatorHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_cabinetCluster = 'cabinet';
		self::$_accountCluster = 'account';
		self::$_thisTable = 'product';
		self::$_favoriteTable = 'favorite_product';
		self::$_followTable = 'follow_seller';
		self::$_sellerTable = 'seller';
		self::$_buyerTable = 'buyer';
		self::$_thisComponentIonic = 'ProductDetailPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnDomain = parent::_cdnDomain();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
		self::$_cdnUser = parent::_cdnDirectoryUser();
	}


	/** 
	 * Mengambil Database category setelah mencocokan dengan ID 
	 */
	private static function loopMatchRelation($data=null, $clusterGet=null, $tableGet=null, $select=null, $namedIndex=null, $rowCompare=null, $return=true)
	{
		if(is_null($data)) return false;
		if(is_null($clusterGet)) return false;
		if(is_null($tableGet)) return false;
		if(is_null($select)) return false;
		if(!is_array($rowCompare)) return false;
		if(count($rowCompare) < 1) return false;
		if(is_null($namedIndex)) $namedIndex = $tableGet;

		for($i = 0; $i < count($data); $i++)
		{
			$reformat = parent::_relation(null, array($clusterGet, $tableGet, $select, array($rowCompare[0]=>$data[$i][$rowCompare[1]])),false,false);
			$data[$i][$namedIndex] = $reformat[0];
		}
		if($return) return $data;
	}


	/**
	 * self::detailProduct() - Private static function untuk mengambil database product detil
	 * self::matchProductRelation() -  Mencocokan database lain sesuai dengan ID pada database product
	 * self::reformatProductArrayDB() - Memformat ulang database nilai array dalam PostgreSQL
	 * _proposeCrudServices() di file crudHandlerPDO.php
	 */
	protected static function detailProduct($param)
	{
		//var_dump(self::$_userExist);
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		if($handlerValidate->issetAndEmptyFalse(array('filter')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);
		
		$where = array(':slug'=>$param['filter'], ':status'=>1);
		$getProdDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, $where, null, null, null);
		$getProdDB = $getProdDB[0];

		if($getProdDB)
		{
			$categoryDetail = parent::_relation(null, array('config', 'category_product', array('name', 'section', 'slug'), array(':id'=>$getProdDB['category'])),false,false);
			$getProdDB['category_detail'] = $categoryDetail[0];
			
			$selectSellerDetail = array(
				'slug','name','owner_id','premium_shop_type','logo','city','note',
				'accurate_score','negative_rate','overall_service_score','positive_rate',
				'negative_rate','speed_answer_score','speed_service_score', 'code_shop'
			);
			$sellerDetail = parent::_relation(null, array('account', 'seller', $selectSellerDetail, array(':id'=>$getProdDB['seller_id'])),false,false);
			$sellerDetail = $sellerDetail[0];

			$getProdDB['seller_detail'] = $sellerDetail;
			$getProdDB['seller_detail']['logo'] = parent::_handler('file', array('dir'=>self::$_cdnSeller.'/'.$getProdDB['seller_detail']['slug'], 'filename'=>$getProdDB['seller_detail']['logo']))->checkAvatar(parent::_generate('avatar'));
			
			$ownerDetail = parent::_relation(null, array('account', 'buyer', array('user_name','real_name'), array(':idsecure'=>$getProdDB['seller_detail']['owner_id'])),false,false);
			$getProdDB['seller_detail']['owner_detail'] = $ownerDetail[0];
			$getProdDB['seller_detail']['component'] = 'SellerDetailPage';
			
			$getProdDB['photo'] = unserialize($getProdDB['photo']);
			$getProdDB['fix_price'] = parent::_kurs($getProdDB['fix_price']);
			$getProdDB['insurance'] = $getProdDB['insurance'] == true ? self::$_lang['priority']['true'] : self::$_lang['priority']['false'];
			$getProdDB['returned'] = $getProdDB['returned'] == true ? self::$_lang['support']['true'] : self::$_lang['support']['false'];
			$getProdDB['condition'] = $getProdDB['condition'] == true ? self::$_lang['condition']['true'] : self::$_lang['condition']['false'];
			$getProdDB['minimum_order'] = (int)$getProdDB['minimum_order'];
			$getProdDB['interact_data'] = array(
				'id_product'=>$getProdDB['id'],
				'id_seller'=>$getProdDB['seller_id'],
				'id_seller_owner'=>$getProdDB['seller_detail']['owner_id'],
				'id_category'=>$getProdDB['category'],
				'slug_product'=>$getProdDB['name'],
				'slug_seller'=>$getProdDB['seller_detail']['slug'],
				'slug_seller_owner'=>$getProdDB['seller_detail']['owner_detail']['user_name'],
				'slug_category'=>$getProdDB['category_detail']['slug'],
				'seller_name'=>$getProdDB['seller_detail']['name'],
				'ready_order'=>($getProdDB['stock'] < $getProdDB['minimum_order'] ? false : true)
			);
			$getProdDB['seller_detail']['score'] = array('icon'=>array(), 'medal'=>array(), 'number'=>0, 'slug'=>null);
			
			// Check jika produk ini MILIKMUUUUU
			$findUserID = self::$_userExist ? self::$_userExist[0]['idsecure'] : null;
			$nu_aing = $sellerDetail['owner_id'] == $findUserID ? true : false;
			$pushOtherProduct = array();
			if($nu_aing == false)
			{
				// Check Favorited Product
				$whereFav = array(':id_product'=>$getProdDB['id'], ':id_buyer'=>self::$_userExist[0]['idsecure']);
				$checkFavoriteExist = parent::_handler('crud', self::$_cabinetCluster)->getDataWhere(self::$_favoriteTable, null, $whereFav);
				$getProdDB['favorited'] = $checkFavoriteExist ? true : false;
				
				// Check Followed Seller
				$whereFollow = array(':id_seller'=>$getProdDB['seller_id'], ':id_buyer'=>self::$_userExist[0]['idsecure']);
				$checkFollowExist = parent::_handler('crud', self::$_cabinetCluster)->getDataWhere(self::$_followTable, null, $whereFollow);
				$getProdDB['followed'] = $checkFollowExist ? true : false;
				//var_dump($checkFollowExist);
				
				// Check Others Product
				$othersProduct = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
					self::$_thisTable, 
					array('id','slug','name','thumb','fix_price','stock','minimum_order'), 
					array(':seller_id'=>$getProdDB['seller_id']), 4);
				$othersProduct = parent::_loopkurs($othersProduct, 'fix_price');
				for($i=0;$i<count($othersProduct);$i++)
				{
					if($othersProduct[$i]['id'] != $getProdDB['id'] && $othersProduct[$i]['stock'] > $othersProduct[$i]['minimum_order'])
					{
						$othersProduct[$i]['component'] = self::$_thisComponentIonic;
						array_push($pushOtherProduct, $othersProduct[$i]);
					}
				}

				Imports::name('Seller')->from('service');
				$calculateScore = sellerServices::calculateScore(array('db'=>$sellerDetail, 'load'=>false));
				//var_dump(sellerServices::calculateScore(array('db'=>$sellerDetail, 'load'=>false)));
				$getProdDB['seller_detail']['score'] = $calculateScore;
			}

			$return = array(
				'server'=>array(
					'seller'=>self::$_cdnSeller,
					'icon'=>self::$_cdnIcon,
					'product'=>self::$_cdnProduct
				),
				'product'=>array($getProdDB),
				'other_product'=>$pushOtherProduct,
				'nu_aing'=>$nu_aing
			);

			return $return;
		}
		else
		{
			return false;
		}
	}


	/*
	 * Sorting
	 */
	protected static function sorting($param)
	{
		if(isset($param['sorting']))
		{
			if($param['sorting'] == 1)
			{
				$order = array('row'=>'name', 'sort'=>'ASC');
				$group = null;
			}
			elseif(in_array($param['sorting'], array(2,3)))
			{
				$sortDesc = $param['sorting'] == 2 ? 'ASC' : 'DESC';
				$order = array('row'=>'fix_price', 'sort'=>$sortDesc);
				$group = null;
			}
			elseif($param['sorting'] == 4)
			{
				$order = array('row'=>'returned', 'sort'=>'ASC');
				$group = array('row'=>'returned');
			}
			else
			{
				$order = array('row'=>'id', 'sort'=>'ASC');
				$group = null;
			}
		}
		else
		{
			$order = array('row'=>'add_date', 'sort'=>'DESC');
			$group = null;
		}

		return array('order'=>$order, 'group'=>$group);
	}


	/** 
	 * Method Filter Product untuk menampilkan data produk sesuai kategorinya atau penjualnya
	 */
	protected static function filterProduct($param, $filterType=null)
	{
		/**
		 * Untuk mengisi nilai boolean pada where (filter)
		 * di parameter isikan nilai 1 atau 'true' untuk true
		 * di parameter isikan nilai kosong atau '' untuk false
		 */
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		if($loadLib == true) self::load($param);

		// Menentukan parameter pencarian jika $param['search'] terdapat di XHTTP
		if(isset($param['search'])) $param['search'] = strtolower($param['search']);

		// Menyiapakan parameter SELECT database
		$select = array(
			'id','slug','name','thumb','fix_price','sku','returned',
			'flash_sale','seller_id','stock','minimum_order','category'
		);
		$simple = isset($param['simple']) ? $param['simple'] : false;
		$stockshow = isset($param['stockshow']) ? $param['stockshow'] : false;
		$limit = isset($param['limit']) ? $param['limit'] : null;
		$status = isset($param['status']) ? $param['status'] : 1;

		// Menentukan pengambilan database sesuai $filterType
		if(is_null($filterType))
		{
			// Jika $filterType kosong maka akan ditentukan default
			$where = array(':status'=>$status);
			
			// Menentukan kondisi jika filter default dan terdapat parameter search
			if(isset($param['search']))
			{
				$search = array(':name'=>$param['search'], ':description'=>$param['search'], ':tag'=>$param['search']);
				$getProdDB = parent::_handler('crud', self::$_ClusterDB)->searchData(self::$_thisTable, $search, $select, $limit);
			}
			else
			{
				$getProdDB = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, $select, $where);
			}
		}
		else
		{
			// Jika $filterType tersedia maka parameter $param['filter'] wajib dicantumkan di XHTTP
			if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('filter')) == false) return array(
				'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
			);

			// Menentukan kondisi $filterType jika berisi string 'sort' atau 'category'
			if(in_array($filterType, array('sort','category')))
			{
				$filterArrayParam = array(
					'cluster'=>'config', 
					'table'=>'category_product', 
					'select'=>'id',
					'whereReference'=>':slug', 
					'whereMatch'=>':category'
				);
			}
			// Menentukan kondisi $filterType jika berisi string 'seller'
			elseif($filterType == 'seller')
			{
				$filterArrayParam = array(
					'cluster'=>'account', 
					'table'=>'seller', 
					'select'=>'id',
					'whereReference'=>':id', 
					'whereMatch'=>':seller_id'
				);
			}
			// Menentukan kondisi $filterType jika tidak berisi string 'sort'/'category'/'seller'
			else
			{
				$filterArrayParam = false;
			}

			// Menentukan nilai untuk di match kedalam pengambilan database sesuai isi filter
			$filterGetMatch = isset($param['filter']) && $filterArrayParam !== false ? (
				parent::_relation(null, array(
					$filterArrayParam['cluster'], $filterArrayParam['table'], $filterArrayParam['select'], 
					array($filterArrayParam['whereReference']=>$param['filter'])),
				false,false)
			) : false;

			$filterGetMatch = is_numeric($filterGetMatch) ? (int)$filterGetMatch : $filterGetMatch;
			$filterQuery = $filterGetMatch != false ? array($filterArrayParam['whereMatch']=>$filterGetMatch) : null;
			$filterQuery[':status'] = $status;
			//var_dump($filterQuery);
		
			$sorting = self::sorting($param);
			$order = $where == 1 ? $sorting['order'] : null;
			$group = $where == 1 ? $sorting['group'] : null;
			$where = $filterQuery;
			$priceMin = isset($param['price_min']) ? $param['price_min'] : null;
			$priceMax = isset($param['price_max']) ? $param['price_max'] : null;
			$priceLimit = is_null($priceMin) && is_null($priceMax) ? array() : array('min'=>array(':fix_price',(int)$priceMin), 'max'=>array(':fix_price',(int)$priceMax));
			$priceLimit =  $where == 1 ? $priceLimit : null;
			$lookup = isset($param['search']) ? array(':name'=>$param['search'], ':slug'=>$param['search'], ':description'=>$param['search']) : array();
			
			//var_dump($where);die();
			$getProdDB = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
				self::$_thisTable, $select, $where, $group, $order, $limit, $lookup, $priceLimit
			);
		}
		
		if($getProdDB)
		{
			if($simple == false)
			{
				$getProdDB = self::loopMatchRelation(
					$getProdDB, 'config', 'category_product', array('name', 'section', 'slug'), 'category_detail', array(':id','category')
				);

				$getProdDB = self::loopMatchRelation(
					$getProdDB, 'account', 'seller', array('name', 'slug', 'logo', 'premium_shop_type', 'location', 'city', 'province'), 'seller_detail', array(':id','seller_id')
				);
			}

			$productReturn = parent::_loopkurs($getProdDB, 'fix_price');
			for($i=0;$i<count($productReturn);$i++)
			{
				$productReturn[$i]['component'] = self::$_thisComponentIonic;
			}

			if($stockshow == false)
			{
				if($where == 1)
				{
					$pushOtherProduct = array();
					for($i=0;$i<count($productReturn);$i++)
					{
						if($productReturn[$i]['stock'] > $productReturn[$i]['minimum_order'])
						{
							array_push($pushOtherProduct, $productReturn[$i]);
						}
					}
					$productReturn = $pushOtherProduct;
				}
			}
			//var_dump($productReturn);die();

			$return = array(
				'product'=>$productReturn,
				'server'=>array(
					'seller'=>self::$_cdnSeller,
					'icon'=>self::$_cdnIcon,
					'product'=>self::$_cdnProduct
				)
			);

			if($filterType == 'seller')
			{
				$return['approve'] = true;
				$return['message'] = self::$_lang['global']['success'];
			}
			
			if($simple == false)
			{
				$return['total'] = count($productReturn);
				if($where == 1)
				{
					if(array_key_exists(':status', $where)) $where[':status'] = $where[':status'] == 1 ? 'true' : 'false';
					$rangePrice = parent::_handler('crud', self::$_ClusterDB)->minmax(self::$_thisTable, 'fix_price', $where);
					for($i=0;$i<count($rangePrice);$i++)
					{
						$rangePrice[$i]['min'] = (int)$rangePrice[$i]['min'];
						$rangePrice[$i]['max'] = (int)$rangePrice[$i]['max'];
					}
					$return['range_price'] = $rangePrice[0];
				}
			}
			
			//var_dump($where);die();
			return $return;
		}
		else
		{
			if($filterType == 'seller')
			{
				return array('approve'=>true, 'message'=>self::$_lang['global']['failes']);
			}
			else
			{
				return false;
			}
		}
	}


	/** 
	 * Method Search Product untuk mencari produk berdasarkan kata kunci 
	 */
	protected static function favoriteSearch($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		
		$getFav = parent::_handler('crud', self::$_cabinetCluster)->getDataFilter(
			self::$_favoriteTable, null, array(':id_buyer'=>$findUserID), null, null, null, array(':slug'=>$param['search'])
		);
		
		if(!$getFav) if(count($getFav) < 1) return array('approve'=>false, 'message'=>self::$_lang['read']['failed']); 

		import_snippet('arrayMerge');
		$collectFav = getLoopValueFromOneIndexArray(array('data'=>$getFav, 'cellGrab'=>'id_product', 'cellUnset'=>'id_product'));
		$returnFavProd = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(
			self::$_thisTable, array('id','slug','name','thumb','fix_price','seller_id','stock','minimum_order'), array('id', array_unique($collectFav))
		);

		$returnFavProd = parent::_loopkurs($returnFavProd, 'fix_price');
		for($i=0;$i<count($returnFavProd);$i++)
		{
			$returnFavProd[$i]['component'] = self::$_thisComponentIonic;
			$returnFavProd[$i]['ready_order'] = $returnFavProd[$i]['stock'] < $returnFavProd[$i]['minimum_order'] ? false : true;
			unset($returnFavProd[$i]['stock']);
			unset($returnFavProd[$i]['minimum_order']);
		}

		return array(
			'server'=>array(
				'product'=>self::$_cdnProduct
			),
			'approve'=>true,
			'message'=>self::$_lang['crud']['read']['success'],
			'data'=>$returnFavProd
		);
	}



	protected static function favoritingProduct($param)
	{
		self::load($param);
		//var_dump(self::$_token); return false;
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('id_product','id_seller','slug_product','cluster');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		// $findUserID is get Users Database with search DB COndition where by user token
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		
		$param['favorited'] = isset($param['favorited']) ? $param['favorited'] : false;
		$whereFav = array(':id_product'=>$param['id_product'], ':id_buyer'=>$findUserID);
		$checkFavoriteExist = parent::_handler('crud', $param['cluster'])->getDataWhere(self::$_favoriteTable, null, $whereFav);
		if($param['favorited'] == true)
		{
			if(!$checkFavoriteExist) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['favorite']['failed']);
			$favoriting = parent::_handler('crud', $param['cluster'])->deleteData(self::$_favoriteTable, array(':id_product'=>$param['id_product'], ':id_buyer'=>$findUserID));
			if($favoriting)
			{
				return array(
					'approve'=>true, 
					'message'=>self::$_lang['crud']['delete']['success'], 
					'favoriting'=>false, 
					'id'=>$param['id_product']
				);
			}	
		}
		else
		{
			if($checkFavoriteExist) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['favorite']['exist']);
			$where = array(':id'=>$param['id_product'], ':seller_id'=>$param['id_seller']);
			$checkProduct = parent::_handler('crud', 'sensus')->getDataWhere('product', 'id', $where);
			if($checkProduct !== false)
			{
				$paramSet = array(
					':id_buyer'=>$findUserID, 
					':id_seller'=>$param['id_seller'], 
					':id_product'=>$param['id_product'], 
					':slug'=>$param['slug_product'], 
					':add_date'=>date('Y-m-d')
				);
				$favoriting = parent::_handler('crud', $param['cluster'])->insertData(self::$_favoriteTable, $paramSet);

				if($favoriting)
				{
					return array(
						'approve'=>true, 
						'message'=>self::$_lang['ecommerce']['favorite']['success'], 
						'favoriting'=>true, 
						'id'=>$param['id_product']
					);
				}
				else
				{
					return array(
						'approve'=>false,
						'message'=>self::$_lang['ecommerce']['favorite']['failed']
					);
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['ecommerce']['favorite']['denied']
				);
			}
		}
	}


	
	protected static function favoriteList($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$param['check_user'] = isset($param['check_user']) ? $param['check_user'] : true;
		if($param['check_user'] == true)
		{
			if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
			$findUserID = self::$_userExist[0]['idsecure'];
		}
		else
		{
			$findUserID = array(array('idsecure'=>$param['check_user']));
		}
		
		$whereFav = array(':id_buyer'=>$findUserID);
		$checkFavoriteExist = parent::_handler('crud', self::$_cabinetCluster)->getDataWhere(self::$_favoriteTable, null, $whereFav);
		if($checkFavoriteExist)
		{
			//var_dump(self::$_ClusterDB, self::$_thisTable);
			import_snippet('arrayMerge');
			$checkFavoriteList = getLoopValueFromOneIndexArray(array('data'=>$checkFavoriteExist, 'cellGrab'=>'id_product', 'cellUnset'=>'id_product'));
			$returnFavProd = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(self::$_thisTable, array('id','slug','name','thumb','fix_price','seller_id','stock','minimum_order'), array('id', array_unique($checkFavoriteList)));
			$returnFavProd = parent::_loopkurs($returnFavProd, 'fix_price');

			for($i=0;$i<count($returnFavProd);$i++)
			{
				$returnFavProd[$i]['component'] = self::$_thisComponentIonic;
				$returnFavProd[$i]['ready_order'] = $returnFavProd[$i]['stock'] < $returnFavProd[$i]['minimum_order'] ? false : true;
				unset($returnFavProd[$i]['stock']);
				unset($returnFavProd[$i]['minimum_order']);
			}

			return array(
				'server'=>array(
					'product'=>self::$_cdnProduct
				),
				'approve'=>true,
				'message'=>self::$_lang['crud']['read']['success'],
				'data'=>$returnFavProd
			);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['crud']['read']['failed']
			);
		}
	}

	protected static function fieldInsert($param)
	{
		self::load($param);
		if(self::$_userExist == false) return self::$_lang['error']['403_message'];
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);
		
		$translateEcommerce = self::$_lang['ecommerce']['product'];
		$translateGlobal = self::$_lang['global']['form'];
		$fetch = parent::_handler('crud', self::$_ClusterDB)->showRowSchema('public', self::$_thisTable);
		if(!$fetch) return array('approve'=>false, 'message'=>$translateEcommerce['denied']);

		$fetch = parent::_handler('array')->reindexInput($fetch, array('column_name', array()), array(0,1,3,4,6,9,10,11,13,15,16,19,20,21,22,23,24,25,26,27));
		for($i=0;$i<count($fetch);$i++)
		{
			$fetch[$i]['label'] = $translateEcommerce['form'][$fetch[$i]['field']];
			$fetch[$i]['placeholder'] = strtolower(sprintf($translateGlobal['type'], $fetch[$i]['placeholder']));
			if(in_array($fetch[$i]['field'], array('name'))) $fetch[$i]['type'] = 'text';
			if(in_array($fetch[$i]['field'], array('fix_price','weight_gram','stock','minimum_order'))) $fetch[$i]['type'] = 'number';
			if(in_array($fetch[$i]['field'], array('description'))) $fetch[$i]['type'] = 'textarea';
			if(in_array($fetch[$i]['field'], array('condition'))) $fetch[$i]['type'] = 'select';
			if(in_array($fetch[$i]['field'], array('category'))) $fetch[$i]['type'] = 'hide';

			$langConfition = self::$_lang['condition'];
			if($fetch[$i]['field'] == 'condition') $fetch[$i]['sub_value'] = array(
				array('name'=>$langConfition['true'], 'value'=>true),
				array('name'=>$langConfition['false'], 'value'=>false)
			);

			$langStatus = self::$_lang['bool'];
			if($fetch[$i]['field'] == 'status') $fetch[$i]['sub_value'] = array(
				array('name'=>$langStatus['true'], 'value'=>true),
				array('name'=>$langStatus['false'], 'value'=>false)
			);
		}

		$fetch = self::array_push_assoc($fetch, 3, array(
			'label'=>$translateEcommerce['form']['category'],
			'field'=>'category_text',
			'name'=>'category_text',
			'placeholder'=>strtolower(sprintf($translateGlobal['type'], 'kategori')),
			'type'=>'autocomplete',
			'value'=>''
		));

		return array(
			'approve'=>true,
			'message'=>self::$_lang['crud']['read']['success'],
			'data'=>$fetch,
			'photo'=>array('field'=>'photo', 'value'=>array()),
			'readwrite'=>$accessDecision,
			'server'=>self::$_cdnDomain
		);
	}
	
	/*
	 * Fungsi untuk mengenerate informasi akun buyer
	 */
	protected static function fieldProduct($param)
	{
		self::load($param);
		// Cek token untuk sekuritas Level 1
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		// Cek ketersediaan data buyer untuk sekuritas Level 2
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		
		// Cek isset parameter planning untuk sekuritas Level 3
		if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('planning')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset'].' (mode)'
		);

		// Cek & limit parameter planning untuk sekuritas Level 4
		if(!in_array($param['planning'], array('create', 'update'))) return array(
			'approve'=>false, 'message'=>self::$_lang['crud']['create']['denied']
		);

		$param['load'] = false;
		if($param['planning'] == 'create') return self::fieldInsert($param);

		// Cek isset slug produk untuk sekuritas Level 5
		if(parent::_handler('validate', $param)->issetAndEmptyFalse(array('slug')) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		// Cek level buyer untuk sekuritas Level 6
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$getSeller = parent::_handler('crud', self::$_accountCluster)->getDataWhere(
			self::$_sellerTable, 'id', array(':owner_id'=>$findUserID)
		);

		if(!$getSeller) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['failed']);
		$select = array('slug','name','category','fix_price','weight_gram','stock','minimum_order','description','condition','status','photo');
		$getProduct = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_thisTable, $select, array(':seller_id'=>$getSeller[0]['id'], ':slug'=>$param['slug'])
		);

		//var_dump($getProduct);die();
		$slug = $getProduct[0]['slug'];
		unset($getProduct[0]['slug']);
		$translateEcommerce = self::$_lang['ecommerce']['product'];
		$translateGlobal = self::$_lang['global']['form'];
		if(!$getProduct) return array('approve'=>false, 'message'=>$translateEcommerce['denied']);
		
		$category_id = array_column($getProduct, 'category');
		$category_name = parent::_handler('crud', 'config')->getDataWhere('category_product', 'name', array(':id'=>$category_id[0]));
		
		$tmpArray = array();
		$gallery = array();
		foreach($getProduct[0] as $konci=>$nilai)
		{
			if(in_array($konci, array('name'))) $type = 'text';
			if(in_array($konci, array('fix_price','weight_gram','stock','minimum_order'))) $type = 'number';
			if(in_array($konci, array('description'))) $type = 'textarea';
			if(in_array($konci, array('condition'))) $type = 'select';
			if(in_array($konci, array('category'))) $type = 'hide';

			$placeholder = strtolower(sprintf($translateGlobal['type'], $konci));
			$dataArray = array(
				'field'=>$konci, 
				'value'=>$nilai, 
				'type'=>$type,
				'label'=>$translateEcommerce['form'][$konci],
				'placeholder'=>$placeholder
			);

			$langConfition = self::$_lang['condition'];
			if($konci == 'condition') $dataArray['sub_value'] = array(
				array('name'=>$langConfition['true'], 'value'=>true),
				array('name'=>$langConfition['false'], 'value'=>false)
			);

			$langStatus = self::$_lang['bool'];
			if($konci == 'status') $dataArray['sub_value'] = array(
				array('name'=>$langStatus['true'], 'value'=>true),
				array('name'=>$langStatus['false'], 'value'=>false)
			);

			if($konci == 'photo')
			{
				array_push($gallery, array('field'=>$konci, 'value'=>(is_null($nilai) || empty($nilai) ? array() : unserialize($nilai))));
			}
			if(!in_array($konci, array('photo'))) array_push($tmpArray, $dataArray);
		}

		/* 
		 * Menambahkan field baru untuk "ALIAS" input sebagai referensi pencarian kategori berupa ID menjadi nama
		 * Kemudian merubah posisi array (1) untuk kolom category (sesuai urutan select row database getProduct)
		 */
		$tmpArray = self::array_push_assoc($tmpArray, 1, array(
			'field'=>'category_text', 'value'=>$category_name[0]['name'], 'type'=>'autocomplete', 'label'=>$translateEcommerce['form']['category'], 
			'placeholder'=>strtolower(sprintf($translateGlobal['type'], 'category'))
		));

		//var_dump($tmpArray);die();

		return array(
			'approve'=>true,
			'message'=>self::$_lang['crud']['read']['success'],
			'data'=>$tmpArray,
			'photo'=>$gallery[0],
			'slug'=>$slug,
			'server'=>self::$_cdnDomain
		);
	}

	protected static function array_push_assoc($array, $key, $value)
	{
		$array_push = [];
		for($i=0;$i<count($array);$i++)
		{
			if($i == $key) array_push($array_push, $array[$i]);
		}
		$array[$key] = $value;
		$array[$key+count($array)] = $array_push[0];
		return array_values($array);
	}
	
	protected static function insertProduct($param)
	{
		self::load(array('load'=>$param['load']));

		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$getShop = parent::_handler('crud', self::$_accountCluster)->getDataWhere(
			self::$_sellerTable, array('id','code_shop'), array(':owner_id'=>$findUserID)
		);

		if(!$getShop) return array('approve'=>false, 'message'=>self::$_lang['ecommerce']['seller']['denied']);
		if(isset($param['cluster'])) unset($param['cluster']);

		$messageGlobal = '';
		$paramSet = array();
		if(isset($param['trace'])) 
		{
			$paramSet = parent::_handler('array')->reTrace($param, '', null, true);
			if(isset($paramSet[':category_text'])) unset($paramSet[':category_text']);
			$messageGlobal = '(validate form)';
		}
		else
		{
			$paramPush = [];
			foreach($param as $k=>$v)
			{
				$paramPush[":$k"] = $v;
			}

			$paramSet = $paramPush;
		}

		foreach($paramSet as $key=>$val)
		{
			//var_dump($key);
			if(empty($val) || $val == '' || strlen($val) < 1) unset($paramSet[$key]);
		}
		
		// Cek isset parameter planning untuk sekuritas Level 3
		$paramValidate = array(':name');
		if(parent::_handler('validate', $paramSet)->issetAndEmptyFalse($paramValidate) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset'].' (mode)'
		);

		$paramSet[':seller_id'] = $getShop[0]['id'];
		$paramSet[':slug'] = strtolower((date('ym')) .'_'. $getShop[0]['code_shop'] .'_'. (preg_replace('/[^a-zA-Z0-9\_\-]/', '_', $paramSet[':name'])));
		$paramSet[':sku'] = parent::_handler('generator')->getFirstLetterWords($paramSet[':name']).$paramSet[':seller_id'].date('Ym');
		$paramSet[':add_date'] = date('Y-m-d H:i:s');
		$paramSet[':status'] = isset($paramSet[':status']) ? (
			empty($paramSet[':status']) ? 0 : $paramSet[':status']
		) : 0;

		if(isset($paramSet[':photo']))
		{
			if(is_array($paramSet[':photo']))
			{
				if(count($paramSet[':photo']) == 1) $paramSet[':thumb'] = $paramSet[':photo'][0];
			}

			$paramSet[':photo'] = is_array($paramSet[':photo']) ? (
				count($paramSet[':photo']) > 0 ? serialize(array_values($paramSet[':photo'])) : null
			) : null;

			$messageGlobal = '(insert photo)';
		}

		//var_dump($paramSet);die();
		
		$insertProduct = parent::_handler('crud', self::$_ClusterDB)->insertData(self::$_thisTable, $paramSet);
		if(!$insertProduct) return array('approve'=>false, 'message'=>self::$_lang['crud']['create']['failed']);
		return array('approve'=>true, 'message'=>self::$_lang['crud']['create']['success']." $messageGlobal");
	}

	protected static function updateProduct($param, $id)
	{
		self::load(array('load'=>true));
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$accessDecision = parent::_access('partner', self::$_userExist);
		if($accessDecision == false) return array('approve'=>false, 'message'=>self::$_lang['access']['denied']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$getShop = parent::_handler('crud', self::$_accountCluster)->getDataWhere(
			self::$_sellerTable, 'id', array(':owner_id'=>$findUserID)
		);

		if(!$getShop) return array('approve'=>false, 'message'=>self::$_lang['ecommerce']['seller']['denied']);
		if(isset($param['cluster'])) unset($param['cluster']);

		$messageGlobal = '';
		$paramSet = array();
		if(isset($param['trace'])) 
		{
			$paramSet = parent::_handler('array')->reTrace($param, '', null, true);
			if(isset($paramSet[':category_text'])) unset($paramSet[':category_text']);
			$messageGlobal = '(update traced)';
		}
		else
		{
			$paramPush = [];
			foreach($param as $k=>$v)
			{
				$paramPush[":$k"] = $v;
			}

			$paramSet = $paramPush;
		}

		$paramSet[':edit_date'] = date('Y-m-d H:i:s');
		if(isset($paramSet[':condition'])) if(is_bool($paramSet[':condition'])) $paramSet[':condition'] = $paramSet[':condition'] == true ? 1 : 0;
		if(isset($paramSet[':status'])) if(is_bool($paramSet[':status'])) $paramSet[':status'] = $paramSet[':status'] == true ? 1 : 0;

		if(isset($paramSet[':photo']))
		{
			if(is_array($paramSet[':photo']))
			{
				if(count($paramSet[':photo']) == 1) $paramSet[':thumb'] = $paramSet[':photo'][0];
			}

			$paramSet[':photo'] = is_array($paramSet[':photo']) ? (
				count($paramSet[':photo']) > 0 ? serialize(array_values($paramSet[':photo'])) : null
			) : null;

			$messageGlobal = '(update photo)';
		}

		//var_dump($paramSet);die();
		if(isset($paramSet[':hapus_foto']))
		{
			if(is_string($paramSet[':hapus_foto']))
			{
				$removesFiles = parent::_handler('file', array(
					'filename'=>$paramSet[':hapus_foto'])
				)->removeFiles('product');
				if($removesFiles == true) $messageGlobal = '(photo trashed)';
			}
			unset($paramSet[':hapus_foto']);
		}
		//var_dump($paramSet);die();
		
		$updateProduct = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, array(':slug'=>$id), $paramSet);
		if(!$updateProduct) return array('approve'=>false, 'message'=>self::$_lang['crud']['update']['failed']);
		return array('approve'=>true, 'message'=>self::$_lang['crud']['update']['success']." $messageGlobal");
	}

	protected static function disableProduct($id)
	{
		$setData = self::updateProduct(array(':'));
	}
}
?>