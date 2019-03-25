<?php if(!defined('_thisFileDIR')) header('Location:..');

class CheckoutServices extends OfanCoreFramework
{

	private static $_ClusterDB;
	private static $_lang;
	private static $_userConfig;
	private static $_token;
	private static $_userExist;
	private static $_thisTable;
	private static $_cdnIcon;
	private static $_cdnProduct;

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$cluster = 'transaction';
		/**
		 * Untuk mengisi nilai boolean pada where (filter)
		 * di parameter isikan nilai 1 atau 'true' untuk true
		 * di parameter isikan nilai kosong atau '' untuk false
		 */
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
		if($loadLib == true)
		{
			parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'fileHandler', 'shippingHandler'));
			self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
		}
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'cart';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
		self::$_cdnProduct = parent::_cdnDirectoryProduct();
	}


	public static function getAllQuantity($data, $unserialize=true)
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
	


	protected static function calculatePrice($data, $unserialize=true)
	{
		if(is_null($data)) return self::$_lang['access']['failed'];
		$allPrice = [];
		for($i=0;$i<count($data);$i++)
		{
			$thisDataProduct = $unserialize == true ? unserialize($data[$i]['data_product']) : $data[$i]['data_product'];
			$calculate = parent::_calculateprice($thisDataProduct, array('stock','minimum_order'), array('fix_price','quantity'));
			array_push($allPrice, $calculate);
		}

		return array_sum($allPrice);
	}



	/*
	 * Ini Fungsi sangat memakan resource
	 * Mohon jadi perhatian untuk pengembangan kedepan
	 * Saran: Dibuat lebih ringkas alogaritma nya 
	 */
	public static function checkoutListFormat($checkout, $returnIndexToken=false)
	{
		$checkout = parent::_loopunserialize($checkout, 'data_product');
		for($i=0;$i<count($checkout);$i++)
		{
			$subQty = [];
			$subWeight = [];
			for($iy=0;$iy<count($checkout[$i]['data_product']);$iy++)
			{
				/* 
				 * Resource ini sangat besar memakan memory karena di cari satu persatu produknya dengan ID 
				 * Kedepan untuk pengembangan gunakan array_recrusive dengan pencarian ID kolektif getDataWhereIn()
				 * Dengan kendala/stack index dari array sumber & array pembanding mengacak/tidak sesuai
				*/
				$returnItemOrder = parent::_handler('crud', 'sensus')->getDataWhere(
					'product', 
					array('id','slug','name','thumb','fix_price','stock','minimum_order','weight_gram'), 
					array(':id'=>$checkout[$i]['data_product'][$iy]['id'])
				);

				$returnItemOrder[0]['minimum_order'] = (int)$returnItemOrder[0]['minimum_order'];
				$returnItemOrder[0]['stock'] = (int)$returnItemOrder[0]['stock'];
				$returnItemOrder[0]['weight_gram'] = (int)$returnItemOrder[0]['weight_gram'];
				$returnItemOrder[0]['weight_kilogram'] = $returnItemOrder[0]['weight_gram']/1000;
				//$returnItemOrder[0]['ready_order'] = $returnItemOrder[0]['stock'] < $returnItemOrder[0]['minimum_order'] ? false : true;
				array_push($subQty, $checkout[$i]['data_product'][$iy]['quantity']);
				array_push($subWeight, ($returnItemOrder[0]['weight_gram'] * $checkout[$i]['data_product'][$iy]['quantity']));
				$checkout[$i]['data_product'][$iy] = array_merge($checkout[$i]['data_product'][$iy], $returnItemOrder[0]);
			}

			$checkout[$i]['sub_quantity'] = array_sum($subQty);
			$checkout[$i]['sub_weight_gram'] = array_sum($subWeight);
			$checkout[$i]['sub_weight_kilogram'] = $checkout[$i]['sub_weight_gram']/1000;

			for($iy=0;$iy<count($checkout[$i]['data_product']);$iy++)
			{	
				if($returnItemOrder[0]['stock'] < $returnItemOrder[0]['minimum_order'])
				{
					$checkout[$i]['sub_quantity'] = $checkout[$i]['sub_quantity'] - $checkout[$i]['data_product'][$iy]['quantity'];
					unset($checkout[$i]['data_product'][$iy]);
				}
			}
			
			$checkout[$i]['data_product'] = array_values($checkout[$i]['data_product']);
			$subTotalPrice = parent::_calculateprice($checkout[$i]['data_product'], array('stock','minimum_order'), array('fix_price',$checkout[$i]['sub_quantity']));
			//$subTotalPrice = parent::_calculateprice($checkout[$i]['data_product']);
			$checkout[$i]['subtotal_price'] = $subTotalPrice;
			$checkout[$i]['subtotal_price_format'] = parent::_kurs($subTotalPrice);
		}

		for($i=0;$i<count($checkout);$i++)
		{
			if(count($checkout[$i]['data_product']) < 1)
			{
				unset($checkout[$i]);
			}
		}

		$checkout = array_values($checkout);

		if($returnIndexToken !== false)
		{
			$checkout = parent::_grabtoindextop($checkout, array('index'=>'token_cart'));
		}

		return $checkout;
	}

    

	protected static function get($param)
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

		$allOrder = parent::_handler('crud', self::$_ClusterDB)->getDataFilter(
			self::$_thisTable, null, array(':buyer_id'=>$findUserID), null, array('row'=>'update_date','sort'=>'DESC NULLS LAST')
        );
        
		if($allOrder)
		{
			$allOrder = self::checkoutListFormat($allOrder);
			$totalPrice = self::calculatePrice($allOrder, false);
			$productReturn = parent::_loopkurs($allOrder, array('rootparent'=>'data_product', 'rootsub'=>'fix_price'), true);

			if($param['seller_detail'] == true)
			{
				import_snippet('arrayMerge');
				$collectIDseller = getLoopValueFromOneIndexArray(array('data'=>$productReturn,'cellGrab'=>'seller_id'));
				$dataSelect = parent::_handler('crud', 'account')->getDataWhereIn(
					'seller', array('id','slug','name','premium_shop_type','postal_code','district_code','shipping'), 
					array('id', array_unique($collectIDseller))
				);
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
					$shippings = unserialize($productReturn[$i]['seller_detail']['shipping']);
					$productReturn[$i]['seller_detail']['shipping'] = $shippings;
					$productReturn[$i]['seller_detail']['shipping_names'] = join($shippings, ',');
					$productReturn[$i]['seller_detail']['shipping_from_code'] = $productReturn[$i]['seller_detail']['district_code'].'|'.$productReturn[$i]['seller_detail']['postal_code'];
				}
			}

			return array(
				'approve'=>true,
				'server'=>array(
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
}
?>