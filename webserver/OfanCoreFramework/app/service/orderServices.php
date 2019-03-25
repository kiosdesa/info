<?php if(!defined('_thisFileDIR')) header('Location:..');

class OrderServices extends OfanCoreFramework
{
	private static $_ClusterDB;
	private static $_secondCluster;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;
	private static $_secondTable;
	private static $_thirdTable;
	private static $_fourthTable;
	private static $_fifthTable;
	private static $_sixthTable;
	private static $_cdnIcon;
	private static $_cdnProduct;
	private static $_cdnSeller;
	private static $_thisComponentIonic;
	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$cluster = 'transaction';
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array(
				'dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 
				'fileHandler', 'shippingHandler', 'cryptoHandler', 'dateHandler')
			);
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_secondCluster = 'account';
		self::$_thisTable = 'cart';
		self::$_secondTable = 'order';
		self::$_thirdTable = 'complaint';
		self::$_fourthTable = 'review_order';
		self::$_fifthTable = 'buyer';
		self::$_sixthTable = 'seller';
		self::$_thisComponentIonic = 'OrderPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
		self::$_cdnSeller = parent::_cdnDirectorySeller();
	}

	protected static function _this_orders_grabRoot($data, $index)
	{
		$push = array();
		foreach($data as $arg)
		{
			$push[$arg[$index]] = $arg;
		}
		return $push;
	}

	/*
	 * Count All Orders
	 */
	public static function countOrder($param)
	{
		self::load($param);
		if(isset($param['user_check']))
		{
			$findUserID = $param['user_check'];
		}
		else
		{
			if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
			if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
			$findUserID = self::$_userExist[0]['idsecure'];
		}

		$listOrder = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			'public.'.self::$_secondTable, null, array(':buyer_id'=>$findUserID)
		);

		if($listOrder)
		{
			$pushRecieve = [];
			$pushProcess = [];
			$pushVerify = [];
			for($i=0;$i<count($listOrder);$i++)
			{
				if($listOrder[$i]['status_shipping'] == 7) array_push($pushRecieve, $listOrder[$i]['status_shipping']);
				if($listOrder[$i]['status_shipping'] == 1) array_push($pushVerify, $listOrder[$i]['token']);
				if(!in_array($listOrder[$i]['status_shipping'], array(1,7,8,9,10))) array_push($pushProcess, $listOrder[$i]['status_shipping']);
			}
			//var_dump($pushRecieve);die();

			return array(
				'approve'=>true,
				'message'=>self::$_lang['crud']['read']['success'],
				'count'=>array(
					'placed'=>count($listOrder),
					'receive'=>count($pushRecieve),
					'process'=>count($pushProcess),
					'verify'=>count(array_unique($pushVerify)),
					'items'=>self::getAllQuantity($listOrder)
				)
			);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['error']['500_message'],
				'count'=>array(
					'placed'=>0,
					'receive'=>0,
					'process'=>0,
					'verify'=>0,
					'items'=>0
				)
			);
		}
	}

	/*
	 * Count all Quantity Orders
	 */
	protected static function getAllQuantity($data, $unserialize=true)
	{
		$allQuantity = [];
		for($i=0;$i<count($data);$i++)
		{
			$thisDataProduct = $unserialize == true ? unserialize($data[$i]['data_product']) : $data[$i]['data_product'];
			for($ic=0;$ic<count($thisDataProduct);$ic++)
			{
				if($thisDataProduct[$ic]['stock'] > $thisDataProduct[$ic]['minimum_order'])
				{
					array_push($allQuantity, $thisDataProduct[$ic]['quantity']);
				}
			}
		}

		return array_sum($allQuantity);
	}

	/*
	 * Fixing this -> "&" into "n" because X-HTTP JSON response error if not replace that text
	 */
	protected static function reformatShipperName($data)
	{
		$str = preg_replace('/([a-zA-Z]+)(\s)(.*)/', '$1', $data);
		return preg_replace('/([\&])/', 'n', $str);
	}

	/*
	 * Passing Shipping Function to calculate all ammount with shipping from Orders Method
	 */
	protected static function calculateShipping($data)
	{
		$tariffParam = array(
			'type'=>'tokped',
			'from'=>$data['from'],
			'to'=>$data['to'],
			'weight'=>$data['weight'],
			'service'=>'regular',
			'thenames'=>$data['name']
		);
		$tariff = parent::_handler('shipping', $tariffParam)->get('rate');
		return $tariff;
	}

	/*
	 * Calculate all orders Ammount
	 */
	protected static function calculateOrdersAmmount($database, $param, $typePay, $formated=false)
	{
		$dataOrder = $formated == false ? $database : self::reformatOrderIndexValue($database, true);
		$tariffPush = $formated == false ? $param : parent::_grabtoindextop($param, array('index'=>'tc'));

		$pushSubAmount = [];
		$pushTotalAmount = [];
		$pushTotalTariff = [];
		$pushCodeUnique = [];
		/*
		 * Nama alias tc = Token Cart (dari param X-HTTP-Request)
		 * Nama alias asc = address shipping choose (dari param X-HTTP-Request)
		 * Nama alias sc = shipping choose (dari param X-HTTP-Request)
		 * Nama alias nsc = name shipping choose (dari param X-HTTP-Request)
		 * Nama alias isc = ID shipping choose (dari param X-HTTP-Request)
		 */
		foreach($tariffPush as $i=>$v)
		{
			$shippingName = self::reformatShipperName($v['sc']['nsc']);
			$shippingNameLower = strtolower($shippingName);
			$calculateShipping = self::calculateShipping(
				array(
					'from'=>$dataOrder[$i]['seller_detail']['shipping_from_code'],
					'to'=>$v['sc']['asc'],
					'weight'=>$dataOrder[$i]['sub_weight_kilogram'],
					'name'=>$shippingNameLower
				)
			);

			if(!$calculateShipping) break;
			$tariff = $calculateShipping['attributes'][0]['products'][0]['shipper_price'];
			$subTotalShipping = $dataOrder[$i]['subtotal_price'] + intval($tariff);
			$pushSubAmount[$i]['subtotal_price'] = $dataOrder[$i]['subtotal_price'];
			$pushSubAmount[$i]['sub_total_with_shipping'] = $subTotalShipping;
			$pushSubAmount[$i]['shipping_slug'] = $shippingNameLower;
			$pushSubAmount[$i]['shipping_name'] = $shippingName;
			$pushSubAmount[$i]['shipping_time'] = $calculateShipping['attributes'][0]['products'][0]['shipper_etd'];
			$pushSubAmount[$i]['shipping_maxtime'] = $calculateShipping['attributes'][0]['products'][0]['max_etd'];
			$pushSubAmount[$i]['shipping_id'] = $v['sc']['isc'];
			$mdhis = array_sum(explode('-', date('y-m-d-h-i-s')));
			if($typePay != 'balance') array_push($pushCodeUnique, ($mdhis + $dataOrder[$i]['sub_quantity']));
			array_push($pushTotalAmount, $subTotalShipping);
			array_push($pushTotalTariff, intval($tariff));
		}

		//var_dump(count($pushTotalAmount), count($tariffPush));
		if(count($pushTotalAmount) != count($tariffPush)) return false;
		
		$pushTotalAmount = count($pushTotalAmount) == count($tariffPush) ? array_sum($pushTotalAmount) : null;
		$pushTotalTariff = count($pushTotalTariff) == count($tariffPush) ? array_sum($pushTotalTariff) : null;
		$pushCodeUnique = $typePay == 'balance' ? 0 : count($pushCodeUnique) == count($tariffPush) ? array_sum($pushCodeUnique) : null;
		return array(
			'sub_ammount'=>$pushSubAmount,
			'total_tariff'=>$pushTotalTariff,
			'total_ammount'=>$pushTotalAmount,
			'code_unique'=>$pushCodeUnique
		);
	}

	/*
	 * Passing function for extract Seller Detail from Orders Method
	 */
	protected static function sellerDetail($data)
	{
		if(!function_exists('ArrayMergeIndexValueDB'))
		{
			import_snippet('arrayMerge');
		}
		$collectIDseller = getLoopValueFromOneIndexArray(array('data'=>$data,'cellGrab'=>'seller_id'));
		$dataSelect = parent::_handler('crud', self::$_secondCluster)->getDataWhereIn(
			'seller', array('id','slug','name','premium_shop_type','postal_code','district_code','shipping'), 
			array('id', array_unique($collectIDseller))
		);
		$collectDataSeller = ArrayMergeIndexValueDB($collectIDseller, $dataSelect, 'id');
		//var_dump($collectDataSeller);
		for($i=0;$i<count($data);$i++)
		{
			for($is=0;$is<count($collectDataSeller);$is++)
			{
				if($data[$i]['seller_id'] == $collectDataSeller[$is]['id'])
				{
					$data[$i]['seller_detail'] = $collectDataSeller[$is];
				}
			}
			$shippings = unserialize($data[$i]['seller_detail']['shipping']);
			$data[$i]['seller_detail']['shipping'] = $shippings;
			$data[$i]['seller_detail']['shipping_names'] = join($shippings, ',');
			$data[$i]['seller_detail']['shipping_from_code'] = $data[$i]['seller_detail']['district_code'].'|'.$data[$i]['seller_detail']['postal_code'];
		}
		return $data;
	}

	/*
	 * Ini Fungsi sangat memakan resource
	 * Mohon jadi perhatian untuk pengembangan kedepan
	 * Saran: Dibuat lebih ringkas alogaritma nya 
	 */
	protected static function reformatOrderIndexValue($dataOrders, $returnIndexToken=false)
	{
		$dataOrders = parent::_loopunserialize($dataOrders, 'data_product');
		for($i=0;$i<count($dataOrders);$i++)
		{
			$subQty = [];
			$subWeight = [];
			for($iy=0;$iy<count($dataOrders[$i]['data_product']);$iy++)
			{
				/* 
				 * Resource ini sangat besar memakan memory karena di cari satu persatu produknya dengan ID 
				 * Kedepan untuk pengembangan gunakan array_recrusive dengan pencarian ID kolektif getDataWhereIn()
				 * Dengan kendala/stack index dari array sumber & array pembanding mengacak/tidak sesuai
				*/
				$returnItemOrder = parent::_handler('crud', 'sensus')->getDataWhere(
					'product', 
					array('id','slug','name','thumb','fix_price','stock','minimum_order','weight_gram'), 
					array(':id'=>$dataOrders[$i]['data_product'][$iy]['id'])
				);

				$returnItemOrder[0]['minimum_order'] = (int)$returnItemOrder[0]['minimum_order'];
				$returnItemOrder[0]['stock'] = (int)$returnItemOrder[0]['stock'];
				$returnItemOrder[0]['weight_gram'] = (int)$returnItemOrder[0]['weight_gram'];
				$returnItemOrder[0]['weight_kilogram'] = number_format(((float)$returnItemOrder[0]['weight_gram']/1000), 2, '.', '');
				$returnItemOrder[0]['ready_order'] = $returnItemOrder[0]['stock'] < $returnItemOrder[0]['minimum_order'] ? false : true;
				array_push($subQty, $dataOrders[$i]['data_product'][$iy]['quantity']);
				array_push($subWeight, ($returnItemOrder[0]['weight_gram'] * $dataOrders[$i]['data_product'][$iy]['quantity']));
				$dataOrders[$i]['data_product'][$iy] = array_merge($dataOrders[$i]['data_product'][$iy], $returnItemOrder[0]);
			}

			$dataOrders[$i]['sub_quantity'] = array_sum($subQty);
			$dataOrders[$i]['sub_weight_gram'] = array_sum($subWeight);
			$dataOrders[$i]['sub_weight_kilogram'] = $dataOrders[$i]['sub_weight_gram']/1000;

			for($iy=0;$iy<count($dataOrders[$i]['data_product']);$iy++)
			{	
				if($dataOrders[$i]['data_product'][$iy]['ready_order'] == false)
				{
					$dataOrders[$i]['sub_quantity'] = $dataOrders[$i]['sub_quantity'] - $dataOrders[$i]['data_product'][$iy]['quantity'];
					unset($dataOrders[$i]['data_product'][$iy]);
				}
			}
			
			$dataOrders[$i]['data_product'] = array_values($dataOrders[$i]['data_product']);
			$subTotalPrice = parent::_calculateprice($dataOrders[$i]['data_product'], array('stock','minimum_order'), array('fix_price',array_sum($subQty)));
			$dataOrders[$i]['subtotal_price'] = $subTotalPrice;
			$dataOrders[$i]['subtotal_price_format'] = parent::_kurs($subTotalPrice);
		}

		for($i=0;$i<count($dataOrders);$i++)
		{
			if(count($dataOrders[$i]['data_product']) < 1)
			{
				unset($dataOrders[$i]);
			}
		}

		$dataOrders = array_values($dataOrders);
		if($returnIndexToken !== false)
		{
			$dataOrders = parent::_grabtoindextop($dataOrders, array('index'=>'token_cart'));
		}

		return $dataOrders;
	}

	/*
	 * Add New Orders by User from X-HTTP
	 */
	protected static function insertOrder($param)
	{
		self::load($param);
		// Check self::$_token --> FROM SESSION SYSTEM
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('data');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		// $findUserID is get Users Database with search DB Condition where by user token
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$typePay = isset($param['pc']['type']) ? $param['pc']['type'] : 'atm';

		// Mengumpulkan semua nilai token_cart menjadi array khusus untuk referensi
		$grabTokenCart = [];
		for($i=0;$i<count($param['data']);$i++)
		{
			array_push($grabTokenCart, $param['data'][$i]['tc']);
		}

		// Mengambil data cart sesuai nilai array khusus token_cart
		$getCartByToken = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(
			self::$_thisTable, array('buyer_id','seller_id','data_product','token_cart'), array('token_cart', $grabTokenCart)
		);
		//var_dump($grabTokenCart);die();

		// Mempersiapkan beberapa variable yang dibutuhkan untuk insert order baru tahap 1
		$sellerDetailOrders = self::sellerDetail($getCartByToken); // Mengambi data seller sesuai ID
		$reIndexingDataOrder = self::reformatOrderIndexValue($sellerDetailOrders, true, true); // Mengubah format array data orders
		$reIndexingTariffPush = parent::_grabtoindextop($param['data'], array('index'=>'tc')); // Merubah index loop menjadi nilai token_cart
		$calculateShipping = self::calculateOrdersAmmount($reIndexingDataOrder, $reIndexingTariffPush, $typePay, false); // Menghitung total ongkir
		if(!$calculateShipping) return array('approve'=>false,'message'=>self::$_lang['ecommerce']['order']['failed']);

		// Mempersiapkan beberapa variable yang dibutuhkan untuk insert order baru tahap 2
		$findUserID = self::$_userExist[0]['idsecure'];
		$insertDate = strtotime('now');
		$tokenOrder = parent::_handler('crypto')->numstring($findUserID.'-'.date('imsyhd'))->encrypt();
		$invoice = parent::_generate('invoice', array('length'=>4, 'suffix'=>$findUserID), false);
		$invoice = $invoice.date('hs');
		$param['pc']['code_unique'] = $calculateShipping['code_unique'];
		$pc = serialize($param['pc']);
		$adrd = serialize($param['adrd']);
		$paramSet = [];
		$totalPrice = [];

		// Mempersiapkan beberapa data loop untuk di insert ke order baru
		$index = 0;
		foreach($reIndexingDataOrder as $i=>$v) // Nilai $i disini adalah string nilai index token_cart setelah di reformat
		{	
			$index++;
			array_push($paramSet, 
				array(
					'buyer_id'=>$findUserID, 
					'seller_id'=>$reIndexingDataOrder[$i]['seller_id'], 
					'add_date'=>$insertDate,
					'data_product'=>serialize($reIndexingDataOrder[$i]['data_product']), 
					'shipping_vendor'=>serialize(
						array(
							$calculateShipping['sub_ammount'][$i]['shipping_id'], $calculateShipping['sub_ammount'][$i]['shipping_name'],
							$calculateShipping['sub_ammount'][$i]['shipping_time'], $calculateShipping['sub_ammount'][$i]['shipping_maxtime']
						)
					), 
					'payment_vendor'=>$pc, 
					'shipping_area'=>$adrd, 
					'total_payment'=>$calculateShipping['sub_ammount'][$i]['sub_total_with_shipping'], 
					'status_shipping'=>($typePay == 'balance' ? 2 : 1),
					'status_receive'=>0,
					'invoice'=>$invoice.'-'.$index,
					'token'=>$tokenOrder
				)
			);
			
			array_push($totalPrice, $calculateShipping['sub_ammount'][$i]['subtotal_price']);
		}
		//return array($paramSet);die();

		// Untuk simulasi gunakan true & berikan prefix tanda komentar kode untuk produce hapus booelan kembalikan kode awal
		$insertOrder = parent::_handler('crud', self::$_ClusterDB)->insertMultiple('public.'.self::$_secondTable, $paramSet);
		if(!$insertOrder) return array('approve'=>false, 'message'=>self::$_lang['ecommerce']['order']['failed']);
		$removeCart = parent::_handler('crud', self::$_ClusterDB)->deleteData(self::$_thisTable, array(':buyer_id'=>$findUserID));
		if(!$removeCart) return array('approve'=>false,'message'=>self::$_lang['crud']['delete']['failed']);

		// Beberapa nilai array yang di inject kareana harus disesuaikan dengan beberapa kondisi
		$totalAmmount = $calculateShipping['total_ammount'];
		$calculateShipping['total_ammount'] = parent::_kurs($totalAmmount);
		$calculateShipping['total_ammount_format'] = array(
			'value'=>$totalAmmount,
			'symbol'=>self::$_lang['lang']['currency']['symbol']
		);
		$calculateShipping['total_price'] = array_sum($totalPrice);
		$calculateShipping['token'] = $tokenOrder;
		$calculateShipping['invoice_all'] = $invoice.'/'.(count($totalPrice)).'x';

		// Generate message cara melakukan pembayaran sesuai metode pembayaran (bank/atm/emoney/ebanking/merchant)
		$calculateShipping['message'] = $typePay == 'balance' ? null : sprintf(
			self::$_lang['ecommerce']['payment']['method'][$typePay],
			$param['pc']['name'],
			$param['pc']['account']
		);

		$globalSuccessMessage = self::$_lang['ecommerce']['order']['success'];

		// Debit Saku/Balance Buyer jika metode pembayaran BUKU SAKU
		if($typePay == 'balance')
		{
			$getBalanceBuyer = parent::_handler('crud', self::$_secondCluster)->getDataWhere(
				self::$_fifthTable, 'balance', array(':idsecure'=>$findUserID)
			);
			$balanceBuyer = $getBalanceBuyer ? (int)$getBalanceBuyer[0]['balance'] : 0;
			$debitAmmount = $balanceBuyer > $totalAmmount ? $balanceBuyer - $totalAmmount : null;
			$debitBalanceBuyer =is_null($debitAmmount) ? false : parent::_handler('crud', self::$_secondCluster)->updateData(
				self::$_fifthTable, array(':idsecure'=>$findUserID), array(':balance'=>$debitAmmount)
			);
			if($debitBalanceBuyer) $globalSuccessMessage = $globalSuccessMessage.' (debit balance)';
		}

		return array(
			'approve'=>true,
			'message'=>$globalSuccessMessage,
			'data'=>$calculateShipping,
		);
	}

	/*
	 * Update status order from BUTTON UI/UX at lists
	 */
	protected static function updateOrder($param)
	{
		self::load($param);
		// Check self::$_token --> FROM SESSION SYSTEM
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('invoice');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array('approve'=>false,'message'=>self::$_lang['crud']['create']['isset']);

		$findUserID = self::$_userExist[0]['idsecure'];
		$paramSet = array(
			':update_date'=>strtotime('now')
		);

		if(isset($param['status_shipping'])) $paramSet[':status_shipping'] = $param['status_shipping'];
		if(isset($param['status_receive'])) $paramSet[':status_receive'] = $param['status_receive'];
		if(isset($param['message'])) $paramSet[':note'] = $param['message'];


		$where = isset($param['clause']) ? null : array(':invoice'=>$param['invoice']);
		$whereLike = isset($param['clause']) ? (
			$param['clause'] == true ? array(':invoice'=>$param['invoice']) : null
		) : null;

		$updateOrder = parent::_handler('crud', self::$_ClusterDB)->updateData('public.'.self::$_secondTable, $where, $paramSet, $whereLike);

		if($updateOrder)
		{
			$GlobalMessage = self::$_lang['global']['success'];

			// Persiapan dana refund jika status order berubah
			if(isset($param['status_shipping']))
			{
				// Melakukan refund untuk buyer jika status shipping adalah 9 atau 10 (lihat di config)
				if(in_array($param['status_shipping'], array(9,10)))
				{
					$myOrder = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
						'public.'.self::$_secondTable,
						null, array(':buyer_id'=>$findUserID),
						null, null, null, array(':invoice'=>strtolower($param['invoice'])), null
					);
					//var_dump($totalBill);die();

					if($myOrder)
					{
						$getBill = array_sum(array_column($myOrder, 'total_payment'));
						$getBalance = parent::_handler('crud', self::$_secondCluster)->getDataWhere(
							self::$_fifthTable, array('balance'), array(':idsecure'=>$findUserID)
						);
						if($getBalance)
						{
							$getBalance = $getBalance[0]['balance'];
							$getBalance = is_null($getBalance) ? 0 : (
								is_numeric($getBalance) ? $getBalance : 0
							);
							$totalBill = (int)$getBill + $getBalance;
							$refundBalance = parent::_handler('crud', self::$_secondCluster)->updateData(
								self::$_fifthTable, array(':idsecure'=>$findUserID), array(':balance'=>$totalBill)
							);

							if($refundBalance) $GlobalMessage = $GlobalMessage.' (refund)';
						}
					}
				}

				// Melakukan transfer pembayaran kepada seller jika status shipping adalah 7 dan status receive adalah true/1 (lihat di config)
				$decisionTransferIncomeSeller = isset($param['status_receive']) ? (
					$param['status_shipping'] == 7 && $param['status_receive'] == 1 ? true : false
				) : false;

				if($decisionTransferIncomeSeller == true)
				{
					$buyerOrder = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
						'public.'.self::$_secondTable, array('seller_id', 'total_payment'), array(':invoice'=>$param['invoice'])
					);

					if($buyerOrder)
					{
						$getBuyerBill = $buyerOrder[0]['total_payment'];
						$getIncome = parent::_handler('crud', self::$_secondCluster)->getDataWhere(
							self::$_sixthTable, array('slug','income'), array(':id'=>$buyerOrder[0]['seller_id'])
						);
						if($getIncome)
						{
							$IncomeSeller = $getIncome[0]['income'];
							$IncomeSeller = is_null($IncomeSeller) ? 0 : (
								is_numeric($IncomeSeller) ? $IncomeSeller : 0
							);
							$totaBuyerlBill = (int)$getBuyerBill + $IncomeSeller;
							$transferIncome = parent::_handler('crud', self::$_secondCluster)->updateData(
								self::$_sixthTable, array(':id'=>$buyerOrder[0]['seller_id']), array(':income'=>$totaBuyerlBill)
							);

							if($transferIncome) $GlobalMessage = $GlobalMessage.' (transfer '.parent::_kurs($totaBuyerlBill).' > @'.$getIncome[0]['slug'].')';
						}
					}
				}
			}
				
			return array(
				'approve'=>true,
				'message'=>$GlobalMessage
			);
		}
		else
		{
			return array(
				'approve'=>false,
				'message'=>self::$_lang['global']['failed']
			);
		}
	}

	/*
	 * Multi-Condition list Order by $param passing from X-HTTP
	 */
	protected static function listOrder($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		$where = array('buyer_id'=>$findUserID); // Multiple Set Update belom fix

		if(isset($param['seller']))
		{
			if($param['seller'] == true)
			{
				$getSellerIDs = parent::_handler('crud', self::$_secondCluster)->getDataWhere(
					self::$_sixthTable, 'id', array(':owner_id'=>$findUserID)
				);

				$where = array('seller_id'=>$getSellerIDs[0]['id']);
			}
		}

		if(isset($param['status_receive'])) $where['status_receive'] = $param['status_receive'];

		$whereIn = isset($param['status_shipping']) ? (
			is_array($param['status_shipping']) ? array(
				'status_shipping', array_unique($param['status_shipping'])
			) : null
		) : null;

		$decisionGroup = isset($param['group']) ? $param['group'] : (
			array_sum($param['status_shipping']) == 1 ? true : false
		);

		$allOrder = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(
			'public.'.self::$_secondTable, null, $whereIn, 
			$where, array('row'=>($decisionGroup == true ? 'add_date' : 'update_date'),'sort'=>'DESC')
		);

		if(!$allOrder) return array('approve'=>false,'message'=>self::$_lang['exist']['false']);

		$collectStatusShipping = array_column($allOrder, 'status_shipping');
		$dataSelectShip = parent::_handler('crud', 'config')->getDataWhereIn(
			'status_order', null, array('id', array_unique($collectStatusShipping))
		);
		
		$invoiceAll = array_column($allOrder, 'invoice');
		$getReview = parent::_handler('crud', self::$_ClusterDB)->getDataWhereIn(
			self::$_fourthTable, array('note','rating_seller','rating_order','invoice'), array('invoice', array_unique($invoiceAll))
		);
		
		$totalOrder = count($allOrder);
		for($i=0;$i<$totalOrder;$i++)
		{
			for($is=0;$is<count($dataSelectShip);$is++)
			{
				if($allOrder[$i]['status_shipping'] == $dataSelectShip[$is]['id'])
				{
					$allOrder[$i]['status_shipping'] = $dataSelectShip[$is];
				}
			}

			$allOrder[$i]['review'] = null;
			for($is=0;$is<count($getReview);$is++)
			{
				if($allOrder[$i]['invoice'] == $getReview[$is]['invoice'])
				{
					$getReview[$is]['rating_order'] = range(1,$getReview[$is]['rating_order']);
					$getReview[$is]['rating_seller'] = (int)$getReview[$is]['rating_seller'];
					if($allOrder[$i]['status_receive'] == true) $allOrder[$i]['review'] = $getReview[$is];
				}
			}

			$allOrder[$i]['shipping_vendor'] = unserialize($allOrder[$i]['shipping_vendor']);
			$allOrder[$i]['add_ut'] = $allOrder[$i]['add_date'];
			$allOrder[$i]['update_ut'] = is_null($allOrder[$i]['update_date']) ? $allOrder[$i]['add_ut'] : $allOrder[$i]['update_date'];
			if($decisionGroup != true)
			{
				$addUT = (int)$allOrder[$i]['add_ut'];
				$updateUT = (int)$allOrder[$i]['update_ut'];
				if($allOrder[$i]['status_shipping']['id'] == 1)
				{
					$nextDay = $addUT + 43200;
					$timeout = ucfirst(self::$_lang['time']['hour']).' '.date('H:i:s', $nextDay);
				}
				elseif(in_array($allOrder[$i]['status_shipping']['id'], array(2,3,4)))
				{
					$nextUpdateDay = $updateUT + 72000;
					$timeout = date('d M\'y / <?-?> H:i:s', $nextUpdateDay);
					$timeout = preg_replace('/\<\?\-\?\>/', ucfirst(self::$_lang['time']['hour']), $timeout);
				}
				elseif(in_array($allOrder[$i]['status_shipping']['id'], array(5,6,7)))
				{
					$shippingVendorTime = $allOrder[$i]['shipping_vendor'][3];
					$shipping_time = $updateUT + $shippingVendorTime;
					$timeout = date('d M\'y / <?-?> H:i:s', $shipping_time);
					$timeout = preg_replace('/\<\?\-\?\>/', ucfirst(self::$_lang['time']['hour']), $timeout);
				}
				else
				{
					$timeout = '-';
				}

				$allOrder[$i]['expire'] = $timeout;
			}
			$traceInvoice = preg_replace('/([a-zA-Z0-9+])(\-[0-9+])/','$1', $allOrder[$i]['invoice']);
			$allOrder[$i]['add_date'] = parent::_handler('date')->dateTranslate((int)$allOrder[$i]['add_ut'], array('flag'=>self::$_lang['lang']['flag_id'].'_short', 'locale'=>self::$_lang['lang']['locale']));
			$allOrder[$i]['update_date'] = is_null($allOrder[$i]['update_date']) ?  $allOrder[$i]['add_date'] : parent::_handler('date')->dateTranslate((int)$allOrder[$i]['update_ut'], array('flag'=>self::$_lang['lang']['flag_id'].'_short', 'locale'=>self::$_lang['lang']['locale']));
			$allOrder[$i]['data_product'] = unserialize($allOrder[$i]['data_product']);
			$allOrder[$i]['shipping_area'] = unserialize($allOrder[$i]['shipping_area']);
			$allOrder[$i]['payment_vendor'] = unserialize($allOrder[$i]['payment_vendor']);
			$allOrder[$i]['total_payment_format'] = parent::_kurs($allOrder[$i]['total_payment']);
			$allOrder[$i]['invoice_root'] = $traceInvoice;
		}
		
		if($decisionGroup == true)
		{
			$tmpOrdersGroup = array();
			foreach($allOrder as $arg)
			{
				$tmpOrdersGroup[$arg['invoice_root']][] = $arg;
				unset($arg['invoice_root']);
			}

			$output = array();
			foreach($tmpOrdersGroup as $type=>$labels)
			{
				$total_bill = array_column($labels, 'total_payment');
				$status_shipping = array_column($labels, 'status_shipping');
				$payment_vendor = array_column($labels, 'payment_vendor');
				$date = array_column($labels, 'add_date');
				$date_ut = array_unique(array_column($labels, 'add_ut'));
				$all_orders = array_column($labels, 'data_product');
				
				$pushProduct = array();
				foreach($all_orders as $key=>$val)
				{
					foreach($val as $arg)
					{
						$pushProduct[$arg['id']] = $arg;
					}
				}
				$pushProduct = array_values($pushProduct);
				$pushPayment = array_values(array_unique(self::_this_orders_grabRoot($payment_vendor,'code')));
				$pushShipping = array_values(array_unique(self::_this_orders_grabRoot($status_shipping,'id')));
				$pushDate = array_unique($date);
				$timeout = (int)$date_ut[0] + 21600;
				$pushExpire = ucfirst(self::$_lang['time']['hour']).' '.date('H:i:s', $timeout);
				//$pushExpire = $timeout['end']['formattimes'];

				$output[] = array(
					'invoice_root'=>$type,
					'add_date'=>$pushDate[0],
					'expire'=>$pushExpire,
					'invoice'=>$type.'/'.count($labels).'x',
					'total_payment'=>array_sum($total_bill),
					'status_shipping'=>$pushShipping[0],
					'payment_vendor'=>$pushPayment[0],
					'data_product'=>$pushProduct
				);
			}

			$allOrder = array_values($output);
		}

		return array(
			'approve'=>true,
			'server'=>array(
				'icon'=>self::$_cdnIcon,
				'product'=>self::$_cdnProduct
			),
			'data'=>$allOrder,
			'total'=>count($allOrder),
			'symbol'=>self::$_lang['lang']['currency']['symbol']
		);
	}

	/*
	 * Detail Orders
	 */
	protected static function detailOrder($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		if(self::$_userExist == false) return array('approve'=>false, 'message'=>self::$_lang['global']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		$param['seller_detail'] = isset($param['seller_detail']) ? $param['seller_detail'] : false;
		$allOrder = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
			self::$_secondTable, null, array(':buyer_id'=>$findUserID), null, array('row'=>'add_date','sort'=>'DESC')
		);

		if(!$allOrder) return array('approve'=>false,'message'=>self::$_lang['exist']['false']);

		import_snippet('arrayMerge');
		$totalAmountPayment = [];
		$allOrder = parent::_loopunserialize($allOrder, 'data_product');
		$collectIDseller = getLoopValueFromOneIndexArray(array('data'=>$allOrder,'cellGrab'=>'seller_id'));
		$dataSelect = parent::_handler('crud', self::$_secondCluster)->getDataWhereIn(
			'seller', array('id','slug','name','premium_shop_type'), array('id', array_unique($collectIDseller))
		);
		$collectDataSeller = ArrayMergeIndexValueDB($collectIDseller, $dataSelect, 'id');
		for($i=0;$i<count($allOrder);$i++)
		{
			array_push($totalAmountPayment, (int)$allOrder[$i]['total_payment']);
			for($is=0;$is<count($collectDataSeller);$is++)
			{
				if($allOrder[$i]['seller_id'] == $collectDataSeller[$is]['id'])
				{
					$allOrder[$i]['seller_detail'] = $collectDataSeller[$is];
				}
			}
		}

		$totalAmountPayment = array_sum($totalAmountPayment);
		return array(
			'approve'=>true,
			'server'=>array(
				'seller'=>self::$_cdnSeller,
				'icon'=>self::$_cdnIcon,
				'product'=>self::$_cdnProduct
			),
			'data'=>$allOrder,
			'count'=>self::getAllQuantity($allOrder, false),
			'total_payment'=>parent::_kurs($totalAmountPayment),
			'total_payment_format'=>array(
				'value'=>$totalAmountPayment,
				'symbol'=>self::$_lang['lang']['currency']['symbol']
			)
		);
	}

	/**
	 * Mengirim kan notif Order melalui SMS gateway
	 */
	protected static function smsOrder($data=null)
	{
		if(is_null($data)) return false;
		if(_smsGateway == false) return false;

		$paramSMS = array(
			'number'=>$data['phone'],
			'message'=>sprintf($_lang['ecommerce']['order']['notif'], $data['ammount'], $data['items'], $data['invoice'], $data['date'])
		);

		$sms = parent::_handler('sms', 1)->send($paramSMS);
		$return = $sms ? array(
			'status'=>true, 'message'=>self::$_lang['global']['notif'], 'response'=>$sms
		) : false;
		return $return;
	}

	/*
	 * Send Email orders if the orders done by user from Apps X-HTTP
	 */
	protected static function emailOrder($param)
	{
		if(_eMailing == true)
		{
			self::load($param);
			// Persiapan kirim email orderan
			$order = parent::_handler('crud',self::$_ClusterDB)->getDataWhere(
				'public.'.self::$_secondTable, null, array(':token'=>$param['token'])
			);

			$totalPrice = [];
			$orderHTML = '<ul style="margin:0;padding:0;left:0;">';
			foreach($order as $k=>$v)
			{
				$dataProduct = unserialize($v['data_product']);
				$product = [];
				foreach($dataProduct as $dpk=>$dpv)
				{
					array_push($product, '<p style="margin:0;padding:0;">'.$dpv['name'].': '.parent::_kurs($dpv['fix_price']).'</p>');
				}
				
				$orderHTML .= '<h5 style="margin:0;">Invoice: '.$v['invoice'].'</h5>';
				$orderHTML .= '<div style="border-bottom:solid 1px #efe;padding:10px 15px;font-size:11px;">'.join($product, '<br />').'</div><hr style="margin:0 0 15px 0;" />';
				array_push($totalPrice, $v['total_payment']);
			}

			$orderHTML .= '<div>Total: <strong>'.parent::_kurs(array_sum($totalPrice)).'</strong>/*</div><br />';
			$orderHTML .= '<small>*<i>'.self::$_lang['ecommerce']['payment']['confirm']['attention'].'</i></small>';
			//var_dump($orderHTML);die();

			// Nilai "21600" adalah 6 jam
			$timeout = parent::_handler('shipping', array('lang'=>self::$_lang))->fromdate($order[0]['add_date'])->todate("21600")->checktimeout();
			$time = $timeout['longtimes']['value'];
			$format = $timeout['longtimes']['label_unix'];
			$timeframe = date('h:i:s', strtotime("$time $format"));
			$lastdate = $timeout['end']['formattimes'];

			Imports::name('Mail')->from('service');
			$emailingActivate = MailServices::emailOrder(
				array(
					'to'=>self::$_userExist[0]['email'], 
					'to_name'=>self::$_userExist[0]['real_name'], 
					'order'=>$orderHTML, 
					'timeout'=>$lastdate.' '.$timeframe, 
					'load'=>false
				)
			);
			return array('email_status'=>$emailingActivate);
		}
	}

	/*
	 * Send Feedback Orders
	 */
	protected static function feedbackOrder($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('invoice', 'message', 'score');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		$getReview = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			'public.'.self::$_fourthTable, array('id_buyer'), array(':invoice'=>$param['invoice'])
		);

		if($getReview) return array('approve'=>false, 'message'=>self::$_lang['global']['exist']);
		$getOrderDetail = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			'public.'.self::$_secondTable, array('seller_id','buyer_id'), array(':invoice'=>$param['invoice'])
		);

		if(!$getOrderDetail) return array('approve'=>false, 'message'=>self::$_lang['global']['failed'].' (get order)');
		$orders = $getOrderDetail[0];
		if(count($param['score']) < 2) return array('approve'=>false, 'message'=>self::$_lang['global']['denied']);
		$score = array(
			'order'=>$param['score'][0],
			'kios'=>$param['score'][1]
		);
		
		// Persiapan memasukan data baru ke review order
		$saveReviewOrder = parent::_handler('crud', self::$_ClusterDB)->insertData(
			'public.'.self::$_fourthTable, array(
				':id_buyer'=>$orders['seller_id'],
				':id_seller'=>$orders['buyer_id'],
				':invoice'=>$param['invoice'],
				':note'=>$param['message'],
				':rating_order'=>$score['order'],
				':rating_seller'=>$score['kios'],
				':add_date'=>strtotime('now'),
				':banned'=>0,
			)
		);
		
		// Persiapan mengambil data score kios yang sudah ada
		if(!$saveReviewOrder) return array('approve'=>false, 'message'=>self::$_lang['global']['failed'].' (rate order)');
		$getKios = parent::_handler('crud', self::$_secondCluster)->getDataWhere(self::$_sixthTable, array(
				'positive_rate','negative_rate','accurate_score','speed_service_score','overall_service_score'
			), array(':id'=>$orders['seller_id'])
		);

		if(!$getKios) return array('approve'=>false, 'message'=>self::$_lang['global']['denied'].' (unavailable seller)');
		$getKios = $getKios[0];
		
		// Persiapan penghitungan score
		$feedBackScore = (int)$score['kios'];
		$kiosAlgoScore = $feedBackScore > 1 ? $feedBackScore / 2  : $feedBackScore;
		$defaultScore = 5;

		// -- HITUNG NEGATIF / POSITIF SCORE
		$kiosPositif = ($feedBackScore >= 2 ? 1 : 0);
		$kiosNegatif = ($feedBackScore >= 2 ? 0 : 1);
		// -- HITUNG AKURASI SCORE
		$acurrateScore = ($kiosAlgoScore >= 3 ? $kiosAlgoScore + $feedBackScore : $kiosAlgoScore);
		$kiosAccurate = round(($acurrateScore * $defaultScore) /2);
		// -- HITUNG KECEPATAN SCORE
		$speedScore = ($kiosAlgoScore >= 5 ? $kiosAlgoScore + $feedBackScore : $kiosAlgoScore);
		$kiosSpeed = round(($speedScore * $defaultScore) /2);
		// -- HITUNG OVERALL SCORE
		$kiosOverall = ($kiosPositif + $kiosAccurate + $kiosSpeed) - $kiosNegatif;

		// Persiapan update Database Seller
		$paramSet = array(
			':positive_rate'=>$kiosPositif + (int)$getKios['positive_rate'],
			':negative_rate'=>$kiosNegatif + (int)$getKios['negative_rate'],
			':accurate_score'=>$kiosAccurate + (int)$getKios['accurate_score'],
			':speed_service_score'=>$kiosSpeed + (int)$getKios['speed_service_score'],
			':overall_service_score'=>round($kiosOverall) + (int)$getKios['overall_service_score']
		);
		//var_dump($paramSet);die();

		$rateKios = parent::_handler('crud', self::$_secondCluster)->updateData(self::$_sixthTable, array(':id'=>$orders['seller_id']), $paramSet);
		if(!$rateKios) return array('approve'=>false, 'message'=>self::$_lang['global']['failed'].' (rate seller)');
		return array('approve'=>true, 'message'=>self::$_lang['global']['success']);
	}

	/*
	 * Send Message Complaint Orders
	 */
	protected static function newComplaintOrder($param)
	{
		self::load($param);
		if(is_null(self::$_token)) return self::$_lang['error']['403_message'];
		$handlerValidate = parent::_handler('validate', $param);
		$validateParam = array('invoice', 'message');
		if($handlerValidate->issetAndEmptyFalse($validateParam) == false) return array(
			'approve'=>false,'message'=>self::$_lang['crud']['create']['isset']
		);

		if(!self::$_userExist) return array('approve'=>false, 'message'=>self::$_lang['access']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];

		$getIdSeller = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(
			self::$_secondTable, 'seller_id', array(':invoice'=>$param['invoice'])
		);

		if(!$getIdSeller) return array('approve'=>false, 'message'=>self::$_lang['crud']['create']['denied']);

		$paramSet = array(
			'invoice'=>$param['invoice'],
			'message'=>$param['message'],
			'id_buyer'=>$findUserID,
			'id_seller'=>$getIdSeller[0]['seller_id'],
			'add_date'=>date('Y-m-d'),
			'clear'=>0
		);

		$post = parent::_handler('crud', self::$_ClusterDB)->insertData(self::$_thirdTable, $paramSet);
		if(!$post) return array('approve'=>false, 'message'=>self::$_lang['crud']['create']['failed']);
		return array(
			'approve'=>true,
			'message'=>self::$_lang['crud']['create']['success']
		);
	}

	/*
	 * List Complaint orders by users sended before
	 */
	protected static function listComplaintOrder($param)
	{
		self::load($param);
		if(!self::$_userExist) return array('approve'=>false, 'message'=>self::$_lang['access']['failed']);
		$findUserID = self::$_userExist[0]['idsecure'];
		$where = array();
		if(isset($param['seller_id'])) $where[':seller_id'] = $param['seller_id'];
		if(isset($param['buyer_id'])) $where[':buyer_id'] = $param['buyer_id'];
		if(isset($param['clear'])) $where[':clear'] = $param['clear'];

		$getList = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
			self::$_secondTable, null,  $where, null, array('row'=>'add_date','sort'=>'DESC')
		);

		if(!$getList) return array('approve'=>false, 'message'=>self::$_lang['crud']['read']['failed']);
		return array(
			'approve'=>true,
			'data'=>$getList,
			'message'=>self::$_lang['crud']['create']['success']
		);
	}
}
?>