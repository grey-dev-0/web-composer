<?php namespace GreyDev\WebComposer;

use Composer\Console\Application;
use GreyDev\WebComposer\Models\Package;
use GreyDev\WebComposer\Models\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;

class PackageProcessor{
	/**
	 * @var $composer Application Composer application instance.
	 */
	private $composer;
	/**
	 * @var $consoleOutput BufferedOutput|StreamOutput Console output.
	 */
	private $consoleOutput;
	/**
	 * @var $cacheDir string Directory where the packages cache files are stored.
	 */
	private $cacheDir;
	/**
	 * @var $baseUrl string Base URL of the application including the web-composer path.
	 */
	private $baseUrl;
	/**
	 * @var $appKey string Application's internal key for background tasks URL protection.
	 */
	private $appKey;
	/**
	 * @var resource|null Optional console log file handle.
	 */
	public $consoleLog = null;

	public function __construct($cacheDir, $baseUrl, $appKey){
		$this->cacheDir = $cacheDir;
		$this->baseUrl = $baseUrl;
		$this->appKey = $appKey;
	}

	/**
	 * Getting installed packages and their count (paginated)
	 *
	 * @return array Packages collection and count
	 */
	public function getInstalled(){
		if(!is_file("{$this->cacheDir}/installed.cache"))
			$packages = $this->cacheInstalledPackages();
		else
			$packages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$packagesCount = $packages->count();
		$packages = $packages->get(0, 10);
		return compact('packagesCount', 'packages');
	}

	/**
	 * Getting another page of installed packages - total count included in response.
	 *
	 * @param $offset int The index of the first package requested in the collection.
	 * @param $length int Number of packages that should be retrieved from the collection.
	 * @return array Packages collection including total count.
	 */
	public function getAjaxInstalled($offset, $length){
		$packages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$packagesCount = $packages->count();
		$packages = $packages->get($offset, $length);
		return compact('packagesCount', 'packages');
	}

	/**
	 * Getting another page of all world's packages - total count included in response.
	 *
	 * @param $offset int The index of the first package requested in the collection.
	 * @param $length int Number of packages that should be retrieved from the collection.
	 * @return array Packages collection including total count.
	 */
	public function getAjaxAll($offset, $length){
		$this->setupEnvironment();
		$packages = unserialize(file_get_contents("{$this->cacheDir}/all.cache"));
		$packagesCount = $packages->count();
		$packages = $packages->get($offset, $length);
		return compact('packagesCount', 'packages');
	}

	/**
	 * Getting all packages of the world.
	 *
	 * @return array Requested packages including metadata.
	 */
	public function getAll(){
		ini_set('memory_limit', '1G');
		if(is_file("{$this->cacheDir}/all.cache"))
			$packages = unserialize(file_get_contents("{$this->cacheDir}/all.cache"));
		else
			$this->cacheAllPackages();
		return (isset($packages))? ['packagesCount' => $packages->count(), 'packages' => $packages->get(0, 10)]
			: ['packagesCount' => 0, 'packages' => []];
	}

	/**
	 * Cache and provide the collection of installed packages.
	 *
	 * @return Collection Installed packages collection
	 */
	private function cacheInstalledPackages(){
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show']);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		$packages = new Collection();
		foreach($lines as &$line){
			if(empty($line))
				continue;
			$line = preg_split('/ +/', $line);
			$packages->add(new Package($line[0], $line[1], implode(' ', array_values(array_except($line, [0, 1]))), true));
		}
		if(!is_dir($this->cacheDir))
			mkdir($this->cacheDir);
		$packages->cache("{$this->cacheDir}/installed.cache");
		return $packages;
	}

	/**
	 * Cache all world's packages in background.
	 */
	public function taskCacheAllPackages(){
		$this->setupEnvironment();
		$packages = new Collection();
		if(!is_dir($this->cacheDir))
			mkdir($this->cacheDir);
		$packages->cache("{$this->cacheDir}/all.cache");
		$installedPackages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$installedPackagesNames = $installedPackages->getNames();
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show', '-a' => true]);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		foreach($lines as &$line){
			if(empty($line))
				continue;
			$line = preg_split('/ +/', $line);
			if(($i = array_search($line[0], $installedPackagesNames)) !== false)
				$packages->add($installedPackages->get($i, 1));
			else
				$packages->add(new Package($line[0], null, null));
		}
		$packages->cache("{$this->cacheDir}/all.cache");
	}

	/**
	 * Cache all world's packages - front-end handler.
	 */
	private function cacheAllPackages(){
		$this->makeBackgroundRequest("{$this->baseUrl}/cache-all-packages");
	}

	/**
	 * Refreshing a package details data in the All Packages collection.
	 *
	 * @param $name string Package name to be refreshed.
	 * @return array|false Returns full package details or false if package is no longer available.
	 */
	public function refreshPackage($name){
		$packageData = $this->getPackageDetails($name);
		if(!isset($packageData['name']) || is_null($packageData['name']))
			return false;
		$this->makeBackgroundRequest("{$this->baseUrl}/update-package", [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query(['package' => $packageData, 'file' => "{$this->cacheDir}/all.cache"])
		]);
		return $packageData;
	}

	/**
	 * Uninstall an existing package from the application - front-end handler.
	 *
	 * @param $package string Package name to be removed.
	 */
	public function removePackage($package){
		$this->makeBackgroundRequest("{$this->baseUrl}/task-remove-package", [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query(compact('package'))
		]);
	}

	/**
	 * Uninstall an existing package from the application - background task.
	 *
	 * @param $package string Package name to be removed.
	 */
	public function taskRemovePackage($package){
		$this->setupEnvironment();
		$this->initComposer();
		$input = new ArrayInput(['command' => 'remove', 'packages' => [$package]]);
		$this->composer->run($input, $this->consoleOutput);
		$this->removePackageFromCache($package, "{$this->cacheDir}/installed.cache");
	}

	/**
	 * Removing a package from a cached collection.
	 *
	 * @param $package string Name of the package to be removed from the cache.
	 * @param $cacheFile string Filename where cache is stored.
	 */
	private function removePackageFromCache($package, $cacheFile){
		$packages = unserialize(file_get_contents($cacheFile));
		$packages->remove($package);
		$packages->cache($cacheFile);
	}

	/**
	 * Fetching console output content written by other tasks.
	 *
	 * @return array Console output content.
	 */
	public function fetchConsoleOutput(){
		$logFile = "{$this->cacheDir}/console.log";
		return ['content' => ((is_file($logFile))? file_get_contents($logFile) : 'No console output.')];
	}

	/**
	 * Requesting a background task to be run.
	 *
	 * @param $url string Task URL to be called.
	 * @param $options array Extra cURL request options to be set.
	 * @return string Background task response if returned.
	 */
	private function makeBackgroundRequest($url, $options = []){
		$request = curl_init($url);
		curl_setopt_array($request, [
			CURLOPT_TIMEOUT_MS => 1002,
			CURLOPT_CONNECTTIMEOUT_MS => 1001,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/x-www-form-urlencoded',
				"App-Key: {$this->appKey}",
			]
		] + $options);
		return curl_exec($request);
	}

	/**
	 * Background task that refreshes a package info in the requested cache file.
	 *
	 * @param $packageData array Associative array that resembles the info of the package to be updated.
	 * @param $cacheFile string The cache file that contains the package to be updated.
	 */
	public function taskRefreshPackage($packageData, $cacheFile){
		$this->setupEnvironment();
		$packages = unserialize(file_get_contents($cacheFile));
		if(!isset($packageData['dependencies']))
			$packageData['dependencies'] = [];
		$packages->update($packageData, $cacheFile);
	}

	/**
	 * Getting package's info from Composer.
	 *
	 * @param $packageName string Name of the package that is queried about.
	 * @return array Associative array that represents the retrieved package info.
	 */
	private function getPackageDetails($packageName){
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show', '-a' => true, 'package' => $packageName]);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		$requires = false;
		$dependencies = [];
		do{
			$line = current($lines);
			$output = explode(':', $line);
			switch(trim($output[0])){
				case 'name': $name = trim($output[1]); break;
				case 'descrip.': $description = trim($output[1]); break;
				case 'versions': $available_versions = explode(', ', trim($output[1])); break;
				case 'requires': case 'requires (dev)': $requires = true; break;
				default:
					if($requires){
						if($output[0] == '')
							$requires = false;
						else
							$dependencies[] = trim($output[0]);
					}
			}
		} while(next($lines));
		return compact('name', 'description', 'available_versions', 'dependencies');
	}

	/**
	 * Initializing Composer application instance.
	 */
	private function initComposer(){
		$this->composer = new Application();
		$this->composer->setAutoExit(false);
		$this->consoleOutput = (is_null($this->consoleLog))?
			new BufferedOutput() : new StreamOutput($this->consoleLog);
		putenv('COMPOSER_HOME='.storage_path());
	}

	/**
	 * Setting up environment settings for long running tasks.
	 */
	private function setupEnvironment(){
		set_time_limit(0);
		ignore_user_abort(true);
		ini_set('memory_limit', '1G');
	}
}