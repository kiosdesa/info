<?php if(!defined('_thisFileDIR')) header('Location:..');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class emailHandler extends OfanCoreFramework
{
	private $_mail;
	private $_server;
	private $_port;
	private $_secure;
	private $_auth;

	/**
	 * Pengiriman email masih menggunakan integrasi SMTP Google/Gmail dengan PHP Mailer (WORKED!)
	 * Kedepan gunakan vendor lain atau maksimalkan layanan sewa server dengan memperbaharui script Core Web Service nya
	 * Bisa menggukan plugin yg sudah di install (PHP Mailer) atau membuat sendiri Class Email Sender
	 */
	function __construct($param=null)
	{
		$this->_server = isset($param['server']) ? $param['server'] : 'smtp.gmail.com';
		$this->_port = isset($param['port']) ? $param['port'] : 587;
		$this->_secure = isset($param['secure']) ? $param['secure'] : 'tls';
		$this->_auth = isset($param['auth']) ? $param['auth'] : true;

		require parent::_thisPackage().'PHPMailer/src/Exception.php';
		require parent::_thisPackage().'PHPMailer/src/PHPMailer.php';
		require parent::_thisPackage().'PHPMailer/src/SMTP.php';
		$this->_mail = new PHPMailer;
	}

	public function connect($smtpUsername, $smtpPassword)
	{
		$this->_mail->isSMTP(); 
		$this->_mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
		$this->_mail->Host = $this->_server; // use $this->_mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
		$this->_mail->Port = $this->_port; // TLS only
		$this->_mail->SMTPSecure = $this->_secure; // ssl is depracated
		$this->_mail->SMTPAuth = $this->_auth;
		$this->_mail->Username = $smtpUsername;
		$this->_mail->Password = $smtpPassword;
		return $this;
	}

	public function header($emailFrom, $emailFromName, $emailTo, $emailToName)
	{
		$this->_mail->setFrom($emailFrom, $emailFromName);
		$this->_mail->addAddress($emailTo, $emailToName);
		return $this;
	}

	public function body($subject, $message, $type='html')
	{
		$this->_mail->Subject = $subject;
		if($type='html')
		{
			$this->_mail->msgHTML($message); //$this->_mail->msgHTML(file_get_contents('contents.html'), __DIR__);
		}
		else
		{
			$this->_mail->AltBody = htmlentities($message);
		}
		return $this;
	}

	public function attachment($file)
	{
		$this->_mail->addAttachment($file); //Attach an image file
		return $this;
	}

	public function send()
	{
		return $this->_mail->send();
	}
}
?>