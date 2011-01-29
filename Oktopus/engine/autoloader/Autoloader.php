<?php
namespace Oktopus;

/**
 * Main Oktopus Autoloader
 *
 * @author geraldcroes
 */
class Autoloader {
	/**
	 * Construct
	 * @param string $pTmpPath the path where to store the cache files
	 * @param ICLassParser the class parser to find classes in PHPFiles.
	 */
	public final function __construct ($pTmpPath, IClassParser $pClassParser){
		$this->_classHunter = $pClassParser;
		$this->setCachePath($pTmpPath);	
	}
	
	/**
	 * The class parser Oktopus will use
	 * @var Oktopus\IClassParser
	 */
	private $_pClassParser;

	/**
	 * Register the autoloader to the stack
	 *
	 * @param string $pTmpPath
	 * @throws Exception if the autoloader is already registered
	 */
	public function register (){
		if ($this->isRegistered ()){
			require_once (OKTOPUS_PATH.'engine/exception/Exception.php');
			require_once (__DIR__.'AutoaderException.php');
			throw new Exception ('Oktopus\Autoloader is already registered');
		}else{
			spl_autoload_register (array ($this, 'autoload'));
		}
		return $this;
	}
	
	/**
	 * Remove Autoloader from the autoload stack
	 */
	public function unregister (){
		spl_autoload_unregister (array ($this, 'autoload'));
	}

	/**
	 * Says if the Autoloader is registered
	 * @return boolean
	 */
	public function isRegistered (){
		if (($stack = spl_autoload_functions ()) !== false){
			foreach ($stack as $autoloadDescription){
				if (is_array ($autoloadDescription)){
					if (isset ($autoloadDescription[0]) && $autoloadDescription[0] === $this){
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
				throw new AutoloaderException('Cannot create the given CachePath ['.$pTmp.']');
			}			
		}else{
			if (!is_writable ($pTmp)){
				throw new AutoloaderException('Cannot write in given CachePath directory ['.$pTmp.']');
			}
		}
		$this->_cachePath = $pTmp;
		return $this;
	}

	/**
	 * Loads the Directory classes
	 *  (check if the cache file exists, then check its content (debug mode) and compiles if needed)
	 * 
	 * @param string $pDirectoryName the directory name we want to compile
	 */
	private function _loadDirectoryClasses ($pDirectoryName, $pRecurse, $pForce = false, $doNotCheck = false){
		///Can we find the directory index ?
		$directoryIndex = array ();
		$listHasChanged = false;

		if ($pForce == false && is_readable ($cacheFileName = $this->_makeFileName ($pDirectoryName, $pRecurse))){
			require ($cacheFileName);
			$this->_directoryClasses[$pDirectoryName] = $allClasses;
			$checkContent = true;
			//we'll get directoryIndex in the included file
			//we'll also get the classes by files
			if ($doNotCheck === true){
				//We were asked to just load the file if it exists.... exiting
				return;
			}
		}else{
			$checkContent = false;
			//we just don't have to check the content, we know we have to autoload everything
		}

		//We are not gonna check the files of the directoryIndex if we are in a production mode.
		if ($checkContent && 
				(Engine::getMode () === Engine::MODE_PRODUCTION)){
			return;			
		}

		//Prepare the iterator to compile all the directory classes
		if ($pRecurse){
			$directories = new \RecursiveIteratorIterator (new \RecursiveDirectoryIterator ($pDirectoryName));
		}else{
			$directories = new \DirectoryIterator ($pDirectoryName);
		}

		require_once (OKTOPUS_PATH.'engine/decorator/LambdaFilterIteratorDecorator.php');
		$files = new LambdaFilterIteratorDecorator($directories);
		$files->setLambda(function ($filterIterator) {
			if (substr ($filterIterator->current (), -1 * strlen ('.php')) === '.php'){
				return is_readable ($filterIterator->current ());
			}
			return false;
		}, false);
		
		//We iterate in the directories to find files.
		foreach ($files as $fileName){
			if ($checkContent){
				if (!array_key_exists ((string) $fileName, $directoryIndex) || 
					 ($directoryIndex[(string)$fileName] < ($fileMTime = $fileName->getMTime ()))){
					 	//The file is not registered in the directory index
					 	//Or the file is not up to date....
					$directoryIndex[(string) $fileName] = $fileMTime;//we want to use the compared fileMTime to avoid shortcut conditions
					$compileFile = true;
				}else{
					//The file is up to date and is in the directoryIndex
					$compileFile = false;
				}
			}else{
				//We did not have to check the directory index content....
				//meaning that we have to compile everything.
				$compileFile = true;
			}

			//So we have to compile the file
			if ($compileFile){
				$directoryIndex[(string) $fileName] = $fileName->getMTime ();
				$classes[(string) $fileName]= $this->_classHunter->find ((string) $fileName);
				$compiledFiles[(string) $fileName] = true;
				$listHasChanged = true;
			}else{
				$compiledFiles[(string) $fileName] = false;
			}
		}

		//if we had to check the content of the index, we'll now have to iterate through the old index to find
		// if there are no missing classes.
		$toRemoveFiles = array ();
		foreach ($directoryIndex as $fileName=>$fileMTime){
			//The file has just been checked or is up to date ?
			if (isset ($compiledFiles[$fileName])){
				//nothing to do!
				continue;
			}
			
			//the file does not exists anymore ? (only reason or it would have been in the iterated elements)
			$toRemoveFiles[] = $fileName; 
		}

		//we're gonna remove old files from classes.
		foreach ($toRemoveFiles as $fileName){
			if (isset ($classes[$fileName])){
				$listHasChanged = true;
				unset ($classes[$fileName]);
			}
		}

		//now we're gonna make a direct access array to get the files.
		if (!$listHasChanged){
			return; 
		}

		$allClasses = array ();
		foreach ($classes as $fileName=>$classesInFileName){
			foreach ($classesInFileName as $className){
				$className = strtolower($className);
				if (isset ($allClasses[$className])){
					if (is_array ($allClasses[$className])){
						if (!in_array($fileName, $allClasses[$className], true)){
							$allClasses[$className][] = $fileName;
						}else{
							trigger_error("The class $className was found twice or more in the file $fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING);
						}
					}else{
						if ($allClasses[$className] !== $fileName){
							$allClasses[$className] = array ($allClasses[$className], $fileName);
						}else{
							trigger_error("The class $className was found twice or more in the file $fileName (PHP may trigger a FATAL ERROR while loading the file)", E_USER_WARNING);
						}
					}
				}else{
					$allClasses[$className] = $fileName;
				}
			}
		}

		//Will adress warnings if a class was found in multiple files.
		foreach ($allClasses as $className=>$files){
			if (is_array ($files)){
				$countFiles = count ($files);
				trigger_error ("The class $className was found in $countFiles different files ".implode (', ', $files) .", the Oktopus Autoloader will use the first file while autoloading the Object", E_USER_WARNING);
			}
		}

		$this->_saveInCache($directoryIndex, $classes, $allClasses, $cacheFileName);
		$this->_directoryClasses[$pDirectoryName] = $allClasses;
	}

	/**
	 * Makes the cache file name for the given directory
	 * 
	 * @param string $pDirectoryName
	 * @param string $pRecurse
	 */
	private function _makeFileName ($pDirectoryName, $pRecurse){
		return $this->_cachePath.'autoload/'.($pRecurse ? '_R_' : '' ).substr (realpath ($pDirectoryName).'index.php', 1);
	}

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
	private function _saveIncache ($directoryIndex, $classes, $allClasses, $fileName){
		$toSave = '<?php $classes = '.var_export ($classes, true).';';
		$toSave .= '$allClasses = '.var_export ($allClasses, true).';';
		$toSave .= '$directoryIndex = '.var_export ($directoryIndex, true).';';

		if (!file_exists (dirname ($fileName))){
			mkdir (dirname ($fileName), 0755, true);
		}

		if (file_put_contents ($fileName, $toSave, true) === false){
			throw new AutoloaderException ('Cannot write cache file '.$this->_cachePath.'directoriesautoloader.cache.php');
		}
	}

	/**
	 * Trys to find a class
	 * 
	 * @param string $pClassName the class name to find
	 * @return boolean (the class was found true, or not false)
	 */
	public function autoload ($pClassName, $justCheck = true){
		$pClassName = strtolower ($pClassName);

		foreach ($this->_directories as $name=>$recurse){
			if (isset ($this->_directoryClasses[$name])){
				if (isset ($this->_directoryClasses[$name][$pClassName])){
					if (! $this->_includeDirectoryClass ($name, $pClassName)){
						//We couldn't include the class (maybe the file was deleted....
						//We have to compile the file again.
						$this->_loadDirectoryClasses ($name, $recurse, true);
						if ($this->_includeDirectoryClass($name, $pClassName)){
							//founded, return
							return true;
						}//else we continue..... maybe the class is in another directory now
					}else{
						return true;
					}
				}
			}else{
				$this->_loadDirectoryClasses ($name, $recurse, false, $justCheck);
				//we check if the class has been found
				if ($this->_includeDirectoryClass($name, $pClassName)){
					//founded, return
					return true;
				}
			}
		}

		//did not find the class.... we then try with not "just a check"
		//justACheck enables the developpement mode to be not that a heavy time cost
		if ($justCheck && Engine::getMode () !== Engine::MODE_PRODUCTION){
			return $this->autoload ($pClassName, false);
		}

		//there are no class that match at all
		return false;
	}
	
	/**
	 * We call this method when we are absolutely sure that
	 *  
	 * @param string $pDirectory
	 * @param string $pClassName
	 */
	private function _includeDirectoryClass ($pDirectory, $pClassName){
		if (! isset ($this->_directoryClasses[$pDirectory][$pClassName])){
			return false;
		}
		if (is_array($this->_directoryClasses[$pDirectory][$pClassName])){
			$classFile = $this->_directoryClasses[$pDirectory][$pClassName][0];
		}else{
			$classFile = $this->_directoryClasses[$pDirectory][$pClassName];
		}
		require_once ($classFile);
		return true;
	}
	
	/**
	 * Adds a path to look for classes
	 * @param string  $pDirectory where to look for php files
	 * @param boolean $pRecursive if we'll look recursively into the class tree
	 */
	public function addPath ($pDirectory, $pRecursive = true, $pMustExists = true){
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