<?php
namespace Oktopus;

/**
 * Main Oktopus Autoloader
 *
 * @author geraldcroes
 */
class Autoloader {
	/**
	 * Singleton
	 */
	private final function __construct (){}
	/**
	 * Singleton
	 */
	private final function __sleep (){}
	/**
	 * Singleton
	 */
	private final function __clone (){}
	/**
	 * Singleton
	 */
	private final function __wakeup(){}
	/**
	 * Singleton
	 * @var Oktopus\Autoloader
	 */
	private static $_instance = false;

	/**
	 * Register the autoloader to the stack
	 *
	 * @param string $pTmpPath
	 * @throws Exception if the autoloader is already registered
	 */
	public function register (){
		if (self::isRegistered ()){
			throw Exception ('Oktopus\Autoloader is already registered');
		}else{
			spl_autoload_register (array (self::$_instance, 'autoload'));
		}
		return $this;
	}
	
	/**
	 * Gets a single instance of the Autoloader 
	 * @return Autoloader
	 */
	public static function instance (){
		if(self::$_instance === false){
			include (OKTOPUS_PATH.'engine/codeparser/ClassParserForPHP5_3.class.php');
			include (OKTOPUS_PATH.'engine/decorator/LambdaFilterIteratorDecorator.class.php');

			self::$_instance = new Autoloader();
			self::$_instance->_classHunter = new ClassParserForPHP5_3();
		}
		return self::$_instance;
	} 

	/**
	 * Remove Autoloader from the autoload stack
	 */
	public function unregister (){
		spl_autoload_unregister (array (self::$_instance, 'autoload'));
	}

	/**
	 * Says if the Autoloader is registered
	 * @return boolean
	 */
	public function isRegistered (){
		if (($stack = spl_autoload_functions ()) !== false){
			foreach ($stack as $autoloadDescription){
				if (is_array ($autoloadDescription)){
					if (isset ($autoloadDescription[0]) && $autoloadDescription[0] === self::$_instance){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * The cache path on the file system to store the previously parsed classes
	 *
	 * @var string
	 */
	private $_cachePath;
	/**
	 * Defines the cache where Oktopus\Autoload can write cache files
	 *
	 * @param string $pTmp the path where to store cache files
	 * @throws AutoloaderException if the given path is not writable
	 */
	public function setCachePath ($pTmp){
		if (! file_exists ($pTmp)){
			if (! mkdir ($pTmp, 0755, true)){
				throw new AutoloaderException('Cannot craete the given CachePath ['.$pTmp.']');
			}			
		}else{
			if (!is_writable ($pTmp)){
				throw new AutoloaderException('Cannot write in given CachePath ['.$pTmp.']');
			}
		}
		$this->_cachePath = $pTmp;
		return $this;
	}

	//--- Autoload
	public function autoload ($pClassName){
		//On regarde si on connais la classe
		if ($this->_loadClass ($pClassName)){
			return true;
		}

		//Si on a le droit de tenter la regénération du fichier d'autoload, on retente l'histoire
		if ($this->_canRegenerate){
			$this->_canRegenerate = false;//pour éviter que l'on
			$this->_includesAll ();
			$this->_saveInCache ();
			return $this->autoload ($pClassName);
		}
		//on a vraiment rien trouvé.
		return false;
	}
	private $_canRegenerate = true;
	//--- /Autoload

	/**
	 * Recherche de toutes les classes dans les répertoires donnés
	 */
	private function _includesAll (){
		//Inclusion de toute les classes connues
		foreach ($this->_directories as $directory=>$recursive){
			$directories = new \AppendIterator ();

			//On ajoute tous les chemins à parcourir
			if ($recursive){
				$directories->append (new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($directory)));
			}else{
				$directories->append (new \DirectoryIterator ($directory));
			}

			//On va filtrer les fichiers php depuis les répertoires trouvés.
			$files = new LambdaFilterIteratorDecorator($directories);
			$files->setLambda(function ($filterIterator) {
				if (substr ($filterIterator->current (), -1 * strlen ('.php')) === '.php'){
					return is_readable ($filterIterator->current ());
				}
				return false;
			}, false);

			foreach ($files as $fileName){
				$classes = $this->_classHunter->find ((string) $fileName);
				foreach ($classes as $className=>$fileName){
					$this->_classes[strtolower ($className)] = $fileName;
				}
			}
		}
	}

	/**
	 * The founded classes
	 *
	 * @var array
	 */
	private $_classes = array ();
	/**
	 * Saves the classes in the cache path
	 *
	 * @throws AutoloaderException
	 */
	private function _saveIncache (){
		$toSave = '<?php $classes = '.var_export ($this->_classes, true).'; ?>';
		if (file_put_contents ($this->_cachePath.'directoriesautoloader.cache.php', $toSave) === false){
			throw new AutoloaderException ('Cannot write cache file '.$this->_cachePath.'directoriesautoloader.cache.php');
		}
	}

	/**
	 * Trys to find a class
	 * @param string $pClassName the class name to find
	 * @return boolean (the class was found true, or not false)
	 */
	private function _loadClass ($pClassName){
		$className = strtolower ($pClassName);
		if (count ($this->_classes) === 0){
			if (is_readable ($this->_cachePath.'directoriesautoloader.cache.php')){
				require ($this->_cachePath.'directoriesautoloader.cache.php');
				$this->_classes = $classes;
			}
		}
		if (isset ($this->_classes[$className])){
			require_once ($this->_classes[$className]);
			return true;
		}
		return false;
	}

	/**
	 * Adds a path to look for classes
	 * @param string  $pDirectory where to look for php files
	 * @param boolean $pRecursive if we'll look recursively into the class tree
	 *
	 */
	public function addPath ($pDirectory, $pRecursive = true, $pMustExists = true, $pCallBackFilter = null){
		if (! is_readable ($pDirectory)){
			if ($pMustExists){
				//The directory must exists. Raise an exception.
				throw new AutoloaderException('Cannot read from ['.$pDirectory.']');
			}
		}
		$this->_directories[$pDirectory] = $pRecursive ? true : false;
		return $this;
	}

	/**
	 * Current path where to look for classes
	 * @see Oktopus\Autoloader::addPath 
	 * @var array
	 */
	private $_directories = array ();
}