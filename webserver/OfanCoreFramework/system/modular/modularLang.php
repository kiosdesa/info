<?php if(!defined('_thisFileDIR')) header('Location:..');/**
 * Class Object Language
 * Adalah Core level 2 untuk menangani perubahan bahasa atau i18n pada system Web Service
 * 
 * Nama System: OfanCoreFramework
 * Nama Class: Language
 * Constructor @param $country default value 'en'
 * @author OFAN
 * @since 2018
 * @version 1.0
 * @copyright GNU and GPL license
 */
class Language
{
	private $_currentLang;

	function __construct($country='en')
	{
		if($country !== 'en')
		{
			$identitylang = _thisLangDIR.strtoupper($country).'_lang.json';
			$lang = $this->loadLangFile($identitylang);
			$this->_currentLang = $lang;
		}
		else
		{
			$this->_currentLang = $this->defaultLang();
		}
	}

	protected function defaultLang()
	{
		return array(
			'lang'=>array(
				'flag_id'=>'en',
				'locale'=>'en_EN',
				'currency'=>array(
					'symbol'=>'$',
					'id'=>'USD',
					'curs'=>array(1,15000)
				)
			),
			'login'=>array(
				'title'=>'Insert Your Credential to Access Dashboard',
				'success'=>'Success Validate Credential, You\'re Logged',
				'failed'=>'Failed Login, Check Your Credential Input and Try Again!',
				'denied'=>'Failed Login, This Credential data unmatch'
			),
			'logout'=>array(
				'title'=>'You Sure to Exit Dashboard?',
				'success'=>'Success Logout Account, You\'re Leaving',
				'failed'=>'Failed Logout, Maybe Poor Connection and Try Again!',
				'denied'=>'Failed logout, This Credential data unmatch'
			),
			'register'=>array(
				'title'=>'Registering new User',
				'success'=>'Success Add Account, Check Email or Insert code OTP for verifying',
				'failed'=>'Failed Add Account, Maybe Poor Connection and Try Again!',
				'denied'=>'Failed Add Account, This Credential data has exits/taken'
			),
			'forgot'=>array(
				'title'=>'Forgot Account',
				'success'=>'Our code verify has been send to your mail',
				'failed'=>'Failed reset account, Maybe Poor Connection and Try Again!',
				'denied'=>'Failed reset account, Your data inserted unmatch'
			),
			'otp'=>array(
				'title'=>'OTP Code',
				'message'=>'OTP Code Devliverd',
				'success'=>'OTP Code Verification Success.',
				'failed'=>'Failed OTP Verification, Maybe Poor Connection and Try Again!',
				'denied'=>'Failed OTP Verification, This Credential data is unregister'
			),
			'activate'=>array(
				'title'=>'Activate Account',
				'success'=>'Activate Account Success.',
				'failed'=>'Failed Activate Account, Maybe Poor Connection and Try Again!',
				'denied'=>'Failed Activate Account, This Credential data is unregiter'
			),
			'error'=>array(
				'404_message'=>'Was Not Found',
				'403_message'=>'Forbidden Access',
				'500_message'=>'Internal System Server Error'
			),
			'global'=>array(
				'success'=>'Your request success',
				'failed'=>'Failed open request',
				'denied'=>'Your Request is Denied',
				'forbidden'=>'Forbidden to Access',
				'exist'=>'Denied process because has saved!',
				'inactive'=>'Please activating account from email or insert OTP code',
				'activate'=>'Your account has been activated',
				'notif'=>'Notification is delivered',
				'form'=>array(
					'input'=>'Masukan',
					'isset'=>'Terdapat',
					'submit'=>'Kirimkan',
					'text'=>'Teks',
					'number'=>'Nomor',
					'type'=>'Ketikan %s disini...'
				)
			),
			'access'=>array(
				'success'=>'You\'re approved',
				'failed'=>'You can\'t do process',
				'denied'=>'Forbidden, you can\t access'
			),
			'crud'=>array(
				'create'=>array(
					'success'=>'New data saved!',
					'exist'=>'Ups, data has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, data hasn\'t save!',
					'denied'=>'You haven\'t access to add new data',
					'isset'=>'You missing some data input please check again!'
				),
				'read'=>array(
					'success'=>'Data was found',
					'failed'=>'Data was not found',
					'denied'=>'You haven\'t access to look up'
				),
				'update'=>array(
					'success'=>'Modify Success!',
					'failed'=>'Modify Failed!',
					'denied'=>'You haven\'t access to modify',
					'isset'=>'You missing some data input please check again!'
				),
				'delete'=>array(
					'success'=>'Deleting Success!',
					'failed'=>'Deleting Failed!',
					'denied'=>'You haven\'t access to remove'
				)
			),
			'ecommerce'=>array(
				'product'=>array(
					'success'=>'New product saved!',
					'exist'=>'Ups, product has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, product hasn\'t save!',
					'denied'=>'You haven\'t access to add new product',
					'isset'=>'You missing some data to input product please check again!',
					'form'=>array(
						'name'=>'Name of Product',
						'fix_price'=>'Price MSRP Product',
						'category'=>'Category Product',
						'description'=>'Description Product',
						'weight_gram'=>'Weight Product (gram)',
						'stock'=>'Stock Product (pcs)',
						'minimum_order'=>'Minimum Buy Product',
						'condition'=>'Condition Product (baru/bekas)',
						'status'=>'Publishing Status of Product'
					)
				),
				'favorite'=>array(
					'success'=>'Your favorite product saved!',
					'exist'=>'Ups, favorite product has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, favorite product hasn\'t save!',
					'denied'=>'You haven\'t access to add new favorite product',
					'isset'=>'You missing some data to input favorite product please check again!'
				),
				'buy'=>array(
					'success'=>'Your has buy item product!',
					'exist'=>'Ups, buy product has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, failed buy product!',
					'denied'=>'You haven\'t access to buy new product',
					'isset'=>'You missing some data to input buy product please check again!'
				),
				'seller'=>array(
					'success'=>'Your configurating some data saved!',
					'exist'=>'Ups, some information has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, hasn\'t save!',
					'denied'=>'You haven\'t access to configurating some data',
					'isset'=>'You missing some data to input configurating please check again!',
					'notif'=>array(
						'complaint'=>'Ups, complaint from buyer, invoice: %1$s, <br>date: %2$s!'
					),
					'form'=>array(
						'slug'=>'Kiosk Alias Name (ex: @tokoadasegala)',
						'name'=>'Kiosk Name (ex: Toko Segala Ada)',
						'location'=>'Kiosk Address/Venue/Location',
						'contact'=>'Official Kiosk Contact/Phone Number',
						'description'=>'Information and Description Kiosk',
						'note'=>'Kios Note or Rules',
						'district'=>'District Name of Kiosk',
						'city'=>'City of Kiosk',
						'province'=>'Province of Kiosk',
						'postal_code'=>'Kiosk Postal Code',
						'district_code'=>'Kiosk district code',
						'add_date'=>'Kioks Date Create',
						'logo'=>'Kiosk Logo or Avatar Identify',
						'shipping'=>'Kiosk Shipping Services',
						'owner_id'=>'Kiosk Owner ID'
					)
				),
				'subscribe'=>array(
					'success'=>'Subscribing seller is done!',
					'exist'=>'Ups, seller has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, hasn\'t subscribing!',
					'denied'=>'You haven\'t access to subscribing seller',
					'isset'=>'You missing some data to input subscribing please check again!'
				),
				'cart'=>array(
					'success'=>'Your update cart is done!',
					'exist'=>'Ups, data cart has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, hasn\'t adding to cart!',
					'denied'=>'You haven\'t access to add cart',
					'isset'=>'You missing some data to adding item cart please check again!'
				),
				'order'=>array(
					'success'=>'Your update orders is done!',
					'exist'=>'Ups, data orders has exist\'s and can\'t duplicate',
					'failed'=>'Oops, something wrong, hasn\'t adding to orders!',
					'denied'=>'You haven\'t access to add orders',
					'isset'=>'You missing some data to adding item orders please check again!',
					'notif'=>'New order\'s, total: %1$s, %2$s items, invoice: %3$s, <br>date:[%4$s]'
				),
				'payment'=>array(
					'message'=>'Confirm Payment, User: %1$s, Invoice: %2$s, Total: %3$s, Via: %4$s',
					'success'=>'Confirm Payment is Success!',
					'exist'=>'Ups, Confirm payment has done',
					'failed'=>'Ups, something wrong confirm payment, try again!',
					'denied'=>'You haven\'t access to Confirm Payment',
					'isset'=>'You missing some data to Confirm Payment, try again!',
					'notif'=>'Payment order\'s is transfered, total: %1$s [%2$s]',
					'confirm'=>array(
						'manual'=>'<h6>How to Confirm Payment?</h6><ol><li>Choose method confirm</li><li>Prepare proof of transaction/receipt print and take picture</li><li>Send/Submit</li></ol>',
						'message'=>'Please paying the invoice before the times is over',
						'warning'=>'Time left of confirming payment',
						'attention'=>'Costs not included (shipping / unique code / admin outlets / etc)'
					),
					'method'=>array(
						'atm'=>'<ol><li>Open your bank account in %1$s ATM\'s</li><li>Choose transfer menu with destiny %1$s</li><li>Input our account number %2$s</li><li>Input ammount same as bill/invoice</li><li>After that please keep bill print and capture it for proof</li></ol>',
						'ebanking'=>'<ol><li>Open your %1$s e-banking app\'s and Login with Access Code</li><li>Choose m-Transfer and Transfer %1$s Account Virtual</li><li>Input our company code %2$s and some require registerd data</li><li>Input ammount same as bill/invoice</li><li>After that please screen shoot transfer log for proof</li></ol>',
						'merchant'=>'<ol><li>Visit merchant venue %1$s nearby</li><li>Ask to cassier %1$s for payment</li><li>Tell cassier the code %2$s (ID transaction)</li><li>Give money to cassier same as total billing</li><li>After that please keep bill print and capture it for proof</li></ol>'
					)
				),
				'shipping'=>array(
					'success'=>'Order\'s has arrived',
					'pending'=>'Order\'s on process',
					'process'=>'Order\'s on delivering',
					'failed'=>'Order\'s failed sending'
				)
			),
			'email'=>array(
				'user'=>array(
					'activate'=>'<h4>Thank you for joining %1$s</h4><p>Before doing activities buy/sale in the %1$s application, please activate your account via the link below:</p><div style="display:inline-block;padding:20px;background:rgb(242,102,63);border-radius:7px;"><a href="%2$s" target="_blank" style="color:#fff;text-decoration:none;">Click here for activation</a></div><p><a href="%2$s" target="_blank">%2$s</a></p>',
					'reset'=>'You have made changes to your account information such as changing passwords or changing telephone numbers, checking your account activity again',
					'suspend'=>'Your account is temporarily suspended, if you object please contact our team at the number listed'
				),
				'ecommerce'=>array(
					'create_kios'=>'<h4>You have made a Kiosk at %1$s</h4><p>The following is information about your Kiosk:</p><ul><li>Kiosk Name: %2$s</li><li>Kiosk Address: %3$s</li><li>Contact: %4$s</li></ul>',
					'order'=>'<h3>Thank you for shopping at %1$s</h3><p>The following is your shopping list:</p><div style="display:inline-block;padding:10px;background:#eeefff">%2$s</div><p>Please make payment before afternoon %3$s</p>'
				)
			),
			'whatsapp'=>array(
				'message_greeting'=>'Hi? I know you from bumdesku apps...',
				'message_asking'=>'Hai, I have question for you...',
				'message_order_confirm'=>'Hi? I\'ll doing payment confirmation, here I list the invoice number and proof of transfer...'
			),
			'sms'=>array(
				'message_verifikasi'=>'%s - Your Verification Code is',
				'message_login'=>'%s - Your Login Code is'
			),
			'graph'=>array(
				'title'=>'Chart',
				'status'=>'Status',
				'title_predictive'=>'Prediction Sensus Population',
				'title_compare'=>'Data Comparation'
			),
			'bool'=>array(
				'true'=>'Active',
				'false'=>'Inactive'
			),
			'condition'=>array(
				'true'=>'New',
				'false'=>'Second'
			),
			'exist'=>array(
				'true'=>'Data is Exist',
				'false'=>'Data is Empty'
			),
			'support'=>array(
				'true'=>'Supported',
				'false'=>'Unsupported'
			),
			'priority'=>array(
				'true'=>'Required',
				'false'=>'Optional'
			),
			'time'=>array(
				'second'=>'second',
				'minute'=>'minute',
				'hour'=>'hour',
				'day'=>'day',
				'week'=>'week',
				'month'=>'month',
				'year'=>'year'
			),
			'status'=>array
			(
				'fresh'=>'Baru Banget',
				'new'=>'Terbaru',
				'early'=>'Baru Saja',
				'latest'=>'Sudah Berlalu',
				'oldest'=>'Sudah Lama',
				'selling'=>'Going to Sale',
				'buying'=>'Going to Buy',
				'collapse'=>'It Was Bankrupt',
				'rich'=>'So Wealthy',
				'poor'=>'Underprivileged'
			),
			'geolocation'=>array(
				'district'=>'Kecamatan',
				'city'=>'Kota',
				'province'=>'Provinsi',
				'country'=>'Negara',
				'zip_code'=>'Kode Pos',
				'area'=>'Kode Area',
				'longitude'=>'Garis Bujur',
				'latitude'=>'garis Lintang'
			)
		);
	}

	private function loadLangFile($data=null)
	{
		if(is_null($data)) return false;
		$preload = @file_get_contents($data);
		if(!$preload) return $this->defaultLang();
		return json_decode($preload, true);
	}

	public function translate()
	{
		return $this->_currentLang;
	}
}
?>