<?php if(!defined('_thisFileDIR')) header('Location:..');
/**
 * Function import
 * Adalah fungsi-fungsi untuk malakukan import method/class/object kedalam controller atau lainnya
 * 
 * Nama System: OfanCoreFramework
 * Nama Function: 
 *			1. import_service
 *			2. import_controller
 * Constructor @param tidak ada
 * @author OFAN
 * @since 2018
 * @version 1.0
 * @copyright GNU & GPL license
 */
class ImportClass
{
	public $_name;
	private $_filename;

	public function name($name)
	{
		$this->_name = $name;
		return $this;
	}

	protected function requires($from)
	{
		$filename = $this->_filename;
		if(!file_exists($filename)) die(_error(null, 'Error '.(ucwords($from)).' File Method Undefined', 500));
			require_once($filename);
	}

	public function from($from=null)
	{
		switch($from)
		{
			case 'service':
				$name = strtolower($this->_name);
				$filename = _thisServiceDIR.$name.'Services.php';
			break;
			case 'controller':
				$name = $this->_name;
				$filename = _thisControllerDIR.$name.'.php';
			break;
			case 'snippet':
				$name = $this->_name;
				$filename = _thisSnippetDIR.$name.'.php';
			break;
			default:
				$filename = false;
			break;
		}

		$this->_filename = $filename;
		return $this->requires($from);
	}
}

class Imports
{
	public static function name($name)
	{
		$import = new ImportClass();
		return $import->name($name);
	}
}

function import_service($name)
{
	Imports::name($name)->from('service');
}

function import_controller($name)
{
	Imports::name($name)->from('controller');
}

function import_snippet($name)
{
	
	$filename = _thisSnippetDIR.$name.'.php';
	if(!file_exists($filename)) die(_error(null, 'Error Snippet File Undefined', 500));
		include($filename);
}
?>