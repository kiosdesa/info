<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * @author ofanebob
 * @copyright 2017
 * method dbHandler() menggunakan PDO
 * Berlaku untuk versi PHP terbaru (2015 - 2017)
 *
 * Credenial PostgreSQL untuk cPanel:
 * host: localhost
 * database: sofandan_bumdesa
 * user: sofandan_ofan
 * password: laillaha.ilallah.1
 * port: 5432
 */
if(!class_exists('OfanCoreFramework')) die('Error Class OfanCoreFramework');

class dbHandler extends OfanCoreFramework
{
    private $_Manifest;

    function __construct()
    {
        $this->_Manifest = parent::_loadDatabaseConfig();
    }


    protected function credential($Manifest, $arg)
    {
        $_platform = $Manifest['platform'];
        $identifyDBname = isset($arg['dbname']) ? $arg['dbname'] : null;
        $_database = array_key_exists($identifyDBname, $Manifest['mapping'][0]['cluster']) ? $Manifest['mapping'][0]['cluster'][$identifyDBname] : null;

        $_username = $Manifest['mapping'][0]['credential']['user'];
        $_password = $Manifest['mapping'][0]['credential']['password'];
        $_hosting = $Manifest['mapping'][0]['server']['host'];
        $_port = $Manifest['mapping'][0]['server']['port'];
        @$_connecting = new PDO("$_platform:host=$_hosting;port=$_port;dbname=$_database;user=$_username;password=$_password");
        //$_connecting->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $_connecting;
    }


    public function check($arguments=false)
    {
        //if(!is_array($arguments)) return false;
        $Manifest = $this->_Manifest;
        $clusterDB = array_rand($Manifest['mapping'][0]['cluster']);
        return $this->connect(array('dbname'=>$clusterDB));
    }


    /**
     * Establishing database connection
     * @return database connection handler
     */
    public function connect($arguments=false)
    {
        if(is_array($arguments))
        {
            /* Memanggil dan menginject file json untuk informasi credential SQL Server
             * Skema file JSON ada di:
             * /manifest/.config --> dbconfig {}
             */
            $Manifest = $this->_Manifest;
            if($Manifest)
            {
                try
                {   
                    $conn = $this->credential($Manifest, $arguments);
                    if($conn)
                    {
                        return $conn;
                    }
                    else
                    {
                        //return 'Error 3';
                        return false;
                    }
                }
                catch(Exception $e)
                {
                    //return $e->getMessage();
                    return false;
                }
            }
            else
            {
                //return 'Error 2';
                return false;
            }
        }
        else
        {
            //return 'Error 1';
            return false;
        }
    }
}
?>