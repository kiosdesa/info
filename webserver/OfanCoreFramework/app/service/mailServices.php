<?php if(!defined('_thisFileDIR')) header('Location:..');

class MailServices extends OfanCoreFramework
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

	/** 
	 * Load Library 
	 */
	private static function load($param=null)
	{
		$cluster = 'config';
		$loadLib = isset($param['load']) ? ($param['load'] == true ? true : false) : true;
		self::$_token = isset($_SESSION['login_token']) ? $_SESSION['login_token'] : null;
        if($loadLib == true)
        {
            parent::_library(array('dbHandler', 'crudHandlerPDO', 'jsonHandler', 'validateHandler', 'emailHandler'));
            self::$_userExist = parent::_handler('validate', self::$_token)->buyerToken();
        }
        if(!class_exists('emailHandler')) parent::_library(array('emailHandler'));
		self::$_ClusterDB = (isset($param['cluster']) ? (is_null($param['cluster']) ? $cluster : $param['cluster']) : $cluster);
		self::$_thisTable = 'company_options';
		self::$_thisComponentIonic = 'MailPage';
		self::$_lang = parent::_languageConfig();
		self::$_userConfig = parent::_loadUserConfig();
		self::$_cdnIcon = parent::_cdnDirectoryIcon();
	}

    public static function emailer($param)
    {
        $param['load'] = false;
        return self::sendEmail($param);
    }

    public static function emailActivateUser($param)
    {
        self::load($param);
        $param['load'] = false;
        $param['subject'] = self::$_lang['activate']['title'];
        $message = self::$_lang['email']['user']['activate'];
        $param['message'] = sprintf($message, _thisBrand, $param['link_activating']);
        //var_dump($param);return false;
        return self::sendEmail($param);
    }

    public static function emailOrder($param)
    {
        self::load($param);
        $param['load'] = false;
        $param['subject'] = self::$_lang['ecommerce']['order']['success'];
        $message = self::$_lang['email']['ecommerce']['order'];
        $param['message'] = sprintf($message, _thisBrand, $param['order'], $param['timeout']);
        //var_dump($param);die();
        return self::sendEmail($param);
    }

    protected static function sendEmail($param)
    {
        self::load($param);
        $emailFromName = _thisBrand;
        $emailTo = $param['to'];
        $emailToName = $param['to_name'];
        $subject = $param['subject'];
        $message = $param['message'];

        $configCompany = parent::_handler('crud', self::$_ClusterDB)->getDataWhere(self::$_thisTable, null, array(':name'=>'office'));
        $pushFooterMail = [];
        $emailFrom = '';
        foreach($configCompany as $k=>$v)
        {
            array_push($pushFooterMail, '<li>'.$v['value'].'</li>');
            if($v['type'] == 'email')
            {
                $emailFrom = $v['value'];
            }
        }
        $pushFooterMail = join(($pushFooterMail),'');
        $pushFooterMail = '<ol style="left:0;padding:0 0 0 20px;clear:both;font-size:12px;margin:0;">'.$pushFooterMail.'</ol>';

        $footerMailRender = '<div style="display:inline-block;width:100%;padding:10px 15px;color:#fff;background:#eeba18;line-height:1.5em;margin-top:20px;clear:both;">';
        $footerMailRender .= '<h5 style="margin:0;">Contact:</h5>'.$pushFooterMail.'</div>';
        
        $messageRender = '<div style="text-align:left;max-width:96%">';
        $messageRender .= '<div style="display:inline-block;width:100%;clear:both;border:solid 1px #eeba18;padding:10px 12px;line-height:0;">';
        $messageRender .= '<img src="http://cdn.bumdesapp.com/data_icon/logo-ecommerce-bumdesku-text-icon.jpg" width="160" height="32" title="'._thisBrand.'" alt="'.strtolower(_thisBrand).'" /></div>';
        $messageRender .= $message.$footerMailRender.'</div>';
        //var_dump($messageRender);return false;

        $prepareMail = parent::_handler('email')->connect(
            'kios.bumdesku@gmail.com', 'laillaha.ilallah.1'
        )->header(
            $emailFrom, $emailFromName, $emailTo, $emailToName
        )->body(
            $subject, $messageRender, 'html'
        )->send();
        if(!$prepareMail) return array('approve'=>false,'message'=>self::$_lang['global']['failed']);
        return array('approve'=>$prepareMail,'message'=>self::$_lang['global']['success']);
    }
}
?>