<?php if(!defined('_thisFileDIR')) header('Location:..');

class CartServices extends OfanCoreFramework
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
	private static $_thisComponentIonic;

	private static function load($param=null)
	{
		$cluster = 'transaction';
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'shippingHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'cart';
		self::$_thisComponentIonic = 'CartPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
	}
	
	// Menghitung jumlah pembelian item pada setiap barang di keranjang
	protected static function getAllQuantity($data, $unserialize=true)
	{
		$allQuantity = [];
		for($i=0;$i<count($data);$i++)
		{
			$thisDataProduct = $unserialize == true ? unserialize($data[$i]['data_product']) : $data[$i]['data_product'];
			for($ic=0;$ic<count($thisDataProduct);$ic++)
			{
				if(isset($thisDataProduct[$ic]['stock']) && isset($thisDataProduct[$ic]['minimum_order']))
				{
					if($thisDataProduct[$ic]['stock'] > $thisDataProduct[$ic]['minimum_order']) array_push($allQuantity, $thisDataProduct[$ic]['quantity']);
				}
				else
				{
					array_push($allQuantity, $thisDataProduct[$ic]['quantity']);
				}
			}
		}

		return array_sum($allQuantity);
	}

	// Menghitung jumlah total barang pada keranjang
	public static function countCart($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure']; 
		
		$listCart = parent::_handler('crud', self::$_ClusterDB)
		->getDataWhere(self::$_thisTable, 'data_product', array(':buyer_id'=>$findUserID));
		if($listCart)
		{
			return array(
				'approve'=>true,
				'message'=>self::$_lang['crud']['read']['success'],
				'count'=>self::getAllQuantity($listCart)
			);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['error']['500_message']
			);
		}
	}

	// Menghitung total harga semua barang yang ada di keranjang
	protected static function calculatePrice($data, $unserialize=true)
	{
		if(is_null($data)) return self::$_lang['access']['failed'];
		$allPrice = [];
		for($i=0;$i<count($data);$i++)
		{
			$thisDataProduct = $unserialize == true ? unserialize($data[$i]['data_product']) : $data[$i]['data_product'];
			for($ic=0;$ic<count($thisDataProduct);$ic++)
			{
				if($thisDataProduct[$ic]['stock'] > $thisDataProduct[$ic]['minimum_order'])
				{
					$calculate = ($thisDataProduct[$ic]['fix_price'] * $thisDataProduct[$ic]['quantity']);
					array_push($allPrice, $calculate);
				}
			}
		}

		return array_sum($allPrice);
	}

	// Daftar barang pada keranjang
	protected static function listCart($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$param['check_user'] = isset($param['check_user']) ? $param['check_user'] : true;
		$param['seller_detail'] = isset($param['seller_detail']) ? $param['seller_detail'] : false;
		if($param['check_user'] == true)
		{
			if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
			$findUserID = self::$_userExist[0]['idsecure'];
		}
		else
		{
			$findUserID = $param['check_user'];
		}

		$allCart = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
			self::$_thisTable, null, array(':buyer_id'=>$findUserID), null, array('row'=>'token_cart','sort'=>'DESC')
		);
		if($allCart)
		{
			import_snippet('arrayMerge');
			$allCart = parent::_loopunserialize($allCart, 'data_product');
			for($i=0;$i<count($allCart);$i++)
			{
				for($iy=0;$iy<count($allCart[$i]['data_product']);$iy++)
				{
					/* 
					 * Resource ini sangat besar memakan memory karena di cari satu persatu produknya dengan ID 
					 * Kedepan untuk pengembangan gunakan array_recrusive dengan pencarian ID kolektif getDataWhereIn()
					*/
					$returnItemCart = parent::_handler('crud', 'sensus')->getDataWhere(
						'product', 
						array('id','slug','name','thumb','fix_price','stock','minimum_order'), 
						array(':id'=>$allCart[$i]['data_product'][$iy]['id'])
					);
				
					$returnItemCart[0]['minimum_order'] = (int)$returnItemCart[0]['minimum_order'];
					$returnItemCart[0]['stock'] = (int)$returnItemCart[0]['stock'];
					$returnItemCart[0]['ready_order'] = $returnItemCart[0]['stock'] < $returnItemCart[0]['minimum_order'] ? false : true;
					$allCart[$i]['data_product'][$iy] = array_merge($allCart[$i]['data_product'][$iy], $returnItemCart[0]);
				}
			}
			
			$totalPrice = self::calculatePrice($allCart, false);
			$productReturn = parent::_loopkurs($allCart, array('rootparent'=>'data_product', 'rootsub'=>'fix_price'), true);

			if($param['seller_detail'] == true)
			{
				$collectIDseller = getLoopValueFromOneIndexArray(array('data'=>$productReturn,'cellGrab'=>'seller_id'));
				$dataSelect = parent::_handler('crud', 'account')->getDataWhereIn('seller', array('id','slug','name','premium_shop_type'), array('id', array_unique($collectIDseller)));
				$collectDataSeller = ArrayMergeIndexValueDB($collectIDseller, $dataSelect, 'id');
				for($i=0;$i<count($productReturn);$i++)
				{
					for($is=0;$is<count($collectDataSeller);$is++)
					{
						if($productReturn[$i]['seller_id'] == $collectDataSeller[$is]['id'])
						{
							$productReturn[$i]['seller_detail'] = $collectDataSeller[$is];
						}
					}
				}
			}

			return array(
				'approve'=>true,
				'server'=>array(
					'seller'=>self::$_cdnSeller,
					'icon'=>self::$_cdnIcon,
					'product'=>self::$_cdnProduct
				),
				'data'=>$productReturn,
				'count'=>self::getAllQuantity($productReturn, false),
				'total_price'=>parent::_kurs($totalPrice),
				'total_price_format'=>array(
					'value'=>$totalPrice,
					'symbol'=>self::$_lang['lang']['currency']['symbol']
				)
			);
		}
		else
		{
			
			return array(
				'approve'=>false,
				'message'=>self::$_lang['exist']['false']
			);
		}
	}

	// Menambahkan barang baru pada keranjang
	protected static function insertCart($param)
	{
		self::load($param);
		// Check self::$_token --> FROM SESSION SYSTEM
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('id_seller','id_product','product_qty');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		// $findUserID is get Users Database with search DB COndition where by user token
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];

		$whereCart = array(':seller_id'=>$param['id_seller'], ':buyer_id'=>$findUserID);
		$checkCartExist = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, $whereCart);
		if($checkCartExist)
		{
			// Update Cart if Cart by Users is Exists
			$checkCartExist[0]['load'] = false;
			$checkCartExist[0]['id_seller'] = $checkCartExist[0]['seller_id'];
			$checkCartExist[0]['id_product'] = $param['id_product'];
			$checkCartExist[0]['product_qty'] = $param['product_qty'];
			$checkCartExist[0]['check_user'] = $findUserID; // Dipake dan di buat array value nya ketika ID user seudah ditemukan
			$checkCartExist[0]['note'] = isset($param['note']) ? $param['note'] : '';
			return self::updateCart($checkCartExist[0]);
		}
		else
		{
			// Insert Cart if Cart by Users is Empty
			$tokenCart = parent::_generate('tokencart',array('length'=>5,'id_buyer'=>$findUserID,'id_seller'=>$param['id_seller']));
			$whereProduct = array(':id'=>$param['id_product'], ':seller_id'=>$param['id_seller']);
			$checkProduct = parent::_handler('crud', 'sensus')->getDataWhere('product', array('id'), $whereProduct);
			if($checkProduct)
			{
				//$checkProduct[0]['minimum_order'] = (int)$checkProduct[0]['minimum_order'];
				//$checkProduct[0]['stock'] = (int)$checkProduct[0]['stock'];
				$checkProduct[0]['quantity'] = $param['product_qty'];
				$checkProduct[0]['note'] = isset($param['note']) ? $param['note'] : '';
				$paramSet = array(
					':buyer_id'=>$findUserID, 
					':seller_id'=>$param['id_seller'], 
					':data_product'=>serialize($checkProduct), 
					':token_cart'=>$tokenCart, 
					':add_date'=>date('Y-m-d'),
					':update_date'=>null
				);
				$insertCart = parent::_handler('crud', self::$_ClusterDB)->insertData(self::$_thisTable, $paramSet);
				if($insertCart)
				{
					$listCart = self::listCart(array('load'=>false,'seller_detail'=>false,'check_user'=>$findUserID));
					$listCart = $listCart['data'];
					return array(
						'approve'=>true,
						'message'=>self::$_lang['ecommerce']['cart']['success'] . ' (Inserted)',
						'count'=>self::getAllQuantity($listCart, false)
					);
				}
				else
				{
					return array(
						'approve'=>false,
						'message'=>self::$_lang['ecommerce']['cart']['failed']
					);
				}
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['ecommerce']['cart']['failed']
				);
			}
		}
	}

	// Merubah dan memperbaharui barang yang ada di keranjang
	/*
	 * Note Bugs:
	 * Jumlah quantity barang yang dimasukan kedalam keranjang dari halaman detail produk
	 * Belum di sesuaikan dengan jumlah stok yang tersedia, artinya ketika total quantity di add cart
	 * melebihi stok maka di dalam listCart() tidak menyesuaikan dengan jumlah stok
	 */
	protected static function updateCart($param)
	{
		//var_dump($param);return false;
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('id_seller','id_product','token_cart');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		if($param['load'] == true)
		{
			if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
			$findUserID = self::$_userExist[0]['idsecure'];

			// Check Cart Exist
			$whereCart = array(':seller_id'=>$param['id_seller'], ':buyer_id'=>$findUserID);
			$getCart = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, $whereCart);
			$getCart = $getCart ? $getCart[0] : false;
		}
		else
		{
			// Skip Check if load is false
			$findUserID = $param['check_user'];
			$getCart = isset($param['load']) ? $param['load'] == false ? $param : false : false;
		}

		if($getCart)
		{
			$fromCart = isset($param['form_cart']) ? $param['form_cart'] : null;
			$dataProduct = unserialize($getCart['data_product']);
			$countProduct = count($dataProduct);
			$dbProduct = parent::_handler('crud','sensus')->getDataWhere('product', array('id'), array(':id'=>$param['id_product']));
			
			// Loop data_product after unserialize from cart database
			for($i=0;$i<$countProduct;$i++)
			{
				// If Database product reference by Where id_product is true do execute
				if($dbProduct)
				{
					// Search product cart by id_product from data_product loop
					if(array_search($param['id_product'], array_column($dataProduct, 'id')) !== false)
					{
						// If Found data_product reference between id and id_product
						if($dataProduct[$i]['id'] == $param['id_product'])
						{
							if(isset($param['product_qty']))
							{
								// Change quantity value and break after that
								if(is_null($fromCart))
								{
									$dataProduct[$i]['quantity'] = ($dataProduct[$i]['quantity'] + (int)$param['product_qty']);
								}
								else
								{
									$dataProduct[$i]['quantity'] = (int)$param['product_qty'];
								}
							}

							if(isset($param['note']))
							{
								$dataProduct[$i]['note'] = $param['note'];
							}

							break;
						}
					}
					else
					{
						// Insert new product to data_product with reference id_buyer in cart database
						$dataProduct[$countProduct] = $dbProduct[0];
						$dataProduct[$countProduct]['quantity'] = isset($param['product_qty']) ? $param['product_qty'] : $dbProduct[0]['minimum_order'];
						$dataProduct[$countProduct]['note'] = isset($param['note']) ? $param['note'] : '';
					}
				}
			}
			//var_dump(serialize($dataProduct));
			$paramSet = array(
				':data_product'=>serialize($dataProduct), 
				':update_date'=>date('Y-m-d')
			);

			$updateCart = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, array(':token_cart'=>$param['token_cart']), $paramSet);
			if($updateCart)
			{
				$listCart = self::listCart(array('load'=>false,'seller_detail'=>false,'check_user'=>$findUserID));
				$listCart = $listCart['data'];
				return array(
					'approve'=>true,
					'message'=>self::$_lang['ecommerce']['cart']['success'] . ' (Updated)',
					'count'=>self::getAllQuantity($listCart, false)
				);
			}
			else
			{
				return array(
					'approve'=>false,
					'message'=>self::$_lang['ecommerce']['cart']['failed']
				);
			}
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['ecommerce']['cart']['denied']
			);
		}
	}

	// Menghapus barang yang ada di keranjang
	protected static function deleteCart($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);

		$whereCart = array(':token_cart'=>$param['token_cart']);
		$getCart = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, $whereCart);
		if($getCart)
		{
			$dataProduct = unserialize($getCart[0]['data_product']);
			$countProduct = count($dataProduct);
			if($countProduct > 1)
			{
				// Loop data_product after unserialize from cart database
				for($i=0;$i<$countProduct;$i++)
				{
					// Search product cart by id_product from data_product loop
					if(array_search($param['id_product'], array_column($dataProduct, 'id')) !== false)
					{
						// If Found data_product reference between id and id_product
						if($dataProduct[$i]['id'] == $param['id_product'])
						{
							// Remove Items in carts
							unset($dataProduct[$i]);
							break;
						}
					}
				}
				// Re-Index array list data after unset
				$dataProduct = array_values($dataProduct);
				// Set param to update
				$paramSet = array(
					':data_product'=>serialize($dataProduct), 
					':update_date'=>date('Y-m-d')
				);

				$updateCart = parent::_handler('crud', self::$_ClusterDB)->updateData(self::$_thisTable, array(':token_cart'=>$param['token_cart']), $paramSet);
				if(!$updateCart) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['cart']['failed']);

				return array(
					'approve'=>true,
					'message'=>self::$_lang['ecommerce']['cart']['success'] . ' (Updated)'
				);
			}
			else
			{
				$removeWhere = array(':token_cart'=>$param['token_cart']);
				$removeCart = parent::_handler('crud', self::$_ClusterDB)->deleteData(self::$_thisTable, $removeWhere);
				if(!$removeCart) return array('approve'=>false,'message'=>self::$_lang['crud']['delete']['failed']);
				
				return array(
					'approve'=>true,
					'message'=>self::$_lang['crud']['delete']['success']
				);
			}
		}
	}
}
?>