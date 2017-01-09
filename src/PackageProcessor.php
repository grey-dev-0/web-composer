<?php namespace GreyDev\WebComposer;

use Composer\Console\Application;
use GreyDev\WebComposer\Models\Package;
use GreyDev\WebComposer\Models\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PackageProcessor{
	/**
	 * @var $composer Application Composer application instance.
	 */
	private $composer;
	/**
	 * @var $consoleOutput BufferedOutput Console output.
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

	public function __construct($cacheDir, $baseUrl){
		$this->cacheDir = $cacheDir;
		$this->baseUrl = $baseUrl;
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

	public function getAll(){
		ini_set('memory_limit', '1G');
		if(is_file("{$this->cacheDir}/all.cache"))
			$packages = unserialize(file_get_contents("{$this->cacheDir}/all.cache"));
		else
			$this->cacheAllPackages();
		return (isset($packages))? ['packagesCount' => $packages->count(), 'packages' => $packages->get(0, 10)]
			: ['packagesCount' => 0, 'packages' => []];
	}

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

	public function taskCacheAllPackages(){
		$this->setupEnvironment();
		$installedPackages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$installedPackagesNames = $installedPackages->getNames();
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show', '-a' => true]);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		$packages = new Collection();
		if(!is_dir($this->cacheDir))
			mkdir($this->cacheDir);
		$packages->cache("{$this->cacheDir}/all.cache");
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

	private function cacheAllPackages(){
		$request = curl_init("{$this->baseUrl}/cache-all-packages");
		curl_setopt_array($request, [
			CURLOPT_TIMEOUT_MS => 1002,
			CURLOPT_CONNECTTIMEOUT_MS => 1001,
			CURLOPT_RETURNTRANSFER => true
		]);
		curl_exec($request);
	}

	/**
	 * Refreshing a package details data in the All Packages collection (deferred in a background task).
	 *
	 * @param $name string Package name to be refreshed.
	 * @return array|false Returns full package details or false if package is no longer available.
	 */
	public function refreshPackage($name){
		$packageData = $this->getPackageDetails($name);
		if(!isset($packageData['name']) || is_null($packageData['name']))
			return false;
		$taskRequest = curl_init("{$this->baseUrl}/update-package");
		curl_setopt_array($taskRequest, [
			CURLOPT_TIMEOUT_MS => 1002,
			CURLOPT_CONNECTTIMEOUT_MS => 1001,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query(['package' => $packageData, 'file' => "{$this->cacheDir}/all.cache"]),
			CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
		]);
		curl_exec($taskRequest);
		return $packageData;
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

	private function initComposer(){
		$this->composer = new Application();
		$this->composer->setAutoExit(false);
		$this->consoleOutput = new BufferedOutput();
		putenv('COMPOSER_HOME='.storage_path());
	}

	private function setupEnvironment(){
		set_time_limit(0);
		ignore_user_abort(true);
		ini_set('memory_limit', '1G');
	}
}